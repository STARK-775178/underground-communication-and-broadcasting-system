<?php

namespace app\admin\model\broadcast;

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
        'broadAreasTable',
    ];


    public function getBroadAreasTableAttr($value, $row): array
    {
        return [
            'area' => \app\admin\model\device\Area::whereIn('area', $row['broad_areas'])->column('area'),
        ];
    }

    public function getBroadAreasAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setBroadAreasAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }
}