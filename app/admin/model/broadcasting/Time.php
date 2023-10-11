<?php

namespace app\admin\model\broadcasting;

use think\Model;

/**
 * Task
 */
class Time extends Model
{
    // 表主键
    protected $pk = 'task_id';
    
    // 表名
    protected $name = 'time_task';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getReminderTimeAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setReminderTimeAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }
}