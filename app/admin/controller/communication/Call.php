<?php

namespace app\admin\controller\communication;




use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\HangupAction;
use app\common\controller\Backend;
use PAMI\Message\Action\CoreShowChannelsAction;
use app\admin\utils\GraphQLRequest;

class Call extends Backend
{


    // 无需登录的方法列表
    protected array $noNeedLogin = ['call','hangup','test'];

    public function test(){

        $query = 'query{

    fetchAllExtensions {
        status
        message
        totalCount
        extension {
            id
            extensionId
            user {
              name
              password
              outboundCid
              ringtimer
              noanswer
              sipname
              password
              extPassword
            }
              coreDevice {
              deviceId
              dial
              devicetype
              description
              emergencyCid
            }
        }
    }

}';
    $gqlRequest = new GraphQLRequest();

    $result = $gqlRequest->sendQuery($query);

    var_dump($result);
    }

    /*
     * 发起呼叫请求
     */
    public function call($extension)
    {
//        $client = new PJSIPClient();
//        $client->callExtension($extension);


        $options = [
            'host' => '192.168.1.13',
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
            $action->setExtension($extension);
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


    public function hangup($extension){
//        $client = new PJSIPClient();
//        $client->hangupByExtension($extension);
        $options = [
            'host' => '192.168.1.13',
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

        $coreShowChannelsAction = new CoreShowChannelsAction();
        $response = $pamiClient->send($coreShowChannelsAction);

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
//                    $this->hangupChannel($channelId);
                    $hangupAction = new HangupAction($channelId);
                    $hangupResponse = $pamiClient->send($hangupAction);
                    if ($hangupResponse->isSuccess()) {
                        $hangupPerformed = true;
                        $this->success('挂断成功',null);

                    } else {
                        $hangupPerformed = true;
                        $this->error('挂断失败',null);
                    }


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
