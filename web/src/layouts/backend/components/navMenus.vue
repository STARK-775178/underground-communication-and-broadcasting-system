<template>
    <div class="nav-menus" :class="configStore.layout.layoutMode">
        <div @click="dialogTableVisible = true" class="nav-menu-item pt2">
            <el-badge>
                <Icon :color="iconColor" class="nav-menu-icon" name="el-icon-Bell" size="20" />
            </el-badge>
            <el-dialog v-model="dialogTableVisible" title="定时广播">
                <el-row>
                    <el-col>
                        <el-countdown title="剩余播放时间" :value="remainingCallDuration" @finish="broadcastFinish" />
                    </el-col>
                </el-row>
                <el-divider />
                <el-row>
                    <el-table :data="broadcastRecordingData">
                        <el-table-column property="recording_file_url" label="文件地址" width="250px" />
                        <el-table-column property="recording_file_name" label="文件名称" />
                        <el-table-column property="remark" label="备注" />
                        <el-table-column property="duration" label="时长" />
                    </el-table>
                </el-row>
                <audio-player
                    ref="audioPlayerRef"
                    class="audio-box"
                    :fileid="musicId"
                    :fileurl="musicUrl"
                    :filename="musicName"
                    @previousAudio="previousAudio"
                    @nextAudio="nextAudio"
                ></audio-player>
            </el-dialog>
        </div>
        <router-link class="h100" target="_blank" :title="t('Home')" to="/">
            <div class="nav-menu-item">
                <Icon :color="configStore.getColorVal('headerBarTabColor')" class="nav-menu-icon" name="el-icon-Monitor" size="18" />
            </div>
        </router-link>
        <!-- <el-dropdown
            @visible-change="onCurrentNavMenu($event, 'lang')"
            class="h100"
            size="large"
            :hide-timeout="50"
            placement="bottom"
            trigger="click"
            :hide-on-click="true"
        >
            <div class="nav-menu-item pt2" :class="state.currentNavMenu == 'lang' ? 'hover' : ''">
                <Icon :color="configStore.getColorVal('headerBarTabColor')" class="nav-menu-icon" name="local-lang" size="18" />
            </div>
            <template #dropdown>
                <el-dropdown-menu class="dropdown-menu-box">
                    <el-dropdown-item v-for="item in configStore.lang.langArray" :key="item.name" @click="editDefaultLang(item.name)">
                        {{ item.value }}
                    </el-dropdown-item>
                </el-dropdown-menu>
            </template>
        </el-dropdown> -->
        <div @click="onFullScreen" class="nav-menu-item" :class="state.isFullScreen ? 'hover' : ''">
            <Icon
                :color="configStore.getColorVal('headerBarTabColor')"
                class="nav-menu-icon"
                v-if="state.isFullScreen"
                name="local-full-screen-cancel"
                size="18"
            />
            <Icon :color="configStore.getColorVal('headerBarTabColor')" class="nav-menu-icon" v-else name="el-icon-FullScreen" size="18" />
        </div>
        <div v-if="adminInfo.super" @click="terminal.toggle()" class="nav-menu-item pt2">
            <el-badge :is-dot="terminal.state.showDot">
                <Icon :color="configStore.getColorVal('headerBarTabColor')" class="nav-menu-icon" name="local-terminal" size="26" />
            </el-badge>
        </div>
        <el-dropdown
            v-if="adminInfo.super"
            @visible-change="onCurrentNavMenu($event, 'clear')"
            class="h100"
            size="large"
            :hide-timeout="50"
            placement="bottom"
            trigger="click"
            :hide-on-click="true"
        >
            <div class="nav-menu-item" :class="state.currentNavMenu == 'clear' ? 'hover' : ''">
                <Icon :color="configStore.getColorVal('headerBarTabColor')" class="nav-menu-icon" name="el-icon-Delete" size="18" />
            </div>
            <template #dropdown>
                <el-dropdown-menu class="dropdown-menu-box">
                    <el-dropdown-item @click="onClearCache('tp')">{{ t('utils.Clean up system cache') }}</el-dropdown-item>
                    <el-dropdown-item @click="onClearCache('storage')">{{ t('utils.Clean up browser cache') }}</el-dropdown-item>
                    <el-dropdown-item @click="onClearCache('all')" divided>{{ t('utils.Clean up all cache') }}</el-dropdown-item>
                </el-dropdown-menu>
            </template>
        </el-dropdown>
        <el-popover
            @show="onCurrentNavMenu(true, 'adminInfo')"
            @hide="onCurrentNavMenu(false, 'adminInfo')"
            placement="bottom-end"
            :hide-after="0"
            :width="260"
            trigger="click"
            popper-class="admin-info-box"
        >
            <template #reference>
                <div class="admin-info" :class="state.currentNavMenu == 'adminInfo' ? 'hover' : ''">
                    <el-avatar :size="25" fit="fill">
                        <img :src="adminInfo.avatar" alt="" />
                    </el-avatar>
                    <div class="admin-name">{{ adminInfo.nickname }}</div>
                </div>
            </template>
            <div>
                <div class="admin-info-base">
                    <el-avatar :size="70" fit="fill">
                        <img :src="adminInfo.avatar" alt="" />
                    </el-avatar>
                    <div class="admin-info-other">
                        <div class="admin-info-name">{{ adminInfo.nickname }}</div>
                        <div class="admin-info-lasttime">{{ adminInfo.last_login_time }}</div>
                    </div>
                </div>
                <div class="admin-info-footer">
                    <!-- <el-button @click="onAdminInfo" type="primary" plain>{{ t('layouts.personal data') }}</el-button> -->
                    <el-button @click="onLogout" type="danger" plain>{{ t('layouts.cancellation') }}</el-button>
                </div>
            </div>
        </el-popover>
        <div @click="configStore.setLayout('showDrawer', true)" class="nav-menu-item">
            <Icon :color="configStore.getColorVal('headerBarTabColor')" class="nav-menu-icon" name="fa fa-cogs" size="18" />
        </div>
        <Config />
        <TerminalVue />
    </div>
