<?php
class threadController extends Controller
{
    public function __construct(){
        parent::__construct();
    }
    public function view($thread_id,$last_id,$direction){
        $thread_id=intval($thread_id);
        $last_id=intval($last_id);
        $direction=intval($direction);
        $thread_id<1&&exit;
        $thread_info=$this->model->getThreadInfoById($thread_id);
        $thread_info['uid']==''&&parent::err404();//thread is deleted
        if($last_id==0){
            $this->model->incrThreadAttr($thread_id,'view_num');
            $this->model->setThreadHotRatio($thread_id,$thread_info['view_num']+1,$thread_info['reply_num'],$thread_info['vote_num'],$thread_info['Ascores'],$thread_info['ctime'],$thread_info['update_time']);
        }
        if($this->uid){
            if($this->model->_hasVote($thread_id,$this->uid)){
                $this->set('vote',$this->model->getVoteType($thread_id,$this->uid));
            }
        }
        $reply=$thread_info['reply_num']>0?$this->model('reply')->getReplyList($thread_id,$last_id,false,$direction):array();
        $page=pagination($reply,$direction,$last_id,$this->config['reply_per_page']);
        $prev=$page['prev']!=''?url('thread/view',array('thread_id'=>$thread_id,'last_id'=>$page['prev'],'direction'=>0)):'';
        $next=$page['next']!=''?url('thread/view',array('thread_id'=>$thread_id,'last_id'=>$page['next'],'direction'=>1)):'';

        $this->set('reply',$page['arr']);
        $this->set('prev',$prev);
        $this->set('next',$next);
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" href="'.url('main/category',array('category_id'=>$thread_info['category_id'],'last_id'=>0,'direction'=>1)).'">
                                        <span itemprop="name">'.$this->config['category'][$thread_info['category_id']].'</span>
                                    </a>
                                    <meta itemprop="position" content="2" />
                                </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" href="'.url('thread/view',array('thread_id'=>$thread_id,'last_id'=>0,'direction'=>1)).'">
                                        <h1 itemprop="name">'.$thread_info['thread_title'].'</h1>
                                    </a>
                                    <meta itemprop="position" content="3" />
                                </li>');
        $this->set('keywords',str_replace(' ',',',$thread_info['keywords']));
        $this->set('description','帖子：'.$thread_info['thread_title'].'回复数：'.$thread_info['reply_num'].'，位于分类：'.$this->config['category'][$thread_info['category_id']]);
        $this->set('is_collect',$this->uid&&$this->model->isCollect($thread_id,$this->uid));
        $this->set('avatar',get_avatar($this->model('user')->getAttrByUid($thread_info['uid'],'small_avatar'),'small'));
        $this->set('thread',$thread_info);
        $this->set('title',$thread_info['thread_title'].' | '.$this->config['category'][$thread_info['category_id']].' | '.$this->config['shortname']);
        $this->render('thread');
    }
    public function postThread(){
        parent::ajaxNeedLogin();
        $thread_title=x($_POST['thread_title']);
        $category_id=intval($_POST['thread_category_id']);
        $thread_content=x(htmlspecialchars($_POST['thread_content'], ENT_QUOTES, 'UTF-8'));
        if($thread_title==''){
            $callback=array(
                'status'=>false,
                'msg'=>'请输入帖子标题哟~'
            );
            echo json_encode($callback);
        }elseif(!array_key_exists($category_id,$this->config['category'])){
            $callback=array(
                'status'=>false,
                'msg'=>'请选择帖子分类哟~'
            );
            echo json_encode($callback);
        }elseif($thread_content==''){
            $callback=array(
                'status'=>false,
                'msg'=>'请输入帖子具体内容哟~'
            );
            echo json_encode($callback);
        }else{
            $result=$this->model->addThread($thread_title,$category_id,$thread_content,$this->uid,$this->username);
            if($result['status']){
                $callback=array(
                    'status'=>true,
                    'msg'=>str_replace("\\/", "/",url('thread/view',array('category_id'=>$category_id,'thread_id'=>$result['msg'],'last_id'=>0,'direction'=>1)))
                );
                echo json_encode($callback);
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>$result['msg']
                );
                echo json_encode($callback);
            }
        }
    }
    public function collect(){
        parent::ajaxNeedLogin();
        $thread_id=intval($_POST['thread_id']);
        $type=intval($_POST['type']);
        if($thread_id>0){
            if($type==0){
                echo json_encode($this->model->addCollect($thread_id,$this->uid));
            }else{
                echo json_encode($this->model->removeCollect($thread_id,$this->uid));
            }
        }
    }
    public function threadVote(){
        parent::ajaxNeedLogin();
        $thread_id=intval($_POST['thread_id']);
        $vote_type=intval($_POST['vote_type'])==1?1:2;
        if($thread_id>0){
            $callback=$this->model->threadVote($thread_id,$this->uid,$vote_type);
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'提交了非法参数，请重试'
            );
        }
        echo json_encode($callback);
    }
    public function delThread(){
        parent::ajaxNeedLogin();
        $thread_id=intval($_POST['thread_id']);
        if($thread_id>0){
            if($this->uid==1){
                if($this->model->delThread($thread_id)){
                    $callback=array(
                        'status'=>true
                    );
                }else{
                    $callback=array(
                        'status'=>false,
                        'msg'=>'内部错误，请稍后重试'
                    );
                }
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
    public function delReply(){
        parent::ajaxNeedLogin();
        $reply_id=x($_POST['reply_id']);
        if($reply_id!=''){
            if($this->uid==1){
                $this->model('reply')->setReplyAttr($reply_id,'content','<i>该回复因不友善内容被删除</i>');
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
    public function postReply(){
        parent::ajaxNeedLogin();
        $thread_id=intval($_POST['thread_id']);
        //$content=x(htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8'));
        $content=x($_POST['content']);
        $parent_id=x($_POST['parent_id']);
        if($thread_id<1||$content==''){
            $callback=array(
                'status'=>false,
                'msg'=>'内容或帖子id不能为空'
            );
            echo json_encode($callback);
        }else{
            $result=$this->model('reply')->postReply($thread_id,$content,$this->uid,$parent_id);
            echo json_encode($result);
        }
    }
    public function setstate(){
        parent::needLogin();
        $this->model('msg')->setAsRead(x($_GET['key']),$this->uid);
        header('location:'.$_GET['url']);
    }
    public function replyVote(){
        parent::ajaxNeedLogin();
        $reply_id=x($_POST['reply_id']);
        $type=intval($_POST['type']);
        if($reply_id==''||$type<1){
            echo json_encode(array(
                'status'=>false,
                'msg'=>'参数错误'
            ));
            exit;
        }
        $reply_model=$this->model('reply');
        $has_vote=$reply_model->hasVote($reply_id,$this->uid);
        if($has_vote>0){
            echo json_encode(array(
                'status'=>false,
                'has_vote'=>$has_vote,
                'msg'=>'您已经'.($has_vote==1?'赞':'踩').'过该回复咯~'
            ));
            exit;
        }
        $reply_info=$reply_model->getReplyInfoById($reply_id);
        if($reply_info['uid']==''){
            echo json_encode(array(
                'status'=>false,
                'msg'=>'此回复不存在'
            ));
            exit;
        }
        $thread_info=$this->model->getThreadInfoById($reply_info['thread_id']);
        if($thread_info['uid']==''){
            echo json_encode(array(
                'status'=>false,
                'msg'=>'该回复的主题帖不存在'
            ));
            exit;
        }
        if($reply_info['uid']==$this->uid){
            echo json_encode(array(
                'status'=>false,
                'msg'=>'不能给自己的回复投票哟~'
            ));
            exit;
        }
        $num=1;
        if($type==1){
            $reply_model->_incrReplyAttr($reply_id,'upvote_num');
            $this->model('user')->incrUserVoteInteraction($this->uid,$reply_info['uid']);
            $this->model('msg')->sendNotification($this->uid,$reply_info['uid'],$reply_info['thread_id'],'',$reply_id,'赞了您的回复');//only upvote we send notification.Also, you can custom this as you like
        }else{
            $num=-1;
            $reply_model->_incrReplyAttr($reply_id,'downvote_num');
        }
        $this->model->incrThreadAttr($reply_info['thread_id'],'Ascores',$num);
        $this->model->setThreadHotRatio($reply_info['thread_id'], $thread_info['view_num'],$thread_info['reply_num'],$thread_info['vote_num'],$thread_info['Ascores']+$num,$thread_info['ctime'],$thread_info['update_time']);
        $reply_model->addVote($reply_id,$this->uid,$type);
        echo json_encode(array(
            'status'=>true
        ));
    }
    public function wordSegment(){
        $sentence=x($_POST['sentence']);
        if($sentence!=''){
            $segment=devide_word($sentence);
            $segment=trim($segment);
            if($segment!=''){
                echo json_encode(array(
                    'status'=>true,
                    'url'=>url('thread/search',array('segment'=>$segment,'last_id'=>0,'direction'=>1))
                ));
            }else{
                echo json_encode(array(
                    'status'=>$segment
                ));
            }
        }
    }
    public function search($segment,$last_id,$direction){
        $segment=urldecode(x($segment));
        $last_id=intval($last_id);
        $direction=intval($direction);
        if($segment!=''){
            $thread_ids=$this->model->getSearchThreadIds($segment,$last_id,$direction);
            $thread_info=$this->model->getThreadInfoById($thread_ids,false);
            $thread_result=array_combine($thread_ids,$thread_info);
            $thread=pagination($thread_result,$direction,$last_id,$this->config['thread_per_page']);
            $prev=$thread['prev']!=''?url('thread/search',array('segment'=>$segment,'last_id'=>$thread['prev'],'direction'=>0)):'';
            $next=$thread['next']!=''?url('thread/search',array('segment'=>$segment,'last_id'=>$thread['next'],'direction'=>1)):'';
            $keywords=str_replace(' ',',',$segment);
            $this->set('thread_list',$thread['arr']);
            $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" rel="bookmark" href="'.url('thread/search',array('segment'=>$segment,'last_id'=>$last_id,'direction'=>$direction)).'">
                                        <h1 itemprop="name">关键词“'.$keywords.'”的搜索结果</h1>
                                    </a>
                                    <meta itemprop="position" content="2" />
                                </li>');
            $this->set('title','关键词“'.$keywords.'”的搜索结果 | '.$this->config['shortname']);
            $this->set('prev',$prev);
            $this->set('next',$next);
            $this->render('index');
        }
    }
}
?>