<?php

namespace app\admin\controller\broadcasting\propaganda;

use app\common\controller\Backend;

/**
 * 录音文件
 */
class Recording extends Backend
{
    /**
     * File模型对象
     * @var object
     * @phpstan-var \app\admin\model\broadcasting\propaganda\Recording
     */
    protected object $model;

    protected string|array $defaultSortField = 'recording_file_id,desc';

    protected array|string $preExcludeFields = ['recording_file_id', 'update_time', 'create_time'];

    protected string|array $quickSearchField = ['recording_file_id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\broadcasting\propaganda\Recording;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}