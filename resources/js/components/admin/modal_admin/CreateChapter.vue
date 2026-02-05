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
                    accept=".pdf,.ppt,.pptx,video/*,image/*"
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
                      <span class="dialog__file-name">{{ file.name }}</span>
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
                        (JPG/PNG, PDF/PPT/PPTX, Видео — до 20 МБ)
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
  const count = selectedFileNames.value.length;
  if (!count) return "";
  if (count === 1) return selectedFileNames.value[0];
  return `Выбрано файлов: ${count}`;
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
    name.endsWith(".pptx")
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
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.dialog__file-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 10px;
  border: 1px solid #e1e1e1;
  border-radius: 8px;
  background: #fff;
}

.dialog__file-name {
  font-size: 13px;
  color: #333;
  word-break: break-word;
}

.dialog__file-remove {
  border: none;
  background: transparent;
  color: #8d8d8d;
  font-size: 14px;
  cursor: pointer;
}

.dialog__file-remove:disabled {
  cursor: not-allowed;
  opacity: 0.5;
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
