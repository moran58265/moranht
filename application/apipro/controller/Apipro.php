<?php

namespace app\apipro\controller;

use app\admin\model\Admin;
use app\admin\model\App;
use app\admin\model\Comment;
use app\admin\model\Email;
use app\admin\model\Km;
use app\admin\model\Likepost;
use app\admin\model\Message;
use app\admin\model\Notes;
use app\admin\model\Plate;
use app\admin\model\PlatePost;
use app\admin\model\Shop;
use app\admin\Model\Shoporder;
use app\admin\model\User;
use think\Db;
use think\facade\Cookie;
use think\Validate;

class Apipro extends Common
{

    //用户系统
    //                            _ooOoo_
    //                           o8888888o
    //                           88" . "88
    //                           (| -_- |)
    //                            O\ = /O
    //                        ____/`---'\____
    //                      .   ' \\| |// `.
    //                       / \\||| : |||// \
    //                     / _||||| -:- |||||- \
    //                       | | \\\ - /// | |
    //                     | \_| ''\---/'' | |
    //                      \ .-\__ `-` ___/-. /
    //                   ___`. .' /--.--\ `. . __
    //                ."" '< `.___\_<|>_/___.' >'"".
    //               | | : `- \`.;`\ _ /`;.`/ - ` : | |
    //                 \ \ `-. \_ __\ /__ _/ .-` / /
    //         ======`-.____`-.___\_____/___.-`____.-'======
    //                            `=---='
    //
    //         .............................................
    //                  佛祖镇楼                 BUG辟易
    //          佛曰:
    //                  写字楼里写字间，写字间里程序员；
    //                  程序人员写程序，又拿程序换酒钱。
    //                  酒醒只在网上坐，酒醉还来网下眠；
    //                  酒醉酒醒日复日，网上网下年复年。
    //                  但愿老死电脑间，不愿鞠躬老板前；
    //                  奔驰宝马贵者趣，公交自行程序员。
    //                  别人笑我忒疯癫，我笑自己命太贱；
    //                  不见满街漂亮妹，哪个归得程序员？


    /**
     * 登录
     */
    public function Login()
    {
        $data = [
            'username' => $this->request->param('username'),
            'password' => $this->request->param('password'),
        ];
        $rule = [
            'username' => 'require|min:5|max:20',
            'password' => 'require|min:5|max:20',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, '用户名不存在');
        }
        if (md5($this->request->param('password')) != $user->password) {
            return $this->returnJson(400, '密码错误');
        }
        if ($user->banned == 'true') {
            return $this->returnJson(400, '账号已被禁用,封禁理由为:' . $user->banned_reason);
        }
        $nowip = $this->request->ip();
        //判断异地登录邮箱是否开启
        if ($this->app->isemailmsg == 'true') {
            if ($user->ip != $nowip) {
                if ($this->app->emailtitle == '') {
                    $emailresult = Email::get(1);
                    $emailtitle = $emailresult["email_title"];
                } else {
                    $emailtitle = $this->app->emailtitle;
                }
                $address = $this->ip_address($nowip, $user->ip);
                if ($address['code'] == 400) {
                    $emailcontent = "<h3>您的账号在异地登录,登录IP为" . $nowip . "(" . $address['msg'] . ")<br>若是你本人登录，请忽略<br>若不是请及时修改密码</h3>";
                    $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailtitle . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">异地登录提醒</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">若是本人操作可忽略。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailtitle . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                    $this->send_mail($user->useremail, "异地登录提醒", $emailcontenthtml);
                }
            }
        }
        if (!empty($data['device'])) {
            if ($data['device'] != $user->device) {
                if ($this->app->emailtitle == '') {
                    $emailresult = Email::get(1);
                    $emailtitle = $emailresult["email_title"];
                } else {
                    $emailtitle = $this->app->emailtitle;
                }
                $address = $this->ip_address($nowip, $user->ip);
                $emailcontent = "<h3>您的账号在新设备登录,登录IP为" . $nowip . "(" . $address['msg'] . ")<br>若是你本人登录，请忽略<br>若不是请及时修改密码</h3>";
                $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailtitle . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">异地登录提醒</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">若是本人操作可忽略。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailtitle . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                $this->send_mail($user->useremail, "新设备登录提醒", $emailcontenthtml);
                $updateuser = User::where('username', $data['username'])->where('appid', $this->appid)->find();
                $updateuser->device = $data['device'];
                $updateuser->save();
            }
        }
        //判断cookie是否存在
        if (Cookie::has('usertoken')) {
            $usertoken = Cookie::get('usertoken');
            if ($usertoken != $user->user_token) {
                //清楚所有cookie
                Cookie::delete('usertoken');
                Cookie::delete('username');
                Cookie::delete('appid');
                Cookie::clear();
            }
            $result = [
                'username' => $user->username,
                'usertoken' => $usertoken,
                'ip' => $nowip,
            ];
            Cookie::set('usertoken', $usertoken, 3600 * 24 * 30);
            Cookie::set('username', $data['username'], 3600 * 24 * 30);
            Cookie::set('appid', $this->appid, 3600 * 24 * 30);
            User::where('id', $user->id)->update(['user_token' => $usertoken, 'ip' => $nowip]);
            return $this->returnJson(200, '登录成功', $result);
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid" . $this->appid . "username" . $data['username'] . "password" . $data['password'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $usertoken = md5($user->id . $user->username . $user->password . time());
        //判断是否开启sign功能
        $result = [
            'username' => $user->username,
            'usertoken' => $usertoken,
            'ip' => $nowip,
        ];
        Cookie::set('usertoken', $usertoken, 3600 * 24 * 30);
        $updateuser = User::where('id', $user->id)->update(['user_token' => $usertoken, 'ip' => $nowip]);
        Cookie::set('username', $data['username'], 3600 * 24 * 30);
        Cookie::set('appid', $this->appid, 3600 * 24 * 30);
        $this->userLog($this->appid, $data['username'], "登录了账号");
        return $this->returnJson(200, '登录成功', $result);
    }

