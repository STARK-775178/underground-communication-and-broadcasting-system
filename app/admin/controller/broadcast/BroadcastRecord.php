<?php

namespace app\admin\controller\broadcast;

use app\common\controller\Backend;

/**
 * 广播记录
 */
class BroadcastRecord extends Backend
{
    /**
     * BroadcastRecord模型对象
     * @var object
     * @phpstan-var \app\admin\model\broadcast\BroadcastRecord
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\broadcast\BroadcastRecord;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}