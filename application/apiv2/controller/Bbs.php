<?php

namespace app\apiv2\controller;

use app\apiv2\controller\Base;
use app\admin\model\App as ModelApp;
use app\admin\model\Comment;
use app\admin\model\Likepost as ModelLikePost;
use app\admin\model\Message;
use app\admin\model\Plate as ModelPlate;
use app\admin\model\User as ModelUser;
use app\admin\model\PlatePost as ModelPost;
use think\Db;
use think\facade\Validate;
use think\Request;
use app\common\controller\Common;
use think\facade\Cookie;

class Bbs extends Base
{
    /**
     * 获取版块列表
     *
     * @param Request $request
     * @return void
     */
    public function GetPlateList(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $plates = ModelPlate::where('appid', $data['appid'])->select();
        return $this->returnSuccess("查询成功", $plates);
    }

    /**
     * 获取版块帖子
     */
    public function GetPostList(Request $request)
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $plate = ModelPlate::get($data['id']);
        if (!$plate) {
            return $this->returnError('没有此版块');
        }
        $result = Db::name('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $data['appid'])
            ->where('p.plateid', $data['id'])
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->field("(select count(*) from mr_post where plateid = {$data['id']}) as postnum")
            ->order('p.replytime', 'desc')
            ->limit($limit)
            ->page($page)
            ->select();
        return $this->returnSuccess("查询成功", $result);
    }

    /**
     * 获取帖子详情
     */
    public function GetPost(Request $request)
    {
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $post = ModelPost::get($data['id']);
        if (!$post) {
            return $this->returnError('没有此帖子');
        }
        ModelPost::where('id', $data['id'])->update(['view' => $post['view'] + 1]);
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
        $result['posturl'] = "http://" . $_SERVER['HTTP_HOST'] . "/bbs/" . $this->lock_url($data['id']);
        return $this->returnSuccess("查询成功", $result);
    }

