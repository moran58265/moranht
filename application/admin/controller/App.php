<?php

namespace app\admin\controller;

use app\admin\model\App as AppModel;
use app\admin\model\Message;
use app\common\controller\Common;
use think\Db;
use think\facade\Request;

class App extends BaseController
{
    public function index()
    {
        return $this->fetch();
    }

    //获取所有信息
    public function getapplist()
    {
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        $sort = input('sort')?input('sort'):'appid';
        $sortOrder = input('sortOrder')?input('sortOrder'):'asc';
        $appname = input('appname')?input('appname'):'';
        $appList = AppModel::where('appname',"like",'%'.$appname.'%')
        ->order($sort,$sortOrder)
        ->limit($limit)
        ->page($page)
        ->select();
        $count = Db::name('app')->where('appname',"like",'%'.$appname.'%')->count();
        return json(['rows' => $appList,'total' => $count]);
    }

    //删除app
    public function deleteapp()
    {
        $appid = input('appid');
        $app = AppModel::destroy($appid);
        $appall = AppModel::get($appid);
        $comment = Db::name('comment')->where('appid',$appid)->delete();
        $km = Db::name('km')->where('appid',$appid)->delete();
        $user = Db::name('user')->where('appid',$appid)->delete();
        $note = Db::name('notes')->where('appid',$appid)->delete();
        $plate = Db::name('plate')->where('appid',$appid)->delete();
        $post = Db::name('post')->where('appid',$appid)->delete();
        $shop = Db::name('shop')->where('appid',$appid)->delete();
        $shoporder = Db::name('shoporder')->where('appid',$appid)->delete();
        return Common::ReturnSuccess("删除成功");
    }

    //修改app禁用状态
    public function editfastatus()
    {
        $appid = explode(",",input('appid'));
        for ($i=0;$i<count($appid);$i++)
        {
            $app = AppModel::get($appid[$i]);
            $app->app_site_status = 'false';
            $app->save();
        }
        return Common::ReturnSuccess("修改成功");
    }

    //修改app启用状态
    public function edittrstatus()
    {
        $appid = explode(",",input('appid'));
        for ($i=0;$i<count($appid);$i++)
        {
            $app = AppModel::get($appid[$i]);
            $app->app_site_status = 'true';
            $app->save();
        }
        return Common::ReturnSuccess("修改成功");
    }

    //添加app
    public function addapp()
    {
        $app = new AppModel();
        $app->appname = input('appname');
        $app->creattime = date('Y-m-d H:i:s');
        $app->appicon = Request::domain() . '/static/images/app.png';
        $app->save();
        return Common::ReturnSuccess("添加成功");
    }

    //修改app
    public function queryapp()
    {
        $appid = input('appid');
        $app = AppModel::get($appid);
        return $this->fetch()->assign('app',$app);
    }

    //修改app信息
    public function editapp()
    {
        $data = input('post.');
        $app = new AppModel();
        $res = $app->save($data, ['appid' => $data['appid']]);
        if ($res) {
            return Common::ReturnSuccess('修改成功');
        } else {
            return Common::ReturnError('修改失败');
        }
    }

    //发布通知页面
    public function addmsg()
    {
        return $this->fetch();
    }

    //发布通知
    public function addmsgdo()
    {
        $data = input('post.');
        $app = AppModel::get($data['appid']);
        if($app == null){
            return Common::ReturnError('应用不存在');
        }
        $msg = new Message();
        $msg->appid = $data['appid'];
        $msg->msgid = 1;
        $msg->username = 0;
        $msg->content = $data['content'];
        $msg->creattime = date('Y-m-d H:i:s');
        $msg->save();
        return Common::ReturnSuccess("发布成功");
    }
}