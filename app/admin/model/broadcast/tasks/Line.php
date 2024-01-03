<?php

namespace app\admin\model\broadcast\tasks;

use think\Model;

/**
 * Line
 */
class Line extends Model
{
    // 表主键
    protected $pk = 'recording_file_id';
    
    // 表名
    protected $name = 'broadcast_tasks_line';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function setDurationAttr($value): string
    {
        return $value ? date('H:i:s', strtotime($value)) : '';
    }

    public function head(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\broadcast\Tasks::class, 'head_id', 'head_id');
    }
}