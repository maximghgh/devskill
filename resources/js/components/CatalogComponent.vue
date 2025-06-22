<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="course">
                    <div class="course__inner">
                      <div
                            class="course__button course__button_active"
                            @click="toggleSidebar"
                        >
                            <img
                                src="https://devskills.foncode.ru/img/filter.jpg"
                                width="40"
                                height="40"
                            />
                        </div>
                        <div class="course__sidebar" :class="{ course__sidebar_open: isSidebarOpen }">
                            <div class="course__sidebar-block">
                                <div class="course__sidebar-title">
                                    Тематика
                                </div>
                                <div class="course__sidebar-check">
                                    <div
                                        class="course__sidebar-oncheck"
                                        v-for="language in languages"
                                        :key="language.id"
                                    >
                                        <!-- Привязываем состояние конкретного языка -->
                                        <input
                                            :id="'lang_' + language.id"
                                            type="checkbox"
                                            :name="'lang_' + language.id"
                                            v-model="language.checked"
                                        />
                                        <label :for="'lang_' + language.id">
                                            <span>{{ language.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="course__sidebar-block">
                                <div class="course__sidebar-title">
                                    Уровень
                                </div>
                                <div class="course__sidebar-check">
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_1"
                                            name="lvl_1"
                                            type="checkbox"
                                            value="basic"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_1"><span>Начинающий</span></label>
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_2"
                                            name="lvl_2"
                                            type="checkbox"
                                            value="middle"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_2"><span>Средний</span></label>
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_3"
                                            name="lvl_3"
                                            type="checkbox"
                                            value="advanced"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_3"><span>Продвинутый</span></label>
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_4"
                                            name="lvl_4"
                                            type="checkbox"
                                            value="mixed"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_4"><span>Смешанный</span></label>
                                    </div>
                                </div>
                            </div>
                            <div class="course__sidebar-block course__sidebar-block_noline">
                                <div class="course__sidebar-title">
                                    Длительность
                                </div>
                                <div class="course__sidebar-check">
                                    <div class="course__sidebar-input">
                                        <label>
                                            <input
                                                type="number"
                                                min="1"
                                                max="24"
                                                v-model="selectedDuration"
                                                @input="validateDuration"
                                            />
                                            <span>От 1 до 24 месяцев</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>   
                        <div class="course__content">
                          <div class="block__filter">
                            <h2>Все программы обучения</h2>
                          </div>
                            <div class="course__menu">
                                <!-- Пункт "Все направления" -->
                                <div
                                    class="course__menu-one"
                                    :class="{
                                        active: selectedDirection === 'all',
                                    }"
                                    @click="selectedDirection = 'all'"
                                >
                                    Все направления
                                </div>
                                <!-- Остальные направления -->
                                <div
                                    class="course__menu-one"
                                    v-for="direction in directions"
                                    :key="direction.id"
                                    :class="{
                                        active:
                                            selectedDirection === direction.id,
                                    }"
                                    @click="selectedDirection = direction.id"
                                >
                                    {{ direction.name }}
                                </div>
                            </div>
                            <div class="course__cards">
                                <div
                                    v-for="course in paginatedCourses"
                                    :key="course.id"
                                    :class="[
                                        'course__card',
                                        'course__card_bg1 ',
                                        difficultyColorClass[course.difficulty],
                                    ]"
                                >
                                    <!-- Изображение курса -->
                                    <div class="course__card-image">
                                        <!-- Проверяем, есть ли путь к файлу. Если нет, можно подставить заглушку -->
                                        <img
                                            :src="
                                                course.card_image
                                                    ? course.card_image
                                                    : '../../img/logo_placeholder.png'
                                            "
                                            alt="Изображение курса"
                                        />
                                    </div>
                                    <!-- Название на карточке -->
                                    <div class="course__card-title">
                                        {{ course.card_title }}
                                    </div>
                                    <!-- Уровень сложности -->

                                    <!-- Цена -->
                                    <!-- Кнопки -->
                                    <div class="course__card-buttons">
                                        <div class="course__card-price">
                                            <p class="course__card-desc">
                                                {{
                                                    difficultyTranslation[
                                                        course.difficulty
                                                    ]
                                                }}
                                            </p>
                                            <span>{{ course.price }} P</span>
                                        </div>
                                        <!-- Ссылка «Подробнее» может вести на страницу с подробной информацией, например /courses/ID -->
                                        <div class="menu__button">
                                            <a
                                                :href="`/cpp/${course.id}`"
                                                class="button button_transparent"
                                                >Подробнее</a
                                            >
                                            <a href="#"
                                                class="button button_white button_white-card"
                                                @click.prevent="openModal(course)"
                                            >Купить</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <transition name="modal">
                                <div
                                    v-if="isModalOpen"
                                    class="modal-overlay"
                                    @click.self="closeModal"
                                >
                                    <div class="modal-content__block">
                                        <button
                                            class="modal-close"
                                            @click="closeModal"
                                        >X</button>

                                        <div v-if="!isSubmitted" class="modal-content">
                                            <!-- info -->
                                            <div
                                                class="block-info"
                                                :class="selectedCourse ? difficultyBgClass[selectedCourse.difficulty] : ''"
                                            >
                                                <div class="block__top">
                                                    <div class="block__logo">
                                                        <img
                                                            :src="selectedCourse.card_image || '/img/logo_placeholder.png'"
                                                            width="50"
                                                            height="50"
                                                        />
                                                        <h2>{{ selectedCourse.card_title }}</h2>
                                                    </div>
                                                    <p class="block__difficul block-bg">
                                                        {{ selectedCourse.description }}
                                                    </p>
                                                </div>
                                                <div class="block__bottom">
                                                    <p class="block__difficul">
                                                        Уровень:&nbsp;
                                                        {{ difficultyTranslation[selectedCourse.difficulty] }}
                                                    </p>
                                                    <div class="block__price">
                                                        <p>Цена: {{ selectedCourse.price }} P</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- form -->
                                            <div class="form-block">
                                                <form @submit.prevent="submitForm" class="forma">
                                                    <!-- radio main -->
                                                    <div class="radio-group">
                                                        <label
                                                            :class="['radio-option', { active: selectedOption === 'consultation' }]"
                                                        >
                                                            <input
                                                                type="radio"
                                                                value="consultation"
                                                                v-model="selectedOption"
                                                            />
                                                            <span class="custom-radio"></span>
                                                            <span class="custom-radio__text">
                                                                Записаться на бесплатную консультацию
                                                            </span>
                                                        </label>

                                                        <label
                                                            :class="['radio-option', { active: selectedOption === 'discount' }]"
                                                        >
                                                            <input
                                                                type="radio"
                                                                value="discount"
                                                                v-model="selectedOption"
                                                            />
                                                            <span class="custom-radio"></span>
                                                            <span class="custom-radio__text">
                                                                Оплатить и получить дополнительную скидку
                                                            </span>
                                                        </label>
                                                    </div>

                                                    <div class="form-group floating-label">
                                                        <input
                                                            id="email"
                                                            type="email"
                                                            required
                                                            v-model="formData.email"
                                                            placeholder=" "
                                                        />
                                                        <label for="email">Электронная почта</label>
                                                    </div>

                                                    <div class="form-group floating-label">
                                                        <input
                                                            id="name"
                                                            type="text"
                                                            required
                                                            v-model="formData.name"
                                                            placeholder=" "
                                                        />
                                                        <label for="name">Имя</label>
                                                    </div>

                                                    <div class="form-group">
                                                        <input
                                                            id="phone"
                                                            type="tel"
                                                            required
                                                            v-model="formData.phone"
                                                            placeholder="+7 999 999-99-99"
                                                            v-mask="'+7 (###) ###-##-##'"
                                                            class="form__input"
                                                        />
                                                    </div>

                                                    <!-- payment -->
                                                    <transition name="fade-slide">
                                                        <div
                                                            v-if="selectedOption === 'discount'"
                                                            class="payment-block"
                                                        >
                                                            <h3 class="payment__h3">
                                                                Оплата курса со скидкой
                                                            </h3>

                                                            <div class="radio-group">
                                                                <label
                                                                    :class="['radio-option', { active: selectedDiscountOption === 'card' }]"
                                                                >
                                                                    <input
                                                                        type="radio"
                                                                        value="card"
                                                                        v-model="selectedDiscountOption"
                                                                    />
                                                                    <span class="custom-radio"></span>
                                                                    <span class="custom-radio__text">
                                                                        Покупка картой
                                                                    </span>
                                                                </label>

                                                                <label
                                                                    :class="['radio-option', { active: selectedDiscountOption === 'sbp' }]"
                                                                >
                                                                    <input
                                                                        type="radio"
                                                                        value="sbp"
                                                                        v-model="selectedDiscountOption"
                                                                    />
                                                                    <span class="custom-radio"></span>
                                                                    <span class="custom-radio__text">
                                                                        Покупка через СБП
                                                                    </span>
                                                                </label>
                                                            </div>

                                                            <!-- card fields -->
                                                            <div v-if="selectedDiscountOption === 'card'">
                                                                <div class="form-group">
                                                                    <label for="cardNumber" class="form-label">
                                                                        Номер карты:
                                                                    </label>
                                                                    <input
                                                                        id="cardNumber"
                                                                        type="text"
                                                                        v-model="cardInfo.cardNumber"
                                                                        placeholder="0000 0000 0000 0000"
                                                                        class="form__input"
                                                                        v-mask="'#### #### #### ####'"
                                                                    />
                                                                </div>

                                                                <div class="block-card">
                                                                    <div class="form-group">
                                                                        <label for="cardExpiry" class="form-label">
                                                                            Срок действия (ММ/ГГ):
                                                                        </label>
                                                                        <input
                                                                            id="cardExpiry"
                                                                            type="text"
                                                                            v-model="cardInfo.expiry"
                                                                            placeholder="ММ/ГГ"
                                                                            class="form__input--card"
                                                                            v-mask="'##/##'"
                                                                        />
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="cardCVC" class="form-label">
                                                                            CVC:
                                                                        </label>
                                                                        <input
                                                                            id="cardCVC"
                                                                            type="text"
                                                                            v-model="cardInfo.cvc"
                                                                            placeholder="CVC"
                                                                            class="form__input--card"
                                                                            v-mask="'###'"
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </transition>

                                                    <div class="form-field form-field--button">
                                                        <input
                                                            class="form-submit form-submit--button"
                                                            type="submit"
                                                            :value="selectedOption === 'consultation'
                                                                ? 'Заказать консультацию'
                                                                : 'Оплатить'"
                                                        />
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- success screen -->
                                        <div v-else class="modal__else">
                                            <div class="else__info">
                                                <h2>{{ successMessage }}</h2>
                                                <p v-if="selectedOption === 'consultation'">
                                                    Скоро с вами свяжется специалист
                                                </p>
                                                <p v-else>
                                                    Вы успешно купили курс, поздравляем!
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                                <!-- Список номеров страниц -->
                                
                        </div>
                    </div>
                    <div class="pagination-pages">
                        <button
                            v-for="page in pages"
                            :key="page"
                            @click="currentPage = page"
                            :class="{ active: currentPage === page }"
                        >
                            {{ page }}
                        </button>
                    </div>
                </section>
            </div>
        </div>
        <div class="b-popup" id="popup">
    		<div class="closer-big"></div>
            <div class="b-popup-content">
                <div class="popup-title"></div>
                <div class="popup-desc"></div>
                <div class="form-block">
                    <form method="post" name="mtForm1" id="mtForm1" class="forma">
                        <input type="hidden" name="data_form" id="data_form" value="">
                        <input type="hidden" name="no" value="no" />
                        <div class="form-field">
                            <label>
                                <span>Ваше имя:</span>
                                <input type="text" name="name" required="required">
                            </label>
                        </div>
                        <div class="form-field">
                            <label>
                                <span>Ваш телефон:</span>
                                <input type="tel" name="phone" required="required" class="phone_valid">
                            </label>
                        </div>
                        <div class="form-field">
                            <input type="submit" name="submit" value="Отправить заявку">
                        </div>
                    </form>
                </div>
                <div class="close-up">
                    <div></div>
                    <div></div>
                </div>
            </div>
		</div>
        <div class="b-popup" id="message">
            <div class="closer-big closer-big-all"></div>
            <div class="b-popup-content">
                <div class="popup-title">Cпасибо вам!</div>
                <div class="popup-desc">Ваша заявка успешно отправлена. <br>C вами свяжутся в ближайшее время.</div>
                <div class="close-up close-up-all">
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
        <transition name="modal">
            <div
                v-if="showAuthModal"
                class="modal-overlay modal-overlay--auth"
                @click.self="showAuthModal = false"
            >
                <div class="modal-content__block modal-close--auth">
                <button class="modal-close modal-close--auth" @click="showAuthModal = false">X</button>
                <div class="modal-content modal-content--auth">
                    <h2 class="modal__h2--auth">Войдите или зарегистрируйтесь</h2>
                    <p>Чтобы приобрести курс или заказать консультацию</p>
                    <div class="auth-buttons">
                    <a href="/login" class="button button_white--auth">Войти</a>
                    <a href="/register" class="button button_white--auth">Регистрация</a>
                    </div>
                </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";

