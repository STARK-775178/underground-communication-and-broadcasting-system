<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class BroadcastTask extends Command
{
    protected function configure()
    {
        $this->setName('broadcast:execute')->setDescription('Execute scheduled broadcasts');
    }

    protected function execute(Input $input, Output $output)
    {
        $broadcastTaskModel = new \app\admin\model\broadcast\Tasks;
        $currentTime = time();

        $tasks = $broadcastTaskModel->where('execution_time', '<=', $currentTime)
            ->where('status', 1)
            ->select();

        foreach ($tasks as $task) {
            $output->writeln("Executing broadcast task: ID {$task->id}");

            // 在这里执行广播任务的逻辑
            // 可以根据 $task 中的其他字段来获取广播区域、音频文件等信息
            // 例如: $task->broadcast_area_ids, $task->broadcast_file

            // 如果需要，在这里可以更新数据库，标记任务已执行等
            $task->status = 0;
            $task->save();
        }
    }
}