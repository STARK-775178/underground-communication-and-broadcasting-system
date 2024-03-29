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
                    <FormItem :label="t('broadcast.propaganda.voice_file_url')" type="file" v-model="baTable.form.items!.voice_file_url" prop="voice_file_url" />
                    <FormItem :label="t('broadcast.propaganda.voice_file_name')" type="string" v-model="baTable.form.items!.voice_file_name" prop="voice_file_name" :placeholder="t('Please input field', { field: t('broadcast.propaganda.voice_file_name') })" />
                    <FormItem :label="t('broadcast.propaganda.remark')" type="string" v-model="baTable.form.items!.remark" prop="remark" :placeholder="t('Please input field', { field: t('broadcast.propaganda.remark') })" />
<!--                    <FormItem :label="t('broadcast.propaganda.duration')" type="time" v-model="baTable.form.items!.duration" prop="duration" :placeholder="t('Please select field', { field: t('broadcast.propaganda.duration') })" />-->
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
import { reactive, ref, inject, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { FormInstance, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { Howl } from 'howler'; // 引入 Howl 对象

const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    update_time: [buildValidatorData({ name: 'date', title: t('broadcast.propaganda.update_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('broadcast.propaganda.create_time') })],
})

const musicDataUrl = ref(""); // 播放音乐时间获取用的url

// 音频播放时间换算
function transTime({duration}: { duration: any }) {
    const seconds = Math.floor(duration % 60);
    const minutes = Math.floor(duration / 60);
    const hours = Math.floor(duration / 60 / 60);
    const formattedSeconds = String(seconds).padStart(2, "0");
    const formattedMinutes = String(minutes).padStart(2, "0"); //padStart(2,"0") 使用0填充使字符串长度达到2
    const formattedHours = String(hours).padStart(2, "0");
    return `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
}

watch(
    () => baTable.form.items!.voice_file_url,
    () => {
        musicDataUrl.value = baTable.form.items!.voice_file_url
        // 使用 howler.js 加载音频文件并获取时长
        const sound = new Howl({
            src: [musicDataUrl.value],
            onload: function() {
              baTable.form.items!.duration = transTime({duration: sound.duration()}); // 换算成时间格式
            }
        });
    }
)

</script>

<style scoped lang="scss"></style>
