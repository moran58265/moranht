<<<<<<< HEAD
<?php

namespace app\apipro\controller;

use COM;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Request;

class Upload
{
    /**
     * 默认上传配置
     * @var array
     */
    private $config = [
        'image' => [
            'validate' => [
                'size' => 10*1024*1024,
                'ext'  => 'jpg,png,gif,jpeg',
            ],
            'rootPath'      =>  './Uploads/images/', //保存根路径
        ],
//        'audio' => [
//            'validate' => [
//                'size' => 100*1024*1024,
//                'ext'  => 'mp3,wav,cd,ogg,wma,asf,rm,real,ape,midi',
//            ],
//            'rootPath'      =>  './Uploads/audios/', //保存根路径
//        ],
//        'video' => [
//            'validate' => [
//                'size' => 100*1024*1024,
//                'ext'  => 'mp4,avi,rmvb,rm,mpg,mpeg,wmv,mkv,flv',
//            ],
//            'rootPath'      =>  './Uploads/videos/', //保存根路径
//        ],
//        'file' => [
//            'validate' => [
//                'size' => 5*1024*1024,
//                'ext'  => 'doc,docx,xls,xlsx,pdf,ppt,txt,rar',
//            ],
//            'rootPath' =>  './Uploads/files/', //保存根路径
//        ],
    ];
    private $domain;
    function __construct()
    {
        //获取当前域名
        $this->domain = Request::instance()->domain();
    }

