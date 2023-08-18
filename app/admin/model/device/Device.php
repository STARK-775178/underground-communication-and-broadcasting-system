<?php

namespace app\admin\model\device;

use think\Model;

/**
 * Device
 */
class Device extends Model
{
    // 表名
    protected $name = 'device';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;


    public function workAreaTable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\device\Area::class, 'work_area', 'area');
    }
}