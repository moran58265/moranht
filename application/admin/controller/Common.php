<?php

namespace app\admin\controller;

use app\admin\model\Email;
use app\admin\model\User;
use think\facade\Session;

class Common {
    /**
     * 成功返回
     *
     * @param string $msg
     */
    public static function ReturnSuccess($msg = '操作成功') {
        return json(['code' => 200, 'msg' => $msg]);
    }
    /**
     * 失败返回
     *
     * @param string $msg
     */
    public static function ReturnError($msg = '操作失败') {
        return json(['code' => 400, 'msg' => $msg]);
    }
    /**
     * 成功带数据返回
     *
     * @param [type] $length
     */
    public static function ReturnSuccessData($data = [], $msg = '操作成功') {
        return json(['code' => 200, 'msg' => $msg, 'data' => $data]);
    }
    /**
     * 随机字符串
     *
     * @param [type] $length
     */
    public static function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];
        }
        return $str;
    }
    /**
     * 记录管理员操作日志
     */
    public static function adminLog($msg) {
        $admin = session::get('admininfo');
        $data = [
            'adminname' => $admin['username'],
            'msg' => $msg,
            'ip' => request()->ip(),
            'creattime' => date('Y-m-d H:i:s')
        ];
        db('adminlog')->insert($data);
    }

    /**
     * 上传图片类，支持多图上传  作为公共类使用
     */
    public static function upload(){
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        return $file;
    }

    //获取当个ip所在的省份
    public static function get_ip_address($ip)
    {
        //访问api接口获取ip地址http://ip-api.com/json/ip地址?lang=zh-CN
        //获取当前ip所在的省份
        $url = "http://whois.pconline.com.cn/jsAlert.jsp?callback=testJson&ip=" . $ip;
        try {
            $ipaddres = file_get_contents($url);
            $iphtml = iconv("gb2312", "utf-8//IGNORE", $ipaddres);
            $addres = mb_substr($iphtml, 9, -4);
            return $addres;
        } catch (\Exception $e) {
            return '未知ip';
        }
    }


    /**
     * parm $username 用户账号
     * 邀请码生成
     */
    public static function getinvitacode($username)
    {
        //获取用户账号的长度
        $length = strlen($username);
        if ($length > 8) {
            $length = 8;
        }
        while (true) {
            $invitacode = Common::getRandChar($length);
            //查询邀请码是否存在
            $invitacode_info = User::where('invitecode', $invitacode)->find();
            //判断邀请码是否存在
            if (empty($invitacode_info)) {
                break;
            } else {
                $invitacode = Common::getRandChar($length);
            }
        }
        return $invitacode;
    }

    /**
     * 发送邮箱
     * @param string $toEmail 发送到邮箱
     * @param string $emailTitle 发送邮箱标题
     * @param string $emailContent 发送邮箱内容
     * @return \think\response\Json
     */
    public static function send_mail($toEmail, $emailTitle = '', $emailContent = '')
    {
        try {
            $result = Email::get(1);
            $mail = new \PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP(); // 使用SMTP服务（发送邮件的服务）
            $mail->CharSet = "utf8"; // 编码格式为utf8，不设置编码的话，中文会出现乱码
            $mail->Host = $result['mail_way']; // 发送方的SMTP服务器地址
            $mail->SMTPAuth = true; // 是否使用身份验证
            $mail->Username = $result['username']; // 申请了smtp服务的邮箱名（自己的邮箱名）
            $mail->Password = $result['password']; // 发送方的邮箱密码，不是登录密码,是qq的第三方授权登录码,要自己去开启（之前叫你保存的那个密码）
            $mail->SMTPSecure = "ssl"; // 使用ssl协议方式,
            $mail->Port = $result['port']; // QQ邮箱的ssl协议方式端口号是465/587
            $mail->setFrom($result['username'], $result['email_title']);
            // 设置发件人信息，如邮件格式说明中的发件人,
            $mail->addAddress($toEmail); // 设置收件人信息，如邮件格式说明中的收件人
            //$mail->addReplyTo($test_email['email_user'],"Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
            $mail->Subject = $emailTitle; // 邮件标题
            $mail->Body = $emailContent; // 邮件正文
            //配置html格式发送
            $mail->isHTML(true);
            //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用**
            if (!$mail->send()) { // 发送邮件
                return Common::ReturnError($mail->ErrorInfo);
            } else {
                return Common::ReturnSuccess('发送成功');
            }
        } catch (\Exception $exception) {
            return Common::ReturnError($exception);
        }
    }


}