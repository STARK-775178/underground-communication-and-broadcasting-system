<template>
    <div v-loading="!deviceIsLoaded && !areaIsLoaded">
        <div class="common-layout">
            <el-container>
                <!-- header -->
                <el-header>
                    <el-tabs v-model="selectedArea" type="card" @tab-click="handleClick">
                        <el-tab-pane name="所有区域" :label="'所有区域'"></el-tab-pane>
                        <el-tab-pane v-for="item in areaList" :key="item.id" :name="item.area" :label="item.area"></el-tab-pane>
                    </el-tabs>
                </el-header>
                <div style="display: flex; justify-content: space-between">
                    <div style="margin-left: 50px">
                        <el-text class="mx-1" size="large">音箱数量:</el-text>
                        <el-text style="margin-left: 3px" size="large" class="mx-1">
                            {{ filteredDeviceList.length }}
                        </el-text>
                    </div>
                    <!-- 在线设备、离线设备 -->
                    <div style="margin-right: 80px">
                        <!-- <el-text class="mx-1" size="large">在线</el-text> -->
                        <el-tag type="success" class="mx-1" effect="dark" round> 在线 </el-tag>
                        <el-text size="large" class="mx-1">{{ onlineDeviceCount }}</el-text>
                        <!-- <el-text style="margin-left: 10px" class="mx-1" size="large">离线</el-text> -->
                        <el-tag style="margin-left: 10px" type="warning" class="mx-1" effect="dark" round> 离线 </el-tag>
                        <el-text size="large" class="mx-1">{{ offlineDeviceCount }}</el-text>
                    </div>
                </div>

                <!-- main -->
                <el-main>
                    <div class="card-grid-container">
                        <el-card class="box-card" v-for="card in getCurrentPageDevices" :key="card.id" body-style="padding: 0;">
                            <template #header>
                                <div>
                                    <el-tag
                                        size="small"
                                        style="margin-left: 120px; margin-top: 0"
                                        :type="getTagType(card.status)"
                                        class="mx-1"
                                        effect="dark"
                                        round
                                    >
                                        {{ getStatusText(card.status) }}
                                    </el-tag>
                                </div>
                                <div class="centered-image">
                                    <img style="width: 50%; height: 50%; margin-top: 0px" src="src\assets\horn.png" />
                                </div>

                                <div>
                                    <span class="centered-text">{{ card.work_area }}</span>
                                </div>
                            </template>

                            <div class="info-container">
                                <div class="ip-address">
                                    <span class="centered-text">{{ card.adress_ip }}</span>
                                </div>

                                <div class="speakers">
                                    <span class="centered-text">{{ card.device_name }}</span>
                                </div>
                            </div>

                            <div class="centered-button">
                                <el-button :disabled="card.status === 0" @click="openDialog(card)" size="small">独立呼叫</el-button>
                            </div>
                        </el-card>
                    </div>
                    <!-- 点击呼叫确认对话Dialog -->
                    <el-dialog
                        :close-on-click-modal="false"
                        :close-on-press-escape="false"
                        :show-close="false"
                        v-model="dialogVisible"
                        title="提示"
                        width="30%"
                    >
                        <template #default>
                            <span>确认对 {{ selectedCard.work_area }}、{{ selectedCard.adress_ip }}、{{ selectedCard.device_name }} 进行通话？</span>
                        </template>
                        <template #footer>
                            <div class="dialog-footer">
                                <el-button type="warning" @click="cancelCall">取消</el-button>
                                <el-button type="primary" @click="confirmCall">确认</el-button>
                            </div>
                        </template>
                    </el-dialog>

                    <!-- 通话过程中对话框 -->
                    <el-dialog
                        :close-on-click-modal="false"
                        :close-on-press-escape="false"
                        width="250px"
                        title="    正在IP通话...."
                        :show-close="false"
                        v-model="callDialogVisible"
                    >
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
                                <el-text class="mx-1" size="small">{{ selectedCard.work_area }}</el-text>
                            </div>
                            <div class="dialog-row">
                                <el-text class="mx-1" size="small">{{ selectedCard.adress_ip }}</el-text>
                            </div>

                            <div class="dialog-row">
                                <el-text class="mx-1" size="small">{{ selectedCard.device_name }}</el-text>
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
                        <el-pagination
                            layout="prev, pager, next"
                            :total="filteredDeviceList.length"
                            :page-size="8"
                            @current-change="handlePageChange"
                            background
                        />
                    </div>
                </el-footer>
            </el-container>
        </div>
    </div>
