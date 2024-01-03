<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
<!--                    <FormItem :label="t('broadcast.tasks.head_id')" type="string" v-model="baTable.form.items!.head_id" prop="head_id" :placeholder="t('Please input field', { field: t('broadcast.tasks.head_id') })" />-->
                    <FormItem :label="t('broadcast.tasks.name')" type="string" v-model="baTable.form.items!.name" prop="name" :placeholder="t('Please input field', { field: t('broadcast.tasks.name') })" />
                    <FormItem :label="t('broadcast.tasks.execution_time')" type="datetime" v-model="baTable.form.items!.execution_time" prop="execution_time" :placeholder="t('Please select field', { field: t('broadcast.tasks.execution_time') })" />
                    <FormItem :label="t('broadcast.tasks.broadcast_area_ids')" type="remoteSelects" v-model="baTable.form.items!.broadcast_area_ids" prop="broadcast_area_ids" :input-attr="{ pk: 'cbroadcast_area.id', field: 'area', 'remote-url': '/admin/device.Area/index' }" :placeholder="t('Please select field', { field: t('broadcast.tasks.broadcast_area_ids') })" />
                    <FormItem :label="t('broadcast.tasks.broadcast_file')" type="string" v-model="baTable.form.items!.broadcast_file" prop="broadcast_file" />
                    <FormItem :label="t('broadcast.tasks.task_status')" type="string" v-model="baTable.form.items!.task_status" prop="task_status" :data="{ content: { NEW: t('broadcast.tasks.task_status NEW'), EXECUTING: t('broadcast.tasks.task_status EXECUTING'), ' FINISH': t('broadcast.tasks.task_status  FINISH') } }" :placeholder="t('Please input field', { field: t('broadcast.tasks.task_status') })" />
                    <FormItem :label="t('broadcast.tasks.source')" type="string" v-model="baTable.form.items!.source" prop="source" :data="{ content: { OTHER: t('broadcast.tasks.source OTHER'), ' THIS': t('broadcast.tasks.source  THIS') } }" :placeholder="t('Please input field', { field: t('broadcast.tasks.source') })" />
                    <FormItem :label="t('broadcast.tasks.updated_time')" type="datetime" v-model="baTable.form.items!.updated_time" prop="updated_time" :placeholder="t('Please select field', { field: t('broadcast.tasks.updated_time') })" />
                    <FormItem :label="t('broadcast.tasks.status')" type="number" v-model="baTable.form.items!.status" prop="status" :data="{ content: { '0': t('broadcast.tasks.status 0'), '1': t('broadcast.tasks.status 1') } }" :placeholder="t('Please input field', { field: t('broadcast.tasks.status') })" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm()">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { FormInstance, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'

const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

// const rules: Partial<Record<string, FormItemRule[]>> = reactive({
//     execution_time: [buildValidatorData({ name: 'number', title: t('broadcast.tasks.execution_time') })],
//     create_time: [buildValidatorData({ name: 'date', title: t('broadcast.tasks.create_time') })],
//     updated_time: [buildValidatorData({ name: 'number', title: t('broadcast.tasks.updated_time') })],
//     status: [buildValidatorData({ name: 'number', title: t('broadcast.tasks.status') })],
// })
</script>

<style scoped lang="scss"></style>