const isSidebarOpen = ref(false)
const showAuthModal = ref(false)
// 2) метод-переключатель
function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

/* --- Реактивные переменные общего каталога --- */
const user                  = ref(null);
const courses               = ref([]);
const languages             = ref([]);
const directions            = ref([]);

/* --- Модалка покупки --- */
const isModalOpen           = ref(false);
const selectedCourse        = ref(null);
const isSubmitted           = ref(false);
const successMessage        = ref("");
const selectedOption        = ref("consultation");
const selectedDiscountOption= ref("card");
const formData              = ref({ email: "", name: "", phone: "" });
const cardInfo              = ref({ cardNumber: "", expiry: "", cvc: "" });

/* --- Переменные пагинации --- */
const pageSize   = 6;
const currentPage= ref(1);
const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return courses.value.slice(start, start + pageSize);
});
const totalPages = computed(() => Math.ceil(courses.value.length / pageSize));
const pages      = computed(() => Array.from({ length: totalPages.value }, (_, i) => i + 1));

/* --- Фильтры --- */
const selectedDifficulties = ref([]);
const selectedDirection    = ref("all");
const selectedDuration     = ref(null);
const selectedLanguages    = computed(() =>
  languages.value.filter(l => l.checked).map(l => l.id)
);

/* --- Словари сложности для карточек и модалки --- */
const difficultyTranslation = {
  basic: "Начинающий",
  middle: "Средний",
  advanced: "Продвинутый",
  mixed: "Смешанный",
};
const difficultyColorClass = {
  basic: "course__card_bg-cyan",
  middle: "course__card_bg-fiolet",
  advanced: "course__card_bg-orange",
  mixed: "course__card_bg-green",
};
const difficultyBgClass = {
  basic: "block-info_bg-cyan",
  middle: "block-info_bg-fiolet",
  advanced: "block-info_bg-orange",
  mixed: "block-info_bg-green",
};

