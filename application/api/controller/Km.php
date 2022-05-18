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

class Km extends Controller
{
    public function UserKm(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username'  => 'require',
            'appid' => 'require|number',
            'km' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->find();
            $km = Db::name('km')->where('km',$data['km'])->where('appid', $data['appid'])->find();
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
        if ($user == "" || $user == null){
            return Common::return_msg(400,"没有该用户");
        }
        if ($km == "" || $km == null){
            return Common::return_msg(400,"不存在此卡密");
        }
        if ($km['isuse'] == 'true'){
            return Common::return_msg(400,"此卡密已被使用");
        }
        $updateuserdata = [
            'viptime' => $user['viptime'] + $km['vip']*24*3600,
            'money' => $user['money'] + $km['money'],
            'exp' => $user['exp'] + $km['exp']
        ];
        $updatekmdata = [
            'isuse' => 'true',
            'username' => $data['username'],
            'usetime' => date("Y-m-d H:i:s",time()),
        ];
        try {
            $updateuser = Db::name('user')->where('username', $data['username'])->update($updateuserdata);
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($updateuser>0){
            $result = array();
            $result['money'] = $km['money'];
            $result['exp'] = $km['exp'];
            $result['vip'] = $km['vip'];
            try {
                Db::name('km')->where('km', $data['km'])->update($updatekmdata);
            } catch (PDOException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (Exception $e) {
                return Common::return_msg(400, "请求失败");
            }
            return Common::return_msg(200,"使用成功",$result);
        }else{
            return Common::return_msg(400,"使用失败");
        }

    }

}