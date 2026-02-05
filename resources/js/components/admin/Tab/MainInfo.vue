<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch, nextTick } from "vue";
import axios from "axios";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";

import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";

import { globalNotification } from "@/globalNotification";

const props = defineProps({
  draft: { type: Object, default: () => ({ slug: null }) },
  allTags: { type: Array, default: () => [] }, // не используем, оставлено для совместимости
  isEdit: { type: Boolean, default: false },

  // эти props прилетают из оболочки модалки
  admin: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  hackathonSlug: { type: String, default: "" }, // чтобы не ругался Vue
});

const emit = defineEmits(["saved", "cancel", "dirty", "saving"]);

// =======================
// EditorJS data helpers (normalize list blocks)
// =======================
function parseEditorData(raw) {
  if (!raw) return { blocks: [] };
  if (typeof raw === "object") return raw;
  try {
    return JSON.parse(raw);
  } catch {
    return { blocks: [] };
  }
}

function normalizeListItems(items, style) {
  const listStyle = style === "checklist" ? "checklist" : "list";

  return (items || []).map((item) => {
    if (item == null) return { content: "", items: [] };

    if (typeof item === "string" || typeof item === "number") {
      return { content: String(item), items: [] };
    }

    if (typeof item === "object") {
      const content =
        item.content ??
        item.text ??
        (typeof item.value === "string" ? item.value : "");

      const rawNested = item.items;
      let nested = [];
      if (Array.isArray(rawNested)) {
        nested = normalizeListItems(rawNested, style);
      } else if (rawNested && typeof rawNested === "object") {
        nested = normalizeListItems(Object.values(rawNested), style);
      } else {
        nested = [];
      }

      if (listStyle === "checklist") {
        const checked =
          typeof item.checked === "boolean"
            ? item.checked
            : typeof item.meta?.checked === "boolean"
              ? item.meta.checked
              : false;
        return { content: String(content ?? ""), meta: { checked }, items: nested };
      }

      return { content: String(content ?? ""), items: nested };
    }

    return { content: String(item), items: [] };
  });
}

function normalizeEditorData(raw) {
  const parsed = parseEditorData(raw);
  const data = Array.isArray(parsed) ? { blocks: parsed } : parsed;
  const blocks = Array.isArray(data.blocks) ? data.blocks : [];

  const normalizedBlocks = blocks.map((block) => {
    if (!block || typeof block !== "object") return block;
    const rawType = typeof block.type === "string" ? block.type : "";
    const type = rawType.toLowerCase();
    if (type !== "list" && type !== "checklist") return block;

    let rawData = {};
    if (block.data && typeof block.data === "object" && !Array.isArray(block.data)) {
      rawData = block.data;
    } else if (Array.isArray(block.data)) {
      rawData = { items: block.data };
    } else if (typeof block.data === "string") {
      const trimmed = block.data.trim();
      if (trimmed) {
        try {
          const parsed = JSON.parse(trimmed);
          if (parsed && typeof parsed === "object" && !Array.isArray(parsed)) {
            rawData = parsed;
          } else {
            rawData = { items: parsed };
          }
        } catch {
          rawData = { items: [block.data] };
        }
      }
    }

    let style = rawData.style;
    if (style === "ol" || style === "ordered") style = "ordered";
    else if (style === "ul" || style === "unordered" || style === "bullet") style = "unordered";
    else if (style === "checklist" || style === "check") style = "checklist";
    else if (style !== "ordered" && style !== "unordered" && style !== "checklist") {
      style = type === "checklist" ? "checklist" : "unordered";
    }

    let itemsRaw = rawData.items;
    if (typeof itemsRaw === "string") {
      const trimmed = itemsRaw.trim();
      if (!trimmed) {
        itemsRaw = [];
      } else {
        try {
          itemsRaw = JSON.parse(trimmed);
        } catch {
          itemsRaw = [itemsRaw];
        }
      }
    } else if (itemsRaw && typeof itemsRaw === "object" && !Array.isArray(itemsRaw)) {
      itemsRaw = Object.values(itemsRaw);
    }
    if (!Array.isArray(itemsRaw)) itemsRaw = itemsRaw != null ? [itemsRaw] : [];

    const safeRawData = { ...rawData };
    if (Array.isArray(safeRawData.meta)) delete safeRawData.meta;
    const meta =
      safeRawData.meta && typeof safeRawData.meta === "object" && !Array.isArray(safeRawData.meta)
        ? safeRawData.meta
        : {};
    if (style === "ordered") {
      if (meta.start == null) meta.start = 1;
      if (meta.counterType == null) meta.counterType = "numeric";
    }

    const normalizedItems = normalizeListItems(itemsRaw, style);
    const hasNested = normalizedItems.some(
      (item) => Array.isArray(item.items) && item.items.length > 0
    );
    let safeItems = normalizedItems;
    if (!safeItems.length) {
      safeItems = [
        style === "checklist"
          ? { content: "", meta: { checked: false }, items: [] }
          : { content: "", items: [] },
      ];
    }
    if (style !== "checklist" && !hasNested) {
      safeItems = safeItems.map((item) =>
        typeof item === "string" ? item : String(item.content ?? "")
      );
    }

    return {
      ...block,
      type: "list",
      data: {
        ...safeRawData,
        style,
        meta,
        items: safeItems,
      },
    };
  });

  return { ...data, blocks: normalizedBlocks };
}

