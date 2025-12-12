<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from "vue";
import axios from "axios";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";

import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";

import { globalNotification } from "@/globalNotification";

const props = defineProps({
  course: { type: Object, required: true },
});

const emit = defineEmits(["saved"]);

// =======================
// списки для селектов
// =======================
const users = ref([]);
const directions = ref([]);
const languages = ref([]);

const teachers = computed(() =>
  users.value.filter((u) => String(u.role) === "2" || u.role === 2)
);

// =======================
// Форма
// =======================
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

// один преподаватель в UI, а в форме массив id
const teacherSelectModel = computed({
  get() {
    return form.value.selectedTeachers[0] ?? null;
  },
  set(val) {
    form.value.selectedTeachers = val ? [val] : [];
  },
});

// =======================
// EditorJS
// =======================
const editor = ref(null);
const editorHolder = ref(null);

function initEditor() {
  if (!editorHolder.value) return;

  if (editor.value) {
    editor.value.destroy();
    editor.value = null;
  }

  editor.value = new EditorJS({
    holder: editorHolder.value,
    data: form.value.editorData || { blocks: [] },
    placeholder: "Редактируйте контент курса...",
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
      const data = await editor.value.save();
      form.value.editorData = data;
    },
  });
}

// =======================
// Dropzone для 2 картинок
// =======================
const cardInputRef = ref(null);
const descInputRef = ref(null);

const cardFileName = ref("");
const descFileName = ref("");

const isDraggingCard = ref(false);
const isDraggingDesc = ref(false);

// текущие файлы из курса (если бэк отдаёт пути)
const currentCardPath = computed(() => {
  // подстрой под свои поля если отличаются
  return (
    props.course?.card_image ||
    props.course?.cardImage ||
    props.course?.card_image_path ||
    props.course?.cardImagePath ||
    null
  );
});

const currentDescPath = computed(() => {
  return (
    props.course?.description_image ||
    props.course?.descriptionImage ||
    props.course?.description_image_path ||
    props.course?.descriptionImagePath ||
    null
  );
});

const currentCardName = computed(() => {
  if (!currentCardPath.value) return "";
  return String(currentCardPath.value).split("/").pop();
});

const currentDescName = computed(() => {
  if (!currentDescPath.value) return "";
  return String(currentDescPath.value).split("/").pop();
});

const cardUrl = computed(() => makeFileUrl(currentCardPath.value));
const descUrl = computed(() => makeFileUrl(currentDescPath.value));

function makeFileUrl(path) {
  if (!path) return "#";
  const p = String(path);
  if (p.startsWith("http://") || p.startsWith("https://")) return p;
  return `/${p}`;
}

function triggerCardSelect() {
  cardInputRef.value?.click();
}
function triggerDescSelect() {
  descInputRef.value?.click();
}

function handleCardChange(e) {
  const f = e.target.files?.[0] || null;
  form.value.cardImage = f;
  cardFileName.value = f ? f.name : "";
}
function handleDescChange(e) {
  const f = e.target.files?.[0] || null;
  form.value.descriptionImage = f;
  descFileName.value = f ? f.name : "";
}

function onDropCard(e) {
  isDraggingCard.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (!file) return;

  const dt = new DataTransfer();
  dt.items.add(file);
  cardInputRef.value.files = dt.files;

  handleCardChange({ target: cardInputRef.value });
}

function onDropDesc(e) {
  isDraggingDesc.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (!file) return;

  const dt = new DataTransfer();
  dt.items.add(file);
  descInputRef.value.files = dt.files;

  handleDescChange({ target: descInputRef.value });
}

// =======================
// helpers для даты (input type=date требует YYYY-MM-DD)
// =======================
function toDateInput(val) {
  if (!val) return "";
  const s = String(val);
  // если ISO "2025-12-02T..." — берём первые 10 символов
  return s.length >= 10 ? s.slice(0, 10) : s;
}

// =======================
// Загрузка справочников
// =======================
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

