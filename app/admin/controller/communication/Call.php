<?php

namespace app\admin\controller\communication;



use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;

use app\common\controller\Backend;

class Call extends Backend
{
    // 无需登录的方法列表
    protected array $noNeedLogin = ['call'];


    /*
     * 发起呼叫请求
     */
    public function call() {
        try {
            $options = [
                'host' => '192.168.1.17',
                'scheme' => 'tcp://',
                'port' => 5038,
                'username' => 'myadmin',
                'secret' => 'myadmin',
                'connect_timeout' => 2000,
                'read_timeout' => 2000
            ];

            $pamiClient = new PamiClient($options);

            // 尝试连接到Asterisk
            try {
                $pamiClient->open();
            } catch (\Exception $connectException) {
                // 处理连接失败异常
                // 例如：日志记录、通知用户等
                $this->success('',[
                    'success' => false,
                    'message' => '连接Asterisk失败：' . $connectException->getMessage(),
                    'data' => null
                ]);
                return;
            }

            $action = new OriginateAction('PJSIP/2001');
            $action->setContext('from-internal');
            $action->setPriority('1');
            $action->setExtension('2004');
            $action->setCallerId('2001');
            $action->setTimeout(2000); // 2 seconds

            // 发送外呼请求
            $response = $pamiClient->send($action);



            $this->success('',[
                'success' => true,
                'message' => '请求成功',
                'data' => $response // 这里使用实际的响应数据
            ]);

            // 关闭连接
            $pamiClient->close();

        } catch (\Exception $e) {
            // 处理其他异常
            // 例如：日志记录、通知用户等
            $this->success('',[
                'success' => false,
                'message' => '发生异常：' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}