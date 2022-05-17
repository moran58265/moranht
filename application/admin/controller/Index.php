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
        $data['usertotal'] = \app\admin\model\User::count(); #用户总数
        $data['apptotal'] = \app\admin\model\App::count(); #应用总数
        $data['kmtotal'] = Db::name('km')->count(); #卡密总数
        $data['messagetotal'] = Db::name('notes')->count(); #笔记总数
        $data['todayviptotal'] = Db::name('user')->where('viptime', '>', time())->count(); #今日vip总数
        $data['todayregtotal'] = Db::name('user')->where('creattime', '>', time())->count(); #今日注册总数
        $data['isusekmtotal'] = Db::name('km')->where('isuse', '=', 'true')->count(); #已使用卡密总数
        $data['viewtotal'] = Db::name('app')->sum('view'); #访问总数
        $data['signintotal'] = Db::name('user')->where('signtime','>',strtotime(date("Y-m-d"),time()))->count(); #今日签到人数
        $data['paltetotal'] = Db::name('plate')->count(); #板块数量
        $data['posttotal'] = Db::name('post')->count(); #帖子数量
        $data['filetotal'] = Db::name('upload')->count(); #文件数量
        return $this->fetch()->assign('data', $data);
    }

    public function upload()
    {
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        return Common::ReturnJson('上传成功', $file);
    }

    //统计最近一周的每天注册量
    public function getWeekRegister()
    {
        $data = array();
        $data['register'] = Db::query("
        select a.date,ifnull(b.count,0) as count 
        from (
            SELECT curdate() as date
            union all
            SELECT date_sub(curdate(), interval 1 day) as date
            union all
            SELECT date_sub(curdate(), interval 2 day) as date
            union all
            SELECT date_sub(curdate(), interval 3 day) as date
            union all
            SELECT date_sub(curdate(), interval 4 day) as date
            union all
            SELECT date_sub(curdate(), interval 5 day) as date
            union all
            SELECT date_sub(curdate(), interval 6 day) as date
        ) a left join (
        select FROM_UNIXTIME(creattime, '%Y-%m-%d') as datetime, count(*) as count
        from mr_user
        group by datetime DESC
        ) b on a.date = b.datetime;");
        //循环获取数组中的日期date
        $result = array();
        foreach ($data['register'] as $key => $value) {
            $result['date'][] = $value['date'];
            $result['count'][] = $value['count'];
        }
        return Common::ReturnJson('获取成功', $result);
    }

    //统计最近一周的每天用户量
    public function getmonthusernum()
    {
        $data = array();
        $data['register'] = Db::query("
        select a.date,ifnull(b.count,0) as count 
        from (
            SELECT date_format(CURDATE(), '%Y-%m') as date
            union all
            SELECT date_format(date_sub(curdate(), interval 1 month),'%Y-%m') as date
            union all
            SELECT date_format(date_sub(curdate(), interval 2 month),'%Y-%m') as date
            union all
            SELECT date_format(date_sub(curdate(), interval 3 month),'%Y-%m') as date
            union all
            SELECT date_format(date_sub(curdate(), interval 4 month),'%Y-%m') as date
            union all
            SELECT date_format(date_sub(curdate(), interval 5 month),'%Y-%m') as date
            union all
            SELECT date_format(date_sub(curdate(), interval 6 month),'%Y-%m') as date
        ) a left join (
        select FROM_UNIXTIME(creattime, '%Y-%m') as datetime, count(*) as count
        from mr_user
        group by datetime DESC
        ) b on a.date = b.datetime;");
        $result = array();
        foreach ($data['register'] as $key => $value) {
            $result['date'][] = $value['date'];
            $result['count'][] = $value['count'];
        }
        return Common::ReturnJson('获取成功', $result);
    }


    //请求接口
    public function getupdate()
    {
        // $url = 'https://www.moranblog.cn/mrhtupdate.php';
        // $data = file_get_contents($url);
        // $data = json_decode($data,true);
        $stream_opts = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];
        $response = file_get_contents("https://www.moranblog.cn/mrhtupdate.php", false, stream_context_create($stream_opts));
        $data = json_decode($response, true);
        return Common::ReturnJson('获取成功', $data);
    }

    //下载zip文件
    public function downloadzip()
    {
        $stream_opts = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];
        $downurl = input('post.');
        $arr = parse_url($downurl['url']);  //获取下载地址
        $fileName = basename(time());  //获取文件名
        $file = file_get_contents($downurl['url'], false, stream_context_create($stream_opts)); //获取文件内容
        $file_path = "./update/" . $fileName . ".zip";  //设置文件路径
        file_put_contents($file_path, $file);
        return Common::ReturnJson($file_path);
    }


    //解压文件 覆盖原文件
    public function unzip()
    {
        $file_path = input('post.filepath');
        $zip = new \ZipArchive();
        $res = $zip->open($file_path);  //解压文件
        //解压目录
        if ($res === true) {
            $zip->extractTo('../');
            $zip->close();
            unlink(input('post.filepath')); //删除源文件
            return Common::ReturnJson('解压成功');
        } else {
            return Common::ReturnJson('解压失败');
        }
    }

    //执行sql文件
    public function runsql()
    {
        $issql = File_exists('../sql.sql');
        if ($issql) {
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
        } else {
            return Common::ReturnJson('执行成功');
        }
    }
}
