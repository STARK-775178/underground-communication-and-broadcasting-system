<?php

namespace app\admin\model;

use think\Model;

/**
 * BroadcastRecord
 */
class BroadcastRecord extends Model
{
    // 表名
    protected $name = 'broadcast_record';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 追加属性
    protected $append = [
        'broadcastAreasTable',
    ];


    public function getBroadcastAreasTableAttr($value, $row): array
    {
        return [
            'area' => \app\admin\model\device\Area::whereIn('id', $row['broadcast_areas'])->column('area'),
        ];
    }

    public function getBroadcastAreasAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setBroadcastAreasAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }
}