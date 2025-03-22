<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="offer offer_main">
                    <div class="offer__inner offer__inner_main">
                        <h1>
                            Повышайте свои знания и навыки <br />вместе с
                            DevSkillAcademy
                        </h1>
                        <div class="offer__desc">
                            Наши курсы прошли более 15000 школьников и
                            студентов.
                        </div>
                        <div class="button button_white button_offer-main">
                            Присоединиться
                        </div>
                    </div>
                </section>
                <section class="cooperation">
                    <h2>
                        Мы сотрудничаем с ведущими <br />организациями и
                        университетами
                    </h2>
                    <div class="cooperation__slider">
                        <div class="cooperation__slide">
                            <img src="/img/univer_1.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="/img/univer_2.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="/img/univer_3.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="/img/univer_4.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="/img/univer_5.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="/img/univer_6.png" />
                        </div>
                    </div>
                </section>
                <section class="category">
                    <h2>Категории курсов</h2>
                    <div class="category__inner">
                        <div class="category__one category__one_back1">
                            <div class="category__one-inner">
                                <div class="category__one-title">
                                    Серия курсов по C++
                                </div>
                                <div class="category__one-info">
                                    <p>• Базовый</p>
                                    <p>• Фундаментальный</p>
                                    <p>• Олимпиадный</p>
                                </div>
                                <a
                                    href="#"
                                    class="button button_white button_cat-link"
                                    >Перейти в категорию</a
                                >
                            </div>
                        </div>
                        <div class="category__one category__one_defualt">
                            <div class="category__one-inner">
                                В ближайшее время здесь <br />появятся другие
                                категории
                            </div>
                        </div>
                    </div>
                </section>
                <section class="course">
                    <div class="course__inner">
                        <div class="course__button course__button_active" @click="toggleSidebar">
                            <img src="https://devskills.foncode.ru/img/filter.jpg" width="40" height="40">
                        </div>
                        <div class="course__sidebar" :class="{ 'course__sidebar_open': isSidebarOpen }">
                            <div class="course__sidebar-block">
                                <div class="course__sidebar-title">
                                    Тематика
                                </div>
                                <div class="course__sidebar-check course__sidebar-check--lang">
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
                                <div class="course__sidebar-title">Уровень</div>
                                <div class="course__sidebar-check">
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_1"
                                            name="lvl_1"
                                            type="checkbox"
                                            value="basic"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_1"
                                            ><span>Начинающий</span></label
                                        >
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_2"
                                            name="lvl_2"
                                            type="checkbox"
                                            value="middle"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_2"
                                            ><span>Средний</span></label
                                        >
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_3"
                                            name="lvl_3"
                                            type="checkbox"
                                            value="advanced"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_3"
                                            ><span>Продвинутый</span></label
                                        >
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_4"
                                            name="lvl_4"
                                            type="checkbox"
                                            value="mixed"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_4"
                                            ><span>Смешанный</span></label
                                        >
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
                            <h2>Каталог курсов</h2>
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
                            <div class="block-left">
                                <div class="course__cards">
                                    <div
                                        v-for="course in paginatedCourses"
                                        :key="course.id"
                                        :class="[
                                            'course__card',
                                            'course__card_bg1 ',
                                            difficultyColorClass[
                                                course.difficulty
                                            ],
                                        ]"
                                    >
                                        <!-- Изображение курса -->
                                        <div class="course__card-image">
                                            <!-- Проверяем, есть ли путь к файлу. Если нет, можно подставить заглушку -->
                                            <img
                                                :src="
                                                    course.card_image
                                                        ? course.card_image
                                                        : '/img/logo_placeholder.png'
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
                                                <span
                                                    >{{ course.price }} P</span
                                                >
                                            </div>
                                            <!-- Ссылка «Подробнее» может вести на страницу с подробной информацией, например /courses/ID -->
                                            <div class="menu__button">
                                                <a
                                                    :href="`/cpp/${course.id}`"
                                                    class="button button_transparent"
                                                    >Подробнее</a
                                                >
                                                <a
                                                    href="#"
                                                    class="button button_white button_white-card"
                                                    @click.prevent="
                                                        openModal(course)
                                                    "
                                                    >Купить</a
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pagination-pages">
                                    <button
                                        v-for="page in pages"
                                        :key="page"
                                        @click="currentPage = page"
                                        :class="{
                                            active: currentPage === page,
                                        }"
                                    >
                                        {{ page }}
                                    </button>
                                </div>
                                <transition name="modal">
                                    <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
                                        <div class="modal-content__block">
                                            <button
                                                class="modal-close"
                                                @click="closeModal"
                                            >
                                                X
                                            </button>
                                            <!-- Заголовок и данные о курсе -->
                                            <div class="modal-content" v-if="!isSubmitted">
                                                <div class="block-info"
                                                    :class="
                                                        selectedCourse
                                                            ? difficultyBgClass[
                                                                selectedCourse
                                                                    .difficulty
                                                            ]
                                                            : ''
                                                    "
                                                >
                                                    <div class="block__top">
                                                        <div class="block__logo">
                                                            <img
                                                                :src="
                                                                    selectedCourse.card_image
                                                                        ? selectedCourse.card_image
                                                                        : '/img/logo_placeholder.png'
                                                                "
                                                                alt="Изображение курса"
                                                                width="50"
                                                                height="50"
                                                            />
                                                            <h2 v-if="selectedCourse">
                                                                {{
                                                                    selectedCourse.card_title
                                                                }}
                                                            </h2>
                                                        </div>
                                                        <p
                                                            class="block__difficul block-bg"
                                                            v-if="selectedCourse"
                                                        >
                                                            {{
                                                                selectedCourse.description
                                                            }}
                                                        </p>
                                                    </div>
                                                    <div class="block__bottom">
                                                        <p
                                                            class="block__difficul"
                                                            v-if="selectedCourse"
                                                        >
                                                            Уровень:
                                                            {{
                                                                difficultyTranslation[
                                                                    selectedCourse
                                                                        .difficulty
                                                                ]
                                                            }}
                                                        </p>
                                                        <div class="block__price">
                                                            <p v-if="selectedCourse">
                                                                Цена:
                                                                {{
                                                                    selectedCourse.price
                                                                }}
                                                                P
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Форма (как на скриншоте) -->
                                                <div class="form-block">
                                                    <form @submit.prevent="submitForm" class="forma">
                                                        <!-- Блок с радиокнопками -->
                                                        <div class="radio-group">
                                                            <label
                                                            :class="['radio-option', { active: selectedOption === 'consultation' }]"
                                                            >
                                                            <input
                                                                type="radio"
                                                                name="consultationOption"
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
                                                                name="consultationOption"
                                                                value="discount"
                                                                v-model="selectedOption"
                                                            />
                                                            <span class="custom-radio"></span>
                                                            <span class="custom-radio__text">
                                                                Оплатить и получить дополнительную скидку
                                                            </span>
                                                            </label>
                                                        </div>

                                                        <!-- Поля формы (имя, email, телефон и т.д.) -->
                                                        <div class="form-group floating-label">
                                                            <input
                                                            id="email"
                                                            type="email"
                                                            name="email"
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
                                                            name="name"
                                                            required
                                                            v-model="formData.name"
                                                            placeholder=" "
                                                            />
                                                            <label for="name">Имя</label>
                                                        </div>

                                                        <div class="form-group">
                                                            <input
                                                            class="form__input"
                                                            id="phone"
                                                            type="tel"
                                                            name="phone"
                                                            required
                                                            v-model="formData.phone"
                                                            placeholder="+7 999 999-99-99"
                                                            />
                                                        </div>

                                                        <!-- Если выбран "discount" (оплата), можно показать доп. блок -->
                                                        <transition name="fade-slide">
                                                            <div v-if="selectedOption === 'discount'" class="payment-block">
                                                                <h3 class="payment__h3">Оплата курса со скидкой</h3>
                                                                <!-- Дополнительная радио-группа для выбора способа оплаты -->
                                                                <div class="radio-group">
                                                                <label :class="['radio-option', { active: selectedDiscountOption === 'card' }]">
                                                                    <input
                                                                    type="radio"
                                                                    name="discountOption"
                                                                    value="card"
                                                                    v-model="selectedDiscountOption"
                                                                    />
                                                                    <span class="custom-radio"></span>
                                                                    <span class="custom-radio__text">Покупка картой</span>
                                                                </label>
                                                                <label :class="['radio-option', { active: selectedDiscountOption === 'sbp' }]">
                                                                    <input
                                                                    type="radio"
                                                                    name="discountOption"
                                                                    value="sbp"
                                                                    v-model="selectedDiscountOption"
                                                                    />
                                                                    <span class="custom-radio"></span>
                                                                    <span class="custom-radio__text">Покупка через СБП</span>
                                                                </label>
                                                                </div>

                                                                <!-- Если выбрана опция "Покупка картой", показываем поля для карты -->
                                                                <div v-if="selectedDiscountOption === 'card'">
                                                                <div class="form-group">
                                                                    <label for="cardNumber" class="form-label">Номер карты:</label>
                                                                    <input
                                                                    type="text"
                                                                    id="cardNumber"
                                                                    v-model="cardInfo.cardNumber"
                                                                    placeholder="0000 0000 0000 0000"
                                                                    class="form__input"
                                                                    v-mask="'#### #### #### ####'"
                                                                    />
                                                                </div>
                                                                <div class="block-card">
                                                                    <div class="form-group">
                                                                    <label for="cardExpiry" class="form-label">Срок действия (ММ/ГГ):</label>
                                                                    <input
                                                                        type="text"
                                                                        id="cardExpiry"
                                                                        v-model="cardInfo.expiry"
                                                                        placeholder="ММ/ГГ"
                                                                        class="form__input--card"
                                                                        v-mask="'##/##'"
                                                                    />
                                                                    </div>
                                                                    <div class="form-group">
                                                                    <label for="cardCVC" class="form-label">CVC:</label>
                                                                    <input
                                                                        type="text"
                                                                        id="cardCVC"
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

                                                        <!-- Кнопка отправки -->
                                                        <div class="form-field form-field--button">
                                                            <input
                                                                class="form-submit form-submit--button"
                                                                type="submit"
                                                                name="submit"
                                                                :value="selectedOption === 'consultation' ? 'Заказать консультацию' : 'Оплатить'"
                                                            />
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="modal__else" v-else>
                                                <div class="else__info">
                                                    <h2>{{ successMessage }}</h2>
                                                    <p v-if="selectedOption === 'consultation'">
                                                        Скоро с вами свяжется специалист
                                                    </p>
                                                    <p v-else>
                                                        Вы успешно купили курс, поздравляем!
                                                    </p>
                                                    <!-- Кнопка «Закрыть» или «Ок» -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <footer>
                <div class="footer__inner">
                    <div class="footer__row">
                        <div class="footer__block">API</div>
                        <div class="footer__block">
                            <div class="footer__logo">
                                <img src="img/logo.png" />
                            </div>
                        </div>
                    </div>
                    <div class="footer__row">
                        © 2011-2023 гг. Сайт не является публичной офертой и
                        носит информационный характер. Все материалы данного
                        сайта являются объектами авторского права (в том числе
                        дизайн). Запрещается копирование, распространение (в том
                        числе путем копирования на другие сайты и ресурсы в
                        Интернете) или любое иное использование информации и
                        объектов без предварительного согласия правообладателя.
                    </div>
                </div>
            </footer>
        </div>
        <div class="b-popup" id="popup">
            <div class="closer-big"></div>
            <div class="b-popup-content">
                <div class="popup-title"></div>
                <div class="popup-desc"></div>
                <div class="form-block">
                    <form
                        method="post"
                        name="mtForm1"
                        id="mtForm1"
                        class="forma"
                    >
                        <input
                            type="hidden"
                            name="data_form"
                            id="data_form"
                            value=""
                        />
                        <input type="hidden" name="no" value="no" />
                        <div class="form-field">
                            <label>
                                <span>Ваше имя:</span>
                                <input
                                    type="text"
                                    name="name"
                                    required="required"
                                />
                            </label>
                        </div>
                        <div class="form-field">
                            <label>
                                <span>Ваш телефон:</span>
                                <input
                                    type="tel"
                                    name="phone"
                                    required="required"
                                    class="phone_valid"
                                />
                            </label>
                        </div>
                        <div class="form-field form-field--button">
                            <input
                                type="submit"
                                name="submit"
                                value="Отправить заявку"
                            />
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
                <div class="popup-desc">
                    Ваша заявка успешно отправлена. <br />C вами свяжутся в
                    ближайшее время.
                </div>
                <div class="close-up close-up-all">
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, nextTick } from "vue";
import axios from "axios";


