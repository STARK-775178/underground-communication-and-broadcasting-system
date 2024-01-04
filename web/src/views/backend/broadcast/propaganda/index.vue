<template>
    <div class="default-main ba-table-box">
        <el-row :gutter="12" style="margin-top:10px;margin-bottom:20px;">
            <!-- 直播广播 -->
            <el-col :span="12" :body-style="{ padding: '0px' }">
                <el-card shadow="hover">
                    <div class="card-button">
                      <router-link to="/admin/broadcast/propaganda/live">
                        <el-button type="primary" size="large" circle>
                          <Icon name="el-icon-VideoCamera" color="white" size="25" />
                        </el-button>
                        <el-button text size="large">
                            直播广播<el-icon class="el-icon--right"><ArrowRightBold /></el-icon>
                        </el-button>
                      </router-link>
                    </div>
                    <div class="card-text" >
                        <span>实时语音广播，每日人工播报</span>
                    </div>
                </el-card>
            </el-col>
            <!-- 录音广播 -->
            <el-col :span="12" :body-style="{ padding: '0px' }">
                <el-card shadow="hover">
                    <div class="card-button">
                      <router-link to="/admin/broadcast/propaganda/recording">
                        <el-button type="danger" size="large" circle>
                          <Icon name="el-icon-Microphone" color="white" size="25" />
                        </el-button>
                        <el-button text size="large">
                            录音广播<el-icon class="el-icon--right"><ArrowRightBold /></el-icon>
                        </el-button>
                      </router-link>
                    </div>
                    <div class="card-text">
                        <span>录制好的语音广播，用于某些事件的说明</span>
                    </div>
                </el-card>
            </el-col>
        </el-row>
        <el-row :gutter="12">
            <el-col :span="24" :body-style="{ padding: '0px' }">
                <el-card shadow="never">
                    <div class="common-layout">
                        <!-- 表格顶部菜单 -->
                        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
                        <TableHeader
                            :buttons="['comSearch', 'quickSearch', 'columnDisplay']"
                            :quick-search-placeholder="t('Quick search placeholder', { fields: t('broadcast.propaganda.quick Search Fields') })"
                        >
                            <el-col :span="5">
                                <el-button type="success" :icon="ArrowRightBold" round @click="playAudioBroadcast()" :disabled="!enableBatchOpt">播放音频广播</el-button>
                            </el-col>
<!--                            <el-col :span="3">-->
<!--                                <el-button type="info" :icon="ArrowRightBold" round>批量操作</el-button>-->
<!--                            </el-col>-->
                            <el-col :span="4">
                                <el-button type="danger" :icon="ArrowRightBold" round @click="onAction('delete')" :disabled="!enableBatchOpt">批量删除</el-button>
                            </el-col>
                            <el-col :span="4">
                                <el-button type="info" :icon="ArrowRightBold" round @click="onAction('add')" >上传歌曲</el-button>
                            </el-col>
                        </TableHeader>

                        <!-- 表格 -->
                        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
                        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
                        <Table ref="tableRef"></Table>

                        <!-- 表单 -->
                        <PopupForm/>
                    </div>
                    <!-- 播放器 -->
                    <div>
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
import { ArrowRightBold} from '@element-plus/icons-vue'
import {ref, provide, onMounted, computed} from 'vue'
import baTableClass from '/@/utils/baTable'
import {baTableApi} from '/@/api/common'
import {useI18n} from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import {loadJs} from '/@/utils/common'
import AudioPlayer from '/src/views/backend/broadcast/propaganda/audioPlayer.vue'
import { TableColumnCtx } from "element-plus";
import { Howl } from 'howler';
import Icon from "/@/components/icon/index.vue"; // 引入 Howl 对象

loadJs('https://unpkg.com/axios/dist/axios.min.js')

defineOptions({
    name: 'broadcast/propaganda',
})

const {t} = useI18n()
const tableRef = ref()
const audioPlayerRef = ref(null);

const recordingBroadcastIsDuring = ref(false); // 是否播放音频广播中
const musicData = ref([]); // 当前页的音乐数据
const baTableMusicData = ref([]); // 当前页的音乐数据
const broadcastRecordingData = ref([]); // 需要进行广播的录音数据

const currentIndex = ref(0); // 当前音乐数据索引
const musicUrl = ref(""); // 当前播放的音乐地址
const musicDataUrl = ref(""); // 播放音乐时间获取用的url
const musicId = ref("");
const previousMusicId = ref(""); // 上一首音乐信息
const nextMusicId = ref(""); // 下一首音乐信息
const musicName = ref(""); // 音乐名称

const onAction = (event: string, data: anyObj = {}) => {
    baTable.onTableHeaderAction(event, data)
    baTable.getIndex()
}

const enableBatchOpt = computed(() => (baTable.table.selection!.length > 0 ? true : false));

// 音频播放时间换算
function transTime(duration) {
    const minutes = Math.floor(duration / 60);
    const seconds = Math.floor(duration % 60);
    const formattedMinutes = String(minutes).padStart(2, "0"); //padStart(2,"0") 使用0填充使字符串长度达到2
    const formattedSeconds = String(seconds).padStart(2, "0");
    return `${formattedMinutes}:${formattedSeconds}`;
}

