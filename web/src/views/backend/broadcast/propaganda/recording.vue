<template>
  <div class="default-main ba-table-box">
    <!-- 录音卡片 -->
    <el-row style="margin-top:10px;margin-bottom:20px;">
      <el-col :span="24">
        <el-card class="header-card">
          <!-- 卡片顶端 -->
          <el-row>
            <div class="clearfix">
              <div class="icon-card">
                <el-button text size="large" @click="returnPreviousPage" alt="返回上一页">
                  <Icon name="el-icon-ArrowLeftBold" color="black" size="16" />
                </el-button>
              </div>
              <span>录音广播</span>
            </div>
          </el-row>
          <el-row>
            <el-divider></el-divider>
          </el-row>
          <!-- 卡片中间 -->
          <el-row>
            <!-- 录音显示区域 -->
            <el-col :span="16">
                <el-row>
                    <!-- 录音波形 -->
                    <div class="ctrlProcessWave noneWrap" />
                </el-row>
              <el-row>
                  <div class="ctrlProcessX" style="height:40px;background:cornflowerblue;position:absolute;" :style="{width:state.powerLevel+'%'}"></div>
                  <div class="ctrlProcessT" style="padding-left:50px; line-height:40px; position: relative;">{{ "时间：" + state.duration + " / " + "分贝：" + state.powerLevel }}</div>
              </el-row>
                <!-- 音频播放 -->
                <audio ref="LogAudioPlayer" style="width: 100%" />
            </el-col>
            <!-- 录音按钮区域 -->
            <el-col :span="8">
              <!-- 一个按钮 -->
              <!-- recordingIsBegins = true 开始录音 显示三个按钮-->
              <div v-if="!recordingIsBegins"  class="header-button">
                <el-button @click="recordingBegins" type="danger" size="large" circle alt="开始录音">
                  <Icon name="el-icon-Microphone" color="white" size="25" />
                </el-button>
                <el-button @click="recordingBegins" text size="large">开始录音</el-button>
              </div>
              <!-- 三个按钮 -->
              <!-- recordingIsBegins = true 开始录音 显示三个按钮-->
              <div v-if="recordingIsBegins"  class="header-button">
                <el-button v-if="recordingIsDuring" @click="recordingSuspend" type="danger" size="large" circle alt="暂停录音">
                  <Icon name="el-icon-VideoPause" color="white" size="50" />
                </el-button>
                <el-button v-if="!recordingIsDuring" @click="recordingContinue" type="danger" size="large" circle alt="继续录音">
                  <Icon name="el-icon-VideoPlay" color="white" size="50" />
                </el-button>
                <el-button @click="recordingAgain" type="primary" size="large" circle alt="重新录音">
                  重录
                </el-button>
                <el-button @click="recordingSave" type="success" size="large" circle alt="保存录音">
                  <Icon name="el-icon-Select" color="white" size="25" />
                </el-button>
              </div>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>
    <!-- 列表卡片 -->
    <el-row :gutter="12">
      <el-col :span="24" :body-style="{ padding: '0px' }">
        <el-card shadow="never">
          <!-- 卡片顶端 -->
          <el-row>
            <el-col :span="1">
              <Icon name="el-icon-Microphone" color="black" size="16" />
            </el-col>
            <div class="clearfix">
              <div class="icon-card">
                <span>我的录音</span>
              </div>
            </div>
          </el-row>
          <el-row>
            <el-divider></el-divider>
          </el-row>
          <div class="common-layout">
            <!-- 表格顶部菜单 -->
            <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
            <TableHeader
                :buttons="['comSearch', 'quickSearch', 'columnDisplay']"
                :quick-search-placeholder="t('Quick search placeholder', { fields: t('broadcast.propaganda.quick Search Fields') })"
            >
              <el-col :span="5">
                <el-button type="success" :icon="ArrowRightBold" round @click="playRecordingBroadcast()" :disabled="!enableBatchOpt">播放录音广播</el-button>
              </el-col>
              <el-col :span="4">
                <el-button type="danger" :icon="ArrowRightBold" round @click="onAction('delete')" :disabled="!enableBatchOpt">批量删除</el-button>
              </el-col>
