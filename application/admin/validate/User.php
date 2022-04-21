<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    //验证规则
    protected $rule = [
        'username' => 'require|alphaNum|length:5,20',
        'password' => 'require|length:5,20',
        'repassword' => 'require',
        'captcha' => 'require|captcha',

    ];

    //错误提示信息
    protected $message = [
        'username.require' => '用户名不能为空',
        'username.alphaNum' => '用户名只能是字母和数字',
        'password.require' => '密码不能为空',
        'repassword.require' => '确认密码不能为空',
    ];

    //验证场景
    protected $scene = [
        'Login' => ['username', 'password', 'captcha'],
        'edit' => ['username', 'password', 'repassword'],
    ];

}