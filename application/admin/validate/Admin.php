<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username' => 'require|alphaDash|length:5,20',
        'password' => 'require|length:5,20',
        'nickname' => 'require',
        'captcha' => 'require|captcha',
        'old_password' => 'require|length:5,20',
        'new_password' => 'require|length:5,20',
        'userqq' => 'require',
    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'username.alphaDash' => '用户名只能是字母、数字、下划线或破折号',
        'username.length' => '用户名长度为5-20个字符',
        'password.require' => '密码不能为空',
        'password.length' => '密码长度为5-20个字符',
        'nickname.require' => '昵称不能为空',
        'captcha.require' => '验证码不能为空',
        'captcha.captcha' => '验证码错误',
        'old_password.require' => '旧密码不能为空',
        'new_password.require' => '新密码不能为空',
        'userqq.require' => 'QQ号不能为空',
    ];

    protected $scene = [
        'Login' => ['username', 'password', 'captcha'],
        'edit' => ['username', 'nickname', 'userqq'],
        'password' => ['old_password', 'new_password'],
    ];
}