</template>

<script>
import { callApi, hangupApi } from '/@/api/backend/communication/call'
import { deviceListApi, areaListApi } from '/@/api/backend/device/device'
export default {
    data() {
        return {
            clickCount: 0,
            deviceIsLoaded: false, // 默认显示加载状态
            areaIsloaded: false,
            currentPage: 1,
            deviceList: [],
            areaList: [],
            selectedArea: '所有区域',
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
        // 根据区域筛选数据
        filteredDeviceList() {
            if (this.selectedArea === '所有区域') {
                return this.deviceList
            } else {
                return this.deviceList.filter((device) => device.work_area === this.selectedArea)
            }
        },
        // 制作分页
        getCurrentPageDevices() {
            const startIndex = (this.currentPage - 1) * 8
            const endIndex = startIndex + 8
            return this.filteredDeviceList.slice(startIndex, endIndex)
        },

        // 计算设备在线和离线的书香
        onlineDeviceCount() {
            return this.filteredDeviceList.filter((device) => device.status === 1).length
        },
        offlineDeviceCount() {
            return this.filteredDeviceList.filter((device) => device.status === 0).length
        },

        // 计算通话时间
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

    mounted() {
        // 获取设备清单
        this.getDeviceList()
        // 获取广播区域清单
        this.getAreaList()
    },
    methods: {
        handleTabDblClick(event) {
            // 阻止默认双击事件行为
            event.preventDefault()

            // 执行您的自定义操作，不取消 name
            // 这里可以根据您的需求进行相应的处理
            console.log('双击事件发生，不取消 name')
        },
        handleClick(tab) {
            this.clickCount++

            // 如果点击计数达到两次
            if (this.clickCount >= 2) {
                // 执行您的自定义操作，不取消 name
                // 这里可以根据您的需求进行相应的处理
                console.log('点击两次事件发生，不取消 name')

                return
            }
            this.selectedArea = tab.label
            this.currentPage = 1 // 选择新区域后重置页数
        },
        handlePageChange(newPage) {
            this.currentPage = newPage
        },
        getStatusText(status) {
            return status === 0 ? '离线' : status === 1 ? '在线' : '未知状态'
        },
        getTagType(status) {
            return status === 0 ? 'danger' : 'success'
        },
        // 获取设备清单
        getDeviceList() {
            deviceListApi()
                .then((response) => {
                    // 处理响应数据
                    console.log(response.data.list)
                    this.deviceList = response.data.list
                    this.deviceIsLoaded = true
                })
                .catch((error) => {
                    // 处理错误
                    console.error(error)
                    this.deviceIsLoaded = true
                })
        },
        // 获取设备清单
        getAreaList() {
            areaListApi()
                .then((response) => {
                    // 处理响应数据
                    console.log(response.data.list)
                    this.areaList = response.data.list
                    this.areaIsloaded = true
                })
                .catch((error) => {
                    // 处理错误
                    console.error(error)
                    this.areaIsloaded = true
                })
        },
        // 结束通话方法
        hangup(extension) {
            hangupApi(extension)
                .then((response) => {
                    // 处理响应数据
                    console.log(response)
                    this.$message({
                        type: 'info',
                        message: response.data.message,
                    })
                })
                .catch((error) => {
                    // 处理错误
                    console.error(error)
                })
        },
        // 呼叫方法
        call(extension) {
            callApi(extension)
                .then((response) => {
                    // 处理响应数据
                    console.log(response)
                })
                .catch((error) => {
                    // 处理错误
                    console.error(error)
                })
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
            this.call(this.selectedCard.phone)
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
            this.hangup(this.selectedCard.phone)
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