// =======================
// Заполняем форму из course
// =======================
function fillFormFromCourse(course) {
  if (!course) return;

  // языки: массив id или объектов
  let selectedLangs = [];
  if (Array.isArray(course.language) && languages.value.length > 0) {
    const ids = course.language.map((l) => (typeof l === "object" ? l.id : Number(l)));
    selectedLangs = languages.value.filter((lang) => ids.includes(lang.id));
  }

  // преподаватели
  const teacherIds = Array.isArray(course.teachers)
    ? course.teachers.map((t) => (typeof t === "object" ? t.id : t))
    : [];

  form.value = {
    cardTitle: course.card_title || "",
    courseName: course.course_name || "",
    price: course.price || "",
    duration: course.duration || "",
    description: course.description || "",
    hours: course.hours || "",
    simulators: course.simulators || "",
    difficulty: course.difficulty || "basic",
    selectedTeachers: teacherIds,
    selectedLanguages: selectedLangs,
    selectedDirection: course.direction ?? null,
    upgradeQualification: course.upgradequalification ?? 0,
    cardImage: null,
    descriptionImage: null,
    pdfFile: null,
    editorData: course.editor_data || { blocks: [] },
    startDate: toDateInput(course.start_date),
    endDate: toDateInput(course.end_date),
  };

  // сброс выбранных новых файлов в dropzone
  cardFileName.value = "";
  descFileName.value = "";
  isDraggingCard.value = false;
  isDraggingDesc.value = false;

  nextTick(() => initEditor());
}

// =======================
// Сохранение
// =======================
async function save() {
  try {
    if (!props.course) return;

    if (editor.value) {
      const data = await editor.value.save();
      form.value.editorData = data;
    }

    const fd = new FormData();
    fd.append("cardTitle", form.value.cardTitle);
    fd.append("courseName", form.value.courseName);
    fd.append("price", form.value.price);
    fd.append("duration", form.value.duration);
    fd.append("description", form.value.description);
    fd.append("hours", form.value.hours);
    if (form.value.simulators != null) fd.append("simulators", form.value.simulators);
    fd.append("difficulty", form.value.difficulty);

    const uniqueTeachers = [...new Set(form.value.selectedTeachers)];
    fd.append("teachers", JSON.stringify(uniqueTeachers));

    fd.append("direction", form.value.selectedDirection ?? "");
    fd.append("upgradequalification", String(form.value.upgradeQualification ?? 0));

    // даты (уже YYYY-MM-DD)
    fd.append("start_date", form.value.startDate || "");
    fd.append("end_date", form.value.endDate || "");

    const langIds = form.value.selectedLanguages.map((l) => l.id);
    fd.append("language", JSON.stringify(langIds));

    fd.append("editorData", JSON.stringify(form.value.editorData));

    // файлы
    if (form.value.cardImage) fd.append("cardImage", form.value.cardImage);
    if (form.value.descriptionImage) fd.append("descriptionImage", form.value.descriptionImage);
    if (form.value.pdfFile) fd.append("pdf", form.value.pdfFile);

    const { data } = await axios.post(`/api/courses/${props.course.id}`, fd, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    globalNotification.categoryMessage = "Курс обновлён";
    globalNotification.type = "success";

    emit("saved", data.course || data);
  } catch (err) {
    console.error("Ошибка при обновлении курса:", err);
    globalNotification.categoryMessage = "Ошибка при обновлении курса";
    globalNotification.type = "error";
  }
}

// =======================
// lifecycle
// =======================
onMounted(async () => {
  await Promise.all([loadUsers(), loadDirections(), loadLanguages()]);
  fillFormFromCourse(props.course);
});

// если курс меняется — перезаполним
watch(
  () => props.course,
  (c) => {
    if (c) fillFormFromCourse(c);
  }
);

// если языки загрузились позже — перезаполним, чтобы selectedLanguages подтянулись
watch(
  () => languages.value.length,
  (len) => {
    if (len && props.course) fillFormFromCourse(props.course);
  }
);

onBeforeUnmount(() => {
  if (editor.value) editor.value.destroy();
});
</script>


<template>
  <div class="form__admin">
    <!-- Полное название -->
    <div class="dialog__component">
      <p class="dialog__title">Полное название курса *</p>
      <input
        v-model="form.courseName"
        type="text"
        class="dialog__input"
        placeholder="Введите название курса"
      />
    </div>

    <!-- Цена -->
    <div class="dialog__component">
      <p class="dialog__title">Цена</p>
      <input
        v-model="form.price"
        type="number"
        class="dialog__input"
        placeholder="Введите цену"
      />
    </div>

    <!-- Часы -->
    <div class="dialog__component">
      <p class="dialog__title">Количество часов</p>
      <input
        v-model="form.hours"
        type="number"
        class="dialog__input"
        placeholder="Введите количество часов"
      />
    </div>

    <!-- Даты -->
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
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Описание -->
    <div class="dialog__component">
      <p class="dialog__title">Описание</p>
      <textarea
        v-model="form.description"
        class="dialog__textarea"
        placeholder="Введите описание"
      />
    </div>

    <!-- simulators скрыто -->
    <input v-model="form.simulators" type="hidden" />

    <!-- Уровень -->
    <div class="dialog__component">
      <p class="dialog__title">Уровень курса</p>
      <select
        v-model="form.difficulty"
        class="dialog__input dialog__select"
      >
        <option value="basic">Базовый</option>
        <option value="middle">Средний</option>
        <option value="advanced">Продвинутый</option>
      </select>
    </div>

    <!-- Преподаватель -->
    <div class="dialog__component">
      <p class="dialog__title">Выберите преподавателя</p>
      <select
        v-model="teacherSelectModel"
        class="dialog__input dialog__select"
      >
        <option :value="null">Выберите преподавателя</option>
        <option v-for="t in teachers" :key="t.id" :value="t.id">
          {{ t.name }}
        </option>
      </select>
    </div>

    <!-- Направление -->
    <div class="dialog__component">
      <p class="dialog__title">Выберите направление</p>
      <select
        v-model="form.selectedDirection"
        class="dialog__input dialog__select"
      >
        <option :value="null">Выберите направление</option>
        <option v-for="d in directions" :key="d.id" :value="d.id">
          {{ d.name }}
        </option>
      </select>
    </div>

    <!-- Языки -->
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
      />
    </div>

    <!-- Файлы -->
    <div class="dialog__block dialog__block--date">
  <!-- ЛОГОТИП -->
  <div class="dialog__component">
    <div class="dialog__title_container">
      <p class="dialog__title">Логотип курса</p>
    </div>

    <!-- <p class="dialog__file-current" v-if="currentCardPath">
      Текущий файл:
      <a class="dialog__file-link" :href="cardUrl" target="_blank" rel="noopener">
        {{ currentCardName }}
      </a>
    </p>
    <p class="dialog__file-current dialog__file-current--empty" v-else>
      Файл не прикреплён
    </p> -->

    <input
      ref="cardInputRef"
      type="file"
      accept="image/*"
      class="dialog__file-input"
      @change="handleCardChange"
    />

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
            : currentCardName
              ? `Заменить файл: ${currentCardName}`
              : "Перетащите или выберите файл"
        }}
      </p>
      <p v-if="!cardFileName" class="dialog__dropzone_title dialog__dropzone_hint">
        (JPG или PNG)
      </p>
    </div>
  </div>

  <!-- ИЗОБРАЖЕНИЕ ДЛЯ ОПИСАНИЯ -->
  <div class="dialog__component">
      <div class="dialog__title_container">
        <p class="dialog__title">Изображение для описания курса</p>
      </div>
