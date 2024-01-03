<?php

namespace app\admin\controller\communication;

use app\common\controller\Backend;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\CommandAction;
use PAMI\Message\Action\CoreShowChannelsAction;
use PAMI\Message\Event\CoreShowChannelEvent;
use app\admin\controller\communication\Call;
/**
 * 通话频道
 */
class Channels extends Call
{

    /**
     * Channels模型对象
     * @var object
     * @phpstan-var \app\admin\model\communication\Channels
     */
    // 无需登录的方法列表
    protected array $noNeedLogin = ['insertChannel', 'index','getFreeConferenceRoom'];

    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['callerId', 'extension', 'id'];


    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\communication\Channels;

    }

    /**
     * @return void
     * @throws \PAMI\Client\Exception\ClientException
     * 实现显示正在通话的通道
     */
//    public function index(): void
//    {
//        $clientOptions = require 'config/amiConfig.php';
//        $client = new PamiClient($clientOptions);
//        // 尝试连接到Asterisk
//        try {
//            $client->open();
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
//
//
//        try {
//            $client->open();
//            $channelArray = array(); // 创建一个空数组用于存储数据
//
//            // 使用 CoreShowChannelsAction 获取所有通话的信息
//            $coreShowChannelsAction = new CoreShowChannelsAction();
//            $response = $client->send($coreShowChannelsAction);
//
//            // 解析事件并获取通道信息
//            foreach ($response->getEvents() as $event) {
//                if ($event instanceof CoreShowChannelEvent) {
//                    $callerId = $event->getCallerIDNum();
//                    $createdDate = $event->getCreatedDate();
//
//                    $duration = $event->getDuration();
//                    $data = $event->getApplicationData();
//
//                    // 使用正则表达式匹配并提取被呼叫方信息
//                    preg_match('/PJSIP\/\d+\/sip:(.+?)@/', $data, $calleeMatch);
//                    $calleeNumber = isset($calleeMatch[1]) ? $calleeMatch[1] : 'Unknown';
//
//                    // 将信息存储到关联数组中
//                    $data = array(
//                        'callerId' => $callerId,
//                        'create_data' => $createdDate,
//                        'duration' => $duration,
//                        'extension' => $calleeNumber
//                    );
//
//                    // 如果 calleeNumber 为 "Unknown"，跳过当前循环
//                    if ($calleeNumber === 'Unknown') {
//                        continue;
//                    }
//
//                    // 将关联数组添加到数据数组中
//                    $channelArray[] = $data;
//                }
//            }
//
//            // 打印存储的数据数组
////            foreach ($channelArray as $item) {
////                echo "Caller ID: " . $item['callerId'] . ", Created Date: " . $item['createdDate'] . ", Duration: " . $item['duration'] . " seconds, Extension: " . $item['extension'] . "\n";
////            }
//            $this->success('查询成功', [
//                'list'   => $channelArray,
//                'total'  => 0,
//                'remark' => get_route_remark(),
//            ]);
//        } catch (Exception $e) {
//            echo "Error: " . $e->getMessage();
//        } finally {
//            $client->close();
//        }
//
//    }

    /**
     * @return void
     * 插播
     */
    public function insertChannel(): void
    {
//        获取插播的分机组的号码
        $extensionGroup = $this->request->post('extensions');

//        挂断所有分机组的号码
        $this->hangupAllExtensionGroup($extensionGroup);
////        获取空闲的Conference Room
        $conferenceRoom = $this->getFreeConferenceRoom();
//        echo $conferenceRoom;
//        分机组的号码拨打会议号码
        $this->callConferenceRoom($extensionGroup, $conferenceRoom);
//        第三方加入会议
        $this->joinConferenceRoom($conferenceRoom);
        $this->success('插播成功');
    }



    /**
     * ---------------------------------------------------------------------------------------------------------------------
     */
    private function hangupAllExtensionGroup(mixed $extensionGroup)
    {

        parent::pamiHangUp($extensionGroup);
    }

    private function getFreeConferenceRoom()
    {
//        $clientOptions = require 'config/amiConfig.php';
//        $pamiClient = new PamiClient($clientOptions);
//        $pamiClient->open();
//        $commandAction = new CommandAction('confbridge list');
//        $response = $pamiClient->send($commandAction);
//        echo $response->isSuccess();
//        var_dump($response);
//         尝试连接到Asterisk
        return 1000;
    }

    private function joinConferenceRoom($conferenceRoom)
    {
        parent::pamiCall('2001', $conferenceRoom);
    }

    private function callConferenceRoom(mixed $extensionGroup, $conferenceRoom)
    {
        foreach ($extensionGroup as $extension) {
            parent::pamiCall($extension, $conferenceRoom);
        }
    }
    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}