/* Реактивная переменная, которая управляет видимостью / открытостью сайдбара */
const isSidebarOpen = ref(false)

/* Функция, переключающая состояние */
function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

/* =====================================
   1) Общие данные: пользователь, курсы, языки, направления, пагинация, фильтры
===================================== */
const user = ref(null);
const courses = ref([]);
const languages = ref([]);
const directions = ref([]);

const isSubmitted = ref(false);
const successMessage = ref("");

const pageSize = 6;
const currentPage = ref(1);
const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return courses.value.slice(start, start + pageSize);
});
const totalPages = computed(() => Math.ceil(courses.value.length / pageSize));
const pages = computed(() =>
  Array.from({ length: totalPages.value }, (_, i) => i + 1)
);

// Модальное окно для редактирования курса
const isModalOpen = ref(false);
const selectedCourse = ref(null);
function openModal(course) {
  selectedCourse.value = course;
  isSubmitted.value = false;
  successMessage.value = "";
  isModalOpen.value = true;
}
function closeModal() {
  isModalOpen.value = false;
}

// Фильтры
const selectedDifficulties = ref([]);
const selectedDirection = ref("all");
const selectedDuration = ref(null);

// Чекбоксы для языков
const selectedLanguages = computed(() =>
  languages.value.filter((language) => language.checked).map((language) => language.id)
);

