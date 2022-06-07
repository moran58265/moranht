<?php

namespace app\apiv2\controller;

use think\facade\Validate;
use think\Request;
use think\facade\Cookie;
use app\admin\model\User as ModelUser;
use app\admin\model\App as ModelApp;
use app\admin\model\Km as ModelKm;

class Km extends Base
{
    public function UserKm(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = $request->param();
            $validate = Validate::make([
                'km' => 'require'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
            $datacookie = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'appid' => 'require|number',
                'km' => 'require'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
            $datacookie = [
                'username' => $data['username'],
                'usertoken' => $data['usertoken'],
                'appid' => $data['appid'],
            ];
        }
        $app = ModelApp::where('appid', $datacookie['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $datacookie['username'])->where('appid', $datacookie['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        $km = ModelKm::where('km', $data['km'])->where('appid', $datacookie['appid'])->find();
        if (!$km) {
            return $this->returnError('卡密不存在');
        }
        if ($km->isuse == 'true') {
            return $this->returnError('卡密已被使用');
        }
        $addviptime = $km['vip'] * 24 * 60 * 60;
        if ($user['viptime'] >= time()) {
            $updateuserdata = [
                'viptime' => ($user['viptime'] + $addviptime),
                'money' => ($user['money'] + $km['money']),
                'exp' => ($user['exp'] + $km['exp']),
            ];
        } else {
            $updateuserdata = [
                'viptime' => (time() + $addviptime),
                'money' => ($user['money'] + $km['money']),
                'exp' => ($user['exp'] + $km['exp']),
            ];
        }
        $updatekmdata = [
            'isuse' => 'true',
            'username' => $data['username'],
            'usetime' => date("Y-m-d H:i:s", time()),
        ];
        if ($km['vip'] == 0 && $km['money'] == 0 && $km['exp'] == 0) {
            ModelKm::where('km', $data['km'])->where('appid', $datacookie['appid'])->update($updatekmdata);
            return $this->returnSuccess("使用成功", ['money' => 0, 'exp' => 0, 'viptime' => 0]);
        }
        $updateuser = ModelUser::where('username', $datacookie['username'])->where('appid', $datacookie['appid'])->update($updateuserdata);
        if ($updateuser > 0) {
            $result = array();
            $result['money'] = $km['money'];
            $result['exp'] = $km['exp'];
            $result['vip'] = $km['vip'];
            ModelKm::where('km', $data['km'])->where('appid', $datacookie['appid'])->update($updatekmdata);
            return $this->returnSuccess("使用成功", $result);
        } else {
            return $this->returnError("使用失败");
        }
    }
}
