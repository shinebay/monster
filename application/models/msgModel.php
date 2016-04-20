<?php
class msgModel extends Model {
    var $prefix='n';
    var $msg_prefix='m';
    var $msglist_prefix='l';
    var $queue_prefix='q';
	public function _construct(){
        parent::_construct();
	}
    /*
     * @param    int     $from_uid
     * @param    int     $to_uid
     * @param    int     $thread_id
     * @param    char    $from_reply_id (optional)
     * @param    char    $to_reply_id (optional)
     * @param    char    $msg
     * @return  boolean
     */
    public function sendNotification($from_uid,$to_uid,$thread_id,$from_reply_id,$to_reply_id,$msg){
        $notice_id=$this->ssdb->incr('notice_id',1);
        $result1=$this->ssdb->zset('user_notice'.$to_uid,$this->prefix.$notice_id,time());
        $callback=false;
        if($result1){
            $from_username=$this->model('user')->getUsernameByUid($from_uid);
            $result2=$this->ssdb->set($this->prefix.$notice_id,json_encode(array(
                'status'=>0,
                'from_uid'=>$from_uid,//my uid
                'from_username'=>$from_username,//my username
                'to_uid'=>$to_uid,//his uid
                'thread_id'=>$thread_id,
                'from_reply_id'=>$from_reply_id,//my reply
                'to_reply_id'=>$to_reply_id,//his reply
                'msg'=>$from_username.$msg,
                'ctime'=>time()
            )));
            if($result2){
                $this->model('user')->incrUserAttr($to_uid,'new_notice');
                $callback=true;
            }
        }
        return $callback;
    }
    /*
     * get user notifications
     * @param       int     $last_id
     * @param       int     $uid
     * @return      array
     */
    public function getNotifications($last_id,$uid,$direction){
        if($direction==1){
            $keys=$this->ssdb->zrscan('user_notice'.$uid,$this->prefix.$last_id,'','',$this->config['reply_per_page']+1);
        }else{
            $keys=$this->ssdb->zscan('user_notice'.$uid,$this->prefix.$last_id,'','',$this->config['reply_per_page']+1);
        }
        $notification_keys=array_keys($keys);
        $notifications=array();
        $thread_model=$this->model('thread');
        $reply_model=$this->model('reply');
        if(count($notification_keys)>0){
            foreach($notification_keys as $v){
                $info=json_decode($this->ssdb->get($v),true);
                $info['from_uid_avatar']=$this->model('user')->getAttrByUid($info['from_uid'],'small_avatar');
                if($info['thread_id']>0){
                    $info['thread_info']=$thread_model->getThreadInfoById($info['thread_id']);
                }
                if($info['from_reply_id']!=''){
                    $info['from_reply_info']=$reply_model->getReplyInfoById($info['from_reply_id']);
                }
                if($info['to_reply_id']!=''){
                    $info['to_reply_info']=$reply_model->getReplyInfoById($info['to_reply_id']);
                }
                $notifications[$v]=$info;
            }
        }
        return $notifications;
    }
    /*
     * @param   int     $notice_id
     */
    public function getNotificationInfo($notice_id){
        return json_decode($this->ssdb->get($notice_id),true);
    }
    /*
     * @param   int     $uid
     * @param   char    $notice_id
     */
    public function removeNotification($uid,$notice_id){
        $state=false;
        if($this->ssdb->zdel('user_notice'.$uid,$notice_id)){
            $notification_info=$this->getNotificationInfo($notice_id);
            if($notification_info['status']==0){
                $this->model('user')->incrUserAttr($uid,'new_notice',-1);
            }
            $state=true;
        }
        return $state;
    }
    /*
     * @param   int     $uid
     */
    public function clearNotifications($uid){
        return $this->ssdb->zclear('user_notice'.$uid);
    }
    /*
     * set notification as read state
     * @param   char    $notification
     * @param   int     $uid
     */
    public function setAsRead($notificaton_id,$uid){
        $info=json_decode($this->ssdb->get($notificaton_id),true);
        if($info['to_uid']==$uid&&$info['status']==0){
            $info['status']=1;
            $this->ssdb->set($notificaton_id,json_encode($info));
            $this->ssdb->zset('user_notice'.$uid,$notificaton_id,0);
            $this->model('user')->incrUserAttr($uid,'new_notice',-1);
            //setcookie($this->config['cookie_prefix'].'new_notice',intval($_COOKIE[$this->config['cookie_prefix'].'new_notice'])-1);
        }
    }
    /*
     * add new msg
     * @param   int     $from_uid
     * @param   char    $from_username
     * @param   int     $to_uid
     * @param   char    $content
     */
    public function addMsg($from_uid,$from_username,$to_uid,$content){
        $msg_key=$this->msg_prefix.min($from_uid,$to_uid).'_'.max($from_uid,$to_uid);
        $msg_id=$this->ssdb->incr('msg_id');
        $callback='';
        if($this->ssdb->zset($msg_key,$this->msg_prefix.$msg_id,$msg_id)){
            $result=$this->ssdb->set($this->msg_prefix.$msg_id,json_encode(array(
                'content'=>$content,
                'send_uid'=>$from_uid,
                'ctime'=>time()
            )));
            if($result){
                $this->ssdb->zset($this->msglist_prefix.$from_uid,$msg_key,time());
                $this->ssdb->zset($this->msglist_prefix.$to_uid,$msg_key,time());
                $this->incrUserMsgState($to_uid,$from_username);
                $callback=$this->msg_prefix.'_'.$msg_id;
            }
        }
        return $callback;
    }
    /*
     * @param   int     $uid
     * 防止用户频繁发送私信，在config文件中可自行配置：用户在msg_record_queue次发送私信行为中，平均发送时间低于msg_send_interval_time(秒)则不能发送私信
     */
    public function canSendMsg($uid){
        $queue=$this->queue_prefix.$uid;
        $time=time();
        $state=true;
        if($this->ssdb->qsize($queue)>=$this->config['msg_record_queue']){
            $avg_interval_time=($time-$this->ssdb->qfront($queue))/$this->ssdb->qsize($queue);
            if($avg_interval_time>$this->config['msg_send_interval_time']){
                $state=true;
                $this->ssdb->qpop_front($queue);
                $this->ssdb->qpush_back($queue,$time);
            }else{
                $state=false;
            }
        }else{
            $this->ssdb->qpush_back($queue,$time);
        }
        return $state;
    }
    /*
     * @param   int     $uid
     * @param   char    $msg_username
     */
    public function incrUserMsgState($uid,$msg_username){
        $user_model=$this->model('user');
        $new_msg=json_decode($user_model->getAttrByUid($uid,'new_msg'),true);
        if(array_key_exists($msg_username,(array)$new_msg)){
            $new_msg[$msg_username]++;
        }else{
            $new_msg[$msg_username]=1;
        }
        $user_model->setUserAttr($uid,'new_msg',json_encode($new_msg));
    }
    /*
     * @param   int     $uid
     * @param   char    $msg_username
     */
    public function delUserMsgState($uid,$msg_username){
        $user_model=$this->model('user');
        $new_msg=json_decode($user_model->getAttrByUid($uid,'new_msg'),true);
        if(array_key_exists($msg_username,$new_msg)){
            unset($new_msg[$msg_username]);
        }
        $user_model->setUserAttr($uid,'new_msg',json_encode($new_msg));
    }
    /*
     * get new msg
     * @param   char    $last_id
     * @param   int     $from_uid
     * @param   int     $to_uid
     */
    public function getMsg($last_id,$from_uid,$to_uid,$limit=100,$direction=1){
        $msg_key=$this->msg_prefix.min($from_uid,$to_uid).'_'.max($from_uid,$to_uid);
        if($direction==1){
            $mid_keys=array_keys($this->ssdb->zscan($msg_key,$last_id,'','',$limit));
        }else{
            $mid_keys=array_keys($this->ssdb->zrscan($msg_key,$last_id,'','',$limit));
            $mid_keys=array_reverse($mid_keys);
        }
        $result=array();
        if(count($mid_keys)>0){
            foreach($mid_keys as $v){
                $result[$v]=json_decode($this->ssdb->get($v),true);
            }
        }
        return $result;
    }
    /*
     * @param   int     $uid
     * @param   char    $last_id
     */
    public function getContactList($uid,$last_id=''){
        $last_id=$last_id==''?$this->msglist_prefix.'0':$last_id;
        $contact_list=$this->ssdb->zrscan($this->msglist_prefix.$uid,$last_id,'','',15);
        $contact_info=array();
        if(count($contact_list)>0){
            $user_model=$this->model('user');
            $my_new_msg=(array)json_decode($user_model->getAttrByUid($uid,'new_msg'),true);
            foreach($contact_list as $k=>$v){
                $uids=explode('_',ltrim($k,$this->msg_prefix));
                $another_uid=$uids[0]==$uid?$uids[1]:$uids[0];
                $username=$user_model->getUsernameByUid($another_uid);
                $contact_info[$k]=array(
                    'avatar'=>get_avatar($user_model->getAttrByUid($another_uid,'small_avatar')),
                    'username'=>$username,
                    'uid'=>$another_uid,
                    'unread_num'=>array_key_exists($username,$my_new_msg)?$my_new_msg[$username]:0,
                    'profile_url'=>url('user/index',array('uid'=>$another_uid))
                );
            }
        }
        return $contact_info;
    }
    /*
     * @param   int     $uid
     */
    public function getContactTotalNum($uid){
        return $this->ssdb->zsize($this->msglist_prefix.$uid);
    }
    /*
     * @param   int     $from_uid
     * @param   int     $to_uid
     */
    public function getMsgTotalNum($from_uid,$to_uid){
        $msg_key=$this->msg_prefix.min($from_uid,$to_uid).'_'.max($from_uid,$to_uid);
        return $this->ssdb->zsize($msg_key);
    }
    /*
     * @param    int     $uid
     * @param    char    $contact_id
     */
    public function msgIsExisted($uid,$contact_id){
        return $this->ssdb->zexists($this->msglist_prefix.$uid,$contact_id);
    }
    /*
     * @param    int     $uid
     * @param    char    $contact_id
     */
    public function msgDel($uid,$contact_id){
        $uids=explode('_',ltrim($contact_id,$this->msg_prefix));
        $result=false;
        if(in_array($uid,$uids)){
            $another_uid=$uids[0]==$uid?$uids[1]:$uids[0];
            $this->ssdb->zdel($this->msglist_prefix.$uid,$contact_id);
            $key_start=$this->msg_prefix.'0';
            if(!$this->msgIsExisted($another_uid,$contact_id)){
                while(1){
                    $list=$this->ssdb->zscan($contact_id,$key_start,'','',10);
                    if(!$list){
                        break;
                    }
                    $keys=array_keys($list);
                    $this->ssdb->multi_del($keys);
                    $key_start=end($keys);
                }
                $this->ssdb->zclear($contact_id);
            }
            $result=true;
        }
        return $result;
    }
}
?>