<?php
class userModel extends Model {
    var $prefix='u';
    var $vote_interaction_prefix='v';
    var $reply_interaction_prefix='i';
	public function __construct(){
        parent::__construct();
	}
    /*
     * get username by uid
     * @param   int     $uid
     */
    public function getUsernameByUid($uid){
        $username=$this->ssdb->hget($this->prefix.$uid,'username');
        if($username!=null){
            return $username;
        }else{
            return false;
        }
    }
    /*
     * get uid by username
     * @param   char    $username
     */
    public function getUidByUsername($username){
        $uid=$this->mysql->table('user')->where(array('username'=>$username))->field('uid')->find();
        if(isint($uid['uid'])){
            return $uid['uid'];
        }else{
            return false;
        }
    }
    /*
     * get username attibution by uid, e.g. username email reg_ip ctime
     * @param   int              $uid
     * @param   char or array    $attrname
     */
    public function getAttrByUid($uid,$attrname){
        if(is_array($attrname)){
            $attr=$this->ssdb->multi_hget($this->prefix.$uid,$attrname);
            return $attr;
        }else{
            $attr=$this->ssdb->hget($this->prefix.$uid,$attrname);
            return $attr;
        }
    }
    /*
     * get username attibution by attrname, e.g. email reg_ip ctime, but not including uid
     * @param   char    $username
     * @param   char or array    $attrname
     */
    public function getAttrByUsername($username,$attrname){
        $uid=$this->getUidByUsername($username);
        if(isint($uid)){
            $attr=$this->getAttrByUid($uid,$attrname);
            if($attr!=null){
                return $attr;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /*
     * get user info by uid
     * @param   int     $uid
     */
    public function getUserInfoByUid($uid){
        $userinfo=$this->ssdb->hgetall($this->prefix.$uid);
        if($userinfo!=null){
            return $userinfo;
        }else{
            return false;
        }
    }
    /*
     * get user info by username
     * @param   char    $username
     */
    public function getUserInfoByUsername($username){
        $uid=$this->getUidByUsername($username);
        if(isint($uid)){
            $userinfo=$this->getUserInfoByUid($uid);
            return $userinfo;
        }else{
            return false;
        }
    }
    /*
     * check user has the attrbution e.g. notice msg
     * @param   int     $uid
     * @param   char    $attr
     */
    public function hasAttr($uid,$attr){
        return $this->ssdb->hexists($this->prefix.$uid,$attr);
    }
    /*
     * set user attribution
     * @param       int     $int
     * @param       char    $attr
     * @param       any     $value
     */
    public function setUserAttr($uid,$attr,$value){
        $result=$this->ssdb->hset($this->prefix.$uid,$attr,$value);
        return $result;
    }
    /*
     * @param   int             $uid
     * @param   array           $attr
     */
    public function setUserAttrs($uid,$attr){
        $result=$this->ssdb->multi_hset($this->prefix.$uid,$attr);
        if($result==null){
            return false;
        }else{
            return true;
        }
    }
    /*
     * @param   int     $uid
     */
    public function freezeUser($uid){
        return $this->ssdb->multi_hdel($this->prefix.$uid,array('new_msg','new_notice'));
    }
    /*
     * increase user attibution value e.g. notice msg
     * @param       int     $int
     * @param       char    $attr
     * @param       int     $incr_num
     */
    public function incrUserAttr($uid,$attr,$incr_num=1){
        return $this->ssdb->hincr($this->prefix.$uid,$attr,$incr_num);
    }
    /*
     * @param   char    $words
     */
    public function searchUser($words){
        $where['username'] = array('like','%'.$words.'%');
        return $this->mysql->table('user')->where($where)->field('uid,username')->limit(5)->select();
    }
    /*
     * @param   int     $my_uid
     * @param   int     $hid_uid
     */
    public function getUserVoteInteraction($my_uid,$his_uid){
        return $this->ssdb->get($this->vote_interaction_prefix.$my_uid.'_'.$his_uid);
    }
    /*
     * @param   int     $my_uid
     * @param   int     $hid_uid
     */
    public function getUserReplyInteraction($my_uid,$his_uid){
        return $this->ssdb->get($this->reply_interaction_prefix.$my_uid.'_'.$his_uid);
    }
    /*
     * @param   int     $my_uid
     * @param   int     $hid_uid
     */
    public function incrUserVoteInteraction($my_uid,$his_uid){
        $i=$this->vote_interaction_prefix.$my_uid.'_'.$his_uid;
        $this->ssdb->exists($i)?$this->ssdb->incr($i):$this->ssdb->set($i,1);
    }
    /*
     * @param   int     $my_uid
     * @param   int     $hid_uid
     */
    public function incrUserReplyInteraction($my_uid,$his_uid){
        $i=$this->reply_interaction_prefix.$my_uid.'_'.$his_uid;
        $this->ssdb->exists($i)?$this->ssdb->incr($i):$this->ssdb->set($i,1);
    }
}
?>