</template>

<script lang="ts" setup>
import { reactive, watchEffect } from 'vue'
import { editDefaultLang } from '/@/lang'
import screenfull from 'screenfull'
import { useConfig } from '/@/stores/config'
import { ElMessage } from 'element-plus'
import { useI18n } from 'vue-i18n'
import Config from './config.vue'
import { useAdminInfo } from '/@/stores/adminInfo'
import { useTerminal } from '/@/stores/terminal'
import { Local, Session } from '/@/utils/storage'
import { ADMIN_INFO, BA_ACCOUNT } from '/@/stores/constant/cacheKey'
import router from '/@/router'
import { routePush } from '/@/utils/router'
import { logout } from '/@/api/backend/index'
import { postClearCache } from '/@/api/common'
import TerminalVue from '/@/components/terminal/index.vue'

import { ref } from 'vue'
import AudioPlayer from '/@/views/backend/broadcast/propaganda/audioPlayer.vue'
import mittBus from '/@/utils/mittBus'

import { updateTaskStatusToFinish } from '/@/api/backend/broadcast/broadcastTask'
import { hangupAllTask } from '/@/api/backend/communication/call'
const dialogTableVisible = ref(false)
const broadcastRecordingData = ref([]) // 需要进行广播的录音数据
const iconColor = ref('black') //

const musicId = ref('') // 音乐Id
const musicName = ref('') // 音乐名称
const musicUrl = ref('') // 当前播放的音乐地址
const audioPlayerRef = ref(null)
const recordingBroadcastIsDuring = ref(false) // 是否播放音频广播中
const currentIndex = ref(0) // 当前音乐数据索引
const previousMusicId = ref('') // 上一首音乐信息
const nextMusicId = ref('') // 下一首音乐信息
const remainingCallDuration = ref(0) //剩余播放时间
const head_id = ref(0) // 头id

const { t } = useI18n()

const adminInfo = useAdminInfo()
const configStore = useConfig()
const terminal = useTerminal()

const state = reactive({
    isFullScreen: false,
    currentNavMenu: '',
    showLayoutDrawer: false,
})

const onCurrentNavMenu = (status: boolean, name: string) => {
    state.currentNavMenu = status ? name : ''
}

const onFullScreen = () => {
    if (!screenfull.isEnabled) {
        ElMessage.warning(t('layouts.Full screen is not supported'))
        return false
    }
    screenfull.toggle()
    screenfull.onchange(() => {
        state.isFullScreen = screenfull.isFullscreen
    })
}

const onAdminInfo = () => {
    routePush({ name: 'routine/adminInfo' })
}

const onLogout = () => {
    logout().then(() => {
        Local.remove(ADMIN_INFO)
        router.go(0)
    })
}

const onClearCache = (type: string) => {
    if (type == 'storage' || type == 'all') {
        const adminInfo = Local.get(ADMIN_INFO)
        const baAccount = Local.get(BA_ACCOUNT)
        Session.clear()
        Local.clear()
        Local.set(ADMIN_INFO, adminInfo)
        Local.set(BA_ACCOUNT, baAccount)
        if (type == 'storage') return
    }
    postClearCache(type).then(() => {})
}

mittBus.on('playBroadcastTasks', (res) => {
    // 获取需要播放的数据
    broadcastRecordingData.value = res.broadcastRecordingData
    remainingCallDuration.value = Date.now() + 1000 * Math.floor(res.duration)
    head_id.value = res.head_id
    console.log('res.duration:' + res.duration)
    console.log('remainingCallDuration.value:' + remainingCallDuration.value)
    // 更新音频文件数据
    console.log('播放录音广播')
    musicId.value = broadcastRecordingData.value[0].recording_file_id
    musicUrl.value = broadcastRecordingData.value[0].recording_file_url
    musicName.value = broadcastRecordingData.value[0].recording_file_name
    handleBroadcastRecordingData(broadcastRecordingData.value[0].recording_file_id)
    currentIndex.value = broadcastRecordingData.value[0].index
    // 打开播放框
    dialogTableVisible.value = true
    // 更改标签的颜色
    iconColor.value = 'red'
    // 播放音频
    audioPlayerRef.value.playAudio()
    // 是否播放音频广播中
    recordingBroadcastIsDuring.value = true
})

