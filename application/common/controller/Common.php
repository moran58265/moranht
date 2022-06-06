<?php

namespace app\common\controller;

use app\admin\model\Email;
use PHPMailer\PHPMailer\PHPMailer;
use think\Controller;
use think\Db;

class Common
{
    /**
     * @param $code
     * @param $msg
     * @param array $data
     * @return Json [json] 返回就是json数据
     */
    public static function return_msg($code, $msg, $data = [])
    {
        $return_data['code'] = $code;
        $return_data['msg'] = $msg;
        $return_data['data'] = $data;
        return json($return_data);
    }

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
    public static function send_mail($toEmail, $emailTitle = '', $emailContent = '')
    {
        try {
            $result = Db::name('email')->where('id', 1)->find();
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
            //配置html格式发送
            $mail->isHTML(true);
            //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用**
            if (!$mail->send()) { // 发送邮件
                return Common::ReturnError(400, $mail->ErrorInfo);
            } else {
                return Common::ReturnSuccess(200, '发送成功');
            }
        } catch (\Exception $exception) {
            return Common::ReturnError(400, $exception);
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
            if (empty($invitacode_info)) {
                break;
            } else {
                $invitacode = Common::getRandChar($length);
            }
        }
        return $invitacode;
    }


    //判断ip是否在某个范围内
    public static function ip_address($nowip, $dbip)
    {
        header("Content-type: text/html; charset=utf-8");
        //访问api接口获取ip地址http://ip-api.com/json/ip地址?lang=zh-CN
        //获取当前ip所在的省份
        // $nowurl = "http://ip-api.com/json/" . $nowip . "?lang=zh-CN";
        // $nowurl = "http://whois.pconline.com.cn/ipJson.jsp?callback=testJson&ip=".$nowip;
        // $nowipaddres = file_get_contents($nowurl);
        // $nowipaddresarr = json_decode($nowipaddres, true);
        try {
            $nowurl = "http://whois.pconline.com.cn/jsAlert.jsp?callback=testJson&ip=" . $nowip;
            $nowipaddres = file_get_contents($nowurl);
            $nowhtml = iconv("gb2312", "utf-8//IGNORE", $nowipaddres);
            $nowaddres = mb_substr($nowhtml, 9, -4);
            $dburl = "http://whois.pconline.com.cn/jsAlert.jsp?callback=testJson&ip=" . $nowip;
            $dbipaddres = file_get_contents($dburl);
            $dbhtml = iconv("gb2312", "utf-8//IGNORE", $dbipaddres);
            $dbaddres = mb_substr($dbhtml, 9, -4);
            if ($nowaddres == $dbaddres) {
                return ["code" => 200, "msg" => $nowaddres];
            } else {
                return ["code" => 400, "msg" => $nowaddres];
            }
        } catch (\Exception $exception) {
            return ["code" => 400, "msg" => "未知ip"];
        }
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

    //加密函数  
    public static function lock_url($txt, $key = 'morannn')
    {
        $txt = $txt . $key;
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
        $nh = rand(0, 64);
        $ch = $chars[$nh];
        $mdKey = md5($key . $ch);
        $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
        $txt = base64_encode($txt);
        $tmp = '';
        $i = 0;
        $j = 0;
        $k = 0;
        for ($i = 0; $i < strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = ($nh + strpos($chars, $txt[$i]) + ord($mdKey[$k++])) % 64;
            $tmp .= $chars[$j];
        }
        return urlencode(base64_encode($ch . $tmp));
    }
    //解密函数  
    public static function unlock_url($txt, $key = 'morannn')
    {
        $txt = base64_decode(urldecode($txt));
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
        $ch = $txt[0];
        $nh = strpos($chars, $ch);
        $mdKey = md5($key . $ch);
        $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
        $txt = substr($txt, 1);
        $tmp = '';
        $i = 0;
        $j = 0;
        $k = 0;
        for ($i = 0; $i < strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
            while ($j < 0) $j += 64;
            $tmp .= $chars[$j];
        }
        return trim(base64_decode($tmp), $key);
    }
}
