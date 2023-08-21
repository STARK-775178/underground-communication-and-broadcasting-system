<?php

namespace app\admin\controller\device;

use function Symfony\Component\Mime\toString;
use Throwable;
use app\common\controller\Backend;

use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\ExtensionStateAction;
/**
 * 设备管理
 */
class Device extends Backend
{
    // 无需登录的方法列表
    protected array $noNeedLogin = ['index'];
    /**
     * Device模型对象
     * @var object
     * @phpstan-var \app\admin\model\device\Device
     */
    protected object $model;

    protected string|array $defaultSortField = 'status,desc';

    protected array|string $preExcludeFields = ['id', 'status'];

    protected array $withJoinTable = ['workAreaTable'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\device\Device;
    }

    /**
     * 查看
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
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);
        $res->visible(['workAreaTable' => ['string']]);

        // 将JSON转换为关联数组
        $dataArr = $res->items();
        // 创建 PAMI 客户端实例
        $clientOptions = array(
            'host' => '192.168.1.13',
            'port' => '5038',
            'username' => 'admin',
            'secret' => 'MeYFBp4ccXtT',
            'connect_timeout' => 10000,
            'read_timeout' => 10000
        );
        $client = new ClientImpl($clientOptions);

        // 连接到 Asterisk 服务器
        try {
            $client->open();
        } catch (\Exception $e) {

            $this->error('连接到Asterisk服务器失败',null,500);
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
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}