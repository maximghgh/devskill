<template>
  <div v-if="modelValue" class="dialog" @click.self="close" style="z-index: 1">
    <div class="dialog__container_custom dialog__container_custom--s" @click.stop>
      <div class="dialog__inner" :class="{ 'is-saving': loading }">
        <div class="dialog__header">
          <p>Добавить урок</p>
          <div class="dialog__close" @click="close">✕</div>
        </div>

        <div class="dialog__content">
          <div class="form__admin">
            <!-- Название -->
            <div class="dialog__component">
              <p class="dialog__title">Название урока</p>
              <input
                v-model="form.title"
                class="dialog__input"
                :disabled="loading"
                placeholder="Введите название"
              />
            </div>

            <!-- Описание (EditorJS) -->
            <div class="dialog__component">
              <p class="dialog__title">Описание</p>
              <div id="chapter-editor" class="editor-container"></div>
            </div>

            <!-- Файл -->
            <div class="dialog__component">
                <div class="dialog__title_container">
                    <p class="dialog__title">Файл (необязательно)</p>
                </div>

                <!-- hidden input -->
                <input
                    ref="fileInputRef"
                    type="file"
                    accept=".pdf,.ppt,.pptx,.pps,.ppsx,.pptm,.doc,.docx,video/*,image/*"
                    class="dialog__file-input"
                    @change="handleFileChange"
                    :disabled="loading"
                    multiple
                />

                <div v-if="selectedFiles.length" class="dialog__file-list">
                  <p class="dialog__file-list-title">Выбранные файлы</p>
                  <div class="dialog__file-list-items">
                    <div
                      v-for="(file, idx) in selectedFiles"
                      :key="fileKey(file, idx)"
                      class="dialog__file-item"
                    >
                      <div class="dialog__file-card">
                        <img
                          class="dialog__file-icon"
                          :src="getChapterFileIcon(file)"
                          :alt="getChapterFileLabel(file)"
                        />
                        <span
                          class="dialog__file-name"
                          :title="getChapterFileName(file)"
                        >
                          {{ getChapterFileName(file) }}
                        </span>
                      </div>
                      <button
                        type="button"
                        class="dialog__file-remove"
                        :disabled="loading"
                        aria-label="Удалить файл"
                        @click="removeSelectedFile(idx)"
                      >
                        ✕
                      </button>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="dialog__file-add"
                    :disabled="loading"
                    @click="triggerFileSelect"
                  >
                    Добавить ещё
                  </button>
                </div>

                <!-- красивое поле -->
                <div
                    class="dialog__dropzone"
                    :class="{ 'is-dragging': isDraggingFile }"
                    @click="triggerFileSelect"
                    @dragenter.prevent="isDraggingFile = true"
                    @dragover.prevent="isDraggingFile = true"
                    @dragleave.prevent="isDraggingFile = false"
                    @drop.prevent="onDropFile"
                >
                    <p class="dialog__dropzone_title">
                        {{
                          selectedFileLabel
                            ? selectedFileLabel
                            : "Перетащите или выберите файлы"
                        }}
                    </p>
                    <p
                      v-if="!selectedFileLabel && !fileError"
                      class="dialog__dropzone_title dialog__dropzone_hint"
                    >
                        (JPG/PNG, PDF/PPT/PPTX, DOC/DOCX, Видео — до 20 МБ)
                    </p>
                    <p v-if="fileError" class="dialog__dropzone_error">
                      {{ fileError }}
                    </p>
                </div>
            </div>


            <p v-if="error" class="dialog__error">{{ error }}</p>

            <div class="dialog__btns">
              <button
                class="main__btn main__btn--white"
                type="button"
                @click="close"
                :disabled="loading"
              >
                Отмена
              </button>

              <button
                class="main__btn"
                type="button"
                @click="submit"
                :disabled="loading || !form.title.trim()"
              >
                {{ loading ? "Сохранение..." : "Сохранить" }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, computed } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";
import { globalNotification } from "@/globalNotification";
import {
  getChapterFileIcon,
  getChapterFileLabel,
  getChapterFileName,
} from "@/utils/chapterFiles";

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  topicId: { type: [String, Number], required: true },
});

