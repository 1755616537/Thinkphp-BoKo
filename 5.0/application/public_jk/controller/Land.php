<?php
namespace app\public_jk\controller;
use think\captcha\Captcha;
use think\Controller;
use think\Cookie;
use think\Db;
use think\Loader;
use think\Session;

class Land extends Controller {
	public function login(){
        return $this->fetch();
	}
    public function xlogin(){
        return $this->fetch();
    }
//	获取验证码图片
	public function huoQu_verify(){
	    $Verify=new  Captcha();
	    $Verify->length=4;
        return $Verify->entry(1);
    }
//    是否验证码正确
    public function shiFou_verify($code){
        $Captcha=new Captcha();
        return $Captcha->check($code,1);
    }
//    验证登陆信息
    public  function shiFou_login(){
//        验证码
//        $code=input('code');
//        $Verify=$this->shiFou_verify($code);
//        if(!$Verify){
//            $jg_return=array(
//            'code'=>'0',
//            'msg'=>'验证码错误',
//        );
//        return $jg_return;
//        }
//        账号密码
        $username=input('userlogin','trim');
        $password=input('password');
        $data = [
            'userlogin'=>$username,
            'password'=>$password
        ];
        $validate = Loader::validate('User_1');
        if(!$validate->check($data)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>$validate->getError(),
            );
            return $jg_return;
        }
        $user_information=$this->shiFou_User_information($username,$password,true);
        if(!$user_information){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'用户不存在或密码错误',
            );
            return $jg_return;
        }

//        登陆成功
        //设置session和有效时间
        Session::set('id',$user_information['id']);
        Session::set('username',$user_information['username']);
        $jg_return=array(
            'code'=>'1',
            'msg'=>'登陆成功',
        );
        Cookie::set('id',$user_information['id'],3600);
        Cookie::set('username',$user_information['username'],3600);
//        $this->success('登陆成功', '/gongju');
        return $jg_return;

    }
//    是否已经登陆
    public function if_shiFou_login(){
        $user_id=Session::get('id');
        if(!$user_id){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'已登陆',
            'user_id'=>$user_id,
        );
        return $jg_return;
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
            if($data_query['username']!=''){
                return $data_query;
            }else{
                return false;
            }
        }
	    if($data_query['password']===$password){
	        return $data_query;
        }else{
	        return false;
        }
    }
//    退出
    public function quit(){
	    session('id',null);
        session('username',null);
        Cookie::set('id',"");
        Cookie::set('username',"");
        $jg_return=array(
            'code'=>'1',
            'msg'=>'退出成功',
        );
        return $jg_return;
    }
//    注册
    public function register(){
        $username=input('userlogin','trim');
        $password=input('password');
        $data = [
            'userlogin'=>$username,
            'password'=>$password
        ];
        $validate = Loader::validate('User_1');
        if(!$validate->check($data)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>$validate->getError(),
            );
            return $jg_return;
        }
        //查询是否已存在用户
        $user_information=$this->shiFou_User_information($username,'',false);
        if($user_information){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'用户已存在',
            );
            return $jg_return;
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
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'注册成功',
        );
        return $jg_return;
    }
//    找回密码
    public function retrieve(){
        $username=input('userlogin','trim');
        //查询是否已存在用户
        $user_information=$this->shiFou_User_information($username,'',false);
        if($user_information){
            $jg_return=array(
                'code'=>'1',
                'msg'=>$user_information['password'],
            );
            return $jg_return;
        }else{
            $jg_return=array(
                'code'=>'0',
                'msg'=>'用户不存在',
            );
            return $jg_return;
        }


    }
    //    获取ip
    public function get_real_ip(){
        $ip=false;
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
        return false;
    }



}