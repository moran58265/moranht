<?php

namespace app\admin\controller;

use app\admin\model\Plate;
use app\common\controller\Common;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\facade\Validate;
use think\Request;

class Bbs extends BaseController
{
    public function plate()
    {
        return $this->fetch('/bbs/index');
    }
    
    public function getplatelist()
    {
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        $sort = input('sort')?input('sort'):'appid';
        $sortOrder = input('sortOrder')?input('sortOrder'):'desc';
        $platename = input('platename')?input('platename'):'';
        $appList = Db::name('plate')
            ->alias('p')
            ->join('app a', 'a.appid=p.appid')
            ->where('platename', "like", '%' . $platename . '%')
            ->field('p.*,a.appname')
            ->order($sort, $sortOrder)
            ->limit($limit)
            ->page($page)
            ->select();
        $appListcount = Db::name('plate')
            ->alias('p')
            ->join('app a', 'a.appid=p.appid')
            ->where('platename', "like", '%' . $platename . '%')
            ->field('p.*,a.appname')
            ->count();
        return json(['rows' => $appList, 'total' => $appListcount]);
    }


    public function addplate(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'platename'  => 'require',
            'plateicon' => 'require',
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::ReturnError($validate->getError());
        }
        $app = \app\admin\model\App::get($data['appid']);
        if ($app == null) {
            return Common::ReturnError('app不存在');
        }
        $data['creattime'] = date("Y-m-d H:i:s", time());
        $plate = new Plate();
        $db = $plate->save($data);
        if ($db > 0) {
            return Common::ReturnSuccess("添加成功");
        } else {
            return Common::ReturnError("添加失败");
        }
    }

    public function deleteplate()
    {
        $appid = input('id');
        $app = Plate::destroy($appid);
        return Common::ReturnSuccess("删除成功");
    }

    public function queryplate()
    {
        $id = input('id');
        $listPlate = Db::name('plate')->where('p.id', $id)
            ->alias('p')
            ->join('app a', 'a.appid = p.appid')
            ->field('p.*,a.appname')
            ->find();
        return $this->fetch('bbs/queryplate')->assign('plate', $listPlate);
    }


    public function updateplate(Request $request)
    {
        $data = $request->post();
        $validate = Validate::make([
            'id' => 'require|number',
            'platename'  => 'require',
            'plateicon' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::ReturnError($validate->getError());
        }
        $update = [
            'platename' => $data['platename'],
            'plateicon' => $data['plateicon'],
        ];
        $db = Db::name('plate')->where('id', $data['id'])->update($update);
        if ($db > 0) {
            return Common::ReturnSuccess("修改成功");
        } else {
            return Common::ReturnError("修改失败");
        }
    }


    public function platepost()
    {
        try {
            $listPost = Db::name('post')
                ->alias('p')
                ->join('app a', 'a.appid = p.appid')
                ->join('plate q', 'q.id = p.plateid')
                ->field('p.*,a.appname,q.platename,(SELECT COUNT(*) FROM mr_comment as c WHERE c.postid  = p.id) as commentnum')
                ->distinct(true)
                ->paginate(10);
        } catch (DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        $page = $listPost->render();
        return $this->fetch('/bbs/platepost', ['list' => $listPost, 'page' => $page]);
    }

    public function delplatepost(Request $request)
    {
        $data = $request->post();
        try {
            $user = Db::name('post')->delete($data['id']);
        } catch (PDOException $e) {
            return Common::ReturnError($e->getMessage());
        } catch (Exception $e) {
            return Common::ReturnError($e->getMessage());
        }
        if ($user > 0) {
            return Common::ReturnSuccess("删除成功");
        } else {
            return Common::ReturnError('删除失败');
        }
    }

    public function querypost($id)
    {
        try {
            $listPost = Db::name('post')->where('p.id', $id)
                ->alias('p')
                ->join('app a', 'a.appid = p.appid')
                ->join('plate q', 'q.appid = p.appid')
                ->field('p.*,a.appname,q.platename')
                ->find();
            return $this->fetch('bbs/querypost')->assign('post', $listPost);
        } catch (DataNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        } catch (DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
    }
    public function updatepost(Request $request)
    {
        $data = $request->post();
        $validate = Validate::make([
            'id' => 'require|number',
            'postname'  => 'require',
            'postcontent' => 'require',
            'lock' => 'require|number',
            'top' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::ReturnError($validate->getError());
        }
        $update = [
            'postname' => $data['postname'],
            'postcontent' => htmlspecialchars_decode($data['postcontent']),
            'lock' => $data['lock'],
            'top' => $data['top'],
        ];
        $db = Db::name('post')->where('id', $data['id'])->update($update);
        if ($db > 0) {
            return Common::ReturnSuccess("修改成功");
        } else {
            return Common::ReturnError("修改失败");
        }
    }
}
