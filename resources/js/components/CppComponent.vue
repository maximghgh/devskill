<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="course-content">
                    <div class="course-content__inner">
                        <!-- Верхний блок -->
                        <div v-if="course" class="course-content__top">
                            <div class="course-content__top-block">
                                <h1>{{ course.course_name }}</h1>
                                <p>{{ course.description }}</p>
                            </div>
                            <div class="course-content__top-block">
                                <img
                                    :src="
                                        course.description_image
                                            ? '/' + course.description_image
                                            : '../../img/course_image_main.jpg'
                                    "
                                    class="course-content__top-img"
                                    alt="Изображение курса"
                                    width="600"
                                    height="350"
                                />
                            </div>
                        </div>
                        <div v-else>Загрузка...</div>

                        <!-- Основная часть -->
                        <div class="course-content__main">
                            <!-- Левая часть (контент курса) -->
                            <div class="course-content__main-block">
                                <!-- Элемент для Editor.js -->
                                <div class="editor" id="editorjs"></div>
                                <section
                                    class="course-teachers"
                                    v-if="
                                        course &&
                                        course.teachersData &&
                                        course.teachersData.length
                                    "
                                >
                                    <h2 class="teachers-title">
                                        Наши преподаватели
                                    </h2>
                                    <div class="teachers-list">
                                        <div
                                            class="teacher-card"
                                            v-for="teacher in course.teachersData"
                                            :key="teacher.id"
                                        >
                                            <!-- Если у преподавателя есть фото, выводим его, иначе заглушку -->
                                            <img
                                                class="teacher-photo"
                                                :src="
                                                    teacher.photo
                                                        ? `/storage/${teacher.photo}`
                                                        : '../../img/nofotolk.png'
                                                "
                                                alt="Преподаватель"
                                            />
                                            <div class="teacher__info">
                                                <p class="teacher-name">
                                                    {{ teacher.name }}
                                                </p>
                                                <!-- Можно вывести и другие поля, например email -->
                                                <p class="teacher-email">
                                                    {{ teacher.position }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- Правая часть (сайдбар) -->
                            <div class="course-content__sidebar" v-if="course">
                                <div class="course-content__sidebar-inner">
                                    <!-- Цена -->
                                    <div class="course-content__sidebar-cost">
                                        {{ course.price }} руб.
                                    </div>
                                    <!-- Дополнительная информация -->
                                    <div class="course-content__sidebar-info">
                                        <p>
                                            В курс входят
                                            <b>{{ course.topics_count }}</b>
                                            темы
                                        </p>
                                        <p>
                                            <b>{{ course.simulators_count }}</b>
                                            тренажера
                                        </p>
                                        <p>
                                            <b>{{ course.hours }}</b> часа
                                            занятий
                                        </p>
                                    </div>
                                    <a
                                        v-if="course.pdf_path"
                                        :href="`/${course.pdf_path}`"
                                        download
                                        class="course-content__sidebar-program"
                                    >
                                        Программа курса
                                    </a>
                                    <small v-else class="no-pdf"></small>
                                    <a
                                        href="#"
                                        class="button button_sidebar"
                                        @click.prevent="openModal(course)"
                                    >
                                        Купить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <transition name="modal">
            <div
                v-if="showModal"
                class="modal-overlay"
                @click.self="closeModal"
            >
                <div class="modal-content__block">
                    <button class="modal-close" @click="closeModal"> × </button>

                    <!-- === Первый экран: форма === -->
                    <div
                        v-if="!submitted"
                        class="modal-content grid md:grid-cols-2"
                    >
                        <!-- левая часть — краткая информация о курсе -->
                        <div :class="['block-info', diffBg[course.difficulty]]">
                            <div class="block__top">
                                <div class="block__logo">
                                    <img
                                        :src="
                                            selectedCourse &&
                                            selectedCourse.card_image
                                                ? `/${selectedCourse.card_image}`
                                                : '/img/logo_placeholder.png'
                                        "
                                        width="65"
                                        height="65"
                                    />

                                    <h2>
                                        {{ course.card_title }}
                                    </h2>
                                </div>
                                <p class="block__difficul block-bg">
                                    {{ course.description }}
                                </p>
                            </div>

                            <div class="block__bottom mt-auto">
                                <p class="block__difficul">
                                    Уровень:
                                    {{ diffLabel[course.difficulty] }}
                                </p>
                                <div class="block__price">
                                    Цена:
                                    {{ course.price }} ₽
                                </div>
                            </div>
                        </div>

                        <!-- правая часть — форма -->
                        <div class="form-block">
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- вариант действия -->
                                <div class="radio-group">
                                    <label
                                        :class="[
                                            'radio-option',
                                            {
                                                active:
                                                    option === 'consultation',
                                            },
                                        ]"
                                        ><input
                                            type="radio"
                                            value="consultation"
                                            v-model="option"
                                        /><span class="custom-radio"></span
                                        ><span class="custom-radio__text"
                                            >Записаться на бесплатную
                                            консультацию</span
                                        ></label
                                    >
                                    <label
                                        :class="[
                                            'radio-option',
                                            {
                                                active: option === 'discount',
                                            },
                                        ]"
                                        ><input
                                            type="radio"
                                            value="discount"
                                            v-model="option"
                                        /><span class="custom-radio"></span
                                        ><span class="custom-radio__text"
                                            >Оплатить курс</span
                                        ></label
                                    >
                                </div>

                                <!-- контакты -->
                                <div class="floating-label">
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        placeholder=" "
                                        required
                                    /><label for="email"
                                        >Электронная почта</label
                                    >
                                </div>
                                <div class="floating-label">
                                    <input
                                        id="name"
                                        type="text"
                                        v-model="form.name"
                                        placeholder=" "
                                        required
                                    /><label for="name">Имя</label>
                                </div>
                                <div class="floating-label">
                                    <input
                                        id="phone"
                                        type="tel"
                                        v-model="form.phone"
                                        placeholder="+7 999 999-99-99"
                                        v-mask="'+7 (###) ###-##-##'"
                                        required
                                    />
                                </div>

                                <!-- блок оплаты появится, когда выбран discount -->
                                <transition name="fade-slide">
                                    <div
                                        v-if="option === 'discount'"
                                        class="payment-block space-y-4"
                                    >
                                        <h3 class="payment__h3">
                                            Оплата курса
                                        </h3>

                                        <div class="radio-group">
                                            <label
                                                :class="[
                                                    'radio-option',
                                                    {
                                                        active:
                                                            payWay === 'card',
                                                    },
                                                ]"
                                                ><input
                                                    type="radio"
                                                    value="card"
                                                    v-model="payWay"
                                                /><span
                                                    class="custom-radio"
                                                ></span
                                                ><span
                                                    class="custom-radio__text"
                                                    >Покупка картой</span
                                                ></label
                                            >
                                            <label
                                                :class="[
                                                    'radio-option',
                                                    {
                                                        active:
                                                            payWay === 'sbp',
                                                    },
                                                ]"
                                                ><input
                                                    type="radio"
                                                    value="sbp"
                                                    v-model="payWay"
                                                /><span
                                                    class="custom-radio"
                                                ></span
                                                ><span
                                                    class="custom-radio__text"
                                                    >Через СБП</span
                                                ></label
                                            >
                                        </div>

                                        <div
                                            v-if="payWay === 'card'"
                                            class="space-y-4"
                                        >
                                            <div class="floating-label">
                                                <input
                                                    v-model="card.cardNumber"
                                                    placeholder="0000 0000 0000 0000"
                                                    v-mask="
                                                        '#### #### #### ####'
                                                    "
                                                />
                                            </div>
                                            <div class="block-card">
                                                <input
                                                    v-model="card.expiry"
                                                    placeholder="ММ/ГГ"
                                                    class="form__input--card"
                                                    v-mask="'##/##'"
                                                />
                                                <input
                                                    v-model="card.cvc"
                                                    placeholder="CVC"
                                                    class="form__input--card"
                                                    v-mask="'###'"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </transition>

                                <input
                                    type="submit"
                                    class="form-submit w-full"
                                    :value="
                                        option === 'consultation'
                                            ? 'Заказать консультацию'
                                            : 'Оплатить'
                                    "
                                />
                            </form>
                        </div>
                    </div>

                    <!-- === Второй экран: успех === -->
                    <div v-else class="modal__else">
                        <div class="else__info">
                            <h2>{{ successText }}</h2>
                            <p v-if="option === 'consultation'">
                                Скоро с вами свяжется специалист
                            </p>
                            <p v-else>Вы успешно купили курс, поздравляем!</p>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
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
/* ──────────────────────── импорты ──────────────────────── */
import { ref, watch, onMounted, nextTick } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";

