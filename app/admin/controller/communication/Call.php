<?php

namespace app\admin\controller\communication;



use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;

use app\common\controller\Backend;

class Call extends Backend
{
    // 无需登录的方法列表
    protected array $noNeedLogin = ['call','callByUrl'];


    /*
     * 发起呼叫请求
     */
    public function call($extension)
    {


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
            ], 500);
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
                ],200);
            } else {
                // Handle the case when the requested number does not exist
                $this->error('', [
                    'success' => false,
                    'message' => '请求失败：号码不存在',
                    'data' => null
                ],500);
            }

            $pamiClient->close();
        } catch (Exception $e) {
            // Handle other exceptions that may occur during the call initiation
            $this->error('', [
                'success' => false,
                'message' => '请求失败：' . $e->getMessage(),
                'data' => null
            ],500);
        }

            $pamiClient->close();


    }

}
