<!-- 示例核心代码(3/3) -->
<template>
    <el-dialog v-model="baTable.table.extend!.showInfo" width="35%">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">插播</div>
            <div style="margin-top: 50px">
                <div>正在进行插播.......</div>
                <div class="centered-image">
                    <img style="width: 20%; height: 100%" src="~assets/call.png" />
                </div>
                {{ baTable.table.extend!.infoData.duration }}
            </div>
        </template>
        <div
            v-loading="baTable.table.extend!.infoLoading"
            style="
                display: flex;

                justify-content: center;
                margin-top: 20px;
            "
        >
            <div v-if="baTable.table.extend!.infoData">
                <div>会议成员：</div>
                <el-scrollbar style="height: auto">
                    <!-- 滚动内容 -->
                    <div>
                        {{ baTable.table.extend!.infoData.callerId }}
                    </div>
                    <div>
                        {{ baTable.table.extend!.infoData.extension }}
                    </div>
                </el-scrollbar>
            </div>
        </div>
        <template #footer>
            <div style="display: flex; justify-content: center; margin-bottom: 20px">
                <el-button round type="danger" size="large" @click="endCall">结束插播</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { timeFormat } from '/@/utils/common'
import { hangupExtensionsApi } from '/@/api/backend/communication/call'
import type baTableClass from '/@/utils/baTable'

const baTable = inject('baTable') as baTableClass

const endCall = () => {
    // baTable.table.extend!.infoData = null
    // 获取要结束通话的号码
    const callerId = baTable.table.extend!.infoData.callerId
    const extension = baTable.table.extend!.infoData.extension
    const admin = 2001
    // 将上面的号码拼接为post请求里的json里的一个数组
    const data = {
        extensions: [callerId, extension, admin],
    }
    // 发送结束通话请求
    hangupExtensionsApi(data).then((response: any) => {
        console.log(response)
    })
    // 关闭弹窗
    baTable.table.extend!.showInfo = false
    // 清空弹窗数据
    baTable.table.extend!.infoData = null
}
</script>

<style scoped lang="scss">
.info-box {
    margin-top: 60px;
    div {
        width: 100%;
        text-align: center;
    }
    .mt-40 {
        margin-top: 40px;
    }
    /* 图片居中样式 */
}
</style>
