<?php

namespace app\admin\controller;

use app\admin\model\Notes as NotesModel;
use app\admin\controller\Common;
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
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        $sort = input('sort')?input('sort'):'id';
        $sortOrder = input('sortOrder')?input('sortOrder'):'desc';
        $title = input('title')?input('title'):'';
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
            $notecount = Db::name('notes')
                ->alias('n')
                ->join('app a', 'a.appid = n.appid')
                ->field('n.*,a.appname')
                ->where('n.title', "like", '%' . $title . '%')
                ->count();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        }catch (ModelNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        }catch (DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        return json(['rows' => $appList,'total' => $notecount]);
    }

    //删除
    public function deletenotes()
    {
        $id = input('id');
        $app = NotesModel::destroy($id);
        Common::adminLog('删除笔记'.$id);
        return Common::ReturnSuccess("删除成功");
    }


    public function querynote()
    {
        $id = input('id');
        $notes = Db::name('notes')->alias('n')->join('app a', 'a.appid = n.appid')->where('n.id', $id)->field('n.*,a.appname')->find();
        //return json($notes);
        return $this->fetch()->assign('notes',$notes);
    }

    public function editnote()
    {
        $data = input('post.');
        $notes = new NotesModel();
        $res = $notes->save($data, ['id' => $data['id']]);
        if ($res) {
            Common::adminLog('编辑笔记'.$data['id']);
            return Common::ReturnSuccess('修改成功');
        } else {
            return Common::ReturnError('修改失败');
        }
    }


}