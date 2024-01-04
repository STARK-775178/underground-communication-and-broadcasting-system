<?php

namespace app\admin\model\broadcast\propaganda;

use think\Model;

/**
 * Recording
 */
class Recording extends Model
{
    // 表主键
    protected $pk = 'recording_file_id';

    // 表名
    protected $name = 'recording_file';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function setDurationAttr($value): string
    {
        return $value ? date('H:i:s', strtotime($value)) : '';
    }

}