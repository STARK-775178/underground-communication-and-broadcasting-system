<?php
/**
 * Created by PhpStorm.
 * User: 冯钰卓
 * Date: 2023/8/28
 * Time: 15:34
 */

namespace app\admin\model\broadcast;
use think\Model;

class PagingConfig extends Model
{
    // 表名
    protected $name = 'paging_config';
    protected $pk = 'page_group';
    // 使用asterisk数据库
    protected $connection = 'asterisk';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;


}