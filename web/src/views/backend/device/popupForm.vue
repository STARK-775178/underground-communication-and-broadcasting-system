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
                    <FormItem :label="t('device.user_name')" type="string" v-model="baTable.form.items!.user_name" prop="user_name" :placeholder="t('Please input field', { field: t('device.user_name') })" />
                    <FormItem :label="t('device.password')" type="string" v-model="baTable.form.items!.password" prop="password" :placeholder="t('Please input field', { field: t('device.password') })" />
                    <FormItem :label="t('device.adress_ip')" type="string" v-model="baTable.form.items!.adress_ip" prop="adress_ip" :placeholder="t('Please input field', { field: t('device.adress_ip') })" />
                    <FormItem :label="t('device.port')" type="number" prop="port" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.port" :placeholder="t('Please input field', { field: t('device.port') })" />
                    <FormItem :label="t('device.phone')" type="string" v-model="baTable.form.items!.phone" prop="phone" :placeholder="t('Please input field', { field: t('device.phone') })" />
                    <FormItem :label="t('device.device_name')" type="string" v-model="baTable.form.items!.device_name" prop="device_name" :placeholder="t('Please input field', { field: t('device.device_name') })" />
                    <FormItem :label="t('device.work_area')" type="string" v-model="baTable.form.items!.work_area" prop="work_area" :placeholder="t('Please input field', { field: t('device.work_area') })" />
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
    port: [buildValidatorData({ name: 'number', title: t('device.port') })],
})
</script>

<style scoped lang="scss"></style>