/* --- Методы пагинации --- */
function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++;
}
function prevPage() {
  if (currentPage.value > 1) currentPage.value--;
}

/* --- Методы открытия/закрытия модалки --- */
function openModal(course) {
  if (!user.value) {
    showAuthModal.value = true; // если у вас есть auth-модалка
    return;
  }
  selectedCourse.value = course;
  isSubmitted.value    = false;
  successMessage.value = "";
  isModalOpen.value    = true;
}
function closeModal() {
  isModalOpen.value = false;
}

/* --- Отправка формы покупки/консультации --- */
async function submitForm() {
  if (!selectedCourse.value) return;

  const payload = {
    user_id: user.value.id,
    ...formData.value,
    type: selectedOption.value,
    payment_method:
      selectedOption.value === "discount"
        ? selectedDiscountOption.value
        : "card",
    payment_details:
      selectedOption.value === "discount" && selectedDiscountOption.value === "card"
        ? JSON.stringify(cardInfo.value)
        : null,
  };

  const url =
    selectedOption.value === "consultation"
      ? `/api/${selectedCourse.value.id}/consultation`
      : `/api/${selectedCourse.value.id}/purchase`;

  try {
    await axios.post(url, payload);
    successMessage.value =
      selectedOption.value === "consultation"
        ? "Спасибо за заявку!"
        : "Поздравляем с покупкой!";
    isSubmitted.value = true;
  } catch (e) {
    console.error(e);
  }
}

