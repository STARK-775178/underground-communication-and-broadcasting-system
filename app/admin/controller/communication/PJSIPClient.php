<?php
/**
 * Created by PhpStorm.
 * User: 冯钰卓
 * Date: 2023/8/21
 * Time: 17:55
 */

namespace app\admin\controller\communication;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\HangupAction;
use PAMI\Message\Action\CoreShowChannelsAction;

use app\common\controller\Backend;
class PJSIPClient extends Backend
{
    private $client;

    public function __construct()
    {
        $options = array(
            'host' => '192.168.1.13',
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => 'admin',
            'secret' => 'MeYFBp4ccXtT',
            'connect_timeout' => 20000,
            'read_timeout' => 20000
        );

        $this->client = new ClientImpl($options);

        try {
            $this->client->open();
        } catch (Exception $e) {
            // 处理连接错误
        }

        // 注册事件监听器
        $this->client->registerEventListener(function ($event) {
            if ($event instanceof \PAMI\Message\Event\ConnectEvent) {
                echo "Connected to Asterisk server\n";
            }
        });

        $this->client->process();
    }

    public function callExtension($extension)
    {

        try {
            $action = new OriginateAction('PJSIP/2001');
            $action->setContext('from-internal');
            $action->setPriority('1');
            $action->setExtension($extension);
            $action->setCallerId('2001');
            $action->setTimeout(20000); //
            $response = $this->client->send($action);
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


        } catch (Exception $e) {
            // Handle other exceptions that may occur during the call initiation
            $this->error('', [
                'success' => false,
                'message' => '请求失败：' . $e->getMessage(),
                'data' => null
            ]);
        }


    }

    public function hangupChannel($channelId)
    {
        $action = new HangupAction($channelId);
        $response = $this->client->send($action);

        if ($response->isSuccess()) {
            $this->success('挂断成功',null);
        } else {
            $this->error('挂断失败',null);
        }
    }


    public function hangupByExtension($extension)
    {

        $coreShowChannelsAction = new CoreShowChannelsAction();
        $response = $this->client->send($coreShowChannelsAction);

        if ($response->isSuccess()) {
            $channels = $response->getEvents();
//            var_dump($channels);
            $hangupPerformed = false;
            foreach ($channels as $channel) {
                $channelId = $channel->getKey('channel');
                // 在此处执行挂断操作，例如调用 hangupChannel() 方法
                // Check if the channel ID contains "2004"
                if (strpos($channelId, $extension) !== false) {
                    // Perform the hangup operation using the hangupChannel() method
                    $this->hangupChannel($channelId);
                    $hangupPerformed = true;
                }
            }
            if($hangupPerformed===false){
                $this->success('Channel已经挂断');
            }
        } else {
            $this->error('获取channel失败',null);
        }

    }
}