/** Локальная форма */
const form = ref({
  cardTitle: "",
  courseName: "",
  price: "",
  duration: "",
  description: "",
  hours: "",
  simulators: "",
  difficulty: "basic",
  selectedTeachers: [],
  selectedLanguages: [],
  selectedDirection: null,
  upgradeQualification: 0,
  cardImage: null,
  descriptionImage: null,
  pdfFile: null,
  editorData: {},
  startDate: "",
  endDate: "",
});

/** Состояние селекта преподавателя (у тебя один преподаватель) */
const teacherSelectModel = ref(null);
watch(teacherSelectModel, (val) => {
  form.value.selectedTeachers = val ? [val] : [];
});

/** Файлы (UI) */
const cardInputRef = ref(null);
const descInputRef = ref(null);

const cardFileName = ref("");
const descFileName = ref("");

const isDraggingCard = ref(false);
const isDraggingDesc = ref(false);

const isDisabled = computed(() => props.readonly || props.admin);

/** Trigger/Drop helpers */
function triggerCardSelect() {
  if (isDisabled.value) return;
  cardInputRef.value?.click();
}
function triggerDescSelect() {
  if (isDisabled.value) return;
  descInputRef.value?.click();
}

function onCardImage(e) {
  form.value.cardImage = e.target.files?.[0] || null;
}
function onDescriptionImage(e) {
  form.value.descriptionImage = e.target.files?.[0] || null;
}
function onPdf(e) {
  form.value.pdfFile = e.target.files?.[0] || null;
}

function handleCardChange(e) {
  const f = e.target.files?.[0] || null;
  onCardImage(e);
  cardFileName.value = f ? f.name : "";
}
function handleDescChange(e) {
  const f = e.target.files?.[0] || null;
  onDescriptionImage(e);
  descFileName.value = f ? f.name : "";
}

function onDropCard(e) {
  if (isDisabled.value) return;
  isDraggingCard.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (!file) return;

  const dt = new DataTransfer();
  dt.items.add(file);
  cardInputRef.value.files = dt.files;

  onCardImage({ target: cardInputRef.value });
  cardFileName.value = file.name;
}

function onDropDesc(e) {
  if (isDisabled.value) return;
  isDraggingDesc.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (!file) return;

  const dt = new DataTransfer();
  dt.items.add(file);
  descInputRef.value.files = dt.files;

  onDescriptionImage({ target: descInputRef.value });
  descFileName.value = file.name;
}

/** Источники для селектов */
const users = ref([]);
const directions = ref([]);
const languages = ref([]);
const currentUser = ref(null);

const teachers = computed(() =>
  users.value.filter((u) => String(u.role) === "2" || u.role === 2)
);
const isTeacher = computed(() => Number(currentUser.value?.role) === 2);
const teacherSelectDisabled = computed(() => isDisabled.value || isTeacher.value);

