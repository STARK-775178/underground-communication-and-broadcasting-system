<?php

namespace app\admin\model\broadcasting;

use think\Model;

/**
 * Propaganda
 */
class Propaganda extends Model
{
    // 表主键
    protected $pk = 'voice_file_id';

    // 表名
    protected $name = 'voice_file';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function setDurationAttr($value): string
    {
        return $value ? date('H:i:s', strtotime($value)) : '';
    }

}