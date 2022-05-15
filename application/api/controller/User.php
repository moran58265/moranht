<?php

namespace app\api\controller;

use app\common\controller\Common as Commoncode;
use think\Controller;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\facade\Session;
use think\facade\Validate;
use think\helper\Time;
use think\Request;

class User extends Controller
{
    public function Login(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'password' => 'require',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $AppResult = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (Exception $exception) {
            return Common::return_msg(400, $exception->getMessage());
        }
        if ($AppResult == "" || $AppResult == null) {
            return Common::return_msg(400, "没有此app");
        } else {
            try {
                $UserResult = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            } catch (DataNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (DbException $e) {
                return Common::return_msg(400, "请求失败");
            }
            if ($UserResult == "" || $UserResult == null) {
                return Common::return_msg(400, "没有此账号");
            } else {
                if ($UserResult['password'] == md5($data['password'])) {
                    $UserToken = md5($data['username'] . time());
                    $result = [
                        'username' => $data['username'],
                        'UserToken' => $UserToken,
                        'ip' => Common::get_user_ip(),
                    ];
                    if ($UserResult['ip'] != Common::get_user_ip()) {
                        $emailcontent = "你的账号在异地登录，登录IP为" . Common::get_user_ip() . "如是你本人登录，请忽略，不是请立即修改密码";
                        try {
                            Common::send_mail($UserResult['useremail'], "异地登录提醒", $emailcontent);
                        } catch (DataNotFoundException $e) {
                            return Common::return_msg(400, "请求失败");
                        } catch (ModelNotFoundException $e) {
                            return Common::return_msg(400, "请求失败");
                        } catch (DbException $e) {
                            return Common::return_msg(400, "请求失败");
                        }
                    }
                    try {
                        Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update(['user_token' => $UserToken, 'ip' => Common::get_user_ip()]);
                    } catch (PDOException $e) {
                        return Common::return_msg(400, "请求失败");
                    } catch (Exception $e) {
                        return Common::return_msg(400, "请求失败");
                    }
                    return Common::return_msg(200, "登录成功", $result);
                } else {
                    return Common::return_msg(400, "密码错误");
                }
            }
        }
    }

    public function Register(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'password' => 'require',
            'useremail' => 'require|email',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $emailcode = Db::name('emailcode')->where('id', 1)->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $app['appid'])->find();
            $useremail = Db::name('user')->where('useremail', $data['useremail'])->where('appid', $app['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == null || $app == "") {
            return Common::return_msg(400, "app不存在");
        } else {
            if ($app['is_email'] == 'true') {
                $validate = Validate::make([
                    'emailcode' => 'require',
                ]);
                if (!$validate->check($data)) {
                    return Common::return_msg(400, $validate->getError());
                }
                if ($user == null || $user == "") {
                    if ($useremail != null || $useremail != "") {
                        return Common::return_msg(400, "邮箱已存在");
                    }
                    $emailregcode = Session::get('emailcode');
                    if ($emailcode['emailcode'] == $data['emailcode']) {
                        //if ($emailregcode == $data['emailcode']) {
                        $adddata = [
                            'username' => $data['username'],
                            'password' => md5($data['password']),
                            'useremail' => $data['useremail'],
                            'appid' => $data['appid'],
                            'usertx' => \think\facade\Request::scheme() . "://" . \think\facade\Request::host() . "/" . "usertx.png",
                            'viptime' => time() + ($app['zcvip'] * 24 * 3600),
                            'money' => $app['zcmoney'],
                            'exp' => $app['zcexp'],
                            'creattime' => time(),
                        ];
                        $user = Db::name('user')->data($adddata)->insert();
                        if ($user > 0) {
                            Db::name('emailcode')->where('id', 1)->update(["emailcode" => "", "ip" => "", "creat_time" => ""]);
                            return Common::return_msg(200, '注册成功');
                        } else {
                            return Common::return_msg(400, '注册失败');
                        }
                    } else {
                        return Common::return_msg(400, "验证码错误");
                    }
                } else {
                    return Common::return_msg(400, "账号已存在");
                }
            } else {
                if ($user == null || $user == "") {
                    if ($useremail != null || $useremail != "") {
                        return Common::return_msg(400, "邮箱已存在");
                    }
                    $adddata = [
                        'username' => $data['username'],
                        'password' => md5($data['password']),
                        'useremail' => $data['useremail'],
                        'appid' => $data['appid'],
                        'usertx' => \think\facade\Request::scheme() . "://" . \think\facade\Request::host() . "/" . "usertx.png",
                        'viptime' => time() + ($app['zcvip'] * 24 * 3600),
                        'money' => $app['zcmoney'],
                        'exp' => $app['zcexp'],
                        'creattime' => time(),
                    ];
                    $user = Db::name('user')->data($adddata)->insert();
                    if ($user > 0) {

                        return Common::return_msg(200, '注册成功');
                    } else {
                        return Common::return_msg(400, '注册失败');
                    }
                } else {
                    return Common::return_msg(400, "账号已存在");
                }
            }
        }
    }

    public function GetPasswordCode(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $ckcodetime = Db::name('passcode')->where('id', 1)->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($ckcodetime['ip'] == Common::get_user_ip()) {
            if (time() - $ckcodetime['creattime'] < 60) {
                return Common::return_msg(400, "60s内只能发送一次");
            } else {
                $passcode = Common::getchar(4);
                $updatapasscode = [
                    'passcode' => $passcode,
                    'creattime' => time(),
                    'ip' => Common::get_user_ip(),
                ];
                try {
                    Db::name('passcode')->where('id', 1)->update($updatapasscode);
                } catch (PDOException $e) {
                    return Common::return_msg(400, "请求失败");
                } catch (Exception $e) {
                    return Common::return_msg(400, "请求失败");
                }
                try {
                    return Common::send_mail($user['useremail'], "找回密码验证码", "你的找回密码验证码是：" . $passcode);
                } catch (DataNotFoundException $e) {
                    return Common::return_msg(400, "请求失败");
                } catch (ModelNotFoundException $e) {
                    return Common::return_msg(400, "请求失败");
                } catch (DbException $e) {
                    return Common::return_msg(400, "请求失败");
                }
            }
        } else {
            $passcode = Common::getchar(4);
            $updatapasscode = [
                'passcode' => $passcode,
                'creattime' => time(),
                'ip' => Common::get_user_ip(),
            ];
            try {
                Db::name('passcode')->where('id', 1)->update($updatapasscode);
            } catch (PDOException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (Exception $e) {
                return Common::return_msg(400, "请求失败");
            }
            try {
                return Common::send_mail($user['useremail'], "找回密码验证码", "你的找回密码验证码是：" . $passcode);
            } catch (DataNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (DbException $e) {
                return Common::return_msg(400, "请求失败");
            }
        }
    }

    public function GetRegCode(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'useremail' => 'require|email',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('useremail', $data['useremail'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == null || $app == "") {
            return Common::return_msg(400, "app不存在");
        } else {
            if ($user == null || $user == "") {
                try {
                    $ckcodetime = Db::name('emailcode')->where('id', 1)->find();
                } catch (DataNotFoundException $e) {
                    return Common::return_msg(400, "请求失败");
                } catch (ModelNotFoundException $e) {
                    return Common::return_msg(400, "请求失败");
                } catch (DbException $e) {
                    return Common::return_msg(400, "请求失败");
                }

                if ($ckcodetime['ip'] == Common::get_user_ip()) {
                    if (time() - $ckcodetime['creat_time'] > 60) {
                        $emailcode = Common::getchar(4);
                        $updataemailcode = [
                            'emailcode' => $emailcode,
                            'creat_time' => time(),
                            'ip' => Common::get_user_ip(),
                        ];
                        try {
                            Db::name('emailcode')->where('id', 1)->update($updataemailcode);
                        } catch (PDOException $e) {
                            return Common::return_msg(400, "请求失败");
                        } catch (Exception $e) {
                            return Common::return_msg(400, "请求失败");
                        }
                        try {
                            return Common::send_mail($data['useremail'], "注册验证码", "你的注册验证码是：" . $emailcode);
                        } catch (DataNotFoundException $e) {
                            return Common::return_msg(400, "请求失败");
                        } catch (ModelNotFoundException $e) {
                            return Common::return_msg(400, "请求失败");
                        } catch (DbException $e) {
                            return Common::return_msg(400, "请求失败");
                        }
                    } else {
                        return Common::return_msg(400, "60s内只能发送一次");
                    }
                } else {
                    $emailcode = Common::getchar(4);
                    $updataemailcode = [
                        'emailcode' => $emailcode,
                        'creat_time' => time(),
                        'ip' => Common::get_user_ip(),
                    ];
                    try {
                        Db::name('emailcode')->where('id', 1)->update($updataemailcode);
                    } catch (PDOException $e) {
                        return Common::return_msg(400, "请求失败");
                    } catch (Exception $e) {
                        return Common::return_msg(400, "请求失败");
                    }
                    try {
                        return Common::send_mail($data['useremail'], "注册验证码", "你的注册验证码是：" . $emailcode);
                    } catch (DataNotFoundException $e) {
                        return Common::return_msg(400, "请求失败");
                    } catch (ModelNotFoundException $e) {
                        return Common::return_msg(400, "请求失败");
                    } catch (DbException $e) {
                        return Common::return_msg(400, "请求失败");
                    }
                }
            } else {
                return Common::return_msg(400, "你注册的邮箱已经存在了");
            }
        }
    }


    public function GetUserinfo(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }

        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        } else {
            if ($user == "" || $user == null) {
                return Common::return_msg(400, "没有此账号");
            } else {
                if ($data['usertoken'] == $user['user_token']) {
                    if ($user['banned'] == 'true') {
                        $need_field = ['u.id', 'u.username', 'u.nickname', 'u.qq', 'u.useremail', 'u.usertx', 'u.signature', 'u.viptime', 'u.money', 'u.exp', 'FROM_UNIXTIME(u.creattime,"%Y-%m-%d") as creattime', 'u.banned', 'u.banned_reason', 'u.title', 'a.appname','u.invitecode','u.invitetotal','u.inviter'];
                    } else {
                        $need_field = ['u.id', 'u.username', 'u.nickname', 'u.qq', 'u.useremail', 'u.usertx', 'u.signature', 'u.viptime', 'u.money', 'u.exp', 'FROM_UNIXTIME(u.creattime,"%Y-%m-%d") as creattime', 'u.banned', 'u.title', 'a.appname','u.invitecode','u.invitetotal','u.inviter'];
                    }
                    try {
                        $userdata = Db::name('user')
                            ->alias('u')
                            ->join('app a', 'a.appid=u.appid')
                            ->where('u.username', $data['username'])
                            ->where('u.appid', $app['appid'])
                            ->field($need_field)
                            ->find();
                    } catch (DataNotFoundException $e) {
                        return Common::return_msg(400, "请求失败");
                    } catch (ModelNotFoundException $e) {
                        return Common::return_msg(400, "请求失败");
                    } catch (DbException $e) {
                        return Common::return_msg(400, "请求失败");
                    }
                    $arr = $app['hierarchy'];
                    foreach (eval("return $arr;") as $key => $value) {
                        if ($userdata['exp'] >= $key) {
                            $hierarchy = $value;
                        } else {
                            break;
                        }
                    }
                    $result = array();
                    $result['id'] = $userdata['id'];
                    $result['username'] = $userdata['username'];
                    $result['nickname'] = $userdata['nickname'];
                    $result['qq'] = $userdata['qq'];
                    $result['useremail'] = $userdata['useremail'];
                    $result['usertx'] = $userdata['usertx'];
                    $result['signature'] = $userdata['signature'];
                    $result['viptime'] = date("Y-m-d", $userdata['viptime']);
                    $result['money'] = $userdata['money'];
                    $result['exp'] = $userdata['exp'];
                    $result['creattime'] = $userdata['creattime'];
                    $result['banned'] = $userdata['banned'];
                    if ($userdata['banned'] == 'true') {
                        $result['banned_reason'] = $userdata['banned_reason'];
                    }
                    $result['title'] = $userdata['title'];
                    $result['appname'] = $userdata['appname'];
                    $result['hierarchy'] = $hierarchy;
                    $result['invitecode'] = $userdata['invitecode'];
                    $result['invitetotal'] = $userdata['invitetotal'];
                    $result['inviter'] = $userdata['inviter'];
                    return Common::return_msg(200, "查询成功", $result);
                } else {
                    return Common::return_msg(400, "token错误");
                }
            }
        }
    }

    public function ResetPassword(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'code' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $ckcode = Db::name('passcode')->where('id', 1)->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($ckcode['passcode'] != $data['code']) {
            return Common::return_msg(400, "验证码错误");
        }
        $newpassword = Common::getchar(6);
        try {
            $updateuser = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update(['password' => md5($newpassword)]);
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($updateuser > 0) {
            try {
                Common::send_mail($user['useremail'], "随机密码", "你的随机密码是：" . $newpassword);
                Db::name('passcode')->where('id', 1)->update(["passcode" => "", "ip" => "", "creattime" => ""]);
            } catch (DataNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (DbException $e) {
                return Common::return_msg(400, "请求失败");
            }
            return Common::return_msg(200, '随机密码已发送到您的邮箱，请注意查看');
        } else {
            return Common::return_msg(400, '修改失败');
        }
    }

    public function UpdateUser(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'usertoken' => 'require',
            'nickname' => 'require',
            'qq' => 'require',
            'useremail' => 'require',
            'signature' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        $update = [
            'nickname' => $data['nickname'],
            'qq' => $data['qq'],
            'useremail' => $data['useremail'],
            'signature' => $data['signature'],
        ];
        try {
            $user = Db::name('user')->where('username', $data['username'])->update($update);
        } catch (PDOException $e) {
            return Common::return_msg(400, $e);
        } catch (Exception $e) {
            return Common::return_msg(400, $e);
        }
        if ($user > 0) {
            return Common::return_msg(200, '修改成功');
        } else {
            return Common::return_msg(400, '你未做任何修改！');
        }
    }

    public function UploadHead(Request $request)
    {
        $data = $request->param();
        $upload = new Upload();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        $file = $upload->uploadDetail('file');
        try {
            Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update(['usertx' => $file["fullPath"]]);
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "上传成功");
    }


    public function UserList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'type' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ((new Request)->post('limit') == null) {
            $data['limit'] = 10;
        }
        if ($data['type'] == 'exp') {
            $need_field = ['id', 'username', 'nickname', 'usertx', 'exp', 'title'];
            try {
                $userlist = Db::name('user')
                    ->where('appid', $data['appid'])
                    ->order('exp', 'desc')
                    ->field($need_field)
                    ->limit($data['limit'])
                    ->select();
            } catch (DataNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (DbException $e) {
                return Common::return_msg(400, "请求失败");
            }
        } else {
            $need_field = ['id', 'username', 'nickname', 'usertx', 'money', 'title'];
            try {
                $userlist = Db::name('user')
                    ->where('appid', $data['appid'])
                    ->order('money', 'desc')
                    ->field($need_field)
                    ->limit($data['limit'])
                    ->select();
            } catch (DataNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (DbException $e) {
                return Common::return_msg(400, "请求失败");
            }
        }
        return Common::return_msg(200, "查询成功", $userlist);
    }

    public function UserSign(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        list($start, $end) = Time::today();
        if ($user['signtime'] > $start) {
            return Common::return_msg(400, "您今天已经签到过了");
        }
        $addviptime = $app['signvip'] * 24 * 60 * 3600;
        if ($user['viptime'] >= time()) {
            $updateuser = [
                'viptime' => $user['viptime'] + $addviptime,
                'money' => $user['money'] + $app['signmoney'],
                'exp' => $user['exp'] + $app['signexp'],
                'signtime' => time(),
            ];
        } else {
            $updateuser = [
                'viptime' => time() + $addviptime,
                'money' => $user['money'] + $app['signmoney'],
                'exp' => $user['exp'] + $app['signexp'],
                'signtime' => time(),
            ];
        }
        try {
            $sql = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update($updateuser);
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($sql > 0) {
            return Common::return_msg(200, '签到成功');
        } else {
            return Common::return_msg(400, '签到失败');
        }
    }

    public function isVip(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($user['viptime'] > time()) {
            return Common::return_msg(200, "true");
        } else {
            return Common::return_msg(200, "false");
        }
    }

    public function UpdatePassword(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'usertoken' => 'require',
            'oldpwd' => 'require',
            'newpwd' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($user['password'] != md5($data['oldpwd'])) {
            return Common::return_msg(400, "旧密码错误");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        $result = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update(['password' => md5($data['newpwd'])]);
        if ($result > 0) {
            return Common::return_msg(200, "修改成功");
        } else {
            return Common::return_msg(400, "修改失败");
        }
    }

    //填写邀请码
    public function InviteCode(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'invitecode' => 'require',   //别人的邀请码
            'username' => 'require',    //自己的用户名
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $invitecode = Db::name('user')->where('invitecode', $data['invitecode'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($user['inviter'] != "") {
            return Common::return_msg(400, "已经填写过邀请码");
        }
        if ($invitecode == "" || $invitecode == null) {
            return Common::return_msg(400, "没有该邀请码");
        }
        //判断用户会员状态
        if ($user['viptime'] > time()) {
            $viptime = $user['viptime'];
        } else {
            $viptime = time();
        }
        if ($invitecode['viptime'] > time()) {
            $viptime = $invitecode['viptime'];
        } else {
            $viptime = time();
        }
        if($user['money'] == ""){
            $user['money'] = 0;
        }
        if($user['exp'] == ""){
            $user['exp'] = 0;
        }
        if($invitecode['money'] == ""){
            $invitecode['money'] = 0;
        }
        if($invitecode['exp'] == ""){
            $invitecode['exp'] = 0;
        }
        //填写邀请码得人
        Db::name('user')
            ->where('username', $data['username'])
            ->update([
                'inviter' => $invitecode['username'],
                'money' => $user['money'] + $app['finvitemoney'],
                'viptime' => $viptime + $app['finvitevip'] * 3600 * 24,
                'exp' => $user['exp'] + $app['finviteexp'],
            ]);
        //拥有邀请码得人
        Db::name('user')
            ->where('invitecode', $data['invitecode'])
            ->update([
                'invitetotal' => $invitecode['invitetotal'] + 1,
                'money' => $invitecode['money'] + $app['invitemoney'],
                'viptime' => $viptime + $app['invitevip'] * 3600 * 24,
                'exp' => $invitecode['exp'] + $app['inviteexp'],
            ]);
        return Common::return_msg(200, "填写成功");
    }

    public function Getinvitecode(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        $finvitecode = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        if ($finvitecode['invitecode'] == "" || $finvitecode['invitecode'] == null) {
            $invitecode = Commoncode::getinvitacode($data['username']);
            Db::name('user')
                ->where('username', $data['username'])
                ->where('appid', $data['appid'])
                ->update(['invitecode' => $invitecode]);
            return Common::return_msg(200, "生成成功", $invitecode);
        } else {
            return Common::return_msg(400, "已经生成过邀请码");
        }
    }
}
