<?php
class profileController extends Controller{
    public function __construct(){
        parent::__construct();
        $this->set('isProfile',true);
        $this->uid&&$this->set('user_info',$this->model('user')->getUserInfoByUid($this->uid));
    }
    public function avatar(){
        parent::needLogin();
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/setting').'">
                                            <span itemprop="name">个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/avatar').'">
                                            <h1 itemprop="name">头像设置</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
        $this->set('title','头像设置 | 个人主页 | '.$this->config['shortname']);
        $this->set('user_cur',__FUNCTION__);
        $this->render('user_avatar');
    }
    public function doAvatar(){
        parent::ajaxNeedLogin();
        $base64_image_content = $_POST['content'];
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = '.'.$result[2];//get image extension
            $img_content= base64_decode(str_replace($result[1], '', $base64_image_content));
            $md5=md5($img_content);
            $dir1=substr($md5,0,2);
            $dir2=substr($md5,2,2);
            if(!is_dir(ROOT_DIR.'imgs')){
                mkdir(ROOT_DIR.'imgs',0755);
            }
            if(!is_dir(ROOT_DIR.'imgs'.DIRECTORY_SEPARATOR.$dir1)){
                mkdir(ROOT_DIR.'imgs'.DIRECTORY_SEPARATOR.$dir1,0755);
            }
            if(!is_dir(ROOT_DIR.'imgs'.DIRECTORY_SEPARATOR.$dir1.DIRECTORY_SEPARATOR.$dir2)){
                mkdir(ROOT_DIR.'imgs'.DIRECTORY_SEPARATOR.$dir1.DIRECTORY_SEPARATOR.$dir2,0755);
            }
            $path='imgs'.DIRECTORY_SEPARATOR.$dir1.DIRECTORY_SEPARATOR.$dir2.DIRECTORY_SEPARATOR;
            $name=uniqid();
            $new_file=ROOT_DIR.$path.$name.$type;
            if (file_put_contents($new_file,$img_content)){
                if(filesize($new_file) * .0009765625<=1024){
                    $size=getimagesize($new_file);
                    if(is_array($size)&&$size[0]>1&&$size[1]>1&&in_array($size['mime'],array('image/jpeg','image/jpg','image/png'))&&$size['mime']!='image/gif'){
                        require(APP_DIR.'thumb'.DIRECTORY_SEPARATOR.'ThumbLib.inc.php');
                        $thumb = PhpThumbFactory::create($new_file);
                        $big_avatar=$this->config['site'].'/'.$path.$name.'_1'.$type;
                        $small_avatar=$this->config['site'].'/'.$path.$name.'_2'.$type;
                        $thumb->resize(120,120)->save(ROOT_DIR.$path.$name.'_1'.$type);
                        $thumb->resize(40,40)->save(ROOT_DIR.$path.$name.'_2'.$type);
                        $this->model('user')->setUserAttrs($this->uid,array('big_avatar'=>$big_avatar,'small_avatar'=>$small_avatar));
                        unlink($new_file);
                        setcookie($this->config['cookie_prefix'].'avatar_small',$small_avatar,0,'/');
                        setcookie($this->config['cookie_prefix'].'avatar_big',$big_avatar,0,'/');
                        $callback=array(
                            'status'=>true
                        );
                    }else{
                        $callback=array(
                            'status'=>false,
                            '图片只支持jpeg jpg png格式的哟'
                        );
                    }
                }else{
                    $callback=array(
                        'status'=>false,
                        'msg'=>'图片必须小于1MB哟'
                    );
                }
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'图片写入错误'
                );
            }
            echo json_encode($callback);
        }
    }
    public function collect($last_id,$direction){
        parent::needLogin();
        $last_id=intval($last_id);
        $direction=intval($direction);
        $thread_list=$this->model('main')->getThreads('user_collect'.$this->uid,$last_id,$direction);
        $thread=pagination($thread_list,$direction,$last_id,$this->config['thread_per_page']);
        $prev=$thread['prev']!=''?url('profile/thread',array('last_id'=>$thread['prev'],'direction'=>0)):'';
        $next=$thread['next']!=''?url('profile/thread',array('last_id'=>$thread['next'],'direction'=>1)):'';
        $this->set('thread_list',$thread['arr']);
        $this->set('prev',$prev);
        $this->set('next',$next);
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/setting').'">
                                            <span itemprop="name">个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/collect',array('last_id'=>0,'direction'=>1)).'">
                                            <h1 itemprop="name">我的收藏(共'.$this->model('user')->getAttrByUid($this->uid,'collect_num').'个帖子)</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
        $this->set('title','我的收藏 | 个人主页 | '.$this->config['shortname']);
        $this->set('user_cur',__FUNCTION__);
        $this->render('user_collect');
    }
    public function thread($last_id,$direction){
        parent::needLogin();
        $last_id=intval($last_id);
        $direction=intval($direction);
        $thread_list=$this->model('main')->getThreads('user_thread'.$this->uid,$last_id,$direction);
        $thread=pagination($thread_list,$direction,$last_id,$this->config['thread_per_page']);
        $prev=$thread['prev']!=''?url('profile/thread',array('last_id'=>$thread['prev'],'direction'=>0)):'';
        $next=$thread['next']!=''?url('profile/thread',array('last_id'=>$thread['next'],'direction'=>1)):'';
        $this->set('thread_list',$thread['arr']);
        $this->set('prev',$prev);
        $this->set('next',$next);
        $this->set('user_cur',__FUNCTION__);
        $this->set('title','我最近发表的帖子 | 个人主页 | '.$this->config['shortname']);
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/setting').'">
                                            <span itemprop="name">个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/thread',array('last_id'=>0,'direction'=>1)).'">
                                            <h1 itemprop="name">我最近发表的帖子</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
        $this->render('user_thread');
    }
    public function reply($last_id,$direction){
        parent::needLogin();
        $last_id=intval($last_id);
        $direction=intval($direction);
        $reply_list=$this->model('reply')->getUserPostReplies($this->uid,$last_id,$direction);
        $page=pagination($reply_list,$direction,$last_id,$this->config['reply_per_page']);
        $prev=$page['prev']!=''?url('profile/reply',array('last_id'=>$page['prev'],'direction'=>0)):'';
        $next=$page['next']!=''?url('profile/reply',array('last_id'=>$page['next'],'direction'=>1)):'';
        $this->set('reply',$page['arr']);
        $this->set('prev',$prev);
        $this->set('next',$next);
        $this->set('user_cur',__FUNCTION__);
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/setting').'">
                                            <span itemprop="name">个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/reply',array('last_id'=>0,'direction'=>1)).'">
                                            <h1 itemprop="name">我回复过的帖子</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
        $this->set('title','我回复过的帖子 | 个人主页 | '.$this->config['shortname']);
        $this->render('user_reply');
    }
    public function notifications($last_id,$direction){
        parent::needLogin();
        $last_id=intval($last_id);
        $direction=intval($direction);
        $notifications_list=$this->model('msg')->getNotifications($last_id,$this->uid,$direction);
        $notifications=pagination($notifications_list,$direction,$last_id,$this->config['reply_per_page']);
        $prev=$notifications['prev']!=''?url('profile/notifications',array('last_id'=>$notifications['prev'],'direction'=>0)):'';
        $next=$notifications['next']!=''?url('profile/notifications',array('last_id'=>$notifications['next'],'direction'=>1)):'';
        $this->set('prev',$prev);
        $this->set('next',$next);
        $this->set('user_cur',__FUNCTION__);
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/setting').'">
                                            <span itemprop="name">个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                  </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/notifications',array('last_id'=>0,'direction'=>1)).'">
                                            <h1 itemprop="name">我的通知信息</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
        $this->set('title','我的通知信息 | 个人主页 | '.$this->config['shortname']);
        $this->set('notifications',$notifications['arr']);
        $this->render('user_notifications');
    }
    public function removeNotification(){
        parent::ajaxNeedLogin();
        $notice_id=x($_POST['notifiction_id']);
        if($this->model('msg')->removeNotification($this->uid,$notice_id)){
            echo json_encode(array(
                'status'=>true
            ));
        }else{
            echo json_encode(array(
                'status'=>false,
                'msg'=>'删除消息失败'
            ));
        }
    }
    public function clearNotifications(){
        parent::ajaxNeedLogin();
        if($this->model('msg')->clearNotifications($this->uid)){
            $this->model('user')->setUserAttr($this->uid,'new_notice',0);
            echo json_encode(array(
                'status'=>true
            ));
        }else{
            echo json_encode(array(
                'status'=>false,
                'msg'=>'清空所有消息失败'
            ));
        }
    }
    public function msg(){
        parent::needLogin();
        $msg_model=$this->model('msg');
        $contact_info=$msg_model->getContactList($this->uid);
        $this->set('contact_totalnum',$msg_model->getContactTotalNum($this->uid));
        $this->set('contact_info',$contact_info);
        $this->set('msg_prefix',$msg_model->msg_prefix);
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/setting').'">
                                            <span itemprop="name">个人主页</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li> › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/msg').'">
                                            <h1 itemprop="name">我的私信(<span id="msg_breadcrumb"></span>)</h1>
                                        </a>
                                        <meta itemprop="position" content="3" />
                                    </li>');
        $this->set('title','我的站内信 | 个人主页 | '.$this->config['shortname']);
        $this->set('user_cur',__FUNCTION__);
        $this->render('user_msg');
    }
    public function sendMsg(){
        parent::ajaxNeedLogin();
        $content=x($_POST['content']);
        $to=x($_POST['to']);
        $last_id=x($_POST['last_msg_id']);
        if($content!=''&&$to!=''){
            $user_model=$this->model('user');
            $to_uid=$user_model->getUidByUsername($to);
            if($to_uid!=$this->uid){
                if(!$to_uid){
                    $callback=array(
                        'status'=>false,
                        'msg'=>'收信人不存在'
                    );
                }else{
                    $ban_send_msg=$user_model->getAttrByUid($to_uid,'ban_send_msg');
                    if($ban_send_msg!=1){
                        $msg_model=$this->model('msg');
                        if(!$msg_model->canSendMsg($this->uid)){
                            $callback=array(
                                'status'=>false,
                                'msg'=>'亲，发送私信太频繁了吧~'
                            );
                        }else{
                            $mid=$msg_model->addMsg($this->uid,$this->username,$to_uid,$content);
                            if($mid!=''){
                                $content_arr=$msg_model->getMsg($last_id,$this->uid,$to_uid);
                                $new_avatar='';
                                if(intval($_POST['is_exist'])==0){
                                    $new_avatar=get_avatar($user_model->getAttrByUid($to_uid,'small_avatar'));
                                }
                                if(count($content_arr)>0){
                                    $callback=array(
                                        'status'=>true,
                                        'content_arr'=>$content_arr,
                                        'new_avatar'=>$new_avatar,
                                        'new_profile_url'=>url('user/index',array('uid'=>$to_uid)),
                                        'new_contact_id'=>$msg_model->msg_prefix.min($this->uid,$to_uid).'_'.max($this->uid,$to_uid)
                                    );
                                }else{
                                    $callback=array(
                                        'status'=>false,
                                        'msg'=>'获取聊天记录失败'
                                    );
                                }
                            }else{
                                $callback=array(
                                    'status'=>false,
                                    'msg'=>'聊天记录添加到数据库失败'
                                );
                            }
                        }
                    }else{
                        $callback=array(
                            'status'=>false,
                            'msg'=>'对方暂不接受私信哟~'
                        );
                    }
                }
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'不能给自己发私信哟~'
                );
            }
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'内容和收信人不能为空'
            );
        }
        echo json_encode($callback);
    }
    public function getLatestMsg(){
        parent::ajaxNeedLogin();
        $msg_model=$this->model('msg');
        $direction=intval($_POST['direction']);
        $last_id=x($_POST['last_id']);
        $username=x($_POST['username']);
        $to_uid=$this->model('user')->getUidByUsername($username);
        if($to_uid>0){
            $limit=max(intval($_POST['unread_num']),15);
            $content_arr=$msg_model->getMsg($last_id,$this->uid,$to_uid,$limit,$direction);
            intval($_POST['unread_num'])>0&&$msg_model->delUserMsgState($this->uid,$username);
            echo json_encode(array(
                'status'=>true,
                'content_arr'=>$content_arr,
                'prev_has_more'=>$msg_model->getMsgTotalNum($this->uid,$to_uid)>count($content_arr)
            ));
        }else{
            echo json_encode(array(
                'status'=>false,
                'msg'=>'私信接收人不存在'
            ));
        }
    }
    public function prevLoad(){
        parent::ajaxNeedLogin();
        $msg_model=$this->model('msg');
        $direction=intval($_POST['direction']);
        $first_id=x($_POST['first_id']);
        $to_uid=$this->model('user')->getUidByUsername(x($_POST['username']));
        if($to_uid>0&&$first_id!=''){
            $content_arr=$msg_model->getMsg($first_id,$this->uid,$to_uid,15,$direction);
            if(count($content_arr)>0){
                $callback=array(
                    'status'=>true,
                    'content_arr'=>$content_arr,
                    'prev_has_more'=>$msg_model->getMsgTotalNum($this->uid,$to_uid)>(count($content_arr)+intval($_POST['current_num']))
                );
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'没有更多的信息了'
                );
            }
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'私信接收人不存在'
            );
        }
        echo json_encode($callback);
    }
    public function refreshMsg(){
        parent::ajaxNeedLogin();
        $msg_model=$this->model('msg');
        $direction=intval($_POST['direction']);
        $last_id=x($_POST['last_id']);
        $username=x($_POST['username']);
        $to_uid=$this->model('user')->getUidByUsername($username);
        if($to_uid>0){
            $content_arr=$msg_model->getMsg($last_id,$this->uid,$to_uid,100,$direction);
            count($content_arr)>0&&$msg_model->delUserMsgState($this->uid,$username);
            echo json_encode(array(
                'status'=>true,
                'content_arr'=>$content_arr
            ));
        }else{
            echo json_encode(array(
                'status'=>false,
                'msg'=>'私信接收人不存在'
            ));
        }
    }
    public function contactLoadMore(){
        parent::ajaxNeedLogin();
        $last_id=x($_POST['contact_last_id']);
        if($last_id!=''){
            $result=$this->model('msg')->getContactList($this->uid,$last_id);
            if(count($result)>0){
                $callback=array(
                    'status'=>true,
                    'content_arr'=>$result
                );
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'没有更多了'
                );
            }
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'提交参数不合法'
            );
        }
        echo json_encode($callback);
    }
    public function delMsg(){
        parent::ajaxNeedLogin();
        $contact_id=x($_POST['contact_id']);
        if($contact_id!=''){
            $msg_model=$this->model('msg');
            if($msg_model->msgIsExisted($this->uid,$contact_id)){
                $result=$msg_model->msgDel($this->uid,$contact_id);
                if($result){
                    $callback=array(
                        'status'=>true
                    );
                }else{
                    $callback=array(
                        'status'=>false,
                        'msg'=>'删除消息失败'
                    );
                }
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'消息组不存在或已被删除'
                );
            }
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'提交的参数错误'
            );
        }
        echo json_encode($callback);
    }
    public function setting(){
        parent::needLogin();
        $this->set('user_cur',__FUNCTION__);
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('profile/setting').'">
                                            <span itemprop="name">个人设置</span>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li>');
        $this->set('title','个人设置 | 个人主页 | '.$this->config['shortname']);
        $this->render('user_setting');
    }
    public function saveProfile(){
        parent::ajaxNeedLogin();
        $name=x($_POST['name']);
        $val=x($_POST['val']);
        $arr=array('intro','website','weibo','dribbble','github','facebook','twitter');
        if($name!=''&&in_array($name,$arr)){
            if($name!='intro'){
                $regex = '/(http|https){1}(:\/\/)?([\da-z-\.]+)\.([a-z]{2,6})([\/\w \.-?&%-=]*)*\/?/';
                if($val!=''&&!preg_match($regex,$val)){
                    echo json_encode(array(
                        'status'=>false,
                        'msg'=>'请输入正确的网址，以http://或https://开头'
                    ));
                    exit;
                }
                $val=cutstr($val,35);
            }else{
                $val=cutstr($val,30);
            }
            $this->model('user')->setUserAttr($this->uid,$name,$val);
            $callback=array(
                'status'=>true
            );
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'提交参数错误'
            );
        }
        echo json_encode($callback);
    }
    public function savePermission(){
        parent::ajaxNeedLogin();
        $name=x($_POST['name']);
        $val=intval($_POST['val']);
        $arr=array('ban_view_thread','ban_view_reply','ban_view_collect','ban_send_msg','inform_without_reply','inform_without_at','inform_without_upvote');
        if($name!=''&&in_array($name,$arr)){
            $this->model('user')->setUserAttr($this->uid,$name,$val);
            $callback=array(
                'status'=>true
            );
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'提交参数错误'
            );
        }
        echo json_encode($callback);
    }
}
?>