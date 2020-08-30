<?php


namespace app\public1jk\controller;


use think\facade\Request;

class Token
{
    public function b(){
        $user_data=array(
            'id'=>'1',
            'username'=>'2',
            'password'=>'2',
        );
        $token=$this->generate($user_data);
        sleep(1);
        $data_return=$this->verification($token);
        return json($data_return);
        //        登陆成功验证token
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
        $data_return=$data_return['msg'];
//  --------------------------------------
    }
//    生成token
    public function generate($user_data){
        $time=strtotime('+1hour');
        $user_data['time']=$time;
//        令牌的类型
        $data_suz=array(
            'G'=>$this->encode_pkcs($time),
            'alg'=>'HS256',//所使用的加密算法（如：SHA256或者RSA）
            'typ'=>'JWT',//：令牌的类型（即JWT）
        );
        $data_json=json_encode($data_suz);
        //加密令牌
        $d1=$this->encode_base64($data_json);
        $user_json=json_encode($user_data);
        //加密用户信息
        $d2=$this->encode_base64($user_json);
        //获取用户ip
        $ip=request()->ip();
        $data_suz=array(
            'd1'=>$d1,
            'd2'=>$d2,
            'ip'=>$ip,
            'ip2'=>$this->ip(),
        );
//        print_r(ip());
        $data_json=json_encode($data_suz);
        //加密签名
        $d3=crypt($data_json,'$5$rounds=5000$usesomesillystringforsalt$');
        $jg_return=$d1 . "." . $d2 . "." .$d3;
        return $jg_return;
    }
//    验证token
    public function verification($data){
        $data_suz = explode(".",$data,3);
        if(count($data_suz)!=3){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'无效token',
            );
            return $jg_return;
        }
        $d1=$data_suz[0];
        $d2=$data_suz[1];
        //获取用户ip
        $ip=request()->ip();
        $data_suz2=array(
            'd1'=>$d1,
            'd2'=>$d2,
            'ip'=>$ip,
            'ip2'=>$this->ip(),
        );
        $data_json=json_encode($data_suz2);
        $d3=crypt($data_json,'$5$rounds=5000$usesomesillystringforsalt$');
        //验证是否被篡改
        if($data_suz[2]!=$d3){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'无效token',
            );
            return $jg_return;
        }
        //是否已经过期
        $d1=$this->decode_base64($d1);
        $d2=$this->decode_base64($d2);
        $d1_suz=json_decode($d1,true);
        $d2_suz=json_decode($d2,true);
        if($this->decode_pkcs($d1_suz['G'])!=$d2_suz['time']){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'过期时间不一致',
            );
            return $jg_return;
        }
        $time=strtotime('now');
        if($this->decode_pkcs($d1_suz['G'])<=$time){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'时间过期',
            );
            return $jg_return;
        }

        $jg_return=array(
            'code'=>'1',
            'msg'=>$d2_suz,
        );
        return $jg_return;
    }
//    加密 base64+urlencode
    public function encode_base64($data){
        return base64_encode(urlencode($data));
    }
//    解密 base64+urlencode
    public function decode_base64($data){
        return urldecode(base64_decode($data));
    }
//    公钥加密文本
    public function encode_pkcs($data){
        $public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDl/CJbz2hYivhO5Z/znLPr5Pt5
F4/TDBsyOxRJox4dMx7hSJbsJcIrhFOJQsnU70pNrgnYhpCXQGwk0RFkupA++bcj
zrtJp1jlbfs1sxTf7Ay7K82TEMGgU7/eqqwPt8Cf/6ZxfHK5CT76CYr24c7l9PAt
JA24kEKZ7UWzUJTm4wIDAQAB
-----END PUBLIC KEY-----';
        $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的
        openssl_public_encrypt($data,$jg_return,$pu_key);//公钥加密
        $jg_return = base64_encode($jg_return);
        return $jg_return;
    }
