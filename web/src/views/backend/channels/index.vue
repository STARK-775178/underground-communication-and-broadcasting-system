<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('channels.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"></Table>

        <!-- 表单 -->
        <PopupForm />
        <!-- 示例核心代码(1/3) -->
        <!-- 详情组件在此处使用，但显示与否的判断是写在组件内的 -->
        <Info />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { insertChannelApi } from '/@/api/backend/communication/channel'
import Info from './info.vue'
import { hangupExtensionsApi } from '/@/api/backend/communication/call'
defineOptions({
    name: 'channels',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons([])

/**
 * 示例核心代码(2/3)
 * 表格操作按钮组 optButtons 只是个普通的数组，此处向其 push 一个 OptButton
 */
optButtons.push({
    render: 'tipButton',
    // name 是任意的
    name: 'info',
    // title 是语言翻译 key
    title: '插播',
    text: '插播',
    type: 'success',
    icon: 'fa fa-search-plus icon',
    click(row, field) {
        console.info('%c-------插播按钮被点击了--------', 'color:blue')
        console.log('接受到行数据和列数据', row, field)

        // 在 extend 上自定义一个变量标记详情弹窗显示状态，详情组件内以此判断显示即可！


        // 您也可以使用 baTable.form.operate，默认情况它有三个值`Add、Edit、空字符串`，前两个值将显示添加和编辑弹窗

        // 您也可以再来个 loading 态，然后请求详情数据等
        baTable.table.extend!.infoLoading = true

// 获取呼叫方和目标分机的id并组合成数组
        let extensions = [row.callerId, row.extension]
        //组合post请求的数据
        let data = {
            "extensions": extensions
        }
        // 测试插播界面
        baTable.table.extend!.infoData = row
        baTable.table.extend!.showInfo = true
        baTable.table.extend!.infoLoading = false

        // // 发送插播请求
        // insertChannelApi(data).then((response) => {
        //     //判断是否插播成功
        //     if (response.data.code == 200) {
        //         //插播成功后，将插播的数据传给详情组件
        //         baTable.table.extend!.infoData = row
        //         //关闭弹窗
        //         baTable.table.extend!.showInfo = true
        //         //关闭等待提示
        //         baTable.table.extend!.infoLoading = false
        //     }
        //     //插播失败
        //     else {
        //         //关闭弹窗
        //         baTable.table.extend!.showInfo = false
        //         //清空弹窗数据
        //         baTable.table.extend!.infoData = null
        //         //提示插播失败
        //         baTable.table.extend!.infoLoading = false

        //     }
        //     console.log(response)
        // })



    },

})




optButtons.push({
    render: 'tipButton',
    // name 是任意的
    name: 'hangup',
    text: '结束通话',
    type: 'danger',
    icon: 'fa fa-search-plus icon',
    click(row) {
        // 获取呼叫方和目标分机的id并组合成数组
        let extensions = [row.callerId, row.extension]
        //组合post请求的数据
        let data = {
            "extensions": extensions
        }
        //发送结束通话请求
        hangupExtensionsApi(data).then((response) => {
            console.log(response)
        })




    },

})

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/communication.Channels/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            {
                label: t('channels.callerId'),
                prop: 'callerId',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            // { label: t('channels.create_data'), prop: 'create_data', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            {
                label: t('channels.duration'),
                prop: 'duration',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('channels.extension'),
                prop: 'extension',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            // { label: t('channels.id'), prop: 'id', align: 'center', width: 180, operator: 'RANGE', sortable: 'custom' },
            { label: t('Operate'), align: 'center', width: 200, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { create_data: null },
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
</script>

<style scoped lang="scss"></style>