    public function upload($fileName){
        if(empty($_FILES) || empty($_FILES[$fileName])){
            return '';
        }
        try{
            $file = request()->file($fileName);
            if (is_array($file)){
                $path = [];
                foreach ($file as $item){
                    $path[] =  $this->save($item);
                }
            } else {
                $path = $this->save($file);
            }
            return $path;
        } catch (\Exception $e){
            $arr = [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
            header('Content-Type: application/json; charset=UTF-8');
            exit(json($arr));
        }
    }
    public function uploadDetail($fileName){
        if(empty($_FILES) || empty($_FILES[$fileName])){
            return [];
        }
        try{
            $file = request()->file($fileName);
            if (is_array($file)){
                $path = [];
                $image = '';
                foreach ($file as $item){
                    $detail = $item->getInfo();
                    $returnData['name'] = $detail['name'];
                    $returnData['type'] = $detail['type'];
                    $returnData['size'] = $detail['size'];
                    $returnData['filePath'] = $this->save($item);
                    $returnData['fullPath'] = $this->domain.$returnData['filePath'];
                    $returnData['creat_time'] = time();
                    $path[] = $returnData;
                    db('upload')->insert($returnData);
                }
            } else {
                $detail = $file->getInfo();
                $returnData['name'] = $detail['name'];
                $returnData['type'] = $detail['type'];
                $returnData['size'] = $detail['size'];
                $returnData['filePath'] = $this->save($file);
                $returnData['fullPath'] = $this->domain.$returnData['filePath'];
                $returnData['creat_time'] = time();
                $path = $returnData;
                db('upload')->insert($path);
            }
            return $path;
        } catch (\Exception $e){
            header('Content-Type: application/json; charset=UTF-8');
            return Common::returnJson(400,$e->getMessage());
        }
    }
    private function getConfig($file){
        $name = pathinfo($file['name']);
        $end = $name['extension'];
        foreach ($this->config as $key=>$item){
            if ($item['validate']['ext'] && strpos($item['validate']['ext'], $end) !== false){
                return $this->config[$key];
            }
        }
        return null;
    }

    /**
     * @throws Exception
     */
    private function save($file){
        $config = $this->getConfig($file->getInfo());
        if (empty($config)){
            throw new Exception('上传文件类型不被允许！');
        }
        // 移动到框架应用根目录/uploads/ 目录下
        if ($config['validate']) {
            $file->validate($config['validate']);
            $result = $file->move($config['rootPath']);
        } else {
            $result = $file->move($config['rootPath']);
        }
        if($result){
            $path = $config['rootPath'];
            if (strstr($path,'.') !== false){
                $path = str_replace('.', '', $path);
            }
            return $path.$result->getSaveName();
        }else{
            // 上传失败获取错误信息
            throw new Exception($file->getError());
        }
    }

}
=======
<?php ?><?php /* 2659917175 */ ?><?php
if(!function_exists('sg_load')){$__v=phpversion();$__x=explode('.',$__v);$__v2=$__x[0].'.'.(int)$__x[1];$__u=strtolower(substr(php_uname(),0,3));$__ts=(@constant('PHP_ZTS') || @constant('ZEND_THREAD_SAFE')?'ts':'');$__f=$__f0='ixed.'.$__v2.$__ts.'.'.$__u;$__ff=$__ff0='ixed.'.$__v2.'.'.(int)$__x[2].$__ts.'.'.$__u;$__ed=@ini_get('extension_dir');$__e=$__e0=@realpath($__ed);$__dl=function_exists('dl') && function_exists('file_exists') && @ini_get('enable_dl') && !@ini_get('safe_mode');if($__dl && $__e && version_compare($__v,'5.2.5','<') && function_exists('getcwd') && function_exists('dirname')){$__d=$__d0=getcwd();if(@$__d[1]==':') {$__d=str_replace('\\','/',substr($__d,2));$__e=str_replace('\\','/',substr($__e,2));}$__e.=($__h=str_repeat('/..',substr_count($__e,'/')));$__f='/ixed/'.$__f0;$__ff='/ixed/'.$__ff0;while(!file_exists($__e.$__d.$__ff) && !file_exists($__e.$__d.$__f) && strlen($__d)>1){$__d=dirname($__d);}if(file_exists($__e.$__d.$__ff)) dl($__h.$__d.$__ff); else if(file_exists($__e.$__d.$__f)) dl($__h.$__d.$__f);}if(!function_exists('sg_load') && $__dl && $__e0){if(file_exists($__e0.'/'.$__ff0)) dl($__ff0); else if(file_exists($__e0.'/'.$__f0)) dl($__f0);}if(!function_exists('sg_load')){$__ixedurl='http://www.sourceguardian.com/loaders/download.php?php_v='.urlencode($__v).'&php_ts='.($__ts?'1':'0').'&php_is='.@constant('PHP_INT_SIZE').'&os_s='.urlencode(php_uname('s')).'&os_r='.urlencode(php_uname('r')).'&os_m='.urlencode(php_uname('m'));$__sapi=php_sapi_name();if(!$__e0) $__e0=$__ed;if(function_exists('php_ini_loaded_file')) $__ini=php_ini_loaded_file(); else $__ini='php.ini';if((substr($__sapi,0,3)=='cgi')||($__sapi=='cli')||($__sapi=='embed')){$__msg="\nPHP script '".__FILE__."' is protected by SourceGuardian and requires a SourceGuardian loader '".$__f0."' to be installed.\n\n1) Download the required loader '".$__f0."' from the SourceGuardian site: ".$__ixedurl."\n2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="\n3) Edit ".$__ini." and add 'extension=".$__f0."' directive";}}$__msg.="\n\n";}else{$__msg="<html><body>PHP script '".__FILE__."' is protected by <a href=\"http://www.sourceguardian.com/\">SourceGuardian</a> and requires a SourceGuardian loader '".$__f0."' to be installed.<br><br>1) <a href=\"".$__ixedurl."\" target=\"_blank\">Click here</a> to download the required '".$__f0."' loader from the SourceGuardian site<br>2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="<br>3) Edit ".$__ini." and add 'extension=".$__f0."' directive<br>4) Restart the web server";}}$__msg.="</body></html>";}die($__msg);exit();}}return sg_load('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');
>>>>>>> 65397660d776cb795cd7b8980daef3f614b34c5c
