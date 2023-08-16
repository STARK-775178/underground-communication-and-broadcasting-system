<template>
    <div class="common-layout">
        <el-container>
            <!-- header -->
            <el-header>
                <el-tabs type="card" @tab-click="handleClick">
                    <el-tab-pane label="所有区域"></el-tab-pane>
                    <el-tab-pane label="公共区域1"></el-tab-pane>
                    <el-tab-pane label="公共区域2"></el-tab-pane>
                    <el-tab-pane label="矿区库房"></el-tab-pane>
                    <el-tab-pane label="矿下作业区"></el-tab-pane>
                </el-tabs>
            </el-header>
            <div style="display: flex; justify-content: space-between">
                <div style="margin-left: 50px">
                    <el-text class="mx-1" size="large">音箱数量:</el-text>
                    <el-text style="margin-left: 3px" size="large" class="mx-1">
                        {{ deviceNumber }}
                    </el-text>
                </div>
                <!-- 在线设备、离线设备 -->
                <div style="margin-right: 80px">
                    <!-- <el-text class="mx-1" size="large">在线</el-text> -->
                    <el-tag type="success" class="mx-1" effect="dark" round> 在线 </el-tag>
                    <el-text size="large" class="mx-1">{{ onlineNumber }}</el-text>
                    <!-- <el-text style="margin-left: 10px" class="mx-1" size="large">离线</el-text> -->
                    <el-tag style="margin-left: 10px" type="warning" class="mx-1" effect="dark" round> 离线 </el-tag>
                    <el-text size="large" class="mx-1">{{ offlineNumber }}</el-text>
                </div>
            </div>

            <!-- main -->
            <el-main>
                <div class="card-grid-container">
                    <el-card class="box-card" v-for="card in cards" :key="card.id" body-style="padding: 0;">
                        <template #header>
                            <div>
                                <el-tag size="small" style="margin-left: 120px; margin-top: 0" :type="card.tagType" class="mx-1" effect="dark" round>
                                    {{ card.status }}
                                </el-tag>
                            </div>
                            <div class="centered-image">
                                <img style="width: 50%; height: 50%; margin-top: 0px" src="src\assets\horn.png" />
                            </div>

                            <div>
                                <span class="centered-text">{{ card.area }}</span>
                            </div>
                        </template>

                        <div class="info-container">
                            <div class="ip-address">
                                <span class="centered-text">{{ card.ip }}</span>
                            </div>

                            <div class="speakers">
                                <span class="centered-text">{{ card.speaker }}</span>
                            </div>
                        </div>

                        <div class="centered-button">
                            <el-button :disabled="card.status === '离线'" @click="openDialog(card)" size="small">独立呼叫</el-button>
                        </div>
                    </el-card>
                </div>
                <!-- 点击呼叫确认对话Dialog -->
                <el-dialog :show-close="false" v-model="dialogVisible" title="提示" width="30%">
                    <template #default>
                        <span>确认对 {{ selectedCard.area }}、{{ selectedCard.ip }}、{{ selectedCard.speaker }} 进行通话？</span>
                    </template>
                    <template #footer>
                        <div class="dialog-footer">
                            <el-button type="warning" @click="cancelCall">取消</el-button>
                            <el-button type="primary" @click="confirmCall">确认</el-button>
                        </div>
                    </template>
                </el-dialog>

                <!-- 通话过程中对话框 -->
                <el-dialog width="250px" title="    正在IP通话...." :show-close="false" v-model="callDialogVisible">
                    <div class="dialog-content">
                        <div class="centered-image">
                            <img style="width: 100%; height: 100%" src="src\assets\call.png" />
                        </div>
                        <div class="dialog-row">
                            <el-col>
                                <el-text class="mx-1" size="default">{{ formatDuration }}</el-text>
                            </el-col>
                        </div>
                        <el-divider />
                        <div class="dialog-row">
                            <el-text class="mx-1" size="small">{{ selectedCard.area }}</el-text>
                        </div>
                        <div class="dialog-row">
                            <el-text class="mx-1" size="small">{{ selectedCard.ip }}</el-text>
                        </div>

                        <div class="dialog-row">
                            <el-text class="mx-1" size="small">{{ selectedCard.speaker }}</el-text>
                        </div>
                    </div>
                    <div class="dialog-footer">
                        <el-button round type="danger" size="large" @click="endCall">结束呼叫</el-button>
                    </div>
                </el-dialog>
            </el-main>

            <!-- 分页 -->
            <!-- footer -->
            <el-footer>
                <div class="bottom-pagination">
                    <el-pagination background layout="prev, pager, next" :total="1000" />
                </div>
            </el-footer>
        </el-container>
    </div>
