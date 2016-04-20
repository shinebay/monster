<?php
    class Controller {//basic controller
        public $config;
        public $uid=false;
        public $username=false;
        public $model;
        public $pageVars = array();
        public function __construct(){
            global $config;
            $this->config=$config;
            $classname=str_replace('Controller','',get_class($this));
            if(file_exists(APP_DIR .'models'.DIRECTORY_SEPARATOR. strtolower($classname) .'Model.php')){
                $this->model=$this->model($classname);
            }
            $this->set('siteCounter',$this->model('main')->getSiteCounter());
            $cookie=decrypt($_COOKIE[$this->config['cookie_prefix'].'Auth']);
            if(isint($cookie)&&$_COOKIE[$this->config['cookie_prefix'].'username']!=''){
                $this->uid=$cookie;
                $this->username=urldecode($_COOKIE[$this->config['cookie_prefix'].'username']);
                if($_COOKIE[$this->config['cookie_prefix'].'avatar_small']==''){
                    $avatar=$this->model('user')->getAttrByUid($cookie,array('small_avatar','big_avatar'));
                    if($avatar['small_avatar']==''||$avatar['big_avatar']==''){
                        setcookie($this->config['cookie_prefix'].'avatar_big',$this->config['default_big_avatar'],0,'/');
                        setcookie($this->config['cookie_prefix'].'avatar_small',$this->config['default_small_avatar'],0,'/');
                    }else{
                        setcookie($this->config['cookie_prefix'].'avatar_small',$avatar['small_avatar'],0,'/');
                        setcookie($this->config['cookie_prefix'].'avatar_big',$avatar['big_avatar'],0,'/');
                    }
                }
                $new_inform=$this->model('user')->getAttrByUid($cookie,array('new_notice','new_msg'));
                if(count($new_inform)==0&&$_COOKIE[$this->config['cookie_prefix'].'Auth']!=''){//account has been deleted
                    setcookie($this->config['cookie_prefix'].'Auth','',time()-3600,'/');
                    setcookie($this->config['cookie_prefix'].'avatar_big','',time()-3600,'/');
                    setcookie($this->config['cookie_prefix'].'avatar_small','',time()-3600,'/');
                    setcookie($this->config['cookie_prefix'].'user_info','',time()-3600,'/');
                    header('location:'.$this->config['site']);
                }
                $new_inform['new_msg']=json_decode($new_inform['new_msg'],true);
                $this->set('new_inform',$new_inform);
                if($_COOKIE[$this->config['cookie_prefix'].'user_info']==''){
                    $user_attrs=$this->model('user')->getAttrByUid($cookie,array('thread_num','reply_num','collect_num'));
                    setcookie($this->config['cookie_prefix'].'user_info',$user_attrs['thread_num'].'_'.$user_attrs['reply_num'].'_'.$user_attrs['collect_num'],0,'/');
                }
            }
        }
        public function model($name){
            require_once(APP_DIR .'models'.DIRECTORY_SEPARATOR. strtolower($name) .'Model.php');
            $name.='Model';
            $model = new $name;
            return $model;
        }
        public function set($var, $val){
            $this->pageVars[$var] = $val;
        }
        public function render($template){
            $tpl_cache=ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.$this->config['theme'].DIRECTORY_SEPARATOR.$template.'.php';
            $tpl=APP_DIR.'views'.DIRECTORY_SEPARATOR . $this->config['theme'].DIRECTORY_SEPARATOR . strtolower($template).'.php';
            if(!file_exists($tpl_cache)){
                //start to create template cache dir
                if(!is_dir(ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.$this->config['theme'])){
                    mkdir(ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'tpl',0755);
                    mkdir(ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.$this->config['theme'],0755);
                }
                copy($tpl,$tpl_cache);
                $match=true;
                while($match){
                    $tpl_content=file_get_contents($tpl_cache);
                    preg_match_all('/(include|require|include_once|require_once)\((\"|\')(.*?)(\"|\')\)(\s*)\;/',$tpl_content,$matches);
                    $pattern=array();
                    $replace=array();
                    if(count($matches[0])>0){
                        foreach($matches[0] as $k=>$v){
                            $pattern[]='/\<\?php(\s*)'.preg_quote($v).'(\s*)\?\>/';
                            $replace[]=file_get_contents(APP_DIR .'views'.DIRECTORY_SEPARATOR.$this->config['theme'].DIRECTORY_SEPARATOR.$matches[3][$k]);
                        }
                        $tpl_temp=preg_replace($pattern,$replace,$tpl_content);
                        file_put_contents($tpl_cache,$tpl_temp);
                    }else{
                        $match=false;
                    }
                }
            }
            extract($this->pageVars);
            ob_start();
            if(CACHE_ON){
                include($tpl_cache);
            }else{
                include($tpl);
            }
            echo ob_get_clean();
        }
        function ajaxNeedLogin(){
            if(!$this->uid){
                $callback=array(
                    'status'=>false,
                    'msg'=>'登录后才能执行此操作哟~'
                );
                echo json_encode($callback);
                exit;
            }
        }
        function needLogin(){
            if(!$this->uid){
                header('location:'.$this->config['site']);
                exit;
            }
        }
        function err404(){
            header("HTTP/1.1 404 Not Found");
            require_once(APP_DIR . 'views/default/err404.php');
            exit;
        }
    }
?>