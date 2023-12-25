<?php

namespace app\admin\controller\broadcast;

use app\common\controller\Backend;

/**
 * 定时广播管理
 */
class Tasks extends Backend
{
    /**
     * Tasks模型对象
     * @var object
     * @phpstan-var \app\admin\model\broadcast\Tasks
     */
    protected object $model;

    protected string|array $defaultSortField = 'id,asc';

    protected array|string $preExcludeFields = ['id', 'create_time', 'updated_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\broadcast\Tasks;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}