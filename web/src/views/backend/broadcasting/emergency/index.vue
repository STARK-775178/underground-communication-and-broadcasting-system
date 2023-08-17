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

                        <div>
                            <el-tag size="large" style="margin-left: 75px; margin-top: 10px" :type="card.tagType" class="mx-1" effect="plain" round>
                                <div>{{ card.status }}</div>
                            </el-tag>
                        </div>
                    </el-card>
                </div>
                <!-- 区域广播对话框 -->
                <el-dialog
                    :close-on-click-modal="false"
                    :close-on-press-escape="false"
                    width="800px"
                    title="请选择紧急广播区域"
                    v-model="selectAreaDialogVisible"
                >
                    <div class="scrollable-collor">
                        <el-scrollbar class="scrollable-container" style="width: 80%">
                            <div v-for="(area, index) in broadcastArea" :key="index" class="area-info">
                                <div class="inline-info">
                                    <el-checkbox v-model="selectedAreasId" :label="area.id">{{ area.name }}</el-checkbox>
                                    <p>
                                        音箱数量：<span class="info-number">{{ area.speakers }}</span>
                                    </p>
                                    <p>
                                        在线数量：<span class="info-number">{{ area.online }}</span>
                                    </p>
                                    <p>
                                        离线数量：<span class="info-number">{{ area.offline }}</span>
                                    </p>
                                    <p>
                                        区域人数：<span class="info-number">{{ area.population }}</span>
                                    </p>
                                </div>
                            </div>
                        </el-scrollbar>
                    </div>

                    <div style="margin-top: 10px; display: flex; justify-content: center" >
                        <el-button class="larger-button" round type="success" size="large" :disabled="isConfirmButtonDisabled" @click="openBroadcastDialog"
                            >开始广播</el-button
                        >
                    </div>
                </el-dialog>

                <!-- 点击呼叫全体广播 -->
                <el-dialog
                    :close-on-click-modal="false"
                    :close-on-press-escape="false"
                    width="800px"
                    :title=dialogBroadcastTitle
                    :show-close="false"
                    v-model="allBroadcastDialogVisible"
                >
                    <div class="dialog-content">
                        <div class="centered-image">
                            <img style="width: 100%; height: 100%; margin-top: 0" src="src\assets\call.png" />
                        </div>
                    </div>
                    <div style="justify-content: center; align-items: center; text-align: center" class="dialog-row">
                        <el-col>
                            <el-text class="mx-1" size="default">{{ formatDuration }}</el-text>
                        </el-col>
                    </div>
                    <div class="scrollable-collor">
                        <!-- 显示全体广播区域 -->
                        <el-scrollbar style="width: 80%" wrap-class="scrollable-container">
                            <div v-for="areaId in selectedAreasId" :key="areaId" class="area-info">
                                <div class="inline-info">
                                    <p>{{ getAreaById(areaId).name }}</p>
                                    <p>
                                        音箱数量：<span class="info-number">{{ getAreaById(areaId).speakers }}</span>
                                    </p>
                                    <p>
                                        在线数量：<span class="info-number">{{ getAreaById(areaId).online }}</span>
                                    </p>
                                    <p>
                                        离线数量：<span class="info-number">{{ getAreaById(areaId).offline }}</span>
                                    </p>
                                    <p>
                                        区域人数：<span class="info-number">{{ getAreaById(areaId).population }}</span>
                                    </p>
                                </div>
                            </div>
                        </el-scrollbar>
                    </div>

                    <div style="margin-top: 20px" class="dialog-footer">
                        <el-button round type="danger" size="large" @click="endEmergencyBroadcast">结束紧急广播</el-button>
                    </div>
                </el-dialog>
                <!-- 分割线 -->
                <!-- 分页 -->
                <div style="display: flex; justify-content: center; margin-top: 20px">
                    <el-pagination background layout="prev, pager, next" :total="1000" />
                </div>
            </el-main>
            <!-- 全体广播对话框对话框 -->

            <el-divider></el-divider>
            <!-- 分页 -->
            <!-- footer -->
            <el-footer>
                <div class="centered-buttons">
                    <el-button size="large" @click="emergencyBroadcast" type="danger" round class="button-spacing">全体紧急广播</el-button>
                    <el-button size="large" @click="selectBroadcast" type="primary" round class="button-spacing">区域广播选择</el-button>
                </div>
            </el-footer>
        </el-container>
    </div>
