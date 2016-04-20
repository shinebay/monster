<?php
/*
 * @param   char    $handler    means:'$controller/$method'
 * @param   array   $arr        replace elements. for example:array('thread_title'=>'sometexthere','thread_id'=>1,'last_id'=>23))
 * this function support url router,if matches your url router.it will return your customed url.
 * if not matches.it will return the default url like:http://example.com/index.php?c=thread&m=index&para=123
 * 此函数支持url路由，如果查询到了对应的路由，则返回你自定义的url形式，如果没命中路由，则返回默认url形式，如：http://example.com/index.php?c=thread&m=index&para=123
 */
function url($handler,$arr=array()){
    global $config;
    $router=array_flip($config['router']);
    $replace=array();
    if($router[$handler]!=''){// if exist in url router
        if(count($arr)>0){
            foreach($arr as $k=>$v){
                preg_match('/^[0-9]*$/',$v,$matches);
                $symbol=$matches[0]!=''?'#':'!';
                $replace['<'.$symbol.$k.'>']=remove_puntuation($v,true);
            }
            return $config['site'].strtr($router[$handler],$replace);
        }else{
            return $config['site'].$router[$handler];
        }
    }else{
        $handler_arr=explode('/',$handler);
        $end='';
        if(count($arr)>0){
            foreach($arr as $k=>$v){
                $end.='&'.$k.'='.$v;
            }
        }
        return $config['site'].'/index.php?c='.$handler_arr[0].'&m='.$handler_arr[1].$end;
    }
}
function get_clean_url($site)
{
    $path_info='/';
    if (! empty($_SERVER['PATH_INFO'])) {
        $path_info = $_SERVER['PATH_INFO'];
    } elseif (! empty($_SERVER['ORIG_PATH_INFO']) && $_SERVER['ORIG_PATH_INFO'] !== '/index.php') {
        $path_info = $_SERVER['ORIG_PATH_INFO'];
    } else {
        if (! empty($_SERVER['REQUEST_URI'])) {
            $path_info = (strpos($_SERVER['REQUEST_URI'], '?') > 0) ? strstr($_SERVER['REQUEST_URI'], '?', true) : $_SERVER['REQUEST_URI'];
        }
    }
    $domain=explode('/',str_replace('http://','',$site));
    $url=str_replace($site,'','http://'.array_shift($domain).$path_info);

    // If the URL looks like http://localhost/index.php/path/to/folder remove /index.php
    if (substr($url, 1, strlen(basename($_SERVER['SCRIPT_NAME']))) == basename($_SERVER['SCRIPT_NAME'])) {
        $url = substr($url, strlen(basename($_SERVER['SCRIPT_NAME'])) + 1);
    }

    // Make sure the URI ends in a /
    $url = rtrim($url, '/') . '/';

    // Replace multiple slashes in a url, such as /my//dir/url
    $url = preg_replace('/\/+/', '/', $url);

    return $url;
}
function route($route)
{
    // Make sure the route ends in a / since all of the URLs will
    $route = rtrim($route, '/') . '/';

    // Custom capture, format: <:var_name|regex>
    //$route = preg_replace('/\<\:(.*?)\|(.*?)\>/', '(?P<\1>\2)', $route);

    // Alphanumeric capture (0-9A-Za-z-_), format: <:var_name>
    //$route = preg_replace('/\<\:(.*?)\>/', '(?P<\1>[A-Za-z0-9\-\_]+)', $route);

    // Numeric capture (0-9), format: <#var_name>
    $route = preg_replace('/\<\#(.*?)\>/', '(?P<\1>[0-9]+)', $route);

    // Wildcard capture (Anything INCLUDING directory separators), format: <*var_name>
    //$route = preg_replace('/\<\*(.*?)\>/', '(?P<\1>.+)', $route);

    // Wildcard capture (Anything EXCLUDING directory separators), format: <!var_name>
    $route = preg_replace('/\<\!(.*?)\>/', '(?P<\1>[^\/]+)', $route);

    // Add the regular expression syntax to make sure we do a full match or no match
    $route = '#^' . $route . '$#';
    return $route;
}
function lang($words){
    global $lang;
    return $lang[$words];
}
function remove_puntuation($string,$keep_space=false){
    if($keep_space){
        return preg_replace("/[[:punct:]]/",'',strip_tags(html_entity_decode(str_replace(array('？','！','￥','（','）','：','‘','’','“','”','《','》','，','…','。','、','nbsp'),'',$string),ENT_QUOTES,'UTF-8')));
    }else{
        return preg_replace('/\s/','',preg_replace("/[[:punct:]]/",'',strip_tags(html_entity_decode(str_replace(array('？','！','￥','（','）','：','‘','’','“','”','《','》','，','…','。','、','nbsp'),'',$string),ENT_QUOTES,'UTF-8'))));

    }
}
function encrypt($txt, $key = null) {
    global $config;
    $key = $config['key'];
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
    $nh = rand ( 0, 64 );
    $ch = $chars [$nh];
    $mdKey = md5 ( $key . $ch );
    $mdKey = substr ( $mdKey, $nh % 8, $nh % 8 + 7 );
    $txt = base64_encode ( $txt );
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for($i = 0; $i < strlen ( $txt ); $i ++) {
        $k = $k == strlen ( $mdKey ) ? 0 : $k;
        $j = ($nh + strpos ( $chars, $txt [$i] ) + ord ( $mdKey [$k ++] )) % 64;
        $tmp .= $chars [$j];
    }
    return $ch . $tmp;
}
function decrypt($txt, $key = null) {
    global $config;
    $key = $config['key'];
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
    $ch = $txt [0];
    $nh = strpos ( $chars, $ch );
    $mdKey = md5 ( $key . $ch );
    $mdKey = substr ( $mdKey, $nh % 8, $nh % 8 + 7 );
    $txt = substr ( $txt, 1 );
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for($i = 0; $i < strlen ( $txt ); $i ++) {
        $k = $k == strlen ( $mdKey ) ? 0 : $k;
        $j = strpos ( $chars, $txt [$i] ) - $nh - ord ( $mdKey [$k ++] );
        while ( $j < 0 )
            $j += 64;
        $tmp .= $chars [$j];
    }
    return base64_decode ( $tmp );
}
function authcode($string, $operation = 'DECODE', $expiry = 0)
{
    global $config;
    $key=$config['key'];
    $ckey_length = 4;
    // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥
    $key = md5($key ? $key : EABAX::getAppInf('KEY'));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('0d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++)
    {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++)
    {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++)
    {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE')
    {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))
        {
            return substr($result, 26);
        }
        else
        {
            return '';
        }
    }
    else
    {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}
function x($data, $htmlentities = 0)
{
    $htmlentities && $data = htmlentities($data, ENT_QUOTES, 'utf-8');
    // Fix &entity\n;
    $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"\\\\]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"\\\\]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"\\\\]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"\\\\]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"\\\\]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"\\\\]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

    do
    {
        // Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    }
    while ($old_data !== $data);

    // we are done...
    $data = filter_remote_img_type($data, FALSE);
    return trim($data);
}

