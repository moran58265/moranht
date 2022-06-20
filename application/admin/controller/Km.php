<?php

namespace app\admin\controller;

use app\admin\model\Km as KmModel;
use app\admin\model\App as AppModel;
use app\admin\controller\Common;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;

class Km extends BaseController
{
    public function index()
    {
        return $this->fetch();
    }

    public function GetKmlist()
    {
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        $sort = input('sort')?input('sort'):'id';
        $sortOrder = input('sortOrder') ?input('sortOrder'):'asc';
        $km = input('km')?input('km'):'';
        $isuse = input('isuse')?input('isuse'):'';
        try {
            $appList = Db::name('km')
                ->alias('k')
                ->join('app a', 'a.appid = k.appid')
                ->field('k.*,FROM_UNIXTIME(k.creattime,"%Y-%m-%d") as creattime,a.appname')
                ->where('k.km', "like", '%' . $km . '%')
                ->where('k.isuse',"like","%". $isuse. '%')
                ->order($sort, $sortOrder)
                ->limit($limit)
                ->page($page)
                ->select();
            $appcount = Db::name('km')
                ->alias('k')
                ->join('app a', 'a.appid = k.appid')
                ->field('k.*,FROM_UNIXTIME(k.creattime,"%Y-%m-%d") as creattime,a.appname')
                ->where('k.km', "like", '%' . $km . '%')
                ->where('k.isuse',"like","%". $isuse. '%')
                ->count();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        }catch (ModelNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        }catch (DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        return json(['rows' => $appList,'total' => $appcount]);
    }

    //删除卡密
    public function deletekm()
    {
        $id = input('id');
        $app = KmModel::destroy($id);
        Common::adminLog('删除卡密'.$id);
        return Common::ReturnSuccess("删除成功");
    }

    public function addkm(){
        return $this->fetch();
    }

    public function addkmdo(){
        $data = input('post.');
        $app = AppModel::get($data['appid']);
        $validate = validate('km');
        if (!$validate->scene('add')->check($data)) {
            return Common::ReturnError($validate->getError());
        }
        if ($app == null){
            return Common::ReturnError("应用不存在");
        }
        for ($a = 0;$a<$data['generatenum'];$a++){
            $data['creattime'] = time();
            $data['km'] = Common::getRandChar($data['kmlength']);
            $km = new KmModel();
            $km->data($data);
            $km->save();
        }
        Common::adminLog('添加卡密'.$data['generatenum'].'个');
        return Common::ReturnSuccess("添加成功");
    }

    public function export(){
        return $this->fetch();
    }

    public function exportdo(){
        $data = input('post.');
        $km = KmModel::where('appid',$data['appid'])->where('isuse',$data['isuse'])->where('classification',$data['classification'])->select();
        //写入txt文件
        $url =  "km/km.txt";
        $file = fopen($url,"w");
        foreach ($km as $k => $v){
            fwrite($file,$v['km']."\r\n");
        }
        fclose($file);
        $download =  new \think\response\Download('km/km.txt');
        Common::adminLog('导出卡密');
        return $download->name('km.txt');
    }

    public function validatekm(){
        $data = input('post.');
        $app = AppModel::get($data['appid']);
        if ($app == null){
            return Common::ReturnError("应用不存在");
        }
        $km = KmModel::where('appid',$data['appid'])->where('isuse',$data['isuse'])->where('classification',$data['classification'])->select();
        if (sizeof($km) == 0){
            return Common::ReturnError("没有数据");
        }else{
            return Common::ReturnSuccess("验证成功");
        }
    }

}