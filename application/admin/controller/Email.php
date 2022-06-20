<?php

namespace app\admin\controller;

use app\admin\controller\Common;

class Email extends BaseController
{
    public function index()
    {
        $email = \app\admin\model\Email::get(1);
        return $this->fetch()->assign(['email' => $email, 'title' => '邮箱设置',]);
    }

    //测试邮件发送
    public function TestEmail()
    {
        $data = input('post.');
        $email = \app\admin\model\Email::get(1);
        if ($email['username'] == ""){
            return Common::ReturnError("你还没有配置邮箱呢");
        }
        if ($data['test_email'] == ""){
            return Common::ReturnError("你还没有输入测试邮箱呢");
        }
        $res = Common::send_mail($data['test_email'], '测试邮件', '你看到这封邮件说明邮件配置成功');
        if ($res) {
            Common::adminLog("测试邮件发送成功".$data['test_email']);
            return Common::ReturnSuccess('发送成功');
        } else {
            return Common::ReturnError('发送失败');
        }
    }

    //保存邮箱设置
    public function save()
    {
        $data = input('post.');
        $email = new \app\admin\model\Email();
        $email->save($data, ['id' => 1]);
        Common::adminLog("保存邮箱设置");
        return Common::ReturnSuccess('保存成功');
    }



}