    /**
     * 注册
     */
    public function register()
    {
        $data = [
            'username' => $this->request->param('username'),
            'password' => $this->request->param('password'),
            'useremail' => $this->request->param('useremail'),
        ];
        $validate = Validate::make([
            'username'  => 'require|alphaNum|length:5,20',
            'password' => 'require',
            'useremail' => 'require|email',
        ]);
        if (!$validate->check($data)) {
            return $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        if ($user) {
            return $this->returnJson(400, "用户名已存在");
        }
        $user = User::where('useremail', $data['useremail'])->where('appid', $this->appid)->find();
        if ($user) {
            return $this->returnJson(400, "邮箱已存在");
        }
        if (substr($data['useremail'], -7) == '@qq.com') {
            $userqq = substr($data['useremail'], 0, strpos($data['useremail'], '@'));
        } else {
            $userqq = '';
        }
        if ($this->app->is_email == 'true') {
            if (empty($this->request->param('regcode'))) {
                return $this->returnJson(400, '邮箱验证码不能为空');
            }
            if (time() - Cookie::get('regcodetime') > 60) {
                return $this->returnJson(400, '验证码已过期');
            }
            if ($this->request->param('regcode') != Cookie::get('regcode')) {
                return $this->returnJson(400, '邮箱验证码错误');
            }
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            if ($this->app->is_email == 'true') {
                $nowsign = $this->app->signkey . "username=" . $data['username'] . "&password=" . $data['password'] . "&useremail=" . $data['useremail'] . "&appid=" . $this->appid . "&regcode=" . $this->request->param('regcode');
                if (md5($nowsign) != $this->request->param('sign')) {
                    return $this->returnJson(400, '签名错误');
                }
            } else {
                $nowsign = $this->app->signkey . "username=" . $data['username'] . "&password=" . $data['password'] . "&useremail=" . $data['useremail'] . "&appid=" . $this->appid;
                if (md5($nowsign) != $this->request->param('sign')) {
                    return $this->returnJson(400, '签名错误');
                }
            }
        }
        if (empty($this->request->has('device'))) {
            $adddata = [
                'username' => $data['username'],
                'password' => md5($data['password']),
                'useremail' => $data['useremail'],
                'appid' => $this->appid,
                'qq' => $userqq,
                'usertx' => \think\facade\Request::domain() . "/" . "usertx.png",
                'viptime' => time() + $this->app->zcvip * 24 * 3600,
                'money' => $this->app->zcmoney,
                'exp' => $this->app->zcexp,
                'creattime' => time(),
            ];
        } else {
            $userdevicenum = User::where('zcdevice', $data['device'])->count();
            if ($this->app->devicenum != 0) {
                if ($this->app->devicenum < $userdevicenum) {
                    return $this->returnJson(400, '此设备注册数量已达上限');
                }
            }
            $adddata = [
                'username' => $data['username'],
                'password' => md5($data['password']),
                'useremail' => $data['useremail'],
                'appid' => $this->appid,
                'qq' => $userqq,
                'usertx' => \think\facade\Request::scheme() . "://" . \think\facade\Request::host() . "/" . "usertx.png",
                'viptime' => time() + $this->app->zcvip * 24 * 3600,
                'money' => $this->app->zcmoney,
                'exp' => $this->app->zcexp,
                'creattime' => time(),
                'driver' => $data['device'],
                'zcdriver' => $data['device'],
            ];
        }
        $user = User::create($adddata);
        Cookie::delete('regcode');
        Cookie::delete('regcodetime');
        return $this->returnJson(200, '注册成功');
    }

    /**
     * 用户注册邮箱验证码
     */
    public function GetRegCode()
    {
        $data = [
            'useremail' => $this->request->param('useremail'),
        ];
        $validate = Validate::make([
            'useremail' => 'require|email',
        ]);
        if (!$validate->check($data)) {
            return $this->returnJson(400, $validate->getError());
        }
        $user = User::where('useremail', $data['useremail'])->where('appid', $this->appid)->find();
        if ($user) {
            return $this->returnJson(400, "邮箱已存在");
        }
        if ($this->app->emailtitle == '') {
            $emailresult = Email::get(1);
            $emailtitle = $emailresult["email_title"];
        } else {
            $emailtitle = $this->app->emailtitle;
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "useremail=" . $data['useremail'] . "&appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        if (Cookie::has('regcode') != false) {
            if (time() - Cookie::get('regcodetime') < 60) {
                return $this->returnJson(400, '60s内只能发送一次');
            }
            $emailcode = $this->getRandChar(4);
            Cookie::set('regcode', $emailcode, 60);
            Cookie::set('regcodetime', time(), 60);
            $emailcontent = "<h3>您的注册验证码是：" . $emailcode . "</h3>";
            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailtitle . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">注册验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span><span style="font-weight: bold;">此验证码一分钟内有效！</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailtitle . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
            $this->send_mail($data['useremail'], "注册验证码", $emailcontenthtml);
            return $this->returnJson(200, "发送成功");
        } else {
            $emailcode = $this->getRandChar(4);
            Cookie::set('regcode', $emailcode, 60);
            Cookie::set('regcodetime', time(), 60);
            $emailcontent = "<h3>您的注册验证码是：" . $emailcode . "</h3>";
            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailtitle . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">注册验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span><span style="font-weight: bold;">此验证码一分钟内有效！</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailtitle . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
            $this->send_mail($data['useremail'], "注册验证码", $emailcontenthtml);
            return $this->returnJson(200, "发送成功");
        }
    }

    /**
     * 找回密码
     */
    public function ResetPassword()
    {
        $data = [
            'username' => $this->request->param('username'),
            'code' => $this->request->param('code'),
        ];
        $validate = Validate::make([
            'username' => 'require|alphaNum|length:5,20',
            'code' => 'require'
        ]);
        if (!$validate->check($data)) {
            return $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        $passcode = Cookie::get('passcode');
        if ($passcode != $data['code']) {
            return $this->returnJson(400, '验证码错误');
        }
        if (time() - Cookie::get('passcodetime') > 60) {
            return $this->returnJson(400, '验证码已过期');
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "username=" . $data['username'] . "&appid=" . $this->appid .  "&code=" . $data['code'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $newpassword = $this->getRandChar(6);
        $updateuser = USER::where('username', $data['username'])->update(['password' => md5($newpassword)]);
        if (!$updateuser) {
            return $this->returnJson(400, '重置密码失败');
        }
        if ($this->app->emailtitle == '') {
            $emailresult = Email::get(1);
            $emailtitle = $emailresult["email_title"];
        } else {
            $emailtitle = $this->app->emailtitle;
        }
        $emailcontent = "<h3>您的随机密码是：" . $newpassword . "<br>请及时修改为您易记的密码</h3>";
        $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailtitle . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">找回密码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的' . $data["username"] . '用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意密码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailtitle . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
        $this->send_mail($user['useremail'], "重置密码", $emailcontenthtml);
        Cookie::set('passcode', null, -60);
        Cookie::set('passcodetime', null, -60);
        $this->userLog($this->appid, $data['username'], "找回密码");
        return $this->returnJson(400, "随机密码已发送到您的邮箱，请注意查看");
    }

    /**
     * 找回密码验证码
     */
    public function GetPasswordCode()
    {
        $data = [
            'username' => $this->request->param('username'),
        ];
        $validate = Validate::make([
            'username' => 'require|alphaNum|length:5,20',
        ]);
        if (!$validate->check($data)) {
            return $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "username=" . $data['username'] . "&appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        if ($this->app->emailtitle == '') {
            $emailresult = Email::get(1);
            $emailtitle = $emailresult["email_title"];
        } else {
            $emailtitle = $this->app->emailtitle;
        }
        $passcode = $this->getRandChar(6);
        if (Cookie::has('passcode') != false) {
            if (time() - Cookie::get('passcodetime') < 60) {
                return $this->returnJson(400, '60s内只能发送一次');
            }
            Cookie::set('regcode', $passcode, 60);
            Cookie::set('regcodetime', time(), 60);
            $emailcontent = "<h3>您的找回密码验证码是：" . $passcode . "</h3>";
            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailtitle . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">注册验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span><span style="font-weight: bold;">此验证码一分钟内有效！</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailtitle . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
            $this->send_mail($user->useremail, "找回密码验证码", $emailcontenthtml);
            return $this->returnJson(200, "发送成功");
        } else {
            Cookie::set('passcode', $passcode, 60);
            Cookie::set('passcodetime', time(), 60);
            $emailcontent = "<h3>您的找回密码验证码是：" . $passcode . "</h3>";
            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>' . $emailtitle . '</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">注册验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">' . $emailcontent . '</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span><span style="font-weight: bold;">此验证码一分钟内有效！</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>' . $emailtitle . '</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
            $this->send_mail($user->useremail, "找回密码验证码", $emailcontenthtml);
            return $this->returnJson(200, "发送成功");
        }
    }

    /**
     * 获取用户信息
     *
     */
    public function getUserInfo()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->field('password,user_token,signtime', true)->find();
        $arr = $this->app->hierarchy;
        foreach (eval("return $arr;") as $key => $value) {
            if ($user->exp >= $key) {
                $hierarchy = $value;
            } else {
                break;
            }
        }
        if ($user->viptime > time()) {
            $user['vipstatus'] = "true";
        } else {
            $user['vipstatus'] = "false";
        }
        $user['viptime'] = date("Y-m-d H:i:s", $user['viptime']);
        $user['creattime'] = date("Y-m-d H:i:s", $user['creattime']);
        $user['hierarchy'] = $hierarchy;
        //评论数量
        $user['commentnum'] = Comment::where('username', Cookie::get('username'))->where('appid', $this->appid)->count();
        //点赞数量
        $user['likenum'] = Likepost::where('username', Cookie::get('username'))->where('appid', $this->appid)->count();
        //帖子数量
        $user['postnum'] = PlatePost::where('username', Cookie::get('username'))->where('appid', $this->appid)->count();
        return $this->returnJson(200, "获取成功", $user);
    }

    /**
     * 直接获取用户信息
     */
    public function getUserInfoByUsername()
    {
        $data = [
            'username' => $this->request->param('username'),
        ];
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->field('password,user_token,signtime', true)->find();
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "username=" . Cookie::get('username') . "&appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $arr = $this->app->hierarchy;
        foreach (eval("return $arr;") as $key => $value) {
            if ($user->exp >= $key) {
                $hierarchy = $value;
            } else {
                break;
            }
        }
        if ($user->viptime > time()) {
            $user['vipstatus'] = "true";
        } else {
            $user['vipstatus'] = "false";
        }
        $user['viptime'] = date("Y-m-d H:i:s", $user['viptime']);
        $user['creattime'] = date("Y-m-d H:i:s", $user['creattime']);
        $user['hierarchy'] = $hierarchy;
        //评论数量
        $user['commentnum'] = Comment::where('username', Cookie::get('username'))->where('appid', $this->appid)->count();
        //点赞数量
        $user['likenum'] = Likepost::where('username', Cookie::get('username'))->where('appid', $this->appid)->count();
        //帖子数量
        $user['postnum'] = PlatePost::where('username', Cookie::get('username'))->where('appid', $this->appid)->count();
        return $this->returnJson(200, "获取成功", $user);
    }

    /**
     * 用户签到
     */
    public function UserSign()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        //获取今天零点的时间戳
        $today = strtotime(date("Y-m-d"));
        if ($user->signtime > $today) {
            return $this->returnJson(400, "今天已经签到过了");
        }
        $addviptime = $this->app->signvip * 24 * 60 * 60;
        if ($user->viptime >= time()) {
            $updateuserdata = [
                'viptime' => $user['viptime'] + $addviptime,
                'money' => $user['money'] + $this->app->signmoney,
                'exp' => $user['exp'] + $this->app->signexp,
                'signtime' => time(),
            ];
        } else {
            $updateuserdata = [
                'viptime' => time() + $addviptime,
                'money' => $user['money'] + $this->app->signmoney,
                'exp' => $user['exp'] + $this->app->signexp,
                'signtime' => time(),
            ];
        }
        $user->save($updateuserdata);
        $this->userLog($this->appid, Cookie::get('username'), "签到成功");
        return $this->returnJson(200, "签到成功");
    }

    /**
     * 修改用户信息
     */
    public function updateUserInfo()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'nickname' => $this->request->param('nickname'),
            'qq' => $this->request->param('qq'),
            'useremail' => $this->request->param('useremail'),
            'signature' => $this->request->param('signature'),
        ];
        $rule = [
            'nickname' => 'require',
            'qq' => 'require|number',
            'useremail' => 'require|email',
            'signature' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&nickname=" . $data['nickname'] . "&qq=" . $data['qq'] . "&useremail=" . $data['useremail'] . "&signature=" . $data['signature'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user->save($data);
        $this->userLog($this->appid, Cookie::get('username'), "修改用户信息");
        return $this->returnJson(200, "修改成功");
    }

    /**
     * 直接修改用户信息
     */
    public function updateUser()
    {
        $data = [
            'nickname' => $this->request->param('nickname'),
            'qq' => $this->request->param('qq'),
            'useremail' => $this->request->param('useremail'),
            'signature' => $this->request->param('signature'),
            'username' => $this->request->param('username'),
            'usertoken' => $this->request->param('usertoken'),
        ];
        $rule = [
            'nickname' => 'require',
            'qq' => 'require|number',
            'useremail' => 'require|email',
            'signature' => 'require',
            'username' => 'require',
            'usertoken' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($user->user_token != $data['usertoken']) {
            return $this->returnJson(400, "用户token错误");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&nickname=" . $data['nickname'] . "&qq=" . $data['qq'] . "&useremail=" . $data['useremail'] . "&signature=" . $data['signature'] . "&username=" . $data['username'] . "&usertoken=" . $data['usertoken'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user->save($data);
        $this->userLog($this->appid, Cookie::get('username'), "修改用户信息");
        return $this->returnJson(200, "修改成功");
    }

    /**
     * 用户头像上传
     */
    public function UploadHead()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        $file = $this->request->file('file');
        if (empty($file)) {
            return $this->returnJson(400, '请选择上传的头像');
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        if ($file) {
            $update = [
                'usertx' => $file['fullPath'],
            ];
            $user->save($update);
            $this->userLog($this->appid, Cookie::get('username'), "修改用户头像");
            return $this->returnJson(200, "上传成功", $file['fullPath']);
        } else {
            return $this->returnJson(400, '上传失败');
        }
    }

    /**
     * 修改密码
     */
    public function updatePassword()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'oldpwd' => $this->request->param('oldpwd'),
            'newpwd' => $this->request->param('newpwd'),
        ];
        $rule = [
            'oldpwd' => 'require',
            'newpwd' => 'require|different:oldpwd',
        ];
        $message = [
            'oldpwd.require' => '旧密码不能为空',
            'newpwd.require' => '新密码不能为空',
            'newpwd.different' => '新密码不能与旧密码一致',
        ];
        $validate = new Validate($rule, $message);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        if ($user->password != md5($data['oldpwd'])) {
            return $this->returnJson(400, "旧密码错误");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&oldpwd=" . $data['oldpwd'] . "&newpwd=" . $data['newpwd'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user->password = md5($data['newpwd']);
        $user->save();
        $this->userLog($this->appid, Cookie::get('username'), "修改密码");
        return $this->returnJson(200, "修改成功");
    }

    /**
     * 用户金币/经验排行榜
     */
    public function UserList()
    {
        $data = [
            'type' => $this->request->param('type'),   //exp / money
        ];
        $rule = [
            'type' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&type=" . $data['type'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user = User::where('appid', $this->appid)->order($data['type'] . ' desc')->field('id,nickname,username,usertx,exp,money,title')->page(1, $this->limit)->select();
        $this->returnJson(200, '获取成功', $user);
    }

    /**
     * 判断用户是否是vip 利用sign签名
     */
    public function isVip()
    {
        $data = [
            'username' => $this->request->param('username'),
        ];
        $rule = [
            'username' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&username=" . $data['username'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        if ($user->viptime >= time()) {
            return $this->returnJson(200, "true", md5($this->app->signkey . "true"));
        } else {
            return $this->returnJson(400, "false", md5($this->app->signkey . "false"));
        }
    }

    /**
     * 填写邀请码
     */
    public function setInviteCode()
    {
        $data = [
            'username' => $this->request->param('username'),
            'invitecode' => $this->request->param('invitecode'),  //其他人的邀请码
        ];
        $rule = [
            'username' => 'require',
            'invitecode' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断邀请码是否存在
        $invite = User::where('invitecode', $data['invitecode'])->where('appid', $this->appid)->find();
        if (empty($invite)) {
            return $this->returnJson(400, "邀请码不存在");
        }
        //不能填写自己的邀请码
        if ($user->invitecode == $data['invitecode']) {
            return $this->returnJson(400, "不能填写自己的邀请码");
        }
        if ($user->inviter != '') {
            return $this->returnJson(400, "已经填写过邀请码了");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&username=" . $data['username'] . "&invitecode=" . $data['invitecode'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        //判断填写用户的会员状态
        if ($user['viptime'] > time()) {
            $viptime = $user['viptime'];
        } else {
            $viptime = time();
        }
        if ($this->app->finvitemoney == '') {
            $finvitemoney = 0;
        }
        if ($this->app->finviteexp == '') {
            $finviteexp = 0;
        }
        if ($this->app->finvitevip == '') {
            $finvitevip = 0;
        }

        if ($this->app->invitemoney == '') {
            $invitemoney = 0;
        }
        if ($this->app->invitevip == '') {
            $invitevip = 0;
        }
        if ($this->app->inviteexp == '') {
            $inviteexp = 0;
        }
        //判断邀请码用户的会员状态
        if ($invite['viptime'] > time()) {
            $inviteviptime = $invite['viptime'];
        } else {
            $inviteviptime = time();
        }
        //自己的
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
        User::where('username', $data['username'])->where('appid', $this->appid)->update($updatefuser);
        User::where('invitecode', $data['invitecode'])->where('appid', $this->appid)->update($updateuser);
        $this->userLog($this->appid, Cookie::get('username'), "填写邀请码");
        return $this->returnJson(200, "填写成功");
    }

    /**
     * 生成邀请码
     */
    public function Getinvitecode()
    {
        //判断是否登录
        if (empty(Cookie::get('username'))) {
            return $this->returnJson(400, "请先登录");
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($user->invitecode != '') {
            return $this->returnJson(400, '已经生成过邀请码');
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&username=" . Cookie::get('username');
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $invitecode = $this->getRandChar(Strlen(Cookie::get('username')));
        //更新用户邀请码
        User::where('username', Cookie::get('username'))->where('appid', $this->appid)->update(['invitecode' => $invitecode]);
        $this->userLog($this->appid, Cookie::get('username'), "生成邀请码");
        return $this->returnJson(200, "生成成功", $invitecode);
    }

    /**
     * 邀请排行榜
     */
    public function GetinviterList()
    {
        $inviterList = User::where('appid', $this->appid)->field('username,nickname,usertx,signature,invitetotal')->order('invitetotal', 'desc')->limit($this->limit)->select();
        return $this->returnJson(200, "获取成功", $inviterList);
    }

    /**
     * 退户登录
     */
    public function Logout()
    {
        //删除COokie
        Cookie::delete('username');
        Cookie::delete('usertoken');
        Cookie::delete('appid');
        $this->userLog($this->appid, Cookie::get('username'), "退出登录");
        return $this->returnJson(200, "退出成功");
    }

    /**
     * 用户操作日志
     */
    public function Getuserlog()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        $userlog = db('userlog')->where('username', Cookie::get('username'))->where('appid', $this->appid)->order('id', 'desc')->limit($this->limit)->page($this->page)->select();
        return $this->returnJson(200, "获取成功", $userlog);
    }

    /*
                       .::::.
                     .::::::::.
                    :::::::::::  FUCK YOU
                ..:::::::::::'
              '::::::::::::'
                .::::::::::
           '::::::::::::::..
                ..::::::::::::.
              ``::::::::::::::::
               ::::``:::::::::'        .:::.
              ::::'   ':::::'       .::::::::.
            .::::'      ::::     .:::::::'::::.
           .:::'       :::::  .:::::::::' ':::::.
          .::'        :::::.:::::::::'      ':::::.
         .::'         ::::::::::::::'         ``::::.
     ...:::           ::::::::::::'              ``::.
    ````':.          ':::::::::'                  ::::..
                       '.:::::'                    ':'````..
*/
    //卡密系统

    /**
     * 卡密系统
     */
    public function UseKm()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'km' => $this->request->param('km'),
        ];
        $rule = [
            'km' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        //判断用户存在
        if (empty($user)) {
            return $this->returnJson(400, "用户不存在");
        }
        $km = Km::where('km', $data['km'])->where('appid', $this->appid)->find();
        if (!$km) {
            return $this->returnJson(400, '卡密不存在');
        }
        if ($km->isuse == 'true') {
            return $this->returnJson(400, '卡密已被使用');
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&km=" . $data['km'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        if ($km->vip == 0 && $km->exp == 0 && $km->money == 0) {
            return $this->returnJson(200, '使用成功', ['vip' => $km->vip, 'exp' => $km->exp, 'money' => $km->money]);
        }
        if ($user->viptime < time()) {
            $updateuser = [
                'viptime' => time() + $km->vip * 24 * 3600,
                'exp' => $user->exp + $km->exp,
                'money' => $user->money + $km->money,
            ];
        } else {
            $updateuser = [
                'viptime' => $user->viptime + $km->vip * 24 * 3600,
                'exp' => $user->exp + $km->exp,
                'money' => $user->money + $km->money,
            ];
        }

        User::where('username', Cookie::get('username'))->where('appid', $this->appid)->update($updateuser);
        $updatekm = [
            'isuse' => 'true',
            'username' => Cookie::get('username'),
            'usetime' => date('Y-m-d H:i:s'),
        ];
        Km::where('km', $data['km'])->where('appid', $this->appid)->update($updatekm);
        $this->userLog($this->appid, Cookie::get('username'), "使用了卡密:" . $data['km']);
        return $this->returnJson(200, "使用成功", ['vip' => $km->vip, 'exp' => $km->exp, 'money' => $km->money]);
    }


    //论坛系统
    /**
     * 获取版块列表
     */
    public function GetPlateList()
    {
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $forumList = Plate::where('appid', $this->appid)->order('id', 'asc')->select();
        return $this->returnJson(200, "获取成功", $forumList);
    }

    /**
     * 获取版块下的帖子列表
     */
    public function GetPostList()
    {
        $data = [
            'plateid' => $this->request->param('plateid'),
        ];
        $rule = [
            'plateid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $plate = Plate::where('id', $data['plateid'])->where('appid', $this->appid)->find();
        if (!$plate) {
            return $this->returnJson(400, '版块不存在');
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&plateid=" . $data['plateid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $this->appid)
            ->where('p.plateid', $data['plateid'])
            ->where('p.is_audit', 0)
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->field("(select count(*) from mr_post where plateid = {$data['plateid']}) as postnum")
            ->order('p.top', 'asc')
            ->order('p.replytime', 'desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "获取成功", $result);
    }

    /**
     * 获取全部帖子
     */
    public function GetAllPostList()
    {
        $result = db('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $this->appid)
            ->where('p.is_audit', 0)
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->field("(select count(*) from mr_post where plateid = p.plateid) as postnum")
            ->order('p.replytime', 'desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "获取成功", $result);
    }

    /**
     * 获取帖子详情
     */
    public function GetPost()
    {
        $data = [
            'postid' => $this->request->param('postid'),
        ];
        $rule = [
            'postid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $post = PlatePost::where('id', $data['postid'])->find();
        if (!$post) {
            return $this->returnJson(400, '帖子不存在');
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&postid=" . $data['postid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $this->appid)
            ->where('p.id', $data['postid'])
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->find();
        $result['posturl'] = "http://" . $_SERVER['HTTP_HOST'] . "/bbs/" . $this->lock_url($data['postid']);
        return $this->returnJson(200, "获取成功", $result);
    }

    /**
     * 新增帖子
     */
    public function AddPost()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'plateid' => $this->request->param('plateid'),
            'postname' => $this->request->param('postname'),
            'postcontent' => $this->request->param('postcontent'),
        ];
        $rule = [
            'plateid' => 'require',
            'postname' => 'require',
            'postcontent' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        $plate = Plate::where('id', $data['plateid'])->where('appid', $this->appid)->find();
        if (!$plate) {
            return $this->returnJson(400, "版块不存在");
        }
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&plateid=" . $data['plateid'] . "&postname=" . $data['postname'] . "&postcontent=" . $data['postcontent'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        $a = json_encode($file);
        $b = json_decode($a);
        $imgurl = '';
        foreach ($b as $v) {
            $imgurl .= $v->fullPath . ",";
        }
        if ($imgurl != '') {
            $imgurl = substr($imgurl, 0, -1);
        }
        $result = db('post')
            ->insert([
                'plateid' => $data['plateid'],
                'postname' => $data['postname'],
                'postcontent' => $data['postcontent'],
                'appid' => $this->appid,
                'username' => Cookie::get('username'),
                'creat_time' => date('Y-m-d H:i:s'),
                'replytime' => date('Y-m-d H:i:s'),
                'file' => $imgurl,
            ]);
        $this->userLog($this->appid, Cookie::get('username'), '新增帖子', $data['postname']);
        return $this->returnJson(200, "发布成功");
    }

    /**
     * 编辑帖子
     */
    public function UpdatePost()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'postid' => $this->request->param('postid'),
            'postname' => $this->request->param('postname'),
            'postcontent' => $this->request->param('postcontent'),
        ];
        $rule = [
            'postid' => 'require',
            'postname' => 'require',
            'postcontent' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        //判断帖子是否存在
        $post = PlatePost::where('id', $data['postid'])->where('appid', $this->appid)->find();
        if (!$post) {
            return $this->returnJson(400, "帖子不存在");
        }
        //判断是否是自己的帖子
        if ($post->username != Cookie::get('username')) {
            return $this->returnJson(400, "不是自己的帖子");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&plateid=" . $data['plateid'] . "&postname=" . $data['postname'] . "&postcontent=" . $data['postcontent'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        $a = json_encode($file);
        $b = json_decode($a);
        $imgurl = '';
        foreach ($b as $v) {
            $imgurl .= $v->fullPath . ",";
        }
        if ($imgurl != '') {
            $imgurl = substr($imgurl, -1);
        }
        $result = db('post')
            ->where('id', $data['postid'])
            ->update([
                'postname' => $data['postname'],
                'postcontent' => $data['postcontent'],
                'file' => $imgurl,
            ]);
        $this->userLog($this->appid, Cookie::get('username'), "修改了帖子:" . $data['postname']);
        return $this->returnJson(200, "编辑成功");
    }

    /**
     * 删除帖子
     */
    public function DeletePost()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'postid' => $this->request->param('postid'),
        ];
        $rule = [
            'postid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        //判断帖子是否存在
        $post = PlatePost::where('id', $data['postid'])->where('appid', $this->appid)->find();
        if (!$post) {
            return $this->returnJson(400, "帖子不存在");
        }
        //判断是否是自己的帖子
        if ($post->username != Cookie::get('username')) {
            return $this->returnJson(400, "不是自己的帖子");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&postid=" . $data['postid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('post')
            ->where('id', $data['postid'])
            ->delete();
        $this->userLog($this->appid, Cookie::get('username'), "修改了帖子:" . $post['postname']);
        return $this->returnJson(200, "删除成功");
    }

    /**
     * 获取用户发表的帖子
     */
    public function GetUserPostList()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $this->appid)
            ->where('p.username', Cookie::get('username'))
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->order('p.replytime', 'desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "获取成功", $result);
    }

    /**
     * 获取帖子评论
     */
    public function GetCommentList()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'postid' => $this->request->param('postid'),
        ];
        $rule = [
            'postid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        //判断帖子是否存在
        $post = PlatePost::where('id', $data['postid'])->where('appid', $this->appid)->find();
        if (!$post) {
            return $this->returnJson(400, "帖子不存在");
        }
        //sign签名
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&postid=" . $data['postid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('comment')
            ->alias('c')
            ->join('plate b', 'b.id = c.plateid')
            ->join('post p', 'p.id = c.postid')
            ->join('app a', 'a.appid = c.appid')
            ->join('user u', 'u.username = c.username')
            ->where('c.postid', $data['postid'])
            ->field('c.*,a.appname,u.nickname,u.usertx,u.title,p.postname,b.platename')
            ->order('c.creattime', 'desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "获取成功", $result);
    }

    /**
     * 新增帖子评论
     */
    public function AddComment()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'postid' => $this->request->param('postid'),
            'comment' => $this->request->param('comment'),
        ];
        $rule = [
            'postid' => 'require',
            'comment' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        //判断帖子是否存在
        $post = PlatePost::where('id', $data['postid'])->where('appid', $this->appid)->find();
        if (!$post) {
            return $this->returnJson(400, "帖子不存在");
        }
        //sign签名
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&postid=" . $data['postid'] . "&comment=" . $data['comment'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('comment')
            ->insert([
                'appid' => $this->appid,
                'username' => Cookie::get('username'),
                'postid' => $data['postid'],
                'comment' => $data['comment'],
                'creattime' => date('Y-m-d H:i:s'),
                'plateid' => $post->plateid,
            ]);
        if ($result) {
            $this->msg_notification(3, $data['postid'], Cookie::get('username'), $result['id'], $post->username, $this->appid, date("Y-m-d H:i:s", time()));
            $this->userLog($this->appid, Cookie::get('username'), "评论了帖子:" . $post['postname']);
            return $this->returnJson(200, "评论成功");
        } else {
            return $this->returnJson(400, "评论失败");
        }
    }

    /**
     * 获取用户自己的评论
     */
    public function GetUserCommentList()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('comment')
            ->alias('c')
            ->join('plate b', 'b.id = c.plateid')
            ->join('post p', 'p.id = c.postid')
            ->join('app a', 'a.appid = c.appid')
            ->join('user u', 'u.username = c.username')
            ->where('c.username', Cookie::get('username'))
            ->field('c.*,a.appname,u.nickname,u.usertx,u.title,p.postname,b.platename')
            ->order('c.creattime', 'desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "获取成功", $result);
    }

    /**
     * 删除评论
     */
    public function DeleteComment()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'commentid' => $this->request->param('commentid'),
        ];
        $rule = [
            'commentid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        //判断评论是否存在
        $comment = Comment::where('id', $data['commentid'])->where('appid', $this->appid)->find();
        if (!$comment) {
            return $this->returnJson(400, "评论不存在");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&commentid=" . $data['commentid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('comment')
            ->where('id', $data['commentid'])
            ->delete();
        if ($result) {
            $this->userLog($this->appid, Cookie::get('username'), "删除了评论:" . $comment['comment']);
            return $this->returnJson(200, "删除成功");
        } else {
            return $this->returnJson(400, "删除失败");
        }
    }

    /**
     * 搜索帖子
     */
    public function SearchPost()
    {
        $data = [
            'keyword' => $this->request->param('keyword'),
        ];
        $rule = [
            'keyword' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&keyword=" . $data['keyword'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $this->appid)
            ->where('p.postname', 'like', '%' . $data['keyword'] . '%')
            ->whereOr('p.postcontent', 'like', '%' . $data['keyword'] . '%')
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->order('p.replytime', 'desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "获取成功", $result);
    }

    /**
     * 点赞帖子
     */
    public function LikePost()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'postid' => $this->request->param('postid'),
        ];
        $rule = [
            'postid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        //判断帖子是否存在
        $post = PlatePost::where('id', $data['postid'])->where('appid', $this->appid)->find();
        if (!$post) {
            return $this->returnJson(400, "帖子不存在");
        }
        //判断是否已点赞
        $likepost = Likepost::where('postid', $data['postid'])->where('username', Cookie::get('username'))->find();
        if ($likepost) {
            return $this->returnJson(400, "已点赞");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&postid=" . $data['postid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('likepost')
            ->insert([
                'appid' => $this->appid,
                'postid' => $data['postid'],
                'username' => Cookie::get('username'),
                'creattime' => date("Y-m-d H:i:s"),
                'plateid' => $post->plateid,
            ]);
        if ($result) {
            $this->userLog($this->appid, Cookie::get('username'), "点赞了帖子:" . $post['postname']);
            $this->msg_notification(2, $data['postid'], Cookie::get('username'), '', $post->username, $this->appid, date("Y-m-d H:i:s", time()));
            return $this->returnJson(200, "点赞成功");
        } else {
            return $this->returnJson(400, "点赞失败");
        }
    }

    /**
     * 取消点赞帖子
     */
    public function CancelLikePost()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'postid' => $this->request->param('postid'),
        ];
        $rule = [
            'postid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        //判断帖子是否存在
        $post = PlatePost::where('id', $data['postid'])->where('appid', $this->appid)->find();
        if (!$post) {
            return $this->returnJson(400, "帖子不存在");
        }
        //判断是否已点赞
        $likepost = Likepost::where('postid', $data['postid'])->where('username', Cookie::get('username'))->find();
        if (!$likepost) {
            return $this->returnJson(400, "未点赞");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&postid=" . $data['postid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('likepost')
            ->where('postid', $data['postid'])
            ->where('username', Cookie::get('username'))
            ->delete();
        if ($result) {
            $this->userLog($this->appid, Cookie::get('username'), "取消点赞了帖子:" . $post['postname']);
            return $this->returnJson(200, "取消点赞成功");
        } else {
            return $this->returnJson(400, "取消点赞失败");
        }
    }

    /**
     * 获取用户点赞帖子列表
     */
    public function GetLikePostList()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('likepost')
            ->alias('l')
            ->join('plate b', 'l.plateid = b.id')
            ->join('post p', 'l.postid = p.id')
            ->join('app a', 'l.appid = a.appid')
            ->join('user u', 'l.username = u.username')
            ->where('l.appid', $this->appid)
            ->where('l.username', Cookie::get('username'))
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "获取成功", $result);
    }

    /**
     * 判断是否点赞帖子
     */
    public function IsLikePost()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'postid' => $this->request->param('postid'),
        ];
        $rule = [
            'postid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            return $this->returnJson(400, "用户不存在");
        }
        //判断usertoken是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, "用户token错误");
        }
        //判断帖子是否存在
        $post = PlatePost::where('id', $data['postid'])->where('appid', $this->appid)->find();
        if (!$post) {
            return $this->returnJson(400, "帖子不存在");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&postid=" . $data['postid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        //判断是否已点赞
        $likepost = Likepost::where('postid', $data['postid'])->where('username', Cookie::get('username'))->find();
        if ($likepost) {
            return $this->returnJson(200, "已点赞", true);
        } else {
            return $this->returnJson(400, "未点赞", false);
        }
    }

    //应用公告
    /**
     * 获取应用公告
     */
    public function GetAppNotice()
    {
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = [
            "appname" => $this->app->appname,
            'title' => $this->app->title,
            'content' => $this->app->content,
        ];
        return $this->returnJson(200, "查询成功", $result);
    }

    /**
     * 获取应用更新信息
     */
    public function GetAppUpdate()
    {
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = [
            "appname" => $this->app->appname,
            "version" => $this->app->version,
            "updatecontent" => $this->app->updatecontent,
            "download" => $this->app->download,
        ];
        return $this->returnJson(200, "查询成功", $result);
    }

    /**
     * 获取应用信息
     */
    public function GetAppInfo()
    {
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = [
            "appicon" => $this->app->appicon,
            'appname' => $this->app->appname,
            'introduction' => $this->app->introduction,
            'author' => $this->app->author,
            'group' => $this->app->group,
            'view' => $this->app->view,
        ];
        $result['online'] = db('useronline')->count();
        return $this->returnJson(200, "查询成功", $result);
    }

    /**
     * 增加应用访问量
     */
    public function AddAppView()
    {
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $app = App::where('appid', $this->appid)->find();
        db('app')->where('appid', $this->appid)->update(['view' => $app->view + 1]);
        return $this->returnJson(200, "访问成功");
    }

    //商城系统
    /**
     * 获取商城商品列表
     */
    public function GetShopList()
    {
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('shop')
            ->alias('s')
            ->where('s.appid', $this->appid)
            ->join('app a', 'a.appid = s.appid')
            ->field('s.id,s.shopname,s.shoptype,s.money,s.vipnum,s.inventory,s.sales,s.shopimg,s.shopcontent,a.appname')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "查询成功", $result);
    }

    /**
     * 获取商城商品详情
     */
    public function GetShopInfo()
    {
        $data = [
            'shopid' => $this->request->param('shopid'),
        ];
        $rule = [
            'shopid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&shopid=" . $this->request->param('shopid');
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('shop')
            ->alias('s')
            ->where('s.appid', $this->appid)
            ->join('app a', 'a.appid = s.appid')
            ->field('s.id,s.shopname,s.shoptype,s.money,s.vipnum,s.inventory,s.sales,s.shopimg,s.shopcontent,a.appname')
            ->find();
        return $this->returnJson(200, "查询成功", $result, $this->meta);
    }

    /**
     * 购买商品
     */
    public function BuyShop()
    {
        //判断是否登录
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'shopid' => $this->request->param('shopid'),
        ];
        $rule = [
            'shopid' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&shopid=" . $this->request->param('shopid');
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user = db('user')->where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (empty($user)) {
            return $this->returnJson(400, "没有此用户");
        }
        //判断token是否相等
        if ($user["user_token"] != Cookie::get('usertoken')) {
            return $this->returnJson(400, "token错误");
        }
        $shop = db('shop')->where('id', $this->request->param('shopid'))->find();
        if (empty($shop)) {
            return $this->returnJson(400, "没有此商品");
        }
        if ($shop["inventory"] <= $this->request->param('num')) {
            return $this->returnJson(400, '库存不足');
        }
        if ($user["money"] < $shop['money']) {
            return $this->returnJson(400, '金币不足');
        }
        if ($shop["shoptype"] == 1) {
            $shoptype = "会员类型";
        } else {
            $shoptype = "其他类型";
        }
        $intoshoporderdata = [
            'username' => Cookie::get('username'),
            'shopname' => $shop['shopname'],
            'shoptype' => $shoptype,
            'appid' => $this->appid,
            'creat_time' => date("Y-m-d H:i:s", time()),
            'shopid' => $data['shopid'],
        ];
        if ($shop['shoptype'] == 2) {
            User::where('username', Cookie::get('username'))->where('appid', $this->appid)->update(['money' => $user['money'] - $shop['money']]);
            Shop::where('id', $data['shopid'])->update(['sales' => $shop['sales'] + 1, 'inventory' => $shop['inventory'] - 1]);
            Shoporder::create($intoshoporderdata);
            $this->userLog($this->appid, Cookie::get('username'), "购买商品:" . $shop['shopname']);
            return $this->returnJson(200, "购买成功", $shop["shopresult"]);
        } else {
            if ($user['viptime'] > time()) {
                User::where('username', Cookie::get('username'))->where('appid', $this->appid)->update(['money' => $user['money'] - $shop['money'], 'viptime' => $user['viptime'] + $shop['vipnum'] * 24 * 3600]);
                Shop::where('id', $data['shopid'])->update(['sales' => $shop['sales'] + 1, 'inventory' => $shop['inventory'] - 1]);
                Shoporder::create($intoshoporderdata);
                $this->userLog($this->appid, Cookie::get('username'), "购买商品:" . $shop['shopname']);
                return $this->returnJson(200, "购买成功");
            } else {
                User::where('username', Cookie::get('username'))->where('appid', $this->appid)->update(['money' => $user['money'] - $shop['money'], 'viptime' =>  time() + $shop['vipnum'] * 24 * 3600]);
                Shop::where('id', $data['shopid'])->update(['sales' => $shop['sales'] + 1, 'inventory' => $shop['inventory'] - 1]);
                Shoporder::create($intoshoporderdata);
                $this->userLog($this->appid, Cookie::get('username'), "购买商品:" . $shop['shopname']);
                return $this->returnJson(200, "购买成功");
            }
        }
    }

    /**
     * 购买商品记录
     */
    public function UserShopOrder()
    {
        //判断是否登录
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user = db('user')->where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (empty($user)) {
            return $this->returnJson(400, "没有此用户");
        }
        $result = db('shoporder')
            ->alias('s')
            ->where('s.appid', $this->appid)
            ->where('s.username', Cookie::get('username'))
            ->join('app a', 'a.appid = s.appid')
            ->join('shop b', 'b.id = s.shopid')
            ->field('s.*,a.appname,b.shopresult')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        $this->returnJson(200, "获取成功", $result);
    }

    //笔记管理
    /**
     * 添加笔记
     */
    public function addNotes()
    {
        //判断是否登录
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'title' => $this->request->param('title'),
            'content' => $this->request->param('content'),
        ];
        $rule = [
            'title' => 'require',
            'content' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&title=" . $this->request->param('title') . "&content=" . $this->request->param('content');
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user = db('user')->where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (empty($user)) {
            return $this->returnJson(400, "没有此用户");
        }
        //判断usertoken是否正确
        if ($user['user_token'] != Cookie::get('usertoken')) {
            return $this->returnJson(400, "usertoken错误");
        }
        $adddata = [
            'username' => Cookie::get('username'),
            'title' => $data['title'],
            'content' => $data['content'],
            'ip' => Common::get_user_ip(),
            'creattime' => date("Y-m-d H:i:s", time()),
            'updatetime' => date("Y-m-d H:i:s", time()),
            'appid' => $this->appid,
        ];
        $result = Notes::create($adddata);
        if ($result) {
            $this->userLog($this->appid, Cookie::get('username'), "添加了笔记:" . $data['title']);
            return $this->returnJson(200, "添加成功");
        } else {
            return $this->returnJson(400, "添加失败");
        }
    }

    /**
     * 删除笔记
     */
    public function deleteNotes()
    {
        //判断是否登录
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'id' => $this->request->param('id'),
        ];
        $rule = [
            'id' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&id=" . $this->request->param('id');
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user = db('user')->where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (empty($user)) {
            return $this->returnJson(400, "没有此用户");
        }
        //判断usertoken是否正确
        if ($user['user_token'] != Cookie::get('usertoken')) {
            return $this->returnJson(400, "usertoken错误");
        }
        //判断是否存在此笔记
        $notes = Notes::where('id', $data['id'])->where('appid', $this->appid)->find();
        if (empty($notes)) {
            return $this->returnJson(400, "没有此笔记");
        }
        //判断是否是自己的笔记
        if ($notes['username'] != Cookie::get('username')) {
            return $this->returnJson(400, "不是自己的笔记");
        }
        $result = Notes::destroy($data['id']);
        if ($result) {
            $this->userLog($this->appid, Cookie::get('username'), "删除了笔记:" . $notes['title']);
            return $this->returnJson(200, "删除成功");
        } else {
            return $this->returnJson(400, "删除失败");
        }
    }

    /**
     * 修改笔记
     */
    public function updateNotes()
    {
        //判断是否登录
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'id' => $this->request->param('id'),
            'title' => $this->request->param('title'),
            'content' => $this->request->param('content'),
        ];
        $rule = [
            'id' => 'require',
            'title' => 'require',
            'content' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&id=" . $this->request->param('id') . "&title=" . $this->request->param('title') . "&content=" . $this->request->param('content');
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user = db('user')->where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (empty($user)) {
            return $this->returnJson(400, "没有此用户");
        }
        //判断usertoken是否正确
        if ($user['user_token'] != Cookie::get('usertoken')) {
            return $this->returnJson(400, "usertoken错误");
        }
        //判断是否存在此笔记
        $notes = Notes::where('id', $data['id'])->where('appid', $this->appid)->find();
        if (empty($notes)) {
            return $this->returnJson(400, "没有此笔记");
        }
        //判断是否是自己的笔记
        if ($notes['username'] != Cookie::get('username')) {
            return $this->returnJson(400, "不是自己的笔记");
        }
        $updata = [
            'title' => $data['title'],
            'content' => $data['content'],
            'updatetime' => date("Y-m-d H:i:s", time()),
            'ip' => $this->request->ip(),
        ];
        $result = Notes::where('id', $data['id'])->where('appid', $this->appid)->update($updata);
        if ($result) {
            $this->userLog($this->appid, Cookie::get('username'), "修改了笔记:" . $data['title']);
            return $this->returnJson(200, "修改成功");
        } else {
            return $this->returnJson(400, "修改失败");
        }
    }

    /**
     * 获取自己笔记列表
     */
    public function getNotesList()
    {
        //判断是否登录
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $user = db('user')->where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (empty($user)) {
            return $this->returnJson(400, "没有此用户");
        }
        //判断usertoken是否正确
        if ($user['user_token'] != Cookie::get('usertoken')) {
            return $this->returnJson(400, "usertoken错误");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $notes = db('notes')
            ->alias('n')
            ->where('n.username', Cookie::get('username'))
            ->where('n.appid', $this->appid)
            ->join('app a', 'a.appid = n.appid')
            ->field('n.*,a.appname')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, "获取成功", $notes);
    }

    /**
     * 获取笔记详情
     */
    public function getNotesInfo()
    {
        $data = [
            'id' => $this->request->param('id'),
        ];
        $rule = [
            'id' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        //判断是否存在此笔记
        $notes = Notes::where('id', $data['id'])->where('appid', $this->appid)->find();
        if (empty($notes)) {
            return $this->returnJson(400, "没有此笔记");
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&id=" . $this->request->param('id');
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $notes['notesurl'] = "http://" . $_SERVER['HTTP_HOST'] . "/notes/" . $this->lock_url($notes['id']);
        return $this->returnJson(200, "获取成功", $notes);
    }

    /**
     * 消息系统
     * 获取最新的一条信息
     */
    public function getNewMessage()
    {
        $data = [
            'username' => $this->request->param('username'),
        ];
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&username=" . $data['username'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $message = Db::name('message')
            ->where('isread', 0)
            ->where('appid', $this->appid)
            ->where('username', $data['username'])
            ->whereOr('username', 0)
            ->order('id desc')
            ->find();
        $messagecount = Db::name('message')
            ->where('isread', 0)
            ->where('appid', $this->appid)
            ->where('username', $data['username'])
            ->whereOr('username', 0)
            ->order('id desc')
            ->count();
        //查询sql语句
        $returnmsgalert = [];
        switch ($message['msgid']) {
            case '1':
                $returnmsgalert['msgtype'] = "系统消息";
                $returnmsgalert['msgcontent'] = $message['content'];
                $returnmsgalert['creattime'] = $message['creattime'];
                $returnmsg[] = $returnmsgalert;
                break;
            case '2':
                $returnmsgalert['msgtype'] = "点赞信息";
                $post = PlatePost::get($message['postid']);
                $returnmsgalert['msgcontent'] = "用户" . $message['userid'] . "点赞了您的文章[" . $post->postname . "]";
                $returnmsg[] = $returnmsgalert;
                break;
            case '3':

                $returnmsgalert['msgtype'] = "评论信息";
                $post = PlatePost::get($message['postid']);
                $comment = Comment::get($message['commentid']);
                $returnmsgalert['msgcontent'] = "用户" . $message['userid'] . "评论了您的文章[" . $post->postname . "],评论内容为" . $comment->comment;
                $returnmsg[] = $returnmsgalert;
                break;
        }
        return $this->returnJson(200, $messagecount, $message);
    }

    /**
     * 消息系统
     * 获取消息列表
     * @return \think\response\Json
     */
    public function getMessageList()
    {
        //判断是否登录
        if (empty(Cookie::get('username'))) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'msgtype' => $this->request->param('msgtype'),
        ];
        $rule = [
            'msgtype' => 'number',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&msgtype=" . $data['msgtype'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        switch ($data['msgtype']) {
            case '0': //全部
                $message = Message::where('appid', $this->appid)->where('username', Cookie::get('username'))->whereOr('username', 0)->order('id desc')->limit($this->limit)->page($this->page)->select();
                Message::where('appid', $this->appid)->where('username', Cookie::get('username'))->whereOr('username', 0)->update(['isread' => 1]);
                //循环遍历信息
                $returnmsgalert = [];
                foreach ($message as $key => $value) {
                    if ($message[$key]['msgid'] == 1) {
                        $returnmsgalert['msgtype'] = "系统消息";
                        $returnmsgalert['msgcontent'] = $message[$key]['content'];
                        $returnmsgalert['creattime'] = $message[$key]['creattime'];
                    } else if ($message[$key]['msgid'] == 2) {
                        $returnmsgalert['msgtype'] = "点赞信息";
                        $post = PlatePost::get($message[$key]['postid']);
                        $returnmsgalert['msgcontent'] = "用户" . $message[$key]['userid'] . "点赞了您的文章[" . $post->postname . "]";
                        $returnmsgalert['creattime'] = $message[$key]['creattime'];
                    } else {
                        $returnmsgalert['msgtype'] = "评论信息";
                        $post = PlatePost::get($message[$key]['postid']);
                        $comment = Comment::get($message[$key]['commentid']);
                        $returnmsgalert['msgcontent'] = "用户" . $message[$key]['userid'] . "评论了您的文章[" . $post->postname . "],评论内容为" . $comment->comment;
                        $returnmsgalert['creattime'] = $message[$key]['creattime'];
                    }
                    $returnmsg[] = $returnmsgalert;
                }
                return $this->returnJson(200, "查询成功", $returnmsg);
                break;
            case '1': //系统消息
                $message = Message::where('username', 0)->where('appid', $this->appid)->where('msgid', 1)->order('id desc')->limit($this->limit)->page($this->page)->select();
                Message::where('username', 0)->where('appid', $this->appid)->where('msgid', 1)->update(['isread' => 1]);
                //循环遍历信息
                $returnmsgalert = [];
                foreach ($message as $key => $value) {
                    $returnmsgalert['msgtype'] = "系统消息";
                    $returnmsgalert['msgcontent'] = $message[$key]['content'];
                    $returnmsgalert['creattime'] = $message[$key]['creattime'];
                    $returnmsg[] = $returnmsgalert;
                }
                return $this->returnJson(200, "查询成功", $returnmsg);
                break;
            case '2': //点赞信息
                $message = Message::where('username', Cookie::get('username'))->where('appid', $this->appid)->where('msgid', 2)->order('id desc')->limit($this->limit)->page($this->page)->select();
                Message::where('username', Cookie::get('username'))->where('appid', $this->appid)->where('msgid', 2)->update(['isread' => 1]);
                //循环遍历信息
                $returnmsgalert = [];
                foreach ($message as $key => $value) {
                    $returnmsgalert['msgtype'] = "点赞信息";
                    $post = PlatePost::get($message[$key]['postid']);
                    $returnmsgalert['msgcontent'] = "用户" . $message[$key]['userid'] . "点赞了您的文章[" . $post->postname . "]";
                    $returnmsg[] = $returnmsgalert;
                }
                return $this->returnJson(200, "查询成功", $returnmsg);
                break;
            case '3': //评论信息
                $message = Message::where('username', Cookie::get('username'))->where('appid', $this->appid)->where('msgid', 3)->order('id desc')->limit($this->limit)->page($this->page)->select();
                Message::where('username', Cookie::get('username'))->where('appid', $this->appid)->where('msgid', 3)->update(['isread' => 1]);
                //循环遍历信息
                $returnmsgalert = [];
                foreach ($message as $key => $value) {
                    $returnmsgalert['msgtype'] = "评论信息";
                    $post = PlatePost::get($message[$key]['postid']);
                    $comment = Comment::get($message[$key]['commentid']);
                    $returnmsgalert['msgcontent'] = "用户" . $message[$key]['userid'] . "评论了您的文章[" . $post->postname . "],评论内容为" . $comment->comment;
                    $returnmsg[] = $returnmsgalert;
                }
                return $this->returnJson(200, "查询成功", $returnmsg);
                break;
            default:
                return $this->returnJson(400, '获取失败');
                break;
        }
    }

    /**
     * 管理员密钥
     * 充值会员
     */
    public function adminkeyvip()
    {
        $data = [
            'username' => $this->request->param('username'),
            'adminkey' => $this->request->param('adminkey'),
            'vipday' => $this->request->param('vipday'),
        ];
        $rule = [
            'username' => 'require',
            'adminkey' => 'require',
            'vipday' => 'require|number',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        if (!$user) {
            $this->returnJson(400, '用户不存在');
        }
        $admin = Admin::get('1');
        if ($admin->admintoken != $data['adminkey']) {
            $this->returnJson(400, '密钥错误');
        }
        if ($user['viptime'] > time()) {
            $userviptime = $user['viptime'];
        } else {
            $userviptime = time();
        }
        //sign签名判断
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&username=" . $data['username'] . "&vipday=" . $data['vipday'] . "&adminkey=" . $data['adminkey'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $viptime = $userviptime + $data['vipday'] * 24 * 3600;
        $user->viptime = $viptime;
        $user->save();
        return $this->returnJson(200, '操作成功');
    }

    /**
     * 管理员密钥
     * 充值金币
     */
    public function adminkeymoney()
    {
        $data = [
            'username' => $this->request->param('username'),
            'adminkey' => $this->request->param('adminkey'),
            'money' => $this->request->param('money'),
        ];
        $rule = [
            'username' => 'require',
            'adminkey' => 'require',
            'money' => 'require|number',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        if (!$user) {
            $this->returnJson(400, '用户不存在');
        }
        $admin = Admin::get('1');
        if ($admin->admintoken != $data['adminkey']) {
            $this->returnJson(400, '密钥错误');
        }
        //sign签名判断
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&username=" . $data['username'] . "&money=" . $data['money'] . "&adminkey=" . $data['adminkey'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user->money = $user->money + $data['money'];
        $user->save();
        return $this->returnJson(200, '操作成功');
    }

    /**
     * 管理员密钥
     * 充值经验
     */
    public function adminkeyexp()
    {
        $data = [
            'username' => $this->request->param('username'),
            'adminkey' => $this->request->param('adminkey'),
            'exp' => $this->request->param('exp'),
        ];
        $rule = [
            'username' => 'require',
            'adminkey' => 'require',
            'exp' => 'require|number',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $user = User::where('username', $data['username'])->where('appid', $this->appid)->find();
        if (!$user) {
            $this->returnJson(400, '用户不存在');
        }
        $admin = Admin::get('1');
        if ($admin->admintoken != $data['adminkey']) {
            $this->returnJson(400, '密钥错误');
        }
        //sign签名判断
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&username=" . $data['username'] . "&exp=" . $data['exp'] . "&adminkey=" . $data['adminkey'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $user->exp = $user->exp + $data['exp'];
        $user->save();
        return $this->returnJson(200, '操作成功');
    }

    /**
     * 获取每个板块的未审核数据
     */
    public function GetunAuditpost()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $plate = Plate::where('appid', $this->appid)->where('admin', Cookie::get('username'))->find();
        if (!$plate) {
            return $this->returnJson(400, "您没有权限");
        }
        $user = User::where('username', Cookie::get('username'))->where('appid', $this->appid)->find();
        if (!$user) {
            $this->returnJson(400, '用户不存在');
        }
        //判断token是否正确
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, 'token错误');
        }
        //sign签名判断
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid;
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $result = db('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $this->appid)
            ->where('p.plateid', $plate['id'])
            ->where('p.is_audit', 1)
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->order('p.top', 'asc')
            ->order('p.replytime', 'desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        return $this->returnJson(200, '获取成功', $result);
    }

    /**
     * 管理员审核帖子
     */
    public function Auditpost()
    {
        if (!Cookie::has('username')) {
            return $this->returnJson(400, "请先登录");
        }
        $data = [
            'postid' => $this->request->param('postid'),
        ];
        $rule = [
            'postid' => 'require|number',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $post = PlatePost::get($data['postid']);
        if (!$post) {
            $this->returnJson(400, '帖子不存在');
        }
        $user = User::where('username', $post->username)->where('appid', $this->appid)->find();
        if (!$user) {
            $this->returnJson(400, '用户不存在');
        }
        if ($user->user_token != Cookie::get('usertoken')) {
            return $this->returnJson(400, 'token错误');
        }
        $plate = Plate::where('appid', $this->appid)->where('admin', Cookie::get('username'))->find();
        if (!$plate) {
            return $this->returnJson(400, "您没有权限");
        }
        if ($plate['id'] != $post->plateid) {
            return $this->returnJson(400, "您不是该板块的管理员");
        }
        //sign签名判断
        if ($this->app->issign == 'true') {
            if (empty($this->request->param('sign'))) {
                return $this->returnJson(400, '签名不能为空');
            }
            $nowsign = $this->app->signkey . "appid=" . $this->appid . "&postid=" . $data['postid'];
            if (md5($nowsign) != $this->request->param('sign')) {
                return $this->returnJson(400, '签名错误');
            }
        }
        $post->is_audit = 0;
        $post->save();
        return $this->returnJson(200, '操作成功');
    }

    /**
     * 邮箱发送
     * @param string $toEmail 发送到邮箱
     * @param string $emailTitle 发送邮箱标题
     * @param string $emailContent 发送邮箱内容
     */
    public function Sendemail()
    {
        $data = [
            'toEmail' => $this->request->param('toemail'),
            'emailTitle' => $this->request->param('emailtitle'),
            'emailContent' => $this->request->param('emailcontent'),
        ];
        $rule = [
            'toEmail' => 'require|email',
            'emailTitle' => 'require',
            'emailContent' => 'require',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if (!$result) {
            $this->returnJson(400, $validate->getError());
        }
        $result = $this->send_mail($data['toEmail'], $data['emailTitle'], $data['emailContent']);
        if ($result) {
            return $this->returnJson(200, '发送成功');
        } else {
            return $this->returnJson(400, '发送失败');
        }
    }

}
