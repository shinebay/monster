<?php
class userController extends Controller{
    public function __construct(){
        parent::__construct();
    }
    public function searchUser(){
        $words=x($_GET['query']);
        if($words!=''){
            $result=$this->model->searchUser($words);
            $callback=array();
            foreach($result as $k=>$v){
                $callback[$k]['value']=$v['username'];
                $callback[$k]['data']=$v['uid'];
            }
            echo json_encode(array('suggestions'=>$callback));
        }
    }
    public function avatarHover(){
        $uid=intval($_POST['uid']);
        $callback=array(
            'status'=>false
        );
        if($uid>0){
            $user_info=$this->model->getUserInfoByUid($uid);
            $user['profile_url']=url('user/index',array('uid'=>$user_info['uid']));
            $user['big_avatar']=get_avatar($user_info['big_avatar'],'big');
            $user['username']=$user_info['username'];
            $user['ban_send_msg']=$user_info['ban_send_msg'];
            $user['thread_num']=$user_info['thread_num'];
            $user['reply_num']=$user_info['reply_num'];
            $user['collect_num']=$user_info['collect_num'];
            $user['intro']=$user_info['intro']==''?'该用户暂无自我介绍':$user_info['intro'];
            $user['thread_url']=url('user/thread',array('uid'=>$uid,'last_id'=>0,'direction'=>1));
            $user['collect_url']=url('user/collect',array('uid'=>$uid,'last_id'=>0,'direction'=>1));
            $user['reply_url']=url('user/reply',array('uid'=>$uid,'last_id'=>0,'direction'=>1));
            if($this->uid){
                $vote_interaction=$this->model->getUserVoteInteraction($this->uid,$uid);
                $reply_interaction=$this->model->getUserReplyInteraction($this->uid,$uid);
                $user['vote_interaction']=$vote_interaction==null?0:$vote_interaction;
                $user['reply_interaction']=$reply_interaction==null?0:$reply_interaction;
                $user['send_msg_url']=url('profile/msg');
            }
            $callback=array(
                'status'=>true,
                'user_info'=>$user
            );
        }
        echo json_encode($callback);
    }
    public function index($uid){
        $uid=intval($uid);
        if($uid>0){
            $user_info=$this->model->getUserInfoByUid($uid);
            $this->set('user_info',$user_info);
            if($this->uid&&$this->uid!=$uid){
                $vote_interaction=$this->model->getUserVoteInteraction($this->uid,$uid)==null;
                $reply_interaction=$this->model->getUserReplyInteraction($this->uid,$uid);
                $this->set('vote_interaction',$vote_interaction==null?0:$vote_interaction);
                $this->set('reply_interaction',$reply_interaction==null?0:$vote_interaction);
            }
            $this->set('user_cur','info');
            $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('user/index',array('uid'=>$uid)).'">
                                            <h1 itemprop="name">'.$user_info['username'].'个人主页</h1>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li>');
            $this->set('title',$user_info['username'].'个人主页 | '.$this->config['shortname']);
            $this->render('user_info');
        }
    }
    public function thread($uid,$last_id,$direction){
        $uid=intval($uid);
        if($uid>0) {
            $user_info = $this->model->getUserInfoByUid($uid);
            $this->set('user_info', $user_info);
            if($user_info['ban_view_thread']!=1){
                $thread_list=$this->model('main')->getThreads('user_thread'.$uid,$last_id,$direction);
                $thread=pagination($thread_list,$direction,$last_id,$this->config['thread_per_page']);
                $prev=$thread['prev']!=''?url('user/thread',array('uid'=>$uid,'last_id'=>$thread['prev'],'direction'=>0)):'';
                $next=$thread['next']!=''?url('user/thread',array('uid'=>$uid,'last_id'=>$thread['next'],'direction'=>1)):'';
                $this->set('thread_list',$thread['arr']);
                $this->set('prev',$prev);
                $this->set('next',$next);
            }
            $this->set('title',$user_info['username'].'最近发表的帖子 | '.$user_info['username'].'个人主页 | '.$this->config['shortname']);
            $this->set('user_cur',__FUNCTION__);
            $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('user/index',array('uid'=>$uid)).'">
                                            <span itemprop="name">'.$user_info['username'].'个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('user/thread',array('uid'=>$uid,'last_id'=>0,'direction'=>1)).'">
                                            <h1 itemprop="name">'.$user_info['username'].'最近发表的帖子(共发布了'.$user_info['thread_num'].'篇帖子)</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
        }
        $this->render('user_thread');
    }
    public function collect($uid,$last_id,$direction){
        $uid=intval($uid);
        if($uid>0) {
            $user_info = $this->model->getUserInfoByUid($uid);
            $this->set('user_info', $user_info);
            if($user_info['ban_view_collect']!=1){
                $thread_list=$this->model('main')->getThreads('user_collect'.$uid,$last_id,$direction);
                $thread=pagination($thread_list,$direction,$last_id,$this->config['thread_per_page']);
                $prev=$thread['prev']!=''?url('user/collect',array('uid'=>$uid,'last_id'=>$thread['prev'],'direction'=>0)):'';
                $next=$thread['next']!=''?url('user/collect',array('uid'=>$uid,'last_id'=>$thread['next'],'direction'=>1)):'';
                $this->set('thread_list',$thread['arr']);
                $this->set('prev',$prev);
                $this->set('next',$next);
            }
            $this->set('title',$user_info['username'].'收藏的帖子 | '.$user_info['username'].'个人主页 | '.$this->config['shortname']);
            $this->set('user_cur',__FUNCTION__);
            $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('user/index',array('uid'=>$uid)).'">
                                            <span itemprop="name">'.$user_info['username'].'个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('user/collect',array('uid'=>$uid,'last_id'=>0,'direction'=>1)).'">
                                            <h1 itemprop="name">'.$user_info['username'].'收藏的帖子(共收集了'.$user_info['collect_num'].'篇帖子)</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
        }
        $this->render('user_collect');
    }
    public function reply($uid,$last_id,$direction){
        $uid=intval($uid);
        if($uid>0){
            $last_id=intval($last_id);
            $direction=intval($direction);
            $user_info = $this->model->getUserInfoByUid($uid);
            if($user_info['ban_view_reply']!=1){
                $reply_list=$this->model('reply')->getUserPostReplies($uid,$last_id,$direction);
                $page=pagination($reply_list,$direction,$last_id,$this->config['reply_per_page']);
                $prev=$page['prev']!=''?url('user/reply',array('uid'=>$uid,'last_id'=>$page['prev'],'direction'=>0)):'';
                $next=$page['next']!=''?url('user/reply',array('uid'=>$uid,'last_id'=>$page['next'],'direction'=>1)):'';
                $this->set('reply',$page['arr']);
                $this->set('prev',$prev);
                $this->set('next',$next);
            }
            $this->set('user_info', $user_info);
            $this->set('user_cur',__FUNCTION__);
            $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('user/index',array('uid'=>$uid)).'">
                                            <span itemprop="name">'.$user_info['username'].'个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('user/reply',array('uid'=>$uid,'last_id'=>0,'direction'=>1)).'">
                                            <h1 itemprop="name">'.$user_info['username'].'回复过的帖子</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
            $this->set('title',$user_info['username'].'回复过的帖子 | '.$user_info['username'].'个人主页 | '.$this->config['shortname']);
        }
        $this->render('user_reply');
    }
    public function freezeUser(){
        $uid=intval($_POST['uid']);
        if($uid>0){
            if($this->uid==1){
                $this->model->freezeUser($uid);
                $callback=array(
                    'status'=>true
                );
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'无此操作权限'
                );
            }
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'参数错误'
            );
        }
        echo json_encode($callback);
    }
}
?>