// Переводы сложности и CSS-классы
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

// Валидатор длительности
function validateDuration(event) {
  let value = event.target.value;
  if (value === "") {
    selectedDuration.value = "";
    return;
  }
  const numericValue = Number(value);
  if (numericValue > 24) {
    event.target.value = 24;
    selectedDuration.value = 24;
  } else if (numericValue < 1) {
    event.target.value = "";
    selectedDuration.value = "";
  } else {
    selectedDuration.value = numericValue;
  }
}

// Logout
function logout() {
  localStorage.removeItem("user");
  user.value = null;
  window.location.href = "/";
}

// Загрузка направлений
async function loadDirections() {
  try {
    const response = await axios.get("/api/directions");
    if (Array.isArray(response.data)) {
      directions.value = response.data;
    } else if (response.data && Array.isArray(response.data.data)) {
      directions.value = response.data.data;
    } else {
      console.warn("Непредвиденный формат данных направлений:", response.data);
      directions.value = [];
    }
  } catch (error) {
    console.error("Ошибка при загрузке направлений:", error);
  }
}

// Загрузка курсов
async function fetchCourses() {
  try {
    let params = {};
    if (selectedLanguages.value.length > 0) {
      params.languages = selectedLanguages.value.join(",");
    }
    if (selectedDifficulties.value.length > 0) {
      params.difficulties = selectedDifficulties.value;
    }
    if (selectedDirection.value !== "all") {
      params.direction = selectedDirection.value;
    }
    if (selectedDuration.value) {
      params.duration = selectedDuration.value;
    }
    const response = await axios.get("/api/courses", { params });
    const coursesData = response.data?.data ?? response.data;
    if (Array.isArray(coursesData)) {
      courses.value = coursesData;
    } else {
      console.warn("Непредвиденный формат данных курсов:", response.data);
      courses.value = [];
    }
  } catch (error) {
    console.error("Ошибка при загрузке курсов:", error);
  }
}

