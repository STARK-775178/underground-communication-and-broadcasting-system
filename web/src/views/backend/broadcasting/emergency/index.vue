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
                <!-- 点击呼叫确认对话Dialog -->

                <!-- 分页 -->
                <div class="bottom-pagination">
                    <el-pagination background layout="prev, pager, next" :total="1000" />
                </div>
            </el-main>

            <!-- 分割线 -->
            <el-divider></el-divider>
            <!-- 分页 -->
            <!-- footer -->
            <el-footer>
                <div class="centered-buttons">
                    <el-button size="large" type="danger" round class="button-spacing">全体紧急广播</el-button>
                    <el-button size="large" type="primary" round class="button-spacing">区域广播选择</el-button>
                </div>
            </el-footer>
        </el-container>
    </div>
</template>

<script>
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
</style>