</template>

<script>
import { callApi } from '/@/api/backend/communication/call'

export default {
    data() {
        return {
            cards: [
                { id: 1, status: '在线', tagType: 'success', area: '公共区域1', ip: '192.168.1.17', speaker: '音箱1' },
                { id: 2, status: '离线', tagType: 'danger', area: '公共区域2', ip: '192.168.1.18', speaker: '音箱2' },
                { id: 3, status: '在线', tagType: 'success', area: '矿区库房', ip: '192.168.1.19', speaker: '音箱3' },
                { id: 4, status: '离线', tagType: 'danger', area: '矿下作业区', ip: '192.168.1.20', speaker: '音箱4' },
                { id: 5, status: '在线', tagType: 'success', area: '休息室', ip: '192.168.1.21', speaker: '音箱5' },
                { id: 6, status: '离线', tagType: 'danger', area: '走廊', ip: '192.168.1.22', speaker: '音箱6' },
                { id: 7, status: '在线', tagType: 'success', area: '矿下作业区', ip: '192.168.1.23', speaker: '音箱7' },
            ],

            deviceNumber: 20, //设备数量
            onlineNumber: 10, //在线
            offlineNumber: 10, //离线

            // 确认呼叫弹窗属性
            selectedCard: null,
            dialogVisible: false,
            // 呼叫弹窗属性F
            callTime: 1,
            callDialogVisible: false,

            // 通话时长定时器
            callStartTime: null,
            callDuration: 0,
        }
    },
    computed: {
        formatDuration() {
            const hours = Math.floor(this.callDuration / 3600)
            const minutes = Math.floor((this.callDuration % 3600) / 60)
            const seconds = this.callDuration % 60

            const formattedHours = String(hours).padStart(2, '0')
            const formattedMinutes = String(minutes).padStart(2, '0')
            const formattedSeconds = String(seconds).padStart(2, '0')

            return `${formattedHours}:${formattedMinutes}:${formattedSeconds}`
        },
    },
    methods: {
        // 呼叫方法
        call() {
            callApi()
                .then((response) => {
                    // 处理响应数据
                    console.log(response)
                })
                .catch((error) => {
                    // 处理错误
                    console.error(error)
                })
        },
        // 页头点击按钮
        handleClick(tab, event) {
            console.log(tab, event)
        },

        openDialog(card) {
            this.selectedCard = card // Store the selected card
            this.dialogVisible = true // Show the dialog
        },
        confirmCall() {
            // Handle confirmation logic
            console.log('Confirmed call for', this.selectedCard)
            this.dialogVisible = false // Hide the dialog after confirmation
            this.callDialogVisible = true //开启呼叫的弹窗

            // 开启通话计时
            this.callStartTime = Date.now()
            setInterval(() => {
                const currentTime = Date.now()
                this.callDuration = Math.floor((currentTime - this.callStartTime) / 1000)
            }, 1000)
        },
        cancelCall() {
            this.$message({
                type: 'info',
                message: '已取消呼叫',
            })
            // Handle cancellation logic
            console.log('Cancelled call')
            this.dialogVisible = false // Hide the dialog after cancellation
        },
        // 结束呼叫按钮
        endCall() {
            clearInterval(this.intervalId)
            this.callTime = 0
            this.callDialogVisible = false
        },
    },
}
</script>

<style>
.card-grid-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-gap: 20px;
    padding: 20px;
}
/* card格式 */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.text {
    font-size: 14px;
}

.box-card {
    width: 200px;
    height: 220px;
}
/* 文字居中样式 */
.centered-text {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}
/* 按钮居中 */
.centered-button {
    display: flex;
    justify-content: center;
    margin-top: 10px; /* 调整上方间距 */
}
/* 图片居中样式 */
.centered-image {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 0; /* 调整底部间距 */
}
.centered-image img {
    max-width: 100%;
    max-height: 100%;
}
/* 页头格式 */
.demo-tabs > .el-tabs__content {
    padding: 0px;
    color: #6b778c;
    font-size: 32px;
    font-weight: 600;
}

/* 分页格式 */
.bottom-pagination {
    display: flex;
    justify-content: center;
    position: absolute;
    bottom: 20px;
    left: 0;
    right: 0;
}
/* 对话框按钮 */
.dialog-footer button:first-child {
    margin-right: 10px;
}

.dialog-content {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.dialog-row {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.dialog-footer {
    text-align: center;
}

.el-dialog__header {
    text-align: center;
}
</style>
