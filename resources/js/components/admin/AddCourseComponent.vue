<template>
  <div class="course-form-container">
    <div class="backs">
        <a href="/admin" class="btn-back">Вернуться на предыдущую страницу</a>
    </div>
    <h2>Создать новый курс</h2>

    <form @submit.prevent="submitForm" class="course-form">
      <!-- Названия -->
      <div class="form-group">
        <label class="form-label">Полное название курса</label>
        <input v-model="form.courseName" type="text" placeholder="Введите название курса" class="form-input" />
      </div>

      <div class="form-group">
        <label class="form-label">Краткое название курса</label>
        <input v-model="form.cardTitle" type="text" placeholder="Введите название для карточки" class="form-input" />
      </div>

      <!-- Цена/длительность/часы -->
      <div class="form-group">
        <label class="form-label">Цена</label>
        <input v-model="form.price" type="number" placeholder="Введите цену" class="form-input" />
      </div>

      <div class="form-group">
        <label class="form-label">Длительность в месяцах (1–24)</label>
        <input v-model="form.duration" type="text" placeholder="Введите от 1 до 24 месяцев" class="form-input" />
      </div>

      <div class="form-group">
        <label class="form-label">Количество часов</label>
        <input v-model="form.hours" type="number" placeholder="Введите количество часов" class="form-input" />
      </div>

      <!-- Описание -->
      <div class="form-group">
        <label class="form-label">Описание</label>
        <textarea v-model="form.description" placeholder="Введите описание" class="form-textarea"></textarea>
      </div>

      <!-- Скрытый simulators (как в твоём коде) -->
      <input v-model="form.simulators" type="hidden" class="form-input" />

      <!-- Сложность -->
      <div class="form-group">
        <label class="form-label">Уровень курса</label>
        <select v-model="form.difficulty" class="form-input">
          <option value="basic">ДПО</option>
          <option value="middle">ДПО детское</option>
          <option value="advanced">Продвинутый</option>
          <option value="mixed">Смешанный</option>
        </select>
      </div>

      <!-- Преподаватели -->
      <div class="form-group">
        <label class="form-label">Выберите преподавателей</label>
        <select v-model="form.selectedTeachers" multiple class="form-input">
          <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </div>

      <!-- Направление -->
      <div class="form-group">
        <label class="form-label">Выберите направление</label>
        <select v-model="form.selectedDirection" class="form-input">
          <option :value="null">-- Выберите направление --</option>
          <option v-for="d in directions" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>
      </div>

      <!-- Языки -->
      <div class="form-group">
        <label class="form-label">Выберите языки программирования</label>
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

      <!-- Повышение квалификации -->
      <div class="form-group">
        <label class="form-label">Повышение квалификации</label>
        <select v-model.number="form.upgradeQualification" class="form-input">
          <option :value="0">Нет</option>
          <option :value="1">Да</option>
        </select>
      </div>

      <!-- Файлы -->
      <div class="form-group">
        <label class="form-label">Логотип курса</label>
        <input type="file" accept="image/*" class="form-input" @change="onCardImage" />
      </div>

      <div class="form-group">
        <label class="form-label">Изображение для описания курса</label>
        <input type="file" accept="image/*" class="form-input" @change="onDescriptionImage" />
      </div>

      <div class="form-group">
        <label class="form-label">PDF файл курса</label>
        <input type="file" accept="application/pdf" class="form-input" @change="onPdf" />
      </div>
      <!-- EditorJS -->
      <div ref="editorHolder" class="editor-container"></div>

      <button type="submit" class="form-button">Отправить</button>
    </form>
  </div>
</template>
<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import EditorJS from '@editorjs/editorjs'
import Header from '@editorjs/header'
import List from '@editorjs/list'
import ImageTool from '@editorjs/image'
import { globalNotification } from '@/globalNotification' // поправь путь при необходимости





/** Параметры поведения */
const props = defineProps({
  /** Автоматически редиректить после создания (ожидается поле redirect_url в ответе) */
  autoRedirect: { type: Boolean, default: true },
  /** Название поля с URL редиректа в ответе API */
  redirectField: { type: String, default: 'redirect_url' }
})
const emit = defineEmits(['created']) // parent сможет поймать событие

/** Локальная форма */
const form = ref({
  cardTitle: '',
  courseName: '',
  price: '',
  duration: '',
  description: '',
  hours: '',
  simulators: '',            // как в исходнике
  difficulty: 'basic',
  selectedTeachers: [],
  selectedLanguages: [],
  selectedDirection: null,
  upgradeQualification: 0,   // 0 | 1
  cardImage: null,
  descriptionImage: null,
  pdfFile: null,
  editorData: {}
})

/** Источники для селектов */
const users = ref([])
const directions = ref([])
const languages = ref([])

const teachers = computed(() => users.value.filter(u => String(u.role) === '2' || u.role === 2))

/** EditorJS */
const editor = ref(null)
const editorHolder = ref(null)

onMounted(async () => {
  await Promise.all([loadUsers(), loadDirections(), loadLanguages()])
  initEditor()
})
onBeforeUnmount(() => {
  if (editor.value) editor.value.destroy()
})