/**
 * 过滤内容中有问题网络图片
 * @author phpseyo<phpseyo@qq.com>
 * @param string $text 过滤文本
 * @param boolean $bbcode 是否为BBCODE类型
 * @return string
 */
function filter_remote_img_type($text, $bbcode = TRUE)
{
    /*$pattern = $bbcode ? "/\[img[^\]]*\]\s*(.*?)+\s*\[\/img\]/is" : "/<img[^>]+src=[\'|\"]([^\'|\"]+)[\'|\"][^>]*[\/]?>/is"*/;
    $pattern = $bbcode ? "/\[img\](.+?)\[\/img\]/is" : "/<img[^>]+src=[\'|\"]([^\'|\"]+)[\'|\"][^>]*[\/]?>/is";
    preg_match_all($pattern, $text, $matches);
    foreach ($matches[1] as $k => $src) {
        $data = get_headers($src);
        $header_str = implode('', $data);
        if (FALSE === strpos($header_str, 'Content-Type: image') || FALSE !== strpos($header_str, 'HTTP/1.1 401') || FALSE !== strpos($header_str, 'HTTP/1.1 404')) {
            $text = str_replace($matches[0][$k], '', $text);
        }
    }
    return $text;
}
/*
 * 密码加密
 * */
function pwd_encode($p)
{
    $pwd=substr(md5($p),6);
    return md5(strrev(md5($pwd)).base64_encode($pwd));
}
function getIp(){
    $ip='未知IP';
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        return is_ip($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:$ip;
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        return is_ip($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$ip;
    }else{
        return is_ip($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:$ip;
    }
}
function is_ip($str){
    $ip=explode('.',$str);
    for($i=0;$i<count($ip);$i++){
        if($ip[$i]>255){
            return false;
        }
    }
    return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/',$str);
}
function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,5}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false)
    {
        if (preg_match($chars, $user_email))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}
