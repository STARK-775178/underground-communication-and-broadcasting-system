<?php
/**
 * Created by PhpStorm.
 * User: 冯钰卓
 * Date: 2023/8/28
 * Time: 15:33
 */

namespace app\admin\model\broadcast;
use think\Model;

class PagingGroups extends Model
{
    // 表名
    protected $name = 'paging_groups';
    // 使用asterisk数据库
    protected $connection = 'asterisk';

    protected $pk = 'page_number';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;


}