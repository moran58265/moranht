<?php

namespace app\admin\controller;

use think\Controller;
use think\facade\Session;

class BaseController extends Controller
{
    public function __construct()
    {
        $admin_id = session('admin_id');
        $admintoken = session('adminToken');
        $admin = \app\admin\model\Admin::get($admin_id);
        if ($admintoken == null){
            return $this->error('登录已失效,请重新登录！','login/');
        }else{
            if (md5($admin['password'].$admin['salt']) != $admintoken){
                return $this->error('你的账号已在别处登录，请重新登录账号','login/');
            }
        }
    }

}