<!--              <el-col :span="4">-->
<!--                <el-button type="info" :icon="ArrowRightBold" round @click="onAction('add')" >上传录音</el-button>-->
<!--              </el-col>-->
            </TableHeader>

            <!-- 表格 -->
            <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
            <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
            <Table ref="tableRef"></Table>

            <!-- 表单 -->
            <PopupForm :url="recordingUrl" :duration="state.duration"/>
            <!-- 播放音频广播 -->
            <BroadcastPopupForm
                ref="broadcastPopupFormRef"
                v-model="dialogTableVisible"
                @broadcastFinish="broadcastFinish"
            />
          </div>
          <!-- 播放器 -->
          <div v-show="audioPlayerTableVisible">
            <audio-player
                ref="audioPlayerRef"
                class="audio-box"
                :fileid="musicId"
                :fileurl="musicUrl"
                :filename="musicName"
                @previousAudio="previousAudio"
                @nextAudio="nextAudio"
            ></audio-player>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup lang="ts">
import {ref, provide, onMounted, computed, reactive, onBeforeUnmount, watch} from 'vue'
import baTableClass from '/@/utils/baTable'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './recordingPopupForm.vue'
import BroadcastPopupForm from './broadcastRecordingPopupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { ArrowRightBold } from '@element-plus/icons-vue'
import { loadJs } from '/src/utils/common'
import AudioPlayer from '/src/views/backend/broadcast/propaganda/audioPlayer.vue'
import Icon from "/src/components/icon/index.vue"
import { useRouter } from 'vue-router'
import { fileUpload } from '/@/api/common'
/** 录音 **/
import Recorder from 'recorder-core'
// 需要使用到的音频格式编码引擎的js文件统统加载进来，这些引擎文件会比较大
import 'recorder-core/src/engine/mp3'
import 'recorder-core/src/engine/mp3-engine'
import 'recorder-core/src/extensions/waveview'

loadJs('https://unpkg.com/axios/dist/axios.min.js')

defineOptions({
    name: 'broadcast/propaganda/recording',
})

const { t } = useI18n()
const tableRef = ref()
const audioPlayerRef = ref(null);
const broadcastPopupFormRef = ref(null);

const recordingIsBegins = ref(false); // 是否开始录音
const recordingIsDuring = ref(false); // 是否录音中
const recordingUrl = ref(""); // 录音上传后的url

const musicData = ref([]); // 当前页的音乐数据
const currentIndex = ref(0); // 当前音乐数据索引
const musicId = ref(""); // 当前播放的音乐id
const musicUrl = ref(""); // 当前播放的音乐地址
const musicName = ref(""); // 当前播放的音乐名称
const previousMusicId = ref(""); // 上一首音乐信息
const nextMusicId = ref(""); // 下一首音乐信息

const router = useRouter(); // 路由

const broadcastRecordingData = ref([]); // 需要进行广播的录音数据
const dialogTableVisible = ref(false); //播放音频广播弹窗
const audioPlayerTableVisible = ref(false); //播放器是否显示

/** ==================录音开始=============== **/
const LogAudioPlayer = ref()
const state = reactive({
    Rec: Recorder,
    type: 'mp3',
    bitRate: 16,
    sampleRate: 16000,
    rec: 0,
    duration: 0,
    powerLevel: 0,
    recOpenDialogShow: 0,
    logs: [],
    recLogLast: null,
    isStartFlag: false, // 是否开始录制
    pauseResume: false, // 暂停/继续
    stopVedio: false, // 停止录音
    isStatus: false // 重新录制时，收回播放和上传
})