/* --- Прочие вспомогательные функции --- */
function validateDuration(e) {
  const v = e.target.value;
  if (v === "") {
    selectedDuration.value = "";
    return;
  }
  const n = Number(v);
  if (n > 24) {
    e.target.value = 24;
    selectedDuration.value = 24;
  } else if (n < 1) {
    e.target.value = "";
    selectedDuration.value = "";
  } else {
    selectedDuration.value = n;
  }
}
function base64ToUtf8(str) {
  return decodeURIComponent(escape(atob(str)));
}
const logout = () => {
  localStorage.removeItem("user");
  user.value = null;
  window.location.href = "/";
};
function goToCourse(id) {
  window.location.href = `/cpp/${id}`;
}

/* --- Загрузка данных --- */
async function loadDirections() {
  try {
    const { data } = await axios.get("/api/directions");
    directions.value = Array.isArray(data) ? data : data.data || [];
  } catch (e) { console.error(e); }
}
async function fetchCourses() {
  try {
    const params = {};
    if (selectedLanguages.value.length)    params.languages   = selectedLanguages.value.join(",");
    if (selectedDifficulties.value.length) params.difficulties= selectedDifficulties.value;
    if (selectedDirection.value !== "all") params.direction   = selectedDirection.value;
    if (selectedDuration.value)            params.duration    = selectedDuration.value;

    const { data } = await axios.get("/api/courses", { params });
    const arr = Array.isArray(data) ? data : data.data || [];
    courses.value = arr;
  } catch (e) { console.error(e); }
}

