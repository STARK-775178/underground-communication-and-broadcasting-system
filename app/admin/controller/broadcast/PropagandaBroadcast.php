<?php
/**
 * Created by PhpStorm.
 * User: 冯钰卓
 * Date: 2023/9/16
 * Time: 14:15
 */

namespace app\admin\controller\broadcast;

use app\common\controller\Backend;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\AgiAction;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\CoreShowChannelsAction;

class PropagandaBroadcast extends Backend
{

    // 无需登录的方法列表
protected array $noNeedLogin = ['test','propagandaBroadcast'];


    public function initialize(): void
    {
        parent::initialize();
    }

    public function test(){

//        $options = [
//            'host' => '192.168.203.8',
//            'scheme' => 'tcp://',
//            'port' => 5038,
//            'username' => 'admin',
//            'secret' => 'MeYFBp4ccXtT',
//            'connect_timeout' => 20000,
//            'read_timeout' => 20000
//        ];
//
//        $pamiClient = new ClientImpl($options);
        $clientOptions = require 'config/amiConfig.php';
        $pamiClient = new PamiClient($clientOptions);

        // 尝试连接到 Asterisk
        try {
            $pamiClient->open();
        } catch (\Exception $connectException) {
            // 处理连接失败异常
            // 例如：日志记录、通知用户等
            echo '连接 Asterisk 失败：' . $connectException->getMessage();
            return;
        }

        $sourceChannel = 'PJSIP/2001';  // 呼叫的源通道
        $targetExtension = '2003';  // 被呼叫的分机号码
        $audioFile = '/var/lib/asterisk/sounds/custom/test.wav';  // 音频文件路径

        // 创建 OriginateAction
        $originateAction = new OriginateAction($sourceChannel);
        $originateAction->setContext('from-internal');
        $originateAction->setExtension($targetExtension);
        $originateAction->setPriority(1);

        // 发起呼叫
        $response = $pamiClient->send($originateAction);

        // 检查 OriginateAction 的响应
        if ($response->isSuccess()) {
            // 等待一段时间，确保呼叫连接成功
            sleep(5);

            $extDestination = 'PJSIP/2001';

//            // 创建 CoreShowChannelsAction
//            $coreShowChannelsAction = new CoreShowChannelsAction();
//
//            // 发送 CoreShowChannelsAction
//            $response = $pamiClient->send($coreShowChannelsAction);
//
//            //        播放音频的分机
//            // 检查 CoreShowChannelsAction 的响应
//            if ($response->isSuccess()) {
//                $channels = $response->getEvents();
//
//                // 筛选包含 "2003" 的通道
//                foreach ($channels as $channel) {
//                    $channelId = $channel->getKey('channel');
//                    // 在此处执行挂断操作，例如调用 hangupChannel() 方法
//                    // Check if the channel ID contains "2004"
//                    if (strpos($channelId, '2003') !== false) {
//                        $targetExtension = $channelId;
//                    }
//                }
//
//
//            } else {
//                echo '获取通道信息失败：' . $response->getMessage();
//            }

            // 创建 AgiAction
            $agiAction = new AgiAction($extDestination, 'EXEC Playback ' . $audioFile);

            // 发送 AgiAction
            $agiResponse = $pamiClient->send($agiAction);

            // 检查 AgiAction 的响应
            if ($agiResponse->isSuccess()) {
                echo '音频播放成功';
            } else {
                echo '音频播放失败：' . $agiResponse->getMessage();
            }
        } else {
            echo '呼叫失败：' . $response->getMessage();
        }

        // 关闭 PAMI 客户端连接
        $pamiClient->close();
    }


    public function test222(){

//        $options = [
//            'host' => '192.168.203.8',
//            'scheme' => 'tcp://',
//            'port' => 5038,
//            'username' => 'admin',
//            'secret' => 'MeYFBp4ccXtT',
//            'connect_timeout' => 20000,
//            'read_timeout' => 20000
//        ];
//
//        $pamiClient = new ClientImpl($options);
        $clientOptions = require 'config/amiConfig.php';
        $pamiClient = new PamiClient($clientOptions);
        // 尝试连接到 Asterisk
        try {
            $pamiClient->open();
        } catch (\Exception $connectException) {
            // 处理连接失败异常
            // 例如：日志记录、通知用户等
            echo '连接 Asterisk 失败：' . $connectException->getMessage();
            return;
        }

//        // 创建 CoreShowChannelsAction
//        $coreShowChannelsAction = new CoreShowChannelsAction();
//
//        // 发送 CoreShowChannelsAction
//        $response = $pamiClient->send($coreShowChannelsAction);
//
//        //        播放音频的分机
//        $extDestination = '';
//        // 检查 CoreShowChannelsAction 的响应
//        if ($response->isSuccess()) {
//            $channels = $response->getEvents();
//
//            // 筛选包含 "2003" 的通道
//            foreach ($channels as $channel) {
//                $channelId = $channel->getKey('channel');
//                // 在此处执行挂断操作，例如调用 hangupChannel() 方法
//                // Check if the channel ID contains "2004"
//                if (strpos($channelId, '2003') !== false) {
//                    $extDestination = $channelId;
//                }
//            }
//
//
//        } else {
//            echo '获取通道信息失败：' . $response->getMessage();
//        }

        $audioFile = '/var/lib/asterisk/sounds/custom/test.wav';  // 音频文件路径

        // 创建 AgiAction
        $agiAction = new AgiAction($extDestination, 'EXEC Playback ' . $audioFile);

        // 发送 AgiAction
        $response = $pamiClient->send($agiAction);

        // 检查 AgiAction 的响应
        if ($response->isSuccess()) {
            echo '音频播放成功';
        } else {
            echo '音频播放失败：' . $response->getMessage();
        }

        // 关闭 PAMI 客户端连接
        $pamiClient->close();
    }


}