/** 打开麦克风权限 */
function recOpen() {
    const This = state
    const rec = (state.rec = Recorder({
        type: state.type,
        bitRate: state.bitRate,
        sampleRate: state.sampleRate,
        onProcess: function (buffers, powerLevel, duration, sampleRate) {
            state.duration = transTime({duration: duration / 1000})
            state.powerLevel = powerLevel
            state.wave.input(buffers[buffers.length - 1], powerLevel, sampleRate)
        }
    }))
    state.dialogInt = setTimeout(function () {
        // 定时8秒后打开弹窗，用于监测浏览器没有发起权限请求的情况
        showDialog()
    }, 8000)
    rec.open(
        function () {
            dialogCancel()
            reclog('已打开:' + state.type + ' ' + state.sampleRate + 'hz ' + state.bitRate + 'kbps', 2);
            state.wave = Recorder.WaveView({ elem: '.ctrlProcessWave' })
        },
        function (msg, isUserNotAllow) {
            dialogCancel()
            reclog((isUserNotAllow ? 'UserNotAllow，' : '') + '打开失败：' + msg, 1);
        }
    )
    state.waitDialogClickFn = function () {
        dialogCancel()
        reclog('打开失败：权限请求被忽略，用户主动点击的弹窗', 1);
    }
}
/** 开始录制 */
function recStart() {
    if (!state.rec || !Recorder.IsOpen()) {
        reclog('未打开录音', 1);
        return
    }
    state.isStartFlag = true
    state.isStatus = !state.isStatus
    state.stopVedio = false
    state.rec.start()
    const set = state.rec.set
    reclog('录制中：' + set.type + ' ' + set.sampleRate + 'hz ' + set.bitRate + 'kbps');
}
/** 停止录制 */
function recStop() {
    if (!(state.rec && Recorder.IsOpen())) {
        reclog('未打开录音', 1);
        return Promise.resolve();
    }

    const This = this;
    const rec = state.rec;

    return new Promise((resolve, reject) => {
        rec.stop(
            function (blob, duration) {
                reclog('已录制:', '', {
                    blob: blob,
                    duration: duration,
                    rec: rec
                });
                resolve(); // 当录音停止后，Promise resolve。
            },
            function (s) {
                reclog('录音失败：' + s, 1);
                reject(new Error('录音失败')); // 如果录音失败，Promise reject。
            }
        );
    });
}
// function recStop() {
//     if (!(state.rec && Recorder.IsOpen())) {
//         reclog('未打开录音', 1);
//         return
//     }
//     const This = this
//     const rec = state.rec
//     rec.stop(
//         function (blob, duration) {
//             reclog('已录制:', '', {
//                 blob: blob,
//                 duration: duration,
//                 rec: rec
//             })
//         },
//         function (s) {
//             reclog('录音失败：' + s, 1);
//         }
//     )
//     state.stopVedio = true
// }
/** 关闭录音,释放资源 */
function recClose() {
    const rec = state.rec
    state.rec = null;
    if (rec) {
        rec.close()
        reclog('已关闭');
    } else {
        reclog('未打开录音', 1);
    }
}
/** 暂停 */
function recPause() {
    if (state.rec && Recorder.IsOpen()) {
        state.rec.pause()
    } else {
        reclog('未打开录音', 1);
    }
    state.pauseResume = true
}
/** 继续 */
function recResume() {
    if (state.rec && Recorder.IsOpen()) {
        state.rec.resume()
    } else {
        reclog('未打开录音', 1);
    }
    state.pauseResume = false
}
/** 播放 */
// function recPlayLast() {
//     if (!state.recLogLast) {
//         reclog('请先录音，然后停止后再播放', 1);
//         return
//     }
//     recplay(state.recLogLast.idx)
// }
// function recplay(idx) {
//     const This = this
//     const o = state.logs[state.logs.length - idx - 1]
//     o.play = (o.play || 0) + 1
//     const logmsg = function (msg) {
//         o.playMsg = '<span style="color:green">' + o.play + '</span> ' + getTime() + ' ' + msg
//     }
//     logmsg('')
//     const audio = LogAudioPlayer.value
//     audio.controls = true
//     if (!(audio.ended || audio.paused)) {
//         audio.pause()
//     }
//     audio.onerror = function (e) {
//         logmsg('<span style="color:red">播放失败[' + audio.error.code + ']' + audio.error.message + '</span>')
//     }
//     audio.src = (window.URL || webkitURL).createObjectURL(o.res.blob)
//     audio.play()
// }
/** 上传 */
function recUploadLast() {
    if (!state.recLogLast) {
        reclog('请先录音，然后停止后再上传', 1)
        return
    }
    console.log("上传中")
    const blob = state.recLogLast.res.blob
    const api = 'https://xx.xx/test_request'
    reclog('开始上传到' + api + '，请求稍后...', '#f60')
    const fd = new FormData()
    fd.append('file', blob, 'recorder.mp3')
    fileUpload(fd).then((res) => {
        if (res.code == 1) {
            console.log("===================================" + res.data.file.url);
            recordingUrl.value = res.data.file.url;
            // 添加录音文件
            onAction('add')
        }
    })
}
/** 日志区 */
function reclog(msg, color, res) {
    const obj = {
        idx: state.logs.length,
        msg: msg,
        color: color,
        res: res,

        playMsg: '',
        down: 0,
        down64Val: ''
    }
    if (res && res.blob) {
        state.recLogLast = obj
    }
    state.logs.splice(0, 0, obj)
    console.log('操作日志(⊙o⊙)：', msg)
}
function getTime() {
    const now = new Date()
    const t =
        ('0' + now.getHours()).substr(-2) +
        ':' +
        ('0' + now.getMinutes()).substr(-2) +
        ':' +
        ('0' + now.getSeconds()).substr(-2)
    return t
}
function intp(s, len) {
    s = s == null ? '-' : s + ''
    if (s.length >= len) return s
    return ('_______' + s).substr(-len)
}
function showDialog() {
    if (!/mobile/i.test(navigator.userAgent)) {
        return // 只在移动端开启没有权限请求的检测
    }
    state.recOpenDialogShow = 1
}
function dialogCancel() {
    clearTimeout(state.dialogInt)
    state.recOpenDialogShow = 0
}
function waitDialogClick() {
    dialogCancel()
    state.waitDialogClickFn()
}
onBeforeUnmount(() => {
    recClose()
})

