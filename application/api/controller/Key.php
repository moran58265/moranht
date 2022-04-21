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

class Key extends Controller
{
    public function KeyVip(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'adminkey' => 'require',
            'vipday' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $admin = Db::name('admin')->where('id',1)->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400, $e->getMessage());
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($admin['admintoken'] != $data['adminkey']) {
            return Common::return_msg(400, "key不正确");
        }
        $viptime = $user['viptime']+$data['vipday']*24*3600;
        try {
            $result = Db::name('user')->where('username', $data['username'])->update(['viptime' => $viptime]);
        } catch (PDOException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (Exception $e) {
            return Common::return_msg(400, $e->getMessage());
        }
        if ($result>0){
            return Common::return_msg(200, "充值成功");
        }else{
            return Common::return_msg(400, "充值失败");
        }
    }

    public function KeyMoney(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'adminkey' => 'require',
            'money' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $admin = Db::name('admin')->where('id',1)->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400, $e->getMessage());
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($admin['admintoken'] != $data['adminkey']) {
            return Common::return_msg(400, "key不正确");
        }
        $money = $user['money']+$data['money'];
        try {
            $result = Db::name('user')->where('username', $data['username'])->update(['money' => $money]);
        } catch (PDOException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (Exception $e) {
            return Common::return_msg(400, $e->getMessage());
        }
        if ($result>0){
            return Common::return_msg(200, "充值成功");
        }else{
            return Common::return_msg(400, "充值失败");
        }
    }


    public function KeyExp(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'adminkey' => 'require',
            'exp' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $admin = Db::name('admin')->where('id',1)->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400, $e->getMessage());
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($admin['admintoken'] != $data['adminkey']) {
            return Common::return_msg(400, "key不正确");
        }
        $exp = $user['exp']+$data['exp'];
        try {
            $result = Db::name('user')->where('username', $data['username'])->update(['exp' => $exp]);
        } catch (PDOException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (Exception $e) {
            return Common::return_msg(400, $e->getMessage());
        }
        if ($result>0){
            return Common::return_msg(200, "充值成功");
        }else{
            return Common::return_msg(400, "充值失败");
        }
    }

    public function vipPermanent(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username' => 'require',
            'appid' => 'require|number',
            'adminkey' => 'require',
            'vipdate' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $admin = Db::name('admin')->where('id',1)->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400, $e->getMessage());
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有该用户");
        }
        if ($admin['admintoken'] != $data['adminkey']) {
            return Common::return_msg(400, "key不正确");
        }
        try {
            $result = Db::name('user')->where('username', $data['username'])->update(['viptime' => $data['vipdate']]);
        } catch (PDOException $e) {
            return Common::return_msg(400, $e->getMessage());
        } catch (Exception $e) {
            return Common::return_msg(400, $e->getMessage());
        }
        if ($result>0){
            return Common::return_msg(200, "充值成功");
        }else{
            return Common::return_msg(400, "充值失败");
        }
    }
}