function print_arr($arr){
    if(is_array($arr)){
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }else{
        echo $arr;
    }
}
function isURL($URL) {
    $arr=explode('.',$URL);
    if(count($arr)>1){
        return true;
    }else{
        return false;
    }
}
function isint($data){
    if( preg_match("/^\+?[1-9][0-9]*$/",$data)){
        return true;
    }else{
        return false;
    }
}
function upload_url($url){
    global $config;
    require_once(APP_DIR.'tietuku_sdk.php');
    $ttk=new TTKClient($config['tietuku_accessKey'],$config['tietuku_secretKey']);
    if(is_array($url)){
        $count=count($url);
        $res=array();
        $res=$ttk->uploadFromWeb($config['tietuku_album_id'],$url);
    }else{
        $res=$ttk->uploadFromWeb($config['tietuku_album_id'],$url);
    }
    return $res;
}
function upload_file($file){
    global $config;
    require_once(APP_DIR.'tietuku_sdk.php');
    $ttk=new TTKClient($config['tietuku_accessKey'],$config['tietuku_secretKey']);
    $res=$ttk->uploadFile($config['tietuku_album_id'],$file);
    $res_arr=json_decode($res,true);
    return $res_arr['s_url'];
}
function devide_word($sentence,$is_array=false){
    $sentence=urlencode(trim($sentence));
    $result=file_get_contents('http://api.pullword.com/get.php?source='.$sentence.'&param1=0&param2=0');
    if($is_array){
        $arr=explode(' ',trim($result));
        if(count($arr)>0){
            return $arr;
        }else{
            return false;
        }
    }else{
        return trim(strtr($result,array("\n"=>"","\r"=>" ")));
    }
}
function filter_keys($key_arr,$prefix){
    $index=0;
    foreach($key_arr as $k=>$v){
        if(substr($v,0,strlen($prefix))!=$prefix){
            $index=$k;
            break;
        }
    }
    $index>0&&array_splice($key_arr,$index);
    return $key_arr;
}
function get_plain_text($thread_content){
    $content=preg_replace('/<img.*?src=[\\\'| \\\"](.*?(?:[\.gif|\.jpg|\.png]))[\\\'|\\\"].*?[\/]?>/',' [图片附件] ',$thread_content);
    $content=preg_replace('#<a.*?>(.*?)</a>#i','\1',$content);
    return preg_replace('#<pre.*?>[\s\S]+pre>#i',' [代码片段] ',$content);
}

function filter_at($content){
    if(preg_match_all('~\@([.-_\w\d\_\-\x7f-\xff]+)(?:[\r\n\t\s ]+|[\xa1\xa1]+)~',($content . ' '),$at_arr)) {
        if(is_array($at_arr[1]) && count($at_arr[1])) {
            foreach($at_arr[1] as $k=>$v)
            {
                $v = trim($v);
                if('　'==substr($v,-2)) {
                    $v = substr($v,0,-2);
                }
                if($v && strlen($v)<16) {
                    $at_arr[1][$k] = $v;
                }
            }
            return $at_arr[1];
        }
    }
}
function filter_link($text){
    $search = array('|(http://[^ ]+)|', '|(https://[^ ]+)|', '|(www.[^ ]+)|');
    $replace = array('<a href="$1" target="_blank">$1</a>', '<a href="$1" target="_blank">$1</a>', '<a href="http://$1" target="_blank">$1</a>');
    $text = preg_replace($search, $replace, $text);
    return $text;
}
function filter_ubb($Text) {
    //$Text=ereg_replace("\n","<br>",$Text);
    $content=preg_replace("/\[img\](.+?)\[\/img\]/is","<img class=\"attach\" src=\"\\1\">",$Text);
    $content=preg_replace("/\[link\](.+?)\[\/link\]/is","<a rel=\"nofollow\" class=\"link\" target=\"_blank\" href=\"\\1\">\\1</a>",$content);
    $content=preg_replace("/\[code\](.+?)\[\/code\]/is","<pre class=\"code\"><code>\\1</code></pre>",$content);
    preg_match_all("/\[img\](.+?)\[\/img\]/is",$Text,$match);
    return array('content'=>$content,'imgs'=>$match[1]);
}
function second_arr($arr,$word){
    if(is_array($arr)){
        $a=array();
        foreach($arr as $v){
            array_push($a,$v[$word]);
        }
        return $a;
    }else{
        return false;
    }
}
function cutstr($string,$length)
{
    $start=0;
    $chars = $string;
    $i=0;
    $m=0;
    $n=0;
    do{
        if (preg_match ("/[0-9a-zA-Z]/", $chars[$i])){//纯英文
            $m++;
        }
        else {
            $n++;
        }//非英文字节,
        $k = $n/3+$m/2;
        $l = $n/3+$m;//最终截取长度；$l = $n/3+$m*2？
        $i++;
    } while($k < $length);
    $str1 = mb_substr($string,$start,$l,'utf-8');//保证不会出现乱码
    if($str1!=$string){
        $str1=$str1.'......';
    }
    return $str1;
}
function hot($Qviews, $Qanswers, $Qscore, $Ascores, $date_ask, $date_active)
{
    $Qage = (time() - strtotime(gmdate("Y-m-d H:i:s",$date_ask))) / 3600;
    $Qage = round($Qage, 1);

    $Qupdated = (time() - strtotime(gmdate("Y-m-d H:i:s",$date_active))) / 3600;
    $Qupdated = round($Qupdated, 1);

    $dividend = (log10($Qviews)*4) + (($Qanswers * $Qscore)/5) + $Ascores;
    $divisor = pow((($Qage + 1) - ($Qage - $Qupdated)/2), 1.5);

    return round(1000*$dividend/$divisor);
}
function get_avatar($avatar,$size='small'){
    global $config;
    if($avatar==''){
        return $config['default_'.$size.'_avatar'];
    }else{
        return $avatar;
    }
}
function get_number($str){
    return preg_replace('/\D/s','',$str);
}
function replace_link($str){
    $pregstr = "/((http|https):\/\/[A-Za-z0-9_#?.&=\/]+)([\x{4e00}-\x{9fa5}])?(\s)?/u";
    if(preg_match($pregstr,$str,$matchArray)){
        return preg_replace_callback($pregstr,function($n){
            if(preg_match('/i(\d+).piimg.com\/(.*).(jpg|jpeg|webp|png|gif)/',$n[1],$match)){
                return '<a class="img_link" href="'.strtr($n[1],array('s.jpg'=>'.jpg','s.png'=>'.png','s.gif'=>'.gif','s.jpeg'=>'.jpeg','s.webp'=>'.webp')).'"><img src="'.$n[1].'"/></a>';
            }else{
                return '<a class="link" rel="nofollow" target="_blank" href="'.$n[1].'">'.$n[1].'</a>';
            }
        },$str);
    }else{
        return $str;
    }
}
function pagination($arr,$direction,$last_id,$page_list_num){
    if($direction==0){
        $prev_has_more=count(array_keys($arr))==($page_list_num+1);
        $arr=array_slice($arr,0,$page_list_num);
        $arr=array_reverse($arr,true);
    }else{
        $next_has_more=count(array_keys($arr))==($page_list_num+1);
        $arr=array_slice($arr,0,$page_list_num);
    }
    $prev='';
    $next='';
    if(($direction==1&&$last_id>0)||($direction==0&&$prev_has_more)){
        reset($arr);
        $prev=get_number(key($arr));
    }
    if(($direction==1&&$next_has_more)||($direction==0&&$last_id>0)){
        end($arr);
        $next=get_number(key($arr));
    }
    return array(
        'arr'=>$arr,
        'prev'=>$prev,
        'next'=>$next
    );
}
function isMobile(){
    require_once 'Mobile_Detect.php';
    $detect = new Mobile_Detect;
    return $detect->isMobile();
}
function genstr($num)
{
    $str='';
    for($i=0;$i<=$num;$i++)
    {
        $str .= '&#'.rand(19968, 40869).';';
    }
    return mb_convert_encoding($str, "UTF-8", "HTML-ENTITIES");
}