const emit = defineEmits(["update:modelValue", "saved"]);

const loading = ref(false);
const error = ref("");
const fileError = ref("");

const fileInputRef = ref(null);

const isDraggingFile = ref(false);
const selectedFiles = computed(() => form.value.files || []);
const selectedFileNames = computed(() =>
  selectedFiles.value.map((file) => file.name)
);
const selectedFileLabel = computed(() => {
  return selectedFileNames.value.join(", ");
});

function triggerFileSelect() {
  if (loading.value) return;
  fileInputRef.value?.click();
}

function fileKey(file) {
  return `${file.name}-${file.size}-${file.lastModified}`;
}

function mergeFiles(existing, incoming) {
  const map = new Map();
  existing.forEach((file) => map.set(fileKey(file), file));
  incoming.forEach((file) => map.set(fileKey(file), file));
  return Array.from(map.values());
}

function syncFileInput(files) {
  if (!fileInputRef.value) return;
  if (!files.length) {
    fileInputRef.value.value = "";
    return;
  }
  const dt = new DataTransfer();
  files.forEach((file) => dt.items.add(file));
  fileInputRef.value.files = dt.files;
}

function setFiles(files) {
  form.value.files = files;
  syncFileInput(files);
}

function removeSelectedFile(index) {
  const next = form.value.files.filter((_, i) => i !== index);
  setFiles(next);
}

function handleFileChange(e) {
  const incoming = Array.from(e.target.files || []);
  if (!incoming.length) return;
  const validated = validateIncomingFiles(incoming);
  const merged = mergeFiles(form.value.files, validated);
  setFiles(merged);
}

function onDropFile(e) {
  if (loading.value) return;
  isDraggingFile.value = false;

  const incoming = Array.from(e.dataTransfer?.files || []);
  if (!incoming.length) return;
  const validated = validateIncomingFiles(incoming);
  const merged = mergeFiles(form.value.files, validated);
  setFiles(merged);
}


const form = ref({
  title: "",
  files: [],
});

const MAX_FILE_SIZE = 20 * 1024 * 1024;
const ALLOWED_MIMES = new Set([
  "application/pdf",
  "application/vnd.ms-powerpoint",
  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
  "application/vnd.ms-powerpoint.presentation.macroenabled.12",
  "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
  "application/vnd.openxmlformats-officedocument.presentationml.slideshow.main+xml",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
]);

function isAllowedFile(file) {
  if (!file) return false;
  const type = file.type || "";
  const name = String(file.name || "").toLowerCase();
  if (type.startsWith("image/") || type.startsWith("video/")) return true;
  if (ALLOWED_MIMES.has(type)) return true;
  return (
    name.endsWith(".pdf") ||
    name.endsWith(".ppt") ||
    name.endsWith(".pptx") ||
    name.endsWith(".pps") ||
    name.endsWith(".ppsx") ||
    name.endsWith(".pptm") ||
    name.endsWith(".doc") ||
    name.endsWith(".docx")
  );
}

function validateIncomingFiles(incoming) {
  const ok = [];
  const errors = [];

  incoming.forEach((file) => {
    if (file.size > MAX_FILE_SIZE) {
      errors.push(`Файл "${file.name}" больше 20 МБ`);
      return;
    }
    if (!isAllowedFile(file)) {
      errors.push(`Файл "${file.name}" имеет неподдерживаемый тип`);
      return;
    }
    ok.push(file);
  });

  fileError.value = errors.join(". ");
  if (!errors.length) fileError.value = "";
  return ok;
}

let editor = null;

function reset() {
  form.value = { title: "", files: [] };
  error.value = "";
  fileError.value = "";
  if (fileInputRef.value) fileInputRef.value.value = "";
}

function destroyEditor() {
  if (editor) {
    editor.destroy();
    editor = null;
  }
}

