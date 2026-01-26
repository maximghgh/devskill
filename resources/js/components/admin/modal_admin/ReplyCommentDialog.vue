<template>
    <teleport to="body">
        <div v-if="modelValue" class="dialog" @click.self="close" style="z-index: 9999">
            <div class="dialog__container_custom dialog__container_custom--s" @click.stop>
                <div class="dialog__inner" :class="{ 'is-saving': loading }">
                    <div class="dialog__header">
                        <p>Ответить на комментарий</p>

                        <div class="dialog__close" @click="close">
                            <svg width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M7.90994 6.00019L12.2099 1.71019C12.3982 1.52188 12.504 1.26649 12.504 1.00019C12.504 0.733884 12.3982 0.478489 12.2099 0.290185C12.0216 0.101882 11.7662 -0.00390625 11.4999 -0.00390625C11.2336 -0.00390625 10.9782 0.101882 10.7899 0.290185L6.49994 4.59019L2.20994 0.290185C2.02164 0.101882 1.76624 -0.00390625 1.49994 -0.00390625C1.23364 -0.00390625 0.978243 0.101882 0.789939 0.290185C0.601635 0.478489 0.495847 0.733884 0.495847 1.00019C0.495847 1.26649 0.601635 1.52188 0.789939 1.71019L5.08994 6.00019L0.789939 10.2902C0.696211 10.3831 0.621816 10.4937 0.571048 10.6156C0.520279 10.7375 0.494141 10.8682 0.494141 11.0002C0.494141 11.1322 0.520279 11.2629 0.571048 11.3848C0.621816 11.5066 0.696211 11.6172 0.789939 11.7102C0.882902 11.8039 0.993503 11.8783 1.11536 11.9291C1.23722 11.9798 1.36793 12.006 1.49994 12.006C1.63195 12.006 1.76266 11.9798 1.88452 11.9291C2.00638 11.8783 2.11698 11.8039 2.20994 11.7102L6.49994 7.41019L10.7899 11.7102C10.8829 11.8039 10.9935 11.8783 11.1154 11.9291C11.2372 11.9798 11.3679 12.006 11.4999 12.006C11.632 12.006 11.7627 11.9798 11.8845 11.9291C12.0064 11.8783 12.117 11.8039 12.2099 11.7102C12.3037 11.6172 12.3781 11.5066 12.4288 11.3848C12.4796 11.2629 12.5057 11.1322 12.5057 11.0002C12.5057 10.8682 12.4796 10.7375 12.4288 10.6156C12.3781 10.4937 12.3037 10.3831 12.2099 10.2902L7.90994 6.00019Z"
                                    fill="#999999"
                                />
                            </svg>
                        </div>
                    </div>

                    <div class="dialog__content">
                        <div class="form__admin">
                            <div class="dialog__component">
                                <p class="dialog__title">Текст комментария</p>
                                <textarea
                                    :value="commentText"
                                    class="dialog__textarea dialog__textarea--s"
                                    disabled
                                />
                            </div>

                            <div class="dialog__component">
                                <p class="dialog__title">Текст ответа</p>
                                <textarea
                                    v-model="form.body"
                                    class="dialog__textarea dialog__textarea--s"
                                    placeholder="Введите текст"
                                    :disabled="loading"
                                />
                            </div>

                            <div class="dialog__component">
                                <div class="dialog__title_container">
                                    <p class="dialog__title">Вложение</p>
                                </div>

                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    accept="image/*"
                                    class="dialog__file-input"
                                    @change="handleFileChange"
                                    :disabled="loading"
                                />

                                <div
                                    class="dialog__dropzone"
                                    :class="{ 'is-dragging': isDragging }"
                                    @click="triggerFileSelect"
                                    @dragenter.prevent="isDragging = true"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="onDropFile"
                                >
                                    <p class="dialog__dropzone_title">
                                        {{ fileName ? fileName : "Перетащите или выберите файл" }}
                                    </p>
                                    <p v-if="!fileName" class="dialog__dropzone_title">
                                        (JPG или PNG, 5 МБ максимальный размер файла)
                                    </p>
                                </div>
                            </div>

                            <p v-if="error" class="dialog__error">{{ error }}</p>

                            <div class="dialog__btns">
                                <button
                                    type="button"
                                    class="main__btn main__btn--white"
                                    @click="close"
                                    :disabled="loading"
                                >
                                    Отменить
                                </button>
                                <button
                                    type="button"
                                    class="main__btn"
                                    @click="submit"
                                    :disabled="loading"
                                >
                                    {{ loading ? "Отправляем..." : "Отправить" }}
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { ref, watch } from "vue";
import axios from "axios";
import { globalNotification } from "@/globalNotification";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    newsId: { type: [Number, String], default: null },
    parentId: { type: [Number, String], default: null },
    commentText: { type: String, default: "" },
});

