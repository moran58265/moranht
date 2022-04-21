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



}