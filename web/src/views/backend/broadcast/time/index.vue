<template>
    <!-- 定时广播 -->
    <el-container>
        <el-main>
            <el-row>
                <el-col :span="24">
                    <el-card class="header-card">
                        <div class="clearfix">
                            <div class="icon-card">
                                <Icon name="el-icon-Bell" color="#909399" size="16" />
                            </div>
                            <span>
                             定时广播
                          </span>
                            <el-divider></el-divider>
                        </div>
                        <div class="header-button">
                            <el-button type="success" round @click="onAction('add')">添加定时广播</el-button>
                        </div>
                        <div class="header-text">
                            <span>您可根据需求设置单个或多个按时间、日期、月份</span>
                        </div>
                    </el-card>
                </el-col>
            </el-row>
        </el-main>
    </el-container>
    <!-- 我的定时广播-没有数据 -->
    <el-container v-if="false">
        <el-main>
            <el-row>
                <el-col :span="24">
                    <el-card class="main-card-null">
                        <div class="clearfix">
                            <span>我的定时广播</span>
                            <el-divider></el-divider>
                        </div>
                        <div class="main-text">
                            <span>您暂无定时广播任务</span>
                        </div>
                    </el-card>
                </el-col>
            </el-row>
        </el-main>
    </el-container>
    <!-- 我的定时广播-有数据 -->
    <el-container v-else>
        <el-main>
            <el-row>
                <el-col :span="24">
                    <el-card class="main-card">
                        <div class="clearfix">
                <span>
                  <Icon class="icon-card" name="el-icon-Clock" color="#909399" size="16"/>
                   我的定时广播
                </span>
                            <el-divider></el-divider>
                        </div>
                        <!-- 表格 -->
                        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
                        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
                        <Table ref="tableRef"></Table>
                    </el-card>
                </el-col>
            </el-row>
        </el-main>
    </el-container>
    <!-- 表单 -->
    <PopupForm />

</template>

<script setup lang="ts">
import {ref, provide, onMounted, watchEffect} from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import { computed } from 'vue'
import {TableColumnCtx} from "element-plus";
import { ElNotification } from 'element-plus'

defineOptions({
    name: 'broadcasting/time',
})

const { t } = useI18n()
const tableRef = ref()
let optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

// 自定义一个新的按钮
let startBroadcastingButton: OptButton[] = [
    {
        // 渲染方式:tipButton=带tip的按钮,confirmButton=带确认框的按钮,moveButton=移动按钮
        render: 'tipButton',
        // 按钮名称
        name: 'info',
        // 鼠标放置时的 title 提示
        title: 'broadcasting.time.start_broadcasting',
        // 直接在按钮内显示的文字，title 有值时可为空
        text: '开始广播',
        // 按钮类型，请参考 element plus 的按钮类型
        type: 'primary',
        // 按钮 icon
        icon: '',
        class: 'table-row-info',
        // tipButton 禁用 tip
        disabledTip: false,
        // 自定义点击事件
        click: (row: TableRow, field: TableColumn) => {
            console.log()

        },
        // 按钮是否显示，请返回布尔值
        display: (row: TableRow, field: TableColumn) => {
            return true
        },
        // 按钮是否禁用，请返回布尔值
        disabled: (row: TableRow, field: TableColumn) => {
            if (row.remaining_datetime === '已到广播时间') {
                return false
            }else {
                return true
            }
        },
        // 自定义el-button属性
        attr: {}
    },
]

