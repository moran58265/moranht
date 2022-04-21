<?php

namespace app\api\controller;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\response\Json;

class Common
{
    /**
     * @param $code
     * @param $msg
     * @param array $data
     * @return Json [json] 返回就是json数据
     */
    public static function return_msg($code,$msg,$data =[]){
        $return_data['code'] = $code;
        $return_data['msg'] = $msg;
        $return_data['data'] = $data;
        return json($return_data);
    }

    /**
     * 生成随机字符串
     * @param $n
     * @return string
     */
    public static function getchar($n)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    /**
     * 发送邮箱
     * @param string $toEmail 发送到邮箱
     * @param string $emailTitle 发送邮箱标题
     * @param string $emailContent 发送邮箱内容
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public static function send_mail($toEmail,$emailTitle = '',$emailContent = '') {
        try {
            $result = Db::name('email')->where('id', 1)->find();
            $mail=new Phpmailer();
            $mail->isSMTP();// 使用SMTP服务（发送邮件的服务）
            $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
            $mail->Host = $result['mail_way'];// 发送方的SMTP服务器地址
            $mail->SMTPAuth = true;// 是否使用身份验证
            $mail->Username = $result['username'];// 申请了smtp服务的邮箱名（自己的邮箱名）
            $mail->Password = $result['password'];// 发送方的邮箱密码，不是登录密码,是qq的第三方授权登录码,要自己去开启（之前叫你保存的那个密码）
            $mail->SMTPSecure = "ssl";// 使用ssl协议方式,
            $mail->Port = $result['port'];// QQ邮箱的ssl协议方式端口号是465/587
            $mail->setFrom($result['username'],$result['email_title']);
            // 设置发件人信息，如邮件格式说明中的发件人,
            $mail->addAddress($toEmail);// 设置收件人信息，如邮件格式说明中的收件人
            //$mail->addReplyTo($test_email['email_user'],"Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
            $mail->Subject = $emailTitle;// 邮件标题
            $mail->Body = $emailContent;// 邮件正文
            //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用**
            if(!$mail->send()){// 发送邮件
                return Common::return_msg(400,$mail->ErrorInfo);
            }else {
                return Common::return_msg(200, '发送成功');
            }
        }catch (Exception $exception){
            return Common::return_msg(400,$exception);
        }
    }

    /**
     * 获取用户真实IP
     * @param int $type
     * @param bool $adv
     * @return mixed
     */
    public static function get_user_ip($type = 0, $adv = true)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim(current($arr));
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


}