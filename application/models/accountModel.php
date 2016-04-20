<?php
class accountModel extends Model {
    public function __construct(){
        parent::__construct();
    }
    /*
     * @param   char    $email
     * @param   char    $pwd
     * @return  array
     */
	public function checkUser($email,$pwd){
		return $this->mysql->table('user')->where(array('email'=>$email,'pwd'=>pwd_encode($pwd)))->field('uid,username')->find();
	}
    public function userIsExist($attrname,$attr){
        $count=$this->mysql->table('user')->where(array($attrname=>$attr))->count();
        return $count>0?true:false;
    }
    public function addUser($username,$email,$pwd){
        $uid=$this->mysql->table('user')->data(array('username'=>$username,'email'=>$email,'pwd'=>pwd_encode($pwd)))->insert();
        if($uid>0){
            $this->ssdb->multi_hset($this->model('user')->prefix.$uid,
                array(
                    'uid'=>$uid,
                    'username'=>$username,
                    'email'=>   $email,
                    'pwd'=>     pwd_encode($pwd),
                    'reg_ip'=>  getIp(),
                    'last_reply_time'=>0,
                    'last_thread_add_time'=>0,
                    'last_msg_send_time'=>0,
                    'new_notice'=>0,
                    'new_msg'=>'',
                    'small_avatar'=>'',
                    'big_avatar'=>'',
                    'collect_num'=>0,
                    'reply_num'=>0,
                    'thread_num'=>0,
                    'ctime'=>time()
                ));
            $this->ssdb->set('user_count',$uid);
            return $uid;
        }else{
            return false;
        }
    }
}

?>
