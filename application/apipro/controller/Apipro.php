<<<<<<< HEAD
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
=======
<?php ?><?php /* 2659917175 */ ?><?php
if(!function_exists('sg_load')){$__v=phpversion();$__x=explode('.',$__v);$__v2=$__x[0].'.'.(int)$__x[1];$__u=strtolower(substr(php_uname(),0,3));$__ts=(@constant('PHP_ZTS') || @constant('ZEND_THREAD_SAFE')?'ts':'');$__f=$__f0='ixed.'.$__v2.$__ts.'.'.$__u;$__ff=$__ff0='ixed.'.$__v2.'.'.(int)$__x[2].$__ts.'.'.$__u;$__ed=@ini_get('extension_dir');$__e=$__e0=@realpath($__ed);$__dl=function_exists('dl') && function_exists('file_exists') && @ini_get('enable_dl') && !@ini_get('safe_mode');if($__dl && $__e && version_compare($__v,'5.2.5','<') && function_exists('getcwd') && function_exists('dirname')){$__d=$__d0=getcwd();if(@$__d[1]==':') {$__d=str_replace('\\','/',substr($__d,2));$__e=str_replace('\\','/',substr($__e,2));}$__e.=($__h=str_repeat('/..',substr_count($__e,'/')));$__f='/ixed/'.$__f0;$__ff='/ixed/'.$__ff0;while(!file_exists($__e.$__d.$__ff) && !file_exists($__e.$__d.$__f) && strlen($__d)>1){$__d=dirname($__d);}if(file_exists($__e.$__d.$__ff)) dl($__h.$__d.$__ff); else if(file_exists($__e.$__d.$__f)) dl($__h.$__d.$__f);}if(!function_exists('sg_load') && $__dl && $__e0){if(file_exists($__e0.'/'.$__ff0)) dl($__ff0); else if(file_exists($__e0.'/'.$__f0)) dl($__f0);}if(!function_exists('sg_load')){$__ixedurl='http://www.sourceguardian.com/loaders/download.php?php_v='.urlencode($__v).'&php_ts='.($__ts?'1':'0').'&php_is='.@constant('PHP_INT_SIZE').'&os_s='.urlencode(php_uname('s')).'&os_r='.urlencode(php_uname('r')).'&os_m='.urlencode(php_uname('m'));$__sapi=php_sapi_name();if(!$__e0) $__e0=$__ed;if(function_exists('php_ini_loaded_file')) $__ini=php_ini_loaded_file(); else $__ini='php.ini';if((substr($__sapi,0,3)=='cgi')||($__sapi=='cli')||($__sapi=='embed')){$__msg="\nPHP script '".__FILE__."' is protected by SourceGuardian and requires a SourceGuardian loader '".$__f0."' to be installed.\n\n1) Download the required loader '".$__f0."' from the SourceGuardian site: ".$__ixedurl."\n2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="\n3) Edit ".$__ini." and add 'extension=".$__f0."' directive";}}$__msg.="\n\n";}else{$__msg="<html><body>PHP script '".__FILE__."' is protected by <a href=\"http://www.sourceguardian.com/\">SourceGuardian</a> and requires a SourceGuardian loader '".$__f0."' to be installed.<br><br>1) <a href=\"".$__ixedurl."\" target=\"_blank\">Click here</a> to download the required '".$__f0."' loader from the SourceGuardian site<br>2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="<br>3) Edit ".$__ini." and add 'extension=".$__f0."' directive<br>4) Restart the web server";}}$__msg.="</body></html>";}die($__msg);exit();}}return sg_load('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');
>>>>>>> 65397660d776cb795cd7b8980daef3f614b34c5c
