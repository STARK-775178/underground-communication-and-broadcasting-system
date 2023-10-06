<?php

namespace app\admin\controller\device;


use Throwable;
use app\common\controller\Backend;
use app\admin\utils\GraphQLRequest;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\ExtensionStateAction;
/**
 * 设备管理
 */
class Device extends Backend
{
protected array $noNeedLogin = ['add', 'index','del','test'];
    // 无需登录的方法列表

    /**
     * Device模型对象
     * @var object
     * @phpstan-var \app\admin\model\device\Device
     */
protected object $model;

protected object $pagingGroupsModel;

protected object $pagingConfigModel;

protected object $AreaModel;

protected string|array $defaultSortField = 'status,desc';

protected array|string $preExcludeFields = ['id', 'status'];

protected array $withJoinTable = ['workAreaTable'];

protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\device\Device;
        $this->pagingConfigModel = new \app\admin\model\broadcast\PagingConfig;
        $this->pagingGroupsModel = new \app\admin\model\broadcast\PagingGroups;
        $this->areaModel = new \app\admin\model\device\Area;
    }



    /*
     *   删除设备设备与freepbx交互测试
     */
    public function test(): void{
        $testDeviceId = 23;
//        根据设备id查询设备号码
        $device = $this->model->where('id',$testDeviceId)->find();

//        查询到所有与该设备关联的广播区域信息
        $pageGroups = $this->pagingGroupsModel
            ->where('ext', $device->user_name)->select();

        //若$pageGroupCount == 1时，删除该$pageGroup时添加一条（$pageGroup->page_number,''）信息
        foreach ($pageGroups as $pageGroup){
//            获取该广播号码的设备数量
            $pageGroupCount = $this->pagingGroupsModel
                ->where('page_number', $pageGroup->page_number)
                ->count();
            echo $pageGroupCount;
            if($pageGroupCount == 1){
//                $this->pagingGroupsModel->create(['page_number'=>$pageGroup->page_number]);
            }
            $pageGroup->where('ext', $device->user_name)->delete();
        }

    }



    /**
     * 添加
     * @author 冯钰卓
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
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }

                $username = $data['user_name'];

                //通过area查询areaid
                $area = $this->areaModel->where('area', $data["work_area"])->find();
                $deviceName = $data['device_name'];

                //添加设备时与Freepbx 区域广播交互
                $testAreaId = $area->id;

                $pageGroupList = $this->pagingConfigModel->where('description', 'LIKE', '%'.$testAreaId.'%')
                    ->field('page_group')->select();
                foreach ($pageGroupList as $pageGroup) {

                    //            判断该page_groups中该号码是否存在分机
                    $exists = $this->pagingGroupsModel
                        ->where('page_number', $pageGroup->page_group)
                        ->where('ext', '')
                        ->find();
                    if($exists){
                        //                进行替换
                        $this->pagingGroupsModel->update(['page_number'=>$pageGroup->page_group,'ext'=>$username]);
                    }else{
                        $this->pagingGroupsModel->create(['page_number'=>$pageGroup->page_group,'ext'=>$username]);
                    }

                }



                //        与freepbx设备交互
                $query = 'mutation {
    addExtension(
        input: {
            extensionId: ' . $username . '
            name: "' . $deviceName . '"
            tech: "pjsip"
            channelName: "' . $username . '"
            outboundCid: "' . $username . '"
            email: "' . $username . '@gmailcom"
            umGroups: "1"
            umEnable: true
            umPassword: "' . $username . '"
            vmPassword: "' . $username . '"
            vmEnable: true
            callerID: "' . $username . '"
            emergencyCid: "' . $username . '"
            maxContacts: "3"
        }
    ) {
        status
        message
    }
}';


                $gqlRequest = new GraphQLRequest();

                $gqlResult = $gqlRequest->sendQuery($query);


                $queryPwd = 'query{
  fetchExtension(extensionId: "' . $username . '") {
    status
    message
    id
    extensionId
    user {
      extPassword
    }

    }
  }';
                $gqlPwd = $gqlRequest->sendQuery($queryPwd);


                $password = $gqlPwd['data']['fetchExtension']['user']['extPassword'];
                $data['password'] = $password;

                //                freepbx asterisk数据库
                $this->pagingGroupsModel->page_number = '3000';
                $this->pagingGroupsModel->ext =$username;
                $this->pagingGroupsModel->save();

                //                数据库部分
                $result = $this->model->save($data);
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
     * 查看
     * @author 冯钰卓
     * @throws Throwable
     */
    public function index(): void
    {
        // 如果是 select 则转发到 select 方法，若未重写该方法，其实还是继续执行 index
        if ($this->request->param('select')) {
            $this->select();
        }

        /**
         * 1. withJoin 不可使用 alias 方法设置表别名，别名将自动使用关联模型名称（小写下划线命名规则）
         * 2. 以下的别名设置了主表别名，同时便于拼接查询参数等
         * 3. paginate 数据集可使用链式操作 each(function($item, $key) {}) 遍历处理
         */
        $where =
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where('area', '<>', '广播室')
            ->order($order)
            ->paginate($limit);
        $res->visible(['workAreaTable' => ['string']]);

        // 将JSON转换为关联数组
        $dataArr = $res->items();
        // 创建 PAMI 客户端实例
        $clientOptions = array(
            'host' => '192.168.203.8',
            'port' => '5038',
            'username' => 'admin',
            'secret' => 'MeYFBp4ccXtT',
//            'username' => 'fyz',
//            'secret' => '000418',
            'connect_timeout' => 10000,
            'read_timeout' => 10000
        );
        $client = new ClientImpl($clientOptions);

        // 连接到 Asterisk 服务器
        try {
            $client->open();
        } catch (\Exception $e) {

            $this->error('连接到Asterisk服务器失败'.$e->getMessage(),null,500);
            return;
        }

        foreach ($dataArr as &$item) {
            $extension = $item['phone'];

            // 创建 ExtensionStateAction 并设置要查询的分机号
            $action = new ExtensionStateAction($extension,'from-internal');

            try {
                // 发送 Action 请求到 Asterisk 服务器
                $response = $client->send($action);
                //                var_dump($response);
                if ($response->isSuccess()) {
                    // 获取状态信息
                    $status = $response->getKeys()['status'];

                    // 假设状态为 0 表示离线，其他值表示在线
                    $item['status'] = ($status == '0') ? 1 : 0;
                } else {
                    $this->error('查询分机状态失败',null,500);

                }
            } catch (\Exception $e) {
                $this->error('查询分级状态出错',null,500);

            }
        }

        // 关闭 PAMI 客户端连接
        $client->close();


        // 更新后的带有修改后状态的数据
        $updatedData = $dataArr;

        // 从数组中提取status列作为排序依据
        $statusArray = array_column($updatedData, 'status');

        // 使用array_multisort()函数排序
        array_multisort($statusArray, SORT_DESC, $updatedData);

        $this->success('设备清单查询成功', [
            'list'   => $updatedData,
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 删除
     * @author 冯钰卓
     * @param array $ids
     * @throws Throwable
     */
    public function del(array $ids = []): void
    {

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $filteredData = [];
        $dataArr = $res->items();
        foreach ($dataArr as $item) {
            if (in_array($item['id'], $ids)) {
                $filteredData[] = $item;
            }
        }


        foreach ($filteredData as &$item) {
            $extensionID = $item['user_name'];

//            与page_group表进行交互，完成区域交互

            //        查询到所有与该设备关联的广播区域信息
            $pageGroups = $this->pagingGroupsModel
                ->where('ext', $extensionID)->select();


            foreach ($pageGroups as $pageGroup){
                $pageGroupCount = $this->pagingGroupsModel
                    ->where('page_number', $pageGroup->page_number)
                    ->count();
                //若$pageGroupCount == 1时，删除该$pageGroup时添加一条（$pageGroup->page_number,''）信息
                if($pageGroupCount == 1){
                    $this->pagingGroupsModel->create(['page_number'=>$pageGroup->page_number]);
                }
            }

            foreach ($pageGroups as $pageGroup){
                //            获取该广播号码的设备数量
//                删除pageGroup信息
                $pageGroup->where('ext', $extensionID)->delete();
            }


//            与Freepbx删除分机api交互
                    $queryDelExt = 'mutation {
            deleteExtension(
                input: { extensionId: '.$extensionID.' }
            ) {
                status
                message
            }
        }';
            $gqlRequest = new GraphQLRequest();
            $gqlResult = $gqlRequest->sendQuery($queryDelExt);
        }


        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

                //        与freepbx交互
                $query = 'mutation {
                deleteExtension(
                    input: { extensionId:  }
            ) {
                    status
                message
            }
        }';


        $gqlRequest = new GraphQLRequest();

        $gqlResult = $gqlRequest->sendQuery($query);




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