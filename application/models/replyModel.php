<?php
class replyModel extends Model {
    var $prefix='r';//SSDB prefix
    var $live_time_prefix='l';//reply live time flag prefix
	public function __construct(){
        parent::__construct();
	}
    /*
     * to prevent frequently add new reply to the same thread.record last reply time by setting a flag
     * @param   int     $thread_id
     * @param   int     $uid
     * 为防止用户频繁的对一个帖子不停回复，我们记录下他最近一次回帖时间，并设立一个有存活值（默认60秒）的标识，过了这个指定时间，这个标识便自动销毁
     */
    public function _addReplyFlag($thread_id,$uid){
        return $this->ssdb->setx($this->live_time_prefix.$thread_id.'_'.$uid,time(),$this->config['reply_live_time']);
    }
    /*
     * to prevent frequently add new reply to the same thread.check whether the flag is destroyed or not
     * @param   int     $thread_id
     * @param   int     $uid
     * 为防止用户频繁对一个帖子不停回复，我们记录了他最后一次回帖的时间标识（flag），现在我们来检测这个标识是否还存活着没有
     */
    public function _checkReplyFlag($thread_id,$uid){
        return $this->ssdb->exists($this->live_time_prefix.$thread_id.'_'.$uid);
    }
    /*
     * increase reply attribution e.g. reply_num upvote_num
     * @param   int     $reply_id
     * @param   char    $attr
     * @param   int     $num
     * 增加（或减少）回复的属性，如回复数、赞同数等等
     */
    public function _incrReplyAttr($reply_id,$attr,$num=1){
        return $this->ssdb->hincr($reply_id,$attr,$num);
    }
    /*
     * @param   int    $reply_id
     * @param   char   $attr
     * @param   char   $value
     */
    public function setReplyAttr($reply_id,$attr,$value){
        $reply_id=isint($reply_id)?$this->prefix.$reply_id:$reply_id;
        return $this->ssdb->hset($reply_id,$attr,$value);
    }
    /*
     * @param   char        $reply_id
     * @param   boolean     $is_show_replyuserinfo
     */
    public function getReplyInfoById($reply_id,$is_show_replyUserInfo=false){
        $reply_info=$this->ssdb->hgetall($reply_id);
        if($is_show_replyUserInfo){
            $reply_info['reply_user_info']=$this->model('user')->getUserInfoByUid($reply_info['uid']);
        }
        return $reply_info;
    }
    /*
     * @param   int     $thread_id
     * @param   char    $content
     * @param   int     $uid
     * @param   char    $parent_id (parent reply id)
     */
    public function postReply($thread_id,$content,$uid,$parent_id){
        $user_model=$this->model('user');
        if(!$this->_checkReplyFlag($thread_id,$uid)){
            $global_last_reply_time=$user_model->getAttrByUid($uid,'last_reply_time');//get user last reply time
            if(time()-$global_last_reply_time<$this->config['global_reply_live_time']){
                $result=array(
                    'status'=>false,
                    'msg'=>'亲，回帖不要太频繁了哟~，再过'.str_replace('前','',friendlyDate(($this->config['global_reply_live_time']-time()+$global_last_reply_time))).'试试吧~'
                );
            }else{
                $thread_model=$this->model('thread');
                $thread_prefix=$thread_model->prefix;
                $thread_info=$thread_model->getThreadInfoById($thread_id);
                if($thread_info['uid']>0){
                    /*$at_users=filter_at($content);
                    $result_at_users=$thread_model->_convert_at_users($at_users);
                    if($result_at_users){
                        $content=strtr($content,$result_at_users);
                    }*/
                    $reply=$this->ssdb->incr('reply_id',1);
                    $parent_reply_uid='';
                    if($reply>0){
                        $parent_reply_id='[]';
                        if($parent_id!=''){
                            $parent_reply_info=$this->getReplyInfoById($parent_id);
                            if($parent_reply_info['uid']>0&&$parent_reply_info['thread_id']==$thread_id){
                                $parent_reply_uid=$parent_reply_info['uid'];
                                $parent_reply_id_arr=json_decode($parent_reply_info['parent_id'],true);
                                array_unshift($parent_reply_id_arr,$parent_id);
                                $parent_reply_id=json_encode($parent_reply_id_arr);
                            }
                        }
                        $time=time();
                        $status=$this->ssdb->multi_hset($this->prefix.$reply,array(
                            'thread_id'=>$thread_id,
                            'content'=>$content,
                            'uid'=>$uid,
                            'username'=>$user_model->getUsernameByUid($uid),
                            'upvote_num'=>0,
                            'downvote_num'=>0,
                            'thread_uid'=>$thread_info['uid'],
                            'parent_id'=>$parent_reply_id,
                            'post_ip'=>getIp(),
                            'ctime'=>$time
                        ));
                        $msg_model=$this->model('msg');
                        if($uid!=$thread_info['uid']){
                            $msg_model->sendNotification($uid,$thread_info['uid'],$thread_id,$this->prefix.$reply,'','回复了您的帖子');
                        }
                        if($parent_reply_uid!=''&&$parent_reply_uid!=$thread_info['uid']&&$parent_reply_uid!=$uid){
                            $msg_model->sendNotification($uid,$parent_reply_uid,$thread_id,$this->prefix.$reply,$parent_id,'在帖子中回复了您');
                        }
                        if(!$status){
                            $result=array(
                                'status'=>false,
                                'msg'=>'插入数据库失败，请稍后重试'
                            );
                        }else{
                            $user_model->setUserAttr($uid,'last_reply_time',$time);
                            $user_model->incrUserAttr($uid,'reply_num');
                            $user_model->incrUserReplyInteraction($uid,$thread_info['uid']);
                            $this->_addReplyFlag($thread_id,$uid);
                            $thread_model->incrThreadAttr($thread_id,'reply_num');
                            $thread_model->setThreadAttrById($thread_id,'update_time',$time);
                            $this->ssdb->zset('user_reply'.$uid,$this->prefix.$reply,$reply);
                            $this->ssdb->zset('thread_reply'.$thread_id,$this->prefix.$reply,$reply);
                            $this->ssdb->zset('category'.$thread_info['category_id'],$thread_model->prefix.$thread_id,$time);
                            $this->ssdb->zset('hot_thread',$thread_prefix.$thread_id,hot($thread_info['view_num'],($thread_info['reply_num']+1),$thread_info['vote_num'],$thread_info['Ascores'],$thread_info['ctime'],$time));
                            $result=array(
                                'status'=>true
                            );
                        }
                    }else{
                        $result=array(
                            'status'=>false,
                            'msg'=>'更新回复id失败，请稍后重试'
                        );
                    }
                }else{
                    $result=array(
                        'status'=>false,
                        'msg'=>'所回复的帖子不存在'
                    );
                }
            }
        }else{
            $result=array(
                'status'=>false,
                'msg'=>'亲，对这个帖子回复太频繁了吧~'
            );
        }
        return $result;
    }
    /*
     * @param   int     $thread_id
     * @param   int     $start
     * @param   boolean $show_threaduid_info(whether show thread post user info or not)
     * 注意，为实现cursor分页，返回的reply list的数组个数会比正常数量多1个（$this->config['reply_per_page']+1）
     */
    public function getReplyList($thread_id,$start,$show_threaduid_info=false,$direction){
        $user_model=$this->model('user');
        if($direction==1){
            $reply_list_keys=$this->ssdb->zscan('thread_reply'.$thread_id,$this->prefix.$start,'','',($this->config['reply_per_page']+1));
        }else{
            $reply_list_keys=$this->ssdb->zrscan('thread_reply'.$thread_id,$this->prefix.$start,'','',($this->config['reply_per_page']+1));
        }
        $reply_list=array();
        foreach($reply_list_keys as $k=>$v){
            $reply_list[$k]=$this->ssdb->hgetall($k);
        }
        if(count($reply_list)>0){
            $reply_list_clone=$reply_list;
            foreach($reply_list as &$v){
                $v['reply_user_info']=$user_model->getUserInfoByUid($v['uid']);
                if($show_threaduid_info){
                    $v['thread_user_info']=$user_model->getUserInfoByUid($v['thread_uid']);
                }
                if($v['parent_id']!='[]'){
                    $parent_reply_info=array();
                    $parent_id_arr=json_decode($v['parent_id'],true);
                    if(count($parent_id_arr)>0){
                        foreach($parent_id_arr as $v1){
                            if(array_key_exists($v1,$reply_list_clone)){
                                $parent_reply_info[$v1]=$reply_list_clone[$v1];
                            }else{
                                $parent_reply_info[$v1]=$this->getReplyInfoById($v1,true);
                                $reply_list_clone[$v1]=$parent_reply_info[$v1];
                            }
                        }
                    }
                    $v['parent_reply_info']=$parent_reply_info;
                }
            }
        }
        unset($reply_list_clone);
        return $reply_list;
    }
    /*
     * @param   int     $uid
     * @param   char    $last_id
     * @param   int     $direction
     */
    public function getUserPostReplies($uid,$last_id,$direction){
        if($direction==1){
            $reply_list_keys=$this->ssdb->zrscan('user_reply'.$uid,$this->prefix.$last_id,'','',($this->config['reply_per_page']+1));
        }else{
            $reply_list_keys=$this->ssdb->zscan('user_reply'.$uid,$this->prefix.$last_id,'','',($this->config['reply_per_page']+1));
        }
        $reply_list=array();
        if(count($reply_list_keys)>0){
            $thread_model=$this->model('thread');
            foreach($reply_list_keys as $k=>$v){
                $reply_info=$this->ssdb->hgetall($k);
                $thread_info=$thread_model->getThreadInfoById($reply_info['thread_id']);
                if($thread_info['uid']>0){
                    $reply_list[$k]=$reply_info;
                    $reply_list[$k]['thread_info']=$thread_info;
                }
            }
        }
        return $reply_list;
    }
    /*
     * check user has vote reply
     * @param   char    $reply_id
     * @param   int     $int
     * @return  boolean or int
     */
    public function hasVote($reply_id,$uid){
        $result=false;
        if($this->ssdb->exists($reply_id.'_'.$uid)){
            $result=$this->ssdb->get($reply_id.'_'.$uid);
        }
        return $result;
    }
    /*
     * @param   char    $reply_id
     * @param   int     $int
     * @return  boolean or int
     */
    public function addVote($reply_id,$uid,$type){
        return $this->ssdb->set($reply_id.'_'.$uid,$type);
    }
}
?>