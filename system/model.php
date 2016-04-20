<?php
//base model
class Model {
    public $config;
    public $database;
    public $ssdb;
	public function __construct()
	{
		global $config;
        $this->config=$config;
		require_once(APP_DIR.'db'.DIRECTORY_SEPARATOR.'db.class.php');
        include_once(APP_DIR.'db'.DIRECTORY_SEPARATOR.'SSDB.php');
        $this->mysql=new db();
        $this->ssdb = new SimpleSSDB('127.0.0.1',8888);
    }
    public function model($name){
        $model_name=$name.'Model';
        if($model_name!=get_class($this)){
            require_once(APP_DIR .'models'.DIRECTORY_SEPARATOR. strtolower($name) .'Model.php');
            $model = new $model_name;
        }else{
            $model = $this;
        }
        return $model;
    }
}
?>