async function loadUsers() {
  const { data } = await axios.get("/api/users");
  users.value = data;
}
async function loadDirections() {
  const { data } = await axios.get("/api/directions");
  directions.value = data;
}
async function loadLanguages() {
  const { data } = await axios.get("/api/languages");
  languages.value = data;
}

/** EditorJS */
const editor = ref(null);
const editorHolder = ref(null);

function destroyEditor() {
  if (editor.value) {
    editor.value.destroy();
    editor.value = null;
  }
}

function initEditor() {
  if (!editorHolder.value) return;

  destroyEditor();

  const normalized = normalizeEditorData(form.value.editorData);
  form.value.editorData = normalized;

  editor.value = new EditorJS({
    holder: editorHolder.value,
    placeholder: "Добавьте контент для нового курса...",
    data: normalized,
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
    async onChange() {
      try {
        const data = await editor.value.save();
        form.value.editorData = data;
      } catch {
        // молча
      }
    },
  });
}

/** Dirty tracking (не ставим dirty при автозаполнении) */
const isDirty = ref(false);
const isHydrating = ref(false);

watch(
  form,
  () => {
    if (isHydrating.value) return;
    if (!isDirty.value) {
      isDirty.value = true;
      emit("dirty", true);
    }
  },
  { deep: true }
);

function fillFormFromDraft(draft = props.draft) {
  if (!props.isEdit || !draft) return;

  isHydrating.value = true;
  try {
    const c = draft;

    form.value.cardTitle = c.card_title ?? "22222";
    form.value.courseName = c.course_name ?? "";
    form.value.price = c.price ?? "";
    form.value.duration = c.duration ?? "";
    form.value.description = c.description ?? "";
    form.value.hours = c.hours ?? "";
    form.value.simulators = c.simulators ?? "";
    form.value.difficulty = c.difficulty ?? "basic";

    form.value.startDate = c.start_date ? String(c.start_date).slice(0, 10) : "";
    form.value.endDate = c.end_date ? String(c.end_date).slice(0, 10) : "";

    const teacherIds = Array.isArray(c.teachers)
      ? c.teachers.map((t) => (typeof t === "object" ? t.id : t))
      : [];
    form.value.selectedTeachers = teacherIds;
    teacherSelectModel.value = teacherIds[0] ?? null;

    form.value.selectedDirection = c.direction ?? null;

    let langIds = [];
    if (Array.isArray(c.language)) {
      if (c.language.length && typeof c.language[0] === "object") {
        langIds = c.language.map((l) => l.id);
      } else {
        langIds = c.language.map((id) => Number(id));
      }
    }
    form.value.selectedLanguages = languages.value.filter((l) => langIds.includes(l.id));

    form.value.upgradeQualification = c.upgradequalification ?? 0;

    form.value.editorData = c.editor_data || {};
  } finally {
    // важно: dirty не должен становиться true при автозаполнении
    isDirty.value = false;
    emit("dirty", false);
    isHydrating.value = false;
  }
}

/** Reset */
function resetForm() {
  isHydrating.value = true;

  form.value = {
    cardTitle: "",
    courseName: "",
    price: "",
    duration: "",
    description: "",
    hours: "",
    simulators: "",
    difficulty: "basic",
    selectedTeachers: [],
    selectedLanguages: [],
    selectedDirection: null,
    upgradeQualification: 0,
    cardImage: null,
    descriptionImage: null,
    pdfFile: null,
    editorData: {},
    startDate: "",
    endDate: "",
  };

  teacherSelectModel.value = null;
  cardFileName.value = "";
  descFileName.value = "";

  isDirty.value = false;
  emit("dirty", false);
  isHydrating.value = false;

  // очистка EditorJS безопасно
  if (editor.value?.render) {
    editor.value.render({ blocks: [] });
  }
}