// 开始录音
function recordingBegins() {
    console.log("开始录音")
    recordingIsBegins.value = true;
    recordingIsDuring.value = true;
    recStart();
}
// 暂停录音
function recordingSuspend() {
    console.log("暂停录音")
    recordingIsDuring.value = false;
    recPause();
}
// 继续录音
function recordingContinue () {
    console.log("继续录音")
    recordingIsDuring.value = true;
    recResume();
}
// 重新录音
function recordingAgain() {
    console.log("重新录音")
    recordingIsDuring.value = true;
    recStart()
}
// 保存录音
function recordingSave() {
    console.log("保存录音")
    recordingIsBegins.value = false;
    recordingIsDuring.value = false;
    // 停止录音
    recStop().then(() => {
        // 上传录音
        recUploadLast();
    });
}

/** ==================录音结束=============== **/

const onAction = (event: string, data: anyObj = {}) => {
    baTable.onTableHeaderAction(event, data)
    baTable.getIndex()
}

const enableBatchOpt = computed(() => (baTable.table.selection!.length > 0 ? true : false));

// 音频播放时间换算
function transTime({duration}: { duration: any }) {
    const minutes = Math.floor(duration / 60);
    const seconds = Math.floor(duration % 60);
    const hours = Math.floor(duration / 60 / 60);
    const formattedMinutes = String(minutes).padStart(2, "0"); //padStart(2,"0") 使用0填充使字符串长度达到2
    const formattedSeconds = String(seconds).padStart(2, "0");
    const formattedHours = String(hours).padStart(2, "0");
    return `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
}

// 录音广播
function playRecordingBroadcast() {
    broadcastRecordingData.value = baTable.table.selection!;
    // 调用子组件方法
    broadcastPopupFormRef.value.broadcastCallAll(broadcastRecordingData.value);
    // 打开播放框
    dialogTableVisible.value = true;
}

// 广播结束
function broadcastFinish() {
    // 关闭播放框
    dialogTableVisible.value = false;
}

// 返回上一页
function returnPreviousPage() {
    router.go(-1)
}

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/broadcast.propaganda.Recording/'),
    {
        pk: 'recording_file_id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { type: 'index', align: 'center', operator: false },
            // { label: t('broadcast.propaganda.recording.recording_file_id'), prop: 'recording_file_id', align: 'center', width: 120, operator: 'RANGE', sortable: 'custom' },
            { label: t('broadcast.propaganda.recording.recording_file_name'), prop: 'recording_file_name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('broadcast.propaganda.recording.recording_file_url'), prop: 'recording_file_url', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: t('broadcast.propaganda.recording.duration'),
                prop: 'duration',
                align: 'center',
                operator: 'eq',
                sortable: 'custom',
                render: 'tags' ,
            },
            {
                label: t('broadcast.propaganda.remark'),
                prop: 'remark',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            { label: t('broadcast.propaganda.recording.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            // { label: t('broadcast.propaganda.recording.update_time'), prop: 'update_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            // { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'recording_file_id', order: 'desc' },
    },
    {
        defaultItems: { duration: null },
    },
    {
        onTableDblclick: ({ row, column }) => {
            musicId.value = row.recording_file_id;
            musicUrl.value = row.recording_file_url;
            musicName.value = row.recording_file_name;
            handleMusicData(row.recording_file_id)
            currentIndex.value = row.index;
            // 显示播放器
            audioPlayerTableVisible.value = true;
            // 调用子组件的方法
            audioPlayerRef.value.playAudio();
            return false;
        },
    }
)

// 根据传入的musicId获取当前播放列表歌曲的上一首和下一首
function handleMusicData(currentMusicId: string) {
    console.log("获取上一首和下一首")
    musicData.value.forEach((item, index) => {
        if (item.recording_file_id === currentMusicId) {
            // 更新当前播放音乐信息
            musicId.value = item.recording_file_id;
            musicUrl.value = item.recording_file_url;
            musicName.value = item.recording_file_name;
            // 更新上一首歌
            if (index === 0) {
                previousMusicId.value = musicData.value[musicData.value.length - 1].recording_file_id;
            } else if (index > 0) {
                previousMusicId.value = musicData.value[index - 1].recording_file_id;
            }
            // 更新下一首歌
            if (index === musicData.value.length - 1) {
                nextMusicId.value = musicData.value[0].recording_file_id;
            } else if (index < musicData.value.length - 1) {
                nextMusicId.value = musicData.value[index + 1].recording_file_id;
            }
        }
        index++;
    });
}

//播放上一首
function previousAudio() {
    handleMusicData(previousMusicId.value)
}

//播放下一首
function nextAudio() {
    handleMusicData(nextMusicId.value)
}

provide('baTable', baTable)

onMounted(() => {
    // 打开麦克风权限
    recOpen()
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
        //获取音频文件数据
        musicData.value = baTable.table.data;
    })
})

watch(
    () => baTable.table.data,
    () => {
        musicData.value = baTable.table.data;
    }
)

</script>

<style scoped lang="scss">
.centered-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
}

.card-button {
  display: flex;
  justify-content: center;
  align-items: center;
}
.card-text {
  padding: 14px;
  text-align:center;
  line-height:18px;
}

.header-text {
  font-size: 14px;
  margin-bottom: 20px;
  text-align: center;
}
.header-button {
  padding: 20px 0;
  text-align: center;
}
.icon-card {
  display: inline-block;
  margin-top: 3px;
}

body{
  word-wrap: break-word;
  background:#f5f5f5 center top no-repeat;
  background-size: auto 680px;
}
pre{
  white-space:pre-wrap;
}
a{
  text-decoration: none;
  color:#06c;
}
a:hover{
  color:#f00;
}

.main{
  max-width:700px;
  margin:0 auto;
  padding-bottom:80px
}

.mainBox{
  margin-top:12px;
  padding: 12px;
  border-radius: 6px;
  background: #fff;
  --border: 1px solid #0b1;
  box-shadow: 2px 2px 3px #aaa;
}


.btns button{
  display: inline-block;
  cursor: pointer;
  border: none;
  border-radius: 3px;
  background: #0b1;
  color:#fff;
  padding: 0 15px;
  margin:3px 20px 3px 0;
  line-height: 36px;
  height: 36px;
  overflow: hidden;
  vertical-align: middle;
}
.btns button:active{
  background: #0a1;
}
.pd{
  padding:0 0 6px 0;
}
.lb{
  display:inline-block;
  vertical-align: middle;
  background:#00940e;
  color:#fff;
  font-size:14px;
  padding:2px 8px;
  border-radius: 99px;
}
.learnDetail {
  background: #fff;

}
// 音频区
.noneWrap {
  height: 100px;
  width: 100%;
  border-radius: 3px;
  border: 1px solid #dcdfe6;
  box-sizing: border-box;
  display: inline-block;
  vertical-align: bottom;
  margin-bottom: 10px;
}
.processWave {
  position: absolute;
  top: 74px;
  left: 20px;
}
</style>
