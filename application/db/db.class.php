<?php
class db{
    public $db = null; // 当前数据库操作对象
    public $config = array();
    public $sql = '';//sql语句，主要用于输出构造成的sql语句
    public $pre = '';//表前缀，主要用于在其他地方获取表前缀
    private $data = array();// 数据信息
    private $options = array(); // 查询表达式参数
    private $comparison = array('eq'=>'=','neq'=>'!=','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE');//数据库表达式

    public function __construct($config=array()){
        global $config;
        $this->config['DB_TYPE'] = 'mysql';//数据库类型
        $this->config['DB_HOST'] = $config['db']['db_host'];//数据库主机
        $this->config['DB_USER'] = $config['db']['db_username'];//数据库用户名
        $this->config['DB_PWD'] = $config['db']['db_password'];//数据库密码
        $this->config['DB_PORT'] = $config['db']['db_port'];//数据库端口，mysql默认是3306，一般不需要修改
        $this->config['DB_NAME'] = $config['db']['db_name'];//数据库名
        $this->config['DB_CHARSET'] = $config['db']['db_charset'];//数据库编码
        $this->config['DB_PREFIX'] = $config['db']['db_prefix'];//数据库表前缀
        $this->config['DB_PCONNECT'] = false;//true表示使用永久连接，false表示不适用永久连接，一般不使用永久连接
        $this->options['_field'] = '*';//默认查询字段
        $this->pre = $this->config['DB_PREFIX'];//数据表前缀
    }

