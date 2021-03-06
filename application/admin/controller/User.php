<?php

namespace app\admin\controller;

use app\admin\model\User as UserModel;
use app\admin\validate\User as UserValidate;
use app\admin\controller\Common;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;

class User extends BaseController
{
    public function index()
    {
        return $this->fetch();
    }
    //获取所有信息
    public function GetUserlist()
    {
        $nowdaytime = strtotime(date('Y-m-d'));
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        $sort = input('sort') ? input('sort') : 'id';
        $sortOrder = input('sortOrder') ? input('sortOrder') : 'desc';
        $username = input('username') ? input('username') : '';
        $ipaddress = input('ipaddress') == 0 ? 2 : input('ipaddress');
        $fvip = input('fvip') == 'true' ? time() : 1;
        $fsign = input('fsign') == 'true' ? $nowdaytime : 0;
        try {
            $appList = Db::name('user')->alias('u')
                ->join('app a', 'a.appid=u.appid')
                ->field('u.id,u.username,u.usertx,u.nickname,a.appname,u.useremail,FROM_UNIXTIME(u.creattime,"%Y-%m-%d %H:%i:%s") as creattime,u.banned,ip')
                ->where('u.username', "like", '%' . $username . '%')
                ->where('u.viptime', ">=", $fvip)
                ->where('u.signtime', ">=", $fsign)
                ->order($sort, $sortOrder)
                ->limit($limit)
                ->page($page)
                ->select();
            $datauserinfo = array();
            foreach ($appList as $key => $value) {
                $datauserinfo[$key]['id'] = $value['id'];
                $datauserinfo[$key]['username'] = $value['username'];
                $datauserinfo[$key]['usertx'] = $value['usertx'];
                $datauserinfo[$key]['nickname'] = $value['nickname'];
                $datauserinfo[$key]['appname'] = $value['appname'];
                $datauserinfo[$key]['useremail'] = $value['useremail'];
                $datauserinfo[$key]['creattime'] = $value['creattime'];
                $datauserinfo[$key]['banned'] = $value['banned'];
                if ($ipaddress == 2) {
                    $datauserinfo[$key]['ip'] = $value['ip'];
                } else {
                    $datauserinfo[$key]['ip'] = $value['ip'] . "(" . Common::get_ip_address($value['ip']) . ")";
                }
            }
            $appcount = Db::name('user')
                ->alias('u')
                ->join('app a', 'a.appid=u.appid')
                ->field('u.id,u.username,u.usertx,u.nickname,a.appname,u.useremail,FROM_UNIXTIME(u.creattime,"%Y-%m-%d %H:%i:%s") as creattime,u.banned')
                ->where('u.username', "like", '%' . $username . '%')
                ->where('u.viptime', ">=", $fvip)
                ->where('u.signtime', ">=", $fsign)
                ->count();
        } catch (DataNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::ReturnError($e->getMessage());
        } catch (DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        return json(['rows' => $datauserinfo, 'total' => $appcount]);
    }


    //删除用户
    public function deleteuser()
    {
        $id = input('id');
        $app = UserModel::destroy($id);
        Common::adminLog('删除用户'.$id);
        return Common::ReturnSuccess("删除成功");
    }

    //修改用户禁用状态
    public function editfastatus()
    {
        $id = explode(",", input('id'));
        for ($i = 0; $i < count($id); $i++) {
            $app = UserModel::get($id[$i]);
            $app->banned = 'true';
            $app->save();
        }
        Common::adminLog('修改用户禁用状态'.input('id'));
        return Common::ReturnSuccess("修改成功");
    }

    //修改用户正常状态
    public function edittrstatus()
    {
        $id = explode(",", input('id'));
        for ($i = 0; $i < count($id); $i++) {
            $app = UserModel::get($id[$i]);
            $app->banned = 'false';
            $app->save();
        }
        Common::adminLog('修改用户正常状态'.input('id'));
        return Common::ReturnSuccess("修改成功");
    }

    //修改user
    public function queryuser()
    {
        $id = input('id');
        $user = UserModel::get($id);
        return $this->fetch()->assign('user', $user);
    }
    //修改user信息
    public function edituser()
    {
        $data = input('post.');
        $user = new UserModel();
        $userpassword = $user->where('id', $data['id'])->value('password');
        if (input('post.password') == "" || input('post.password') == null) {
            $data['password'] = $userpassword;
        } else {
            $data['password'] = md5(input('post.password'));
        }
        $data['viptime'] = strtotime($data['viptime']);
        $res = $user->save($data, ['id' => $data['id']]);
        if ($res) {
            Common::adminLog('修改用户信息'.$data['id']);
            return Common::ReturnSuccess('修改成功');
        } else {
            return Common::ReturnError('修改失败');
        }
    }

    //添加用户
    public function adduser()
    {
        $data = input('post.');
        $validate = new UserValidate();
        if (!$validate->scene('adduser')->check($data)) {
            return Common::ReturnError($validate->getError());
        }
        $useremail = UserModel::get(['useremail' => $data['useremail']]);
        $username = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        $app = \app\admin\model\App::get($data['appid']);
        if ($app == null) {
            return Common::ReturnError('应用不存在');
        }
        if ($username != null) {
            return Common::ReturnError('用户名已存在');
        }
        if ($useremail != null) {
            return Common::ReturnError('邮箱已存在');
        }
        //截取@qq.com前面的字符串
        $user = new UserModel();
        $data['password'] = md5($data['password']);
        $data['viptime'] = time() + $app->zcvip * 24 * 60 * 60;
        $data['money'] = $app->zcmoney;
        $data['exp'] = $app->zcexp;
        $data['usertx'] =  Request::scheme() . "://" . Request::host() . "/" . "usertx.png";
        $data['creattime'] = time();
        $data['qq'] = substr($data['useremail'], 0, strpos($data['useremail'], '@'));
        $res = $user->save($data);
        Common::adminLog('添加用户'.$data['username']);
        return Common::ReturnSuccess('添加成功');
    }

    public function getallinvitecode()
    {
        $user = UserModel::where('invitecode is NULL')->all();
        if (count($user) == 0) {
            return Common::ReturnError('没有用户可以生成邀请码');
        } else {
            foreach ($user as $key => $value) {
                $id = $value->id;
                $username = $value->username;
                $invitecode = Common::getinvitacode($username);
                $updateuser = UserModel::get($id);
                $updateuser->invitecode = $invitecode;
                $updateuser->save();
            }
            Common::adminLog('生成所有用户邀请码');
            return Common::ReturnSuccess('所有用户邀请码已生成');
        }
    }
}
