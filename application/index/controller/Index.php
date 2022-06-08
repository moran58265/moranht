<?php
namespace app\index\controller;

use app\admin\model\Notes;
use app\common\controller\Common;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改（2006-2018） - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
    }

    public function querynotes($id)
    {
        $notes = Db::name('notes')
            ->alias('n')
            ->join('user u', 'n.username = u.username')
            ->where('n.id', Common::unlock_url($id))
            ->field('n.*,u.usertx')
            ->find();
        return $this->fetch()->assign('notes', $notes);
    }

    public function querypost($id)
    {
        $result = Db::name('post')
            ->alias('p')
            ->join('plate b', 'b.id = p.plateid')
            ->join('app a', 'a.appid = p.appid')
            ->join('user u', 'u.username = p.username')
            ->where('p.id', Common::unlock_url($id))
            ->field('p.*,a.appname,u.nickname,u.usertx,u.title')
            ->find();
            $file = explode(',', $result['file']);
            //去除空数组
            $file = array_filter($file);
            $comment = Db::name('comment')
            ->alias('c')
            ->join('plate b', 'b.id = c.plateid')
            ->join('post p', 'p.id = c.postid')
            ->join('app a', 'a.appid = c.appid')
            ->join('user u', 'u.username = c.username')
            ->where('c.postid', Common::unlock_url($id))
            ->field('c.*,a.appname,u.nickname,u.usertx,u.title,p.postname,b.platename')
            ->order('c.creattime', 'desc')
            ->limit(10)
            ->page(1)
            ->select();
            // return json_encode($comment);
            $app = Db::name('app')->where('appid', $result['appid'])->find();
            // return json_encode($app);
        return $this->fetch()->assign('postdata', $result)->assign('file', $file)->assign('comment', $comment)->assign('app', $app);
    }

}
