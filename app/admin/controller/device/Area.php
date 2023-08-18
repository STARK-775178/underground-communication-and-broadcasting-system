<?php

namespace app\admin\controller\device;

use app\common\controller\Backend;

/**
 * 广播区域
 */
class Area extends Backend
{
    /**
     * Area模型对象
     * @var object
     * @phpstan-var \app\admin\model\device\Area
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\device\Area;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}