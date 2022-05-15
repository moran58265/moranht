<?php

namespace app\admin\controller;

use app\common\controller\Common;
use app\common\controller\Upload;
use think\Controller;
use think\Db;

class Index extends BaseController
{
    public function index()
    {
        $data = array();
        $data['usertotal'] = \app\admin\model\User::count();
        $data['apptotal'] = \app\admin\model\App::count();
        $data['kmtotal'] = Db::name('km')->count();
        $data['messagetotal'] = Db::name('notes')->count();
        return $this->fetch()->assign('data', $data);
    }

    public function upload(){
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        return Common::ReturnJson('上传成功',$file);
    }

    //统计最近一周的每天注册量
    public function getWeekRegister(){
        $data = array();
        $data['register'] = Db::query("SELECT FROM_UNIXTIME( creattime, '%m-%d' ) AS date,COUNT( * ) AS count FROM mr_user GROUP BY date ORDER BY date desc LIMIT 7");
        //循环获取数组中的日期date
        $result = array();
        foreach ($data['register'] as $key => $value) {
            $result['date'][] = $value['date'];
            $result['count'][] = $value['count'];
        }
        return Common::ReturnJson('获取成功',$result);
    }
    //统计最近一周的每天用户量
    public function getmonthusernum(){
        $data = array();
        $data['register'] = Db::query("SELECT FROM_UNIXTIME( creattime, '%m-%d' ) AS month,COUNT( * ) AS usernum FROM mr_user GROUP BY month ORDER BY month desc LIMIT 10");
        //循环获取数组中的日期date
        $result = array();
        foreach ($data['register'] as $key => $value) {
            $result['month'][] = $value['month'];
            $result['usernum'][] = $value['usernum'];
        }
        return Common::ReturnJson('获取成功',$result);
    }

    //请求接口
    public function getupdate(){
        // $url = 'https://www.moranblog.cn/mrhtupdate.php';
        // $data = file_get_contents($url);
        // $data = json_decode($data,true);
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ]; 
        $response = file_get_contents("https://www.moranblog.cn/mrhtupdate.php",false, stream_context_create($stream_opts));
        $data = json_decode($response,true);
        return Common::ReturnJson('获取成功',$data);
    }

    //下载zip文件
    public function downloadzip(){
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ]; 
        $downurl = input('post.');
        $arr=parse_url($downurl['url']);  //获取下载地址
        $fileName=basename(time());  //获取文件名
        $file=file_get_contents($downurl['url'],false, stream_context_create($stream_opts)); //获取文件内容
        $file_path = "./update/".$fileName.".zip";  //设置文件路径
        file_put_contents($file_path,$file);
        return Common::ReturnJson($file_path);
    }


    //解压文件 覆盖原文件
    public function unzip(){
        $file_path = input('post.filepath');
        $zip = new \ZipArchive();
        $res = $zip->open($file_path);  //解压文件
        //解压目录
        if($res === true){
            $zip->extractTo('../');
            $zip->close();
            unlink(input('post.filepath')); //删除源文件
            return Common::ReturnJson('解压成功');
        }else{
            return Common::ReturnJson('解压失败');
        }
    }

    //执行sql文件
public function runsql()
    {
        $issql =File_exists('../sql.sql');
        if($issql){
            $sql = file_get_contents('../sql.sql');
            $sql = explode(';', $sql);
            try {
                foreach ($sql as $key => $value) {
                    Db::execute($value);
                }
                unlink('../sql.sql');
                return Common::ReturnJson('执行成功');
            } catch (\Exception $e) {
                return Common::ReturnJson('执行成功');
            }
        }else{
            return Common::ReturnJson('执行成功');
        }
    }


}