function initEditor(initialData = null) {
  destroyEditor();

  editor = new EditorJS({
    holder: "chapter-editor",
    placeholder: "Введите описание урока…",
    data: initialData || { blocks: [] },
    tools: {
      header: { class: Header, inlineToolbar: ["link"] },
      list: { class: List, inlineToolbar: true },
      image: {
        class: ImageTool,
        config: {
          endpoints: {
            byFile: "/api/uploadFile",
            byUrl: "/api/fetchUrl",
          },
        },
      },
    },
  });
}

function close() {
  destroyEditor();
  reset();
  emit("update:modelValue", false);
}

function onFile(e) {
  const incoming = Array.from(e.target.files || []);
  if (!incoming.length) return;
  const merged = mergeFiles(form.value.files, incoming);
  setFiles(merged);
}

/**
 * Важно:
 * - на бэке type REQUIRED, поэтому отправляем type="text" без UI
 * - content отправляем как JSON из EditorJS
 */
async function submit() {
  error.value = "";
  loading.value = true;

  try {
    const title = (form.value.title || "").trim();
    if (!title) {
      error.value = "Введите название урока";
      loading.value = false;
      return;
    }
    if (fileError.value) {
      error.value = fileError.value;
      loading.value = false;
      return;
    }
    const contentPayload = editor ? await editor.save() : { blocks: [] };

    const fd = new FormData();
    fd.append("title", title);
    fd.append("type", "text"); // ← скрыто, потому что бэк требует
    fd.append("content", JSON.stringify(contentPayload));
    fd.append("points", "0"); // ← чтобы не ловить undefined points на бэке

    form.value.files.forEach((file) => fd.append("files[]", file));

    const { data } = await axios.post(`/admin/topic/${props.topicId}/chapters`, fd);

    globalNotification.categoryMessage = "Урок добавлен";
    globalNotification.type = "success";

    emit("saved", data.chapter || data);
    close();
  } catch (e) {
    console.error(e);
    error.value = "Ошибка создания урока";
    globalNotification.categoryMessage = "Ошибка создания урока";
    globalNotification.type = "error";
  } finally {
    loading.value = false;
  }
}

/**
 * Когда модалка открылась — нужно смонтировать EditorJS,
 * когда закрылась — уничтожить.
 */
watch(
  () => props.modelValue,
  async (open) => {
    if (open) {
      await nextTick();
      initEditor();
    } else {
      destroyEditor();
    }
  }
);
</script>

<style scoped>
:deep .ce-toolbar__actions{
    left: -35px !important;
}
.dialog__error {
  color: #d40000;
  font-size: 13px;
  margin-top: 8px;
}

.dialog__dropzone_hint {
  font-size: 13px;
  color: #9e9e9e;
}

.dialog__dropzone_error {
  margin-top: 6px;
  font-size: 13px;
  color: #d40000;
  text-align: center;
}

.dialog__dropzone_title {
  overflow-wrap: anywhere;
}

.dialog__close {
  cursor: pointer;
}

.editor-container {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  min-height: 150px;
  background-color: #fff;
}

.dialog__file-list {
  margin: 10px 0 12px;
}

.dialog__file-list-title {
  font-size: 13px;
  color: #6d6d6d;
  margin-bottom: 6px;
}

.dialog__file-list-items {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 12px;
}

.dialog__file-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 14px;
  border: 1px solid #e1e1e1;
  border-radius: 14px;
  background: #fff;
  min-width: 0;
}

.dialog__file-card {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  flex: 1;
}

.dialog__file-icon {
  width: 46px;
  height: 46px;
  object-fit: contain;
  flex: 0 0 auto;
}

.dialog__file-name {
  font-size: 15px;
  font-weight: 600;
  color: #26314d;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0;
}

.dialog__file-remove {
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 999px;
  background: #f3f1ff;
  color: #6c57d9;
  font-size: 16px;
  line-height: 1;
  cursor: pointer;
  flex: 0 0 auto;
}

.dialog__file-remove:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

.dialog__file-remove:not(:disabled):hover {
  background: #e6e0ff;
}

.dialog__file-add {
  margin-top: 8px;
  border: none;
  background: none;
  color: #6c57d9;
  cursor: pointer;
  font-size: 13px;
  padding: 0;
  text-align: left;
}

.dialog__file-add:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}
</style>
