<?php
class mainController extends Controller
{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $thread_list=$this->model->getThreads('hot_thread',0,1);
        $thread=pagination($thread_list,1,0,$this->config['thread_per_page']);
        $this->set('thread_list',$thread['arr']);
        $next=$thread['next']!=''?url('main/threads',array('type'=>'hot','last_id'=>$thread['next'],'direction'=>1)):'';
        $this->set('prev','');
        $this->set('next',$next);
        $this->set('title','热门帖子 | '.$this->config['shortname']);
        $this->set('navbar_cur','hot');
        $this->render('index');
    }
    public function threads($type,$last_id,$direction){
        /*$thread_model=$this->model('thread');
        $reply_model=$this->model('reply');
        for($i=0;$i<3;$i++){
            $rand_uid=rand(2,4);
            $u_name=array('abc','def','ghi');
            $t=$thread_model->addThread(genstr(rand(15,30)),rand(1,7),genstr(rand(20,50)),$rand_uid,$u_name[$rand_uid]);
            if($t['status']){
                $thread_id=$t['msg'];
                $reply_model->postReply($thread_id,genstr(rand(10,30)),rand(2,4));
            }
        }*/
        $type=x($type);
        $keys=array('hot'=>'热门','latest'=>'最新');
        !array_key_exists($type,$keys)&&exit;
        $last_id=intval($last_id);
        $direction=intval($direction);
        $convert_keys=array('hot'=>'hot_thread','latest'=>'thread');
        $thread_list=$this->model->getThreads($convert_keys[$type],$last_id,$direction);
        $thread=pagination($thread_list,$direction,$last_id,$this->config['thread_per_page']);
        $prev=$thread['prev']!=''?url('main/threads',array('type'=>$type,'last_id'=>$thread['prev'],'direction'=>0)):'';
        $next=$thread['next']!=''?url('main/threads',array('type'=>$type,'last_id'=>$thread['next'],'direction'=>1)):'';
        $this->set('thread_list',$thread['arr']);
        $this->set('prev',$prev);
        $this->set('next',$next);
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" rel="bookmark" href="'.url('main/threads',array('type'=>$type,'last_id'=>0,'direction'=>1)).'">
                                        <h1 itemprop="name">'.$keys[$type].'帖子</h1>
                                    </a>
                                    <meta itemprop="position" content="2" />
                                </li>');
        $this->set('keywords',$type.' 帖子');
        $this->set('title',$keys[$type].'帖子 | '.$this->config['shortname']);
        $this->set('navbar_cur',$type);
        $this->render('index');
    }
    public function category($category_id,$last_id,$direction){
        $category_id=intval($category_id);
        !array_key_exists($category_id,$this->config['category'])&&exit;
        $category=$this->config['category'][$category_id];
        $last_id=intval($last_id);
        $direction=intval($direction);
        $thread_list=$this->model->getThreads('category'.$category_id,$last_id,$direction);
        $thread=pagination($thread_list,$direction,$last_id,$this->config['thread_per_page']);
        $prev=$thread['prev']!=''?url('main/category',array('category'=>$category,'last_id'=>$thread['prev'],'direction'=>0)):'';
        $next=$thread['next']!=''?url('main/category',array('category'=>$category,'last_id'=>$thread['next'],'direction'=>1)):'';
        $this->set('thread_list',$thread['arr']);
        $this->set('prev',$prev);
        $this->set('next',$next);
        $this->set('category',$category);
        $this->set('navbar_cur','latest');
        $this->set('breadcrumb',' › <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" rel="bookmark" href="'.url('main/category',array('category_id'=>$category_id,'last_id'=>0,'direction'=>1)).'">
                                            <h1 itemprop="name">'.$category.'</h1>
                                        </a>
                                        <meta itemprop="position" content="2" />
                                    </li>');
        $this->set('title',$category.'分类下最新帖子 | '.$this->config['shortname']);
        $this->set('keywords',$category);
        $this->set('description',$category.'分类下最新帖子');
        $this->render('index');
    }
    public function pubUpload(){
        parent::ajaxNeedLogin();
        $image_size=getimagesize($_FILES['jUploaderFile']['tmp_name']);
        if($image_size[0]>0&&$image_size[1]>0){//check if is image
            $ext=pathinfo($_FILES['jUploaderFile']['name'],PATHINFO_EXTENSION);//get image extension
            $upload_name=ROOT_DIR.'public'.DIRECTORY_SEPARATOR.'temp_upload_dir'.DIRECTORY_SEPARATOR.uniqid().'_'.$this->uid.'.'.$ext;
            if(move_uploaded_file($_FILES['jUploaderFile']['tmp_name'],$upload_name)){
                $image=upload_file($upload_name);// upload to tietuku.cn
                unlink($upload_name);//delete temp uploaded image
                if(strstr($image,'piimg.com')){//check callback result is an image or not
                    $callback=array(
                        'status'=>true,
                        'msg'=>$image
                    );
                }else{
                    $callback=array(
                        'status'=>false,
                        'msg'=>'上传到贴图库失败，请稍后重试'
                    );
                }
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'内部错误，请稍后重试'
                );
            }
        }else{
            $callback=array(
                'status'=>false,
                'msg'=>'不是标准的图片格式'
            );
        }
        echo json_encode($callback);
    }
}
?>
