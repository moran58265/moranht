<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    //验证规则
    protected $rule = [
        'username' => 'require|alphaNum|length:5,20',
        'password' => 'require|length:5,20',
        'captcha' => 'require|captcha',
        'useremail' => 'require|email',
        'appid' => 'require|number',

    ];

    //错误提示信息
    protected $message = [
        'username.require' => '用户名不能为空',
        'username.alphaNum' => '用户名只能是字母和数字',
        'password.require' => '密码不能为空',
        'repassword.require' => '确认密码不能为空',
        'password.length' => '密码长度不正确',
        'useremail.require' => '邮箱不能为空',
        'useremail.email' => '邮箱格式不正确',
        'appid.require' => 'appid不能为空',
        'appid.number' => 'appid必须是数字',
    ];

    //验证场景
    protected $scene = [
        'Login' => ['username', 'password', 'captcha'],
        'adduser' => ['username', 'password', 'useremail', 'appid'],
    ];

}