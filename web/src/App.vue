<template>
    <el-config-provider :locale="lang">
        <router-view></router-view>
    </el-config-provider>
</template>
<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import iconfontInit from '/@/utils/iconfont'
import { useRoute } from 'vue-router'
import { setTitleFromRoute } from '/@/utils/common'
import { useConfig } from '/@/stores/config'
import { useTerminal } from '/@/stores/terminal'

import { indexBroadcastTasks } from '/@/api/common'
import { indexBroadcastTasksLineByHeadId } from '/@/api/common'
import { areaBroadcast } from '/@/api/backend/broadcast/areaBroadcast'
import mittBus from "/@/utils/mittBus";
import { ref } from 'vue'

// modules import mark, Please do not remove.

const config = useConfig()
const route = useRoute()
const terminal = useTerminal()

// 初始化 element 的语言包
const { getLocaleMessage } = useI18n()

// 需要进行广播的录音数据
const broadcastRecordingData = ref([]);

const lang = getLocaleMessage(config.lang.defaultLang) as any
onMounted(() => {
    iconfontInit()
    terminal.init()

    // Modules onMounted mark, Please do not remove.
    // 创建一个每分钟执行一次的定时器
    setInterval(() => {
        console.log('定时器执行中，每分钟一次开始:');
        // 获取定时广播的消息
        indexBroadcastTasks()
            .then((response) => {
                // 处理响应数据
                console.log(response)
                response.data.list.forEach((item, index) => {
                    // 如果时间相等
                    if (Math.trunc(Date.parse(item.execution_time)/1000/60 - Date.now()/1000/60) === 0) {
                        console.log('定时广播开始，开始定时广播的id为：' + item.head_id);
                        // 将回传需要广播的音频数据存储到broadcastRecordingData里面
                        indexBroadcastTasksLineByHeadId(item.head_id).then((response) => {
                            broadcastRecordingData.value = response.data.list;
                            // 发起区域广播请求
                            // areaBroadcast(item.broadcast_area_ids)
                            //     .then((response) => {
                            //         // 播放音乐
                            //         mittBus.emit('playBroadcastTasks', broadcastRecordingData.value);
                            //     })
                            //     .catch((error) => {
                            //         // 处理错误
                            //         console.error(error)
                            //     })

                            // 播放音乐
                            mittBus.emit('playBroadcastTasks', {broadcastRecordingData: broadcastRecordingData.value, duration: item.duration.get});
                        })
                    }
                    index++;
                });
            })
            .catch((error) => {
                // 处理错误
                console.error(error)
            })
    }, 60000);
})

// 监听路由变化时更新浏览器标题
watch(
    () => route.path,
    () => {
        setTitleFromRoute()
    }
)
</script>
