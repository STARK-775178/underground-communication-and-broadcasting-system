<?php

namespace app\admin\model\broadcast;

use think\Model;

/**
 * Tasks
 */
class Tasks extends Model
{
    // 表名
    protected $name = 'broadcast_tasks';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'broadcastArea',
    ];

    // 字段类型转换
    protected $type = [
        'execution_time' => 'timestamp:Y-m-d H:i:s',
        'updated_time'   => 'timestamp:Y-m-d H:i:s',
    ];


    public function getBroadcastAreaAttr($value, $row): array
    {
        return [
            'area' => \app\admin\model\device\Area::whereIn('id', $row['broadcast_area_ids'])->column('area'),
        ];
    }

    public function getBroadcastAreaIdsAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setBroadcastAreaIdsAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }
}