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

    protected object $cbroadcastTasksLineModel;

    protected object $crecordingFile;

    protected object $cvoiceFile;

    protected string|array $defaultSortField = 'head_id,desc';

    protected array|string $preExcludeFields = ['create_time'];

    protected string|array $quickSearchField = ['head_id'];


    // 无需登录的方法列表
    protected array $noNeedLogin = ['indexBroadcastTasks','add'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\broadcast\Tasks;
        $this->cbroadcastTasksLineModel = new \app\admin\model\broadcast\tasks\Line;
        $this->crecordingFile = new \app\admin\model\broadcast\propaganda\Recording;
        $this->cvoiceFile = new \app\admin\model\broadcast\Propaganda;
    }

    /**
     *
     * 接口名称：indexBroadcastTasks
     * 功能:从cbroadcast_tasks_head表中查task_status=NEW的数据
     * select * from cbroadcast_tasks_head cth where cth.task_status='NEW';
     * @return void
     * @throws \Throwable
     * @throws \think\db\exception\DbException
     */
    public function indexBroadcastTasks(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->where('task_status','NEW')
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 功能：根据传入参数head_id查出表cbroadcast_tasks_line表中的音频信息
     * select * from cbroadcast_tasks_line ctl where ctl.head_id = head_id;
     * @return void
     */
    public function indexBroadcastTasksLineByHeadId(): void
    {
        $head_id = $this->request->param('head_id');
        $res = $this->cbroadcastTasksLineModel
            ->where('head_id',$head_id)
            ->select();
        $this->success('', [
            'list'   => $res,
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */



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
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }



    /**
     * 添加
     * 1. 新增
     * 1.1 创建cbroadcast_tasks_head表信息，获取到head_id.
     * 1.2 根据接口传入的broadcast_file，从crecording_file表和cvoice_file表中查找对应的音频信息，将1.1中的head_id信息放入。并存入cbroadcast_tasks_line表中。
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
            $this->cbroadcastTasksLineModel->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);
                $head_id = $this->model->head_id;

                //获取请求参数中的broadcast_file
                $broadcast_file = $data['broadcast_file'];

                //查询crecording_file表的数据 匹配字段为recording_file_name
                $recording_file = $this->crecordingFile->where('recording_file_name',$broadcast_file)->find();
                //查询cvoice_file表的数据 匹配字段为voice_file_name
                $voice_file = $this->cvoiceFile->where('voice_file_name',$broadcast_file)->find();
                //验证两个表中是否都有数据，如果都没有数据，则返回错误信息
                if($recording_file === null && $voice_file === null){
                    $this->error(__('没有该音频文件'));
                }
                //如果第一个表中有数据，则将数据存入cbroadcast_tasks_line表中，存入的字段为recording_file_name、recording_file_url、duration、remark
                if($recording_file !== null){

                    $recording_file_name = $recording_file->recording_file_name;
                    $recording_file_url = $recording_file->recording_file_url;
                    $duration = $recording_file->duration;
                    $remark = $recording_file->remark;
                    $this->cbroadcastTasksLineModel->save([
                        'head_id' => $head_id,
                        'recording_file_name' => $recording_file_name,
                        'recording_file_url' => $recording_file_url,
                        'duration' => $duration,
                        'remark' => $remark,
                        //create_time字段自动写入时间戳
                        'create_time' => time(),
                    ]);
                }
                //如果第二个表中有数据，则将数据存入cbroadcast_tasks_line表中，存入的字段为voice_file_name、voice_file_url、duration、remark
                if($voice_file !== null){

                    $voice_file_name = $voice_file->voice_file_name;
                    $voice_file_url = $voice_file->voice_file_url;
                    $duration = $voice_file->duration;
                    $remark = $voice_file->remark;
                    $this->cbroadcastTasksLineModel->save([
                        'head_id' => $head_id,
                        'recording_file_name' => $voice_file_name,
                        'recording_file_url' => $voice_file_url,
                        'duration' => $duration,
                        'remark' => $remark,
                        //create_time字段自动写入时间戳
                        'create_time' => time(),
                    ]);
                }
                $this->cbroadcastTasksLineModel->commit();
                $this->model->commit();

            } catch (Throwable $e) {
                $this->model->rollback();
                $this->cbroadcastTasksLineModel->rollback();
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
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        $id  = $this->request->param($this->model->getPk());
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $validate->check($data);
                    }
                }
                $result = $row->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 删除
     * 2. 删除
     * 2.1 先根据head_id删除cbroadcast_tasks_line表中的信息。
     * 2.2 再删除cbroadcast_tasks_head表中的信息.
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

                $head_id = $v->head_id;
                //根据head_id删除cbroadcast_tasks_line表中所有head_id为head_id的数据
                $this->cbroadcastTasksLineModel->where('head_id',$head_id)->delete();

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

}