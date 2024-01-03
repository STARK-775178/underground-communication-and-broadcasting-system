<?php

namespace app\admin\model\communication;

use think\Model;

/**
 * Channels
 */
class Channels extends Model
{
    // 表名
    protected $name = 'hannels';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 字段类型转换
    protected $type = [
        'create_data' => 'timestamp:Y-m-d H:i:s',
    ];

    protected static function onBeforeInsert($model)
    {
        $pk         = $model->getPk();
        $model->$pk = \app\common\library\SnowFlake::generateParticle();
    }

    public function getIdAttr($value): string
    {
        return (string)$value;
    }
}