const emit = defineEmits(["update:modelValue", "saved"]);

const loading = ref(false);
const error = ref("");

const form = ref({
    body: "",
    file: null,
});

function resetForm() {
    form.value = { body: "", file: null };
    error.value = "";
    fileName.value = "";
    isDragging.value = false;
    if (fileInputRef.value) fileInputRef.value.value = "";
}

function close() {
    emit("update:modelValue", false);
}

const fileInputRef = ref(null);
const fileName = ref("");
const isDragging = ref(false);

function triggerFileSelect() {
    if (loading.value) return;
    fileInputRef.value?.click();
}

function onFileSelected(file) {
    form.value.file = file || null;
    fileName.value = file ? file.name : "";
}

function handleFileChange(e) {
    const f = e.target.files?.[0] || null;
    onFileSelected(f);
}

function onDropFile(e) {
    if (loading.value) return;
    isDragging.value = false;

    const file = e.dataTransfer?.files?.[0];
    if (!file) return;

    const dt = new DataTransfer();
    dt.items.add(file);
    fileInputRef.value.files = dt.files;

    onFileSelected(file);
}

watch(
    () => props.modelValue,
    (open) => {
        if (!open) resetForm();
    },
    { immediate: true }
);

async function submit() {
    error.value = "";

    if (!props.newsId) {
        error.value = "Не выбрана новость";
        return;
    }
    if (!props.parentId) {
        error.value = "Не выбран комментарий";
        return;
    }

    const body = (form.value.body || "").trim();
    if (!body) {
        error.value = "Введите текст ответа";
        return;
    }

    const current = JSON.parse(localStorage.getItem("user") || "{}");
    if (!current?.id) {
        error.value = "Не удалось определить пользователя";
        return;
    }

    if (form.value.file) {
        const okType = /image\/(jpeg|png)/i.test(form.value.file.type);
        const okSize = form.value.file.size <= 5 * 1024 * 1024;
        if (!okType) {
            error.value = "Можно загрузить только JPG или PNG";
            return;
        }
        if (!okSize) {
            error.value = "Максимальный размер файла — 5 МБ";
            return;
        }
    }

    loading.value = true;
    try {
        const fd = new FormData();
        fd.append("body", body);
        fd.append("parent_id", props.parentId);
        fd.append("user_id", current.id);
        fd.append("user_name", current.name || "");
        if (form.value.file) fd.append("image", form.value.file);

        await axios.post(`/api/news/${props.newsId}/comments`, fd, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        globalNotification.categoryMessage = "Ответ отправлен";
        globalNotification.type = "success";

        emit("saved");
        close();
    } catch (e) {
        console.error(e);
        error.value = "Ошибка отправки ответа";
        globalNotification.categoryMessage = error.value;
        globalNotification.type = "error";
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.dialog__error {
    color: #d40000;
    font-size: 13px;
    margin-top: 8px;
}

.dialog__inner {
    gap: 30px;
}
</style>