// Watchers для обновления курсов при изменении фильтров
watch(selectedDifficulties, fetchCourses, { deep: true });
watch(selectedDirection, fetchCourses);
watch(selectedDuration, fetchCourses);
watch(selectedLanguages, fetchCourses);

/* =====================================
   2) Форма "консультация / оплата"
===================================== */
// Выбор варианта: консультация или скидка (оплата)
const selectedOption = ref("consultation");
const formData = ref({
  email: "",
  name: "",
  phone: ""
});

// Для оплаты со скидкой – выбор способа оплаты и данные карты
const selectedDiscountOption = ref("card"); // "card" или "sbp"
const cardInfo = ref({
  cardNumber: "",
  expiry: "",
  cvc: ""
});

async function submitForm() {

  if (!selectedCourse.value || !selectedCourse.value.id) {
    console.error("Нет выбранного курса");
    return;
  }

  // Берём id из selectedCourse
  const courseId = selectedCourse.value.id;

  const payload = {
    user_id: user.value.id,
    ...formData.value,
    type: selectedOption.value
  };
  console.log(user.value.id);
  // Если выбран вариант "discount" + "card",
  // добавляем данные карты
  if (selectedOption.value === "discount") {
  // Оплата
  payload.payment_method = selectedDiscountOption.value; // 'card' или 'sbp'
  if (selectedDiscountOption.value === "card") {
    // Запишем cardInfo в payment_details
    payload.payment_details = JSON.stringify(cardInfo.value);
  }
} else {
// consultation — тогда как быть? 
// Либо используем 'payment_method = "card"' условно, либо меняем логику на бэкенде
payload.payment_method = "card"; 
payload.payment_details = null;
}

  let url; // сюда сохраним адрес запроса
  if (selectedOption.value === "consultation") {
    // Заказ консультации
    url = `/api/${courseId}/consultation`;
  } else {
    // Покупка (discount)
    url = `/api/${courseId}/purchase`;
  }

//   try {
//     const response = await axios.post(url, payload);
//     console.log("Успешно отправлено:", response.data);
//     alert("Ваша заявка отправлена!");
//     // Сбрасываем форму
//     formData.value = { email: "", name: "", phone: "" };
//     selectedOption.value = "consultation";
//     selectedDiscountOption.value = "card";
//     cardInfo.value = { cardNumber: "", expiry: "", cvc: "" };
//   } catch (error) {
//     console.error("Ошибка при отправке:", error);
//     alert("Ошибка при отправке формы");
//   }
    try {
        const response = await axios.post(url, payload);
        console.log("Успешно отправлено:", response.data);

        // Вместо закрытия модалки - показываем «экран успеха»
        // 1) Заполняем successMessage
        if (selectedOption.value === "consultation") {
        successMessage.value = "Спасибо за заявку!";
        } else {
        successMessage.value = "Поздравляем с покупкой!";
        }

        // 2) Ставим флаг, что форма отправлена
        isSubmitted.value = true;

        // 3) Сбрасываем поля формы (по желанию)
        formData.value = { email: "", name: "", phone: "" };
        selectedOption.value = "consultation";
        selectedDiscountOption.value = "card";
        cardInfo.value = { cardNumber: "", expiry: "", cvc: "" };
    } catch (error) {
        console.error("Ошибка при отправке:", error);
        alert("Ошибка при отправке формы");
    }
}


