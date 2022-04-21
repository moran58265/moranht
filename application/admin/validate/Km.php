<?php

namespace app\admin\validate;

use think\Validate;

class Km extends Validate{
    // 验证规则
    protected $rule = [
        'exp' => 'require|number',
        'money' => 'require|number',
        'vip' => 'require|number',
        'appid' => 'require|number',
        'generatenum' => 'require|number',
        'kmlength' => 'require|number',
    ];

    // 错误提示
    protected $message = [
        'exp.require' => '经验值不能为空',
        'exp.number' => '经验值必须为数字',
        'money.require' => '金币值不能为空',
        'money.number' => '金币值必须为数字',
        'vip.require' => 'vip天数不能为空',
        'vip.number' => 'vip天数必须为数字',
        'appid.require' => 'appid不能为空',
        'appid.number' => 'appid必须为数字',
        'generatenum.require' => '生成数量不能为空',
        'generatenum.number' => '生成数量必须为数字',
        'kmlength.require' => '卡密长度不能为空',
        'kmlength.number' => '卡密长度必须为数字',
    ];


    // 应用场景
    protected $scene = [
        'add' => ['exp','money','vip','appid','generatenum','kmlength'],
    ];
}