/* ─────────────────── глобальное состояние ────────────────── */
const AUTH_KEY = "user"; // имя ключа в localStorage
const user = ref(null); //  FIX: раньше переменной не было

const showAuthModal = ref(false);
const openAuth = () => {
    showAuthModal.value = true;
};
const closeAuth = () => {
    showAuthModal.value = false;
};
/* пытаемся восстановить авторизованного пользователя */
onMounted(() => {
    const stored = localStorage.getItem(AUTH_KEY);
    if (stored) {
        try {
            user.value = JSON.parse(stored);
        } catch {
            /* ignore */
        }
    }
});

/* ─────────────────── диалог покупки/консультации ─────────────────── */
const showModal = ref(false);
const selectedCourse = ref(null);

/* открыть / закрыть */
function openModal(course) {
    if (!user.value) {
        showAuthModal.value = true;   // показываем окно авторизации
        return;                       // дальше не идём
    }
    selectedCourse.value = course;
    showModal.value = true;
}
function closeModal() {
    showModal.value = false;
}

/* локальное состояние формы */
const option = ref("consultation"); // consultation | discount
const payWay = ref("card"); // card | sbp
const form = ref({ email: "", name: "", phone: "" });
const card = ref({ cardNumber: "", expiry: "", cvc: "" });
const submitted = ref(false);
const successText = ref("");

