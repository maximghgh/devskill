<template>
    <div>
        <!-- ===== Главная обёртка ===== -->
        <div class="maincontainer">
            <div class="container">
                <!-- ================= HERO / OFFER ================= -->
                <section class="offer offer_main">
                    <div class="offer__inner offer__inner_main">
                        <h1>
                            Повышайте свои знания и навыки вместе с
                            Институтом образовательных технологий
                        </h1>
                        <div class="offer__desc">
                            Наши курсы прошли более 15&nbsp;000&nbsp;школьников и
                            студентов.
                        </div>
                        <a class="button button_white button_offer-main" href="#course" @click.prevent="scrollToCourse">
                            Присоединиться
                        </a>
                    </div>
                </section>

                <!-- ================= Сотрудничество ================= -->
                <section class="cooperation">
                    <h2>
                        Мы сотрудничаем с ведущими <br />организациями и
                        университетами
                    </h2>
                    <div class="cooperation__slider">
                        <div v-for="n in 6" :key="n" class="cooperation__slide">
                            <img :src="`/img/univer_${n}.png`" />
                        </div>
                    </div>
                </section>

                <!-- ================= Категории ================= -->
                <section class="category">
                    <div class="category__inner">
                        <div class="category__one category__one_back1">
                            <div class="category__one-inner">
                                <div class="category__one-title">
                                    Учись сегодня — меняй карьеру завтра!
                                </div>
                                <div class="category__one-info">
                                    <p>Добро пожаловать в нашу онлайн-платформу дополнительного образования!
                                       Здесь вы найдёте практические курсы, составленные экспертами-практиками, чтобы быстро прокачать навыки и сразу применить их на работе.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ================= Каталог курсов ================= -->
                <section class="course" id="course">
                    <div class="course__inner">
                        <!-- mobile filter toggle -->
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

                        <!-- ===== Sidebar ===== -->
                        <div
                            class="course__sidebar"
                            :class="{ course__sidebar_open: isSidebarOpen }"
                        >
                            <!-- Тематика -->
                            <div class="course__sidebar-block">
                                <div class="course__sidebar-title">
                                    Тематика
                                </div>
                                <div
                                    class="course__sidebar-check course__sidebar-check--lang"
                                >
                                    <div
                                        v-for="language in languages"
                                        :key="language.id"
                                        class="course__sidebar-oncheck"
                                    >
                                        <input
                                            :id="`lang_${language.id}`"
                                            type="checkbox"
                                            v-model="language.checked"
                                        />
                                        <label :for="`lang_${language.id}`">
                                            <span>{{ language.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Уровень -->
                            <div class="course__sidebar-block">
                                <div class="course__sidebar-title">Уровень</div>
                                <div class="course__sidebar-check">
                                    <div
                                        v-for="lvl in levelOptions"
                                        :key="lvl.value"
                                        class="course__sidebar-oncheck"
                                    >
                                        <input
                                            :id="`lvl_${lvl.value}`"
                                            type="checkbox"
                                            :value="lvl.value"
                                            v-model="selectedDifficulties"
                                        />
                                        <label :for="`lvl_${lvl.value}`">
                                            <span>{{ lvl.label }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Длительность -->
                            <div
                                class="course__sidebar-block course__sidebar-block_noline"
                            >
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
                                            <span>От 1 до 24&nbsp;месяцев</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /sidebar -->
                        <!-- ===== Content ===== -->
                        <div class="course__content">
                            <h2>Каталог курсов</h2>
                            <div class="course__menu">
                                <div
                                    class="course__menu-one"
                                    :class="{ active: selectedDirection === 'all' }"
                                    @click="selectedDirection = 'all'"
                                >
                                    Все направления
                                </div>
                                <div
                                    v-for="direction in directions"
                                    :key="direction.id"
                                    class="course__menu-one"
                                    :class="{ active: selectedDirection === direction.id }"
                                    @click="selectedDirection = direction.id"
                                >
                                    {{ direction.name }}
                                </div>
                            </div>
                            <div class="block-left">
                                <!-- cards -->
                                <div class="course__cards">
                                    <div
                                        v-for="course in paginatedCourses"
                                        :key="course.id"
                                        :class="[
                                            'course__card',
                                            'course__card_bg1',
                                            difficultyColorClass[course.difficulty],
                                        ]"
                                    >
                                        <div class="course__card-image">
                                            <img
                                                :src="course.card_image || '/img/logo_placeholder.png'"
                                                alt="Изображение курса"
                                                width="100"
                                                height="100"
                                            />
                                        </div>
                                        <div class="course__card-title">
                                            {{ course.card_title }}
                                        </div>
                                        <div class="course__card-buttons">
                                            <div class="course__card-price">
                                                <p class="course__card-desc">
                                                    {{ difficultyTranslation[course.difficulty] }}
                                                </p>
                                                <span>{{ course.price }} P</span>
                                            </div>
                                            <div class="menu__button">
                                                <a
                                                    :href="`/cpp/${course.id}`"
                                                    class="button button_transparent"
                                                >Подробнее</a>
                                                <a
                                                    href="#"
                                                    class="button button_white button_white-card"
                                                    @click.prevent="openModal(course)"
                                                >Купить</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- pagination -->
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
                                <!-- ===== Modal purchase / consult ===== -->
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
                                            > × </button>

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
                                                        <p class="block__difficul--level">
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
                                                                    Оплатить курс
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

                                                        <div class="floating-label">
                                                            <input
                                                                id="phone"
                                                                type="tel"
                                                                required
                                                                v-model="formData.phone"
                                                                placeholder="+7 999 999-99-99"
                                                                v-mask="'+7 (###) ###-##-##'"
                                                            />
                                                        </div>

                                                        <!-- payment -->
                                                        <transition name="fade-slide">
                                                            <div
                                                                v-if="selectedOption === 'discount'"
                                                                class="payment-block space-y-4"
                                                            >
                                                                <h3 class="payment__h3">
                                                                    Оплата курса
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
                                                                <div v-if="selectedDiscountOption === 'card'" class="space-y-4">
                                                                    <div class="form-group">
                                                                        <div class="floating-label">
                                                                           <input
                                                                                id="cardNumber"
                                                                                type="text"
                                                                                v-model="cardInfo.cardNumber"
                                                                                placeholder="0000 0000 0000 0000"
                                                                                v-mask="'#### #### #### ####'"
                                                                            />         
                                                                        </div>
                                                                    </div>

                                                                    <div class="block-card">
                                                                        <div class="form-group">
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
                                                        <input
                                                            class="form-submit form-submit--button"
                                                            type="submit"
                                                            :value="selectedOption === 'consultation'
                                                                ? 'Заказать консультацию'
                                                                : 'Оплатить'"
                                                        />
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- success screen -->
                                            <div v-else class="modal__else">
                                                <div class="else__info">
                                                    <h2>{{ successMessage }}</h2>
                                                    <p v-if="lastSubmittedOption === 'consultation'">
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
                            </div>
                            <!-- /block-left -->
                        </div>
                        <!-- /content -->
                    </div>
                </section>
            </div>
        </div>
        <!-- ===== auth modal ===== -->
        <transition name="modal">
            <div
                v-if="showAuthModal"
                class="modal-overlay modal-overlay--auth"
                @click.self="showAuthModal = false"
            >
                <div class="modal-content__block modal-close--auth">
                <button class="modal-close modal-close--auth" @click="showAuthModal = false"> × </button>
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
const lastSubmittedOption = ref('');
/* ------------------------------------------------------------------ */
/* 1. Константы                                                       */
/* ------------------------------------------------------------------ */
const AUTH_KEY = 'user';

/* ------------------------------------------------------------------ */
/* 2. Сторы состояния                                                 */
/* ------------------------------------------------------------------ */
const isSidebarOpen         = ref(false);
const showAuthModal         = ref(false);
const user                  = ref(null);

const courses               = ref([]);
const languages             = ref([]);
const directions            = ref([]);

/* ------------------------------------------------------------------ */
/* 3. Постраничный вывод                                              */
/* ------------------------------------------------------------------ */
const pageSize     = 6;
const currentPage  = ref(1);

const paginatedCourses = computed(() =>
    courses.value.slice(
        (currentPage.value - 1) * pageSize,
        currentPage.value * pageSize
    )
);
const pages = computed(() =>
    Array.from(
        { length: Math.ceil(courses.value.length / pageSize) },
        (_, i) => i + 1
    )
);
/* ------------------------------------------------------------------ */
/* 4. Фильтры                                                         */
/* ------------------------------------------------------------------ */
const levelOptions = [
    { value: 'basic',    label: 'Начинающий' },
    { value: 'middle',   label: 'Средний' },
    { value: 'advanced', label: 'Продвинутый' },
    { value: 'mixed',    label: 'Смешанный' },
];

const selectedDifficulties = ref([]);
const selectedDirection    = ref('all');
const selectedDuration     = ref(null);

const selectedLanguages = computed(() =>
    languages.value.filter(l => l.checked).map(l => l.id)
);
const validateDuration = e => {
    const v = Number(e.target.value);
    selectedDuration.value = v && v >= 1 && v <= 24 ? v : '';
};
/* ------------------------------------------------------------------ */
/* 5. Справочники (словарики)                                         */
/* ------------------------------------------------------------------ */
const difficultyTranslation = {
    basic:    'Начинающий',
    middle:   'Средний',
    advanced: 'Продвинутый',
    mixed:    'Смешанный',
};
const difficultyColorClass = {
    basic:    'course__card_bg-cyan',
    middle:   'course__card_bg-fiolet',
    advanced: 'course__card_bg-orange',
    mixed:    'course__card_bg-green',
};
const difficultyBgClass = {
    basic:    'block-info_bg-cyan',
    middle:   'block-info_bg-fiolet',
    advanced: 'block-info_bg-orange',
    mixed:    'block-info_bg-green',
};
/* ------------------------------------------------------------------ */
/* 6. Модалка покупки/консультации                                    */
/* ------------------------------------------------------------------ */
const isModalOpen             = ref(false);
const selectedCourse          = ref(null);
const isSubmitted             = ref(false);
const successMessage          = ref('');

const selectedOption          = ref('consultation');
const selectedDiscountOption  = ref('card');

const formData = ref({ email: '', name: '', phone: '' });
const cardInfo = ref({ cardNumber: '', expiry: '', cvc: '' });

const openAuth = () => {
  showAuthModal.value = true;
};

const openModal = course => {
    if (!user.value) {
        showAuthModal.value = true;
        return;
    }
    selectedCourse.value = course;
    isSubmitted.value    = false;
    successMessage.value = '';
    isModalOpen.value    = true;
};
const closeModal = () => (isModalOpen.value = false);
/* ------------------------------------------------------------------ */
/* 7. API‑запросы                                                     */
/* ------------------------------------------------------------------ */
async function loadDirections() {
    try {
        const r = await axios.get('/api/directions');
        directions.value = Array.isArray(r.data) ? r.data : r.data.data ?? [];
    } catch (e) {
        console.error(e);
    }
}

async function fetchCourses() {
    try {
        const params = {};
        if (selectedLanguages.value.length)
            params.languages = selectedLanguages.value.join(',');
        if (selectedDifficulties.value.length)
            params.difficulties = selectedDifficulties.value;
        if (selectedDirection.value !== 'all')
            params.direction = selectedDirection.value;
        if (selectedDuration.value)
            params.duration = selectedDuration.value;

        const r = await axios.get('/api/courses', { params });
        courses.value = Array.isArray(r.data) ? r.data : r.data.data ?? [];
    } catch (e) {
        console.error(e);
    }
}
/* ------------------------------------------------------------------ */
/* 8. Реактивные вотчеры                                              */
/* ------------------------------------------------------------------ */
watch(
    [
        selectedDifficulties,
        selectedDirection,
        selectedDuration,
        selectedLanguages,
    ],
    fetchCourses,
    { deep: true }
);
/* ------------------------------------------------------------------ */
/* 9. Отправка форм                                                   */
/* ------------------------------------------------------------------ */
async function submitForm() {
    if (!selectedCourse.value) return;

    const payload = {
        user_id: user.value.id,
        ...formData.value,
        type: selectedOption.value,
    };

    if (selectedOption.value === 'discount') {
        payload.payment_method = selectedDiscountOption.value;
        if (selectedDiscountOption.value === 'card') {
            payload.payment_details = JSON.stringify(cardInfo.value);
        }
    } else {
        payload.payment_method  = 'card';
        payload.payment_details = null;
    }

    const url =
        selectedOption.value === 'consultation'
            ? `/api/${selectedCourse.value.id}/consultation`
            : `/api/${selectedCourse.value.id}/purchase`;

    try {
        await axios.post(url, payload);
        lastSubmittedOption.value = selectedOption.value;
        successMessage.value =
            selectedOption.value === 'consultation'
                ? 'Спасибо за заявку!'
                : 'Поздравляем с покупкой!';

        isSubmitted.value = true;

        /* очистка */
        formData.value           = { email: '', name: '', phone: '' };
        selectedOption.value     = 'consultation';
        selectedDiscountOption.value = 'card';
        cardInfo.value           = { cardNumber: '', expiry: '', cvc: '' };
    } catch (e) {
        console.error(e);
    }
}
/* ------------------------------------------------------------------ */
/* 10. Авторизация                                                    */
/* ------------------------------------------------------------------ */
function handleLoginEvent(e) {
    user.value = e.detail;
    showAuthModal.value = false;
    localStorage.setItem(AUTH_KEY, JSON.stringify(e.detail));
}
/* ------------------------------------------------------------------ */
/* 11. Монтирование компонента                                        */
/* ------------------------------------------------------------------ */
onMounted(async () => {
    const storedUser = localStorage.getItem(AUTH_KEY);

    if (storedUser) {
        try {
            user.value = JSON.parse(storedUser);
        } catch (err) {
            console.error(err);
            showAuthModal.value = true;
        }
    }
    window.addEventListener('user:login', handleLoginEvent);

    await loadDirections();

    try {
        const lr  = await axios.get('/api/languages');
        const arr = Array.isArray(lr.data) ? lr.data : lr.data.data ?? [];
        languages.value = arr.map(l => ({ ...l, checked: false }));
    } catch (e) {
        console.error(e);
    }

    await fetchCourses();
});

onUnmounted(() => {
    window.removeEventListener('user:login', handleLoginEvent);
});
/* ------------------------------------------------------------------ */
/* 12. UI‑утилиты                                                     */
/* ------------------------------------------------------------------ */
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};
function scrollToCourse() {
  const el = document.getElementById('course');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}
</script>

<style>
.payment-block{
    display: flex;
    align-items: center;
    flex-direction: column;
}
.modal-close--auth{
    background-color: #00000000 !important;
}
.modal__h2--auth{
    margin: 0 0 10px;
}
.button_white--auth{
    cursor: pointer;
    background: #1d6ffdc9;
    border: 1px solid #1d6ffdc9;
    width: 200px;
    padding: 10px;
    text-align: center;
    color: #ffffff;
}
.button_white--auth:hover{
    background: #ffffff;
    border: 1px solid #1d6ffdc9;
    color: #1d6ffdc9;
}
.modal-content--auth{
    display: flex !important; 
}
.auth-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 50px;
}
</style>

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
    .course__sidebar-check--lang {
        grid-template-columns: 1fr 1fr 1fr;
    }
}
@media (max-width: 500px) {
    .course__sidebar-check--lang {
        grid-template-columns: 1fr 1fr;
    }
}
.else__info {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    z-index: 10;
}
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