// 根据传入的musicId获取当前播放列表歌曲的上一首和下一首
function handleBroadcastRecordingData(currentMusicId: string) {
    console.log('获取上一首和下一首')
    broadcastRecordingData.value.forEach((item, index) => {
        if (item.recording_file_id === currentMusicId) {
            // 更新当前播放音乐信息
            musicId.value = item.recording_file_id
            musicUrl.value = item.recording_file_url
            musicName.value = item.recording_file_name
            // 更新上一首歌
            if (index === 0) {
                previousMusicId.value = broadcastRecordingData.value[broadcastRecordingData.value.length - 1].recording_file_id
            } else if (index > 0) {
                previousMusicId.value = broadcastRecordingData.value[index - 1].recording_file_id
            }
            // 更新下一首歌
            if (index === broadcastRecordingData.value.length - 1) {
                nextMusicId.value = broadcastRecordingData.value[0].recording_file_id
            } else if (index < broadcastRecordingData.value.length - 1) {
                nextMusicId.value = broadcastRecordingData.value[index + 1].recording_file_id
            }
        }
        index++
    })
}

//播放上一首
function previousAudio() {
    console.log('页面上一首')
    handleBroadcastRecordingData(previousMusicId.value)
}

//播放下一首
function nextAudio() {
    console.log('页面下一首')
    handleBroadcastRecordingData(nextMusicId.value)
}

// 播放结束
function broadcastFinish() {
    //修改定时广播的状态为已经执行
    updateTaskStatusToFinish(head_id.value)
        .then((response) => {
            // 结束通话
            hangupAllTask()
                .then((response) => {
                    // 结束音频
                    audioPlayerRef.value.playAudio()
                    // 更改图标状态
                    iconColor.value = 'black'
                    // 是否播放音频广播中
                    recordingBroadcastIsDuring.value = false
                    // 关闭对话框
                    dialogTableVisible.value = false
                    // 处理响应数据
                    console.log(response)
                })
                .catch((error) => {
                    // 处理错误
                    console.error(error)
                })
        })
        .catch((error) => {
            // 处理错误
            console.error(error)
        })
    console.log('播放结束')
}
</script>

<style scoped lang="scss">
.nav-menus.Default {
    border-radius: var(--el-border-radius-base);
    box-shadow: var(--el-box-shadow-light);
}
.nav-menus {
    display: flex;
    align-items: center;
    height: 100%;
    margin-left: auto;
    background-color: v-bind('configStore.getColorVal("headerBarBackground")');
    .nav-menu-item {
        height: 100%;
        width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        .nav-menu-icon {
            box-sizing: content-box;
            color: v-bind('configStore.getColorVal("headerBarTabColor")');
        }
        &:hover {
            .icon {
                animation: twinkle 0.3s ease-in-out;
            }
        }
    }
    .admin-info {
        display: flex;
        height: 100%;
        padding: 0 10px;
        align-items: center;
        cursor: pointer;
        user-select: none;
        color: v-bind('configStore.getColorVal("headerBarTabColor")');
    }
    .admin-name {
        padding-left: 6px;
        white-space: nowrap;
    }
    .nav-menu-item:hover,
    .admin-info:hover,
    .nav-menu-item.hover,
    .admin-info.hover {
        background: v-bind('configStore.getColorVal("headerBarHoverBackground")');
    }
}
.dropdown-menu-box :deep(.el-dropdown-menu__item) {
    justify-content: center;
}
.admin-info-base {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    padding-top: 10px;
    .admin-info-other {
        display: block;
        width: 100%;
        text-align: center;
        padding: 10px 0;
        .admin-info-name {
            font-size: var(--el-font-size-large);
        }
    }
}
.admin-info-footer {
    padding: 10px 0;
    margin: 0 -12px -12px -12px;
    display: flex;
    justify-content: space-around;
}
.pt2 {
    padding-top: 2px;
}

@keyframes twinkle {
    0% {
        transform: scale(0);
    }
    80% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

.el-button--text {
    margin-right: 15px;
}
.el-select {
    width: 300px;
}
.el-input {
    width: 300px;
}
.dialog-footer button:first-child {
    margin-right: 10px;
}

.dialog-row {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.el-col {
    text-align: center;
    width: 300px;
}

.el-row {
    margin-bottom: 20px;
    &:last-child {
        margin-bottom: 0;
    }
}
</style>