/* словари для уровней и цветов */
const diffLabel = {
    basic: "Начинающий",
    middle: "Средний",
    advanced: "Продвинутый",
    mixed: "Смешанный",
};
const diffBg = {
    basic: "block-info_bg-cyan",
    middle: "block-info_bg-fiolet",
    advanced: "block-info_bg-orange",
    mixed: "block-info_bg-green",
};

/* очистка формы при закрытии */
function reset() {
    submitted.value = false;
    successText.value = "";
    option.value = "consultation";
    payWay.value = "card";
    form.value = { email: "", name: "", phone: "" };
    card.value = { cardNumber: "", expiry: "", cvc: "" };
}
watch(showModal, (open) => !open && reset()); // закрыли ⇒ чистим

/* событие успеха пробрасываем наружу (если нужно) */
const emit = defineEmits(["success"]); //  FIX: emit теперь объявлен

/* ----------- отправка формы ----------- */
async function submit() {
    if (!selectedCourse.value) return;

    /* формируем payload */
    const payload = {
        user_id: user.value?.id ?? null,
        ...form.value,
        type: option.value,
    };
    if (option.value === "discount") {
        payload.payment_method = payWay.value;
        if (payWay.value === "card")
            payload.payment_details = JSON.stringify(card.value);
    }

    const endpoint =
        option.value === "consultation"
            ? `/api/${selectedCourse.value.id}/consultation`
            : `/api/${selectedCourse.value.id}/purchase`;

    try {
        await axios.post(endpoint, payload);
        successText.value =
            option.value === "consultation"
                ? "Спасибо за заявку!"
                : "Поздравляем с покупкой!";
        submitted.value = true;
        emit("success"); // уведомляем родителя
    } catch (err) {
        console.error(err);
    }
}

/* ────────────────────── данные курса ────────────────────── */
const course = ref(null);
let editorInstance = null;

onMounted(loadCourse);

