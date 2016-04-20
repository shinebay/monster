<?php
class accountController extends Controller
{
    public function __construct(){
        parent::__construct();
    }
    /*
     * register action
     */
    public function registerProcess() {
        session_start();
        isint($this->uid)&&exit;//if user has already signed in
        include_once ROOT_DIR.'application'.DIRECTORY_SEPARATOR.'securimage'.DIRECTORY_SEPARATOR.'securimage.php';
        $securimage = new Securimage();//for captcha
        $_POST==NULL&&exit;
        $username=x($_POST['username']);
        $email=x($_POST['email']);
        $pwd=x($_POST['pwd']);
        $captcha=x($_POST['captcha']);
        if($username==''||strlen($username)>20){
            $callback=array(
                'status'=>false,
                'msg'=>'请重新输入用户名'
            );
            echo json_encode($callback);
        }elseif($this->model->userIsExist('username',$username)){
            $callback=array(
                'status'=>false,
                'msg'=>'用户名重复，请输入其他用户名吧'
            );
            echo json_encode($callback);
        }elseif(strlen($email)>100||$email==''||!is_email($email)){
            $callback=array(
                'status'=>false,
                'msg'=>'请重新输入邮箱'
            );
            echo json_encode($callback);
        }elseif($this->model->userIsExist('email',$email)){
            $callback=array(
                'status'=>false,
                'msg'=>'邮箱重复，请输入其他邮箱吧'
            );
            echo json_encode($callback);
        }elseif(strlen($pwd)>30||$pwd==''){
            $callback=array(
                'status'=>false,
                'msg'=>'请重新输入密码'
            );
            echo json_encode($callback);
        }elseif($this->config['show_register_captcha']&&!$securimage->check(strtolower($captcha))){
            $callback=array(
                'status'=>false,
                'refresh_captcha'=>true,
                'msg'=>'请重新输入验证码'
            );
            echo json_encode($callback);
        }else{
            $uid=$this->model->addUser($username,$email,$pwd);
            if($uid>0){
                $callback=array(
                    'status'=>true,
                    'url'=>url('profile/avatar')
                );
                //send msg to new user(optional)
                $admin_username=$this->model('user')->getUsernameByUid(1);
                $msg_content=$username.'您好，欢迎来到爱码士，爱码士只为极客和爱代码的人们，这里我们聊极客、聊代码、聊生活、聊职场，为打造一种热闹亲和的盖楼氛围，爱码士设计了一种新颖的论坛界面。请保持友好互助的心态和极客们交流';
                if($uid>1){
                    $msg_model=$this->model('msg');
                    $msg_model->addmsg(1,$admin_username,$uid,$msg_content);
                    $msg_model->msgDel(1,$msg_model->msg_prefix.'1_'.$uid);
                }
                $auth=encrypt("$uid\t$username");
                if($_POST['remember']==0){
                    setcookie($this->config['cookie_prefix'].'Auth',$auth,0,'/');
                }else{
                    setcookie($this->config['cookie_prefix'].'Auth',$auth,time()+5616000,'/');
                }
                echo json_encode($callback);
            }else{
                $callback=array(
                    'status'=>false,
                    'msg'=>'网络繁忙，请稍后重试'
                );
                echo json_encode($callback);
            }
        }
    }
    public function loginProcess(){
        session_start();
        isint($this->uid)&&exit;//if user has signed in
        include_once ROOT_DIR.'application'.DIRECTORY_SEPARATOR.'securimage'.DIRECTORY_SEPARATOR.'securimage.php';
        $securimage = new Securimage();//for captcha
        $login_email=x($_POST['login_email']);
        $login_pwd=x($_POST['login_pwd']);
        $captcha=x($_POST['login_captcha']);
        if(!is_email($login_email)){
            $callback=array(
                'status'=>false,
                'msg'=>'请输入正确的邮箱'
            );
            echo json_encode($callback);
        }else if($login_pwd==''){
            $callback=array(
                'status'=>false,
                'msg'=>'请输入正确的密码'
            );
            echo json_encode($callback);
        }elseif($this->config['show_login_captcha']&&!$securimage->check(strtolower($captcha))){
            $callback=array(
                'status'=>false,
                'refresh_captcha'=>true,
                'msg'=>'请重新输入验证码'
            );
            echo json_encode($callback);
        }else{
            $user=$this->model->checkUser($login_email,$login_pwd);
            if(!is_array($user)){
                $callback=array(
                    'status'=>false,
                    'msg'=>'邮箱或密码错误，请重新登录'
                );
                echo json_encode($callback);
            }else{
                $callback=array(
                    'status'=>true
                );
                $uid=$user['uid'];
                $username=$user['username'];
                $auth=encrypt($uid);
                if(intval($_POST['remember'])==0){
                    setcookie($this->config['cookie_prefix'].'Auth',$auth,0,'/');
                    setcookie($this->config['cookie_prefix'].'username',$username,0,'/');
                }else{
                    setcookie($this->config['cookie_prefix'].'Auth',$auth,time()+5616000,'/');
                    setcookie($this->config['cookie_prefix'].'username',$username,time()+5616000,'/');
                }
                echo json_encode($callback);
            }
        }
    }
    public function logout(){
        setcookie($this->config['cookie_prefix'].'Auth','',time()-1,'/');
        setcookie($this->config['cookie_prefix'].'avatar_small','',time()-1,'/');
        setcookie($this->config['cookie_prefix'].'avatar_big','',time()-1,'/');
        setcookie($this->config['cookie_prefix'].'user_info','',time()-1,'/');
        header('location:'.$this->config['site']);
    }
}
?>