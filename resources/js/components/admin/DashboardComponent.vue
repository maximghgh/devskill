<template>
    <div>
        <div class="container">
            <aside>
                <div class="menu">
                    <div class="aside__menu">
                        <!-- Меню -->
                        <ul class="aside__list">
                            <li class="aside__item" v-for="(item, index) in menuItems" :key="index">
                                <a :href="item.href" :class="[
                                    'aside__link',
                                    {
                                        'aside__link--active':
                                            activeIndex === index,
                                    },
                                ]" @click.prevent="setActive(index)">
                                    {{ item.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu-block">
                        <div v-for="(item, index) in menuItems" :key="index" :id="item.id" class="block"
                            style="margin-top: 20px" v-show="activeIndex === index">
                            <h1 class="page__title">{{ item.label }}</h1>
                            <!-- Блок "Все пользователи" -->
                            <div v-if="item.id === 'users'" class="user-block">
                                <div class="filters">
                                    <label for="roleFilter">Фильтр по роли:</label>
                                    <select id="roleFilter" v-model="selectedRole" class="role-filter">
                                        <option value="all">Все роли</option>
                                        <option value="3">Админ</option>
                                        <option value="2">Преподаватель</option>
                                        <option value="1">Ученик</option>
                                    </select>
                                </div>
                                <table class="light-push-table" v-if="users.length">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Имя</th>
                                            <th>Email</th>
                                            <th>Телефон</th>
                                            <th>День рождения</th>
                                            <th>Местоположение</th>
                                            <th>Роль</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="userItem in filteredUsers" :key="userItem.id">
                                            <td>{{ userItem.id }}</td>
                                            <td>{{ userItem.name }}</td>
                                            <td>{{ userItem.email }}</td>
                                            <td>{{ userItem.phone }}</td>
                                            <td>{{ userItem.birthday }}</td>
                                            <td>{{ userItem.country }}</td>
                                            <td>
                                                {{ getRoleName(userItem.role) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else>Нет пользователей</div>
                            </div>
                            <!-- Блок "Курсы" -->
                            <div v-else-if="item.id === 'courses'">
                                <h2>Список курсов</h2>
                                <div v-if="courses.length">
                                    <table class="light-push-table">
                                        <thead>
                                            <tr>
                                                <th>Название курса</th>
                                                <th>Уровень</th>
                                                <th>Действие</th>
                                                <th>Управлять курсом</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="course in courses" :key="course.id">
                                                <td>
                                                    {{ course.course_name }}
                                                </td>
                                                <td>{{ course.difficulty }}</td>
                                                <td>
                                                    <a href="#" @click.prevent="
                                                        openEditModal(
                                                            course
                                                        )
                                                        ">Изменить</a>
                                                </td>
                                                <td><a :href="`/admin/course/${course.id}/topics/json`">Управлять курсом</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else>Нет курсов</div>

                                <!-- Модальное окно редактирования курса -->
                                <div v-if="showEditModal" class="modal-overlay">
                                    <div class="modal-content">
                                        <button class="close-button" @click="closeEditModal">
                                            X
                                        </button>
                                        <h2>Редактирование курса</h2>
                                        <div class="edit-course-form-container">
                                            <form @submit.prevent="updateCourse" class="edit-course-form">
                                                <!-- Первая колонка -->
                                                <div class="form-column">
                                                    <div class="form-group">
                                                        <label class="form-label">Название на
                                                            карточке</label>
                                                        <input v-model="editCourse.cardTitle
                                                            " type="text" placeholder="Введите название для карточки"
                                                            class="form-input" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Название
                                                            курса</label>
                                                        <input v-model="editCourse.courseName
                                                            " type="text" placeholder="Введите название курса"
                                                            class="form-input" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Цена</label>
                                                        <input v-model="editCourse.price
                                                            " type="number" placeholder="Введите цену"
                                                            class="form-input" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Длительность (в
                                                            месяцах от
                                                            1-24)</label>
                                                        <input v-model="editCourse.duration
                                                            " type="text" placeholder="Введите длительность"
                                                            class="form-input" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Описание</label>
                                                        <textarea v-model="editCourse.description
                                                            " placeholder="Введите описание"
                                                            class="form-textarea"></textarea>
                                                    </div>
                                                </div>
                                                <!-- Вторая колонка -->
                                                <div class="form-column">
                                                    <div class="form-group">
                                                        <label class="form-label">Количество
                                                            часов</label>
                                                        <input v-model="editCourse.hours
                                                            " type="number" placeholder="Введите количество часов"
                                                            class="form-input" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Количество
                                                            тренажёров</label>
                                                        <input v-model="editCourse.simulators
                                                            " type="number" placeholder="Введите количество тренажёров"
                                                            class="form-input" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Уровень сложности
                                                            курса</label>
                                                        <select v-model="editCourse.difficulty
                                                            " class="form-input">
                                                            <option value="basic">
                                                                Базовый
                                                            </option>
                                                            <option value="fundamental">
                                                                Фундаментальный
                                                            </option>
                                                            <option value="olympic">
                                                                Олимпиадный
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Выберите
                                                            преподавателей</label>
                                                        <select v-model="editCourse.selectedTeachers
                                                            " multiple class="form-input">
                                                            <option class="option-form" v-for="teacher in teachers"
                                                                :key="teacher.id
                                                                    " :value="teacher.id
                                                                    ">
                                                                {{
                                                                teacher.name
                                                                }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Язык
                                                            программирования</label>
                                                        <select v-model="editCourse.selectedLanguage
                                                            " class="form-input">
                                                            <option value="javascript">
                                                                JavaScript
                                                            </option>
                                                            <option value="python">
                                                                Python
                                                            </option>
                                                            <option value="java">
                                                                Java
                                                            </option>
                                                            <option value="csharp">
                                                                C#
                                                            </option>
                                                            <option value="php">
                                                                PHP
                                                            </option>
                                                            <option value="ruby">
                                                                Ruby
                                                            </option>
                                                            <option value="other">
                                                                Другой
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Логотип
                                                            курса</label>
                                                        <input type="file" accept="image/*" class="form-input" @change="
                                                            onFileChange
                                                        " />
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Изображение для
                                                            описания
                                                            курса</label>
                                                        <input type="file" accept="image/*" class="form-input" @change="
                                                            onDescriptionImageChange
                                                        " />
                                                    </div>
                                                </div>
                                                <!-- Editor.js контейнер -->
                                                <div id="editorjs-edit" class="editor-container"></div>
                                                <button type="submit" class="form-button">
                                                    Сохранить
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Конец модального окна -->

                                <!-- Форма создания нового курса -->
                                <div class="course-form-container">
                                    <h2>Создать новый курс</h2>
                                    <form @submit.prevent="submitForm" class="course-form">
                                        <div class="form-group">
                                            <label class="form-label">Название на карточке</label>
                                            <input v-model="newCourse.cardTitle" type="text"
                                                placeholder="Введите название для карточки" class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Название курса</label>
                                            <input v-model="newCourse.courseName" type="text"
                                                placeholder="Введите название курса" class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Цена</label>
                                            <input v-model="newCourse.price" type="number" placeholder="Введите цену"
                                                class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Длительность (в месяцах от
                                                1-24)</label>
                                            <input v-model="newCourse.duration" type="text"
                                                placeholder="Введите длительность" class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Описание</label>
                                            <textarea v-model="newCourse.description" placeholder="Введите описание"
                                                class="form-textarea"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Количество часов</label>
                                            <input v-model="newCourse.hours" type="number"
                                                placeholder="Введите количество часов" class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Количество тренажёров</label>
                                            <input v-model="newCourse.simulators" type="number"
                                                placeholder="Введите количество тренажёров" class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Уровень сложности курса</label>
                                            <select v-model="newCourse.difficulty" class="form-input">
                                                <option value="basic">
                                                    Начинающий
                                                </option>
                                                <option value="middle">
                                                    Средний
                                                </option>
                                                <option value="advanced">
                                                    Продвинутый
                                                </option>
                                                <option value="mixed">
                                                    Смешанный
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Выберите преподавателей</label>
                                            <select v-model="newCourse.selectedTeachers
                                                " multiple class="form-input">
                                                <option class="option-form" v-for="teacher in teachers"
                                                    :key="teacher.id" :value="teacher.id">
                                                    {{ teacher.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Выберите направление</label>
                                            <select v-model="newCourse.selectedDirection" class="form-input">
                                            <option> -- Выберите направление --</option>
                                            <option class="option-form" v-for="direction in directions" :key="direction.id" :value="direction.id">
                                                {{ direction.name }}
                                            </option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Выберите языки
                                                программирования</label>
                                            <!-- multiple позволяет выбрать сразу несколько пунктов -->
                                            <Multiselect
                                                v-model="newCourse.selectedLanguages"
                                                :options="languages"
                                                :multiple="true"
                                                track-by="id"
                                                label="name"
                                                placeholder="Нажмите на поле, чтобы выбрать язык"
                                                :close-on-select="false"
                                                :clear-on-select="false"
                                                :preserve-search="true"
                                            >
                                                <!-- Кастомизация пункта выпадающего списка -->
                                                <template #option="{ option, selected }">
                                                <div class="multiselect__option">
                                                    <!-- «Пузырь» вместо стандартного чекбокса -->
                                                    <input
                                                    type="checkbox"
                                                    class="checkbox"
                                                    :class="{ 'checkbox-checked': selected }"
                                                    />
                                                    <span class="checkmark">
                                                    <!-- SVG-галочка -->
                                                    <svg
                                                        class="checkmark__icon"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                        d="M 20.292969 5.2929688 L 9 16.585938 L 4.7070312 12.292969 L 3.2929688 13.707031 
                                                            L 9 19.414062 L 21.707031 6.7070312 L 20.292969 5.2929688 z"
                                                        />
                                                    </svg>
                                                    </span>
                                                    <span class="option-name">{{ option.name }}</span>
                                                </div>
                                                </template>

                                                <!-- Кастомизация заголовка списка -->
                                                <template #selection="{ values }">
                                                <span class="selection-header">
                                                    {{ values.map((v) => v.name).join(", ") }}
                                                </span>
                                                </template>
                                            </Multiselect>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Логотип курса</label>
                                            <input type="file" accept="image/*" class="form-input"
                                                @change="onFileChange" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Изображение для описания
                                                курса</label>
                                            <input type="file" accept="image/*" class="form-input" @change="
                                                onDescriptionImageChange
                                            " />
                                        </div>
                                        <div id="editorjs-create" class="editor-container"></div>
                                        <button type="submit" class="form-button">
                                            Отправить
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Другие блоки (аналитика, FAQ, и т.д.) -->
                            <div v-else-if="item.id === 'news'">
                              <form @submit.prevent="submitNews" class="course-form">
                                <div class="form-group">
                                  <label class="form-label">Заголовок новости</label>
                                  <input
                                    v-model="newNews.title"
                                    type="text"
                                    placeholder="Введите заголовок новости"
                                    class="form-input"
                                  />
                                </div>
                                <div class="form-group">
                                  <label class="form-label">Краткое описание</label>
                                  <textarea
                                    v-model="newNews.content"
                                    placeholder="Введите краткое описание"
                                    class="form-textarea"
                                  ></textarea>
                                </div>
                                <div class="form-group">
                                  <label class="form-label">Изображение новости</label>
                                  <input
                                    type="file"
                                    accept="image/*"
                                    class="form-input"
                                    @change="onNewsImageChange"
                                  />
                                </div>
                                <div class="form-group">
                                  <label class="form-label">Текст новости</label>
                                  <!-- Контейнер для EditorJS -->
                                  <div id="editorjs-news" class="editor-container"></div>
                                </div>
                                <button type="submit" class="form-button">Опубликовать новость</button>
                              </form>
                                <!-- Другой контент -->
                            </div>
                            <div v-else-if="item.id === 'faq'">
                              <form @submit.prevent="submitFaq" class="edit-course-form--small">
                                <label class="form-label--small" for="question">Создание частых вопросов</label>
                                <input
                                  type="text"
                                  name="question"
                                  v-model="faq.question"
                                  placeholder="Введите вопрос"
                                  class="form-input"
                                />
                                <textarea
                                  name="answer"
                                  v-model="faq.answer"
                                  placeholder="Введите ответ"
                                  class="form-input"
                                ></textarea>
                                <button type="submit" class="form-button--small">
                                  Создать ответ
                                </button>
                              </form>
                            </div>
                            <div v-else-if="item.id === 'other'">
                                <div class="category">
                                    <div>
                                        <h2>Создание новой категории обучения</h2>
                                        <form @submit.prevent="createLanguageCategory" class="edit-course-form--small">
                                            <label class="form-label--small" for="name">Название языка программирования</label>
                                            <input v-model="languageCategory" type="text"
                                                placeholder="Введите язык программирования" class="form-input" />
                                            <button data-v-4274cdba="" type="submit" class="form-button--small">
                                                Создать категорию
                                            </button>
                                        </form>
                                    </div>
                                    <div>
                                        <h2>Создание нового направления</h2>
                                        <form class="edit-course-form--small" @submit.prevent="submitDirection">
                                            <label class="form-label--small" for="name">Название направления</label>
                                            <input class="form-input" type="text" id="name" v-model="directionName"
                                                placeholder="Введите направление"/>
                                            <button data-v-4274cdba="" type="submit" class="form-button--small">
                                                Создать направление
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <!-- Другой контент -->
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from "vue";
import axios from "axios";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";
import { globalNotification } from "../../globalNotification";

/* =====================================
   1. Пользователи
===================================== */
const user = ref(null);
const users = ref([]);
const selectedRole = ref("all");
const filteredUsers = computed(() => {
  return selectedRole.value === "all"
    ? users.value
    : users.value.filter((u) => String(u.role) === selectedRole.value);
});
function getRoleName(role) {
  switch (role) {
    case 3:
      return "Админ";
    case 2:
      return "Преподаватель";
    case 1:
      return "Ученик";
    default:
      return "Неизвестно";
  }
}

/* =====================================
   2. Курсы, языки и направления
===================================== */
const courses = ref([]);
const languages = ref([]); // Языки программирования

// Реактивная переменная для направлений
const directions = ref([]);

// Состояние для создания курса
const newCourse = ref({
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
  selectedDirection: null, // <-- новое поле
  cardImage: null,
  descriptionImage: null,
  editorData: {},
});

// Состояние для редактирования курса (в модальном окне)
const editCourse = ref({
  cardTitle: "",
  courseName: "",
  price: "",
  duration: "",
  description: "",
  hours: "",
  simulators: "",
  difficulty: "basic",
  selectedTeachers: [],
  selectedLanguage: "javascript",
  cardImage: null,
  descriptionImage: null,
  editorData: {},
});

// Модальное окно редактирования курса
const showEditModal = ref(false);
const selectedCourse = ref(null);

// Список преподавателей (фильтрация по роли)
const teachers = computed(() => {
  return users.value.filter((u) => u.role === 2);
});

/* =====================================
   3. Editor.js
===================================== */
let editorCreate = ref(null); // для создания курса
let editorEdit = ref(null); // для редактирования курса

/* =====================================
   4. Функции работы с файлами
===================================== */
function onFileChange(e) {
  newCourse.value.cardImage = e.target.files[0] || null;
  editCourse.value.cardImage = e.target.files[0] || null;
}
function onDescriptionImageChange(e) {
  newCourse.value.descriptionImage = e.target.files[0] || null;
  editCourse.value.descriptionImage = e.target.files[0] || null;
}

/* =====================================
   5. Создание нового курса
===================================== */
// async function submitForm() {
//   try {
//     const editorData = await editorCreate.value.save();
//     newCourse.value.editorData = editorData;

//     const formData = new FormData();
//     formData.append("cardTitle", newCourse.value.cardTitle);
//     formData.append("courseName", newCourse.value.courseName);
//     formData.append("price", newCourse.value.price);
//     formData.append("duration", newCourse.value.duration);
//     formData.append("description", newCourse.value.description);
//     formData.append("hours", newCourse.value.hours);
//     formData.append("simulators", newCourse.value.simulators);
//     formData.append("difficulty", newCourse.value.difficulty);
//     formData.append("editorData", JSON.stringify(newCourse.value.editorData));
//     formData.append("teachers", JSON.stringify(newCourse.value.selectedTeachers));
//     const languageIds = newCourse.value.selectedLanguages.map((lang) => lang.id);
//     formData.append("language", JSON.stringify(languageIds));
//     formData.append("direction", newCourse.value.selectedDirection);

//     if (newCourse.value.cardImage) {
//       formData.append("cardImage", newCourse.value.cardImage);
//     }
//     if (newCourse.value.descriptionImage) {
//       formData.append("descriptionImage", newCourse.value.descriptionImage);
//     }

//     const response = await axios.post("/api/courses", formData, {
//       headers: { "Content-Type": "multipart/form-data" },
//     });
    
//     console.log("Курс создан:", response.data);
//     globalNotification.categoryMessage = "Курс создан";
//     globalNotification.type = "success";
    
//     // Получаем id нового курса из ответа сервера
//     const courseId = response.data.course.id;
//     // axios.post('/api/admin/course', formData)
//     // .then(response => {
//     //   // смотрим, что пришло
//     //   console.log(response.data.redirect_url);
//     //   // переходим
//     //   window.location.href = response.data.redirect_url;
//     // })

//     // Переадресация на страницу с курсом (например, /course/{id})
//     window.location.href = response.data.redirect_url;
    
//     // Если хотите сохранить состояние для загрузки курсов, можно вызвать loadCourses() перед редиректом,
//     // но чаще перенаправление происходит мгновенно.
    
//     // Очистка данных формы (если редирект не мгновенный, для примера)
//     newCourse.value = {
//       cardTitle: "",
//       courseName: "",
//       price: "",
//       duration: "",
//       description: "",
//       hours: "",
//       simulators: "",
//       difficulty: "basic",
//       selectedTeachers: [],
//       selectedLanguages: [],
//       selectedDirection: null,
//       cardImage: null,
//       descriptionImage: null,
//       editorData: {},
//     };

//     // Очистка EditorJS (если нужно)
//     editorCreate.value.clear();
//   } catch (error) {
//     console.error("Ошибка при создании курса:", error);
//     globalNotification.categoryMessage = "Заполните все поля для создания курса";
//     globalNotification.type = "error";
//   }
// }

async function submitForm() {
  try {
    // Сохраняем данные из Editor.js
    const editorData = await editorCreate.value.save();
    newCourse.value.editorData = editorData;

    // Формируем formData
    const formData = new FormData();
    formData.append("cardTitle", newCourse.value.cardTitle);
    formData.append("courseName", newCourse.value.courseName);
    formData.append("price", newCourse.value.price);
    formData.append("duration", newCourse.value.duration);
    formData.append("description", newCourse.value.description);
    formData.append("hours", newCourse.value.hours);
    formData.append("simulators", newCourse.value.simulators);
    formData.append("difficulty", newCourse.value.difficulty);
    formData.append("editorData", JSON.stringify(newCourse.value.editorData));
    formData.append("teachers", JSON.stringify(newCourse.value.selectedTeachers));

    // Пример, если вам нужны массивы языков
    const languageIds = newCourse.value.selectedLanguages.map((lang) => lang.id);
    formData.append("language", JSON.stringify(languageIds));

    formData.append("direction", newCourse.value.selectedDirection);

    // Добавляем файлы, если есть
    if (newCourse.value.cardImage) {
      formData.append("cardImage", newCourse.value.cardImage);
    }
    if (newCourse.value.descriptionImage) {
      formData.append("descriptionImage", newCourse.value.descriptionImage);
    }

    // Отправляем POST-запрос на /api/courses
    const response = await axios.post("/api/courses", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    console.log("Курс создан:", response.data);
    globalNotification.categoryMessage = "Курс создан";
    globalNotification.type = "success";

    // 1) Бэкенд вернул JSON, где есть "redirect_url"
    // 2) Переходим на redirect_url
    window.location.href = response.data.redirect_url;

    // Если редирект делается мгновенно, код дальше может не выполниться.
    // Но если хотите очистить форму до редиректа (или если редирект в другом месте), можете это сделать:
    newCourse.value = {
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
      cardImage: null,
      descriptionImage: null,
      editorData: {},
    };

    // Очистка EditorJS (по желанию)
    editorCreate.value.clear();

  } catch (error) {
    console.error("Ошибка при создании курса:", error);
    globalNotification.categoryMessage = "Заполните все поля для создания курса";
    globalNotification.type = "error";
  }
}

/* =====================================
   6. Редактирование курса
===================================== */
async function updateCourse() {
  if (!selectedCourse.value) return;
  try {
    const editorData = await editorEdit.value.save();
    editCourse.value.editorData = editorData;

    const formData = new FormData();
    formData.append("cardTitle", editCourse.value.cardTitle);
    formData.append("courseName", editCourse.value.courseName);
    formData.append("price", editCourse.value.price);
    formData.append("duration", editCourse.value.duration);
    formData.append("description", editCourse.value.description);
    formData.append("hours", editCourse.value.hours);
    formData.append("simulators", editCourse.value.simulators);
    formData.append("difficulty", editCourse.value.difficulty);
    formData.append("language", editCourse.value.selectedLanguage);
    formData.append(
      "teachers",
      JSON.stringify(editCourse.value.selectedTeachers)
    );
    formData.append("editorData", JSON.stringify(editCourse.value.editorData));

    if (editCourse.value.cardImage) {
      formData.append("cardImage", editCourse.value.cardImage);
    }
    if (editCourse.value.descriptionImage) {
      formData.append("descriptionImage", editCourse.value.descriptionImage);
    }

    const response = await axios.post(
      `/api/courses/${selectedCourse.value.id}`,
      formData,
      { headers: { "Content-Type": "multipart/form-data" } }
    );
    console.log("Курс обновлён:", response.data);
    globalNotification.categoryMessage = "Курс обновлён";
    globalNotification.type = "success";
    await loadCourses();
    closeEditModal();
  } catch (error) {
    console.error("Ошибка при обновлении курса:", error);
    globalNotification.categoryMessage = "Ошибка при обновлении курса";
    globalNotification.type = "error";
  }
}

/* =====================================
   7. Модальное окно редактирования курса
===================================== */
async function openEditModal(course) {
  selectedCourse.value = course;
  editCourse.value = {
    cardTitle: course.card_title || "",
    courseName: course.course_name || "",
    price: course.price || "",
    duration: course.duration || "",
    description: course.description || "",
    hours: course.hours || "",
    simulators: course.simulators || "",
    difficulty: course.difficulty || "basic",
    selectedTeachers: course.teachers || [],
    selectedLanguage: course.language || "javascript",
    cardImage: null,
    descriptionImage: null,
    editorData: course.editor_data || {},
  };

  showEditModal.value = true;
  await nextTick();

  if (editorEdit.value) {
    editorEdit.value.destroy();
    editorEdit.value = null;
  }
  editorEdit.value = new EditorJS({
    holder: "editorjs-edit",
    data: editCourse.value.editorData,
    placeholder: "Редактируйте контент...",
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
    onChange: async () => {
      const savedData = await editorEdit.value.save();
      editCourse.value.editorData = savedData;
    },
  });
}

function closeEditModal() {
  showEditModal.value = false;
  selectedCourse.value = null;
  if (editorEdit.value) {
    editorEdit.value.destroy();
    editorEdit.value = null;
  }
}

/* =====================================
   8. Меню, навигация, выход
===================================== */
const activeIndex = ref(0);
const menuItems = [
  { id: "users", label: "Пользователи", href: "#users" },
  { id: "courses", label: "Курсы", href: "#courses" },
  { id: "other", label: "Категории", href: "#other" },
  { id: "news", label: "Новости", href: "#news" },
  { id: "faq", label: "Частые вопросы", href: "#faq" },
];
function saveActiveIndex() {
  localStorage.setItem("activeIndex", activeIndex.value);
}
function setActive(index) {
  activeIndex.value = index;
  saveActiveIndex();
  const targetElement = document.getElementById(menuItems[index].id);
  if (targetElement) {
    targetElement.scrollIntoView({ behavior: "smooth" });
  }
}
function base64ToUtf8(str) {
  return decodeURIComponent(escape(atob(str)));
}
function logout() {
  localStorage.removeItem("user");
  user.value = null;
  window.location.href = "/";
}

/* =====================================
   9. Загрузка данных (пользователи, курсы, языки)
===================================== */
async function loadUsers() {
  try {
    const response = await axios.get("/api/users");
    users.value = response.data;
  } catch (error) {
    console.error("Ошибка при загрузке пользователей:", error);
    globalNotification.categoryMessage = "Ошибка при загрузке пользователей";
    globalNotification.type = "error";
  }
}
async function loadCourses() {
  try {
    const response = await axios.get("/api/courses");
    courses.value = response.data;
  } catch (error) {
    console.error("Ошибка при загрузке курсов:", error);
    globalNotification.categoryMessage = "Ошибка при загрузке курсов";
    globalNotification.type = "error";
  }
}
async function loadLanguages() {
  try {
    const response = await axios.get("/api/languages");
    languages.value = response.data;
  } catch (error) {
    console.error("Ошибка при загрузке языков:", error);
    globalNotification.categoryMessage = "Ошибка при загрузке языков";
    globalNotification.type = "error";
  }
}

// Функция загрузки направлений
async function loadDirections() {
  try {
    const response = await axios.get("/api/directions");
    console.log("Направления получены:", response.data); // для отладки
    // Если /api/directions возвращает массив, сохраняем сразу
    directions.value = response.data;
  } catch (error) {
    console.error("Ошибка при загрузке направлений:", error);
    globalNotification.categoryMessage = "Ошибка при загрузке направлений";
    globalNotification.type = "error";
  }
}

/* =====================================
   10. onMounted
===================================== */
onMounted(() => {
  editorCreate.value = new EditorJS({
    holder: "editorjs-create",
    placeholder: "Добавьте контент для нового курса...",
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

  const urlParams = new URLSearchParams(window.location.search);
  const verifiedUserParam = urlParams.get("verifiedUser");
  if (verifiedUserParam) {
    try {
      const pureBase64 = decodeURIComponent(verifiedUserParam);
      const decodedString = base64ToUtf8(pureBase64);
      const verifiedUserData = JSON.parse(decodedString);
      localStorage.setItem("user", JSON.stringify(verifiedUserData));
      localStorage.removeItem("pendingUser");
      console.log(
        "✅ Email подтвержден, данные пользователя сохранены:",
        verifiedUserData
      );
    } catch (error) {
      console.error("❌ Ошибка при декодировании verifiedUserParam:", error);
      globalNotification.categoryMessage = "Ошибка при подтверждении email";
      globalNotification.type = "error";
    }
  }
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    try {
      user.value = JSON.parse(storedUser);
      console.log("✅ Пользователь авторизован:", user.value);
    } catch (error) {
      console.error("❌ Ошибка при парсинге localStorage:", error);
      globalNotification.categoryMessage =
        "Ошибка при загрузке данных пользователя";
      globalNotification.type = "error";
    }
  } else {
    console.warn("⚠️ Пользователь НЕ авторизован.");
  }
  const savedIndex = localStorage.getItem("activeIndex");
  if (savedIndex !== null) {
    activeIndex.value = parseInt(savedIndex, 10);
  }

  // Загружаем все необходимые данные
  loadUsers();
  loadCourses();
  loadLanguages();
  loadDirections();

  // Инициализация EditorJS для создания новости
  editorNews = new EditorJS({
    holder: "editorjs-news", // Элемент с этим id должен быть в шаблоне
    placeholder: "Введите текст новости...",
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
});

/* =====================================
   11. Добавление категории языка (пример)
===================================== */
const languageCategory = ref("");
async function createLanguageCategory() {
  try {
    const response = await axios.post("/api/languages", {
      name: languageCategory.value,
    });
    globalNotification.categoryMessage =
      response.data.message || "Категория успешно создана!";
    globalNotification.type = "success";
    languageCategory.value = "";
  } catch (error) {
    console.error("Ошибка при создании категории:", error);
    globalNotification.categoryMessage =
      "Заполните все поля для добавления категории";
    globalNotification.type = "error";
  }
}

/* =====================================
   12. Добавление нового направления
===================================== */
const directionName = ref("");
const directionDescription = ref("");
async function submitDirection() {
  try {
    const response = await axios.post("/api/directions", {
      name: directionName.value,
      description: directionDescription.value,
    });
    globalNotification.categoryMessage =
      response.data.message || "Направление успешно создано!";
    globalNotification.type = "success";
    // Очистка формы
    directionName.value = "";
    directionDescription.value = "";
  } catch (error) {
    console.error("Ошибка при создании направления:", error);
    globalNotification.categoryMessage =
      "Заполните все поля для добавления направления";
    globalNotification.type = "error";
  }
}

/* =====================================
   13. Дополнительная навигация
===================================== */
function goToCourse(courseId) {
  console.log("Переход на курс с id:", courseId);
  window.location.href = `/cpp/${courseId}`;
}

/* =====================================
   14. Добавление FAQ
   (Новый блок: вставлено после раздела "13. Дополнительная навигация")
===================================== */
const faq = ref({
  question: "",
  answer: ""
});

async function submitFaq() {
  try {
    const response = await axios.post("/api/faqs", faq.value);
    console.log("FAQ добавлен:", response.data);
    globalNotification.categoryMessage = "FAQ успешно создан";
    globalNotification.type = "success";
    // Очистка формы
    faq.value.question = "";
    faq.value.answer = "";
  } catch (error) {
    console.error("Ошибка при добавлении FAQ:", error);
    globalNotification.categoryMessage = "Заполните все поля, для добавление FAQ";
    globalNotification.type = "error";
  }
}

/* =====================================
   15. Создание новости (News)
   (Новый блок: вставлено после раздела "14. Добавление FAQ")
===================================== */
const newNews = ref({
  title: "",
  summary: "",
  newsImage: null,
  editorData: {}
});

// Обработка выбора файла для изображения новости
function onNewsImageChange(e) {
  const file = e.target.files[0] || null;
  console.log("Выбранный файл:", file);
  newNews.value.newsImage = file;
}


// Переменная для EditorJS для новостей
let editorNews = null;

// Функция отправки формы для создания новости
async function submitNews() {
  try {
    // Сохраняем данные из EditorJS
    const editorData = await editorNews.save();
    newNews.value.editorData = editorData;

    // Формируем данные для отправки
    const formData = new FormData();
    formData.append("title", newNews.value.title);
    formData.append("content", newNews.value.content);
    formData.append("editor_data", JSON.stringify(newNews.value.editorData));

    // Если загружено изображение новости, добавляем его в форму
    if (newNews.value.newsImage) {
      formData.append("image", newNews.value.newsImage);
    }

    // Отправляем POST-запрос для создания новости на сервер
    const response = await axios.post("/api/news", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    console.log("Новость создана:", response.data);
    globalNotification.categoryMessage = "Новость успешно создана";
    globalNotification.type = "success";

    // Очистка формы: сбрасываем поля объекта newNews
    newNews.value = {
      title: "",
      summary: "",
      newsImage: null,
      editorData: {}
    };

    // Очистка EditorJS: сброс содержимого редактора
    editorNews.clear();
  } catch (error) {
    console.error("Ошибка при создании новости:", error);
    globalNotification.categoryMessage = "Ошибка при создании новости";
    globalNotification.type = "error";
  }
}
</script>


<style scoped>

::v-deep .multiselect__tags {
    overflow: hidden;
   padding: 12px 40px 0 12px;/* или больше, чтобы текст расположился ниже */
}
::v-deep .multiselect__input {
    margin-top: 15px;
  padding: 10px; /* или больше, чтобы текст расположился ниже */
}
::v-deep .multiselect__option {
  display: flex;
  align-items: center;
  cursor: pointer;
  gap: 8px;
  margin: 2px 0;
  padding: 6px 8px;
  border-radius: 6px;
  transition: background-color 0.3s, transform 0.2s;
}

/* Лёгкий эффект при наведении */
::v-deep .multiselect__option:hover {
  background-color: #f7f5fa; /* светлый оттенок */
  transform: scale(1.01);
}

/* Квадратик-превращённый-в-пузырь */
::v-deep .checkbox {
  width: 18px;
  height: 18px;
  border-radius: 50%;         /* делаем круг вместо квадрата */
  border: 2px solid #b4b0c4;  /* пастельный фиолетовый */
  background-color: transparent;
  transition: background-color 0.3s, border-color 0.3s;
}

/* Когда пункт выбран, цвет пузыря меняется */
::v-deep .checkbox.checkbox-checked {
  background-color: #a26ce9; /* насыщенный пастельный фиолетовый */
  border-color: #a26ce9;
}

/* Обёртка для SVG */
::v-deep .checkmark {
  position: relative;
  display: inline-block;
  width: 18px;
  height: 18px;
  /* Можно наложить иконку поверх круга 
     через position: absolute, если хотите */
}

/* SVG-галочка */
::v-deep .checkmark__icon {
  display: none;
  fill: #fff;
  width: 18px;
  height: 18px;
}

/* Показываем галочку, когда чекбокс залит */
::v-deep .checkbox.checkbox-checked + .checkmark .checkmark__icon {
  display: block;
}

/* Название языка */
::v-deep .option-name {
  font-size: 14px;
  color: #333;
}

/* Заголовок (слот selection) 
   где перечисляются выбранные языки */
::v-deep .selection-header {
  font-weight: 500;
  color: #6b6680;
}

::v-deep .selection-header{
    margin: 0 0 20px;
}

.page__title {
    margin: 0 0 60px;
}

h2 {
    font-size: 30px;
    margin: 0 0 25px;
}

.option-form {
    margin-bottom: 5px;
}

.course-form-container {
    margin: 35px auto;
    font-family: Arial, sans-serif;
}

.course-form,
.edit-course-form {
    display: grid;
    grid-template-columns: 1fr;
    width: 700px;
    gap: 20px;
    margin: 0 auto 40px;
}

.edit-course-form--small{
    display: grid;
    grid-template-columns: 1fr;
    width: 100%;
    gap: 20px;
    margin: 0 auto 40px;
}

.category{
    display: grid;
    grid-template-columns: repeat(2, 600px);
    justify-content: space-evenly;
}

.edit-course-form-container {
    width: 100%;
}

.form-column {
    flex: 1;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-family: JanoSansProLight;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
}

.form-label--small {
    font-family: JanoSansProLight;
    font-weight: bold;
    margin-bottom: 8px;
    text-align: center;
    color: #333;
}

.form-input,
.form-textarea {
    font-family: JanoSansProLight;
    outline: none;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

.form-textarea {
    resize: none;
    min-height: 80px;
}

.editor-container {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-height: 150px;
    background-color: #fff;
}

.form-button {
    width: 700px;
    background-color: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin: 0 auto;
}
.form-button--small{
    width: 100%;
    background-color: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin: 0 auto;
}

.form-button:hover {
    background-color: #0056b3;
}

.user-block {
    width: 100%;
}

.menu {
    width: 1440px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.container {
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.filters {
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Стилизация контейнера select */
.role-filter {
    /* Установите максимальную ширину */
    padding: 5px 6px;
    font-size: 16px;
    border: 1px solid #d0d0d0;
    border-radius: 6px;
    background-color: #f8f9fa;
    /* Светлый фон */
    color: #333;
    outline: none;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

/* Стилизация при наведении */
.role-filter:hover {
    border-color: #007bff;
    /* Голубой оттенок */
}

/* Стилизация при фокусе */
.role-filter:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.4);
}

/* Стилизация опций */
.role-filter option {
    background-color: #ffffff;
    padding: 10px;
    font-size: 16px;
}

.aside__menu {
    border-radius: 15px;
    height: 100%;
    padding: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.aside__list {
    display: flex;
    gap: 20px;
}

.aside__item {
    padding: 5px;
}

.aside__link {
    color: #575adf;
    text-decoration: none;
    transition: 0.5s;
}

.aside__link--active {
    color: #000;
    text-decoration: underline;
}

.aside__link:hover {
    transition: 0.5s;
    text-decoration: underline;
}

table.light-push-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.light-push-table th,
.light-push-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e0e0e0;
    text-align: left;
    font-size: 14px;
    white-space: nowrap;
    /* Запрещает перенос текста */
    text-overflow: ellipsis;
    /* Добавляет многоточие при обрезке */
}

.light-push-table th {
    text-align: center;
    background-color: #f0f8ff;
    /* Нежно-голубой цвет */
    font-size: 17px;
    font-weight: 600;
    border-right: 1px solid #d0d0d0;
    padding: 14px;
}

.light-push-table td {
    border-right: 1px solid #f0f0f0;
}

.light-push-table tbody tr:last-child td {
    border-bottom: none;
}

/* Выравнивание номера по центру и фиксированная ширина */
.light-push-table td.number-cell {
    text-align: center;
    font-weight: bold;
    font-size: 15px;
    width: 120px;
    /* Фиксированная ширина для номера */
}

/* Анимация при наведении */
@keyframes rowHover {
    from {
        background-color: #ffffff;
        transform: scale(1);
    }

    to {
        background-color: #e0f7fa;
        transform: scale(1.02);
    }
}

.light-push-table tbody tr:hover {
    animation: rowHover 0.3s ease forwards;
}

/* Убираем рамку справа у последнего столбца */
.light-push-table th:last-child,
.light-push-table td:last-child {
    border-right: none;
}

/* Стили модального окна */
.modal-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: flex-start;
    /* Прижимаем окно к верхней границе, если контента много */
    justify-content: center;
    padding: 40px 20px;
    /* Отступы вокруг окна */
    overflow-y: auto;
    /* Скролл при большом контенте */
}

.modal-content {
    background: #fff;
    width: 900px;
    max-width: 90%;
    /* Убираем max-height и overflow-y */
    margin: 40px 0;
    /* Дополнительные отступы сверху/снизу */
    padding: 20px;
    border-radius: 8px;
    position: relative;
}

.close-button {
    position: absolute;
    top: 18px;
    right: 18px;
    background: transparent;
    border: none;
    font-size: 20px;
    cursor: pointer;
}

/* From Uiverse.io by lenin55 */
</style>
