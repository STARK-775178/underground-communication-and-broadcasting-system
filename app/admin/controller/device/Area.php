<?php

namespace app\admin\controller\device;

use app\common\controller\Backend;
use think\Db;
/**
 * 广播区域
 */
class Area extends Backend
{

protected array $noNeedLogin = ['add', 'index','del'];
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

//    /**
//     * 添加
//     */
//    public function add(): void
//    {
//
//
//
////        if ($this->request->isPost()) {
//        $data = $this->request->post();
//// 查询数据库获取所有的ID
//        $result = $this->model->column('id');
//
//// 将查询结果存储在一个数组中
//        $idArray = [];
//        foreach ($result as $id) {
//            $idArray[] = $id;
//        }
//
//
//// 打印数组内容
//        print_r($idArray);
//
////            if (!$data) {
////                $this->error(__('Parameter %s can not be empty', ['']));
////            }
////
////            $data = $this->excludeFields($data);
////            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
////                $data[$this->dataLimitField] = $this->auth->id;
////            }
////
////            $result = false;
////            $this->model->startTrans();
////            try {
////                // 模型验证
////                if ($this->modelValidate) {
////                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
////                    if (class_exists($validate)) {
////                        $validate = new $validate;
////                        if ($this->modelSceneValidate) $validate->scene('add');
////                        $validate->check($data);
////                    }
////                }
////                $result = $this->model->save($data);
////                $this->model->commit();
////            } catch (Throwable $e) {
////                $this->model->rollback();
////                $this->error($e->getMessage());
////            }
////            if ($result !== false) {
////                $this->success(__('Added successfully'));
////            } else {
////                $this->error(__('No rows were added'));
////            }
////        }
////
////        $this->error(__('Parameter error'));
//    }
    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}