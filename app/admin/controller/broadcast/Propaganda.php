<?php

namespace app\admin\controller\broadcast;

use app\common\controller\Backend;

/**
 * 音频文件管理
 */
class Propaganda extends Backend
{
    /**
     * Propaganda模型对象
     * @var object
     * @phpstan-var \app\admin\model\broadcast\Propaganda
     */
    protected object $model;

    protected string|array $defaultSortField = 'voice_file_id,desc';

    protected array|string $preExcludeFields = ['voice_file_id', 'update_time', 'create_time'];

    protected string|array $quickSearchField = ['voice_file_id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\broadcast\Propaganda;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}