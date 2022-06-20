<?php

namespace app\admin\controller;

use app\admin\model\Shop as ShopModel;
use app\admin\Model\Shoporder;
use app\admin\controller\Common;
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

    public function getshoplist()
    {
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        $sort = input('sort')?input('sort'):'id';
        $sortOrder = input('sortOrder')?input('sortOrder'):'desc';
        $shopname = input('shopname')?input('shopname'):'';    
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
        } catch (DataNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        }catch (ModelNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        }catch (DbException $e) {
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
        Common::adminLog('添加商品'.$data['shopname']);
        return Common::ReturnSuccess('添加成功');
    }

    public function queryshop(Request $request){
        $id = $request->get('id');
        $shop = new ShopModel();
        $shop = $shop->where('id',$id)->find();
        // return json($shop);
        return $this->fetch('/shop/queryshop',['shop'=>$shop]); 
    }

    public function updateshop(Request $request)
    {
        $data = $request->param();
        $shop = ShopModel::where('id',$data['id'])->update($data);
        Common::adminLog('修改商品'.$data['shopname']);
        return Common::ReturnSuccess('修改成功');
    }

    public function delshop(){
        $id = input('id');
        $app = ShopModel::destroy($id);
        Common::adminLog('删除商品'.$id);
        return Common::ReturnSuccess("删除成功");
    }

    public function shoporder()
    {
        return $this->fetch('/shop/shoporder');
    }

    public function getshoporderlist(){
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        $sort = input('sort')?input('sort'):'id';
        $sortOrder = input('sortOrder')?input('sortOrder'):'desc';
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
        } catch (DataNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        }catch (ModelNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        }catch (DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        return json(['rows' => $appList,'total' => $listShopordercount]);
    }

    public function delshoporder(){
        $id = input('id');
        $app = Shoporder::destroy($id);
        Common::adminLog('删除商品订单'.$id);
        return Common::ReturnSuccess("删除成功");
    }



}