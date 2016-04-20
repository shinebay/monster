<?php
//error_reporting(0);
define('ROOT_DIR', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('APP_DIR', ROOT_DIR .'application'.DIRECTORY_SEPARATOR);
define('CACHE_ON',false);
$all=ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'all.php';
if(!file_exists($all)){
    mkdir(ROOT_DIR.'runtime',0755);
    touch($all);
    $files=glob(ROOT_DIR.'system'.DIRECTORY_SEPARATOR.'*.php');
    foreach($files as $file){
        file_put_contents($all, file_get_contents($file),FILE_APPEND);
    }
};
if(!CACHE_ON){
    include(ROOT_DIR.'system'.DIRECTORY_SEPARATOR.'config.php');
    include(ROOT_DIR.'system'.DIRECTORY_SEPARATOR.'common.php');
    include(ROOT_DIR.'system'.DIRECTORY_SEPARATOR.'controller.php');
    include(ROOT_DIR.'system'.DIRECTORY_SEPARATOR.'model.php');
}else{
    include(ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'all.php');
}
global $config;
$regex_matches = array();
if($_GET['c']!=''&&$_GET['m']!=''){
    $handler[0]=$_GET['c'];
    $handler[1]=$_GET['m'];
}else{
    $url_clean=get_clean_url($config['site']);
    foreach ($config['router'] as $pattern => $handler_name) {
        if (preg_match(route($pattern), $url_clean, $matches)) {
            $discovered_handler = $handler_name;
            $regex_matches = $matches;
            break;
        }
    }
    $handler=explode('/',$discovered_handler);
}
$handler_instance = null;
$controller=$handler[0].'Controller';
$path = APP_DIR . 'controllers'.DIRECTORY_SEPARATOR. $controller .'.php';
file_exists($path)?require_once($path):header("HTTP/1.1 404 Not Found").require_once(APP_DIR . 'views/default/err404.php');
$handler_instance=new $controller();
foreach($regex_matches as $k=>$v){
    if(is_int($k)){
        unset($regex_matches[$k]);
    }
}
method_exists($controller,$handler[1])&&call_user_func_array(array($handler_instance,$handler[1]), $regex_matches);
?>