<!-- 
      <p class="dialog__file-current" v-if="currentDescPath">
        Текущий файл:
        <a class="dialog__file-link" :href="descUrl" target="_blank" rel="noopener">
          {{ currentDescName }}
        </a>
      </p>
      <p class="dialog__file-current dialog__file-current--empty" v-else>
        Файл не прикреплён
      </p> -->

      <input
        ref="descInputRef"
        type="file"
        accept="image/*"
        class="dialog__file-input"
        @change="handleDescChange"
      />

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
              : currentDescName
                ? `Заменить файл: ${currentDescName}`
                : "Перетащите или выберите файл"
          }}
        </p>
        <p v-if="!descFileName" class="dialog__dropzone_title dialog__dropzone_hint">
          (JPG или PNG)
        </p>
      </div>
    </div>
  </div>


    <!-- EditorJS -->
    <div class="dialog__component">
      <p class="dialog__title">Контент курса</p>
      <div ref="editorHolder" class="dialog__input editor-container"></div>
    </div>

    <!-- Кнопки -->
    <div class="dialog__btns">
      <button
        type="button"
        class="main__btn main__btn--white"
        @click="$emit('saved', null)"
      >
        Отмена
      </button>

      <button
        type="button"
        class="main__btn"
        @click="save"
      >
        Сохранить изменения
      </button>
    </div>
  </div>
</template>

<style scoped>
.editor-container {
  max-width: unset;
  width: 100%;
  min-height: 220px;
  background: #fff;
}
</style>