/* --- Рендер-эффекты и начальная инициация --- */
watch(() => courses.value.length, () => (currentPage.value = 1));
watch([ selectedDifficulties, selectedDirection, selectedDuration, selectedLanguages ], fetchCourses, { deep: true });

onMounted(async () => {
  // подтягиваем verifiedUser из URL, если был
  const p = new URLSearchParams(window.location.search).get("verifiedUser");
  if (p) {
    try {
      const obj = JSON.parse(base64ToUtf8(decodeURIComponent(p)));
      localStorage.setItem("user", JSON.stringify(obj));
    } catch {}
  }
  // загружаем из localStorage
  const stored = localStorage.getItem("user");
  if (stored) {
    try { user.value = JSON.parse(stored); } catch {}
  }
  await loadDirections();
  // языки
  try {
    const res = await axios.get("/api/languages");
    const arr = Array.isArray(res.data) ? res.data : res.data.data || [];
    languages.value = arr.map(l => ({ ...l, checked: !!l.checked }));
  } catch {}
  await fetchCourses();
});
</script>


<style scoped>

/* Оверлей модалки */
.block__top{
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: 15px;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 5000;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Контейнер содержимого модалки */
.modal-content__block {
  overflow: hidden;
  padding: 0;
  background: #fff;
  max-width: 1000px;
  width: 100%;
  min-height: 300px;
  position: relative;
  border-radius: 8px;
}

/* Основной контент модалки покупки */
.modal-content {
  overflow: hidden;
  display: grid;
  grid-template-columns: 500px 1fr;
  padding: 0;
  background: #fff;
  max-width: 1000px;
  width: 100%;
  min-height: 300px;
  position: relative;
  border-radius: 8px;
}

/* Кнопка закрытия */
.modal-close {
  background: none;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  position: absolute;
  top: 10px;
  right: 10px;
  cursor: pointer;
  z-index: 10;
}

/* Экран «успеха» */
.modal__else {
  background-image: url(/resources/assets/img/bg__complite.png);
  background-repeat: no-repeat;
  background-position: right;
  min-height: 300px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
.else__info {
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 10;
}

/* Цветной блок с инфо о курсе */
.block-info {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background-color: #727dcc;
  padding: 20px 30px;
  width: 100%;
  min-height: 300px;
  height: 100%;
  color: #fff;
}
.block-info h2 {
  font-size: 30px;
  margin: 0;
}
.block-info_bg-cyan   { background-color: #698dc9; }
.block-info_bg-fiolet { background-color: #727dcc; }
.block-info_bg-orange { background-color: #d48a66; }
.block-info_bg-green  { background-color: #5bcaa7; }

/* Анимация для платежного блока */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.5s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
.fade-slide-enter-to,
.fade-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
}

/* Блок формы оплаты */
.payment__h3{
    margin: 0 0 20px;
}
.form__input {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    outline: none;
    font-size: 14px;
    margin: 0 0 30px;
}
.form-label {
    font-size: 15px;
    display: block;
    margin: 0 0 10px;
}
.form-block{
    padding: 50px 30px;
}
.payment-block {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.block__logo {
    display: flex;
    gap: 36px;
    align-items: center;
}
.block-card {
  display: grid;
  grid-template-columns: repeat(2, 205px);
  justify-content: space-between;
}
@media (max-width: 400px) {
  .block-card {
    grid-template-columns: 1fr;
  }
  .form__input--card {
    width: 268px;
    padding: 15px;
  }
  .block__logo{
        display: flex;
        flex-direction: column;
        gap: 15px !important;
        margin: 0 0 20px;
    }
}

/* Инпуты карт */
.form__input--card {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
  margin-bottom: 30px;
}
.form-group{
    display: flex;
    width: 100%;
    position: relative;
    flex-direction: column;
}
/* Радиокнопки */
.radio-group {
width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 25px;
}
.radio-option {
  display: inline-flex;
  align-items: center;
  cursor: pointer;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  transition: border-color 0.2s;
}
.radio-option input[type="radio"] {
  display: none;
}
.radio-option .custom-radio {
  width: 18px;
  height: 18px;
  border: 2px solid #ccc;
  border-radius: 50%;
  margin-right: 8px;
  position: relative;
  flex-shrink: 0;
  transition: border-color 0.2s;
}
.radio-option:hover {
  border-color: #999;
}
.radio-option.active {
  border-color: #1d6efd;
}
.radio-option input[type="radio"]:checked + .custom-radio::after {
  content: "";
  position: absolute;
  top: 3px;
  left: 3px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #1d6efd;
}

/* Поля формы с «плавающими» лейблами */
.floating-label {
    display: flex;
    width: 100%;
    position: relative;
    margin-bottom: 16px;
    flex-direction: column;
}
.floating-label input {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
  background: #fff;
  transition: border-color 0.2s;
}
.floating-label input:focus {
  border-color: #666;
}
.floating-label label {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: #fff;
  padding: 0 4px;
  color: #999;
  font-size: 14px;
  pointer-events: none;
  transition: 0.2s ease all;
}
.floating-label input:focus + label,
.floating-label input:not(:placeholder-shown) + label {
  top: -6px;
  transform: translateY(0);
  font-size: 12px;
  color: #666;
}

/* Кнопка отправки */
.form-field--button {
  max-width: 100%;
}
.form-submit--button {
  width: 100%;
  padding: 12px 15px;
  border-radius: 10px;
  background-color: #727dcc;
  color: #fff;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s;
}
.form-submit--button:hover {
  background-color: #727dcc81;
}

/* Страница авторизации в модалке */
.modal-content--auth {
  display: flex !important;
}
.modal-close--auth {
  background-color: transparent !important;
}
.modal__h2--auth {
  margin-bottom: 10px;
}
.button_white--auth {
  cursor: pointer;
  background: #1d6ffdc9;
  border: 1px solid #1d6ffdc9;
  width: 200px;
  padding: 10px;
  text-align: center;
  color: #fff;
}
.button_white--auth:hover {
  background: #fff;
  color: #1d6ffdc9;
}
.auth-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 50px;
}
.course__button {
    display: none;
    width: 40px;
    height: 38px;
    position: absolute;
    top: 0;
    right: 15px;
    z-index: 10;
}
@media (max-width: 991px) {
    .course__button {
        display: block;
    }
    .course__sidebar-check--lang {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 10px;
    }
}
@media (max-width: 1100px) {
     .modal__h2--auth{
        font-size: 30px;
    }
    .modal-content__block{
        max-width: 700px;
    }
    .modal-close--auth{
        max-width: 500px;
    }
    .modal-close--auth p {
        text-align: center;
        font-size: 15px;
        width: 400px;
    }
    .modal-content {
        padding-top: 0px;
        grid-template-columns: 1fr;
        overflow-y: auto; /* или scroll */
        max-height: 80vh;
    }
    .modal-close {
        background-color: #ffffff;
    }
    .modal-close:hover {
        background-color: rgba(128, 128, 128, 0.637);
    }
    .block-card{
        gap: 20px;
        grid-template-columns: repeat(2, 1fr);
    }
    .form__input--card{
        width: 280px;
    }
    .auth-buttons{
        flex-direction: row;
    }
}
@media (max-width: 767px) {
    .modal__h2--auth{
        font-size: 30px;
    }
}
@media (max-width: 550px) {
    .block-info {
        min-height: 200px;
        align-items: center;
    }
    .block-bg {
        display: none;
    }
    .block__price {
        text-align: center;
    }
    .modal-content {
        padding-top: 0px;
        overflow-y: auto; /* или scroll */
        max-height: 80vh;
    }
    .modal-close {
        background-color: #ffffff;
    }
    .modal-close:hover {
        background-color: rgba(128, 128, 128, 0.637);
    }
}
@media (max-width: 515px) {
    .modal-close--auth p{
        text-align: center;
        width: 200px;
    }
    .modal__h2--auth {
        font-size: 1.5em;
    }
    .block-info {
        min-height: 200px;
        align-items: center;
    }
    .block-bg {
        display: none;
    }
    .block__h2 {
        font-size: 18px;
        text-align: center;
        margin: 0 40px 25px;
    }
    .forma {
        display: grid;
        grid-template-columns: 1fr;
        justify-items: center;
    }
    .floating-label input {
        padding: 15px;
    }
    .form__input {
        padding: 15px;
    }
    .form-submit {
        width: 300px;
        padding: 20px;
    }
    .block__price {
        text-align: center;
    }
    .modal__h2--auth{
        font-size: 25px;
        width: 200px;
        margin: 0 0 15px;
    }
    .modal-close--auth{
        flex-direction: column;
        max-width: 400px;
    }
    .auth-buttons{
        margin-top: 25px;
        flex-direction: column;
    }
    .button_white--auth{
        width: 150px;
    }
    .modal-content {
        padding-top: 0px;
        overflow-y: auto; /* или scroll */
        max-height: 80vh;
    }
    .modal-close {
        background-color: #ffffff;
    }
    .modal-close:hover {
        background-color: rgba(128, 128, 128, 0.637);
    }
}
@media (max-width: 410px){
    .block-info h2{
        text-align: center;
    }
    .modal-content__block{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-close--auth{
        max-width: 300px;
    }
}
@media (max-width: 650px) {
    .course__menu {
        display: flex;
        align-items: unset;
        justify-content: unset;
        flex-wrap: unset;
        gap: 20px;
        overflow: auto;
        margin: 0 -10px 40px;
        padding: 0 10px 0;
    }
    .course__menu::-webkit-scrollbar {
        display: none;
    }
    .course__menu-one {
        height: 70px;
        padding: 0px 20px;
        border-radius: 25px;
    }
}
    .pagination-pages {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 3rem;
    }

    .pagination-pages button {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ccc;
    padding: 0.5rem 0.8rem;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    }

    .pagination-pages button:hover:not(:disabled) {
    background-color: #e6e6e6;
    }

    .pagination-pages button.active {
        background-color: #698dc9;
        color: #fff;
    }


    .menu__button {
        display: flex;
        gap: 19px;
    }
    .course__card-buttons {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    .course__card-price {
        display: flex;
        flex-direction: column;
        gap: 25px;
        font-family: JanoSansProSemiBold;
        font-size: 1.4em;
    }
    .course__card-desc {
        font-size: 21px;
        font-family: JanoSansProRegular;
    }
    .course__menu-one {
        cursor: pointer;
    }
    .course__card_bg-cyan {
        background-color: #698dc9;
    }
    .course__card_bg-fiolet {
        background-color: #727dcc;
    }
    .course__card_bg-orange {
        background-color: #d48a66;
    }
    .course__card_bg-green {
        background-color: #5bcaa7;
    }
</style>
