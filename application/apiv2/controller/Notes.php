<?php

namespace app\apiv2\controller;

use app\admin\model\App as ModelApp;
use app\admin\model\Notes as ModelNotes;
use app\admin\model\User as ModelUser;
use think\Db;
use think\facade\Validate;
use think\Request;
use app\common\controller\Common;
use think\facade\Cookie;

class Notes extends Base
{
    /**
     * 获取用户所有笔记
     *
     * @param Request $request
     */
    public function GetNotesList(Request $request)
    {
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username' => 'require',
                'usertoken' => 'require',
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
        if ($user->user_token != $data['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $notes = Db::name('notes')
            ->alias('n')
            ->where('n.username', $data['username'])
            ->where('n.appid', $data['appid'])
            ->join('app a', 'a.appid = n.appid')
            ->field('n.*,a.appname')
            ->limit($limit)
            ->page($page)
            ->select();
        return $this->returnSuccess("查询成功", $notes);
    }

    /**
     * 更新笔记
     *
     * @param Request $request
     */
    public function UpdateNotes(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $data = $request->param();
            $validate = Validate::make([
                'id' => 'require|number',
                'title' => 'require',
                'content' => 'require'
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
            $cookiedata = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username'  => 'require',
                'appid' => 'require|number',
                'usertoken' => 'require',
                'id' => 'require|number',
                'title' => 'require',
                'content' => 'require'
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
        $notes = ModelNotes::where('id', $data['id'])->find();
        if (!$notes) {
            return $this->returnError('笔记不存在');
        }
        if ($notes['username'] != $cookiedata['username']) {
            return $this->returnError('笔记不属于你');
        }
        $updatenotesdata = [
            'title' => $data['title'],
            'content' => html_entity_decode($data['content']),
            'ip' => Common::get_user_ip(),
            'updatetime' => date("Y-m-d H:i:s", time()),
        ];
        $result = ModelNotes::where('id', $data['id'])->update($updatenotesdata);
        if ($result) {
            return $this->returnJson("更新成功");
        } else {
            return $this->returnError("更新失败");
        }
    }

    /**
     * 删除笔记
     *
     * @param Request $request
     */
    public function deleteNotes(Request $request)
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
        $notes = ModelNotes::where('id', $data['id'])->find();
        if (!$notes) {
            return $this->returnError('笔记不存在');
        }
        if ($notes['username'] != $cookiedata['username']) {
            return $this->returnError('此笔记不是你发表的,无法删除');
        }
        $result = ModelNotes::where('id', $data['id'])->delete();
        if ($result) {
            return $this->returnJson("删除成功");
        } else {
            return $this->returnError("删除失败");
        }
    }

    /**
     * 新增笔记
     *
     * @param Request $request
     */
    public function addNotes(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $cookiedata = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
            $data = $request->param();
            $validate = Validate::make([
                'title' => 'require',
                'content' => 'require'
            ]);
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'username'  => 'require',
                'appid' => 'require|number',
                'usertoken' => 'require',
                'title' => 'require',
                'content' => 'require'
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
        $addnotes = [
            'title' => $data['title'],
            'content' => $data['content'],
            'ip' => Common::get_user_ip(),
            'creattime' => date("Y-m-d H:i:s", time()),
            'updatetime' => date("Y-m-d H:i:s", time()),
            'appid' => $data['appid'],
            'username' => $data['username']
        ];
        $result = ModelNotes::create($addnotes);
        if ($result) {
            return $this->returnJson("新增成功");
        } else {
            return $this->returnError("新增失败");
        }
    }


    /**
     * 获取笔记信息
     *
     * @param Request $request
     */
    public function GetNotes(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $cookiedata = [
                'username' => Cookie::get('username'),
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
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
            $cookiedata = [
                'username' => $data['username'],
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
        //关联查询
        $getnotes = Db::name('notes')
        ->alias('n')
        ->where('n.id',$data['id'])
        ->join('app a', 'a.appid = n.appid')
        ->field('n.*,a.appname')
        ->find();
        $getnotes['notesurl'] = "http://".$_SERVER['HTTP_HOST']."/notes/". $this->lock_url($getnotes['id']);
        return $this->returnSuccess("查询成功", $getnotes);
    }
}
