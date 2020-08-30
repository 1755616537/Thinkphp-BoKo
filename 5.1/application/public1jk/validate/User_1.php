<?php


namespace app\public1jk\validate;


use think\Validate;

class User_1 extends Validate {
    protected $rule =   [
        'userlogin'=>'require',
        'password'=>'require|length:4,25',
    ];

    protected $message  =   [
        'userlogin.require' => '账号必须',
        'password.require' => '密码必须',
        'password.length'     => '密码最少不小于4个字符最多不能超过25个字符',
    ];


}