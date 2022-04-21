<?php

namespace app\admin\model;

use app\admin\common\Common;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\facade\Request;
use think\Model;

class Shop extends Model
{
    protected $pk = 'id';
}