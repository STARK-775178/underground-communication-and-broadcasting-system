
<template>
<div>
    <button @click="addBroadcast(broadcast)">测试添加Record记录</button>
</div>
</template>

<script>
import { addBroadcastRecord } from '/@/api/backend/broadcast/broadcastRecord'
import moment from 'moment'
export default {
    data() {
        return {
            broadcast: {
                broad_duration: 156165,
                broadcast_datetime: this.getDateTime(),
                broadcast_type: '区域广播',
                caller: 'admin',
                broad_areas: ['11','12'],
            },
        }
    },
    methods: {
        // 获取当前时间
        getDateTime() {
            return moment().format('YYYY-MM-DD HH:mm:ss')
        },
        addBroadcast(broadcast) {
            addBroadcastRecord(broadcast)
                .then((response) => {
                    // 处理响应数据
                    console.error(response)
                })
                .catch((error) => {
                    // 处理错误
                    console.error(error)
                })
        },
    },
}
</script>

<style></style>
