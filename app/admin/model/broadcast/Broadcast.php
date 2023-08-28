<?php

namespace app\admin\model\broadcast;

use think\Model;

/**
 * Broadcast
 */
class Broadcast extends Model
{
    // 表名
    protected $name = 'broadcast';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 追加属性
    protected $append = [
        'broadcastArea',
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