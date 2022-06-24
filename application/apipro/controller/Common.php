<?php

namespace app\apipro\controller;

use app\admin\model\App;
use app\admin\model\Email;
use app\admin\model\Message;
use app\admin\model\User;
use think\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use think\facade\Cookie;

class Common extends Controller
{
    public function __construct()
    {
        $timemap = time();
        parent::__construct();
        $this->AuthWeb();   //验证此域名是否授权
        $this->limit = $this->request->param('limit', 10, 'intval');  //每页显示条数
        $this->page = $this->request->param('page', 1, 'intval');   //当前页数
        $this->appid = $this->request->param('appid', '', 'intval');   //appid
        $this->expire = $this->request->param('expire', '180', 'intval');   //请求有效期
        if ($this->appid == '') {
            echo self::returnJson(400, 'appid不能为空');
            exit;
        }
        $this->app = App::get($this->appid);
        if (!$this->app) {
            echo self::returnJson(400, 'appid不存在');
            exit;
        }
        //验证时间戳是否过期
        if (time() - $timemap > $this->expire) {
            echo json_encode(['code' => 400, 'msg' => '请求已过期', 'data' => [], 'time' => time()]);
            exit;
        }
        //在线人数统计
        $this->NowOnline();
    }

    /**
     * 网站授权
     */
    public function AuthWeb()
    {
        ini_set("user_agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0)");
        $file = "auth.txt";
        $authfile = './static/js/fullcalendar/bootstrap/auth.txt';
        if (!file_exists($file)) {
            if (file_exists($authfile)) {
                unlink($authfile);
            }
        }
        if (file_exists($authfile)) {
            $a = filectime($authfile);
            $creattimefile = date("Y-m-d H:i:s", $a);
            if (time() - strtotime($creattimefile) > 3600) {
                //超过12小时则删除授权文件
                unlink($authfile);
                unlink($file);
            }
            //获取授权文件内容
            $auth = file_get_contents($file);
            $json = json_decode($auth, true);
            if ($json['domain'] != $this->request->host()) {
                echo json_encode(array('code' => 400, 'msg' => '授权域名不正确,请联系管理员qq：2659917175'));
                exit;
            }
            if (strtotime($json['duetime']) < time()) {
                echo json_encode(array('code' => 400, 'msg' => '授权已过期,请联系管理员qq：2659917175'));
                exit;
            }
            $auth = file_get_contents($authfile);
            $dejson = self::decrypt($auth);
            $json = json_decode($dejson, true);
            if ($json['domain'] != $this->request->host()) {
                echo json_encode(array('code' => 400, 'msg' => '授权域名不正确,请联系管理员qq：2659917175'));
                exit;
            }
            if (self::decrypt($json['duetime']) < time()) {
                echo json_encode(array('code' => 400, 'msg' => '授权已过期,请联系管理员qq：2659917175'));
                exit;
            }
        } else {
            //获取域名
            $domain = $this->request->host();
            //远程访问接口判断是否授权
            $stream_opts = [
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ]
            ];
            $response = file_get_contents("http://ht.moranblog.cn/authweb.php?domain=" . $domain, false, stream_context_create($stream_opts));
            $data = json_decode($response, true);
            if ($data['code'] == 1) {
                //生成数组并写入文件
                $myfile = fopen("auth.txt", "w");
                $authtwo = fopen("./static/js/fullcalendar/bootstrap/auth.txt", "w");
                $arr = [
                    'domain' => $domain,
                    'duetime' => $this->encrypt(strtotime($data['data']['duetime'])),
                    'createtime' => time(),
                ];
                $arra = [
                    'domain' => $domain,
                    'duetime' => $data['data']['duetime'],
                    'createtime' => $data['data']['creattime'],
                ];
                $myfile = fopen("auth.txt", "w");
                fwrite($myfile, json_encode($arra));
                fwrite($authtwo, Common::encrypt(json_encode($arr)));
                fclose($myfile);
            } else {
                echo json_encode(array('code' => 400, 'msg' => '暂无授权，请联系管理员qq：2659917175'));
                exit;
            }
        }
    }

    /**
     * 定义返回格式
     */
    public static function returnJson($code, $msg, $data = [])
    {
        $arr = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'time' => time(),
        ];
        echo json_encode($arr);
        exit;
    }

    /**
     * 加密
     */
    public static function encrypt($string)
    {
        $operation = 'EDECODE';
        $expiry = 12 * 3600;
        //密文有效期。 如果为0，密文将不会被自动删除
        $key = md5('moranblog.cn');
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙     
        $ckey_length = 16;
        // 密匙     
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
        // 密匙a会参与加解密     
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证     
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文     
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙     
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，   
        //解密时会通过这个密匙验证数据完整性     
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确     
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :  sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度     
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分     
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符     
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式     
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因     
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码     
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * 解密
     */
    public static function decrypt($string)
    {
        $operation = 'DECODE';
        $expiry = 12 * 3600;
        //密文有效期。 如果为0，密文将不会被自动删除
        $key = md5('moranblog.cn');
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙     
        $ckey_length = 16;
        // 密匙     
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
        // 密匙a会参与加解密     
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证     
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文     
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙     
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，   
        //解密时会通过这个密匙验证数据完整性     
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确     
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :  sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度     
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分     
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符     
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式     
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因     
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码     
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * 判断ip是否在某个网段内
     *
     * @param [type] $nowip
     * @param [type] $dbip
     */
    public static function ip_address($nowip, $dbip)
    {
        header("Content-type: text/html; charset=utf-8");
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
                return self::returnJson(400, $mail->ErrorInfo);
            } else {
                return self::returnJson(200, '发送成功');
            }
        } catch (\Exception $exception) {
            return self::returnJson(400, $exception);
        }
    }


    /**
     * 随机验证码
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
     * 用户日志方法
     */
    public static function userLog($appid, $username, $msg)
    {
        $add = [
            'appid' => $appid,
            'username' => $username,
            'msg' => $msg,
            'creattime' => date('Y-m-d H:i:s', time()),
            'ip' => self::get_user_ip(),
        ];
        db('userlog')->insert($add);
    }

    public static function lock_url($txt, $key = 'morannn')
    { //加密
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

    //在线人数统计
    public function NowOnline()
    {
        $update_time = time();
        db('useronline')->where('update_time', '<', time())->delete();
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
            $update_time = time();
            $user = User::where('username', $data['username'])->where('appid', $data['appid'])->find();
            if ($user) {
                $fupuser = db('useronline')->where('user_id', $user['id'])->where('appid', $data['appid'])->find();
                if ($fupuser) {
                    db('useronline')->where('user_id', $user['id'])->where('appid', $data['appid'])->update(['update_time' => $update_time]);
                } else {
                    db('useronline')->insert(['user_id' => $user['id'], 'appid' => $data['appid'], 'update_time' => $update_time]);
                }
            }
        }
    }

    /**
     * 消息通知系统
     * msgid 消息类型 1为系统信息 2为点赞消息 3为评论消息 
     * @param [type] $msgid  消息类型
     * @param [type] $postid  文章id
     * @param [type] $userid  用户名
     * @param [type] $commentid  评论id
     * @param [type] $username 通知用户
     * @param [type] $appid 应用id
     */
    public static function msg_notification($msgid, $postid, $userid, $commentid, $username, $appid, $creattime)
    {
        $msg_notification = new Message();
        $msg_notification->msgid = $msgid;
        $msg_notification->postid = $postid;
        $msg_notification->userid = $userid;
        $msg_notification->commentid = $commentid;
        $msg_notification->username = $username;
        $msg_notification->appid = $appid;
        $msg_notification->creattime = $creattime;
        $msg_notification->save();
    }
}