/* =====================================
   3) Форма для создания тем курса
===================================== */
// Извлекаем ID курса из URL, например /admin/course/20
// function getCourseIdFromUrl() {
//   const pathParts = window.location.pathname.split("/");
//   const idx = pathParts.indexOf("course");
//   return pathParts[idx + 1];
// }
// const courseId = getCourseIdFromUrl();

// const topics = ref([]);
// const showTopicForm = ref(false);
// const newTopic = ref({ title: "", description: "" });

// async function loadTopics() {
//   try {
//     const response = await axios.get(`/admin/course/${courseId}/topics`);
//     topics.value = response.data.topics || [];
//   } catch (error) {
//     console.error("Ошибка при загрузке тем:", error);
//   }
// }

// async function submitTopic() {
//   try {
//     const response = await axios.post(`/admin/course/${courseId}/topics`, newTopic.value);
//     if (response.data.topic) {
//       topics.value.push(response.data.topic);
//     }
//     newTopic.value = { title: "", description: "" };
//     showTopicForm.value = false;
//   } catch (error) {
//     console.error("Ошибка при создании темы:", error);
//   }
// }

// function toggleTopicForm() {
//   showTopicForm.value = !showTopicForm.value;
// }

/* =====================================
   4) onMounted: Инициализация и загрузка всех данных
===================================== */
onMounted(async () => {
  // Проверяем параметр verifiedUser
  const urlParams = new URLSearchParams(window.location.search);
  const verifiedUserParam = urlParams.get("verifiedUser");
  if (verifiedUserParam) {
    try {
      const pureBase64 = decodeURIComponent(verifiedUserParam);
      const decodedString = decodeURIComponent(escape(atob(pureBase64)));
      const verifiedUserData = JSON.parse(decodedString);
      localStorage.setItem("user", JSON.stringify(verifiedUserData));
      localStorage.removeItem("pendingUser");
      console.log("✅ Email подтвержден:", verifiedUserData);
    } catch (error) {
      console.error("❌ Ошибка при декодировании verifiedUserParam:", error);
    }
  }
  // Загружаем пользователя из localStorage
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    try {
      user.value = JSON.parse(storedUser);
      console.log("✅ Пользователь авторизован:", user.value);
    } catch (error) {
      console.error("❌ Ошибка при парсинге localStorage:", error);
    }
  } else {
    console.warn("⚠️ Пользователь НЕ авторизован.");
  }
  // Загружаем направления
  await loadDirections();
  // Загружаем языки
  try {
    const langResponse = await axios.get("/api/languages");
    const data = langResponse.data;
    let arr = [];
    if (Array.isArray(data)) {
      arr = data;
    } else if (data && Array.isArray(data.data)) {
      arr = data.data;
    } else {
      console.warn("Непонятная структура данных языков:", data);
    }
    languages.value = arr.map((lang) => ({
      ...lang,
      checked: lang.checked ?? false,
    }));
  } catch (error) {
    console.error("Ошибка при получении языков:", error);
  }
  // Загружаем курсы
  await fetchCourses();
  // Загружаем темы курса
});
</script>

