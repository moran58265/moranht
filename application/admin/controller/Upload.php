<?php

namespace app\admin\controller;

use COM;
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
                    Common::adminLog('上传文件：'.$detail['name']);
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
            return Common::ReturnError($e->getMessage());
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