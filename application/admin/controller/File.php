<?php

namespace app\admin\controller;

use app\admin\model\File as ModelFile;
use app\admin\controller\Common;


class File extends BaseController
{
    public function index(){
        return $this->fetch();
    }

    //获取所有附件信息
    public function getfilelist()
    {
        $limit = input('limit')?input('limit'):10;
        $page = input('page')?input('page'):1;
        $sort = input('sort')?input('sort'):'id';
        $sortOrder = input('sortOrder')?input('sortOrder'):'asc';
        $file = ModelFile::order($sort,$sortOrder)->limit($limit)->page($page)->select();
        $count = ModelFile::count();
        return json(['rows' => $file,'total' => $count]);
    }

    //删除用户
    public function deletefile()
    {
        $id = input('id');
        $file = ModelFile::destroy($id);
        Common::adminLog('删除附件'.$id);
        return Common::ReturnSuccess("删除成功");
    }

}