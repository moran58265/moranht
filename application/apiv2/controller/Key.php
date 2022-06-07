<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\facade\Validate;
use think\Request;
use app\common\controller\Common;
use app\admin\model\App as ModelApp;
use app\admin\model\User as ModelUser;
use app\admin\model\Admin as ModelAdmin;
use app\apiv2\controller\Base;

class Key extends Base
{
    public function KeyVip(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'adminkey' => 'require',
            'vipday' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if ($app == "" || $app == null) {
            return $this->returnError("没有此app");
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if ($user == "" || $user == null) {
            return $this->returnError("没有该用户");
        }
        $admin = ModelAdmin::get(1);
        if ($admin['admintoken'] != $data['adminkey']) {
            return $this->returnError("KEY错误");
        }
        if ($user['viptime'] > time()) {
            $userviptime = $user['viptime'];
        } else {
            $userviptime = time();
        }
        $viptime = $userviptime + $data['vipday'] * 24 * 3600;
        $result = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->update(['viptime' => $viptime]);
        if ($result > 0) {
            return $this->returnJson("充值成功");
        } else {
            return $this->returnError("充值失败");
        }
    }

    public function KeyMoney(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'adminkey' => 'require',
            'money' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if ($app == "" || $app == null) {
            return $this->returnError("没有此app");
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if ($user == "" || $user == null) {
            return $this->returnError("没有该用户");
        }
        $admin = ModelAdmin::get(1);
        if ($admin['admintoken'] != $data['adminkey']) {
            return $this->returnError("KEY错误");
        }
        $money = $user['money'] + $data['money'];
        $result = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->update(['money' => $money]);
        if ($result > 0) {
            return $this->returnJson("充值成功");
        } else {
            return $this->returnError("充值失败");
        }
    }


    public function KeyExp(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'adminkey' => 'require',
            'exp' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if ($app == "" || $app == null) {
            return $this->returnError("没有此app");
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if ($user == "" || $user == null) {
            return $this->returnError("没有该用户");
        }
        $admin = ModelAdmin::get(1);
        if ($admin['admintoken'] != $data['adminkey']) {
            return $this->returnError("KEY错误");
        }
        $exp = $user['exp'] + $data['exp'];
        $result = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->update(['exp' => $exp]);
        if ($result > 0) {
            return $this->returnJson("充值成功");
        } else {
            return $this->returnError("充值失败");
        }
    }

    public function vipPermanent(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'adminkey' => 'require',
            'vipdate' => 'require',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if ($app == "" || $app == null) {
            return $this->returnError("没有此app");
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if ($user == "" || $user == null) {
            return $this->returnError("没有该用户");
        }
        $admin = ModelAdmin::get(1);
        if ($admin['admintoken'] != $data['adminkey']) {
            return $this->returnError("KEY错误");
        }
        $result = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->update(['viptime' => $data['vipdate']]);
        if ($result > 0) {
            return $this->returnJson("充值成功");
        } else {
            return $this->returnError("充值失败");
        }
    }
}
