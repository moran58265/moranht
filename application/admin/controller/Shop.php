<?php

namespace app\admin\controller;

use app\admin\model\Shop as ShopModel;
use app\admin\Model\Shoporder;
use app\common\controller\Common;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;

class Shop extends BaseController
{
    public function index()
    {
        
        return $this->fetch('/shop/index');
    }

    public function getshoplist(){
        $limit = input('limit') ?? 10;
        $page = input('page') ?? 1;
        $sort = input('sort') ?? 'id';
        $sortOrder = input('sortOrder') ?? 'desc';
        $shopname = input('shopname') ?? '';
        try {
            $appList = Db::name('shop')
                ->alias('s')
                ->join('app a', 'a.appid = s.appid')
                ->field('s.*,a.appname')
                ->where('s.shopname', "like", '%' . $shopname . '%')
                ->order($sort, $sortOrder)
                ->limit($limit)
                ->page($page)
                ->select();
            $listShopcount = Db::name('shop')
                ->alias('s')
                ->join('app a', 'a.appid = s.appid')
                ->field('s.*,a.appname')
                ->where('s.shopname', "like", '%' . $shopname . '%')
                ->count();
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        return json(['rows' => $appList,'total' => $listShopcount]);
    }

    public function addshop(Request  $request){
        $data = $request->post();
        $validate = new \app\admin\validate\Shop();
        if (!$validate->scene('add')->check($data)) {
            return Common::ReturnError($validate->getError());
        }
        $data['creat_time'] = date('Y-m-d H:i:s');
        $shop = new ShopModel();
        $shop->save($data);
        return Common::ReturnSuccess('添加成功');
    }

    public function delshop(){
        $id = input('id');
        $app = ShopModel::destroy($id);
        return Common::ReturnSuccess("删除成功");
    }

    public function shoporder()
    {
        return $this->fetch('/shop/shoporder');
    }

    public function getshoporderlist(){
        $limit = input('limit') ?? 10;
        $page = input('page') ?? 1;
        $sort = input('sort') ?? 'id';
        $sortOrder = input('sortOrder') ?? 'desc';
        try {
            $appList = Db::name('shoporder')
                ->alias('s')
                ->join('app a', 'a.appid = s.appid')
                ->field('s.*,a.appname')
                ->order($sort, $sortOrder)
                ->limit($limit)
                ->page($page)
                ->select();
            $listShopordercount = Db::name('shoporder')
                ->alias('s')
                ->join('app a', 'a.appid = s.appid')
                ->field('s.*,a.appname')
                ->count();
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        return json(['rows' => $appList,'total' => $listShopordercount]);
    }

    public function delshoporder(){
        $id = input('id');
        $app = Shoporder::destroy($id);
        return Common::ReturnSuccess("删除成功");
    }



}