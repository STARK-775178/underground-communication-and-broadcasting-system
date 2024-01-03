<?php

namespace app\admin\controller\communication;


use think\Request;

use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\HangupAction;
use PAMI\Message\Action\CoreShowChannelsAction;
use app\common\controller\Backend;
use PAMI\Message\Event\CoreShowChannelEvent;
use PAMI\Message\Action\CommandAction;

class Call extends Backend
{


    public function initialize(): void
    {
        parent::initialize();
    }

    // 无需登录的方法列表
    protected array $noNeedLogin = ['call', 'hangup', 'test','hangUpExtensions'];

    public function test()
    {
//        $options = array(
//            'host' => '192.168.203.8',
//            'scheme' => 'tcp://',
//            'port' => 5038,
//            'username' => 'admin',
//            'secret' => 'MeYFBp4ccXtT',
//            'connect_timeout' => 20000,
//            'read_timeout' => 20000
//        );
//
//        $client = new PamiClient($options);
        $clientOptions = require 'config/amiConfig.php';
        $client = new PamiClient($clientOptions);
        // 尝试连接到Asterisk
        try {
            $client->open();
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
            $client->open();
            $channelArray = array(); // 创建一个空数组用于存储数据

            // 使用 CoreShowChannelsAction 获取所有通话的信息
            $coreShowChannelsAction = new CoreShowChannelsAction();
            $response = $client->send($coreShowChannelsAction);

            // 解析事件并获取通道信息
            foreach ($response->getEvents() as $event) {
                if ($event instanceof CoreShowChannelEvent) {
                    $callerId = $event->getCallerIDNum();
                    $createdDate = $event->getCreatedDate();
                    $duration = $event->getDuration();
                    $data = $event->getApplicationData();

                    // 使用正则表达式匹配并提取被呼叫方信息
                    preg_match('/PJSIP\/\d+\/sip:(.+?)@/', $data, $calleeMatch);
                    $calleeNumber = isset($calleeMatch[1]) ? $calleeMatch[1] : 'Unknown';

                    // 将信息存储到关联数组中
                    $data = array(
                        'callerId' => $callerId,
                        'createdDate' => $createdDate,
                        'duration' => $duration,
                        'extension' => $calleeNumber
                    );

                    // 如果 calleeNumber 为 "Unknown"，跳过当前循环
                    if ($calleeNumber === 'Unknown') {
                        continue;
                    }

                    // 将关联数组添加到数据数组中
                    $channelArray[] = $data;
                }
            }

            // 打印存储的数据数组
            foreach ($channelArray as $item) {
                echo "Caller ID: " . $item['callerId'] . ", Created Date: " . $item['createdDate'] . ", Duration: " . $item['duration'] . " seconds, Extension: " . $item['extension'] . "\n";
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        } finally {
            $client->close();
        }


    }

    /*
     * 发起呼叫请求
     */
    public function call($extension)
    {
//        连接freepbx并发起呼叫

        try {
            $response = $this->pamiCall('2001',$extension);
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

    public function hangupAll()
    {
//        $options = [
//            'host' => '192.168.203.8',
//            'scheme' => 'tcp://',
//            'port' => 5038,
//            'username' => 'admin',
//            'secret' => 'MeYFBp4ccXtT',
//            'connect_timeout' => 20000,
//            'read_timeout' => 20000
//        ];
//        $pamiClient = new PamiClient($options);
        $clientOptions = require 'config/amiConfig.php';
        $pamiClient = new PamiClient($clientOptions);
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
        $coreShowChannelsAction = new CoreShowChannelsAction();
        $response = $pamiClient->send($coreShowChannelsAction);

        $channels = $response->getEvents();


        // 挂断每个通道
        foreach ($channels as $channel) {
            $channelId = $channel->getKey('channel');
            $hangupAction = new HangupAction($channelId);
            $pamiClient->send($hangupAction);
        }
        // 关闭PAMI客户端
        $this->success('挂断成功');
        $pamiClient->close();
    }

    public function hangup($extension)
    {
        if ($this->pamiHangUp([$extension])) {
            $this->success('挂断成功');
        } else {
            $this->error('挂断失败');
        }
//        $clientOptions = require 'config/amiConfig.php';
//        $pamiClient = new PamiClient($clientOptions);
//        // 尝试连接到Asterisk
//        try {
//            $pamiClient->open();
//        } catch (\Exception $connectException) {
//            // 处理连接失败异常
//            // 例如：日志记录、通知用户等
//            $this->error('', [
//                'success' => false,
//                'message' => '连接Asterisk失败：' . $connectException->getMessage(),
//                'data' => null
//            ]);
//            return;
//        }
//        $coreShowChannelsAction = new CoreShowChannelsAction();
//        $response = $pamiClient->send($coreShowChannelsAction);
//
//        if ($response->isSuccess()) {
//            $channels = $response->getEvents();
////            var_dump($channels);
//            $hangupPerformed = false;
//            foreach ($channels as $channel) {
//                $channelId = $channel->getKey('channel');
//                // 在此处执行挂断操作，例如调用 hangupChannel() 方法
//                // Check if the channel ID contains "2004"
//                if (strpos($channelId, $extension) !== false) {
//                    // Perform the hangup operation using the hangupChannel() method
////                    $this->hangupChannel($channelId);
//                    $hangupAction = new HangupAction($channelId);
//                    $hangupResponse = $pamiClient->send($hangupAction);
//                    if ($hangupResponse->isSuccess()) {
//                        $hangupPerformed = true;
//                        $this->success('挂断成功', null);
//
//                    } else {
//                        $hangupPerformed = true;
//                        $this->error('挂断失败', null);
//                    }
//
//
//                }
//            }
//            if ($hangupPerformed === false) {
//                $this->success('Channel已经挂断');
//            }
//        } else {
//            $this->error('获取channel失败', null);
//        }
    }

    /**
     * @param Request $request
     * @return void
     * @throws \PAMI\Client\Exception\ClientException
     * 挂断extensions中的所有通道
     */
    public function hangUpExtensions()
    {
//        获取post请求
        $extensions = $this->request->post('extensions');
        if ($this->pamiHangUp($extensions)) {
            $this->success('挂断成功');
        } else {
            $this->error('挂断失败');
        }
    }

    /**
     * @param $extensions
     * @return bool|void
     * @throws \PAMI\Client\Exception\ClientException
     * 挂断extensions中的所有通道
     */
    protected function pamiHangUp($extensions)
    {
        $clientOptions = require 'config/amiConfig.php';
        $pamiClient = new PamiClient($clientOptions);
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
            return false;
        }
        $coreShowChannelsAction = new CoreShowChannelsAction();
        $response = $pamiClient->send($coreShowChannelsAction);
        if ($response->isSuccess()) {
            $channels = $response->getEvents();
            $hangupPerformed = false;
            $allExtensionsHungUp = true; // 标志所有扩展是否都已挂断，默认为 true
            foreach ($channels as $channel) {
                $channelId = $channel->getKey('channel');
                foreach ($extensions as $extension) {
                    if (strpos($channelId, $extension) !== false) {
                        $hangupAction = new HangupAction($channelId);
                        $hangupResponse = $pamiClient->send($hangupAction);
                        if ($hangupResponse->isSuccess()) {
                            $hangupPerformed = true;
                        } else {
                            // 如果挂断操作失败，将标志设为 false
                            $allExtensionsHungUp = false;
                        }
                    }
                }
            }
            // 如果已执行挂断操作并且所有扩展都已挂断，则返回 true
            if ($hangupPerformed && $allExtensionsHungUp) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function pamiCall($caller,$extension)
    {
        $clientOptions = require 'config/amiConfig.php';
        $pamiClient = new PamiClient($clientOptions);

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
        $action = new OriginateAction('PJSIP/'.$caller);
        $action->setContext('from-internal');
        $action->setPriority('1');
        $action->setExtension($extension);
        $action->setCallerId($caller);
        $action->setTimeout(20000); //
        $response = $pamiClient->send($action);
        $pamiClient->close();
        return $response;
    }

}
