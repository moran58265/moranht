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
use app\common\controller\Common;

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
            return Common::ReturnError($validate->getError());
        }
        try {
            $AppResult = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (Exception $exception) {
            return Common::ReturnError($exception->getMessage());
        }
        if ($AppResult == "" || $AppResult == null) {
            return Common::ReturnError("没有此app");
        } else {
            try {
                $UserResult = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
                $emailresult = Db::name('email')->where('id', 1)->find();
            } catch (DataNotFoundException $e) {
                return Common::ReturnError("请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::ReturnError("请求失败");
            } catch (DbException $e) {
                return Common::ReturnError("请求失败");
            }
            if ($UserResult == "" || $UserResult == null) {
                return Common::ReturnError("没有此账号");
            } else {
                if ($UserResult['password'] == md5($data['password'])) {
                    $UserToken = md5($data['username'] . time());
                    $result = [
                        'username' => $data['username'],
                        'UserToken' => $UserToken,
                        'ip' => Common::get_user_ip(),
                    ];
                    $ipaddress = Common::ip_address(Common::get_user_ip(),$UserResult['ip']);
                    if ($ipaddress['code'] == 400) {
                        $emailcontent = "<h3>您的账号在异地登录,登录IP为" . Common::get_user_ip(). "(" .$ipaddress['msg'] . ")<br>若是你本人登录，请忽略<br>若不是请及时修改密码</h3>";
                        $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>'.$emailresult["email_title"].'</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">异地登录提醒</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的'.$data["username"].'用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">'.$emailcontent.'</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">若是本人操作可忽略。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>'.$emailresult["email_title"].'</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                        try {
                            $msg = Common::send_mail($UserResult['useremail'], "异地登录提醒", $emailcontenthtml);
                        } catch (DataNotFoundException $e) {
                            return Common::ReturnError("请求失败");
                        } catch (ModelNotFoundException $e) {
                            return Common::ReturnError("请求失败");
                        } catch (DbException $e) {
                            return Common::ReturnError("请求失败");
                        }
                    }
                    try {
                        Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update(['user_token' => $UserToken, 'ip' => Common::get_user_ip()]);
                    } catch (PDOException $e) {
                        return Common::ReturnError("请求失败");
                    } catch (Exception $e) {
                        return Common::ReturnError("请求失败");
                    }
                    return Common::ReturnJson("登录成功", $result);
                } else {
                    return Common::ReturnError("密码错误");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $emailcode = Db::name('emailcode')->where('id', 1)->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $app['appid'])->find();
            $useremail = Db::name('user')->where('useremail', $data['useremail'])->where('appid', $app['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == null || $app == "") {
            return Common::ReturnError("app不存在");
        } else {
            if ($app['is_email'] == 'true') {
                $validate = Validate::make([
                    'emailcode' => 'require',
                ]);
                if (!$validate->check($data)) {
                    return Common::ReturnError($validate->getError());
                }
                if ($user == null || $user == "") {
                    if ($useremail != null || $useremail != "") {
                        return Common::ReturnError("邮箱已存在");
                    }
                    $emailregcode = Session::get('emailcode');
                    if ($emailcode['emailcode'] == $data['emailcode']) {
                        //if ($emailregcode == $data['emailcode']) {
                        $adddata = [
                            'username' => $data['username'],
                            'password' => md5($data['password']),
                            'useremail' => $data['useremail'],
                            'appid' => $data['appid'],
                            'qq' => substr($data['useremail'], 0, strpos($data['useremail'], '@')),
                            'usertx' => \think\facade\Request::scheme() . "://" . \think\facade\Request::host() . "/" . "usertx.png",
                            'viptime' => time() + ($app['zcvip'] * 24 * 3600),
                            'money' => $app['zcmoney'],
                            'exp' => $app['zcexp'],
                            'creattime' => time(),
                        ];
                        $user = Db::name('user')->data($adddata)->insert();
                        if ($user > 0) {
                            Db::name('emailcode')->where('id', 1)->update(["emailcode" => "", "ip" => "", "creat_time" => ""]);
                            return Common::ReturnSuccess('注册成功');
                        } else {
                            return Common::ReturnError('注册失败');
                        }
                    } else {
                        return Common::ReturnError("验证码错误");
                    }
                } else {
                    return Common::ReturnError("账号已存在");
                }
            } else {
                if ($user == null || $user == "") {
                    if ($useremail != null || $useremail != "") {
                        return Common::ReturnError("邮箱已存在");
                    }
                    $adddata = [
                        'username' => $data['username'],
                        'password' => md5($data['password']),
                        'useremail' => $data['useremail'],
                        'appid' => $data['appid'],
                        'qq' => substr($data['useremail'], 0, strpos($data['useremail'], '@')),
                        'usertx' => \think\facade\Request::scheme() . "://" . \think\facade\Request::host() . "/" . "usertx.png",
                        'viptime' => time() + ($app['zcvip'] * 24 * 3600),
                        'money' => $app['zcmoney'],
                        'exp' => $app['zcexp'],
                        'creattime' => time(),
                    ];
                    $user = Db::name('user')->data($adddata)->insert();
                    if ($user > 0) {

                        return Common::ReturnSuccess('注册成功');
                    } else {
                        return Common::ReturnError('注册失败');
                    }
                } else {
                    return Common::ReturnError("账号已存在");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $ckcodetime = Db::name('passcode')->where('id', 1)->find();
            $emailresult = Db::name('email')->where('id', 1)->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::ReturnError(400, "没有该用户");
        }
        if ($ckcodetime['ip'] == Common::get_user_ip()) {
            if (time() - $ckcodetime['creattime'] < 60) {
                return Common::ReturnError("60s内只能发送一次");
            } else {
                $passcode = Common::getRandChar(4);
                $updatapasscode = [
                    'passcode' => $passcode,
                    'creattime' => time(),
                    'ip' => Common::get_user_ip(),
                ];
                try {
                    Db::name('passcode')->where('id', 1)->update($updatapasscode);
                } catch (PDOException $e) {
                    return Common::ReturnError("请求失败");
                } catch (Exception $e) {
                    return Common::ReturnError("请求失败");
                }
                try {
                    $emailcontent = "<h3>您要找回密码的验证码是：" . $passcode ."<br>若不是本人操作请警觉</h3>";
                    $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>'.$emailresult["email_title"].'</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">找回密码验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的'.$data["username"].'用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">'.$emailcontent.'</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>'.$emailresult["email_title"].'</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                    Common::send_mail($user['useremail'], "找回密码验证码", $emailcontenthtml);
                    return Common::ReturnSuccess("发送成功");
                } catch (DataNotFoundException $e) {
                    return Common::ReturnError("请求失败");
                } catch (ModelNotFoundException $e) {
                    return Common::ReturnError("请求失败");
                } catch (DbException $e) {
                    return Common::ReturnError("请求失败");
                }
            }
        } else {
            $passcode = Common::getRandChar(4);
            $updatapasscode = [
                'passcode' => $passcode,
                'creattime' => time(),
                'ip' => Common::get_user_ip(),
            ];
            try {
                Db::name('passcode')->where('id', 1)->update($updatapasscode);
            } catch (PDOException $e) {
                return Common::ReturnError("请求失败");
            } catch (Exception $e) {
                return Common::ReturnError("请求失败");
            }
            try {
                $emailcontent = "<h3>您要找回密码的验证码是：" . $passcode ."<br>若不是本人操作请警觉</h3>";
                $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>'.$emailresult["email_title"].'</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">找回密码验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的'.$data["username"].'用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">'.$emailcontent.'</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>'.$emailresult["email_title"].'</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                Common::send_mail($user['useremail'], "找回密码验证码", $emailcontenthtml);
                return Common::ReturnSuccess("发送成功");
            } catch (DataNotFoundException $e) {
                return Common::ReturnError("请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::ReturnError("请求失败");
            } catch (DbException $e) {
                return Common::ReturnError("请求失败");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('useremail', $data['useremail'])->where('appid', $data['appid'])->find();
            $emailresult = Db::name('email')->where('id', 1)->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == null || $app == "") {
            return Common::ReturnError("app不存在");
        } else {
            if ($user == null || $user == "") {
                try {
                    $ckcodetime = Db::name('emailcode')->where('id', 1)->find();
                } catch (DataNotFoundException $e) {
                    return Common::ReturnError("请求失败");
                } catch (ModelNotFoundException $e) {
                    return Common::ReturnError("请求失败");
                } catch (DbException $e) {
                    return Common::ReturnError("请求失败");
                }

                if ($ckcodetime['ip'] == Common::get_user_ip()) {
                    if (time() - $ckcodetime['creat_time'] > 60) {
                        $emailcode = Common::getRandChar(4);
                        $updataemailcode = [
                            'emailcode' => $emailcode,
                            'creat_time' => time(),
                            'ip' => Common::get_user_ip(),
                        ];
                        try {
                            Db::name('emailcode')->where('id', 1)->update($updataemailcode);
                        } catch (PDOException $e) {
                            return Common::ReturnError("请求失败");
                        } catch (Exception $e) {
                            return Common::ReturnError("请求失败");
                        }
                        try {
                            $emailcontent = "<h3>您的注册验证码是：". $emailcode."</h3>";
                            $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>'.$emailresult["email_title"].'</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">注册验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">'.$emailcontent.'</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>'.$emailresult["email_title"].'</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                            Common::send_mail($data['useremail'], "注册验证码", $emailcontenthtml);
                            return Common::ReturnSuccess("发送成功");
                        } catch (DataNotFoundException $e) {
                            return Common::ReturnError("请求失败");
                        } catch (ModelNotFoundException $e) {
                            return Common::ReturnError("请求失败");
                        } catch (DbException $e) {
                            return Common::ReturnError("请求失败");
                        }
                    } else {
                        return Common::ReturnError("60s内只能发送一次");
                    }
                } else {
                    $emailcode = Common::getRandChar(4);
                    $updataemailcode = [
                        'emailcode' => $emailcode,
                        'creat_time' => time(),
                        'ip' => Common::get_user_ip(),
                    ];
                    try {
                        Db::name('emailcode')->where('id', 1)->update($updataemailcode);
                    } catch (PDOException $e) {
                        return Common::ReturnError("请求失败");
                    } catch (Exception $e) {
                        return Common::ReturnError("请求失败");
                    }
                    try {
                        $emailcontent = "<h3>您的注册验证码是：". $emailcode."</h3>";
                        $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>'.$emailresult["email_title"].'</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">注册验证码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">'.$emailcontent.'</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意验证码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>'.$emailresult["email_title"].'</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                        Common::send_mail($data['useremail'], "注册验证码", $emailcontenthtml);
                        return Common::ReturnSuccess("发送成功");
                    } catch (DataNotFoundException $e) {
                        return Common::ReturnError("请求失败");
                    } catch (ModelNotFoundException $e) {
                        return Common::ReturnError("请求失败");
                    } catch (DbException $e) {
                        return Common::ReturnError("请求失败");
                    }
                }
            } else {
                return Common::ReturnError("你注册的邮箱已经存在了");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }

        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        } else {
            if ($user == "" || $user == null) {
                return Common::ReturnError("没有此账号");
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
                        return Common::ReturnError("请求失败");
                    } catch (ModelNotFoundException $e) {
                        return Common::ReturnError("请求失败");
                    } catch (DbException $e) {
                        return Common::ReturnError("请求失败");
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
                    return Common::ReturnError("token错误");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $ckcode = Db::name('passcode')->where('id', 1)->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::ReturnError("没有该用户");
        }
        if ($ckcode['passcode'] != $data['code']) {
            return Common::ReturnError("验证码错误");
        }
        $newpassword = Common::getRandChar(6);
        try {
            $updateuser = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update(['password' => md5($newpassword)]);
        } catch (PDOException $e) {
            return Common::ReturnError("请求失败");
        } catch (Exception $e) {
            return Common::ReturnError("请求失败");
        }
        if ($updateuser > 0) {
            try {
                $emailresult = Db::name('email')->where('id', 1)->find();
                $emailcontent = "<h3>您的随机密码是：". $newpassword."<br>请及时修改为您易记的密码</h3>";
                $emailcontenthtml = '<div><includetail><div align="center"><div class="open_email"style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;"><div><br><span class="genEmailContent"><div id="cTMail-Wrap"style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;"><div class="main-content"style=""><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse"><tbody><tr style="font-weight:300"><td style="width:3%;max-width:30px;"></td><td style="max-width:600px;"><h1>'.$emailresult["email_title"].'</h1><p style="height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;"></p><div id="cTMail-inner"style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;"><table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;"><tbody><tr style="font-weight:300"><td style="width:3.2%;max-width:30px;"></td><td style="max-width:480px;text-align:left;"><h1 id="cTMail-title"style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">找回密码</h1><p id="cTMail-userName"style="font-size:14px;color:#333; line-height:24px; margin:0;">尊敬的'.$data["username"].'用户，您好！</p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;">'.$emailcontent.'</span></p><p class="cTMail-content"style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;"><span style="color: rgb(51, 51, 51); font-size: 14px;"><span style="font-weight: bold;">请注意密码的大小写。</span></span></p><dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;"><dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;"><p id="cTMail-sender"style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">此致<br><strong>'.$emailresult["email_title"].'</strong></p></dd></dl></td><td style="width:3.2%;max-width:30px;"></td></tr></tbody></table></div></td><td style="width:3%;max-width:30px;"></td></tr></tbody></table></div></div></span><br></div></div></div></includetail></div>';
                return Common::send_mail($data['useremail'], "注册验证码", $emailcontenthtml);
                //Common::send_mail($user['useremail'], "随机密码", "您的随机密码是：" . $newpassword);
                Db::name('passcode')->where('id', 1)->update(["passcode" => "", "ip" => "", "creattime" => ""]);
            } catch (DataNotFoundException $e) {
                return Common::ReturnError("请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::ReturnError("请求失败");
            } catch (DbException $e) {
                return Common::ReturnError("请求失败");
            }
            return Common::return_msg(200, '随机密码已发送到您的邮箱，请注意查看');
        } else {
            return Common::ReturnError('修改失败');
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::ReturnError("没有该用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::ReturnError("token过期");
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
            return Common::ReturnError($e);
        } catch (Exception $e) {
            return Common::ReturnError($e);
        }
        if ($user > 0) {
            return Common::return_msg(200, '修改成功');
        } else {
            return Common::ReturnError('你未做任何修改！');
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::ReturnError("没有该用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::ReturnError("token过期");
        }
        $file = $upload->uploadDetail('file');
        try {
            Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update(['usertx' => $file["fullPath"]]);
        } catch (PDOException $e) {
            return Common::ReturnError("请求失败");
        } catch (Exception $e) {
            return Common::ReturnError("请求失败");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        }
        if (empty($data['limit'])) {
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
                return Common::ReturnError("请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::ReturnError("请求失败");
            } catch (DbException $e) {
                return Common::ReturnError("请求失败");
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
                return Common::ReturnError("请求失败");
            } catch (ModelNotFoundException $e) {
                return Common::ReturnError("请求失败");
            } catch (DbException $e) {
                return Common::ReturnError("请求失败");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::ReturnError("没有该用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::ReturnError("token过期");
        }
        list($start, $end) = Time::today();
        if ($user['signtime'] > $start) {
            return Common::ReturnError("您今天已经签到过了");
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
            return Common::ReturnError("请求失败");
        } catch (Exception $e) {
            return Common::ReturnError("请求失败");
        }
        if ($sql > 0) {
            return Common::return_msg(200, '签到成功');
        } else {
            return Common::ReturnError('签到失败');
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::ReturnError("没有该用户");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::ReturnError("没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::ReturnError("没有该用户");
        }
        if ($user['password'] != md5($data['oldpwd'])) {
            return Common::ReturnError("旧密码错误");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::ReturnError("token过期");
        }
        $result = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->update(['password' => md5($data['newpwd'])]);
        if ($result > 0) {
            return Common::return_msg(200, "修改成功");
        } else {
            return Common::ReturnError("修改失败");
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
            return Common::ReturnError($validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $invitecode = Db::name('user')->where('invitecode', $data['invitecode'])->find();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError("请求失败");
        } catch (DbException $e) {
            return Common::ReturnError("请求失败");
        }
        if ($user == "" || $user == null) {
            return Common::ReturnError("没有该用户");
        }
        if ($user['inviter'] != "") {
            return Common::ReturnError("已经填写过邀请码");
        }
        if ($invitecode == "" || $invitecode == null) {
            return Common::ReturnError("没有该邀请码");
        }
        if($user['invitecode'] == $data['invitecode']){
            return Common::ReturnError("不能填写自己的邀请码");
        }
        //判断用户会员状态
        if ($user['viptime'] > time()) {
            $viptime = $user['viptime'];
        } else {
            $viptime = time();
        }
        if ($invitecode['viptime'] > time()) {
            $invitecodeviptime = $invitecode['viptime'];
        } else {
            $invitecodeviptime = time();
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
                'viptime' => $invitecodeviptime + $app['invitevip'] * 3600 * 24,
                'exp' => $invitecode['exp'] + $app['inviteexp'],
            ]);
        return Common::return_msg(200, "填写成功");
    }

    /**
     * 生成邀请码
     *
     * @param Request $request
     * @return void
     */
    public function Getinvitecode(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::ReturnError($validate->getError());
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
            return Common::ReturnError("已经生成过邀请码");
        }
    }

    /**
     * 获取邀请码
     *
     * @param Request $request
     * @return void
     */
    public function GetinviterList(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'limit' => 'number',
            'order' => 'alpha',
        ]);
        if (!$validate->check($data)) {
            return Common::ReturnError($validate->getError());
        }
        $app = Db::name('app')->where('appid', $data['appid'])->find();
        if($app == ""){
            return Common::ReturnError("没有该app");
        }
        $order = isset($data['order']) ? $data['order'] : "asc";
        $limit = isset($data['limit']) ? $data['limit'] : "10";
        $inviterList = Db::name('user')->field('username,nickname,usertx,signature,invitetotal')->where('appid', $data['appid'])->order('invitetotal',$order)->limit($limit)->select();
        return Common::return_msg(200, "获取成功", $inviterList);
    }
}
