<?php

namespace app\admin\controller\broadcast;

use app\common\controller\Backend;

/**
 * 定时广播
 */
class Time extends Backend
{
    /**
     * Time模型对象
     * @var object
     * @phpstan-var \app\admin\model\broadcast\Time
     */
    protected object $model;

    protected string|array $defaultSortField = 'task_id,desc';

    protected array|string $preExcludeFields = ['create_time', 'update_time'];

    protected string|array $quickSearchField = ['task_id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\broadcast\Time;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}