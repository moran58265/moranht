<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Validate;
use think\Request;
use app\common\controller\Common;

class Shop extends Controller
{
    public function GetShopList(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
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
            $notes = Db::name('shop')
                ->alias('s')
                ->where('s.appid', $data['appid'])
                ->join('app a', 'a.appid = s.appid')
                ->field('s.*,a.appname')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200,"查询成功",$notes);
    }

    public function GetShop(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $shop = Db::name('shop')->where('id', $data['id'])->find();
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
        if ($shop == "" || $shop == null){
            return Common::return_msg(400,"没有此商品");
        }
        try {
            $shoplist = Db::name('shop')
                ->alias('s')
                ->where('s.id', $data['id'])
                ->join('app a', 'a.appid = s.appid')
                ->field('s.*,a.appname')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200,"查询成功",$shoplist);
    }

    public function BuyShop(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
            'username' => 'require',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->find();
            $shop = Db::name('shop')->where('id', $data['id'])->find();
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
        if ($user['user_token'] != $data['usertoken']){
            return Common::return_msg(400,"token过期");
        }
        if ($shop == "" || $shop == null){
            return Common::return_msg(400,"没有此商品");
        }
        if ($user['money'] <= $shop['money']){
            return Common::return_msg(400,"金币不足");
        }
        if ($shop['inventory'] == 0){
            return Common::return_msg(400,"库存不足");
        }
        if ($shop['shoptype'] == 1){
            $shoptype = "会员类型";
        }else{
            $shoptype = "其他类型";
        }
        $intoshoporderdata = [
            'username' => $data['username'],
            'shopname' => $shop['shopname'],
            'shoptype' => $shoptype,
            'appid' => $data['appid'],
            'creat_time' => date("Y-m-d H:i:s",time()),
        ];
        if ($shop['shoptype'] == 2){
            Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->update(['money' => $user['money']-$shop['money']]);
            Db::name('shop')->where('id',$data['id'])->update(['sales' => $shop['sales']+1 , 'inventory' => $shop['inventory']-1 ]);
            Db::name('shoporder')->insert($intoshoporderdata);
            return Common::return_msg(200,"购买成功");
        }else{
            if ($user['viptime'] > time()){
                Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->update(['money' => $user['money']-$shop['money'],'viptime' => $user['viptime']+$shop['vipnum']*24*3600]);
                Db::name('shop')->where('id',$data['id'])->update(['sales' => $shop['sales']+1 , 'inventory' => $shop['inventory']-1 ]);
                Db::name('shoporder')->insert($intoshoporderdata);
                return Common::return_msg(200,"购买成功");
            }else{
                Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->update(['money' => $user['money']-$shop['money'],'viptime' => time()+$shop['vipnum']*24*3600]);
                Db::name('shop')->where('id',$data['id'])->update(['sales' => $shop['sales']+1 , 'inventory' => $shop['inventory']-1 ]);
                Db::name('shoporder')->insert($intoshoporderdata);
                return Common::return_msg(200,"购买成功");
            }
        }
    }


    public function UserShopOrder(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'username' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->find();
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
            return Common::return_msg(400,"不存在此用户");
        }
        try {
            $shoplist = Db::name('shoporder')
                ->alias('s')
                ->where('s.appid', $data['appid'])
                ->join('app a', 'a.appid = s.appid')
                ->field('s.*,a.appname')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200,"查询成功",$shoplist);
    }


}