async function loadCourse() {
    try {
        /* id курса в URL вида /cpp/17 */
        const segments = window.location.pathname.split("/");
        const courseId =
            segments[2] || new URLSearchParams(location.search).get("id");
        if (!courseId) return console.error("ID курса не найден");

        /* получаем сам курс + преподавателей */
        const { data } = await axios.get(`/api/courses/${courseId}`);
        course.value = data;

        if (Array.isArray(data.teachers)) {
            const { data: teachers } = await axios.post("/api/users/by-ids", {
                ids: data.teachers,
            });
            course.value.teachersData = teachers;
        }
        const { data: cnt } = await axios.get(
            `/api/courses/${courseId}/task-count`
        );
        course.value.simulators_count = cnt.taskCount;
        /* выводим контент через EditorJS */
        await nextTick(); // ждём, пока div#editorjs окажется в DOM

        editorInstance = new EditorJS({
            holder: "editorjs",
            readOnly: true,
            data:
                typeof data.editor_data === "string"
                    ? JSON.parse(data.editor_data)
                    : data.editor_data ?? {},
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
    } catch (e) {
        console.error("Ошибка при загрузке курса:", e);
    }
}
/* ────────────────────── экспорт утилит (если нужны) ───────────────── */
function downloadPdf(path) {
    const a = document.createElement("a");
    a.href = "/" + path.replace(/^\/+/, "");
    a.download = "";
    a.style.display = "none";
    document.body.append(a);
    a.click();
    a.remove();
}
</script>

<style scoped>
.space-y-4{
    width: 100%;
    max-width: 500px;
}
:deep(.image-tool__image-picture) {
  max-width: none;
  width: 100%;
  height: auto;
  display: block;
}
.block__logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 30px;
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
.modal-overlay {
    z-index: 5000;
}
.modal-content__block {
    z-index: 5001;
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
::v-deep(.modal-overlay) {
    z-index: 10000 !important;
}
::v-deep(.modal-content__block) {
    z-index: 10001 !important;
}
.modal-content {
    overflow-x: hidden;
    overflow-y: auto;
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
.editor {
    z-index: 1;
}

/* Кнопка закрытия */
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
.payment__h3 {
    margin: 0 0 20px;
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
.form-label {
    font-size: 15px;
    display: block;
    margin: 0 0 10px;
}
.form-block {
    padding: 50px 30px;
}
.payment-block {
    display: flex;
    flex-direction: column;
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
        padding: 15px;
    }
    .block__logo {
        flex-direction: column;
        gap: 15px;
        margin-bottom: 20px;
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
    width: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
    margin-bottom: 16px;
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
.form-submit {
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
.course-content__sidebar-info {
    margin: 0 0 30px;
}
.teacher__info {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
}
.course-content p {
    font-size: 0.8em;
    margin-bottom: 0;
    text-indent: unset;
}
.teacher-photo {
    width: 129px;
    height: 129px;
    border-radius: 50%;
}
::v-deep(.ce-block__content) {
    width: 100%;
    max-width: 800px;
}
::v-deep .codex-editor__redactor {
    padding-bottom: 50px !important;
}
.editor {
    z-index: 0;
}
/* Пример базовых стилей для блока преподавателя */
.course-teachers {
    border-radius: 10px;
    padding: 20px;
    border: 3px solid #d3d3dd;
    text-align: center;
}

.teacher-title {
    font-size: 2.3em;
    margin-bottom: 55px;
    line-height: 1.2;
    text-align: center;
}

.teachers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    justify-content: center;
    max-width: 1000px;
    margin: 0 auto;
}

.teacher-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    height: 100%;
    width: 200px;
    gap: 20px;
    background: #fff;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.411);
    text-align: center;
}

.teacher-name {
    text-align: center;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 8px;
}

.teacher-email {
    font-size: 14px;
    color: #666;
}
@media (max-width: 1100px) {
    .space-y-4 {
        width: 100%;
        max-width: 640px;
    }
    .modal__h2--auth {
        font-size: 30px;
    }
    .modal-content__block {
        max-width: 700px;
    }
    .modal-close--auth {
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
    .block-card {
        gap: 20px;
        grid-template-columns: repeat(2, 1fr);
    }
    .auth-buttons {
        flex-direction: row;
    }
}
@media (max-width: 767px) {
    .modal__h2--auth {
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
    .modal-close--auth p {
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
    .modal__h2--auth {
        font-size: 25px;
        width: 200px;
        margin: 0 0 15px;
    }
    .modal-close--auth {
        flex-direction: column;
        max-width: 400px;
    }
    .auth-buttons {
        margin-top: 25px;
        flex-direction: column;
    }
    .button_white--auth {
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
@media (max-width: 480px) {
    .block-card {
        grid-template-columns: repeat(2, 175px);
    }
}
@media (max-width: 428px) {
    .block-card {
        grid-template-columns: repeat(2, 150px);
    }
}
@media (max-width: 410px) {
    .block-info h2 {
        text-align: center;
    }
    .modal-content__block {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-close--auth {
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
</style>
