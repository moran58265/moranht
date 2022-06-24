<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use think\Controller;
use think\facade\Cookie;
use think\facade\Request;

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
        $checkbox = input('post.checkbox');
        if ($checkbox == 1) {
            // session([
            //     'expire' => '3600 * 24 * 5',
            // ]);
        }
        session('admin_id', $user->id);
        session('admininfo', $user->toArray());
        $salt = Common::getRandChar(6);
        session('adminToken', md5($user['password'] . $salt));
        $user->salt = $salt;
        $user->save();
        //授权
        // try {
        //     $url = $this->request->host();
        //     $response = file_get_contents("http://ht.moranblog.cn/authweb.php?domain=" . $url);
        //     $data = json_decode($response, true);
        //     if (strtotime($data['data']['duetime']) < time()) {
        //         Session('auth',2);
        //     } else {
        //         session('auth',1);
        //     }
        // } catch (\Exception $e) {
        //     session('auth',2);
        // }
        Common::adminLog('登录成功');
        return Common::ReturnSuccess('登录成功');
    }
}
