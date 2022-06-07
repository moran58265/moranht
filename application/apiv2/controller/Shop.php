<?php

namespace app\apiv2\controller;

use app\admin\model\App as ModelApp;
use app\admin\model\Shop as ModelShop;
use app\admin\Model\Shoporder;
use app\admin\model\User as ModelUser;
use app\apiv2\controller\Base;
use think\Db;
use think\facade\Validate;
use think\Request;
use app\common\controller\Common;
use think\facade\Cookie;

class Shop extends Base
{
    /**
     * 获取商品
     *
     * @param Request $request
     */
    public function GetShopList(Request $request)
    {
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $notes = Db::name('shop')
            ->alias('s')
            ->where('s.appid', $data['appid'])
            ->join('app a', 'a.appid = s.appid')
            ->field('s.*,a.appname')
            ->limit($limit)
            ->page($page)
            ->select();
        return $this->returnSuccess("查询成功", $notes);
    }

    /**
     * 获取商品信息
     *
     * @param Request $request
     */
    public function GetShop(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $shop = ModelShop::where('id', $data['id'])->find();
        if (!$shop) {
            return $this->returnError('没有此商品');
        }
        $shoplist = Db::name('shop')
            ->alias('s')
            ->where('s.id', $data['id'])
            ->join('app a', 'a.appid = s.appid')
            ->field('s.*,a.appname')
            ->select();
        return $this->returnSuccess("查询成功", $shoplist);
    }

    /**
     * 购买商品
     *
     * @param Request $request
     */
    public function BuyShop(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $cookiedata = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
            $data = $request->param();
            $validate = Validate::make([
                'id' => 'require|number',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'id' => 'require|number',
                'username' => 'require',
                'appid' => 'require|number',
                'usertoken' => 'require',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
            $cookiedata = [
                'username' => $data['username'],
                'usertoken' => $data['usertoken'],
                'appid' => $data['appid'],
            ];
        }
        $app = ModelApp::where('appid', $cookiedata['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        if ($user->user_token != $cookiedata['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $shop = ModelShop::where('id', $data['id'])->find();
        if (!$shop) {
            return $this->returnError('没有此商品');
        }
        if ($user->money <= $shop['money']) {
            return $this->returnError('金币不足');
        }
        if ($shop->inventory == 0) {
            return $this->returnError('库存不足');
        }
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
            'username' => 'require',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        if ($shop->shoptype == 1) {
            $shoptype = "会员类型";
        } else {
            $shoptype = "其他类型";
        }
        $intoshoporderdata = [
            'username' => $cookiedata['username'],
            'shopname' => $shop['shopname'],
            'shoptype' => $shoptype,
            'appid' => $cookiedata['appid'],
            'creat_time' => date("Y-m-d H:i:s", time()),
        ];
        if ($shop['shoptype'] == 2) {
            ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->update(['money' => $user['money'] - $shop['money']]);
            ModelShop::where('id', $data['id'])->update(['sales' => $shop['sales'] + 1, 'inventory' => $shop['inventory'] - 1]);
            Shoporder::create($intoshoporderdata);
            return $this->returnJson("购买成功");
        } else {
            if ($user['viptime'] > time()) {
                ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->update(['money' => $user['money'] - $shop['money'], 'viptime' => $user['viptime'] + $shop['vipnum'] * 24 * 3600]);
                ModelShop::where('id', $data['id'])->update(['sales' => $shop['sales'] + 1, 'inventory' => $shop['inventory'] - 1]);
                Shoporder::create($intoshoporderdata);
                return $this->returnJson("购买成功");
            } else {
                ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->update(['money' => $user['money'] - $shop['money'], 'viptime' =>  time() + $shop['vipnum'] * 24 * 3600]);
                ModelShop::where('id', $data['id'])->update(['sales' => $shop['sales'] + 1, 'inventory' => $shop['inventory'] - 1]);
                Shoporder::create($intoshoporderdata);
                return $this->returnJson("购买成功");
            }
        }
    }


    /**
     * 用户购买记录
     *
     * @param Request $request
     */
    public function UserShopOrder(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'appid' => 'require|number'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::where('appid', $data['appid'])->find();
        if (!$app) {
            return $this->returnError('应用不存在');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('用户不存在');
        }
        $shoplist = Db::name('shoporder')
            ->alias('s')
            ->where('s.appid', $data['appid'])
            ->join('app a', 'a.appid = s.appid')
            ->field('s.*,a.appname')
            ->select();
        return Common::return_msg(200, "查询成功", $shoplist);
    }
}