    //连接数据库
    public function connect(){
        //$this->db不是对象，则连接数据库
        if(!is_object($this->db)){
            $db_type = $this->config['DB_TYPE'];
            $this->db = new $db_type();//连接数据库
            $this->db->connect($this->config['DB_HOST'].":".$this->config['DB_PORT'], $this->config['DB_USER'], $this->config['DB_PWD'], $this->config['DB_NAME'] , $this->config['DB_CHARSET'] , $this->config['DB_PCONNECT'] , $this->config['DB_PREFIX']) ;
         }
    }
    //切换数据库
    public function switch_db($dbname){
        $this->connect();
        $this->db->select_db($dbname);
    }
    //设置表，$$ignore_prefix为true的时候，不加上默认的表前缀
    public function table($table,$ignore_prefix=false){
        if($ignore_prefix){
            $this->options['_table'] = $this->addSpecialChar($table);
        }else{
            $table = $this->config['DB_PREFIX'].$table;
            $this->options['_table'] = $this->addSpecialChar($table);
        }
        return $this;
    }
     //回调方法，连贯操作的实现
    public function __call($method,$args){
        $method = strtolower($method);
        if(in_array($method,array('field','data','where','group','having','order','limit'))){
            $this->options['_'.$method] = $args[0];//接收数据
            return $this;//返回对象，连贯查询
        }
        else{
            $this->error($method.'方法在类中没有定义');
        }
    }
    //启用事务
    public function startTrans(){
        $this->connect();
        return $this->db->startTrans();
    }
    //事务提交
    public function commit(){
        $this->connect();
        return $this->db->commit();
    }
    //事务回滚
    public function rollback(){
        $this->connect();
        return $this->db->rollback();
    }
    //执行原生sql语句，如果sql是查询语句，返回二维数组
    public function query($sql){
        if(empty($sql)){
            return false;
        }
        $this->sql = $sql;
        //判断当前的sql是否是查询语句
        if(strpos(trim(strtolower($sql)),'select')===0){
            $data = array();
            $this->connect();
            $query = $this->db->query($this->sql);
            return $this->getAll($query);
        }else{
            $this->connect();
            return $query = $this->db->query($this->sql);//不是查询条件，执行之后，直接返回
        }
    }
    //获取查询数据
    public function getAll($query){
        $data = array();
        while($row = $this->db->fetch_array($query)){
            $data[] = $row;
        }
        return $data;
    }
    //统计行数
    public function count($field=''){
        $table = $this->options['_table'];//当前表
        if($field==''){//查询的字段
            $field = 'count(*)';
        }else{
            $field = 'count('.$field.')';
        }
        $where = $this->_parseCondition();//条件
        $this->sql = "SELECT $field FROM $table $where";
        $this->connect();
        $query = $this->db->query($this->sql);
        $data = $this->db->fetch_array($query);
        return $data[$field];
    }
    //只查询一条信息，返回一维数组
    public function find(){
        $table = $this->options['_table'];//当前表
        $field = $this->options['_field'];//查询的字段
        $field = $this->parseField($field);
        if(!isset($this->options['_limit'])){
            $this->options['_limit'] = '0,1';//只查询一条
        }
        $where = $this->_parseCondition();//条件
        $this->options['_field'] = '*';//设置下一次查询时，字段的默认值
        $this->sql = "SELECT $field FROM $table $where";
        $this->connect();
        $query = $this->db->query($this->sql);
        $data = $this->db->fetch_array($query);
        return $data;
     }
    //查询多条信息，返回数组
    public function select(){
        $table = $this->options['_table'];//当前表
        $field = $this->options['_field'];//查询的字段
        $field = $this->parseField($field);
        if(!isset($this->options['_limit'])){//避免忘记设置时查询出大量纪录
            $this->options['_limit'] = 300;
        }
        $where = $this->_parseCondition();//条件
        $this->options['_field'] = '*';//设置下一次查询时，字段的默认值
        $this->sql = "SELECT $field FROM $table $where";
        $data = array();
        $this->connect();
        $query = $this->db->query($this->sql);
        return $this->getAll($query);
    }
    //插入数据
    public function insert(){
        $this->connect();
        $table = $this->options['_table'];//当前表
        $data = $this->_parseData('add');//要插入的数据
        $this->sql = "INSERT INTO $table $data" ;
        $query = $this->db->query($this->sql);
        if($this->db->affected_rows()){
            return  $this->db->insert_id();
        }
        return false;
    }
    //替换数据
    public function replace(){
        $this->connect();
        $table = $this->options['_table'];//当前表
        $data = $this->_parseData('add');//要插入的数据
        $this->sql = "REPLACE INTO $table $data" ;
        $query = $this->db->query($this->sql);
        if($this->db->affected_rows()){
            return  $this->db->insert_id();
        }
        return false;
    }
    //修改更新
    public function update(){
        $this->connect();
        $table = $this->options['_table'];//当前表
        $data = $this->_parseData('save');//要更新的数据
        $where = $this->_parseCondition();//更新条件
        if(empty($data))
            return false;
        //修改条件为空时，则返回false，避免不小心将整个表数据修改了
        if(empty($where))
            return false;
        $this->sql = "UPDATE $table $data $where" ;
        $query = $this->db->query($this->sql);
        return $this->db->affected_rows();
    }
    //删除
    public function delete(){
        $this->connect();
        $table = $this->options['_table'];//当前表
        $where = $this->_parseCondition();//条件
        //删除条件为空时，则返回false，避免数据不小心被全部删除
        if(empty($where)){
            return false;
        }
        $this->sql = "DELETE FROM $table $where";
        $query = $this->db->query($this->sql);
        return $this->db->affected_rows();
    }
    //返回sql语句
    public function getSql(){
        return $this->sql;
    }
    //解析数据,添加数据时$type=add,更新数据时$type=save
    private function _parseData($type){
        if((!isset($this->options['_data']))||(empty($this->options['_data']))){
            unset($this->options['_data']);
            return false;
        }
        //如果数据是字符串，直接返回
        if(is_string($this->options['_data'])){
            $data = $this->options['_data'];
            unset($this->options['_data']);
            return $data;
        }
        switch($type){
            case 'add':
                foreach($this->options['_data'] as $key=>$value){
                    $value = $this->parseValue($value);
                    if($value===false||$value===true) continue;//过滤恒为false和true
                    if(is_scalar($value)){ // 过滤非标量数据
                        $values[] = $value;
                        $fields[] = $this->addSpecialChar($key);
                    }
                }
                unset($this->options['_data']);
                return ' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
                break;
            case 'save':
                foreach($this->options['_data'] as $key=>$value){
                    $value = $this->parseValue($value);
                    if($value===false||$value===true) continue;//过滤恒为false和true
                    if(is_scalar($value)) // 过滤非标量数据
                        $set[] = $this->addSpecialChar($key).'='.$value;
                }
                unset($this->options['_data']);
                return ' SET '.implode(',',$set);
                break;
            default:
                unset($this->options['_data']);
                return false;
        }
    }
    //解析sql查询条件
    private function _parseCondition(){
        $condition="";
        //解析where()方法
        if(!empty($this->options['_where'])){
            $where = $this->options['_where'];
            $where = $this->parseWhere($where);
            if($where){
                $condition .= ' WHERE '.$where;
            }
            unset($this->options['_where']);
        }
        if(!empty($this->options['_group'])&&is_string($this->options['_group'])){
            $condition .= " GROUP BY ".$this->options['_group'];
            unset($this->options['_group']);
        }
        if(!empty($this->options['_having'])&&is_string($this->options['_having'])){
            $condition .= " HAVING ".$this->options['_having'];
            unset($this->options['_having']);
        }
        if(!empty($this->options['_order'])&&is_string($this->options['_order'])){
            $condition .= " ORDER BY ".$this->options['_order'];
            unset($this->options['_order']);
        }
        if(!empty($this->options['_limit'])&&(is_string($this->options['_limit'])||is_numeric($this->options['_limit']))){
            $condition .= " LIMIT ".$this->options['_limit'];
            unset($this->options['_limit']);
        }
        if(empty($condition))
            return "";
        return $condition;
    }
    //where条件分析
    private function parseWhere($where){
        $whereStr = '';
        if(is_string($where)){
            // 直接使用字符串条件
            $whereStr = $where;
        }else{ // 使用数组条件表达式
            if(array_key_exists('_logic',$where)) {
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate = ' '.strtoupper($where['_logic']).' ';
                unset($where['_logic']);
            }else{
                // 默认进行 AND 运算
                $operate = ' AND ';
            }
            foreach ($where as $key=>$val){
                if(is_array($val) && empty($val)) continue;
                $whereStr .= "( ";
                if(0===strpos($key,'_')){
                    // 解析特殊条件表达式
                    $whereStr .= $this->parseSpecialWhere($key,$val);
                }else{
                    $key = $this->addSpecialChar($key);
                    if(is_array($val)) {
                        if(is_string($val[0])){
                            if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i',$val[0])) { // 比较运算
                                $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                            }elseif('exp'==strtolower($val[0])){ // 使用表达式
                                $whereStr .= ' ('.$key.' '.$val[1].') ';
                            }elseif(preg_match('/IN/i',$val[0])){ // IN 运算
                                if(is_string($val[1])) {
                                     $val[1] = explode(',',$val[1]);
                                }
                                $zone = implode(',',$this->parseValue($val[1]));
                                $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                            }elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
                                $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                                $whereStr .= ' ('.$key.' BETWEEN '.$data[0].' AND '.$data[1].' )';
                            }else{
                                $this->error($val[0]);
                            }
                        }else{
                            $count = count($val);
                            if(is_string($val[$count-1]) && in_array(strtoupper(trim($val[$count-1])),array('AND','OR','XOR'))){
                                $rule = strtoupper(trim($val[$count-1]));
                                $count = $count -1;
                            }else{
                                $rule = 'AND';
                            }
                            for($i=0;$i<$count;$i++){
                                $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                                if('exp'==strtolower($val[$i][0])) {
                                    $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                                }else{
                                    $op = is_array($val[$i])?$this->comparison[strtolower($val[$i][0])]:'=';
                                    $whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
                                }
                            }
                            $whereStr = substr($whereStr,0,-4);
                        }
                    }else {
                        $whereStr .= $key." = ".$this->parseValue($val);
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return empty($whereStr)?'':$whereStr;
    }
    //特殊条件分析
    private function parseSpecialWhere($key,$val){
        $whereStr = '';
        switch($key){
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr = $this->parseWhere($val);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val,$where);
                if(array_key_exists('_logic',$where)) {
                    $op = ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op = ' AND ';
                }
                $array = array();
                foreach($where as $field=>$data)
                    $array[] = $this->addSpecialChar($field).' = '.$this->parseValue($data);
                $whereStr = implode($op,$array);
                break;
        }
        return $whereStr;
    }
    //field分析
    private function parseField($fields){
        if(is_array($fields)){
            // 完善数组方式传字段名的支持
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array = array();
            foreach($fields as $key=>$field){
                if(!is_numeric($key))
                    $array[] = $this->addSpecialChar($key).' AS '.$this->addSpecialChar($field);
                else
                    $array[] = $this->addSpecialChar($field);
            }
            $fieldsStr = implode(',', $array);
        }elseif(is_string($fields) && !empty($fields)) {
            $fieldsStr = $this->addSpecialChar($fields);
        }else{
            $fieldsStr = '*';
        }
        return $fieldsStr;
    }
    //value分析
    private function parseValue($value){
        if(is_string($value)){
            $value = '\''.$this->escape_string($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value = $this->escape_string($value[1]);
        }elseif(is_array($value)) {
            $value = array_map(array($this,'parseValue'),$value);
        }elseif(is_null($value)){
            //$value = 'null';
            $value = '\'\'';
        }
        return $value;
    }
    //* 字段和表名添加`
    //* 保证指令中使用关键字不出错 针对mysql
    private function addSpecialChar(&$value){
        if(0 === stripos($this->config['DB_TYPE'],'mysql')){
            $value = trim($value);
            if( false !== strpos($value,' ') || false !== strpos($value,',') || false !== strpos($value,'*') ||  false !== strpos($value,'(') || false !== strpos($value,'.') || false !== strpos($value,'`')) {
                //如果包含* 或者 使用了sql方法 则不作处理
            }else{
                $value = '`'.$value.'`';
            }
        }
        return $value;
    }
    //SQL指令安全过滤
    /*public function escape_string($str){
        if (!get_magic_quotes_gpc()){
            $this->connect();
            return $this->db->escape_string($str);
        }
        return $str;
    }*/
    //为什么把上面那个函数废弃？因为如果魔术函数on时，而$str又不是post或get得来（比如读取文本、数据库），它还是没加反斜线。
    //所以我不管$str是否已经被转义，一律先去除转义，然后再加上转义。这样就避免了二次转义，也避免了遗漏转义。
    public function escape_string($str){
        stripslashes($str);
        $this->connect();
        return $this->db->escape_string($str);
    }
    //输出错误信息
    public function error($str){
        //exit($str);
        return false;
    }
}//类定义结束
?>
<?php
//mysql数据库基类
class mysql {
    public $link;
    public $dbhost;//数据库主机
    public $dbuser;//数据库用户名
    public $dbpw;//数据库密码
    public $dbcharset;//数据库编码
    public $pconnect;//true表示使用永久连接，false表示不适用永久连接，一般不使用永久连接
    public $tablepre;//数据库表前缀
    public $goneaway;//数据库连接失败，重试次数
    //连接数据库
    public function connect($dbhost,$dbuser,$dbpw,$dbname='',$dbcharset='',$pconnect=false,$tablepre='') {
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpw = $dbpw;
        $this->dbname = $dbname;
        $this->dbcharset = $dbcharset;
        $this->pconnect = $pconnect;
        $this->tablepre = $tablepre;
        $this->goneaway = 3;
        if($pconnect){
            if(!$this->link = @mysql_pconnect($dbhost,$dbuser,$dbpw)){
                $this->halt('无法连接到数据库服务器');
            }
        }else{
            if(!$this->link = @mysql_connect($dbhost,$dbuser,$dbpw)){
                $this->halt('无法连接到数据库服务器');
            }
        }
        if($this->version() > '4.1'){
            if($dbcharset){
                mysql_query("SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary",$this->link);
            }
            if($this->version() > '5.0.1'){
                mysql_query("SET sql_mode=''",$this->link);
             }
        }
        if($dbname){
            $this->select_db($dbname);
        }
    }
    //选择数据库
    public function select_db($dbname){
        return mysql_select_db($dbname,$this->link);
    }
    //SQL指令安全过滤
    public function escape_string($str){
        return mysql_real_escape_string($str);
    }
    //启用事务
    public function startTrans(){
       return $this->query('START TRANSACTION');
    }
    //事务提交
    public function commit(){
        return $this->query('COMMIT');
    }
    //事务回滚
    public function rollback(){
        return $this->query('ROLLBACK');
    }
    //查询sql语句
    public function query($sql){
        if(!($query = mysql_query($sql,$this->link))){
            $this->halt('MySQL Query Error', $sql);
        }
        return $query;
    }
    //从结果集中取得一行作为关联数组，或数字数组，或二者兼有
    public function fetch_array($query,$result_type=MYSQL_ASSOC){
        return mysql_fetch_array($query,$result_type);
    }
    //获取上一次插入的id
    public function insert_id(){
        return ($id = mysql_insert_id($this->link)) >= 0 ? $id : mysql_result($this->query("SELECT last_insert_id()"), 0);
    }
    //取得前一次 MySQL 操作所影响的记录行数
    public function affected_rows(){
        return mysql_affected_rows($this->link);
    }
    //取得结果集中行的数目
    public function num_rows($query){
        $query = mysql_num_rows($query);
        return $query;
    }
    //取得结果集中字段的数目
    public function num_fields($query){
        return mysql_num_fields($query);
    }
    //从结果集中取得列信息并作为对象返回
    public function fetch_fields($query){
        return mysql_fetch_field($query);
    }
    //释放结果内存
    public function free_result($query){
        return mysql_free_result($query);
    }
    //获取错误信息详情
    public function error(){
        return (($this->link) ? mysql_error($this->link) : mysql_error());
    }
    //获取错误代码
    public function errno(){
        return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
    }
    //获取版本号
    public function version(){
        return mysql_get_server_info($this->link);
    }
    //关闭数据库连接
    public function close(){
        return mysql_close($this->link);
    }
    //输出错误信息
    public function halt($message = '',$sql = ''){
        $error = $this->error();
        $errorno = $this->errno();
        if($errorno == 2006 && $this->goneaway-- > 0){
            $this->connect($this->dbhost,$this->dbuser,$this->dbpw,$this->dbname,$this->dbcharset,$this->pconnect,$this->tablepre);
            $this->query($sql);
        }else{
           /*header("Content-Type:text/html; charset=utf-8");
            $str= " <b>出错</b>: $message<br>
                    <b>SQL</b>: $sql<br>
                    <b>错误详情</b>: $error<br>
                    <b>错误代码</b>:$errorno<br>";
            exit($str);*/
            return false;
        }
    }
}
?>