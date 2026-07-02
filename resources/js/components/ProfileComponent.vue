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
                                    <!-- Личные данные = данные ученика (на обоих табах) -->
                                    <div
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
                                </form>
                            </div>

                            <!-- ====== РАЗДЕЛ: ОПЛАТА КУРСОВ (родитель) ====== -->
                            <div v-show="activeSection === 'payment'" class="cabinet-pane">
                                <div class="profile-payment">
                                    <div class="profile-payment__info">
                                        <span class="profile-payment__label">Задолженность по оплате</span>
                                        <span
                                            class="profile-payment__amount"
                                            :class="{ 'profile-payment__amount--debt': paymentDebt > 0 }"
                                        >{{ formattedDebt }}</span>
                                    </div>
                                    <!-- TODO: подключить редирект на оплату ИжГТУ, когда обсудим интеграцию. Пока только вёрстка. -->
                                    <a class="profile-payment__link" href="#" @click.prevent>Перейти к оплате в ИжГТУ</a>
                                </div>

                                <div class="profile-children" v-if="purchasedCourses.length">
                                    <div class="profile-children__title">Курсы</div>
                                    <div class="profile-children__list">
                                        <div
                                            v-for="course in purchasedCourses"
                                            :key="course.id"
                                            class="profile-children__item"
                                        >
                                            <div class="profile-children__info">
                                                <span class="profile-children__name">{{ course.card_title || course.course_name }}</span>
                                            </div>
                                            <span class="profile-children__debt">{{ formatMoney(course.price) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="cabinet-empty">Пока нет курсов для оплаты.</div>
                            </div>

                            <!-- ====== РАЗДЕЛ: МОИ КУРСЫ ====== -->
                            <div v-show="activeSection === 'courses'" class="cabinet-pane">
                                <div class="course__cards_cabinet" v-if="purchasedCourses.length > 0">
                                    <div
                                        v-for="course in purchasedCourses"
                                        :key="course.id"
                                        :class="['course__cardss','course__card_personal','course__card_bg1', difficultyColorClass[course.difficulty]]"
                                    >
                                        <div class="course__card-image">
                                            <img :src="course.card_image ? course.card_image : '/img/no_foto.jpg'" alt="Изображение курса" />
                                        </div>
                                        <div class="course__card-title">{{ course.card_title }}</div>
                                        <div class="course__card-buttons">
                                            <p class="course__card-desc">{{ difficultyTranslation[course.difficulty] }}</p>
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
                                <div v-else class="cabinet-empty">Вы пока не выбрали ни одного курса.</div>
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
                                                <th class="cabinet-grades__corner">Дата</th>
                                                <th v-for="(row, i) in gradeRows" :key="i">{{ row.date }}</th>
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
import {
  createCourseDifficultyDictionary,
  getCourseDifficultyCardClass,
  getCourseDifficultyLabel,
} from "@/utils/courseDifficulty";

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
// суммарная задолженность по всем детям
const paymentDebt = computed(() =>
  purchasedCourses.value.reduce((sum, c) => sum + (Number(c.price) || 0), 0)
);
const formattedDebt = computed(() => formatMoney(paymentDebt.value));

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
const difficultyColorClass = createCourseDifficultyDictionary(getCourseDifficultyCardClass);
const difficultyTranslation = createCourseDifficultyDictionary(getCourseDifficultyLabel);
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

// --- Табель успеваемости (read-only): даты занятий в шапке, баллы снизу ---
const gradeCourseId = ref("");
const gradeRows = ref([]); // [{ date, score }]
const gradeCourses = computed(() => purchasedCourses.value);
// цвет балла: зелёный (высокий) / оранжевый (средний) / красный (низкий)
const scoreClass = (s) => {
  if (s === null || s === undefined) return "";
  if (s >= 85) return "grade--high";
  if (s >= 60) return "grade--mid";
  return "grade--low";
};
// дата занятия -> ДД.ММ
const formatLessonDate = (value) => {
  if (!value) return "—";
  const p = String(value).slice(0, 10).split("-"); // YYYY-MM-DD
  return p.length === 3 ? `${p[2]}.${p[1]}` : String(value);
};
const loadGrades = async () => {
  gradeRows.value = [];
  if (!gradeCourseId.value || !user.value.id) return;
  try {
    const { data } = await axios.get(
      `/api/user/${user.value.id}/course/${gradeCourseId.value}/grades`
    );
    gradeRows.value = (data.lessons || []).map((l) => ({
      lesson: l.lesson,
      date: formatLessonDate(l.date),
      score: l.score,
    }));
  } catch (error) {
    console.error("Ошибка загрузки табеля:", error);
    gradeRows.value = [];
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

const saveProfile = async () => {
  try {
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
      parent_info: normalizePersonInfo(parentForm),
      student_info: normalizePersonInfo(studentForm),
    };
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
