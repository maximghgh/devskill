<template>
  <div v-if="modelValue" class="dialog" style="z-index: 1">
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
                />

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
                        {{ fileName ? fileName : "Перетащите или выберите файл" }}
                    </p>
                    <p v-if="!fileName" class="dialog__dropzone_title">
                        (JPG, PNG PDF..., 5 MB максимальный размер файла)
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
import { ref, watch, nextTick } from "vue";
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

const fileInputRef = ref(null);

const fileName = ref("");
const isDraggingFile = ref(false);

function triggerFileSelect() {
  if (loading.value) return;
  fileInputRef.value?.click();
}

function handleFileChange(e) {
  const f = e.target.files?.[0] || null;
  form.value.file = f;
  fileName.value = f ? f.name : "";
}

function onDropFile(e) {
  if (loading.value) return;
  isDraggingFile.value = false;

  const file = e.dataTransfer?.files?.[0];
  if (!file) return;

  const dt = new DataTransfer();
  dt.items.add(file);
  fileInputRef.value.files = dt.files;

  // чтобы сработала та же логика, что при обычном выборе
  handleFileChange({ target: fileInputRef.value });
}


const form = ref({
  title: "",
  file: null,
});

let editor = null;

function reset() {
  form.value = { title: "", file: null };
  error.value = "";
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
  form.value.file = e.target.files?.[0] || null;
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
    const contentPayload = editor ? await editor.save() : { blocks: [] };

    const fd = new FormData();
    fd.append("title", form.value.title);
    fd.append("type", "text"); // ← скрыто, потому что бэк требует
    fd.append("content", JSON.stringify(contentPayload));
    fd.append("points", "0"); // ← чтобы не ловить undefined points на бэке

    if (form.value.file) fd.append("file", form.value.file);

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
</style>
