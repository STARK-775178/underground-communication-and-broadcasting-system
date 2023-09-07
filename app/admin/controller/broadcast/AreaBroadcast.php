<?php
/**
 * Created by PhpStorm.
 * User: 冯钰卓
 * Date: 2023/9/5
 * Time: 18:32
 */

namespace app\admin\controller\broadcast;
use app\common\controller\Backend;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;


class AreaBroadcast extends Backend
{

    // 无需登录的方法列表
    protected array $noNeedLogin = ['test','areaBroadcast'];

    protected object $pagingGroupsModel;

    protected object $pagingConfigModel;

//    protected object $callController;

    public function initialize(): void
    {
        parent::initialize();
        $this->pagingConfigModel = new \app\admin\model\broadcast\PagingConfig;
        $this->pagingGroupsModel = new \app\admin\model\broadcast\PagingGroups;
//        $this->callController = new \app\admin\controller\communication\Call;
    }

    public function test(){
//        获取区域广播ids并拼接为 id1,id2,id3的格式
        $areaIds = $this->request->post();
        $areaBroadcastDescription = '';
        sort($areaIds);
        foreach ($areaIds as $item) {
            if($areaBroadcastDescription ===''){
                $areaBroadcastDescription = $item;
            }else{
                $areaBroadcastDescription = $areaBroadcastDescription .','.$item;
            }
        }
//      获取该广播区域的pagingConfig实例
        $pagingConfig = $this->pagingConfigModel->where('description',$areaBroadcastDescription)->find();
//      调用Call方法
        $callController = new \app\admin\controller\communication\Call;
        $callController->call($pagingConfig->page_group);


    }
    public function areaBroadcast(){

//        获取区域广播ids并拼接为 id1,id2,id3的格式
            $areaIds = $this->request->post();
            $areaBroadcastDescription = '';
            sort($areaIds);
            foreach ($areaIds as $item) {
                if($areaBroadcastDescription ===''){
                    $areaBroadcastDescription = $item;
                }else{
                    $areaBroadcastDescription = $areaBroadcastDescription .','.$item;
                }
            }
//      获取该广播区域的pagingConfig实例
            $pagingConfig = $this->pagingConfigModel->where('description',$areaBroadcastDescription)->find();
//      调用Call方法报错，目前无法解决

        $options = [
            'host' => '192.168.203.8',
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => 'admin',
            'secret' => 'MeYFBp4ccXtT',
            'connect_timeout' => 20000,
            'read_timeout' => 20000
        ];
        $pamiClient = new PamiClient($options);
        // 尝试连接到Asterisk
        try {
            $pamiClient->open();
        } catch (\Exception $connectException) {
            // 处理连接失败异常
            // 例如：日志记录、通知用户等
            $this->error('', [
                'success' => false,
                'message' => '连接Asterisk失败：' . $connectException->getMessage(),
                'data' => null
            ]);
            return;
        }
        try {
            $action = new OriginateAction('PJSIP/2001');
            $action->setContext('from-internal');
            $action->setPriority('1');
            $action->setExtension($pagingConfig->page_group);
            $action->setCallerId('2001');
            $action->setTimeout(20000); //
            $response = $pamiClient->send($action);
            if ($response->isSuccess()) {
                // Send the outbound request
                $this->success('', [
                    'success' => true,
                    'message' => '请求成功',
                    'data' => $response
                ]);
            } else {
                // Handle the case when the requested number does not exist
                $this->error('', [
                    'success' => false,
                    'message' => '请求失败：号码不存在',
                    'data' => null
                ]);
            }

            $pamiClient->close();
        } catch (Exception $e) {
            // Handle other exceptions that may occur during the call initiation
            $this->error('', [
                'success' => false,
                'message' => '请求失败：' . $e->getMessage(),
                'data' => null
            ]);
        }
        $pamiClient->close();

    }



}