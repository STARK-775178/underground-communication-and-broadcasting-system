<?php

namespace app\admin\model\communication;

use think\Model;

/**
 * CallRecord
 */
class CallRecord extends Model
{



    // 使用默认连接
    protected $connection = 'mysql2';
    // 表名
    protected $name = 'cdr';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

}