function friendlyDate($timestamp, $time_limit = null, $out_format = 'Y-m-d H:i', $formats = null, $time_now = null)
{
    if ($formats == null)
    {
        $formats = array('YEAR' => '%s年前', 'MONTH' => '%s月前', 'DAY' => '%s天前', 'HOUR' => '%s小时前', 'MINUTE' => '%s分钟前', 'SECOND' => '%s秒前');
    }
    $time_now = $time_now == null ? time() : $time_now;
    $seconds = $time_now - $timestamp;
    if ($seconds == 0)
    {
        $seconds = 1;
    }
    if ($time_limit != null && $seconds > $time_limit)
    {
        return date($out_format, $timestamp);
    }
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);
    $months = floor($days / 30);
    $years = floor($months / 12);
    if ($years > 0)
    {
        $diffFormat = 'YEAR';
    }
    else
    {
        if ($months > 0)
        {
            $diffFormat = 'MONTH';
        }
        else
        {
            if ($days > 0)
            {
                $diffFormat = 'DAY';
            }
            else
            {
                if ($hours > 0)
                {
                    $diffFormat = 'HOUR';
                }
                else
                {
                    $diffFormat = ($minutes > 0) ? 'MINUTE' : 'SECOND';
                }
            }
        }
    }
    $dateDiff = null;
    switch ($diffFormat)
    {
        case 'YEAR' :
            $dateDiff = sprintf($formats[$diffFormat], $years);
            break;
        case 'MONTH' :
            $dateDiff = sprintf($formats[$diffFormat], $months);
            break;
        case 'DAY' :
            $dateDiff = sprintf($formats[$diffFormat], $days);
            break;
        case 'HOUR' :
            $dateDiff = sprintf($formats[$diffFormat], $hours);
            break;
        case 'MINUTE' :
            $dateDiff = sprintf($formats[$diffFormat], $minutes);
            break;
        case 'SECOND' :
            $dateDiff = sprintf($formats[$diffFormat], $seconds);
            break;
    }
    return $dateDiff;
}
?>