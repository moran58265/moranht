<?php

namespace app\admin\controller;

use app\admin\model\Notes as NotesModel;
use app\common\controller\Common;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Notes extends BaseController
{
    public function index()
    {
        return $this->fetch();
    }

    public function Getnoteslist()
    {
        $limit = input('limit') ?? 10;
        $page = input('page') ?? 1;
        $sort = input('sort') ?? 'id';
        $sortOrder = input('sortOrder') ?? 'desc';
        $title = input('title') ?? '';
        try {
            $appList = Db::name('notes')
                ->alias('n')
                ->join('app a', 'a.appid = n.appid')
                ->field('n.*,a.appname')
                ->where('n.title', "like", '%' . $title . '%')
                ->order($sort, $sortOrder)
                ->limit($limit)
                ->page($page)
                ->select();
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        return json(['rows' => $appList,'total' => NotesModel::count()]);
    }

    //删除卡密
    public function deletenotes()
    {
        $id = input('id');
        $app = NotesModel::destroy($id);
        return Common::ReturnSuccess("删除成功");
    }
}