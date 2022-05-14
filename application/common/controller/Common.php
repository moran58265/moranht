<?php

namespace app\common\controller;

use app\admin\model\Email;
use PHPMailer\PHPMailer\PHPMailer;
use think\Controller;
use think\Db;

class Common
{
    //封装json返回
    public static function ReturnJson($msg, $data = [])
    {
        $result = [
            'code' => 200,
            'msg' => $msg,
            'data' => $data,
        ];
        return json($result);
    }

    //成功返回不带数据
    /**
     * @param $msg
     */
    public static function ReturnSuccess($msg)
    {
        $result = [
            'code' => 200,
            'msg' => $msg,
        ];
        return json($result);
    }

    //失败返回
    public static function ReturnError($msg)
    {
        $result = [
            'code' => 400,
            'msg' => $msg,
        ];
        return json($result);
    }

    //随机成成字符串
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

    //邮箱发送
    /**
     * 发送邮箱
     * @param string $toEmail 发送到邮箱
     * @param string $emailTitle 发送邮箱标题
     * @param string $emailContent 发送邮箱内容
     * @return \think\response\Json
     */
    public static function send_mail($toEmail, $emailTitle, $emailContent)
    {
        $result = Email::get(1);
        $mail = new Phpmailer();
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
        //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用**
        if (!$mail->send()) { // 发送邮件
            return Common::ReturnError('发送失败');
        } else {
            return Common::ReturnSuccess('发送成功');
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
            $invitacode_info = Db::name('user')->where('invitecode', $invitacode)->find();
            //判断邀请码是否存在
            if(empty($invitacode_info)){
                break;
            }else{
                $invitacode = Common::getRandChar($length);
            }
        }
        return $invitacode;
    }
}
