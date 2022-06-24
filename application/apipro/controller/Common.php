<<<<<<< HEAD
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
        // $this->AuthWeb();   //验证此域名是否授权
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
=======
<?php ?><?php /* 2659917175 */ ?><?php
if(!function_exists('sg_load')){$__v=phpversion();$__x=explode('.',$__v);$__v2=$__x[0].'.'.(int)$__x[1];$__u=strtolower(substr(php_uname(),0,3));$__ts=(@constant('PHP_ZTS') || @constant('ZEND_THREAD_SAFE')?'ts':'');$__f=$__f0='ixed.'.$__v2.$__ts.'.'.$__u;$__ff=$__ff0='ixed.'.$__v2.'.'.(int)$__x[2].$__ts.'.'.$__u;$__ed=@ini_get('extension_dir');$__e=$__e0=@realpath($__ed);$__dl=function_exists('dl') && function_exists('file_exists') && @ini_get('enable_dl') && !@ini_get('safe_mode');if($__dl && $__e && version_compare($__v,'5.2.5','<') && function_exists('getcwd') && function_exists('dirname')){$__d=$__d0=getcwd();if(@$__d[1]==':') {$__d=str_replace('\\','/',substr($__d,2));$__e=str_replace('\\','/',substr($__e,2));}$__e.=($__h=str_repeat('/..',substr_count($__e,'/')));$__f='/ixed/'.$__f0;$__ff='/ixed/'.$__ff0;while(!file_exists($__e.$__d.$__ff) && !file_exists($__e.$__d.$__f) && strlen($__d)>1){$__d=dirname($__d);}if(file_exists($__e.$__d.$__ff)) dl($__h.$__d.$__ff); else if(file_exists($__e.$__d.$__f)) dl($__h.$__d.$__f);}if(!function_exists('sg_load') && $__dl && $__e0){if(file_exists($__e0.'/'.$__ff0)) dl($__ff0); else if(file_exists($__e0.'/'.$__f0)) dl($__f0);}if(!function_exists('sg_load')){$__ixedurl='http://www.sourceguardian.com/loaders/download.php?php_v='.urlencode($__v).'&php_ts='.($__ts?'1':'0').'&php_is='.@constant('PHP_INT_SIZE').'&os_s='.urlencode(php_uname('s')).'&os_r='.urlencode(php_uname('r')).'&os_m='.urlencode(php_uname('m'));$__sapi=php_sapi_name();if(!$__e0) $__e0=$__ed;if(function_exists('php_ini_loaded_file')) $__ini=php_ini_loaded_file(); else $__ini='php.ini';if((substr($__sapi,0,3)=='cgi')||($__sapi=='cli')||($__sapi=='embed')){$__msg="\nPHP script '".__FILE__."' is protected by SourceGuardian and requires a SourceGuardian loader '".$__f0."' to be installed.\n\n1) Download the required loader '".$__f0."' from the SourceGuardian site: ".$__ixedurl."\n2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="\n3) Edit ".$__ini." and add 'extension=".$__f0."' directive";}}$__msg.="\n\n";}else{$__msg="<html><body>PHP script '".__FILE__."' is protected by <a href=\"http://www.sourceguardian.com/\">SourceGuardian</a> and requires a SourceGuardian loader '".$__f0."' to be installed.<br><br>1) <a href=\"".$__ixedurl."\" target=\"_blank\">Click here</a> to download the required '".$__f0."' loader from the SourceGuardian site<br>2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="<br>3) Edit ".$__ini." and add 'extension=".$__f0."' directive<br>4) Restart the web server";}}$__msg.="</body></html>";}die($__msg);exit();}}return sg_load('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');
>>>>>>> 65397660d776cb795cd7b8980daef3f614b34c5c