.modal-content-wrapper {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.5s ease;
}

.modal-content-wrapper.open {
    max-height: 1000px; /* Достаточно большое значение, чтобы вместить содержимое */
}
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
.form__input--card {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    outline: none;
    font-size: 14px;
    margin: 0 0 30px;
}
.block-card {
    display: grid;
    grid-template-columns: repeat(2, 205px);
    justify-content: space-between;
}

@media (max-width: 400px){
    .block-card {
        display: grid;
        grid-template-columns: repeat(1, 205px);
        justify-content: space-between;
    }
    .form__input--card{
        padding: 15px;
    }
    .block__logo{
        display: flex;
        flex-direction: column;
        gap: 15px !important;
        margin: 0 0 20px;
    }
}
.form-label {
    font-size: 15px;
    display: block;
    margin: 0 0 10px;
}
.payment__h3 {
    margin: 0 0 20px;
}
.block__difficul{
    margin: 0 0 20px;
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
    width: 100%;
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
    width: 100%;
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
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    outline: none;
    font-size: 14px;
    margin: 0 0 30px;
}
.floating-label {
    display: flex;
    width: 100%;
    position: relative;
    margin-bottom: 16px;
    flex-direction: column;
}

.form-group{
    display: flex;
    width: 100%;
    position: relative;
    flex-direction: column;
}

/* Стили для input */
.floating-label input {
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
    font-size: 20px;
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
.space-y-4{
    width: 100%;
    max-width: 500px;
}
@media (max-width: 1100px) {
    .space-y-4{
        width: 100%;
        max-width: 640px;
    }
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
@media (max-width: 480px){
    .block-card{
        grid-template-columns: repeat(2, 175px);
    }
}
@media (max-width: 428px){
    .block-card{
        grid-template-columns: repeat(2, 150px);
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
    font-size: 20px;
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
