<template>
  <div>
    <audio @timeupdate="updateProgress" ref="audioRef" :src="fileurl" autoplay controls loop style="display: none"></audio>
    <el-slider class="slider_box" v-model="currentProgress" :show-tooltip="false" @change="handleProgressChange" />
    <div class="audio_right">
      <div class="left">{{ filename }}</div>
      <div class="center">
        <el-button text size="large" @click="previousAudio" alt="上一首">
          <Icon name="el-icon-CaretLeft" color="grey" size="70" />
        </el-button>
        <el-button v-if="!audioIsPlay" text size="large" @click="playAudio" alt="播放">
          <Icon name="el-icon-VideoPause" color="aquamarine" size="70" />
        </el-button>
        <el-button v-if="audioIsPlay" text size="large" @click="playAudio" alt="暂停">
          <Icon name="el-icon-VideoPlay" color="aquamarine" size="70" />
        </el-button>
        <el-button text size="large" @click="nextAudio" alt="下一首">
          <Icon name="el-icon-CaretRight" color="grey" size="70" />
        </el-button>
      </div>
      <div class="audio_time">{{ audioStart }}/{{ durationTime }}</div>
      <div class="volume">
        <div class="volume_progress" v-show="audioHuds">
          <el-slider
              vertical
              height="100px"
              class="volume_bar"
              v-model="audioVolume"
              :show-tooltip="false"
              @change="handleAudioVolume"
          />
        </div>
        <el-button text v-if="audioVolume <= 0" size="large" @click.stop="audioHuds = !audioHuds" alt="静音">
          <Icon name="el-icon-Mute" color="grey" size="40" />
        </el-button>
        <el-button text v-if="audioVolume > 0" size="large" @click.stop="audioHuds = !audioHuds" alt="非静音">
          <Icon name="el-icon-Microphone" color="grey" size="40" />
        </el-button>

      </div>
    </div>
  </div>
</template>

<script setup>
import {ref, onMounted, computed} from "vue";
import Icon from "/@/components/icon/index.vue";
const props = defineProps({
    fileid: "",
    fileurl: "",
    filename: "",
});
const audioIsPlay = ref(true); //音频是否在播放
const audioStart = ref("0:00");
const durationTime = ref("0:00"); //音频的总时长，显示的时间格式
const duration = ref(0); //音频的总时长
const audioVolume = ref(50); //音量的默认值是0.5
const audioHuds = ref(false); //是否显示音量slider
const audioRef = ref(null);
const currentProgress = ref(0);

defineExpose({
    playAudio,
});

const emits = defineEmits(['previousAudio', 'nextAudio']);

onMounted(() => {
    calculateDuration(props.fileurl);
});

// 获取音频时长
function calculateDuration(fileurl) {
    var myVid = audioRef.value;
    myVid.loop = false;
    myVid.src = fileurl;
    // 监听音频播放完毕
    myVid.addEventListener(
        "ended",
        function () {
            audioIsPlay.value = true;
            currentProgress.value = 0;
        },
        false
    );
    if (myVid != null) {
        myVid.oncanplay = function () {
            duration.value = myVid.duration; // 计算音频时长
            durationTime.value = transTime(myVid.duration); //换算成时间格式
        };
        myVid.volume = 0.5; // 设置默认音量50%
    }
}

// 音频播放时间换算
function transTime(duration) {
    const minutes = Math.floor(duration / 60);
    const seconds = Math.floor(duration % 60);
    const formattedMinutes = String(minutes).padStart(2, "0"); //padStart(2,"0") 使用0填充使字符串长度达到2
    const formattedSeconds = String(seconds).padStart(2, "0");
    return `${formattedMinutes}:${formattedSeconds}`;
}

// 播放暂停控制
function playAudio() {
    if (audioRef.value.paused) {
        audioRef.value.play();
        audioIsPlay.value = false;
    } else {
        audioRef.value.pause();
        audioIsPlay.value = true;
    }
}

//播放上一首
function previousAudio() {
    console.log("上一首")
    emits("previousAudio")
    calculateDuration(props.fileurl);
    playAudio()
}

//播放下一首
function nextAudio() {
    console.log("下一首")
    emits("nextAudio")
    calculateDuration(props.fileurl);
    playAudio()
}

// 根据当前播放时间，实时更新进度条
function updateProgress(e) {
    var value = e.target.currentTime / e.target.duration;
    if (audioRef.value.play) {
        currentProgress.value = value * 100;
        audioStart.value = transTime(audioRef.value.currentTime);
    }
}

//调整播放进度
const handleProgressChange = (val) => {
    if (!val) {
        return;
    }
    // audioRef.value.pause();
    console.log("            audioRef.value.currentTime" + audioRef.value.currentTime)
    let currentTime = duration.value * (val / 100);
    console.log("duration.value" + duration.value)
    console.log("duration.value * (val / 100)" + duration.value * (val / 100))
    // 现在可以安全地设置currentTime
    audioRef.value.currentTime = currentTime;
    audioRef.value.play();
    audioIsPlay.value = false;
    console.log("currentTime" + currentTime)
    console.log("audioRef.value.currentTime" + audioRef.value.currentTime)
};

//调整音量
const handleAudioVolume = (val) => {
    audioRef.value.volume = val / 100;
};
</script>

<style lang="scss" scoped>
.audio_right {
  width: 1400px;
  height: 80px;
  display: flex;
  justify-content: space-around;
  align-items: center;
  // background-color: aquamarine;
  border-radius: 4px;
  padding: 0 10px;
  box-sizing: border-box;
  position: relative;
  .slider_box {
    width: 160px;
    height: 4px;
    border-radius: 5px;
    background-color: #f1f1f1;
    flex: 1;
    margin: 0 8px 4px;
  }
  .audio_icon {
    width: 20px;
    height: 20px;
    margin-bottom: 4px;
    cursor: pointer;
  }
  .audio_time {
    overflow: hidden;
    font-size: 20px;
  }
}
.volume {
  position: relative;
  .volume_progress {
    width: 32px;
    height: 140px;
    position: absolute;
    top: -142px;
    right: -4px;
  }
  .volume_bar {
    background: #f1f1f1;
    border-radius: 4px;
  }
  .volume_icon {
    width: 24px;
    height: 24px;
    cursor: pointer;
  }
}
</style>
<style lang="scss">
.slider_box,
.volume_bar {
  .el-slider__button {
    width: 8px;
    height: 8px;
    border: none;
  }
  .el-slider__bar {
    background: #00db15;
  }
}
.slider_box {
  .el-slider__button-wrapper {
    width: 8px;
  }
}
.volume_bar {
  .el-slider__runway {
    margin: 0 14px !important;
  }
}
.audio_right {
  width: 50%;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>

