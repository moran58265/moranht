<?php

namespace app\apiv2\controller;

use app\admin\model\App as ModelApp;
use app\admin\model\Email;
use app\admin\model\User as ModelUser;
use think\Db;
use think\facade\Cookie;
use think\Request;
use think\facade\Validate;
use think\helper\Time;

class User extends Base
{
    /**
     * 用户登录不设置cookie数据
     *
     * @param Request $request
     */
    public function Login(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'password' => 'require',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('appid错误');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->password != md5($data['password'])) {
            return $this->returnError('密码错误');
        }
        if ($user->banned != 'false') {
            return $this->returnError('账号已被禁用');
        }
        $nowip = $this->get_user_ip();
        //判断ip与数据库中的ip是否一致
        if ($user->ip != $nowip) {
            $emailresult = Email::get(1);
            $address = $this->ip_address($nowip, $user->ip);
            if ($address['code'] == 400) {
                $emailcontent = "<h3>您的账号在异地登录,登录IP为" . $nowip . "(" . $address['msg'] . ")<br>若是你本人登录，请忽略<br>若不是请及时修改密码</h3>";
                $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailresult["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">异地登录提醒</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">若是本人操作可忽略。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailresult["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                $this->send_mail($user->useremail, "异地登录提醒", $emailcontenthtml);
            }
        }
        $usertoken = md5($user->id . $user->username . $user->password . time());
        $userinfo = [
            'username' => $user->username,
            'usertoken' => $usertoken,
            'userip' => $nowip,
        ];
        if (!empty($data['device'])) {
            if ($data['device'] != $user->device) {
                $emailresult = Email::get(1);
                $address = $this->ip_address($nowip, $user->ip);
                $emailcontent = "<h3>您的账号在新设备登录,登录IP为" . $nowip . "(" . $address['msg'] . ")<br>若是你本人登录，请忽略<br>若不是请及时修改密码</h3>";
                $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailresult["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">异地登录提醒</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">若是本人操作可忽略。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailresult["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                $this->send_mail($user->useremail, "新设备登录提醒", $emailcontenthtml);
                $updateuser = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
                $updateuser->user_token = $usertoken;
                $updateuser->ip = $nowip;
                $updateuser->device = $data['device'];
                $updateuser->save();
            } else {
                $updateuser = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
                $updateuser->user_token = $usertoken;
                $updateuser->ip = $nowip;
                $updateuser->save();
            }
        } else {
            $updateuser = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
            $updateuser->user_token = $usertoken;
            $updateuser->ip = $nowip;
            $updateuser->save();
        }
        return $this->returnSuccess('登录成功', $userinfo);
    }

    /**
     * 用户登录设置cookie数据
     * @return string
     */
    public function setLogin(Request $request)
    {
        $data = $request->param();
        $app = ModelApp::get($data['appid']);
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (Cookie::has('usertoken')) {
            $usertoken = Cookie::get('usertoken');
            if ($user->user_token != $usertoken) {
                Cookie::clear();
                return $this->returnError('身份验证失败，请重新登录');
            }
            return $this->returnJson('您已经登录了');
        }
        $validate = Validate::make([
            'username' => 'require',
            'password' => 'require',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        if (!$app) {
            return $this->returnError('appid错误');
        }
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->password != md5($data['password'])) {
            return $this->returnError('密码错误');
        }
        if ($user->banned != 'false') {
            return $this->returnError('账号已被禁用');
        }
        $nowip = $this->get_user_ip();
        //判断ip与数据库中的ip是否一致
        if ($user->ip != $nowip) {
            $emailresult = Email::get(1);
            $address = $this->ip_address($nowip, $user->ip);
            if ($address['code'] == 400) {
                $emailcontent = "<h3>您的账号在异地登录,登录IP为" . $nowip . "(" . $address['msg'] . ")<br>若是你本人登录，请忽略<br>若不是请及时修改密码</h3>";
                $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailresult["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">异地登录提醒</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">若是本人操作可忽略。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailresult["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                $this->send_mail($user->useremail, "异地登录提醒", $emailcontenthtml);
            }
        }
        $usertoken = md5($user->id . $user->username . $user->password . time());
        Cookie::forever('username', $data['username'], 3600 * 24 * 30);
        Cookie::forever('usertoken', $usertoken, 3600 * 24 * 30);
        Cookie::forever('appid', $data['appid'], 3600 * 24 * 30);
        $userinfo = [
            'username' => $user->username,
            'usertoken' => $usertoken,
            'userip' => $nowip,
        ];
        if (!empty($data['device'])) {
            if ($data['device'] != $user->device) {
                $emailresult = Email::get(1);
                $address = $this->ip_address($nowip, $user->ip);
                $emailcontent = "<h3>您的账号在新设备登录,登录IP为" . $nowip . "(" . $address['msg'] . ")<br>若是你本人登录，请忽略<br>若不是请及时修改密码</h3>";
                $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailresult["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">异地登录提醒</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">若是本人操作可忽略。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailresult["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                $this->send_mail($user->useremail, "新设备登录提醒", $emailcontenthtml);
                $updateuser = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
                $updateuser->user_token = $usertoken;
                $updateuser->ip = $nowip;
                $updateuser->device = $data['device'];
                $updateuser->save();
            } else {
                $updateuser = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
                $updateuser->user_token = $usertoken;
                $updateuser->ip = $nowip;
                $updateuser->save();
            }
        } else {
            $updateuser = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
            $updateuser->user_token = $usertoken;
            $updateuser->ip = $nowip;
            $updateuser->save();
        }
        return $this->returnSuccess('登录成功', $userinfo);
    }

    /**
     * 用戶注册
     * @param Request $request
     * @return \think\response\Json
     */
    public function Register(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'password' => 'require',
            'useremail' => 'require|email',
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('appid不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if ($user) {
            return $this->returnError('用户名已存在');
        }
        $user = ModelUser::where('useremail', $data['useremail'])->find();
        if ($user) {
            return $this->returnError('邮箱已存在');
        }
        $userdevicenum = ModelUser::where('zcdevice', $data['device'])->count();
        if ($userdevicenum != 0) {
            if ($app->devicenum != 0) {
                if ($app->devicenum < $userdevicenum) {
                    return $this->returnError('设备数量已达上限');
                }
            }
        }
        if (substr($data['useremail'], -7) == '@qq.com') {
            $userqq = substr($data['useremail'], 0, strpos($data['useremail'], '@'));
        } else {
            $userqq = '';
        }
        $emailcode = Db::name('emailcode')->where('id', 1)->find();
        if ($app->is_email == 'true') {
            if (empty($data['emailcode'])) {
                return $this->returnError('邮箱验证码不能为空');
            }
            if ($data['emailcode'] != $emailcode['code']) {
                return $this->returnError('邮箱验证码错误');
            }
        }
        if (empty($data['device'])) {
            $adddata = [
                'username' => $data['username'],
                'password' => md5($data['password']),
                'useremail' => $data['useremail'],
                'appid' => $data['appid'],
                'qq' => $userqq,
                'usertx' => \think\facade\Request::scheme() . "://" . \think\facade\Request::host() . "/" . "usertx.png",
                'viptime' => time() + $app->zcvip * 24 * 3600,
                'money' => $app->zcmoney,
                'exp' => $app->zcexp,
                'creattime' => time(),
            ];
        } else {
            $adddata = [
                'username' => $data['username'],
                'password' => md5($data['password']),
                'useremail' => $data['useremail'],
                'appid' => $data['appid'],
                'qq' => $userqq,
                'usertx' => \think\facade\Request::scheme() . "://" . \think\facade\Request::host() . "/" . "usertx.png",
                'viptime' => time() + $app->zcvip * 24 * 3600,
                'money' => $app->zcmoney,
                'exp' => $app->zcexp,
                'creattime' => time(),
                'driver' => $data['device'],
                'zcdriver' => $data['zcdevice'],
            ];
        }
        $user = ModelUser::create($adddata);
        if ($user) {
            return $this->returnJson('注册成功');
        } else {
            return $this->returnError('注册失败');
        }
    }

    /**
     * 获取用户信息
     * @param Request $request
     */
    public function GetUserInfo(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'usertoken' => 'require',
                'appid' => 'require|number'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->user_token != $data['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $arr = $app['hierarchy'];
        foreach (eval("return $arr;") as $key => $value) {
            if ($user['exp'] >= $key) {
                $hierarchy = $value;
            } else {
                break;
            }
        }
        $result = array();
        $result['id'] = $user['id'];
        $result['username'] = $user['username'];
        $result['nickname'] = $user['nickname'];
        $result['qq'] = $user['qq'];
        $result['useremail'] = $user['useremail'];
        $result['usertx'] = $user['usertx'];
        $result['signature'] = $user['signature'];
        $result['viptime'] = date("Y-m-d", $user['viptime']);
        $result['money'] = $user['money'];
        $result['exp'] = $user['exp'];
        $result['creattime'] = $user['creattime'];
        $result['banned'] = $user['banned'];
        if ($user['banned'] == 'true') {
            $result['banned_reason'] = $user['banned_reason'];
        }
        $result['title'] = $user['title'];
        $result['appname'] = $app['appname'];
        $result['hierarchy'] = $hierarchy;
        $result['invitecode'] = $user['invitecode'];
        $result['invitetotal'] = $user['invitetotal'];
        $result['inviter'] = $user['inviter'];
        //评论数
        $result['commentnum'] = Db::name('comment')->where('username', $data['username'])->where('appid', $data['appid'])->count();
        //帖子数量
        $result['postnum'] = Db::name('post')->where('username', $data['username'])->where('appid', $data['appid'])->count();
        $result['likenum'] = Db::name('likepost')->where('username', $data['username'])->where('appid', $data['appid'])->count();
        return $this->returnSuccess('获取成功', $result);
    }

    /**
     * 获取注册验证码
     * @param Request $request
     * @return \think\response\Json
     */
    public function GetRegCode(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'useremail' => 'require|email',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return  $this->ReturnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->ReturnError('应用不存在');
        }
        $user = ModelUser::where('useremail', $data['useremail'])->where('appid', $data['appid'])->find();
        if ($user) {
            return $this->ReturnError('邮箱已被注册');
        }
        $email = Email::get(1);
        $ckemailcode = Db::name('emailcode')->where('id', 1)->find();
        if ($ckemailcode['ip'] == $this->get_user_ip()) {
            if (time() - $ckemailcode['creat_time'] < 60) {
                return $this->ReturnError('60s内只能发送一次');
            }
            $emailcode = $this->getRandChar(4);
            $updataemailcode = [
                'emailcode' => $emailcode,
                'creat_time' => time(),
                'ip' => $this->get_user_ip(),
            ];
            Db::name('emailcode')->where('id', 1)->update($updataemailcode);
            $emailcontent = "<h3>您的注册验证码是：" . $emailcode . "</h3>";
            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $email["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">注册验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $email["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
            $this->send_mail($data['useremail'], "注册验证码", $emailcontenthtml);
            return $this->returnJson("发送成功");
        } else {
            $emailcode = $this->getRandChar(4);
            $updataemailcode = [
                'emailcode' => $emailcode,
                'creat_time' => time(),
                'ip' => $this->get_user_ip(),
            ];
            Db::name('emailcode')->where('id', 1)->update($updataemailcode);
            $emailcontent = "<h3>您的注册验证码是：" . $emailcode . "</h3>";
            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $email["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">注册验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $email["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
            $this->send_mail($data['useremail'], "注册验证码", $emailcontenthtml);
            return $this->returnJson("发送成功");
        }
    }

    /**
     * 获取找回密码验证码
     * @param  [type] $data [description]
     */
    public function GetPasswordCode(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return $this->ReturnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->ReturnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->ReturnError('用户不存在');
        }
        $email = Email::get(1);
        $ckemailcode = Db::name('passcode')->where('id', 1)->find();
        if ($ckemailcode['ip'] == $this->get_user_ip()) {
            if (time() - $ckemailcode['creattime'] < 60) {
                return $this->ReturnError('60s内只能发送一次');
            }
            $emailcode = $this->getRandChar(4);
            $updataemailcode = [
                'emailcode' => $emailcode,
                'creat_time' => time(),
                'ip' => $this->get_user_ip(),
            ];
            Db::name('emailcode')->where('id', 1)->update($updataemailcode);
            $emailcontent = "<h3>您要找回密码的验证码是：" . $emailcode . "<br>若不是本人操作请警觉</h3>";
            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $email["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">找回密码验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $email["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
            $this->send_mail($user['useremail'], "找回密码验证码", $emailcontenthtml);
            return $this->returnJson("发送成功");
        } else {
            $emailcode = $this->getRandChar(4);
            $updataemailcode = [
                'emailcode' => $emailcode,
                'creat_time' => time(),
                'ip' => $this->get_user_ip(),
            ];
            Db::name('emailcode')->where('id', 1)->update($updataemailcode);
            $emailcontent = "<h3>您要找回密码的验证码是：" . $emailcode . "<br>若不是本人操作请警觉</h3>";
            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $email["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">找回密码验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $email["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
            $this->send_mail($user['useremail'], "找回密码验证码", $emailcontenthtml);
            return $this->returnJson("发送成功");
        }
    }

    /**
     * 用户签到
     * @param  [type] $userid [用户id]
     */
    public function UserSign(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'usertoken' => 'require',
                'appid' => 'require|number'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->user_token != $data['usertoken']) {
            return $this->returnError('用户token错误');
        }
        list($start, $end) = Time::today();
        if ($user['signtime'] > $start) {
            return $this->ReturnError("您今天已经签到过了");
        }
        $addviptime = $app['signvip'] * 24 * 60 * 60;
        if ($user->viptime >= time()) {
            $updateuserdata = [
                'viptime' => $user['viptime'] + $addviptime,
                'money' => $user['money'] + $app['signmoney'],
                'exp' => $user['exp'] + $app['signexp'],
                'signtime' => time(),
            ];
        } else {
            $updateuserdata = [
                'viptime' => time() + $addviptime,
                'money' => $user['money'] + $app['signmoney'],
                'exp' => $user['exp'] + $app['signexp'],
                'signtime' => time(),
            ];
        }
        $updateuser = ModelUser::where('username', $data['username'])->update($updateuserdata);
        if (!$updateuser) {
            return $this->returnError('签到失败');
        }
        return $this->returnJson("签到成功");
    }

    /**
     * 找回密码
     * 
     */
    public function ResetPassword(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'code' => 'require'
        ]);
        if (!$validate->check($data)) {
            return $this->ReturnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->ReturnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->ReturnError('用户不存在');
        }
        $code = Db::name('passcode')->where('id', 1)->find();
        $newpassword = $this->getRandChar(6);
        $updateuser = ModelUser::where('username', $data['username'])->update(['password' => md5($newpassword)]);
        if (!$updateuser) {
            return $this->ReturnError('重置密码失败');
        }
        $emailresult = Db::name('email')->where('id', 1)->find();
        $emailcontent = "<h3>您的随机密码是：" . $newpassword . "<br>请及时修改为您易记的密码</h3>";
        $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailresult["email_title"] . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">找回密码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意密码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailresult["email_title"] . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
        $this->send_mail($user['useremail'], "重置密码", $emailcontenthtml);
        Db::name('passcode')->where('id', 1)->update(["passcode" => "", "ip" => "", "creattime" => ""]);
        return $this->returnJson("随机密码已发送到您的邮箱，请注意查看");
    }

    /**
     * 修改用户信息
     * 
     */
    public function UpdateUser(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = $request->param();
            $validate = Validate::make([
                'nickname' => 'require',
                'qq' => 'require',
                'useremail' => 'require',
                'signature' => 'require',
            ]);
            if (!$validate->check($data)) {
                return $this->ReturnError($validate->getError());
            }
            $datacookie = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
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
                return $this->ReturnError($validate->getError());
            }
            $datacookie = [
                'username' => $data['username'],
                'usertoken' => $data['usertoken'],
                'appid' => $data['appid'],
            ];
        }

        $app = ModelApp::where('appid', $datacookie['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $datacookie['username'])->where('appid', $datacookie['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->user_token != $datacookie['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $update = [
            'nickname' => $data['nickname'],
            'qq' => $data['qq'],
            'useremail' => $data['useremail'],
            'signature' => $data['signature'],
        ];
        $result = ModelUser::where('username', $datacookie['username'])->where('appid', $datacookie['appid'])->update($update);
        if ($result) {
            return $this->returnJson('修改成功');
        } else {
            return $this->ReturnError('修改失败');
        }
    }

    /**
     * 用户头像上传
     */
    public function UploadHead(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'usertoken' => 'require',
                'appid' => 'require|number'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->user_token != $data['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        if ($file) {
            $update = [
                'usertx' => $file['fullPath'],
            ];
            $result = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->update($update);
            if ($result) {
                return $this->returnJson('上传成功');
            } else {
                return $this->ReturnError('上传失败');
            }
        } else {
            return $this->ReturnError('上传失败');
        }
    }

    /**
     * 用户金币/经验排行榜
     * 
     */
    public function UserList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'type' => 'require'
        ]);
        if (!$validate->check($data)) {
            return $this->ReturnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        if (empty($data['limit'])) {
            $data['limit'] = 10;
        }
        if ($data['type'] == 'exp') {
            $need_field = ['id', 'username', 'nickname', 'usertx', 'exp', 'title'];
            $userlist = Db::name('user')
                ->where('appid', $data['appid'])
                ->order('exp', 'desc')
                ->field($need_field)
                ->limit($data['limit'])
                ->select();
        } else {
            $need_field = ['id', 'username', 'nickname', 'usertx', 'money', 'title'];
            $userlist = Db::name('user')
                ->where('appid', $data['appid'])
                ->order('money', 'desc')
                ->field($need_field)
                ->limit($data['limit'])
                ->select();
        }
        return $this->returnSuccess("查询成功", $userlist);
    }

    /**
     * 判断用户是否是会员
     *
     * @param Request $request
     * @return boolean
     */
    public function isVip(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'appid' => 'require|number'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->viptime > time()) {
            return $this->returnJson('true');
        } else {
            return $this->returnJson('false');
        }
    }

    /**
     * 修改密码
     * 
     */
    public function UpdatePassword(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = $request->param();
            $validate = Validate::make([
                'oldpwd' => 'require',
                'newpwd' => 'require'
            ]);
            if (!$validate->check($data)) {
                return $this->ReturnError($validate->getError());
            }
            $datacookie = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'appid' => 'require|number',
                'usertoken' => 'require',
                'oldpwd' => 'require',
                'newpwd' => 'require'
            ]);
            $datacookie = [
                'username' => $data['username'],
                'usertoken' => $data['usertoken'],
                'appid' => $data['appid'],
            ];
        }
        if (!$validate->check($data)) {
            return $this->ReturnError($validate->getError());
        }
        $app = ModelApp::where('appid', $datacookie['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $datacookie['username'])->where('appid', $datacookie['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->user_token != $datacookie['usertoken']) {
            return $this->returnError('用户token错误');
        }
        if ($user->password != md5($data['oldpwd'])) {
            return $this->returnError('原密码错误');
        }
        if ($data['newpwd'] == $data['oldpwd']) {
            return $this->returnError('新密码不能与原密码一致');
        }
        $update = [
            'password' => md5($data['newpwd']),
        ];
        $result = ModelUser::where('username', $datacookie['username'])->where('appid', $datacookie['appid'])->update($update);
        if ($result) {
            return $this->returnJson('修改成功');
        } else {
            return $this->ReturnError('修改失败');
        }
    }

    /**
     * 填写邀请码
     */
    public function InviteCode(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'invitecode' => 'require',   //别人的邀请码
            'username' => 'require',    //自己的用户名
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->ReturnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user['invitecode'] == $data['invitecode']) {
            return $this->ReturnError("不能填写自己的邀请码");
        }
        if ($user['inviter'] != '') {
            return $this->ReturnError("已经填写过邀请码");
        }
        $invite = ModelUser::where('invitecode', $data['invitecode'])->find();
        if (!$invite) {
            return $this->returnError('邀请码不存在');
        }
        //判断填写用户的会员状态
        if ($user['viptime'] > time()) {
            $viptime = $user['viptime'];
        } else {
            $viptime = time();
        }
        if ($app->finvitemoney == '') {
            $finvitemoney = 0;
        }
        if ($app->finviteexp == '') {
            $finviteexp = 0;
        }
        if ($app->finvitevip == '') {
            $finvitevip = 0;
        }

        if ($app->invitemoney == '') {
            $invitemoney = 0;
        }
        if ($app->invitevip == '') {
            $invitevip = 0;
        }
        if ($app->inviteexp == '') {
            $inviteexp = 0;
        }
        //判断邀请码用户的会员状态
        if ($invite['viptime'] > time()) {
            $inviteviptime = $invite['viptime'];
        } else {
            $inviteviptime = time();
        }
        $updatefuser = [
            'inviter' => $invite['username'],
            'viptime' => $viptime + $finvitevip * 24 * 3600,
            'exp' => $finviteexp + $user->exp,
            'money' => $finvitemoney + $user->money,
        ];
        $updateuser = [
            'invitetotal' => $invite->invitetotal + 1,
            'viptime' => $viptime + $invitevip * 24 * 3600,
            'exp' => $inviteexp + $invite->exp,
            'money' => $invitemoney + $invite->money,
        ];
        $result = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->update($updatefuser);
        $invite = ModelUser::where('invitecode', $data['invitecode'])->update($updateuser);
        return $this->returnJson('填写成功');
    }

    /**
     * 生成邀请码
     *
     * @param Request $request
     * @return void
     */
    public function Getinvitecode(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'appid' => 'require|number'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->invitecode != '') {
            return $this->returnError('已经生成过邀请码');
        }
        $invitecode = $this->getinvitacode($data['username']);
        $update = [
            'invitecode' => $invitecode,
        ];
        $result = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->update($update);
        if ($result) {
            return $this->returnJson('生成成功');
        } else {
            return $this->ReturnError('生成失败');
        }
    }

    /**
     * 邀请排行榜
     * @param Request $request
     * @return void
     */
    public function GetinviterList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'limit' => 'number',
            'order' => 'alpha',
        ]);
        if (!$validate->check($data)) {
            return $this->ReturnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $order = isset($data['order']) ? $data['order'] : "asc";
        $limit = isset($data['limit']) ? $data['limit'] : "10";
        $inviterList = Db::name('user')->field('username,nickname,usertx,signature,invitetotal')->where('appid', $data['appid'])->order('invitetotal', $order)->limit($limit)->select();
        return $this->returnSuccess("获取成功", $inviterList);
    }

    /**
     * 退出登录(限cookie使用)
     * 
     */
    public function LoginOut()
    {
        Cookie::clear();
        Cookie::delete('usertoken');
        Cookie::delete('username');
        Cookie::delete('appid');
        return $this->returnJson('退出成功');
    }

    /**
     * 获取其他用户信息
     * @param Request $request
     */
    public function GetOtherUserInfo(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number'
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        $arr = $app['hierarchy'];
        foreach (eval("return $arr;") as $key => $value) {
            if ($user['exp'] >= $key) {
                $hierarchy = $value;
            } else {
                break;
            }
        }
        $result = array();
        $result['id'] = $user['id'];
        $result['username'] = $user['username'];
        $result['nickname'] = $user['nickname'];
        $result['qq'] = $user['qq'];
        $result['useremail'] = $user['useremail'];
        $result['usertx'] = $user['usertx'];
        $result['signature'] = $user['signature'];
        $result['viptime'] = date("Y-m-d", $user['viptime']);
        $result['money'] = $user['money'];
        $result['exp'] = $user['exp'];
        $result['creattime'] = $user['creattime'];
        $result['banned'] = $user['banned'];
        if ($user['banned'] == 'true') {
            $result['banned_reason'] = $user['banned_reason'];
        }
        $result['title'] = $user['title'];
        $result['appname'] = $app['appname'];
        $result['hierarchy'] = $hierarchy;
        $result['invitecode'] = $user['invitecode'];
        $result['invitetotal'] = $user['invitetotal'];
        $result['inviter'] = $user['inviter'];
        //评论数
        $result['commentnum'] = Db::name('comment')->where('username', $data['username'])->count();
        //帖子数量
        $result['postnum'] = Db::name('post')->where('username', $data['username'])->count();
        //点赞数量
        $result['likenum'] = Db::name('likepost')->where('username', $data['username'])->where('appid', $data['appid'])->count();
        return $this->returnSuccess('获取成功', $result);
    }
}
