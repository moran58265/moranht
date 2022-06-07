<?php

namespace app\apiv2\controller;

use app\admin\model\App as ModelApp;
use think\facade\Validate;
use think\Request;

class App extends Base
{
    public function GetAppGg(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (empty($app)) {
            return $this->returnError('app不存在');
        }
        $result = [
            "appname" => $app->appname,
            'title' => $app->title,
            'content' => $app->content,
        ];
        return $this->returnSuccess("查询成功", $result);
    }

    public function GetAppInfo(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (empty($app)) {
            return $this->returnError('app不存在');
        }
        $result = [
            "appicon" => $app->appicon,
            'appname' => $app->appname,
            'introduction' => $app->introduction,
            'author' => $app->author,
            'group' => $app->group,
            'view' => $app->view,
        ];
        return $this->returnSuccess("查询成功", $result);
    }

    public function GetAppUpdate(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (empty($app)) {
            return $this->returnError('app不存在');
        }
        $result = [
            "appname" => $app->appname,
            "version" => $app->version,
            "updatecontent" => $app->updatecontent,
            "download" => $app->download,
        ];
        return $this->returnSuccess("查询成功", $result);
    }

    public function AddAppView(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (empty($app)) {
            return $this->returnError('app不存在');
        }
        $app->view = $app->view + 1;
        $app->save();
        return $this->returnJson("访问成功");
    }
}
