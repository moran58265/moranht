<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\controller\Common;
use think\Controller;

class Login extends Controller
{
    //登录页面
    public function index()
    {
        return $this->fetch();
    }

    //登录操作
    public function dologin()
    {
        $data = input('post.');
        $AdminValidate = new \app\admin\validate\Admin();
        if (!$AdminValidate->scene('Login')->check($data)) {
            return Common::ReturnError($AdminValidate->getError());
        }
        $user = Admin::where('username', $data['username'])->find();
        if (!$user || $user->password != md5($data['password'])) {
            return Common::ReturnError('用户名或密码错误');
        }
        session('admin_id', $user->id);
        session('admininfo', $user->toArray());
        $salt = Common::getRandChar(6);
        session('adminToken', md5($user['password'] . $salt));
        $user->salt  = $salt;
        $user->save();
        return Common::ReturnSuccess('登录成功');
    }

}