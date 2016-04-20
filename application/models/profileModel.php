<?php
class profileModel extends Model {
    var $thread_prefix;
    public function __construct(){
        parent::__construct();
        $this->thread_prefix=$this->model('thread')->prefix;
    }
}

?>