<style scoped>
/* Пример возможных стилей для модального окна */
/* Обёртка */
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
@media (max-width: 600px) {
    .course__sidebar-check--lang{
        grid-template-columns: 1fr 1fr 1fr;
    }
}
@media (max-width: 500px) {
    .course__sidebar-check--lang{
        grid-template-columns: 1fr 1fr;
    }
}
.else__info{
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 10;
}
.modal__else{
    background-image: url(/resources/assets/img/bg__complite.png);
    background-repeat: no-repeat;
    background-position: right ;
    min-height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.modal__else h2 {
    font-weight: bold;
    margin: 0 0 20px;
    z-index: 10; /* располагаем на заднем плане */
}

.modal-content-wrapper {
  overflow: hidden;
  max-height: 0;
  transition: max-height 0.5s ease;
}

.modal-content-wrapper.open {
  max-height: 1000px; /* Достаточно большое значение, чтобы вместить содержимое */
}
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.5s ease;
}
.fade-slide-enter-from, .fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
.fade-slide-enter-to, .fade-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
}
.form__input--card{

    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    outline: none;
    font-size: 14px;
    margin: 0 0 30px;
}
.block-card{
    display: grid;
    grid-template-columns: repeat(2, 205px);
    justify-content: space-between;
}
.form-label{
    font-size: 15px;
    display: block;
    margin: 0 0 10px;
}
.payment__h3{
    margin: 0 0 20px;
}
.block-bg {
    background-color: #ffffff3a;
    padding: 15px;
    border-radius: 15px;
    text-align: justify;
}
.block__logo {
    display: flex;
    gap: 36px;
    align-items: center;
}
.forma {
    display: grid;
    grid-template-columns: 1fr;
}
.block__h2 {
    font-size: 25px;
    text-align: center;
    font-weight: bold;
    margin: 0 0 25px;
}
.custom-radio__text {
    font-size: 20px;
}
.radio-group {
    display: flex;
    flex-direction: column;
    gap: 1rem; /* отступ между радио */
    margin: 0 0 25px;
}

/* Обёртка для каждой «радиокнопки» */
.radio-option {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: border-color 0.2s;
}

/* Скрываем стандартный input[type="radio"] */
.radio-option input[type="radio"] {
    display: none;
}

/* Кружок, который будет «обводкой» радио */
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

/* При наведении делаем небольшую подсветку рамки (опционально) */
.radio-option:hover {
    border-color: #999;
}

