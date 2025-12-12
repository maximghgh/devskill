<template>
  <div v-if="modelValue" class="dialog" style="z-index: 1">
    <div class="dialog__container_custom dialog__container_custom--s" @click.stop>
      <div class="dialog__inner" :class="{ 'is-saving': loading }">

        <!-- Заголовок как в EditTopicDialog -->
        <div class="dialog__header">
          <p>Редактировать урок</p>
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
            <!-- Название -->
            <div class="dialog__component">
              <p class="dialog__title">Название урока</p>
              <input
                v-model="form.title"
                type="text"
                class="dialog__input"
                placeholder="Введите название урока"
                :disabled="loading"
              />
            </div>

            <!-- EditorJS -->
            <div class="dialog__component">
              <p class="dialog__title">Описание</p>
              <div id="chapter-editor-edit" class="editor-container"></div>
            </div>

            <!-- Файл -->
            <div class="dialog__component">
                <div class="dialog__title_container">
                    <p class="dialog__title">Файл</p>
                </div>

                <!-- текущий прикреплённый -->
                <!-- <p class="dialog__file-current" v-if="currentFilePath">
                    Текущий файл:
                    <a
                    class="dialog__file-link"
                    :href="fileUrl"
                    target="_blank"
                    rel="noopener"
                    >
                    {{ currentFileName }}
                    </a>
                </p>
                <p class="dialog__file-current dialog__file-current--empty" v-else>
                    Файл не прикреплён
                </p> -->

                <!-- hidden input -->
                <input
                    ref="fileInputRef"
                    type="file"
                    accept=".pdf,.ppt,.pptx,video/*,image/*"
                    class="dialog__file-input"
                    :disabled="loading"
                    @change="handleFileChange"
                />

                <!-- dropzone -->
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
                        selectedFileName
                        ? selectedFileName
                        : currentFileName
                            ? `Заменить файл: ${currentFileName}`
                            : "Перетащите или выберите файл"
                    }}
                    </p>
                    <p v-if="!selectedFileName" class="dialog__dropzone_title dialog__dropzone_hint">
                    (PDF/PPT/PPTX/Видео/Картинки)
                    </p>
                </div>
            </div>

            <p v-if="error" class="dialog__error">{{ error }}</p>

            <div class="dialog__btns">
              <button type="button" class="main__btn main__btn--white" @click="close" :disabled="loading">
                Отмена
              </button>

              <button
                type="button"
                class="main__btn"
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
  chapter: { type: Object, default: null }, // выбранный урок
});

const emit = defineEmits(["update:modelValue", "saved"]);

const loading = ref(false);
const error = ref("");

/** form */
const form = ref({
  title: "",
  file: null, // новый файл (если выбрали)
});

/** file dropzone state */
const fileInputRef = ref(null);
const selectedFileName = ref("");
const isDraggingFile = ref(false);

/** текущий файл из chapter */
const currentFilePath = computed(() => {
  // чаще всего у тебя поле такое:
  // presentation_path
  // но оставим запасные варианты
  return (
    props.chapter?.presentation_path ||
    props.chapter?.file_path ||
    props.chapter?.file ||
    null
  );
});

const currentFileName = computed(() => {
  if (!currentFilePath.value) return "";
  const p = String(currentFilePath.value);
  return p.split("/").pop();
});

const fileUrl = computed(() => {
  if (!currentFilePath.value) return "#";
  const p = String(currentFilePath.value);

  // если уже полный URL
  if (p.startsWith("http://") || p.startsWith("https://")) return p;

  // иначе относительный путь
  return `/${p}`;
});

/** EditorJS */
let editor = null;

function destroyEditor() {
  if (editor) {
    editor.destroy();
    editor = null;
  }
}

function safeParseContent(content) {
  if (!content) return { blocks: [] };
  if (typeof content === "object") return content;
  if (typeof content === "string") {
    try {
      return JSON.parse(content);
    } catch {
      return { blocks: [] };
    }
  }
  return { blocks: [] };
}

function initEditor(initialData) {
  destroyEditor();

  editor = new EditorJS({
    holder: "chapter-editor-edit",
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

/** reset/close */
function reset() {
  form.value = { title: "", file: null };
  error.value = "";

  selectedFileName.value = "";
  isDraggingFile.value = false;

  if (fileInputRef.value) fileInputRef.value.value = "";
}

function close() {
  destroyEditor();
  reset();
  emit("update:modelValue", false);
}

/** file handlers */
function triggerFileSelect() {
  if (loading.value) return;
  fileInputRef.value?.click();
}

function handleFileChange(e) {
  const f = e?.target?.files?.[0] || null;
  form.value.file = f;
  selectedFileName.value = f ? f.name : "";
}

function onDropFile(e) {
  if (loading.value) return;
  isDraggingFile.value = false;

  const file = e.dataTransfer?.files?.[0];
  if (!file) return;

  const dt = new DataTransfer();
  dt.items.add(file);

  if (fileInputRef.value) {
    fileInputRef.value.files = dt.files;
    handleFileChange({ target: fileInputRef.value });
  }
}

/** submit */
async function submit() {
  if (!props.chapter?.id) return;

  error.value = "";
  loading.value = true;

  try {
    const contentPayload = editor ? await editor.save() : { blocks: [] };

    const fd = new FormData();
    fd.append("title", form.value.title);

    // бэк требует type + points (по твоему контроллеру)
    fd.append("type", props.chapter?.type || "text");
    fd.append("content", JSON.stringify(contentPayload));
    fd.append("points", "0");

    if (form.value.file) fd.append("file", form.value.file);

    // method spoofing для multipart
    fd.append("_method", "PUT");

    const { data } = await axios.post(
      `/api/admin/topic/${props.topicId}/chapters/${props.chapter.id}`,
      fd
    );

    const updated = data?.chapter ?? data;

    globalNotification.categoryMessage = "Урок обновлён";
    globalNotification.type = "success";

    emit("saved", updated);
    close();
  } catch (e) {
    console.error(e);
    error.value = "Ошибка обновления урока";
    globalNotification.categoryMessage = "Ошибка обновления урока";
    globalNotification.type = "error";
  } finally {
    loading.value = false;
  }
}

/** open watcher */
watch(
  () => props.modelValue,
  async (open) => {
    if (!open) {
      destroyEditor();
      return;
    }

    await nextTick();

    // заполнение формы
    form.value.title = props.chapter?.title ?? "";
    form.value.file = null;

    selectedFileName.value = "";
    isDraggingFile.value = false;

    if (fileInputRef.value) fileInputRef.value.value = "";

    // editor initial
    const parsed = safeParseContent(props.chapter?.content);
    initEditor(parsed);
  }
);
</script>


<style scoped>
.dialog__error { color: #d40000; font-size: 13px; margin-top: 8px; }
.editor-container {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  min-height: 150px;
  background-color: #fff;
}
</style>
