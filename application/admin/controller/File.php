<?php

namespace app\admin\controller;

use app\common\controller\Common;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\Request;


class File extends BaseController
{
    public function index(){
        try {
            $listfile = Db::name('upload')->paginate(10);
        } catch (DbException $e) {
            return Common::ReturnError($e->getMessage());
        }
        $page = $listfile->render();
        return $this->fetch('/file/index', ['list' => $listfile, 'page' => $page]);
    }

    public function deletefile(Request $request){
        $data = $request->post();
        try {
            $file = Db::name('upload')->where('id',$data['id'])->find();
        } catch (PDOException $e) {
            return Common::ReturnError($e->getMessage());
        } catch (Exception $e) {
            return Common::ReturnError( $e->getMessage());
        }
        if(file_exists(".".$file['filePath'])){
            $res = unlink(".".$file['filePath']);
            if ($res){
                try {
                    $delfile = Db::name('upload')->where('id', $data['id'])->delete();
                } catch (PDOException $e) {
                    return Common::ReturnError($e->getMessage());
                } catch (Exception $e) {
                    return Common::ReturnError($e->getMessage());
                }
                return Common::ReturnSuccess("删除成功");
            }else{
                return Common::ReturnError('删除失败');
            }
        }else{
            return Common::ReturnError('服务器错误');
        }
    }

}