function initEditor () {
  if (editor.value) {
    editor.value.destroy()
    editor.value = null
  }
  editor.value = new EditorJS({
    holder: editorHolder.value,
    placeholder: 'Добавьте контент для нового курса...',
    data: form.value.editorData,
    tools: {
      header: { class: Header, inlineToolbar: ['link'] },
      list: { class: List, inlineToolbar: true },
      image: {
        class: ImageTool,
        config: {
          endpoints: {
            byFile: '/api/uploadFile',
            byUrl: '/api/fetchUrl'
          }
        }
      }
    },
    async onChange() {
      const data = await editor.value.save()
      form.value.editorData = data
    }
  })
}

/** Загрузка данных */
async function loadUsers() {
  const { data } = await axios.get('/api/users')
  users.value = data
}
async function loadDirections() {
  const { data } = await axios.get('/api/directions')
  directions.value = data
}
async function loadLanguages() {
  const { data } = await axios.get('/api/languages')
  languages.value = data
}

/** Хендлеры файлов */
function onCardImage(e) { form.value.cardImage = e.target.files?.[0] || null }
function onDescriptionImage(e) { form.value.descriptionImage = e.target.files?.[0] || null }
function onPdf(e) { form.value.pdfFile = e.target.files?.[0] || null }

/** Сабмит создания */
async function submitForm () {
  try {
    // 1) гарантируем, что editorData актуальна
    if (editor.value) {
      const data = await editor.value.save()
      form.value.editorData = data
    }

    // 2) FormData
    const fd = new FormData()
    fd.append('cardTitle', form.value.cardTitle)
    fd.append('courseName', form.value.courseName)
    fd.append('price', form.value.price)
    fd.append('duration', form.value.duration)
    fd.append('description', form.value.description)
    fd.append('hours', form.value.hours)
    if (form.value.simulators != null) fd.append('simulators', form.value.simulators)
    fd.append('difficulty', form.value.difficulty)
    fd.append('editorData', JSON.stringify(form.value.editorData))

    // преподаватели (массив ID)
    fd.append('teachers', JSON.stringify(form.value.selectedTeachers))

    // языки как массив ID (Multiselect -> объекты)
    const languageIds = form.value.selectedLanguages.map(l => l.id)
    fd.append('language', JSON.stringify(languageIds))

    // направление (ID)
    fd.append('direction', form.value.selectedDirection)

    // повышение квалификации (0/1)
    fd.append('upgradequalification', form.value.upgradeQualification)

    // файлы
    if (form.value.cardImage) fd.append('cardImage', form.value.cardImage)
    if (form.value.descriptionImage) fd.append('descriptionImage', form.value.descriptionImage)
    if (form.value.pdfFile) fd.append('pdf', form.value.pdfFile)

    // 3) POST
    const { data } = await axios.post('/api/courses', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    // уведомление
    globalNotification.categoryMessage = 'Курс создан'
    globalNotification.type = 'success'

    // событие наверх (parent сможет обновить список/уйти на другую страницу)
    emit('created', data)

    // авто-редирект (как было в исходнике)
    if (props.autoRedirect && data?.[props.redirectField]) {
      window.location.href = data[props.redirectField]
      return
    }

    // если без редиректа — очищаем форму
    resetForm()

  } catch (err) {
    console.error('Ошибка при создании курса:', err)
    globalNotification.categoryMessage = 'Заполните все поля для создания курса'
    globalNotification.type = 'error'
  }
}

function resetForm() {
  form.value = {
    cardTitle: '',
    courseName: '',
    price: '',
    duration: '',
    description: '',
    hours: '',
    simulators: '',
    difficulty: 'basic',
    selectedTeachers: [],
    selectedLanguages: [],
    selectedDirection: null,
    upgradeQualification: 0,
    cardImage: null,
    descriptionImage: null,
    pdfFile: null,
    editorData: {}
  }
  // очистим EditorJS визуально
  if (editor.value) editor.value.clear()
}
</script>

<!-- ВАЖНО про стили:
Если в родительском файле стили scoped, они НЕ распространятся на дочерний компонент.
Либо убери scoped у общих классов, либо дублируй нужные правила здесь/в общем css. -->
<style scoped>
/* Минимум базовых стилей на случай scoped-родителя */
.btn-back {
    text-decoration: none;
    margin-left: 50px;
    font-size: 15px;
    background: none;
    border: none;
    color: #5b4bff;
    cursor: pointer;
    margin-bottom: 20px;
}
.course-form-container { margin: 35px auto; }
.course-form { display: grid; grid-template-columns: 1fr; width: 700px; gap: 20px; margin: 0 auto 40px; padding-bottom: 20px;}
.form-group { display: flex; flex-direction: column; }
.form-label { font-weight: bold; margin-bottom: 8px; color: #333; }
.form-input, .form-textarea { outline: none; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
.form-textarea { resize: none; min-height: 80px; }
.editor-container { padding: 10px; border: 1px solid #ccc; border-radius: 4px; min-height: 150px; background: #fff; }
.form-button { width: 700px; background: #007bff; color: #fff; padding: 10px 15px; border: 0; border-radius: 4px; font-size: 16px; cursor: pointer; transition: background-color .3s; margin: 0 auto; }
.form-button:hover { background: #0056b3; }
</style>
