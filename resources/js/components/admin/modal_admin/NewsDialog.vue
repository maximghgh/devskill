<template>
    <div v-if="modelValue" class="dialog" @click.self="close" style="z-index: 9999">
        <div class="dialog__container_custom dialog__container_custom--s" @click.stop>
            <div class="dialog__inner" :class="{ 'is-saving': loading }">
                <div class="dialog__header">
                    <p>{{ isEdit ? "Редактировать новость" : "Создать новость" }}</p>

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
                            <p class="dialog__title">Название новости</p>
                            <input
                                v-model="form.title"
                                type="text"
                                class="dialog__input"
                                placeholder="Введите название"
                                :disabled="loading"
                            />
                        </div>

                        <div class="dialog__component">
                            <p class="dialog__title">Описание</p>
                            <textarea
                                v-model="form.content"
                                class="dialog__textarea dialog__textarea--s"
                                placeholder="Введите описание"
                                :disabled="loading"
                            ></textarea>
                        </div>

                        <div class="dialog__component">
                            <div class="dialog__title_container">
                                <p class="dialog__title">Картинка новости</p>
                            </div>

                            <input
                                ref="imageInputRef"
                                type="file"
                                accept="image/*"
                                class="dialog__file-input"
                                @change="handleImageChange"
                                :disabled="loading"
                            />

                            <div
                                class="dialog__dropzone"
                                :class="{ 'is-dragging': isDraggingImage }"
                                @click="triggerImageSelect"
                                @dragenter.prevent="isDraggingImage = true"
                                @dragover.prevent="isDraggingImage = true"
                                @dragleave.prevent="isDraggingImage = false"
                                @drop.prevent="onDropImage"
                            >
                                <p class="dialog__dropzone_title">
                                    {{
                                        imageFileName
                                        ? imageFileName
                                        : currentImageName
                                            ? `Заменить файл: ${currentImageName}`
                                            : "Перетащите или выберите файл"
                                    }}
                                </p>

                                <p v-if="!imageFileName" class="dialog__dropzone_title dialog__dropzone_hint">
                                (JPG или PNG, 5 МБ максимальный размер файла)
</p>
                            </div>

                            <!-- <div v-if="isEdit && currentImageUrl" class="image_db" style="margin-top: 10px;">
                                <img
                                    v-if="currentImageIsImage"
                                    :src="currentImageUrl"
                                    alt="Изображение новости"
                                    class="image"
                                    style="max-width: 160px; display:block; margin-bottom: 8px;"
                                />
                                <a :href="currentImageUrl" target="_blank" rel="noopener">
                                    {{ currentImageName }}
                                </a>
                            </div> -->
                        </div>

                        <div class="dialog__component">
                            <p class="dialog__title">Текст новости</p>
                            <div ref="editorHolder" class="dialog__input editor-container"></div>
                        </div>

                        <p v-if="error" class="dialog__error">{{ error }}</p>

                        <div class="dialog__btns">
                            <button type="button" class="main__btn main__btn--white" @click="close" :disabled="loading">
                                Отмена
                            </button>
                            <button type="button" class="main__btn" @click="submit" :disabled="loading">
                                {{ loading ? "Сохраняем..." : (isEdit ? "Сохранить" : "Опубликовать") }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";
import { globalNotification } from "@/globalNotification";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    news: { type: Object, default: null },
});

const emit = defineEmits(["update:modelValue", "saved"]);

const isEdit = computed(() => !!props.news?.id);

const loading = ref(false);
const error = ref("");

const form = ref({
    id: null,
    title: "",
    content: "",
    newsImage: null,
    editorData: {},
});

function resetForm() {
    form.value = { id: null, title: "", content: "", newsImage: null, editorData: {} };
    error.value = "";
    imageFileName.value = "";
    isDraggingImage.value = false;
}

function close() {
    emit("update:modelValue", false);
}

const PUBLIC_STORAGE_PREFIX = "/storage/";