//    私钥解密
    public function decode_pkcs($data){
        $private_key = '-----BEGIN ENCRYPTED PRIVATE KEY-----
MIICxjBABgkqhkiG9w0BBQ0wMzAbBgkqhkiG9w0BBQwwDgQI4c+VYJZhbxoCAggA
MBQGCCqGSIb3DQMHBAhn+PefKoo4ygSCAoAwNhu7MdbEAdehQT/NKxClZBBwl0Pj
Uj0b2XcT0GnkUYp4Se5gkA8sV0qyTkl45aHqQ/Wxf9AAha226K8Eeogf4FgopjgY
ibeMdNQ/JjxHL+FLh9jeSO9+eotB9ME2jEy6YC8Mdmy5CMQcZEHRnSGDuiuvs2UK
VinvlaQ2Uq12nrse+5wMVLI12LYG/xQ7pKXiOpZwNYSvTuvqaIt/T95OVtngu6yH
U5pF0vwEi3XcFqgLyJZ6LbtBDmnpN1+L7OWAu2AYMivFj1FThuwvHf7kd9SqeqTz
u0LzB+g8c8ceiF7itcYiFBam40cmvGYDlANZD7aDH/S9rG0fLN5VES/uTE3t+TEE
b4re6wcgyqIQJAC917tqq+9fNgC8pGG1lhfHjLl6Ge6RNBzhNBxnvQcuU81+JJ+C
uOAuv5MW81nJn3PgQfovUy7Z4e5YsMaw9DTr7SbJxdXeMXCLJE91U+5aYR6uPat4
RyypqY5GccJt/ZrBesExAXJLBssK87tWex+6gcXLFpCG49b2bO84xavG3NIl4ec6
kUaVwVvnun1uMH0OZkINM+icjlIAZJWH/IbPxD+zwxFkv322Q7x9YAut4Lsuv8kK
v6286D1/Si5/YrRX4IM5p20hechvmkaVQQtBGAzDK85RlawIimoJWHHpWTd6PGrL
luolbq17B38S8ZxDXtIU4U30SwbIvh4PxKSWwOMOvmf9I+xRM11XyQrAkkFTo41d
ErcqS99NiydC9rEsARZ1m5+fUoiBSsE9X/u712ziP2dgKNYixQN7ew7B8KzEJY7P
Zi2lh2/g2Y4LYFjWfWF6qPTMzeLbp+QmZRH2nhe24oW9uzOGW0M3g8I3
-----END ENCRYPTED PRIVATE KEY-----';
        $pi_key = openssl_pkey_get_private($private_key,'20130619');//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $data = base64_decode($data);
        openssl_private_decrypt($data,$jg_return,$pi_key);//私钥解密
        return $jg_return;
    }
    public function ip(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $cip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
        }else{
            $cip = '';
        }
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';
        unset($cips);
        return $cip;
    }
    public function a(){
        $private_key = '-----BEGIN ENCRYPTED PRIVATE KEY-----
MIICxjBABgkqhkiG9w0BBQ0wMzAbBgkqhkiG9w0BBQwwDgQI4c+VYJZhbxoCAggA
MBQGCCqGSIb3DQMHBAhn+PefKoo4ygSCAoAwNhu7MdbEAdehQT/NKxClZBBwl0Pj
Uj0b2XcT0GnkUYp4Se5gkA8sV0qyTkl45aHqQ/Wxf9AAha226K8Eeogf4FgopjgY
ibeMdNQ/JjxHL+FLh9jeSO9+eotB9ME2jEy6YC8Mdmy5CMQcZEHRnSGDuiuvs2UK
VinvlaQ2Uq12nrse+5wMVLI12LYG/xQ7pKXiOpZwNYSvTuvqaIt/T95OVtngu6yH
U5pF0vwEi3XcFqgLyJZ6LbtBDmnpN1+L7OWAu2AYMivFj1FThuwvHf7kd9SqeqTz
u0LzB+g8c8ceiF7itcYiFBam40cmvGYDlANZD7aDH/S9rG0fLN5VES/uTE3t+TEE
b4re6wcgyqIQJAC917tqq+9fNgC8pGG1lhfHjLl6Ge6RNBzhNBxnvQcuU81+JJ+C
uOAuv5MW81nJn3PgQfovUy7Z4e5YsMaw9DTr7SbJxdXeMXCLJE91U+5aYR6uPat4
RyypqY5GccJt/ZrBesExAXJLBssK87tWex+6gcXLFpCG49b2bO84xavG3NIl4ec6
kUaVwVvnun1uMH0OZkINM+icjlIAZJWH/IbPxD+zwxFkv322Q7x9YAut4Lsuv8kK
v6286D1/Si5/YrRX4IM5p20hechvmkaVQQtBGAzDK85RlawIimoJWHHpWTd6PGrL
luolbq17B38S8ZxDXtIU4U30SwbIvh4PxKSWwOMOvmf9I+xRM11XyQrAkkFTo41d
ErcqS99NiydC9rEsARZ1m5+fUoiBSsE9X/u712ziP2dgKNYixQN7ew7B8KzEJY7P
Zi2lh2/g2Y4LYFjWfWF6qPTMzeLbp+QmZRH2nhe24oW9uzOGW0M3g8I3
-----END ENCRYPTED PRIVATE KEY-----';

        $public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDl/CJbz2hYivhO5Z/znLPr5Pt5
F4/TDBsyOxRJox4dMx7hSJbsJcIrhFOJQsnU70pNrgnYhpCXQGwk0RFkupA++bcj
zrtJp1jlbfs1sxTf7Ay7K82TEMGgU7/eqqwPt8Cf/6ZxfHK5CT76CYr24c7l9PAt
JA24kEKZ7UWzUJTm4wIDAQAB
-----END PUBLIC KEY-----';

        //PKCS#8类型

        $pi_key = openssl_pkey_get_private($private_key,'20130619');//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的
        if($pu_key==null){
            return '公钥错误';
        }
        print_r($pi_key);echo "<br>";
        print_r($pu_key);echo "<br>";
        $data = "123";//原始数据
        $encrypted = "";
        $decrypted = "";
        echo "data:",$data,"<br>";
        echo "---------------------------------------<br>";
        echo '私钥加密:'.'<br>';
        openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        echo $encrypted,"<br>";
        echo "公钥解密:".'<br>';
        $encrypted = base64_decode($encrypted);
        openssl_public_decrypt($encrypted,$decrypted,$pu_key);//私钥加密的内容通过公钥可用解密出来
        echo $decrypted,"<br>";
        echo "---------------------------------------<br>";
        echo "公钥加密:".'<br>';
        openssl_public_encrypt($data,$encrypted,$pu_key);//公钥加密
        $encrypted = base64_encode($encrypted);
        echo $encrypted,"<br>";
        echo "私钥解密:".'<br>';
        $encrypted = base64_decode($encrypted);
        openssl_private_decrypt($encrypted,$decrypted,$pi_key);//私钥解密
        echo $decrypted,"<br>";
    }
}