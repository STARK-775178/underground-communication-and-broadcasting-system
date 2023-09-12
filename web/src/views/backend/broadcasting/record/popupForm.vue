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
                    <FormItem :label="t('broadcasting.record.broadcast_type')" type="string" v-model="baTable.form.items!.broadcast_type" prop="broadcast_type" :placeholder="t('Please input field', { field: t('broadcasting.record.broadcast_type') })" />
                    <FormItem :label="t('broadcasting.record.caller')" type="string" v-model="baTable.form.items!.caller" prop="caller" :placeholder="t('Please input field', { field: t('broadcasting.record.caller') })" />
                    <FormItem :label="t('broadcasting.record.broadcast_duration')" type="string" v-model="baTable.form.items!.broadcast_duration" prop="broadcast_duration" :placeholder="t('Please input field', { field: t('broadcasting.record.broadcast_duration') })" />
                    <FormItem :label="t('broadcasting.record.broadcast_areas')" type="remoteSelects" v-model="baTable.form.items!.broadcast_areas" prop="broadcast_areas" :input-attr="{ pk: 'cbroad_area.id', field: 'area', 'remote-url': '/admin/device.Area/index' }" :placeholder="t('Please select field', { field: t('broadcasting.record.broadcast_areas') })" />
                    <FormItem :label="t('broadcasting.record.broadcast_datetime')" type="datetime" v-model="baTable.form.items!.broadcast_datetime" prop="broadcast_datetime" :placeholder="t('Please select field', { field: t('broadcasting.record.broadcast_datetime') })" />
                    <FormItem :label="t('broadcasting.record.broadcast_record')" type="file" v-model="baTable.form.items!.broadcast_record" prop="broadcast_record" />
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
    broadcast_datetime: [buildValidatorData({ name: 'date', title: t('broadcasting.record.broadcast_datetime') })],
})
</script>

<style scoped lang="scss"></style>
