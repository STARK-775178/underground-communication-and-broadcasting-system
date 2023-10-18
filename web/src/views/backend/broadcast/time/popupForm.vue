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
<!--                    <FormItem :label="t('broadcasting.time.task_id')" type="string" v-model="baTable.form.items!.task_id" prop="task_id" :placeholder="t('Please input field', { field: t('broadcasting.time.task_id') })" />-->
                    <FormItem :label="t('broadcasting.time.task_name')" type="string" v-model="baTable.form.items!.task_name" prop="task_name" :placeholder="t('Please input field', { field: t('broadcasting.time.task_name') })" />
                    <FormItem :label="t('broadcasting.time.start_datetime')" type="datetime" v-model="baTable.form.items!.start_datetime" prop="start_datetime" :placeholder="t('Please select field', { field: t('broadcasting.time.start_datetime') })" />
                    <FormItem :label="t('broadcasting.time.reminder_time')" type="checkbox" v-model="baTable.form.items!.reminder_time" prop="reminder_time" :data="{ content: { opt0: '提前5分钟提醒', opt1: '提前15分钟提醒', opt2: '提前30分钟提醒', opt3: '提前1小时提醒', opt4: '提前2小时提醒', opt5: '提前1天提醒', opt6: '提前2天提醒' } }" :placeholder="t('Please select field', { field: t('broadcasting.time.reminder_time') })" />
                    <FormItem :label="t('broadcasting.time.reminder_method')" type="select" v-model="baTable.form.items!.reminder_method" prop="reminder_method" :data="{ content: { opt0: '站内提醒' } }" :placeholder="t('Please select field', { field: t('broadcasting.time.reminder_method') })" />
                    <FormItem :label="t('broadcasting.time.remark')" type="textarea" v-model="baTable.form.items!.remark" prop="remark" :placeholder="t('Please input field', { field: t('broadcasting.time.remark') })" />
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

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    start_datetime: [buildValidatorData({ name: 'date', title: t('broadcasting.time.start_datetime') })],
    create_time: [buildValidatorData({ name: 'date', title: t('broadcasting.time.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('broadcasting.time.update_time') })],
})
</script>

<style scoped lang="scss"></style>
