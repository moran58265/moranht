<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\facade\Validate;
use think\Request;
use app\common\controller\Common;

class Bbs extends Controller
{
    public function GetPlateList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        try {
            $result = Db::name('plate')->where('appid', $data['appid'])->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "查询成功", $result);
    }

    public function GetPostList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $plate = Db::name('plate')->where('id', $data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($plate == "" || $plate == null) {
            return Common::return_msg(400, "不存在此板块");
        }
        try {
            $result = Db::name('post')
                ->alias('p')
                ->join('plate b', 'b.id = p.plateid')
                ->join('app a', 'a.appid = p.appid')
                ->join('user u', 'u.username = p.username')
                ->where('p.appid', $data['appid'])
                ->where('p.plateid', $data['id'])
                ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
                ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
                ->order('p.replytime', 'desc')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "查询成功", $result);
    }

    public function GetPost(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $post = Db::name('post')->where('id', $data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($post == "" || $post == null) {
            return Common::return_msg(400, "不存在此帖子");
        }
        try {
            Db::name('post')->where('id', $data['id'])->update(['view' => $post['view'] + 1]);
            $plate = Db::name('plate')->where('id', $post['plateid'])->find();
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        try {
            $result = Db::name('post')
                ->alias('p')
                ->join('plate b', 'b.id = p.plateid')
                ->join('app a', 'a.appid = p.appid')
                ->join('user u', 'u.username = p.username')
                ->where('p.appid', $data['appid'])
                ->where('p.id', $data['id'])
                ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
                ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
                ->find();
            $result['posturl'] = "http://" . $_SERVER['HTTP_HOST'] . "/bbs/" . Common::lock_url($data['id']);
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "查询成功", $result);
    }

    public function AddPost(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
            'username' => 'require',
            'postname' => 'require',
            'postcontent' => 'require',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $plate = Db::name('plate')->where('id', $data['id'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($plate == "" || $plate == null) {
            return Common::return_msg(400, "不存在此版块");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "不存在此用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        $a = json_encode($file);
        $b = json_decode($a);
        $imgurl = '';
        foreach ($b as $v) {
            $imgurl .= $v->fullPath . ",";
        }
        $adddata = [
            'postname' => $data['postname'],
            'postcontent' => $data['postcontent'],
            'username' => $data['username'],
            'plateid' => $data['id'],
            'appid' => $data['appid'],
            'replytime' => date("Y-m-d H:i:s", time()),
            'creat_time' => date("Y-m-d H:i:s", time()),
            'file' => $imgurl,
        ];
        $result = Db::name('post')->insert($adddata);
        if ($result > 0) {
            $updateuser = [
                'money' => $user['money'] + $app['postmoney'],
                'exp' => $user['exp'] + $app['postexp'],
            ];
            try {
                Db::name('user')->where('username', $data['username'])->update($updateuser);
            } catch (PDOException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (Exception $e) {
                return Common::return_msg(400, "请求失败");
            }
            return Common::return_msg(200, "新增成功");
        } else {
            return Common::return_msg(400, "新增失败");
        }
    }

    public function UpdatePost(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
            'username' => 'require',
            'postname' => 'require',
            'postcontent' => 'require',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $post = Db::name('post')->where('id', $data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "不存在此用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        if ($post == "" || $post == null) {
            return Common::return_msg(400, "不存在此帖子");
        }
        if ($post['lock'] == 0) {
            return Common::return_msg(400,'此贴子已被锁定，无法修改');
        }
        if ($post['username'] != $data['username']) {
            return Common::return_msg(400, "此帖子不是你发表的,无法修改");
        }
        $upload = new Upload();
        $file = $upload->uploadDetail('file');
        $a = json_encode($file);
        $b = json_decode($a);
        $imgurl = '';
        foreach ($b as $v) {
            $imgurl .= $v->fullPath . ",";
        }
        $updatedata = [
            'postname' => $data['postname'],
            'postcontent' => $data['postcontent'],
            'file' => $imgurl,
        ];
        try {
            $result = Db::name('post')->where('id', $data['id'])->update($updatedata);
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($result > 0) {
            return Common::return_msg(200, "修改成功");
        } else {
            return Common::return_msg(400, "你暂未做修改");
        }
    }


    public function DeletePost(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
            'username' => 'require',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $post = Db::name('post')->where('id', $data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "不存在此用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        if ($post == "" || $post == null) {
            return Common::return_msg(400, "不存在此帖子");
        }
        if ($post['username'] != $data['username']) {
            return Common::return_msg(400, "此帖子不是你发表的,无法删除");
        }
        try {
            $result = Db::name('post')->where('id', $data['id'])->delete();
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($result > 0) {
            return Common::return_msg(200, "删除成功");
        } else {
            return Common::return_msg(400, "删除失败");
        }
    }

    public function GetUserPostList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'username' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "不存在此用户");
        }
        try {
            $result = Db::name('post')
                ->alias('p')
                ->join('plate b', 'b.id = p.plateid')
                ->join('app a', 'a.appid = p.appid')
                ->join('user u', 'u.username = p.username')
                ->where('p.appid', $data['appid'])
                ->where('p.username', $data['username'])
                ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
                ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
                ->order('p.replytime', 'desc')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "查询成功", $result);
    }

    public function GetCommentList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $post = Db::name('post')->where('id', $data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($post == "" || $post == null) {
            return Common::return_msg(400, "不存在此帖子");
        }
        try {
            $result = Db::name('comment')
                ->alias('c')
                ->join('plate b', 'b.id = c.plateid')
                ->join('post p', 'p.id = c.postid')
                ->join('app a', 'a.appid = c.appid')
                ->join('user u', 'u.username = c.username')
                ->where('c.postid', $data['id'])
                ->field('c.*,a.appname,u.nickname,u.usertx,u.title,p.postname,b.platename')
                ->order('c.creattime', 'desc')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "查询成功", $result);
    }

    public function GetUserCommentList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'username' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "不存在此用户");
        }
        try {
            $result = Db::name('comment')
                ->alias('c')
                ->join('plate b', 'b.id = c.plateid')
                ->join('post p', 'p.id = c.postid')
                ->join('app a', 'a.appid = c.appid')
                ->join('user u', 'u.username = c.username')
                ->where('c.username', $data['username'])
                ->where('p.appid', $data['appid'])
                ->field('c.*,a.appname,u.nickname,u.usertx,u.title,p.postname,b.platename')
                ->order('c.creattime', 'desc')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "查询成功", $result);
    }

    public function AddComment(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
            'username' => 'require',
            'comment' => 'require',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $post = Db::name('post')->where('id', $data['id'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($post == "" || $post == null) {
            return Common::return_msg(400, "不存在此帖子");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "不存在此用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        $adddata = [
            'comment' => $data['comment'],
            'username' => $data['username'],
            'postid' => $data['id'],
            'plateid' => $post['plateid'],
            'appid' => $data['appid'],
            'creattime' => date("Y-m-d H:i:s", time()),
        ];
        $result = Db::name('comment')->insert($adddata);
        if ($result > 0) {
            $updateuser = [
                'money' => $user['money'] + $app['commentmoney'],
                'exp' => $user['exp'] + $app['commentexp'],
            ];
            try {
                Db::name('user')->where('username', $data['username'])->update($updateuser);
            } catch (PDOException $e) {
                return Common::return_msg(400, "请求失败");
            } catch (Exception $e) {
                return Common::return_msg(400, "请求失败");
            }
            return Common::return_msg(200, "新增成功");
        } else {
            return Common::return_msg(400, "新增失败");
        }
    }

    public function DeleteComment(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
            'username' => 'require',
            'usertoken' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $comment = Db::name('comment')->where('id', $data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "不存在此用户");
        }
        if ($user['user_token'] != $data['usertoken']) {
            return Common::return_msg(400, "token过期");
        }
        if ($comment == "" || $comment == null) {
            return Common::return_msg(400, "不存在此评论");
        }
        if ($comment['username'] != $data['username']) {
            return Common::return_msg(400, "此评论不是你发表的,无法删除");
        }
        try {
            $result = Db::name('comment')->where('id', $data['id'])->delete();
        } catch (PDOException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (Exception $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($result > 0) {
            return Common::return_msg(200, "删除成功");
        } else {
            return Common::return_msg(400, "删除失败");
        }
    }

    public function GetAllPostList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        try {
            $result = Db::name('post')
                ->alias('p')
                ->join('plate b', 'b.id = p.plateid')
                ->join('app a', 'a.appid = p.appid')
                ->join('user u', 'u.username = p.username')
                ->where('p.appid', $data['appid'])
                ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
                ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
                ->order('p.replytime', 'desc')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "查询成功", $result);
    }

    /**
     * 搜索帖子
     */
    public function SearchPost(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'keyword' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        try {
            $result = Db::name('post')
                ->alias('p')
                ->join('plate b', 'b.id = p.plateid')
                ->join('app a', 'a.appid = p.appid')
                ->join('user u', 'u.username = p.username')
                ->where('p.appid', $data['appid'])
                ->where('p.title', 'like', '%' . $data['keyword'] . '%')
                ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
                ->order('p.replytime', 'desc')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if($result == "" || $result == null){
            return Common::return_msg(400, "没有搜索到结果");
        }
        return Common::return_msg(200, "查询成功", $result);
    }

    /**
     * 点赞帖子
     */
    public function LikePost(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'postid' => 'require|number',
            'username' => 'require',
            'usertoken' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $post = Db::name('post')->where('id', $data['postid'])->find();
            $like = Db::name('likepost')->where('postid', $data['postid'])->where('username', $data['username'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有此用户");
        }
        if($user['user_token'] != $data['usertoken']){
            return Common::return_msg(400, "用户token错误");
        }
        if ($post == "" || $post == null) {
            return Common::return_msg(400, "没有此帖子");
        }
        if($like != "" && $like != null){
            return Common::return_msg(400, "已点赞");
        }
        $intodata = [
            'appid' => $data['appid'],
            'postid' => $data['postid'],
            'username' => $data['username'],
            'creattime' => date('Y-m-d H:i:s'),
            'plateid' => $post['plateid'],
        ];
        try {
            $result = Db::name('likepost')->insert($intodata);
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($result == 0) {
            return Common::return_msg(400, "点赞失败");
        }
        return Common::return_msg(200, "点赞成功");
    }

    /**
     * 取消点赞帖子
     */
    public function CancelLikePost(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'postid' => 'require|number',
            'username' => 'require',
            'usertoken' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $post = Db::name('post')->where('id', $data['postid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有此用户");
        }
        if($user['user_token'] != $data['usertoken']){
            return Common::return_msg(400, "用户token错误");
        }
        if ($post == "" || $post == null) {
            return Common::return_msg(400, "没有此帖子");
        }
        try {
            $result = Db::name('likepost')->where('appid', $data['appid'])->where('postid', $data['postid'])->where('username', $data['username'])->delete();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($result == 0) {
            return Common::return_msg(400, "取消点赞失败");
        }  
        return Common::return_msg(200, "取消点赞成功");
    }

    /**
     * 获取用户的点赞帖子
     * @param Request $request
     */
    public function GetLikePost(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'username' => 'require',
            'usertoken' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有此用户");
        }
        if($user['user_token'] != $data['usertoken']){
            return Common::return_msg(400, "用户token错误");
        }
        try{
            $result = Db::name('likepost')
            ->alias('l')
            ->join('plate b', 'b.id = l.plateid')
            ->join('post p', 'p.id = l.postid')
            ->join('app a', 'a.appid = l.appid')
            ->join('user u', 'u.username = l.username')
            ->where('l.appid', $data['appid'])
            ->where('l.username', $data['username'])
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->find();
            $result['likenum'] = Db::name('likepost')->where('appid', $data['appid'])->where('postid', $result['id'])->count();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        return Common::return_msg(200, "获取成功", $result);
    }

    /**
     * 判断用户是否点赞帖子
     * @param Request $request
     */
    public function IsLikePost(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'postid' => 'require|number',
            'username' => 'require',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400, $validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username', $data['username'])->where('appid', $data['appid'])->find();
            $post = Db::name('post')->where('id', $data['postid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($app == "" || $app == null) {
            return Common::return_msg(400, "没有此app");
        }
        if ($user == "" || $user == null) {
            return Common::return_msg(400, "没有此用户");
        }
        if ($post == "" || $post == null) {
            return Common::return_msg(400, "没有此帖子");
        }
        try {
            $result = Db::name('likepost')->where('appid', $data['appid'])->where('postid', $data['postid'])->where('username', $data['username'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400, "请求失败");
        } catch (DbException $e) {
            return Common::return_msg(400, "请求失败");
        }
        if ($result == "" || $result == null) {
            return Common::return_msg(400, "false");
        }
        return Common::return_msg(200, "true");
    }

}
