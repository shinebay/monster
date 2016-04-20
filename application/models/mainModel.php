<?php
class mainModel extends Model {
    var $thread_prefix;
    public function __construct(){
        parent::__construct();
        $this->thread_prefix=$this->model('thread')->prefix;
    }
    /*
     * @param   int     $last_id
     * @param   int     $direction
     * @param   char    $type(hot_thread,thread,user_collect)
     */
    public function getThreads($type,$last_id,$direction){
        if($direction==1){
            $thread_id_list=$this->ssdb->zrscan($type,$this->thread_prefix.$last_id,'','',$this->config['thread_per_page']+1);
        }else{
            $thread_id_list=$this->ssdb->zscan($type,$this->thread_prefix.$last_id,'','',$this->config['thread_per_page']+1);
        }
        $thread_id_list=array_keys($thread_id_list);
        $thread_info_list=$this->model('thread')->getThreadInfoById($thread_id_list,false);
        $threads=array();
        if(count($thread_id_list)>0){
            $threads=array_combine($thread_id_list,$thread_info_list);
        }
        foreach($threads as $k=>$v){
            if($v['uid']==''){
                unset($threads[$k]);
            }
        }
        return $threads;
    }
    public function getSiteCounter(){
        return array(
            'thread_count'=>$this->ssdb->get('thread_count'),
            'user_count'=>$this->ssdb->get('user_count')
        );
    }
}

?>