/* Если этот label «активен» (т.е. выбран), то меняем обводку */
.radio-option.active {
    border-color: #1d6efd;
}

/* Если input в состоянии checked, внутри custom-radio появляется «заливка» */
.radio-option input[type="radio"]:checked + .custom-radio::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 8px;
    height: 8px;
    background-color: #1d6efd; /* Цвет заливки */
    border-radius: 50%;
}
.form-field--button {
    margin: 0;
    max-width: 100%;
}
.form-submit--button {
    width: 100%;
}
/* Слово «Выгодно» подсветим */
.highlight {
    background-color: #ffec99; /* Светло-жёлтый, например */
    border-radius: 4px;
    padding: 2px 4px;
    margin-left: 4px;
    font-size: 0.875em; /* Чуть помельче */
}
.form-submit {
    padding: 12px 15px;
    border-radius: 10px;
    color: #ffffff;
    border: none;
    background-color: #727dcc;
    cursor: pointer;
    transition: background-color 0.3s;
}
.form-submit:hover {
    background-color: #727dcc81;
}
.form__input {
    width: 414px;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    outline: none;
    font-size: 14px;
    margin: 0 0 30px;
}
.floating-label {
    position: relative;
    margin-bottom: 16px;
}

/* Стили для input */
.floating-label input {
    width: 414px;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    outline: none;
    font-size: 14px;

    /* Убираем цвет фона, если нужно (в зависимости от дизайна) */
    background: #fff;

    /* Если хотите анимацию поля, transition можно здесь */
    transition: border-color 0.2s;
}

/* При фокусе меняем обводку — для наглядности */
.floating-label input:focus {
    border-color: #666;
}

/* Стили для label */
.floating-label label {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #999;
    font-size: 14px;

    /* Задаём начальное смещение: label «сидит» внутри поля */
    transform: translateY(-50%);
    transition: 0.2s ease all;

    /* Если у поля белый фон, а label поверх него, 
     можно дать label белый background, чтобы «выделялся» 
     при поднятии: */
    background: #fff;
    padding: 0 4px;
}

/* При фокусе input или когда placeholder «не показан» (то есть пользователь что-то ввёл) — label всплывает */
.floating-label input:focus + label,
.floating-label input:not(:placeholder-shown) + label {
    top: -6px; /* Поднимаем */
    transform: translateY(0);
    font-size: 12px; /* Делаем шрифт чуть меньше */
    color: #666; /* Меняем цвет на более тёмный */
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.block-info {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background-color: #727dcc;
    padding: 20px 30px;
    width: 100%;
    min-height: 300px;
    height: 100%;
    color: #ffffff;
}

.block-info_bg-cyan {
    background-color: #698dc9;
}
.block-info_bg-fiolet {
    background-color: #727dcc;
}
.block-info_bg-orange {
    background-color: #d48a66;
}
.block-info_bg-green {
    background-color: #5bcaa7;
}

.block-info h2 {
    font-size: 30px;
    margin: 0;
}
.block__top img {
    width: 65px;
    height: 65px;
}
.block__top {
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: 15px;
}
.form-block {
    padding: 50px 30px;
}
.block__price {
    background-color: #ffffff46;
    color: #000;
    padding: 10px 10px;
    border-radius: 10px;
    font-size: 16px;
}
.block__difficul {
    font-size: 16px;
    color: #ffffff;
    margin: 0 0 15px;
}
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
.modal-close {
    background-color: rgba(128, 128, 128, 0.438);
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    z-index: 10;
    transition: background-color 0.3s;
}
.modal-close:hover {
    background-color: rgba(128, 128, 128, 0.637);
}
@media (max-width: 1100px) {
    .modal-content {
        max-width: 500px;
        padding-top: 0px;
        grid-template-columns: 500px;
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
        width: 500px;
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
    .radio-group {
        width: 300px;
    }
    .forma {
        display: grid;
        grid-template-columns: 1fr;
        justify-items: center;
    }
    .floating-label input {
        width: 268px;
        padding: 15px;
    }
    .form__input {
        width: 268px;
        padding: 15px;
    }
    .form-submit {
        width: 300px;
        padding: 20px;
    }
    .block__price {
        text-align: center;
    }
    .modal-content {
        width: 400px;
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
