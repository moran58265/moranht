<?php

namespace app\admin\validate;

use think\Validate;

class Shop extends Validate
{
    // 验证规则
    protected $rule = [
        'shopname'  => 'require',
        'shoptype' => 'require|number',
        'money' => 'require|number',
        'vipnum' => 'require|number',
        'inventory' => 'require|number',
        'appid' => 'require|number',
    ];

    // 错误提示
    protected $message = [
        'shopname.require' => '商品名称不能为空',
        'shoptype.require' => '商品类型不能为空',
        'money.require' => '金币值不能为空',
        'money.number' => '金币值必须为数字',
        'vipnum.require' => 'VIP天数不能为空',
        'vipnum.number' => 'VIP天数必须为数字',
        'inventory.require' => '库存不能为空',
        'inventory.number' => '库存必须为数字',
        'appid.require' => 'APPID不能为空',
        'appid.number' => 'APPID必须为数字',
    ];

    //验证场景
    protected $scene = [
        'add'  =>  ['shopname','shoptype','money','vipnum','inventory','appid'],
        'edit'  =>  ['shopname','shoptype','money','vipnum','inventory','appid'],
    ];


}