// 播放音频广播
function playAudioBroadcast() {
    console.log("播放录音广播")
    broadcastRecordingData.value = baTable.table.selection!;
    musicId.value = broadcastRecordingData.value[0].voice_file_id;
    musicUrl.value = broadcastRecordingData.value[0].voice_file_url;
    musicName.value = broadcastRecordingData.value[0].voice_file_name;
    handleBroadcastRecordingData(broadcastRecordingData.value[0].voice_file_id);
    currentIndex.value = broadcastRecordingData.value[0].index;
    recordingBroadcastIsDuring.value = true;
    // 调用子组件的方法
    audioPlayerRef.value.playAudio();
}

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/broadcast.Propaganda/'),
    {
        pk: 'voice_file_id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { type: 'index', align: 'center', operator: false },
            // {label: t('broadcast.propaganda.voice_file_id'), prop: 'voice_file_id', align: 'center', width: 100, operator: 'RANGE', sortable: 'custom'},
            {label: t('broadcast.propaganda.voice_file_url'), prop: 'voice_file_url', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false},
            {
                label: t('broadcast.propaganda.voice_file_name'),
                prop: 'voice_file_name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {
                label: t('broadcast.propaganda.remark'),
                prop: 'remark',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false
            },
            {
                label: t('broadcast.propaganda.duration'),
                prop: 'duration',
                align: 'center',
                operator: 'eq',
                sortable: 'custom',
                render: 'tags' ,
                renderFormatter: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => {
                    musicDataUrl.value = row.voice_file_url
                    // 使用 howler.js 加载音频文件并获取时长
                    const sound = new Howl({ src: [musicDataUrl.value] });
                    sound.on('load', function() {
                        row.duration = transTime(sound.duration()); // 换算成时间格式
                    });
                    return row.duration;
                }
            },
            // {label: t('broadcast.propaganda.update_time'), prop: 'update_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss'},
            // {label: t('broadcast.propaganda.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss'},
            // {label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false},
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: {prop: 'voice_file_id', order: 'desc'},
    },
    {
        defaultItems: {duration: null},
    },
    {
        onTableDblclick: ({ row, column }) => {
            musicId.value = row.voice_file_id;
            musicUrl.value = row.voice_file_url;
            musicName.value = row.voice_file_name;
            handleMusicData(row.voice_file_id)
            recordingBroadcastIsDuring.value = false;
            currentIndex.value = row.index;
            // 调用子组件的方法
            audioPlayerRef.value.playAudio();
            return false;
        },
    }
)

// 根据传入的musicId获取当前歌曲的上一首和下一首并将歌曲数据
function handleMusicData(currentMusicId: string) {
    musicData.value.forEach((item, index) => {
        if (item.voice_file_id === currentMusicId) {
            // 更新当前播放音乐信息
            musicId.value = item.voice_file_id;
            musicUrl.value = item.voice_file_url;
            musicName.value = item.voice_file_name;
            // 更新上一首歌
            if (index === 0) {
                previousMusicId.value = musicData.value[musicData.value.length - 1].voice_file_id;
            } else if (index > 0) {
                previousMusicId.value = musicData.value[index - 1].voice_file_id;
            }
            // 更新下一首歌
            if (index === musicData.value.length - 1) {
                nextMusicId.value = musicData.value[0].voice_file_id;
            } else if (index < musicData.value.length - 1) {
                nextMusicId.value = musicData.value[index + 1].voice_file_id;
            }
        }
        index++;
    });
}

function handleBroadcastRecordingData(currentMusicId: string) {
    console.log("获取上一首和下一首")
    broadcastRecordingData.value.forEach((item, index) => {
        if (item.voice_file_id === currentMusicId) {
            // 更新当前播放音乐信息
            musicId.value = item.voice_file_id;
            musicUrl.value = item.voice_file_url;
            musicName.value = item.voice_file_name;
            // 更新上一首歌
            if (index === 0) {
                previousMusicId.value = broadcastRecordingData.value[broadcastRecordingData.value.length - 1].voice_file_id;
            } else if (index > 0) {
                previousMusicId.value = broadcastRecordingData.value[index - 1].voice_file_id;
            }
            // 更新下一首歌
            if (index === broadcastRecordingData.value.length - 1) {
                nextMusicId.value = broadcastRecordingData.value[0].voice_file_id;
            } else if (index < broadcastRecordingData.value.length - 1) {
                nextMusicId.value = broadcastRecordingData.value[index + 1].voice_file_id;
            }
        }
        index++;
    });
}

//播放上一首
function previousAudio() {
    console.log("页面上一首")
    if (recordingBroadcastIsDuring.value === true) {
        handleBroadcastRecordingData(previousMusicId.value);
    }else {
        handleMusicData(previousMusicId.value)
    }
}

//播放下一首
function nextAudio() {
    console.log("页面下一首")
    if (recordingBroadcastIsDuring.value === true) {
        handleBroadcastRecordingData(nextMusicId.value);
    }else {
        handleMusicData(nextMusicId.value)
    }
}

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
        musicData.value = baTable.table.data
    })
})
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
</style>
