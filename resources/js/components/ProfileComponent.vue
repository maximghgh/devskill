<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <!-- Верхняя панель: роль-таб (слева) + кнопки-разделы (справа) -->
                <section class="cabinet-head">
                        <!-- роль-таб: аккаунт ученика, родитель переключается на «Родитель» -->
                        <div class="profile-person-tabs" role="tablist">
                            <button
                                type="button"
                                class="profile-person-tabs__button"
                                :class="{ active: activeInfoTab === 'student' }"
                                @click="activeInfoTab = 'student'"
                            >
                                Обучающийся
                            </button>
                            <button
                                type="button"
                                class="profile-person-tabs__button"
                                :class="{ active: activeInfoTab === 'parent' }"
                                @click="activeInfoTab = 'parent'"
                            >
                                Родитель
                            </button>
                        </div>
                        <!-- кнопки-разделы (для родителя добавляется «Оплата курсов») -->
                        <div class="cabinet-sections">
                            <button
                                v-if="activeInfoTab === 'parent'"
                                type="button"
                                class="cabinet-sections__btn"
                                :class="{ active: activeSection === 'payment' }"
                                @click="activeSection = 'payment'"
                            >
                                Оплата курсов
                            </button>
                            <button
                                type="button"
                                class="cabinet-sections__btn"
                                :class="{ active: activeSection === 'grades' }"
                                @click="activeSection = 'grades'"
                            >
                                Табель успеваемости
                            </button>
                            <button
                                type="button"
                                class="cabinet-sections__btn"
                                :class="{ active: activeSection === 'courses' }"
                                @click="activeSection = 'courses'"
                            >
                                Мои курсы
                            </button>
                            <button
                                type="button"
                                class="cabinet-sections__btn"
                                :class="{ active: activeSection === 'personal' }"
                                @click="activeSection = 'personal'"
                            >
                                Личные данные
                            </button>
                        </div>
                </section>
                <section class="infoblock block-tab block-tab_active">
                    <div class="infoblock__wrapper">
                        <div class="infoblock__inner">
                            <div class="infoblock-title">{{ sectionTitle }}</div>
                            <!-- ====== РАЗДЕЛ: ЛИЧНЫЕ ДАННЫЕ ====== -->
                            <div v-show="activeSection === 'personal'" class="infoblock__info">
                                <div class="infoblock__info-name">
                                    <div class="infoblock__info-name-image">
                                        <img
                                            :src="photoSrc"
                                            alt="Фото пользователя"
                                        />
                                    </div>
                                    <form @submit.prevent="saveProfile" class="infoblock__info-form">
                                        <label class="infoblock__info-file">
                                            <input type="file" name="file" accept=".jpg, .png, .webp, .jpeg" @change="onFileSelected" />
                                            <span class="infoblock__info-filebtn">Загрузить фото</span>
                                            <span class="infoblock__info-filetext"></span>
                                        </label>
                                        <input type="submit" value="Сохранить" class="infoblock__button" />
                                    </form>
                                </div>
                                <form class="infoblock__data" @submit.prevent="saveProfile">
                                    <!-- Данные обучающегося (подтягиваются из списка при регистрации) -->
                                    <div
                                        v-show="activeInfoTab === 'student'"
                                        class="infoblock__data-top profile-person-panel"
                                    >
                                        <div class="custom-input">
                                            <input
                                                type="text"
                                                v-model="studentForm.name"
                                                placeholder="ФИО обучающегося"
                                            />
                                            <label class="custom-label">ФИО обучающегося</label>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                type="email"
                                                v-model="studentForm.email"
                                                placeholder="E-mail"
                                            />
                                            <label class="custom-label">E-mail</label>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                class="profile-date-input"
                                                type="date"
                                                v-model="studentForm.birthday"
                                                placeholder="Дата рождения"
                                            />
                                            <label class="custom-label">Дата рождения</label>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                v-model="studentForm.phone"
                                                v-mask="'+7 (###) ###-##-##'"
                                                placeholder="+7 999 999-99-99"
                                            />
                                            <label class="custom-label">Телефон</label>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                type="text"
                                                v-model="studentForm.country"
                                                placeholder="Страна + город"
                                            />
                                            <label class="custom-label">Местоположение</label>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                class="custom-status"
                                                type="text"
                                                :value="studentStatus"
                                                readonly
                                            />
                                            <label class="custom-label">Статус</label>
                                        </div>
                                    </div>

                                    <!-- Данные родителя: заполняются вручную, поля обязательны -->
                                    <div
                                        v-show="activeInfoTab === 'parent'"
                                        class="infoblock__data-top profile-person-panel"
                                    >
                                        <div class="custom-input">
                                            <input
                                                type="text"
                                                v-model="parentForm.name"
                                                :class="{ 'is-invalid': parentErrors.name }"
                                                placeholder="ФИО родителя"
                                            />
                                            <label class="custom-label">ФИО родителя *</label>
                                            <span v-if="parentErrors.name" class="field-error">
                                                {{ parentErrors.name }}
                                            </span>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                type="email"
                                                v-model="parentForm.email"
                                                :class="{ 'is-invalid': parentErrors.email }"
                                                placeholder="E-mail"
                                            />
                                            <label class="custom-label">E-mail *</label>
                                            <span v-if="parentErrors.email" class="field-error">
                                                {{ parentErrors.email }}
                                            </span>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                v-model="parentForm.phone"
                                                v-mask="'+7 (###) ###-##-##'"
                                                :class="{ 'is-invalid': parentErrors.phone }"
                                                placeholder="+7 999 999-99-99"
                                            />
                                            <label class="custom-label">Телефон *</label>
                                            <span v-if="parentErrors.phone" class="field-error">
                                                {{ parentErrors.phone }}
                                            </span>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                class="profile-date-input"
                                                type="date"
                                                v-model="parentForm.birthday"
                                                placeholder="Дата рождения"
                                            />
                                            <label class="custom-label">Дата рождения</label>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                type="text"
                                                v-model="parentForm.country"
                                                placeholder="Страна + город"
                                            />
                                            <label class="custom-label">Местоположение</label>
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                class="custom-status"
                                                type="text"
                                                :value="parentStatus"
                                                readonly
                                            />
                                            <label class="custom-label">Статус</label>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- ====== РАЗДЕЛ: ОПЛАТА КУРСОВ (родитель) ====== -->
                            <div v-show="activeSection === 'payment'" class="cabinet-pane">
                                <div class="profile-payment profile-payment--single">
                                    <a
                                        class="profile-payment__link"
                                        href="https://istu.ru/payment"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >Перейти к оплате в ИжГТУ</a>
                                </div>

                                <!-- ====== КУДА ПРИХОДЯТ ЧЕКИ ====== -->
                                <div class="payments-note">
                                    <svg
                                        class="payments-note__icon"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect x="2" y="4" width="20" height="16" rx="3" />
                                        <path d="m3 7 9 6 9-6" />
                                    </svg>
                                    <div class="payments-note__body">
                                        <p class="payments-note__title">
                                            Отправьте чек об оплате
                                        </p>
                                        <p class="payments-note__text">
                                            После оплаты отправьте чек на почту
                                            университета
                                            <a
                                                class="payments-note__email"
                                                href="mailto:info@istu.ru"
                                                >info@istu.ru</a
                                            >. Так мы быстрее подтвердим оплату.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ====== РАЗДЕЛ: МОИ КУРСЫ ====== -->
                            <div v-show="activeSection === 'courses'" class="cabinet-pane">
                                <div class="course__cards_cabinet" v-if="purchasedCourses.length > 0">
                                    <div
                                        v-for="course in purchasedCourses"
                                        :key="course.id"
                                        :class="['course__cardss','course__card_personal','course__card_bg1', getDirectionCardClass(course.direction)]"
                                    >
                                        <div class="course__card-image">
                                            <img :src="course.card_image ? course.card_image : '/img/no_foto.jpg'" alt="Изображение курса" />
                                        </div>
                                        <div class="course__card-title">{{ course.card_title }}</div>
                                        <div class="course__card-buttons">
                                            <div class="card__info-ch">
                                                <div class="course__card-task">
                                                    <p>Пройдено тем:</p>
                                                    <p>{{ getCourseProgress(course).completedTopics }}/{{ getCourseProgress(course).totalTopics }}</p>
                                                </div>
                                                <div class="course__card-task">
                                                    <p>Решено заданий:</p>
                                                    <p>{{ getCourseProgress(course).completedTasks }}/{{ getCourseProgress(course).totalTasks }}</p>
                                                </div>
                                            </div>
                                            <div class="menu__button">
                                                <a :href="`/content/${course.id}`" class="button button_transparent button_transparent--xl">Приступить к курсу</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Курсы, по которым заявка ещё в работе -->
                                <div class="pending-courses" v-if="pendingApplications.length">
                                    <div class="pending-courses__title">Заявки в работе</div>
                                    <div class="pending-courses__list">
                                        <div
                                            v-for="application in pendingApplications"
                                            :key="application.id"
                                            class="pending-course"
                                        >
                                            <div class="pending-course__info">
                                                <span class="pending-course__name">
                                                    {{ applicationCourseTitle(application) }}
                                                </span>
                                                <span class="pending-course__status">
                                                    {{ application.status_label }}
                                                </span>
                                            </div>
                                            <div class="pending-course__actions">
                                                <span
                                                    v-if="application.course?.price"
                                                    class="pending-course__price"
                                                >
                                                    {{ formatMoney(application.course.price) }}
                                                </span>
                                                <a
                                                    v-if="application.status === 'awaiting_payment'"
                                                    class="pending-course__pay"
                                                    href="https://istu.ru/payment"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >Перейти к оплате</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="!purchasedCourses.length && !pendingApplications.length"
                                    class="cabinet-empty"
                                >
                                    Вы пока не выбрали ни одного курса.
                                </div>

                                <!-- ====== КУДА ПРИХОДЯТ ЧЕКИ ====== -->
                                <div class="payments-note" v-if="pendingApplications.length">
                                    <svg
                                        class="payments-note__icon"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect x="2" y="4" width="20" height="16" rx="3" />
                                        <path d="m3 7 9 6 9-6" />
                                    </svg>
                                    <div class="payments-note__body">
                                        <p class="payments-note__title">
                                            Отправьте чек об оплате
                                        </p>
                                        <p class="payments-note__text">
                                            После оплаты отправьте чек на почту
                                            университета
                                            <a
                                                class="payments-note__email"
                                                href="mailto:info@istu.ru"
                                                >info@istu.ru</a
                                            >. Так мы быстрее подтвердим оплату.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ====== РАЗДЕЛ: ТАБЕЛЬ УСПЕВАЕМОСТИ (read-only) ====== -->
                            <div v-show="activeSection === 'grades'" class="cabinet-pane">
                                <div class="cabinet-grades__filter">
                                    <label class="cabinet-grades__label">Курс</label>
                                    <select v-model="gradeCourseId" class="cabinet-grades__select">
                                        <option value="">Вариант из списка</option>
                                        <option v-for="c in gradeCourses" :key="c.id" :value="c.id">
                                            {{ c.card_title || c.course_name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="cabinet-grades__table-wrap" v-if="gradeRows.length">
                                    <table class="cabinet-grades__table cabinet-grades__table--dates">
                                        <thead>
                                            <tr>
                                                <th class="cabinet-grades__corner">Урок</th>
                                                <th v-for="(row, i) in gradeRows" :key="i">{{ row.lesson }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="cabinet-grades__corner">Балл</td>
                                                <td
                                                    v-for="(row, i) in gradeRows"
                                                    :key="i"
                                                    :class="scoreClass(row.score)"
                                                >{{ row.score === null || row.score === undefined ? '—' : row.score }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="cabinet-empty">
                                    {{ gradeCourseId ? 'Нет занятий по выбранному курсу.' : 'Выберите курс, чтобы увидеть табель.' }}
                                </div>

                                <!-- Отзыв преподавателя о ребёнке -->
                                <div v-if="gradeCourseId && teacherReview" class="teacher-review">
                                    <div class="teacher-review__title">Отзыв от преподавателя</div>
                                    <p class="teacher-review__text">{{ teacherReview }}</p>
                                </div>
                            </div>

                            <!-- модалка «Данные изменены» -->
                            <div v-if="showModal" class="modal-overlay">
                                <div class="modal-content">
                                    <h2>Данные изменены</h2>
                                    <p>Перейти в профиль:
                                        <a class="modal__link" href="/profile">Вернуться в профиль</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from "vue";
import axios from "axios";

import { getDirectionCardClass } from "@/utils/courseDirection";
// Управление модальным окном
const showModal = ref(false);
// Данные пользователя и форма для редактирования профиля
const user = ref({});
const activeInfoTab = ref("parent");

const emptyPersonForm = () => ({
  name: "",
  email: "",
  birthday: "",
  phone: "",
  country: "",
});

const parentForm = reactive(emptyPersonForm());
const studentForm = reactive(emptyPersonForm());

const currentRole = computed(() => Number(user.value?.role) || 1);
const parentStatus = computed(() => "Родитель");
const studentStatus = computed(() => "Обучающийся");

// --- Оплата обучения (таб родителя) ---
const children = ref([]);
const formatMoney = (value) =>
  new Intl.NumberFormat("ru-RU", {
    style: "currency",
    currency: "RUB",
    maximumFractionDigits: 0,
  }).format(Number(value) || 0);

// --- Разделы кабинета (переключаются на странице) ---
const activeSection = ref("personal"); // personal | courses | grades | payment
// заголовок блока = название активного раздела
const sectionTitles = {
  personal: "Личные данные",
  courses: "Мои курсы",
  grades: "Табель успеваемости",
  payment: "Оплата курсов",
};
const sectionTitle = computed(() => sectionTitles[activeSection.value] || "Личный кабинет");
// при уходе с таба «Родитель» сбрасываем раздел оплаты
watch(activeInfoTab, (tab) => {
  if (tab !== "parent" && activeSection.value === "payment") {
    activeSection.value = "personal";
  }
});

// --- Заявки на курсы: этап до того, как курс открыт ---
const applications = ref([]);
const loadApplications = async () => {
  if (!user.value.id) return;
  try {
    const { data } = await axios.get(
      `/api/user/${user.value.id}/course-applications`
    );
    applications.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error("Ошибка загрузки заявок:", error);
    applications.value = [];
  }
};

// Выданные курсы приходят отдельно, здесь — только те, что ещё в работе.
const pendingApplications = computed(() =>
  applications.value.filter((a) => a.status !== "issued")
);

const applicationCourseTitle = (application) =>
  application.course?.card_title ||
  application.course?.course_name ||
  "Курс";

// --- Мои курсы ---
const purchasedCourses = ref([]);
const loadCourses = async () => {
  if (!user.value.id) return;
  try {
    const { data } = await axios.get(`/api/user/${user.value.id}/purchased-courses`);
    purchasedCourses.value = data.courses || [];
  } catch (error) {
    console.error("Ошибка загрузки курсов:", error);
    purchasedCourses.value = [];
  }
};

// Карточки «Мои курсы» — хелперы как в старом кабинете
function getCourseProgress(course) {
  let totalTopics = 0, completedTopics = 0, totalTasks = 0, completedTasks = 0;
  if (course.topics && course.topics.length) {
    totalTopics = course.topics.length;
    course.topics.forEach((topic) => {
      if (topic.chapters && topic.chapters.length) {
        if (topic.chapters.every((ch) => ch.is_completed)) completedTopics++;
        topic.chapters.forEach((ch) => {
          if (ch.type === "task") { totalTasks++; if (ch.is_completed) completedTasks++; }
        });
      }
    });
  }
  return { completedTopics, totalTopics, completedTasks, totalTasks };
}

// --- Табель успеваемости (read-only): уроки в шапке, баллы снизу ---
const gradeCourseId = ref("");
const gradeRows = ref([]); // [{ lesson, score }]
const gradeCourses = computed(() => purchasedCourses.value);
// цвет балла: зелёный (высокий) / оранжевый (средний) / красный (низкий)
const scoreClass = (s) => {
  if (s === null || s === undefined) return "";
  if (s >= 85) return "grade--high";
  if (s >= 60) return "grade--mid";
  return "grade--low";
};
// отзыв преподавателя по выбранному курсу
const teacherReview = ref("");
const loadGrades = async () => {
  gradeRows.value = [];
  teacherReview.value = "";
  if (!gradeCourseId.value || !user.value.id) return;
  try {
    const { data } = await axios.get(
      `/api/user/${user.value.id}/course/${gradeCourseId.value}/grades`
    );
    gradeRows.value = (data.lessons || []).map((l) => ({
      lesson: l.lesson || "Без названия",
      score: l.score,
    }));
    teacherReview.value = data.review || "";
  } catch (error) {
    console.error("Ошибка загрузки табеля:", error);
    gradeRows.value = [];
    teacherReview.value = "";
  }
};
watch(gradeCourseId, () => loadGrades());

// Переменная для выбранного файла
const selectedFile = ref(null);
const previewPhotoUrl = ref("");
const photoVersion = ref(Date.now());

const photoSrc = computed(() => {
  if (previewPhotoUrl.value) {
    return previewPhotoUrl.value;
  }

  if (!user.value.photo) {
    return "/img/no_foto.jpg";
  }

  return `/storage/${user.value.photo}?v=${photoVersion.value}`;
});

const clearPreviewPhoto = () => {
  if (!previewPhotoUrl.value) {
    return;
  }

  URL.revokeObjectURL(previewPhotoUrl.value);
  previewPhotoUrl.value = "";
};

const getResponseUser = (payload) => payload?.data || payload || {};

const toDateInputValue = (value) => {
  if (!value) {
    return "";
  }

  return String(value).slice(0, 10);
};

const normalizePersonInfo = (info) => ({
  name: info?.name || "",
  email: info?.email || "",
  birthday: toDateInputValue(info?.birthday),
  phone: info?.phone || "",
  country: info?.country || "",
});

const hasPersonInfo = (info) => {
  if (!info || typeof info !== "object") {
    return false;
  }

  return ["name", "email", "birthday", "phone", "country"].some(
    (key) => Boolean(info[key])
  );
};

const fillPersonForm = (target, source) => {
  const normalized = normalizePersonInfo(source);
  Object.assign(target, normalized);
};

const accountInfoFromUser = (userData) =>
  normalizePersonInfo({
    name: userData?.name,
    email: userData?.email,
    birthday: userData?.birthday,
    phone: userData?.phone,
    country: userData?.country,
  });

const applyUserData = (userData) => {
  if (!userData || !userData.id) {
    return;
  }

  user.value = userData;
  activeInfoTab.value = Number(userData.role) === 1 ? "student" : "parent";

  const accountInfo = accountInfoFromUser(userData);
  const parentInfo = hasPersonInfo(userData.parent_info)
    ? userData.parent_info
    : Number(userData.role) === 4
    ? accountInfo
    : {};
  const studentInfo = hasPersonInfo(userData.student_info)
    ? userData.student_info
    : Number(userData.role) === 1
    ? accountInfo
    : {};

  fillPersonForm(parentForm, parentInfo);
  fillPersonForm(studentForm, studentInfo);
};

// onMounted – загрузка данных из localStorage и API
onMounted(async () => {
  // Попытка загрузить пользователя из localStorage
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    try {
      const parsedUser = JSON.parse(storedUser);
      applyUserData(parsedUser);
    } catch (error) {
      console.error("Ошибка при парсинге пользователя из localStorage:", error);
    }
  }

  // Загружаем актуальные данные из API
  await loadUserData();
  await loadChildren();
  await loadCourses();
  await loadApplications();
});

onBeforeUnmount(() => {
  clearPreviewPhoto();
});

// Загрузка данных пользователя из API
const loadUserData = async () => {
  if (!user.value.id) {
    return;
  }

  try {
    const response = await axios.get(`/api/users/${user.value.id}`);
    const userData = getResponseUser(response.data);
    applyUserData(userData);
    localStorage.setItem("user", JSON.stringify(userData));
  } catch (error) {
    console.error("Ошибка загрузки данных:", error);
  }
};

// Загрузка детей родителя (для таба «Родитель»)
const loadChildren = async () => {
  if (!user.value.id || Number(user.value.role) !== 4) {
    return;
  }
  try {
    const { data } = await axios.get(`/api/user/${user.value.id}/children`);
    children.value = data.children || [];
  } catch (error) {
    console.error("Ошибка загрузки детей:", error);
  }
};

// --- Валидация данных родителя (обязательные поля) ---
const parentErrors = reactive({ name: "", email: "", phone: "" });

const isFilled = (value) => String(value || "").trim().length > 0;

/** Заполнена ли форма родителя хотя бы частично. */
const parentFormTouched = computed(() =>
  ["name", "email", "phone", "birthday", "country"].some((key) =>
    isFilled(parentForm[key])
  )
);

function validateParentForm() {
  parentErrors.name = "";
  parentErrors.email = "";
  parentErrors.phone = "";

  // Пустую форму не проверяем: родитель мог ещё не дойти до этой вкладки.
  if (!parentFormTouched.value && currentRole.value !== 4) return true;

  if (!isFilled(parentForm.name)) {
    parentErrors.name = "Укажите ФИО родителя";
  }
  if (!isFilled(parentForm.email)) {
    parentErrors.email = "Укажите e-mail";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(parentForm.email.trim())) {
    parentErrors.email = "Некорректный e-mail";
  }
  if (!isFilled(parentForm.phone)) {
    parentErrors.phone = "Укажите телефон";
  }

  return !parentErrors.name && !parentErrors.email && !parentErrors.phone;
}

const saveProfile = async () => {
  try {
    if (!validateParentForm()) {
      activeInfoTab.value = "parent";
      return;
    }
    if (selectedFile.value) {
      await uploadPhoto();
    }
    await updateProfile();
  } catch (error) {
    console.error("Ошибка при сохранении профиля:", error);
  }
};

// Функция обновления профиля
const updateProfile = async () => {
  try {
    const primaryInfo = currentRole.value === 4 ? parentForm : studentForm;
    const payload = {
      id: user.value.id,
      name: primaryInfo.name || user.value.name || "",
      email: primaryInfo.email || null,
      birthday: primaryInfo.birthday || null,
      phone: primaryInfo.phone || null,
      country: primaryInfo.country || null,
      student_info: normalizePersonInfo(studentForm),
    };
    // Пустую форму родителя не отправляем — иначе затрём уже сохранённые данные.
    if (parentFormTouched.value) {
      payload.parent_info = normalizePersonInfo(parentForm);
    }
    const response = await axios.post("/api/profile", payload);
    const updatedUser = getResponseUser(response.data.user);
    // Убираем пароль из данных, если он есть
    delete updatedUser.password;
    // Обновляем localStorage и реактивные данные пользователя
    localStorage.setItem("user", JSON.stringify(updatedUser));
    applyUserData(updatedUser);
    // Показываем модальное окно
    showModal.value = true;
  } catch (error) {
    console.error("Ошибка сохранения:", error.response?.data || error.message);
  }
};

const closeModal = () => {
  showModal.value = false;
};

// Функция обработки выбора файла
function onFileSelected(event) {
  const file = event.target.files?.[0];
  if (!file) {
    return;
  }

  clearPreviewPhoto();
  selectedFile.value = file;
  previewPhotoUrl.value = URL.createObjectURL(file);
}

// Функция загрузки фото
async function uploadPhoto() {
  if (!selectedFile.value) {
    alert("Сначала выберите файл!");
    return;
  }
  try {
    const formData = new FormData();
    formData.append("file", selectedFile.value);

    // Берём ID пользователя из реактивной переменной user (или localStorage, если необходимо)
    const userId =
      user.value.id ||
      (localStorage.getItem("user") && JSON.parse(localStorage.getItem("user")).id);
      
    const response = await axios.post(`/api/users/${userId}/photo`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    const updatedUser = getResponseUser(response.data.user);
    console.log("Фото обновлено:", updatedUser);
    alert("Фото успешно загружено!");
    // Обновляем данные пользователя в localStorage и в реактивной переменной
    localStorage.setItem("user", JSON.stringify(updatedUser));
    applyUserData(updatedUser);
    photoVersion.value = Date.now();
    selectedFile.value = null;
    clearPreviewPhoto();
  } catch (error) {
    console.error("Ошибка при загрузке фото:", error);
    alert("Ошибка при загрузке фото.");
  }
}
</script>

<style>
/* Оплата обучения (таб родителя) */
.profile-payment {
    margin-top: 18px;
    padding: 16px 20px;
    border: 1px solid #6352C1;
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
/* Заявки в работе: курс ещё не открыт, показываем этап и цену */
.pending-courses {
    margin-top: 26px;
}
.pending-courses__title {
    margin-bottom: 14px;
    font-size: 20px;
    color: #2b2b3a;
}
.pending-courses__list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.pending-course {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 14px;
    background: #f5f4fb;
}
.pending-course__info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}
.pending-course__name {
    font-size: 16px;
    color: #2b2b3a;
    overflow-wrap: anywhere;
}
.pending-course__status {
    font-size: 14px;
    color: #6352c1;
}
.pending-course__actions {
    display: flex;
    align-items: center;
    gap: 16px;
}
.pending-course__price {
    font-size: 16px;
    font-weight: 600;
    color: #2b2b3a;
    white-space: nowrap;
}
.pending-course__pay {
    padding: 10px 18px;
    border-radius: 12px;
    background: #6352c1;
    color: #ffffff;
    font-size: 15px;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s ease;
}
.pending-course__pay:hover {
    background: #5343ab;
}

.profile-payment__info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.profile-payment__label {
    font-size: 15px;
    color: #333333;
}
.profile-payment__amount {
    font-size: 18px;
    font-weight: 600;
    color: #2e7d32;
}
.profile-payment__amount--debt {
    color: #c62828;
}
.profile-payment__link {
    align-self: flex-start;
    display: inline-block;
    padding: 10px 20px;
    background-color: #6352C1;
    color: #ffffff;
    border-radius: 10px;
    text-decoration: none;
    font-size: 15px;
    transition: opacity 0.2s ease;
}
.profile-payment__link:hover {
    opacity: 0.85;
}
/* Мои дети (таб родителя) */
/* ===== Кабинет: верхняя панель + кнопки-разделы ===== */
.cabinet-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    max-width: var(--max-width);
    margin: 20px auto 24px;
    padding: var(--padding);
    box-sizing: border-box;
}
.cabinet-head__user {
    display: flex;
    align-items: center;
    gap: 16px;
}
.cabinet-head__avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    object-fit: cover;
}
.cabinet-head__name {
    font-size: 28px;
    font-weight: 600;
    color: #2b2b3a;
}
.cabinet-head__controls {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.cabinet-sections {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-left: auto;
}
.cabinet-sections__btn {
    border: 0;
    cursor: pointer;
    padding: 12px 22px;
    border-radius: 14px;
    background: #b8b3d6;
    color: #ffffff;
    font-size: 15px;
    transition: background 0.2s ease;
}
.cabinet-sections__btn:hover {
    background: #8e87bd;
}
.cabinet-sections__btn.active {
    background: #6352c1;
}

/* ===== Кабинет: панели разделов ===== */
.cabinet-pane {
    padding: 4px 0 8px;
    min-width: 0;
    max-width: 100%;
}
.cabinet-empty {
    padding: 28px;
    color: #777777;
    font-size: 16px;
    background: #f5f4fb;
    border-radius: 14px;
}
/* Карточки «Мои курсы» — как в старом кабинете */
.course__cards_cabinet {
    display: grid;
    align-items: center;
    grid-template-columns: repeat(3, 350px);
    gap: 20px;
}
@media (max-width: 900px) {
    .course__cards_cabinet {
        grid-template-columns: 1fr;
    }
}
/* Карточки в кабинете узкие: при max-width 80% и шрифте 24px
   «Программирование» не влезало и рвалось посреди слова. */
.course__cardss .course__card-title {
    max-width: calc(100% - 64px);
    font-size: 20px;
}
.course__cardss {
    width: 100%;
    min-height: 280px;
    padding: 26px 31px;
    border-radius: 32px;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    /* цвет фона задаётся классом сложности (.course__card_bg-cyan/green/fiolet/orange из app.css) */
    background-image: url(/img/bg_1.png);
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}
.card__info-ch {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}
/* отступ между блоком оплаты и списком курсов */
.cabinet-pane .profile-children {
    margin-top: 22px;
}
.cabinet-grades__filter {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 420px;
    margin-bottom: 18px;
}
.cabinet-grades__label {
    font-size: 14px;
    color: #555555;
}
.cabinet-grades__select {
    height: 56px;
    border-radius: 14px;
    border: 2px solid #eeeef4;
    padding: 0 16px;
    font-size: 15px;
    background: #ffffff;
}
.cabinet-grades__table-wrap {
    max-width: 100%;
    overflow-x: auto;
}
.cabinet-grades__table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border: 1px solid #cfc9ea;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(87, 90, 223, 0.12);
}
.cabinet-grades__table th,
.cabinet-grades__table td {
    padding: 14px 18px;
    border: 1px solid #e0dcf2;
    text-align: center;
    white-space: nowrap;
}
.cabinet-grades__table th {
    background: #6352c1;
    color: #ffffff;
    font-weight: 600;
    font-size: 15px;
}
.cabinet-grades__table th:not(.cabinet-grades__corner) {
    min-width: 96px;
    max-width: 220px;
    white-space: normal;
}
.cabinet-grades__table td {
    background: #ffffff;
    color: #2b2b3a;
    font-weight: 700;
    font-size: 16px;
}
.cabinet-grades__corner {
    position: sticky;
    left: 0;
    background: #4b3fa3 !important;
    color: #ffffff !important;
    font-weight: 700;
    text-align: left;
}
.cabinet-grades__score {
    width: 120px;
}
.grade--high { color: #1e8e3e !important; }
.grade--mid  { color: #e8830c !important; }
.grade--low  { color: #d93025 !important; }

.profile-payment--top {
    margin-top: 0;
    margin-bottom: 26px;
}
.profile-parent-extra {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-bottom: 28px;
}
.profile-children {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.profile-children__list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
@media (max-width: 900px) {
    .profile-children__list {
        grid-template-columns: 1fr;
    }
}
.profile-children__title {
    font-size: 16px;
    font-weight: 600;
    color: #333333;
}
.profile-children__item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    border: 1px solid #e0ddf2;
    border-radius: 12px;
}
.profile-children__info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.profile-children__name {
    font-size: 15px;
    color: #333333;
}
.profile-children__contact {
    font-size: 13px;
    color: #888888;
}
.profile-children__debt {
    font-size: 15px;
    font-weight: 600;
    color: #2e7d32;
    white-space: nowrap;
}
.profile-children__debt--has {
    color: #c62828;
}

/* --- Валидация формы родителя --- */
.profile-person-panel__hint {
    width: 100%;
    font-size: 13px;
    color: #888;
    margin: 0 0 8px;
}
.custom-input input.is-invalid {
    border-color: #c62828;
}
.field-error {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #c62828;
}

/* --- Отзыв преподавателя --- */
.teacher-review {
    margin-top: 20px;
    padding: 14px 16px;
    border: 1px solid #ececec;
    border-left: 3px solid #6c5ce7;
    border-radius: 10px;
    background: #fafaff;
}
.teacher-review__title {
    font-size: 14px;
    font-weight: 600;
    color: #444;
    margin-bottom: 6px;
}
.teacher-review__text {
    font-size: 14px;
    color: #333;
    white-space: pre-line;
    margin: 0;
}

/* --- Инфо-блок: куда приходят чеки --- */
.payments-note {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-top: 26px;
    padding: 18px 20px;
    background: #f5f4fb;
    border-left: 4px solid #6352c1;
    border-radius: 14px;
}
.payments-note__icon {
    flex-shrink: 0;
    margin-top: 2px;
    color: #6352c1;
}
.payments-note__body {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.payments-note__title {
    margin: 0;
    font-size: 16px;
    color: #1a1a1a;
}
.payments-note__text {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    color: #555555;
}
.payments-note__email {
    color: #6352c1;
    text-decoration: underline;
    word-break: break-all;
}

/* Пустое состояние в разделе оплаты не должно прилипать к блоку выше */
.cabinet-empty--spaced {
    margin-top: 22px;
}

.custom-input {
    position: relative;
}
.custom-label {
    font-size: 14px;
    position: absolute;
    left: 20px;
    top: -8px;
    background-color: #ffffff;
    border: 1px solid #6352C1;
    border-radius: 10px;
    padding: 5px 10px;
}
.custom-status{
    color: #575adf;
}
.custom-status::placeholder {
    color: #575adf;
}
.profile-title-row {
    display: flex;
    align-items: center;
    gap: 28px;
    flex-wrap: wrap;
    justify-content: space-between;
}
.profile-person-tabs {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    margin: 0;
    border: 1px solid #d2d3f3;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.35);
    box-shadow: 0 12px 28px rgba(87, 90, 223, 0.08);
}
.profile-person-tabs__button {
    min-width: 0;
    height: 42px;
    padding: 0 22px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    color: #34364f;
    font: inherit;
    font-size: 18px;
    font-weight: 400;
    line-height: 1.2;
    white-space: nowrap;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}
.profile-person-tabs__button.active {
    background: #575adf;
    color: #ffffff;
    box-shadow: 0 8px 18px rgba(87, 90, 223, 0.22);
}
.profile-person-panel {
    width: 100%;
}
.profile-date-input::placeholder {
    text-align: center;
}

.modal-overlay {
  position: fixed;
  top: 0; 
  left: 0;
  right: 0; 
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5); 
  display: flex;
  align-items: center; 
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: #fff; 
  padding: 20px 30px;
  border-radius: 8px;
  width: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
}
.modal__link{
  color: #575adf;
  text-decoration: none;
}
.modal__link:hover{
  text-decoration: underline;
}
@media all and (max-width: 490px) {
    .profile-title-row {
        justify-content: center;
        gap: 18px;
    }
    .profile-person-tabs {
        display: flex;
        width: 100%;
    }
    .profile-person-tabs__button {
        min-width: 0;
        flex: 1;
        height: 40px;
        padding: 0 10px;
        font-size: 14px;
    }
    .infoblock__info-name-image img{
        width: 250px;
        height: 250px;
    }
    .infoblock__info-form {
        align-items: center;
        gap: 20px;
        justify-content: center;
    }
    .infoblock__inner{
      padding: 20px 20px 45px;
      margin: 0 0 25px;
    }
}
@media all and (max-width: 405px) {
    .infoblock__info-name-image img{
        width: 250px;
        height: 250px;
    }
    .infoblock__info-form {
        align-items: center;
        gap: 20px;
        justify-content: center;
    }
    .infoblock__inner{
      padding: 20px 20px 45px;
      margin: 0 0 25px;
    }
    .infoblock__info-filebtn,
    .infoblock__button{
      width: 120px;
      font-size: 0.7em;
    }
}
</style>
