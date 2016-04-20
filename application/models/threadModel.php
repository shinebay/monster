<?php
class threadModel extends Model {
    var $prefix='t';//thread prefix
    var $collect_prefix='c';//user collect prefix
	public function __construct(){
        parent::__construct();
	}
    /*
     * @param    char   $thread_title
     * @param    int    $category_id
     * @param    char   $thread_content
     * @param    int    $uid
     * @param    char   $username
     */
    public function addThread($thread_title,$category_id,$thread_content,$uid,$username){
        $user_model=$this->model('user');
        $user_last_add_thread_time=$user_model->getAttrByUid($uid,'last_add_thread_time');
        $interval_time=time()-$user_last_add_thread_time;
        if($interval_time<$this->config['thread_add_interval_time']){
            $callback=array(
                'status'=>false,
                'msg'=>'亲，发帖不要频繁了吧，再过'.str_replace('前','',friendlyDate($interval_time)).'试试吧^_^'
            );
            return $callback;
            exit;
        }
        $words=devide_word($thread_title);
        $thread_id=$this->mysql->table('thread')->data(array('thread_keywords'=>$words))->insert();
        if($thread_id>0){
            /*$at_users=filter_at($thread_content);
            $result_at_users=$this->_convert_at_users($at_users);
            if($result_at_users){
                $thread_content=strtr($thread_content,$result_at_users);
            }*/
            $time=time();
            $result=$this->ssdb->multi_hset($this->prefix.$thread_id,array(
                'thread_id'=>$thread_id,
                'thread_title'=>$thread_title,
                'keywords'=>$words,
                'category_id'=>$category_id,
                'thread_content'=>$thread_content,
                'view_num'=>0,
                'reply_num'=>0,
                'vote_num'=>0,
                'collect_num'=>0,
                'ctime'=>$time,
                'Ascores'=>0,
                'username'=>$username,
                'post_ip'=>getIp(),
                'uid'=>$uid
            ));
            $this->ssdb->set('thread_count',$thread_id);
            $user_model->incrUserAttr($uid,'thread_num');
            if(!$result){
                /*if($result_at_users){
                    foreach($result_at_users as $k=>$v){
                        $to_uid=$user_model->getUidByUsername($k);
                        $this->model('msg')->sendNotice($this->uid,$to_uid,$thread_id,'','在帖子内容中提到了您');
                    }
                }
                $user_model->setUserAttr($uid,'last_thread_add_time',time());
                return $thread_id;*/
                return array(
                    'status'=>false,
                    'msg'=>'数据插入数据库出现了错误╮(╯▽╰)╭'
                );
            }else{
                $this->ssdb->zset('thread',$this->prefix.$thread_id,$thread_id);
                $this->ssdb->zset('user_thread'.$uid,$this->prefix.$thread_id,$thread_id);
                $this->ssdb->zset('category'.$category_id,$this->prefix.$thread_id,$time);
                $this->ssdb->zset('hot_thread',$this->prefix.$thread_id,0);
                return array(
                    'status'=>true,
                    'msg'=>$thread_id
                );
            }
        }else{
            return array(
                'status'=>false,
                'msg'=>'分词出现了错误╮(╯▽╰)╭'
            );
        }
    }
    /*
     * convert post thread content at users to hyperlink
     * @param   array   $at_users
     * 将帖子中@到的人转换为个人主页链接
     */
    public function _convert_at_users($at_users){
        $result_at_users=array();
        if(count($at_users)>0){
            foreach($at_users as $v){
                $uid=$this->model('user')->getUidByUsername($v);
                if($uid){
                    $result_at_users['@'.$v]='<a class="at_users" href="/uid/'.$uid.'">@'.$v.'</a>';
                }
            }
            if(count($result_at_users)>0){
                return $result_at_users;
            }else{
                return false;
            }
        }
    }
    /*
     * @param   int or array    $thread_id
     */
    public function getThreadInfoById($thread_id,$use_prefix=true){
        $prefix=$use_prefix?$this->prefix:'';
        $user_model=$this->model('user');
        $ssdb=$this->ssdb;
        if(is_array($thread_id)){
            $thread_info=array();
            foreach($thread_id as $k=>$v){
                $info=$ssdb->hgetall($prefix.$v);
                $info['user_avatar']=get_avatar($user_model->getAttrByUid($info['uid'],'small_avatar'),'small');
                $thread_info[$k]=$info;
            }
        }else{
            $thread_info=$ssdb->hgetall($prefix.$thread_id);
            $thread_info['user_avatar']=get_avatar($user_model->getAttrByUid($thread_info['uid'],'small_avatar'),'small');
        }
        return $thread_info;
    }
    /*
     * get thread attibution by id e.g. thread_title thread_uid
     * @param   int                 $thread_id
     * @param   char or array       $attr
     * 获取帖子属性，比如：帖子标题或者帖子发布者的uid
     */
    public function getThreadAttrById($thread_id,$attr){
        if(is_array($attr)){
            return $this->ssdb->multi_hget($this->prefix.$thread_id,$attr);
        }else{
            return $this->ssdb->hget($this->prefix.$thread_id,$attr);
        }
    }
    /*
     * @param   int     $thread_id
     * @param   char    $attr
     * @param   char    $value
     */
    public function setThreadAttrById($thread_id,$attr,$value){
        return $this->ssdb->hset($this->prefix.$thread_id,$attr,$value);
    }
    /*
     * increase thread attibution e.g. view_num reply_num vote_num
     * @param   int     $thread_id
     * @param   char    $attr
     * @param   int     $num default=1
     * 增加帖子的属性值，比如增加浏览量数值、回复数值、投票数值等
     */
    public function incrThreadAttr($thread_id,$attr,$num=1){
        return $this->ssdb->hincr($this->prefix.$thread_id,$attr,$num);
    }
    /*
     * @param   int     $thread_id
     * @param   int     $uid
     * @param   int     $vote_type
     */
    public function threadVote($thread_id,$uid,$vote_type){
        if($this->isThreadExist($thread_id)){
            $thread_info=$this->getThreadInfoById($thread_id);
            $thread_uid=$thread_info['uid'];
            if($thread_uid!=$uid&&$thread_uid>0){
                if(!$this->_hasVote($thread_id,$uid)){
                    $result=$this->ssdb->set($this->prefix.$thread_id.'_'.$uid,$vote_type);
                    if(!$result){
                        $callback=array(
                            'status'=>false,
                            'msg'=>'写入数据错误，请稍后重试'
                        );
                    }else{
                        $num=$vote_type==1?1:-1;
                        $this->incrThreadAttr($thread_id,'vote_num',$num);
                        $num==1&&$this->model('msg')->sendNotification($uid,$thread_uid,$thread_id,'','','赞了您的帖子').$this->model('user')->incrUserVoteInteraction($uid,$thread_uid);
                        $this->setThreadHotRatio($thread_id, $thread_info['view_num'],$thread_info['reply_num'],$thread_info['vote_num']+$num,$thread_info['Ascores'],$thread_info['ctime'],$thread_info['update_time']);
                        $callback=array(
                            'status'=>true
                        );
                    }
                }else{
                    $callback=array(
                        'status'=>false,
                        'msg'=>'您已投过票啦'
                    );
                }
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'不能给自己投票哟'
                );
            }
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'该帖子不存在哟'
            );
        }
        return $callback;
    }
    /*
     * @param   int     $thread_id
     */
    public function delThread($thread_id){
        $thread_attrs=$this->getThreadAttrById($thread_id,array('uid','category_id'));
        $this->ssdb->hclear($this->prefix.$thread_id);
        $this->ssdb->zclear('thread_reply'.$thread_id);
        $this->ssdb->zdel('thread',$this->prefix.$thread_id);
        $this->ssdb->zdel('hot_thread',$this->prefix.$thread_id);
        $this->ssdb->zdel('user_thread'.$thread_attrs['uid'],$this->prefix.$thread_id);
        $this->ssdb->zdel('category'.$thread_attrs['category_id'],$this->prefix.$thread_id);
        return true;
    }
    /*
     * @param   int     $thread_id
     * @param   int     $uid
     * @return  boolean whether user has vote the thread or not
     * 返回用户是否对thread_id投过票了没有的布尔值
     */
    public function _hasVote($thread_id,$uid){
        return $this->ssdb->exists($this->prefix.$thread_id.'_'.$uid);
    }
    /*
     * get user vote type,whether upvote or downvote
     * @param   int     $thread_id
     * @param   int     $uid
     * 获取用户投票属性，比如是赞成票还是反对票
     */
    public function getVoteType($thread_id,$uid){
        return $this->ssdb->get($this->prefix.$thread_id.'_'.$uid);
    }
    /*
     * @param   int     $thread_id
     * @param   int     $uid
     */
    public function addCollect($thread_id,$uid){
        if(!$this->isThreadExist($thread_id)){
            return array(
                'status'=>false,
                'msg'=>'要收藏的帖子不存在哟~'
            );
        }elseif($this->isCollect($thread_id,$uid)){
            return array(
                'status'=>false,
                'msg'=>'你已经收藏该贴啦~'
            );
        }else{
            $this->ssdb->zset('user_collect'.$uid,$this->prefix.$thread_id,time());
            $this->incrThreadAttr($thread_id,'collect_num');
            $this->model('user')->incrUserAttr($uid,'collect_num');
            $thread_uid=$this->getThreadAttrById($thread_id,'uid');
            $thread_uid!=$uid&&$this->model('msg')->sendNotification($uid,$thread_uid,$thread_id,'','','收藏了您的帖子');
            return array(
                'status'=>true
            );
        }
    }
    /*
     * @param   int     $thread_id
     * @param   int     $uid
     */
    public function removeCollect($thread_id,$uid){
        if(!$this->isThreadExist($thread_id)){
            return array(
                'status'=>false,
                'msg'=>'要收藏的帖子不存在哟~'
            );
        }elseif(!$this->isCollect($thread_id,$uid)){
            return array(
                'status'=>false,
                'msg'=>'你之前就没收藏过该帖子哟~'
            );
        }else{
            $this->ssdb->zdel('user_collect'.$uid,$this->prefix.$thread_id);
            $this->incrThreadAttr($thread_id,'collect_num',-1);
            $this->model('user')->incrUserAttr($uid,'collect_num',-1);
            return array(
                'status'=>true
            );
        }
    }
    /*
     * @param   int     $thread_id
     * @param   int     $uid
     * @return  boolean
     */
    public function isCollect($thread_id,$uid){
        return $this->ssdb->zexists('user_collect'.$uid,$this->prefix.$thread_id);
    }
    /*
     * check thread is existed or not
     * @param   int     $thread_id
     * @return  boolean
     */
    public function isThreadExist($thread_id){
        return $this->ssdb->zexists('thread',$this->prefix.$thread_id);
    }
    /*
     * @param   int     $uid
     */
    public function getUserThreadCount($uid){
        return $this->ssdb->zsize('user_thread'.$uid);
    }
    /*
     * @param   char    $segment
     */
    public function getSearchThreadIds($segment,$last_id,$direction){
        if($direction==1){
            $result=$this->mysql->table('thread')->query('SELECT thread_id FROM `'.$this->config['db']['db_prefix'].'thread` WHERE MATCH (thread_keywords) AGAINST ("'.$segment.' IN BOOLEAN MODE") AND thread_id>'.$last_id.' ORDER BY `thread_id` ASC LIMIT '.($this->config['thread_per_page']+1));
        }else{
            $result=$this->mysql->table('thread')->query('SELECT thread_id FROM `'.$this->config['db']['db_prefix'].'thread` WHERE MATCH (thread_keywords) AGAINST ("'.$segment.' IN BOOLEAN MODE") AND thread_id<'.$last_id.' ORDER BY `thread_id` DESC LIMIT '.($this->config['thread_per_page']+1));
        }
        $arr=second_arr($result,'thread_id');
        return array_map(function($v){return 't'.$v;},$arr);
    }
    /*
     * @param   int     $thread_id
     * @param   array   $array
     */
    public function setThreadHotRatio($thread_id,$Qviews, $Qanswers, $Qscore, $Ascores, $date_ask, $date_active){
        $this->ssdb->zset('hot_thread',$this->prefix.$thread_id,hot($Qviews, $Qanswers, $Qscore, $Ascores, $date_ask, $date_active));
    }
}
?>