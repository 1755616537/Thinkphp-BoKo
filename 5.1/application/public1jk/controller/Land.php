<?php
namespace app\public1jk\controller;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\facade\Cookie;
use think\facade\Route;
use think\facade\Session;

class Land extends Controller {
	public function login(){
        return $this->fetch();
	}
    public function xlogin(){
        return $this->fetch();
    }
//	获取验证码图片
	public function huoQu_verify(){
        $config =    [
            'length'    =>    4,
            'codeSet'   =>    '0123456789',
            'useCurve'  =>    false,
            'useNoise'  =>    false,
        ];
        $Captcha=new  Captcha($config);
        return $Captcha->entry('login');
    }
//    是否验证码正确
    public function shiFou_verify($code){
        $Captcha=new Captcha();
        if(!$Captcha->check($code,'login')){
            return false;
        }
        return true;
    }
//    登陆
    public  function shiFou_login(){
//        账号密码
        $username=input('userlogin');
        $password=input('password');
        $data = [
            'userlogin'=>$username,
            'password'=>$password
        ];
        $validate = new \app\public1jk\validate\User_1;
        if(!$validate->check($data)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>$validate->getError(),
            );
            return json($jg_return);
        }
        //        验证码
        $verify=Session::get('verify');
        if($verify==1 ){
            $verify_hq=input('verify');
            $Verify=$this->shiFou_verify($verify_hq);
            if(!$Verify){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'验证码错误',
                );
//                默认不开启
                return json($jg_return);
            }
        }
        $user_information=$this->shiFou_User_information($username,$password,true);
        if(!$user_information){
            $verify=Session::get('verify');
            if($verify==null){
                Session::set('verify',3);
            }elseif($verify!=1){
                Session::set('verify',$verify-1);
            }
            $jg_return=array(
                'code'=>'0',
                'msg'=>'用户不存在或密码错误',
            );
            return json($jg_return);
        }

//        登陆成功
        //设置session和有效时间
        $data=array(
            'id'=>$user_information['id'],
            'username'=>$username,
            'password'=>$password,
        );
        $Token=new Token();
        $token=$Token->generate($data);
        Cookie::set('id',$user_information['id'],3600);
        Cookie::set('username',$username,3600);
        Cookie::set('token',$token,3600);
        Session::delete('verify');
        $jg_return=array(
            'code'=>'1',
            'msg'=>'登陆成功',
            'token'=>$token,
        );
//        $this->success('登陆成功', '/gongju');
        return json($jg_return);

    }
//    更改密码
    public function changepassword(){
        $username=input('userlogin','trim');
        $password=input('password');
        $token=Cookie::get('token');
        if(empty($token)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return json($jg_return);
        }
        $Token=new Token();
        $data_return=$Token->verification($token);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data = [
            'userlogin'=>$username,
            'password'=>$password
        ];
        $validate = new \app\public1jk\validate\User_1;
        if(!$validate->check($data)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>$validate->getError(),
            );
            return json($jg_return);
        }
        $user_username=Session::get('username');
        if(!$user_username){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return json($jg_return);
        }
        if($user_username!=$username){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'登陆账号和修改账号不一致',
            );
            return json($jg_return);
        }
        //查询是否已存在用户
        $user_information=$this->shiFou_User_information($username,'',false);
        if($user_information['password']==$password){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'密码相同',
            );
            return json($jg_return);
        }
        if($user_information){
            $username_suz['username']=$username;
            $password_suz['password']=$password;
            $data_query= Db::table('nav_user_information')->where($username_suz)->data($password_suz)->update();
            if(!$data_query){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'错误',
                );
                return json($jg_return);
            }
            $jg_return=array(
                'code'=>'1',
                'msg'=>'更改成功',
            );
            return json($jg_return);
        }else{
            $jg_return=array(
                'code'=>'0',
                'msg'=>'用户不存在',
            );
            return json($jg_return);
        }

    }
//    是否已经登陆
    public function if_shiFou_login(){
        $token=Cookie::get('token');
        if(empty($token)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登录',
            );
            return json($jg_return);
        }
        $Token=new Token();
        $data_return=$Token->verification($token);
        if($data_return['code']==0){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登录',
            );
            return json($jg_return);
        }
        return json($data_return);
        $user_id=Session::get('id');
        if(!$user_id){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'已登陆',
            'user_id'=>$user_id,
        );
        return json($jg_return);
    }
//    用户信息是否正确
    public function shiFou_User_information($username,$password,$query_password){
//	    创建数组类型
	    $username_suz['username']=$username;
//	    数据库中查询对应的username，只输出第一条
	    $data_query= Db::table('nav_user_information')->where($username_suz)->failException(false)->find();
        if($data_query==null){
            return false;
        }
	    if(!$query_password){
	        return $data_query;
        }
	    if($data_query['password']===$password){
	        return $data_query;
        }else{
	        return false;
        }
    }
//    退出
    public function quit(){
        Cookie::delete('token');
        $jg_return=array(
            'code'=>'1',
            'msg'=>'退出成功',
        );
        return json($jg_return);
    }
//    注册
    public function register(){
        $username=input('userlogin','trim');
        $password=input('password');
        $data = [
            'userlogin'=>$username,
            'password'=>$password
        ];
        $validate = new \app\public1jk\validate\User_1;
        if(!$validate->check($data)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>$validate->getError(),
            );
            return json($jg_return);
        }
        //查询是否已存在用户
        $user_information=$this->shiFou_User_information($username,'',false);
        if($user_information){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'用户已存在',
            );
            return json($jg_return);
        }
	    $time=date('Y-m-d h:i:s', time());
	    $ip=$this->get_real_ip();
        $data=array(
            'username'=>$username,
            'password'=>$password,
            'register_time'=>$time,
            'login_time'=>$time,
            'login_ip'=>$ip,
        );
        $data_query=Db::name('nav_user_information')->insert($data);
        if(!$data_query){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'注册失败',
            );
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'注册成功',
        );
        return json($jg_return);
    }
//    找回密码
    public function retrieve(){
        $username=input('userlogin','trim');
//        $token=Cookie::get('token');
//        if(empty($token)){
//            $jg_return=array(
//                'code'=>'0',
//                'msg'=>'未登陆',
//            );
//            return json($jg_return);
//        }
//        $Token=new Token();
//        $data_return=$Token->verification($token);
//        if($data_return['code']==0){
//            return $data_return;
//        }
        if(empty($username)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'userlogin为空',
            );
            return json($jg_return);
        }
        //查询是否已存在用户
        $user_information=$this->shiFou_User_information($username,'',false);
        if($user_information){
            $jg_return=array(
                'code'=>'1',
                'msg'=>$user_information['password'],
            );
            return json($jg_return);
        }else{
            $jg_return=array(
                'code'=>'0',
                'msg'=>'用户不存在',
            );
            return json($jg_return);
        }
    }
    //    获取ip
    public function get_real_ip(){
//        $ip=false;
//        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
//            $ip = $_SERVER["HTTP_CLIENT_IP"];
//        }
//        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
//            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
//            if($ip){
//                array_unshift($ips, $ip); $ip = FALSE;
//            }
//            for($i = 0; $i < count($ips); $i++){
//                if (!preg_match ("^(10|172\.16|192\.168)\.", $ips[$i])){
//                    $ip = $ips[$i];
//                    break;
//                }
//            }
//        }
//        return($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }



}