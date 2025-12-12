<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
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
    const ids = course.language.map((l) =>
      typeof l === "object" ? l.id : l
    );
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
    editorData: course.editor_data || {},
    startDate: course.start_date || "",
    endDate: course.end_date || "",
  };

  initEditor();
}

// =======================
// EditorJS init
// =======================
function initEditor() {
  if (!editorHolder.value) return;
  if (editor.value) {
    editor.value.destroy();
    editor.value = null;
  }

  editor.value = new EditorJS({
    holder: editorHolder.value,
    data: form.value.editorData,
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
// файлы
// =======================
function onCardImage(e) {
  form.value.cardImage = e.target.files?.[0] || null;
}
function onDescriptionImage(e) {
  form.value.descriptionImage = e.target.files?.[0] || null;
}
function onPdf(e) {
  form.value.pdfFile = e.target.files?.[0] || null;
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
    if (form.value.simulators != null) {
      fd.append("simulators", form.value.simulators);
    }
    fd.append("difficulty", form.value.difficulty);

    const uniqueTeachers = [...new Set(form.value.selectedTeachers)];
    fd.append("teachers", JSON.stringify(uniqueTeachers));

    fd.append("direction", form.value.selectedDirection ?? "");
    fd.append(
      "upgradequalification",
      String(form.value.upgradeQualification ?? 0)
    );

    // даты
    fd.append("start_date", form.value.startDate || "");
    fd.append("end_date", form.value.endDate || "");

    const langIds = form.value.selectedLanguages.map((l) => l.id);
    fd.append("language", JSON.stringify(langIds));

    fd.append("editorData", JSON.stringify(form.value.editorData));

    if (form.value.cardImage) fd.append("cardImage", form.value.cardImage);
    if (form.value.descriptionImage)
      fd.append("descriptionImage", form.value.descriptionImage);
    if (form.value.pdfFile) fd.append("pdf", form.value.pdfFile);

    const { data } = await axios.post(
      `/api/courses/${props.course.id}`,
      fd,
      { headers: { "Content-Type": "multipart/form-data" } }
    );

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

watch(
  () => props.course,
  (c) => {
    if (c) fillFormFromCourse(c);
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
                value='{{form.startDate}}'
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
    <div class="dialog__component medium">
      <p class="dialog__title">Уровень курса</p>
      <select
        v-model="form.difficulty"
        class="dialog__input dialog__select"
      >
        <option value="basic">ДПО</option>
        <option value="middle">ДПО детское</option>
        <option value="advanced">Продвинутый</option>
        <option value="mixed">Смешанный</option>
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
      <div class="dialog__component">
        <p class="dialog__title">Логотип курса (если нужно обновить)</p>
        <input
          type="file"
          accept="image/*"
          class="dialog__input"
          @change="onCardImage"
        />
      </div>

      <div class="dialog__component">
        <p class="dialog__title">Изображение для описания (если нужно обновить)</p>
        <input
          type="file"
          accept="image/*"
          class="dialog__input"
          @change="onDescriptionImage"
        />
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