    /**
     * 发帖
     */
    public function AddPost(Request $request)
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
                'postname' => 'require',
                'postcontent' => 'require',
            ]);
        } else {
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
                return $this->returnError($validate->getError());
            }
            $cookiedata = [
                'username' => $data['username'],
                'usertoken' => $data['usertoken'],
                'appid' => $data['appid'],
            ];
        }
        $app = ModelApp::get($cookiedata['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $plate = ModelPlate::get($data['id']);
        if (!$plate) {
            return $this->returnError('没有此版块');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        if ($user['user_token'] != $cookiedata['usertoken']) {
            return $this->returnError('用户token错误');
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
            'username' => $cookiedata['username'],
            'plateid' => $data['id'],
            'appid' => $cookiedata['appid'],
            'replytime' => date("Y-m-d H:i:s", time()),
            'creat_time' => date("Y-m-d H:i:s", time()),
            'file' => $imgurl,
        ];
        $result = ModelPost::create($adddata);
        if ($result) {
            $updateuser = [
                'money' => $user['money'] + $app['postmoney'],
                'exp' => $user['exp'] + $app['postexp'],
            ];
            ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->update($updateuser);
            return $this->returnJson("新增成功");
        } else {
            return $this->returnError("新增失败");
        }
    }

    /**
     * 修改贴子
     *
     * @param Request $request
     */
    public function UpdatePost(Request $request)
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
                'postname' => 'require',
                'postcontent' => 'require',
            ]);
        } else {
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
                return $this->returnError($validate->getError());
            }
            $cookiedata = [
                'username' => $data['username'],
                'usertoken' => $data['usertoken'],
                'appid' => $data['appid'],
            ];
        }
        $app = ModelApp::get($cookiedata['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $post = ModelPost::get($data['id']);
        if (!$post) {
            return $this->returnError('没有此帖子');
        }
        if ($post['lock'] == 0) {
            return $this->returnError('此贴子已被锁定，无法修改');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        if ($user['user_token'] != $cookiedata['usertoken']) {
            return $this->returnError('用户token错误');
        }
        if ($user['username'] != $post['username']) {
            return $this->returnError('此帖子不是你发表的,无法修改');
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
        $result = ModelPost::where('id', $data['id'])->update($updatedata);
        if ($result > 0) {
            return $this->returnJson("修改成功");
        } else {
            return $this->returnError("修改失败");
        }
    }


    /**
     * 删除帖子
     *
     * @param Request $request
     */
    public function DeletePost(Request $request)
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
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'appid' => 'require|number',
                'id' => 'require|number',
                'username' => 'require',
                'usertoken' => 'require'
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
        $app = ModelApp::get($cookiedata['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $post = ModelPost::get($data['id']);
        if (!$post) {
            return $this->returnError('没有此帖子');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        if ($user['user_token'] != $cookiedata['usertoken']) {
            return $this->returnError('用户token错误');
        }
        if ($user['username'] != $post['username']) {
            return $this->returnError('此帖子不是你发表的,无法删除');
        }
        $result = ModelPost::where('id', $data['id'])->delete();
        if ($result > 0) {
            return $this->returnJson("删除成功");
        } else {
            return $this->returnError("删除失败");
        }
    }

    /**
     * 获取用户发表的帖子
     *
     * @param Request $request
     */
    public function GetUserPostList(Request $request)
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'appid' => 'require|number',
                'username' => 'require',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
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
            ->limit($limit)
            ->page($page)
            ->select();
        return $this->returnSuccess("查询成功", $result);
    }

    /**
     * 获取帖子评论
     *
     * @param Request $request
     */
    public function GetCommentList(Request $request)
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $post = ModelPost::get($data['id']);
        if (!$post) {
            return $this->returnError('没有此帖子');
        }
        $result = Db::name('comment')
            ->alias('c')
            ->join('plate b', 'b.id = c.plateid')
            ->join('post p', 'p.id = c.postid')
            ->join('app a', 'a.appid = c.appid')
            ->join('user u', 'u.username = c.username')
            ->where('c.postid', $data['id'])
            ->field('c.*,a.appname,u.nickname,u.usertx,u.title,p.postname,b.platename')
            ->order('c.creattime', 'desc')
            ->limit($limit)
            ->page($page)
            ->select();
        return $this->returnSuccess("查询成功", $result);
    }

    /**
     * 获取用户的评论
     *
     * @param Request $request
     */
    public function GetUserCommentList(Request $request)
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        if (Cookie::has('usertoken')) {
            $data = [
                'username' => Cookie::get('username'),
                'appid' => Cookie::get('appid'),
            ];
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'appid' => 'require|number',
                'username' => 'require',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
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
            ->limit($limit)
            ->page($page)
            ->select();
        return Common::return_msg(200, "查询成功", $result);
    }

    /**
     * 新增评论
     *
     * @param Request $request
     */
    public function AddComment(Request $request)
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
                'comment' => 'require',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'appid' => 'require|number',
                'id' => 'require|number',
                'username' => 'require',
                'comment' => 'require',
                'usertoken' => 'require'
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
        $app = ModelApp::get($cookiedata['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        if ($user->user_token != $cookiedata['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $post = ModelPost::get($data['id']);
        if (!$post) {
            return $this->returnError('没有此帖子');
        }
        $adddata = [
            'comment' => $data['comment'],
            'username' => $cookiedata['username'],
            'postid' => $data['id'],
            'plateid' => $post['plateid'],
            'appid' => $cookiedata['appid'],
            'creattime' => date("Y-m-d H:i:s", time()),
        ];
        $result = Comment::create($adddata);
        if ($result) {
            $updateuser = [
                'money' => $user['money'] + $app['commentmoney'],
                'exp' => $user['exp'] + $app['commentexp'],
            ];
            $commentid = $result->id;
            ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->update($updateuser);
            $msgcomment = Comment::get($commentid);
            $msgpost = ModelPost::get($msgcomment->postid);
            $this->msg_notification(3, $data['id'], $cookiedata['username'], $commentid, $msgpost->username, $cookiedata['appid'], date("Y-m-d H:i:s", time()));
            return $this->returnJson("评论成功");
        } else {
            return $this->returnError("评论失败");
        }
    }

    /**
     * 删除评论
     *
     * @param Request $request
     */
    public function DeleteComment(Request $request)
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
        $app = ModelApp::get($cookiedata['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        if ($user->user_token != $cookiedata['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $comment = Comment::get($data['id']);
        if (!$comment) {
            return $this->returnError('没有此评论');
        }
        if ($cookiedata['username'] != $comment['username']) {
            return $this->returnError('不是你的评论');
        }
        $result = Comment::destroy($data['id']);
        if ($result) {
            return $this->returnJson("删除成功");
        } else {
            return $this->returnError("删除失败");
        }
    }

    /**
     * 获取全部帖子
     */
    public function GetAllPostList(Request $request)
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $result = Db::name('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $data['appid'])
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->order('p.replytime', 'desc')
            ->limit($limit)
            ->page($page)
            ->select();
        return $this->returnSuccess("查询成功", $result);
    }

    /**
     * 搜索帖子
     */
    public function SearchPost(Request $request)
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
        $data = $request->param();
        $validate = Validate::make([
            'appid' => 'require|number',
            'keyword' => 'require',
        ]);
        if (!$validate->check($data)) {
            return $this->returnError($validate->getError());
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $result = Db::name('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.appid', $data['appid'])
            ->where('p.postname', 'like', '%' . $data['keyword'] . '%')
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->order('p.replytime', 'desc')
            ->limit($limit)
            ->page($page)
            ->select();
        if ($result == null) {
            return $this->returnError("没有查询到相关帖子");
        }
        return $this->returnSuccess("查询成功", $result);
    }

    /**
     * 点赞帖子
     */
    public function LikePost(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $cookiedata = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
            $data = $request->param();
            $validate = Validate::make([
                'postid' => 'require|number',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'postid' => 'require|number',
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
        $app = ModelApp::get($cookiedata['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        if ($user->user_token != $cookiedata['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $post = ModelPost::get($data['postid']);
        if (!$post) {
            return $this->returnError('没有此帖子');
        }
        $like = ModelLikePost::where('postid', $data['postid'])->where('username', $cookiedata['username'])->find();
        if ($like) {
            return $this->returnError('已点赞');
        }
        $like = new ModelLikePost();
        $like->postid = $data['postid'];
        $like->appid = $cookiedata['appid'];
        $like->username = $cookiedata['username'];
        $like->plateid = $post->plateid;
        $like->creattime = date('Y-m-d H:i:s');
        $like->save();
        $msgpost = ModelPost::get($data['postid']);
        $this->msg_notification(2, $data['postid'], $cookiedata['username'], '', $msgpost->username, $cookiedata['appid'], date("Y-m-d H:i:s", time()));
        return $this->returnJson("点赞成功");
    }

    /**
     * 取消点赞帖子
     */
    public function CancelLikePost(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $cookiedata = [
                'username' => Cookie::get('username'),
                'usertoken' => Cookie::get('usertoken'),
                'appid' => Cookie::get('appid'),
            ];
            $data = $request->param();
            $validate = Validate::make([
                'postid' => 'require|number',
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
        $app = ModelApp::get($cookiedata['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        if ($user->user_token != $cookiedata['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $post = ModelPost::get($data['postid']);
        if (!$post) {
            return $this->returnError('没有此帖子');
        }
        $like = ModelLikePost::where('postid', $data['postid'])->where('username', $cookiedata['username'])->find();
        if (!$like) {
            return $this->returnError('没有点赞');
        }
        $like->delete();
        return $this->returnJson("取消点赞成功");
    }

    /**
     * 获取用户点赞的帖子
     */
    public function GetLikePost(Request $request)
    {
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
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
                'appid' => 'require|number',
                'usertoken' => 'require',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        if ($user->user_token != $data['usertoken']) {
            return $this->returnError('用户token错误');
        }
        $result = Db::name('likepost')
            ->alias('l')
            ->join('plate b', 'l.plateid = b.id')
            ->join('post p', 'l.postid = p.id')
            ->join('app a', 'l.appid = a.appid')
            ->join('user u', 'l.username = u.username')
            ->where('l.appid', $data['appid'])
            ->where('l.username', $data['username'])
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title,b.platename,(select count(*) from mr_comment where postid = p.id) as commentnum')
            ->field('(select count(*) from mr_likepost where postid = p.id) as likenum')
            ->limit($limit)
            ->page($page)
            ->select();
        return $this->returnSuccess("获取成功", $result);
    }

    /**
     * 判断是否点赞帖子
     */
    public function IsLikePost(Request $request)
    {
        if (Cookie::has('usertoken')) {
            $cookiedata = [
                'username' => Cookie::get('username'),
                'appid' => Cookie::get('appid'),
            ];
            $data = $request->param();
            $validate = Validate::make([
                'postid' => 'require|number',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        } else {
            $data = $request->param();
            $validate = Validate::make([
                'postid' => 'require|number',
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
        $app = ModelApp::get($cookiedata['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $cookiedata['username'])->where('appid', $cookiedata['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        $post = ModelPost::get($data['postid']);
        if (!$post) {
            return $this->returnError('没有此帖子');
        }
        $like = ModelLikePost::where('postid', $data['postid'])->where('username', $cookiedata['username'])->find();
        if ($like) {
            return $this->returnSuccess("true");
        } else {
            return $this->returnError("false");
        }
    }

    /**
     * 消息通知列表
     * msgid 获取消息列表的类型 0全部 1系统消息 2点赞 3评论 
     */
    public function GetMessage(Request $request)
    {
        $msgid = input('msgid') ? input('msgid') : 0;
        $limit = input('limit') ? input('limit') : 10;
        $page = input('page') ? input('page') : 1;
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
                'appid' => 'require|number',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        switch ($msgid) {
            case '0': //全部
                $message = Message::where('appid', $data['appid'])->where('username', $data['username'])->whereOr('username', 0)->order('id desc')->limit($limit)->page($page)->select();
                Message::where('appid', $data['appid'])->where('username', $data['username'])->whereOr('username', 0)->update(['isread' => 1]);
                //循环遍历信息
                $returnmsgalert = [];
                foreach ($message as $key => $value) {
                    if ($message[$key]['msgid'] == 1) {
                        $returnmsgalert['msgtype'] = "系统消息";
                        $returnmsgalert['msgcontent'] = $message[$key]['content'];
                        $returnmsgalert['creattime'] = $message[$key]['creattime'];
                    } else if ($message[$key]['msgid'] == 2) {
                        $returnmsgalert['msgtype'] = "点赞信息";
                        $post = ModelPost::get($message[$key]['postid']);
                        $returnmsgalert['msgcontent'] = "用户" . $message[$key]['userid'] . "点赞了您的文章[" . $post->postname . "]";
                        $returnmsgalert['creattime'] = $message[$key]['creattime'];
                    } else {
                        $returnmsgalert['msgtype'] = "评论信息";
                        $post = ModelPost::get($message[$key]['postid']);
                        $comment = Comment::get($message[$key]['commentid']);
                        $returnmsgalert['msgcontent'] = "用户" . $message[$key]['userid'] . "评论了您的文章[" . $post->postname . "],评论内容为" . $comment->comment;
                        $returnmsgalert['creattime'] = $message[$key]['creattime'];
                    }
                    $returnmsg[] = $returnmsgalert;
                }
                return $this->returnSuccess("查询成功", $returnmsg);
                break;
            case '1': //系统消息
                $message = Message::where('username', 0)->where('appid', $data['appid'])->where('msgid', 1)->order('id desc')->limit($limit)->page($page)->select();
                Message::where('username', 0)->where('appid', $data['appid'])->where('msgid', 1)->update(['isread' => 1]);
                //循环遍历信息
                $returnmsgalert = [];
                foreach ($message as $key => $value) {
                    $returnmsgalert['msgtype'] = "系统消息";
                    $returnmsgalert['msgcontent'] = $message[$key]['content'];
                    $returnmsgalert['creattime'] = $message[$key]['creattime'];
                    $returnmsg[] = $returnmsgalert;
                }
                return $this->returnSuccess("查询成功", $returnmsg);
                break;
            case '2': //点赞信息
                $message = Message::where('username', $data['username'])->where('appid', $data['appid'])->where('msgid', 2)->order('id desc')->limit($limit)->page($page)->select();
                Message::where('username', $data['username'])->where('appid', $data['appid'])->where('msgid', 2)->update(['isread' => 1]);
                //循环遍历信息
                $returnmsgalert = [];
                foreach ($message as $key => $value) {
                    $returnmsgalert['msgtype'] = "点赞信息";
                    $post = ModelPost::get($message[$key]['postid']);
                    $returnmsgalert['msgcontent'] = "用户" . $message[$key]['userid'] . "点赞了您的文章[" . $post->postname . "]";
                    $returnmsg[] = $returnmsgalert;
                }
                return $this->returnSuccess("查询成功", $returnmsg);
                break;
            case '3': //评论信息
                $message = Message::where('username', $data['username'])->where('appid', $data['appid'])->where('msgid', 3)->order('id desc')->limit($limit)->page($page)->select();
                Message::where('username', $data['username'])->where('appid', $data['appid'])->where('msgid', 3)->update(['isread' => 1]);
                //循环遍历信息
                $returnmsgalert = [];
                foreach ($message as $key => $value) {
                    $returnmsgalert['msgtype'] = "评论信息";
                    $post = ModelPost::get($message[$key]['postid']);
                    $comment = Comment::get($message[$key]['commentid']);
                    $returnmsgalert['msgcontent'] = "用户" . $message[$key]['userid'] . "评论了您的文章[" . $post->postname . "],评论内容为" . $comment->comment;
                    $returnmsg[] = $returnmsgalert;
                }
                return $this->returnSuccess("查询成功", $returnmsg);
                break;
            default:
                return $this->returnError('获取失败');
                break;
        }
    }

    /**
     * 获取未读消息数量
     */
    public function getUnreadMessageCount(Request $request)
    {
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
                'appid' => 'require|number',
            ]);
            if (!$validate->check($data)) {
                return $this->returnError($validate->getError());
            }
        }
        $app = ModelApp::get($data['appid']);
        if (!$app) {
            return $this->returnError('没有此app');
        }
        $user = ModelUser::where('username', $data['username'])->where('appid', $data['appid'])->find();
        if (!$user) {
            return $this->returnError('没有此用户');
        }
        $count = Message::where('isread', 0)->where('appid', $data['appid'])->where('username', $data['username'])->count();
        return $this->returnSuccess("查询成功", $count);
    }
}
