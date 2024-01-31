<template>
  <!-- 广播弹窗-带播放器 -->
    <el-dialog
            title="录音广播"
            :before-close="handleClose"
            :close-on-click-modal="false">
    <el-row>
      <el-col>
        <el-countdown title="剩余播放时间" :value="remainingCallDuration"  @finish="broadcastFinish" />
      </el-col>
    </el-row>
    <el-divider />
    <el-row>
      <el-table :data="broadcastRecordingData">
        <el-table-column property="recording_file_name" label="文件名称"/>
        <el-table-column property="recording_file_url" label="文件地址" width="250px"/>
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
</template>

<script setup>
import {nextTick, ref} from 'vue'
import AudioPlayer from "/@/views/backend/broadcast/propaganda/audioPlayer.vue";
import { ElMessage } from 'element-plus'
import { areaBroadcast } from '/@/api/backend/broadcast/areaBroadcast'
import { callApi, hangupAllApi } from '/@/api/backend/communication/call'
import { ElMessageBox } from 'element-plus'

const broadcastRecordingData = ref([]); // 需要进行广播的录音数据
const broadcastAreaIds = ref([3000]); // 广播区域，默认全体广播
const musicId = ref(""); // 音乐Id
const musicName = ref(""); // 音乐名称
const musicUrl = ref(""); // 当前播放的音乐地址
const audioPlayerRef = ref(null);
const recordingBroadcastIsDuring = ref(false); // 是否播放音频广播中
const currentIndex = ref(0); // 当前音乐数据索引
const previousMusicId = ref(""); // 上一首音乐信息
const nextMusicId = ref(""); // 下一首音乐信息
const remainingCallDuration = ref(0); //剩余播放时间

const emits = defineEmits(['broadcastFinish']);

const handleClose = (done) => {
    ElMessageBox.confirm('请问确定要结束音频广播吗?')
        .then(() => {
            broadcastFinish();
            done()
        })
        .catch(() => {
        // catch error
        })
}

defineExpose({
    broadcastCallAll,
});

// 发起全体广播
function broadcastCallAll(broadcastData) {
    // 发起全体广播请求
    callApi(broadcastAreaIds.value[0])
        .then((response) => {
            console.log(response);
            // 使用nextTick确保audioPlayerRef.value有值后再调用playAudio
            nextTick(() => {
                if (audioPlayerRef.value) {
                    // 播放音频
                    playRecordingBroadcast(broadcastData);
                } else {
                    console.error("audioPlayerRef.value is not available yet");
                }
            });
        })
        .catch((error) => {
            // 处理错误
            console.error(error)
        })
}

// 播放录音广播
function playRecordingBroadcast(broadcastData) {
    // 获取播放数据
    broadcastRecordingData.value = broadcastData;
    // 计算播放时间
    calculateDuration(broadcastRecordingData);
    console.log("[" + Date.now() + "-录音广播日志]" + "：开始播放录音广播!");
    // 更新音频文件数据
    musicId.value = broadcastRecordingData.value[0].recording_file_id;
    musicUrl.value = broadcastRecordingData.value[0].recording_file_url;
    musicName.value = broadcastRecordingData.value[0].recording_file_name;
    handleBroadcastRecordingData(broadcastRecordingData.value[0].recording_file_id);
    currentIndex.value = broadcastRecordingData.value[0].index;
    // 调用子组件的方法
    audioPlayerRef.value.playAudio();
    // 是否播放音频广播中
    recordingBroadcastIsDuring.value = true;
}

// 计算播放时间
function calculateDuration(broadcastRecordingData) {
    remainingCallDuration.value = 0;
    broadcastRecordingData.value.forEach((item, index) => {
        // 假设item.duration是一个表示时间的字符串，例如 "01:23:45"
        let timeString = item.duration; // 假设item.duration是 "01:23:45"
        // 将时间字符串转换为毫秒
        let timeParts = timeString.split(":");
        let hours = parseInt(timeParts[0], 10);
        let minutes = parseInt(timeParts[1], 10);
        let seconds = parseInt(timeParts[2], 10);
        let totalMilliseconds = (hours * 60 * 60 * 1000) + (minutes * 60 * 1000) + (seconds * 1000);
        // 计算总的播放时长
        remainingCallDuration.value += totalMilliseconds;
        index++;
    });
    // 转换为时间戳形式
    remainingCallDuration.value = Date.now() + remainingCallDuration.value;
}

// 根据传入的musicId获取当前播放列表歌曲的上一首和下一首
function handleBroadcastRecordingData(currentMusicId) {
    broadcastRecordingData.value.forEach((item, index) => {
        if (item.recording_file_id === currentMusicId) {
            // 更新当前播放音乐信息
            musicId.value = item.recording_file_id;
            musicUrl.value = item.recording_file_url;
            musicName.value = item.recording_file_name;
            // 更新上一首歌
            if (index === 0) {
                previousMusicId.value = broadcastRecordingData.value[broadcastRecordingData.value.length - 1].recording_file_id;
            } else if (index > 0) {
                previousMusicId.value = broadcastRecordingData.value[index - 1].recording_file_id;
            }
            // 更新下一首歌
            if (index === broadcastRecordingData.value.length - 1) {
                nextMusicId.value = broadcastRecordingData.value[0].recording_file_id;
            } else if (index < broadcastRecordingData.value.length - 1) {
                nextMusicId.value = broadcastRecordingData.value[index + 1].recording_file_id;
            }
        }
        index++;
    });
}

//播放上一首
function previousAudio() {
    console.log("页面上一首")
    handleBroadcastRecordingData(previousMusicId.value);
}

//播放下一首
function nextAudio() {
    console.log("页面下一首")
    handleBroadcastRecordingData(nextMusicId.value);
}

// 播放结束
function broadcastFinish() {
    if (recordingBroadcastIsDuring.value) {
        console.log("[" + Date.now() + "-音频广播日志]" + "：录音广播播放结束!");
        remainingCallDuration.value = 0;
        // 是否播放音频广播中
        recordingBroadcastIsDuring.value = false;
        // 调用子组件的方法
        audioPlayerRef.value.pauseAudio();
        // 等待1.5秒
        setTimeout(() => {
            // 结束所有通话
            hangupAllApi()
                .then((response) => {
                    console.log(response)
                    // 弹出提示
                    ElMessage({
                        message: '录音广播播放已结束！',
                        type: 'success',
                    })
                    // 关闭弹窗
                    emits("broadcastFinish")
                })
                .catch((error) => {
                    // 处理错误
                    console.error(error)
                })
        }, 1500);
    }
}

</script>

<style scoped lang="scss">

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
