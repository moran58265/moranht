<?php

namespace app\admin\controller;

use app\admin\model\Plate;
use app\admin\controller\Common;
use app\admin\model\PlatePost;
use app\admin\model\User;
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
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        $sort = input('sort') ? input('sort') : 'appid';
        $sortOrder = input('sortOrder') ? input('sortOrder') : 'desc';
        $platename = input('platename') ? input('platename') : '';
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
            Common::adminLog('添加版块:' . $data['platename']);
            return Common::ReturnSuccess("添加成功");
        } else {
            return Common::ReturnError("添加失败");
        }
    }

    public function deleteplate()
    {
        $appid = input('id');
        $app = Plate::destroy($appid);
        $platepost = PlatePost::destroy(['plateid' => $appid]);
        Common::adminLog('删除版块:' . $appid);
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
        $user = User::where('username', $data['admin'])->find();
        if ($user == null) {
            return Common::ReturnError('用户不存在');
        }
        $db = Plate::where('id', $data['id'])->update($data);
        if ($db > 0) {
            Common::adminLog('修改版块:' . $data['platename']);
            return Common::ReturnSuccess("修改成功");
        } else {
            return Common::ReturnError("修改失败");
        }
    }

    public function platepost()
    {
        return $this->fetch('bbs/platepost');
    }

    public function platepostlist()
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        $sort = input('sort') ? input('sort') : 'id';
        $sortOrder = input('sortOrder') ? input('sortOrder') : 'asc';
        $postname = input('postname') ? input('postname') : '';
        $listPost = Db::name('post')
            ->alias('p')
            ->join('app a', 'a.appid=p.appid')
            ->join('plate q', 'q.id = p.plateid')
            ->where('postname', "like", '%' . $postname . '%')
            ->field('p.*,a.appname,q.platename,(SELECT COUNT(*) FROM mr_comment as c WHERE c.postid  = p.id) as commentnum')
            ->order($sort, $sortOrder)
            ->limit($limit)
            ->page($page)
            ->select();
        $listPostcount = Db::name('post')
            ->alias('p')
            ->join('app a', 'a.appid=p.appid')
            ->join('plate q', 'q.id = p.plateid')
            ->where('postname', "like", '%' . $postname . '%')
            ->field('p.*,a.appname,q.platename,(SELECT COUNT(*) FROM mr_comment as c WHERE c.postid  = p.id) as commentnum')
            ->distinct(true)
            ->count();
        return json(['rows' => $listPost, 'total' => $listPostcount]);
    }

    public function delplatepost()
    {
        $id = input('id');
        $post = PlatePost::destroy($id);
        Common::adminLog('删除帖子:' . $id);
        return Common::ReturnSuccess("删除成功");
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
            Common::adminLog('修改帖子:' . $data['postname']);
            return Common::ReturnSuccess("修改成功");
        } else {
            return Common::ReturnError("修改失败");
        }
    }

    public function auditpost()
    {
       return $this->fetch('bbs/auditpost');
    }

    //审核帖子
    public function auditcheckpost()
    {
        $id = input('id');
        $audit_result = input('audit_result');
        $post = Db::name('post')->where('id', $id)->update(['is_audit' => $audit_result]);
        if ($post > 0) {
            Common::adminLog('审核帖子:' . $id);
            return Common::ReturnSuccess("审核成功");
        } else {
            return Common::ReturnError("审核失败");
        }
    }

    //通过全部帖子
    public function auditallpost()
    {
        $post = Db::name('post')->where('is_audit', 1)->update(['is_audit' => 0]);
        if ($post > 0) {
            Common::adminLog('审核帖子全部');
            return Common::ReturnSuccess("审核成功");
        } else {
            return Common::ReturnError("审核失败");
        }
    }

}