const currentImageUrl = computed(() => {
    const raw = props.news?.image || props.news?.image_path || props.news?.image_url || "";
    if (!raw) return "";
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.startsWith("/")) return raw;
    return PUBLIC_STORAGE_PREFIX + raw.replace(/^storage\//, "");
});

const currentImageName = computed(() => {
    const u = currentImageUrl.value || "";
    const name = u.split("/").pop() || "";
    try { return decodeURIComponent(name); } catch { return name; }
});

const currentImageIsImage = computed(() =>
    /\.(png|jpe?g|gif|webp|svg)$/i.test(currentImageUrl.value || "")
);

/* ====== dropzone картинки (как в курсе) ====== */
const imageInputRef = ref(null);
const imageFileName = ref("");
const isDraggingImage = ref(false);

function triggerImageSelect() {
    if (loading.value) return;
    imageInputRef.value?.click();
}

function onImageFileSelected(file) {
    form.value.newsImage = file || null;
    imageFileName.value = file ? file.name : "";
}

function handleImageChange(e) {
    const f = e.target.files?.[0] || null;
    onImageFileSelected(f);
}

function onDropImage(e) {
    if (loading.value) return;
    isDraggingImage.value = false;

    const file = e.dataTransfer?.files?.[0];
    if (!file) return;

    const dt = new DataTransfer();
    dt.items.add(file);
    imageInputRef.value.files = dt.files;

    onImageFileSelected(file);
}

const editorHolder = ref(null);
let editor = null;

function parseEditorData(raw) {
    if (!raw) return {};
    if (typeof raw === "object") return raw;
    try { return JSON.parse(raw); } catch { return {}; }
}

async function initEditor(initialData = {}) {
    if (!editorHolder.value) return;

    if (editor) {
        editor.destroy();
        editor = null;
    }

    editor = new EditorJS({
        holder: editorHolder.value,
        placeholder: "Введите текст новости...",
        data: initialData,
        tools: {
            header: { class: Header, inlineToolbar: ["link"] },
            list: { class: List, inlineToolbar: true },
            image: {
                class: ImageTool,
                config: {
                    endpoints: { byFile: "/api/uploadFile", byUrl: "/api/fetchUrl" },
                },
            },
        },
    });
}

function destroyEditor() {
    if (editor) {
        editor.destroy();
        editor = null;
    }
}

watch(
    [() => props.modelValue, () => props.news],
    async ([open]) => {
        if (!open) {
            resetForm();
            destroyEditor();
            return;
        }

        const n = props.news;

        form.value.id = n?.id ?? null;
        form.value.title = n?.title ?? "";
        form.value.content = n?.content ?? "";
        form.value.newsImage = null;
        form.value.editorData = parseEditorData(n?.editor_data);

        imageFileName.value = "";
        await nextTick();
        await initEditor(form.value.editorData);
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    destroyEditor();
});

async function submit() {
    error.value = "";
    loading.value = true;

    try {
        const editorData = editor ? await editor.save() : {};
        form.value.editorData = editorData;

        const fd = new FormData();
        fd.append("title", form.value.title);
        fd.append("content", form.value.content);
        fd.append("editor_data", JSON.stringify(form.value.editorData));

        if (form.value.newsImage) fd.append("image", form.value.newsImage);

        if (!isEdit.value) {
            await axios.post("/api/news", fd, { headers: { "Content-Type": "multipart/form-data" } });
            globalNotification.categoryMessage = "Новость успешно создана";
            globalNotification.type = "success";
        } else {
            fd.append("_method", "PATCH");
            await axios.post(`/api/news/${form.value.id}`, fd, { headers: { "Content-Type": "multipart/form-data" } });
            globalNotification.categoryMessage = "Новость обновлена";
            globalNotification.type = "success";
        }
        const res = await axios.get("/api/news");
        emit("saved", res.data);

        close();
    } catch (e) {
        console.error(e);
        error.value = isEdit.value ? "Ошибка обновления новости" : "Ошибка при создании новости";
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

.editor-container {
    padding: 12px 40px;
    max-width: unset;
    width: 100%;
    min-height: 220px;
    background: #fff;
}

.dialog__inner {
    gap: 30px;
}
</style>
