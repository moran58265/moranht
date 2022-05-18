<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\facade\Validate;
use think\Request;
use app\common\controller\Common;

class App extends Controller
{
    public function GetAppGg(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        $result = Db::name('app')
            ->where('appid',$data['appid'])
            ->field("appname,title,content")
            ->find();
        return Common::return_msg(200,"查询成功",$result);
    }

    public function GetAppInfo(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        $result = Db::name('app')
            ->where('appid',$data['appid'])
            ->field("appicon,appname,introduction,author,group,view")
            ->find();
        return Common::return_msg(200,"查询成功",$result);
    }

    public function GetAppUpdate(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        $result = Db::name('app')
            ->where('appid',$data['appid'])
            ->field("appname,version,updatecontent,download")
            ->find();
        return Common::return_msg(200,"查询成功",$result);
    }

    public function AddAppView(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        try {
            $result = Db::name('app')
                ->where('appid', $data['appid'])
                ->update(['view' => $app['view'] + 1]);
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200,"成功");
    }
}