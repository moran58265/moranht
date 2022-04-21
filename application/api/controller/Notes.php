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

class Notes extends Controller
{
    public function GetNotesList(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username'  => 'require',
            'appid' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        if ($user == "" || $user == null){
            return Common::return_msg(400,"没有该用户");
        }
        try {
            $notes = Db::name('notes')
                ->alias('n')
                ->where('n.username', $data['username'])
                ->where('n.appid', $data['appid'])
                ->join('app a', 'a.appid = n.appid')
                ->field('n.*,a.appname')
                ->select();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        return Common::return_msg(200,"查询成功",$notes);
    }

    public function UpdateNotes(Request $request){
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
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->find();
            $notes = Db::name('notes')->where('id',$data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        if ($user == "" || $user == null){
            return Common::return_msg(400,"没有该用户");
        }
        if ($notes == "" || $notes == null){
            return Common::return_msg(400,"不存在此留言");
        }
        if ($user['user_token'] != $data['usertoken']){
            return Common::return_msg(400,"token过期");
        }
        if ($notes['username'] != $data['username']){
            return Common::return_msg(400,"此笔记不是你发表的,无法修改");
        }
        $updatenotesdata = [
            'title' => $data['title'],
            'content' => html_entity_decode($data['content']),
            'ip' => Common::get_user_ip(),
            'updatetime' => date("Y-m-d H:i:s",time()),
        ];
        try {
            $updatenotes = Db::name('notes')->where('id', $data['id'])->update($updatenotesdata);
        } catch (PDOException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (Exception $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        return Common::return_msg(200,"修改成功");
    }

    public function deleteNotes(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username'  => 'require',
            'appid' => 'require|number',
            'usertoken' => 'require',
            'id' => 'require|number',
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->find();
            $notes = Db::name('notes')->where('id',$data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        if ($user == "" || $user == null){
            return Common::return_msg(400,"没有该用户");
        }
        if ($notes == "" || $notes == null){
            return Common::return_msg(400,"不存在此留言");
        }
        if ($user['user_token'] != $data['usertoken']){
            return Common::return_msg(400,"token过期");
        }
        if ($notes['username'] != $data['username']){
            return Common::return_msg(400,"此笔记不是你发表的,无法删除");
        }
        try {
            $delnotes = Db::name('notes')->where('id', $data['id'])->delete();
        } catch (PDOException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (Exception $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        if ($delnotes > 0){
            return Common::return_msg(200,"删除成功");
        }else{
            return Common::return_msg(400,"修改失败");
        }
    }

    public function addNotes(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username'  => 'require',
            'appid' => 'require|number',
            'usertoken' => 'require',
            'title' => 'require',
            'content' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        if ($user == "" || $user == null){
            return Common::return_msg(400,"没有该用户");
        }
        if ($user['user_token'] != $data['usertoken']){
            return Common::return_msg(400,"token过期");
        }
        $addnotes = [
            'title' => $data['title'],
            'content' => $data['content'],
            'ip' => Common::get_user_ip(),
            'creattime' => date("Y-m-d H:i:s",time()),
            'updatetime' => date("Y-m-d H:i:s",time()),
            'appid' => $data['appid'],
            'username' => $data['username']
        ];
        $addnotes = Db::name('notes')->insert($addnotes);
        if ($addnotes > 0){
            return Common::return_msg(200,"保存成功");
        }else{
            return Common::return_msg(400,"保存失败");
        }
    }


    public function GetNotes(Request $request){
        $data = $request->param();
        $validate = Validate::make([
            'username'  => 'require',
            'appid' => 'require|number',
            'id' => 'require'
        ]);
        if (!$validate->check($data)) {
            return Common::return_msg(400,$validate->getError());
        }
        try {
            $app = Db::name('app')->where('appid', $data['appid'])->find();
            $user = Db::name('user')->where('username',$data['username'])->where('appid', $data['appid'])->find();
            $notes = Db::name('notes')->where('id', $data['id'])->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        if ($app == "" || $app == null){
            return Common::return_msg(400,"没有此app");
        }
        if ($user == "" || $user == null){
            return Common::return_msg(400,"没有该用户");
        }
        if ($notes == "" || $notes == null){
            return Common::return_msg(400,"不存在该笔记");
        }
        try {
            $getnotes = Db::name('notes')
                ->alias('n')
                ->where('n.id',$data['id'])
                ->join('app a', 'a.appid = n.appid')
                ->field('n.*,a.appname')
                ->find();
        } catch (DataNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (ModelNotFoundException $e) {
            return Common::return_msg(400,$e->getMessage());
        } catch (DbException $e) {
            return Common::return_msg(400,$e->getMessage());
        }
        return Common::return_msg(200,"查询成功",$getnotes);
    }



}