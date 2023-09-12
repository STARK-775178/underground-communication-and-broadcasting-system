<?php

namespace app\admin\controller\device;

use app\common\controller\Backend;
use think\Db;
/**
 * 广播区域
 */
class Area extends Backend
{

    protected array $noNeedLogin = ['add', 'index','del','test'];
    /**
     * Area模型对象
     * @var object
     * @phpstan-var \app\admin\model\device\Area
     */
    protected object $model;

    protected object $pagingGroupsModel;

    protected object $pagingConfigModel;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\device\Area;
        $this->pagingConfigModel = new \app\admin\model\broadcast\PagingConfig;
        $this->pagingGroupsModel = new \app\admin\model\broadcast\PagingGroups;
    }
    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where('id','>','1')
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /*
     *   添加增加区域与freepbx进行交互
     */
    public function test(): void{
        $testId = 12;
        $pagingConfigList = [];
        $pagingGroupsList = [];
        $descriptionList = $this->
            pagingConfigModel->
            where('page_group', '>', 3000)->
            field('description')->
            select();

//            单独一个号码添加PagingConfig
        $newPagingConfig = ['description' => $testId];
        $pagingConfigList[] = $newPagingConfig;
        foreach ($descriptionList as $description) {
//              与其他广播区域的组合
//            pagingConfigList添加数据
            $newPagingConfig = ['description' => $description['description'].",".$testId];
            $pagingConfigList[] = $newPagingConfig;
        }
//      将所有的PagingConfig添加到数据库
        $pagingConfigDataSet = $this->pagingConfigModel->saveAll($pagingConfigList);
        // 遍历数据集对象，将自增的pagingConfig的主键们存入$pagingGroupsList
        foreach ($pagingConfigDataSet as $pagingConfig) {
//            $newPagingGroups = ['page_number' => $pagingConfig->page_group,'ext'=>''];
//            $pagingGroupsList[] = $newPagingGroups;
            $this->pagingGroupsModel->create([    'page_number'  =>  $pagingConfig->page_group]);
        }

    }

    /**
     * 添加
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->auth->id;
            }

            $result = false;
            $this->model->startTrans();
            try {
//                 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }

                $result = $this->model->save($data);
//                与freepbx进行交互
                $areaId = $this->model->id;
                $pagingConfigList = [];
                $descriptionList = $this->
                pagingConfigModel->
                where('page_group', '>', 3000)->
                field('description')->
                select();

//            单独一个号码添加PagingConfig
                $newPagingConfig = ['description' => $areaId];
                $pagingConfigList[] = $newPagingConfig;
                foreach ($descriptionList as $description) {
//              与其他广播区域的组合
//            pagingConfigList添加数据
                    $newPagingConfig = ['description' => $description['description'].",".$areaId];
                    $pagingConfigList[] = $newPagingConfig;
                }
//      将所有的PagingConfig添加到数据库
                $pagingConfigDataSet = $this->pagingConfigModel->saveAll($pagingConfigList);
                // 遍历数据集对象，将自增的pagingConfig的主键们存入$pagingGroupsList
                foreach ($pagingConfigDataSet as $pagingConfig) {
//            $newPagingGroups = ['page_number' => $pagingConfig->page_group,'ext'=>''];
//            $pagingGroupsList[] = $newPagingGroups;
                    $this->pagingGroupsModel->create([    'page_number'  =>  $pagingConfig->page_group]);
                }


                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));

    }
    /**
     * author fengyuzhuo
     * 删除
     * @param array $ids
     * @throws Throwable
     */
    public function del(array $ids = []): void
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $pk      = $this->model->getPk();
        $where[] = [$pk, 'in', $ids];

        $count = 0;
        $data  = $this->model->where($where)->select();
        $this->model->startTrans();
        try {
            foreach ($data as $v) {
                $count += $v->delete();
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}