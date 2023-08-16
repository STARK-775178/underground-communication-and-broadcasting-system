<?php

namespace app\admin\controller\communication;

use app\common\controller\Backend;

/**
 * 通讯记录
 */
class CallRecord extends Backend
{
    /**
     * CallRecord模型对象
     * @var object
     * @phpstan-var \app\admin\model\communication\CallRecord
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['calldate', 'id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\communication\CallRecord;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}