// 新按钮合入到默认的按钮数组
optButtons = startBroadcastingButton.concat(optButtons)

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/broadcasting.Time/'),
    {
        pk: 'task_id',
        column: [
            { type: 'index', align: 'center', operator: false },
            { label: t('broadcasting.time.task_name'), prop: 'task_name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('broadcasting.time.start_datetime'), prop: 'start_datetime', align: 'center', operator: 'eq', sortable: 'custom' },
            { label: t('broadcasting.time.remaining_datetime'),  render: 'tags' ,prop: 'remaining_datetime', align: 'center', operator: 'eq', sortable: 'custom',
                renderFormatter: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => {
                    // 创建remainingTime作为响应式数据
                    const remainingTime = ref(Date.parse(row.start_datetime)-Date.now());

                    // 定义更新remainingTime的函数
                    const updateTime = () => {
                        remainingTime.value = Date.parse(row.start_datetime)-Date.now();
                    };

                    // 监听props的变化，每秒钟更新一次remainingTime
                    watchEffect(() => {
                        updateTime();
                        const timer = setInterval(() => {
                            updateTime();
                        }, 1000);
                        return () => {
                            clearInterval(timer);
                        };
                    });

                    //利用computed函数计算倒计时文本
                    const remaining_datetime = computed(() => {
                        const days = Math.floor((remainingTime.value / 1000 / 60 / 60 / 24) % 365);
                        const hours = Math.floor((remainingTime.value / 1000 / 60 / 60) % 24);
                        const minutes = Math.floor((remainingTime.value / 1000 / 60) % 60);
                        const seconds = Math.floor((remainingTime.value / 1000) % 60);
                        return `${formatTimeText(days)}天:${formatTimeText(hours)}时:${formatTimeText(minutes)}分:${formatTimeText(seconds)}秒`;
                    });
                    // 监听remainingTime的变化，更新remaining_datetime的值
                    watchEffect(() => {
                        if (remainingTime.value <= 0) {
                            row.remaining_datetime = '已到广播时间';
                        } else {
                            row.remaining_datetime = String(remaining_datetime.value);
                            // console.log('row.remaining_datetime : ' + row.remaining_datetime)
                            const days = Math.floor((remainingTime.value / 1000 / 60 / 60 / 24) % 365);
                            const hours = Math.floor((remainingTime.value / 1000 / 60 / 60) % 24);
                            const minutes = Math.floor((remainingTime.value / 1000 / 60) % 60);
                            const seconds = Math.floor((remainingTime.value / 1000) % 60);
                            row.reminder_time.forEach((item, index) => {
                                if(item === 'opt0' && days === 0 && hours === 0 && minutes === 5 && seconds === 0) {
                                    ElNotification({
                                        title: '广播提醒',
                                        message: '您的定时广播任务' + row.task_name + ':将于5分钟后开始！请及时进行广播',
                                        type: 'info',
                                    })
                                }else if (item === 'opt1' && days === 0 && hours === 0 && minutes === 15 && seconds === 0) {
                                    ElNotification({
                                        title: '广播提醒',
                                        message: '您的定时广播任务' + row.task_name + ':将于15分钟后开始！请及时进行广播',
                                        type: 'info',
                                    })
                                }else if (item === 'opt2' && days === 0 && hours === 0 && minutes === 30 && seconds === 0) {
                                    ElNotification({
                                        title: '广播提醒',
                                        message: '您的定时广播任务' + row.task_name + ':将于30分钟后开始！请及时进行广播',
                                        type: 'info',
                                    })
                                }else if (item === 'opt3' && days === 0 && hours === 1 && minutes === 0 && seconds === 0) {
                                    ElNotification({
                                        title: '广播提醒',
                                        message: '您的定时广播任务' + row.task_name + ':将于1小时后开始！请及时进行广播',
                                        type: 'info',
                                    })
                                }else if (item === 'opt4' && days === 0 && hours === 2 && minutes === 0 && seconds === 0) {
                                    ElNotification({
                                        title: '广播提醒',
                                        message: '您的定时广播任务' + row.task_name + ':将于2小时后开始！请及时进行广播',
                                        type: 'info',
                                    })
                                }else if (item === 'opt5' && days === 1 && hours === 0 && minutes === 0 && seconds === 0) {
                                    ElNotification({
                                        title: '广播提醒',
                                        message: '您的定时广播任务' + row.task_name + ':将于1天后开始！请及时进行广播',
                                        type: 'info',
                                    })
                                }else if (item === 'opt6' && days === 2 && hours === 0 && minutes === 0 && seconds === 0) {
                                    ElNotification({
                                        title: '广播提醒',
                                        message: '您的定时广播任务' + row.task_name + ':将于2天后开始！请及时进行广播',
                                        type: 'info',
                                    })
                                }
                            })
                        }
                    });
                    return row.remaining_datetime;
                }},
            // { label: t('broadcasting.time.reminder_time'), prop: 'reminder_time', align: 'center', render: 'tags', operator: 'FIND_IN_SET', sortable: false, replaceValue: { opt0: '提前5分钟提醒', opt1: '提前15分钟提醒', opt2: '提前30分钟提醒', opt3: '提前1小时提醒', opt4: '提前2小时提醒', opt5: '提前1天提醒', opt6: '提前2天提醒' } },
            // { label: t('broadcasting.time.reminder_method'), prop: 'reminder_method', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { opt0: '站内提醒' } },
            { label: t('broadcasting.time.remark'), prop: 'remark', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            // { label: t('broadcasting.time.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            // { label: t('broadcasting.time.update_time'), prop: 'update_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('Operate'), align: 'center', render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'task_id', order: 'desc' },
    },
    {
        defaultItems: { start_date: null, start_datetime: null, start_time: null, reminder_time: ['opt0', 'opt1'], reminder_method: 'opt0' },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})

const onAction = (event: string, data: anyObj = {}) => {
    baTable.onTableHeaderAction(event, data)
    baTable.getIndex()
}

// 定义用于格式化时间的函数
const formatTimeText = (time: number): string => {
    return time < 10 ? `0${time}` : `${time}`;
};

const open3 = () => {

}

</script>

<style>
.header-text {
    font-size: 14px;
    margin-bottom: 20px;
    text-align: center;
}
.header-button {
    padding: 20px 0;
    text-align: center;
}
.main-text {
    padding: 150px 0;
    margin-bottom: 20px;
    text-align: center;
}
.main-card-null {
    height: 500px;
}
.dialog-footer button:first-child {
    margin-right: 10px;
}
.icon-card {
    display: inline-block;
    margin-top: 3px;
}
</style>
