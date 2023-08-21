<?php

namespace app\admin\model\maintenance;

use think\Model;

/**
 * Maintenance
 */
class Maintenance extends Model
{
    // 表名
    protected $name = 'fault_record';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;


    public function device(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\device\Device::class, 'device_id', 'id');
    }
}