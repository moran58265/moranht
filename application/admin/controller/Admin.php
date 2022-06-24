<?php

namespace app\admin\controller;

use app\admin\controller\Common;
use think\facade\Cache;
use think\facade\Session;
use app\admin\model\Admin as AdminModel;
use think\facade\Request;

class Admin extends BaseController
{
    //修改管理员信息
    public function admininfo()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $validate = validate('Admin');
            if (!$validate->scene('edit')->check($data)) {
                return Common::ReturnError($validate->getError());
            }
            $Admininfo = AdminModel::where('id', Session::get('admin_id'))->find();
            $admintoken = md5($Admininfo['password'] . $Admininfo['salt']);
            if ($admintoken != Session::get('adminToken')) {
                return Common::ReturnError('非法操作');
            }
            $AdminModel = new AdminModel();
            $res = $AdminModel->allowField(true)->save($data, ['id' => Session::get('admin_id')]);
            if ($res) {
                $admininfo = AdminModel::get(Session::get('admin_id'));
                session('admininfo', $admininfo->toArray());
                Common::adminLog('修改管理员信息');
                return Common::ReturnSuccess('修改成功');
            } else {
                return Common::ReturnError('修改失败');
            }
        } else {
            return $this->fetch('admin/admininfo');
        }
    }

    public function geneadminkey()
    {
        $admintoken = md5(Common::getRandChar(10));
        $admininfo = AdminModel::get(Session::get('admin_id'));
        $admininfo->admintoken = $admintoken;
        $admininfo->save();
        session('admininfo', $admininfo->toArray());
        return Common::ReturnSuccess($admintoken);
    }

    public function downadmintoken()
    {
        $admininfo = AdminModel::get(Session::get('admin_id'));
        $admininfo->admintoken = "";
        $admininfo->save();
        session('admininfo', $admininfo->toArray());
        Common::adminLog('关闭管理员密钥');
        return Common::ReturnSuccess("关闭成功");
    }

    //修改管理员密码
    public function changepwd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $validate = validate('Admin');
            if (!$validate->scene('password')->check($data)) {
                return Common::ReturnError($validate->getError());
            }
            $Admininfo = AdminModel::get(Session::get('admin_id'));
            if ($Admininfo['password'] != md5($data['old_password'])) {
                return Common::ReturnError('原密码错误');
            }
            $admintoken = md5($Admininfo['password'] . $Admininfo['salt']);
            if ($admintoken != Session::get('adminToken')) {
                return Common::ReturnError('非法操作');
            }
            $AdminModel = new AdminModel();
            $res = $AdminModel->save(['password' => md5($data['new_password'])], ['id' => Session::get('admin_id')]);
            if ($res) {
                Common::adminLog('修改管理员密码');
                return Common::ReturnSuccess('修改成功');
            } else {
                return Common::ReturnError('修改失败');
            }
        } else {
            return $this->fetch('admin/changepwd');
        }
    }

    //退出登录
    public function loginout()
    {
        Session::clear();
        return Common::ReturnSuccess('退出成功');
    }

    //清楚缓存
    public function clear_all()
    {
        $pathArr = [
            'LOG_PATH'   => env('runtime_path') . 'log/',
            'CACHE_PATH' => env('runtime_path') . 'cache/',
            'TEMP_PATH'  => env('runtime_path') . 'temp/'
        ];
        $dirs = (array) glob($pathArr['LOG_PATH'] . '*');
        foreach ($dirs as $dir) {
            array_map('unlink', glob($dir . '/*.log'));
        }
        array_map('rmdir', $dirs);
        array_map('unlink', glob($pathArr['TEMP_PATH'] . '/*.*'));
        Cache::clear();
        Common::adminLog('清除系统缓存');
        return Common::ReturnSuccess('清除成功');
    }

    //管理员日志
    public function adminlog()
    {
        return $this->fetch('admin/adminlog');
    }

    //管理员日志列表
    public function adminloglist()
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        $sort = input('sort') ? input('sort') : 'id';
        $sortOrder = input('sortOrder') ? input('sortOrder') : 'asc';
        $res = db('adminlog')->order($sort . ' ' . $sortOrder)->limit($limit)->page($page)->select();
        $count = db('adminlog')->count();
        return json(['rows' => $res, 'total' => $count]);
    }
}
