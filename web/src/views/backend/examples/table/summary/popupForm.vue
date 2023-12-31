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
                    <FormItem
                        :label="t('examples.table.summary.number1')"
                        type="number"
                        prop="number1"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.number1"
                        :placeholder="t('Please input field', { field: t('examples.table.summary.number1') })"
                    />
                    <FormItem
                        :label="t('examples.table.summary.number2')"
                        type="number"
                        prop="number2"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.number2"
                        :placeholder="t('Please input field', { field: t('examples.table.summary.number2') })"
                    />
                    <FormItem
                        :label="t('examples.table.summary.float1')"
                        type="number"
                        prop="float1"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.float1"
                        :placeholder="t('Please input field', { field: t('examples.table.summary.float1') })"
                    />
                    <FormItem
                        :label="t('examples.table.summary.float2')"
                        type="number"
                        prop="float2"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.float2"
                        :placeholder="t('Please input field', { field: t('examples.table.summary.float2') })"
                    />
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
    number1: [buildValidatorData({ name: 'number', title: t('examples.table.summary.number1') })],
    number2: [buildValidatorData({ name: 'number', title: t('examples.table.summary.number2') })],
    float1: [buildValidatorData({ name: 'float', title: t('examples.table.summary.float1') })],
    float2: [buildValidatorData({ name: 'float', title: t('examples.table.summary.float2') })],
    create_time: [buildValidatorData({ name: 'date', title: t('examples.table.summary.create_time') })],
})
</script>

<style scoped lang="scss"></style>