</template>

<script>
export default {
    data() {
        return {
            // 区域广播属性
            selectAreaDialogVisible: false,
            selectedAreas: [],
            selectedAreasId: [],

            broadcastArea: [
                {
                    id: 1,
                    name: '公共区域1',
                    speakers: 4,
                    online: 4,
                    offline: 0,
                    population: 10,
                },
                {
                    id: 2,
                    name: '公共区域2',
                    speakers: 6,
                    online: 4,
                    offline: 2,
                    population: 20,
                },
                {
                    id: 3,
                    name: '矿区库房',
                    speakers: 2,
                    online: 2,
                    offline: 0,
                    population: 6,
                },
                {
                    id: 4,
                    name: '矿下作业区',
                    speakers: 3,
                    online: 2,
                    offline: 1,
                    population: 30,
                },
            ],

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

            // 通话时长定时器
            callStartTime: null,
            callDuration: 0,

            // 全体广播对话框
            allBroadcastDialogVisible: false,
            dialogBroadcastTitle:''
        }
    },
    computed: {
        // 区域广播是否被选中
        isConfirmButtonDisabled() {
            // 如果没有选中的区域，确认按钮将被禁用
            return this.selectedAreasId.length === 0
        },

        // 计算广播时间
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
        getAreaById(id) {
            return this.broadcastArea.find((area) => area.id === id)
        },
        // 区域广播确定按钮
        openBroadcastDialog() {
            this.dialogBroadcastTitle = "正在进行区域广播"

            this.selectAreaDialogVisible = false
            this.allBroadcastDialogVisible = true
            // 开启广播时间计时
            this.callStartTime = Date.now()
            setInterval(() => {
                const currentTime = Date.now()
                this.callDuration = Math.floor((currentTime - this.callStartTime) / 1000)
            }, 1000)
        },
        // 全体广播按钮
        emergencyBroadcast() {
            this.dialogBroadcastTitle = "正在进行全体紧急广播"
            this.selectedAreasId = this.broadcastArea.map((area) => area.id)
            this.allBroadcastDialogVisible = true
            // 开启广播时间计时
            this.callStartTime = Date.now()
            setInterval(() => {
                const currentTime = Date.now()
                this.callDuration = Math.floor((currentTime - this.callStartTime) / 1000)
            }, 1000)
            //TODO 全体广播逻辑
        },

        selectBroadcast() {
            this.selectedAreasId = []
            this.selectAreaDialogVisible = true
            //TODO 区域广播逻辑逻辑
        },
        //结束紧急广播
        endEmergencyBroadcast() {
            this.allBroadcastDialogVisible = false
            //TODO 全体全体广播逻辑
            clearInterval()
        },

        // 页头点击按钮
        handleClick(tab, event) {
            console.log(tab, event)
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
    /* position: absolute; */
    /* bottom: 20px; */
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

/* 底部两个按钮 */
.centered-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
}

.button-spacing {
    margin: 0 200px;
    font-size: 18px;
}

.scrollable-container {
    width: 100%;

    width: 100%;
    max-height: 250px; /* Adjust the height as needed */
}

.area-info {
    margin: 10px 0;
}

.inline-info {
    display: flex;
    justify-content: space-between;
}

.inline-info p {
    margin: 0;
}

.info-number {
    color: rgb(13, 172, 215); /* Adjust the color as needed */
    font-weight: bold;
}

.scrollable-collor {
    overflow-y: auto; /* Add vertical scrollbar if content overflows */
    background-color: #f5f5f5; /* Adjust the background color as needed */
    border-radius: 20px; /* Adjust the border radius as needed */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.larger-button {
    font-size: 18px; /* 调整按钮文本大小 */
    padding: 30px 50px; /* 调整按钮内边距 */
}
</style>