function applyTeacherFromStorage() {
  const storedUser = localStorage.getItem("user");
  if (!storedUser) return;

  try {
    currentUser.value = JSON.parse(storedUser);
  } catch (e) {
    console.error("Ошибка при чтении пользователя из localStorage:", e);
    return;
  }

  if (props.isEdit) return;
  if (!isTeacher.value || !currentUser.value?.id) return;
  if (teacherSelectModel.value) return;

  isHydrating.value = true;
  teacherSelectModel.value = currentUser.value.id;
  form.value.selectedTeachers = [currentUser.value.id];
  isDirty.value = false;
  emit("dirty", false);
  isHydrating.value = false;
}

/** Cancel */
function cancel() {
  resetForm();
  emit("cancel");
}

/** Save */
async function save() {
  if (isDisabled.value) return;

  emit("saving", true);

  try {
    // 1) сохраняем данные из EditorJS
    if (editor.value) {
      const data = await editor.value.save();
      form.value.editorData = normalizeEditorData(data);
    }

    const ct = (form.value.cardTitle || form.value.courseName || "").trim();
    if (!ct) throw new Error("Заполните краткое название (cardTitle)");
    form.value.cardTitle = ct;

    // 2) FormData
    const fd = new FormData();
    fd.append("cardTitle", form.value.cardTitle);
    fd.append("courseName", form.value.courseName);
    fd.append("price", form.value.price);
    fd.append("duration", form.value.duration);
    fd.append("description", form.value.description);
    fd.append("hours", form.value.hours);

    if (form.value.simulators != null) fd.append("simulators", form.value.simulators);

    fd.append("difficulty", form.value.difficulty);
    fd.append("editorData", JSON.stringify(normalizeEditorData(form.value.editorData)));

    fd.append("teachers", JSON.stringify(form.value.selectedTeachers));

    const languageIds = form.value.selectedLanguages.map((l) => l.id);
    fd.append("language", JSON.stringify(languageIds));

    fd.append("direction", form.value.selectedDirection ?? "");

    fd.append("upgradequalification", String(form.value.upgradeQualification ?? 0));

    fd.append("start_date", form.value.startDate || "");
    fd.append("end_date", form.value.endDate || "");

    if (form.value.cardImage) fd.append("cardImage", form.value.cardImage);
    if (form.value.descriptionImage) fd.append("descriptionImage", form.value.descriptionImage);
    if (form.value.pdfFile) fd.append("pdf", form.value.pdfFile);

    let data;

    if (!props.isEdit) {
      // CREATE
      const resp = await axios.post("/api/courses", fd, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      data = resp.data;

      globalNotification.categoryMessage = "Основная информация курса сохранена";
      globalNotification.type = "success";
    } else {
      // EDIT
      const id =
        props.draft?.id ??
        props.draft?.courseId ??
        (Number.isFinite(+props.draft?.slug) ? +props.draft.slug : null);

      if (!id) throw new Error("Нет id курса для редактирования");

      const resp = await axios.post(`/api/courses/${id}`, fd, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      data = resp.data;

      globalNotification.categoryMessage = "Курс обновлён";
      globalNotification.type = "success";
    }

    // !!! ВАЖНО: course объявляем, чтобы не падало после успешного создания
    const course = data?.course ?? data;
    const courseId = course?.id ?? data?.id ?? null;

    // if (props.draft) {
    //     props.draft.courseId = courseId;
    //     props.draft.slug = courseId; // важно: НЕ id
    // }

    if (props.draft) {
      props.draft.courseId = courseId;
      props.draft.id = courseId; // ✅ важно
      // slug НЕ трогаем числом. Если бэк вернул slug — можно сохранить:
      if (course?.slug) props.draft.slug = course.slug;
    }

    isDirty.value = false;
    emit("dirty", false);

    // emit("saved", {
    //   slug: courseId,
    //   courseId,
    //   course,
    //   data,
    // });
    emit("saved", {
      id: courseId,
      courseId,
      slug: course?.slug ?? props.draft?.slug ?? null,
      course,
      data,
    });


  } catch (err) {
    console.error("Ошибка при сохранении курса:", err);

    const msg =
      err?.response?.data?.message ||
      err?.message ||
      "Ошибка при сохранении курса";

    globalNotification.categoryMessage = msg;
    globalNotification.type = "error";
  } finally {
    emit("saving", false);
  }
}

defineExpose({ save });

onMounted(async () => {
  await Promise.all([loadUsers(), loadDirections(), loadLanguages()]);
  applyTeacherFromStorage();

  if (props.isEdit && props.draft) {
    fillFormFromDraft(props.draft);
  }

  await nextTick();
  initEditor();
});

watch(
  () => props.draft,
  async (val) => {
    if (props.isEdit && val) {
      fillFormFromDraft(val);

      // обновим EditorJS начальными данными
      if (editor.value?.render) {
        editor.value.render(form.value.editorData || { blocks: [] });
      }
    }
  }
);

onBeforeUnmount(() => {
  destroyEditor();
});

// Экспорт хендлеров, которые используются в template
// (в <script setup> они и так доступны, но оставляю список для ясности)
</script>


<template>
    <div class="form__admin">
        <!-- 1) Полное название -->
        <div class="dialog__component">
            <p class="dialog__title">Полное название курса</p>
            <input
                v-model="form.courseName"
                type="text"
                class="dialog__input"
                placeholder="Введите название курса"
                :disabled="isDisabled"
            />
        </div>

        <!-- 2) Краткое название -->
        <div class="dialog__component">
            <p class="dialog__title">Цена</p>
            <input
                v-model="form.price"
                type="number"
                class="dialog__input" 
                placeholder="Введите цену"
                :disabled="isDisabled"
            />
        </div>

        <div class="dialog__component">
            <p class="dialog__title">Количество часов</p>
            <input
                v-model="form.hours"
                type="number"
                class="dialog__input"
                placeholder="Введите количество часов"
                :disabled="isDisabled"
            />
        </div>
        <!-- 3) Даты -->
        <div class="dialog__block">
            <div class="dialog__component">
                <p class="dialog__title">Даты проведения</p>
                <div class="date">
                    <div class="date_start">
                        <p class="dialog__title">От</p>
                        <div class="dialog__component medium">
                            <input
                                v-model="form.startDate"
                                type="date"
                                class="dialog__input dialog__input--m"
                                :disabled="isDisabled"
                            />
                        </div>
                    </div>
                    <div class="date_start">
                        <p class="dialog__title">До</p>
                        <div class="dialog__component">
                            <input
                                v-model="form.endDate"
                                type="date"
                                class="dialog__input dialog__input--m"
                                :disabled="isDisabled"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5) Описание -->
        <div class="dialog__component">
            <p class="dialog__title">Описание</p>
            <textarea
                v-model="form.description"
                class="dialog__textarea"
                placeholder="Введите описание"
                :disabled="isDisabled"
            />
        </div>

        <!-- simulators hidden -->
        <input v-model="form.simulators" type="hidden" />

        <!-- 6) Уровень -->
        <div class="dialog__component ">
            <p class="dialog__title">Уровень курса</p>
            <select
                v-model="form.difficulty"
                class="dialog__input dialog__select"
                :disabled="isDisabled"
            >
        <option value="basic">Базовый</option>
        <option value="middle">Фундаментальный</option>
        <option value="advanced">Олимпиадный</option>
      </select>
    </div>

        <!-- 7) Преподаватели -->
        <!-- <div class="dialog__component">
            <p class="dialog__title">Выберите преподавателей</p>
            <select
                v-model="form.selectedTeachers"
                multiple
                class="dialog__input"
                :disabled="isDisabled"
            >
                <option v-for="t in teachers" :key="t.id" :value="t.id">
                    {{ t.name }}
                </option>
            </select>
        </div> -->
        <div class="dialog__component">
            <p class="dialog__title">Выберите преподавателя</p>
            <select
                v-model="teacherSelectModel"
                class="dialog__input dialog__select"
                :disabled="teacherSelectDisabled"
            >
                <option :value="null">Выберите преподавателя</option>
                <option v-for="t in teachers" :key="t.id" :value="t.id">
                {{ t.name }}
                </option>
            </select>
        </div>
        <!-- 8) Направление -->
        <div class="dialog__component">
            <p class="dialog__title">Выберите направление</p>
            <select
                v-model="form.selectedDirection"
                class="dialog__input dialog__select"
                :disabled="isDisabled"
            >
                <option :value="null">Выберите направление</option>
                <option v-for="d in directions" :key="d.id" :value="d.id">
                    {{ d.name }}
                </option>
            </select>
        </div>

        <!-- 9) Языки -->
        <div class="dialog__component">
            <p class="dialog__title">Выберите языки программирования</p>
            <Multiselect
                v-model="form.selectedLanguages"
                :options="languages"
                :multiple="true"
                track-by="id"
                label="name"
                placeholder="Нажмите на поле, чтобы выбрать язык"
                :close-on-select="false"
                :clear-on-select="false"
                :preserve-search="true"
                :disabled="isDisabled"
            />
        </div>

        <!-- 11) Файлы -->
        <div class="dialog__block dialog__block--date">
            <!-- ЛОГОТИП -->
            <div class="dialog__component">
                <div class="dialog__title_container">
                    <p class="dialog__title">Логотип курса</p>
                </div>

                <!-- hidden input -->
                <input
                    ref="cardInputRef"
                    type="file"
                    accept="image/*"
                    class="dialog__file-input"
                    @change="handleCardChange"
                    :disabled="isDisabled"
                />

                <!-- красивое поле -->
                <div
                    class="dialog__dropzone"
                    :class="{ 'is-dragging': isDraggingCard }"
                    @click="triggerCardSelect"
                    @dragenter.prevent="isDraggingCard = true"
                    @dragover.prevent="isDraggingCard = true"
                    @dragleave.prevent="isDraggingCard = false"
                    @drop.prevent="onDropCard"
                >
                    <p class="dialog__dropzone_title">
                        {{
                            cardFileName
                                ? cardFileName
                                : "Перетащите или выберите файл"
                        }}
                    </p>
                    <p v-if="!cardFileName" class="dialog__dropzone_title">
                        (JPG или PNG, 5 МБ максимальный размер файла)
                    </p>
                </div>
            </div>

            <!-- ИЗОБРАЖЕНИЕ ДЛЯ ОПИСАНИЯ -->
            <div class="dialog__component">
                <div class="dialog__title_container">
                    <p class="dialog__title">Изображение для описания курса</p>
                </div>

                <!-- hidden input -->
                <input
                    ref="descInputRef"
                    type="file"
                    accept="image/*"
                    class="dialog__file-input"
                    @change="handleDescChange"
                    :disabled="isDisabled"
                />

                <!-- красивое поле -->
                <div
                    class="dialog__dropzone"
                    :class="{ 'is-dragging': isDraggingDesc }"
                    @click="triggerDescSelect"
                    @dragenter.prevent="isDraggingDesc = true"
                    @dragover.prevent="isDraggingDesc = true"
                    @dragleave.prevent="isDraggingDesc = false"
                    @drop.prevent="onDropDesc"
                >
                    <p class="dialog__dropzone_title">
                        {{
                            descFileName
                                ? descFileName
                                : "Перетащите или выберите файл"
                        }}
                    </p>
                    <p v-if="!descFileName" class="dialog__dropzone_title">
                        (JPG или PNG, 5 МБ максимальный размер файла)
                    </p>
                </div>
            </div>
        </div>

        <!-- 12) EditorJS -->
        <div class="dialog__component">
            <p class="dialog__title">Контент курса</p>
            <div
                ref="editorHolder"
                class="dialog__input editor-container"
            ></div>
        </div>

        <!-- Buttons -->
        <div class="dialog__btns">
            <button
                type="button"
                class="main__btn main__btn--white"
                @click="cancel"
            >
                Отмена
            </button>

            <button
                type="button"
                class="main__btn"
                @click="save"
                :disabled="isDisabled"
            >
                Сохранить
            </button>
        </div>
    </div>
</template>

<style scoped>
    :deep .ce-toolbar__actions{
        left: -200px !important;
    }
/* EditorJS внутри визуальной системы dialog */
.editor-container {
    max-width: unset;
    width: 100%;
    min-height: 220px;
    background: #fff;
}
</style>
