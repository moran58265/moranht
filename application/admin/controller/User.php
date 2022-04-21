<?php

namespace app\admin\controller;

use app\admin\model\User as UserModel;
use app\common\controller\Common;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class User extends BaseController
{
    public function index()
    {
        return $this->fetch();
    }
//获取所有信息
    public function GetUserlist()
    {
        $limit = input('limit') ?? 10;
        $page = input('page') ?? 1;
        $sort = input('sort') ?? 'id';
        $sortOrder = input('sortOrder') ?? 'desc';
        $username = input('username') ?? '';
        try {
            $appList = Db::name('user')->alias('u')
                ->join('app a', 'a.appid=u.appid')
                ->field('u.id,u.username,u.usertx,u.nickname,a.appname,u.useremail,FROM_UNIXTIME(u.creattime,"%Y-%m-%d") as creattime,u.banned')
                ->where('u.username', "like", '%' . $username . '%')
                ->order($sort, $sortOrder)
                ->limit($limit)
                ->page($page)
                ->select();
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        return json(['rows' => $appList,'total' => UserModel::count()]);
    }


    //删除用户
    public function deleteuser()
    {
        $id = input('id');
        $app = UserModel::destroy($id);
        return Common::ReturnSuccess("删除成功");
    }

    //修改用户禁用状态
    public function editfastatus()
    {
        $id = explode(",",input('id'));
        for ($i=0;$i<count($id);$i++)
        {
            $app = UserModel::get($id[$i]);
            $app->banned = 'true';
            $app->save();
        }
        return Common::ReturnSuccess("修改成功");
    }

    //修改用户正常状态
    public function edittrstatus()
    {
        $id = explode(",",input('id'));
        for ($i=0;$i<count($id);$i++)
        {
            $app = UserModel::get($id[$i]);
            $app->banned = 'false';
            $app->save();
        }
        return Common::ReturnSuccess("修改成功");
    }

    //修改app
    public function queryuser()
    {
        $id = input('id');
        $user = UserModel::get($id);
        return $this->fetch()->assign('user',$user);
    }
    //修改app信息
    public function edituser()
    {
        $data = input('post.');
        $user = new UserModel();
        $data['viptime'] = strtotime($data['viptime']);
        $res = $user->save($data, ['id' => $data['id']]);
        if ($res) {
            return Common::ReturnSuccess('修改成功');
        } else {
            return Common::ReturnError('修改失败');
        }
    }

}