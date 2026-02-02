<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="offer offer_training">
                    <div class="offer__inner">
                        <h1>Повышение квалификации <br />для учителей</h1>
                        <div class="offer__desc">
                            <ul>
                                <li>
                                    Свидетельство о повышении квалификации гос.
                                    образца
                                </li>
                                <li>Online формат</li>
                                <li>Практические навыки</li>
                            </ul>
                        </div>
                        <div class="offer__buttons">
                            <a
                                href="#course"
                                @click.prevent="scrollToCourse"
                                class="button button_white button_offer-main">
                                Посмотреть 
                            </a>
                        </div>
                    </div>
                </section>

                <section class="course course__traning" id="course">
                    <div class="course__inner">
                        <div class="course__content">
                            <h2>Курсы</h2>
                            <div class="course__cards">
                                <!-- Перебираем только те курсы, у которых upgradequalification = 1 -->
                                <div
                                    class="course__card course__card_bg1"
                                    v-for="course in upgradeCourses"
                                    :key="course.id"
                                    :class="
                                        difficultyBgClass[course.difficulty]
                                    "
                                >
                                    <div class="course__card-image">
                                        <img
                                            :src="
                                                course.card_image
                                                    ? course.card_image
                                                    : '/public/img/logo_placeholder.png'
                                            "
                                            alt="Изображение курса"
                                        />
                                    </div>
                                    <div class="course__card-title">
                                        {{ course.course_name }}
                                    </div>
                                    <div class="course__card-desc">
                                        {{
                                            difficultyTranslation[
                                                course.difficulty
                                            ]
                                        }}
                                    </div>
                                    <div class="course__card-price">
                                        <span>{{ course.price }} P</span>
                                    </div>
                                    <div class="course__card-buttons">
                                        <a
                                            :href="`/cpp/${course.id}`"
                                            class="button button_transparent"
                                            >Подробнее</a
                                        >
                                        <a href="#" class="button button_white button_white-card"
                                            @click.prevent="openModal(course)">
                                            Купить
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <transition name="modal">
  <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-content__block">
      <button class="modal-close" @click="closeModal"> × </button>

      <!-- Экран формы -->
      <div v-if="!isSubmitted" class="modal-content">
        <!-- левая часть — инфо о курсе -->
        <div :class="['block-info', difficultyBgClass[selectedCourse?.difficulty]]">
          <div class="block__top">
            <div class="block__logo">
              <img :src="selectedCourse?.card_image || '/img/logo_placeholder.png'" width="50" height="50" />
              <h2>{{ selectedCourse?.card_title }}</h2>
            </div>
            <p class="block__difficul block-bg">{{ selectedCourse?.description }}</p>
          </div>

          <div class="block__bottom">
            <p class="block__difficul--level">
              Уровень: {{ difficultyTranslation[selectedCourse?.difficulty] }}
            </p>
            <div class="block__price">Цена: {{ selectedCourse?.price }} ₽</div>
          </div>
        </div>

        <!-- правая часть — форма -->
        <div class="form-block">
          <!-- ===== форма покупки/консультации ===== -->
          <form @submit.prevent="submitForm" class="space-y-6">
            <!-- radio main -->
            <div class="radio-group">
              <label :class="['radio-option',{active:selectedOption==='consultation'}]">
                <input type="radio" value="consultation" v-model="selectedOption" />
                <span class="custom-radio"></span>
                <span class="custom-radio__text">Записаться на бесплатную консультацию</span>
              </label>

              <label :class="['radio-option',{active:selectedOption==='discount'}]">
                <input type="radio" value="discount" v-model="selectedOption" />
                <span class="custom-radio"></span>
                <span class="custom-radio__text">Оплатить курс</span>
              </label>
            </div>

            <!-- контакты -->
            <div class="floating-label">
              <input id="email" type="email" v-model="formData.email" placeholder=" " required />
              <label for="email">Электронная почта</label>
            </div>
            <div class="floating-label">
              <input id="name" type="text" v-model="formData.name" placeholder=" " required />
              <label for="name">Имя</label>
            </div>
            <div class="floating-label">
              <input id="phone" type="tel"
                     v-model="formData.phone"
                     placeholder="+7 999 999-99-99"
                     v-mask="'+7 (###) ###-##-##'"
                     required />
            </div>

            <!-- payment (отображается, когда выбран discount) -->
            <transition name="fade-slide">
              <div v-if="selectedOption==='discount'" class="payment-block space-y-4">
                <h3 class="payment__h3">Оплата курса</h3>

                <div class="radio-group">
                  <label :class="['radio-option',{active:selectedDiscountOption==='card'}]">
                    <input type="radio" value="card" v-model="selectedDiscountOption" />
                    <span class="custom-radio"></span><span class="custom-radio__text">Покупка картой</span>
                  </label>
                  <label :class="['radio-option',{active:selectedDiscountOption==='sbp'}]">
                    <input type="radio" value="sbp" v-model="selectedDiscountOption" />
                    <span class="custom-radio"></span><span class="custom-radio__text">Через СБП</span>
                  </label>
                </div>

                <div v-if="selectedDiscountOption==='card'" class="space-y-4">
                    <div class="floating-label">
                        <input v-model="cardInfo.cardNumber" placeholder="0000 0000 0000 0000" v-mask="'#### #### #### ####'" />
                    </div>
                  
                  <div class="block-card">
                    <input v-model="cardInfo.expiry" placeholder="ММ/ГГ"
                           class="form__input--card" v-mask="'##/##'" />
                    <input v-model="cardInfo.cvc" placeholder="CVC"
                           class="form__input--card" v-mask="'###'" />
                  </div>
                </div>
              </div>
            </transition>

            <input type="submit"
                   class="form-submit w-full"
                   :value="selectedOption==='consultation' ? 'Заказать консультацию' : 'Оплатить'" />
          </form>
        </div>
      </div>

      <!-- Экран «Успех» -->
      <div v-else class="modal__else">
        <div class="else__info">
          <h2>{{ successMessage }}</h2>
          <p v-if="selectedOption==='consultation'">Скоро с вами свяжется специалист</p>
          <p v-else>Вы успешно купили курс, поздравляем!</p>
        </div>
      </div>
    </div>
  </div>
                </transition>
                <section class="course-content__teachers">
                    <h2>Наши преподаватели</h2>
                    <div class="course-content__teacher">
                        <!-- Перебираем преподавателей -->
                        <div
                            class="course-content__teacher-one course-content__teacher-one_column"
                            v-for="teacher in teachers"
                            :key="teacher.id"
                        >
                            <!-- Если у преподавателя есть собственное изображение, используем его, иначе — стандартное -->
                            <img
                                :src="
                                    teacher.photo
                                        ? `/storage/${teacher.photo}`
                                        : '../../img/nofotolk.png'
                                "
                                alt="Teacher Image"
                            />
                            <div class="course-content__teacher-info">
                                <p>{{ teacher.name }}</p>
                                <!-- Если у преподавателя есть должность, выводим её, иначе — дефолтное значение -->
                                <p>
                                    {{
                                        teacher.position
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="course-content">
                    <div class="course-content__inner">
                        <div class="course-content__top">
                            <div class="course-content__top-block">
                                <h2>О создателе курса</h2>
                                <p>
                                    Создатель образовательного курса по C++ для
                                    начинающих является опытным программистом,
                                    имеющим многолетний опыт работы в индустрии
                                    разработки программного обеспечения. Однако,
                                    в обезличенном виде его имя не раскрывается.
                                </p>
                                <p>
                                    Создавая курс, он стремится передать свои
                                    знания и опыт новичкам, которые хотят
                                    изучить язык программирования C++. Он
                                    тщательно разработал структуру курса, чтобы
                                    помочь студентам понять основы языка и
                                    научиться эффективно использовать его для
                                    создания программного обеспечения.
                                </p>
                            </div>
                            <div class="course-content__top-block">
                                <img
                                    src="../../img/begin.png"
                                    class="course-content__top-img"
                                />
                            </div>
                        </div>
                    </div>
                </section>
                <section class="cooperation">
                    <h2>Партнеры</h2>
                    <div class="cooperation__slider">
                        <div class="cooperation__slide">
                            <img src="../../img/univer_1.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="../../img/univer_2.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="../../img/univer_3.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="../../img/univer_4.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="../../img/univer_5.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="../../img/univer_6.png" />
                        </div>
                    </div>
                </section>
                <section class="questions">
                    <div class="questions__inner">
                        <h2>Вопросы</h2>
                        <!-- Цикл по массиву вопросов -->
                        <div
                            v-for="(item, index) in questions"
                            :key="index"
                            class="question"
                        >
                            <!-- Шапка вопроса -->
                            <div
                                class="question__row"
                                @click="toggleAnswer(index)"
                            >
                                <div class="question__plus">
                                    {{ item.question }}
                                </div>
                                <div class="question__quest">
                                    {{ item.isOpen ? "-" : "+" }}
                                </div>
                            </div>
                            <!-- Ответ (выпадает вниз) -->
                            <transition name="slide-fade">
                                <div
                                    v-if="item.isOpen"
                                    class="question__answer"
                                >
                                    {{ item.answer }}
                                </div>
                            </transition>
                        </div>
                    </div>
                </section>
                
            </div>
        </div>
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
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import axios from "axios";

/* ---------- модалка покупки / консультации ---------- */


const AUTH_KEY       = 'user';      // ключ в localStorage
const user           = ref(null);   // текущий пользователь
const showAuthModal  = ref(false);  // модалка «Войти/Регистрация»

const isModalOpen            = ref(false);
const isSubmitted            = ref(false);
const selectedCourse         = ref(null);

const selectedOption         = ref('consultation'); // consultation | discount
const selectedDiscountOption = ref('card');         // card | sbp

const successMessage = ref('');

const formData = ref({ email:'', name:'', phone:'' });
const cardInfo = ref({ cardNumber:'', expiry:'', cvc:'' });

function openModal(course){
  // проверка авторизации (у вас уже есть showAuthModal ↑)
  if(!user.value){ showAuthModal.value = true; return; }

  selectedCourse.value   = course;
  isModalOpen.value      = true;
  isSubmitted.value      = false;
  successMessage.value   = '';
  selectedOption.value   = 'consultation';
  selectedDiscountOption.value = 'card';
  formData.value         = { email:'', name:'', phone:'' };
  cardInfo.value         = { cardNumber:'', expiry:'', cvc:'' };
}
function closeModal(){ isModalOpen.value = false; }

/*  — отправка формы — */
async function submitForm(){
  if(!selectedCourse.value) return;

  const payload = {
    user_id : user.value?.id ?? null,
    ...formData.value,
    type    : selectedOption.value,
  };

  if(selectedOption.value==='discount'){
    payload.payment_method = selectedDiscountOption.value;
    if(selectedDiscountOption.value==='card')
      payload.payment_details = JSON.stringify(cardInfo.value);
  }else{
    payload.payment_method  = 'card';
    payload.payment_details = null;
  }

  const url = selectedOption.value==='consultation'
      ? `/api/${selectedCourse.value.id}/consultation`
      : `/api/${selectedCourse.value.id}/purchase`;

  try{
    await axios.post(url, payload);
    successMessage.value = selectedOption.value==='consultation'
        ? 'Спасибо за заявку!' : 'Поздравляем с покупкой!';
    isSubmitted.value = true;
  }catch(e){
    console.error(e);
    alert('Не удалось отправить форму');
  }
}
function handleLoginEvent(e) {
  user.value = e.detail;                // e.detail = объект пользователя
  localStorage.setItem(AUTH_KEY, JSON.stringify(e.detail));
  showAuthModal.value = false;
}

onMounted(() => {
  /* попытка вытянуть сохранённые данные */
  const saved = localStorage.getItem(AUTH_KEY);
  if (saved) {
    try { user.value = JSON.parse(saved); }
    catch { localStorage.removeItem(AUTH_KEY); }
  }

  /* подписываемся на глобальное событие “user:login”,
     которое вы уже dispatch-ите после логина на сайте */
  window.addEventListener('user:login', handleLoginEvent);

  /* …остальной ваш onMounted (loadCourses, loadFaqs …) … */
});

onUnmounted(() => {
  window.removeEventListener('user:login', handleLoginEvent);
});
/* =====================================
   1. FAQ (вопрос-ответ)
===================================== */
const questions = ref([]);

function toggleAnswer(index) {
    questions.value[index].isOpen = !questions.value[index].isOpen;
}

async function loadFaqs() {
    try {
        const response = await axios.get("/api/faqs");
        console.log("Полученные FAQ:", response.data);
        // Добавляем флаг isOpen для анимации раскрытия
        questions.value = response.data.map((item) => ({
            ...item,
            isOpen: false,
        }));
    } catch (error) {
        console.error("Ошибка при загрузке FAQ:", error);
    }
}

function beforeEnter(el) {
    el.style.height = "0";
    el.style.opacity = "0";
}
function enter(el, done) {
    const height = el.scrollHeight;
    el.style.transition = "height 0.4s ease, opacity 0.4s ease";
    requestAnimationFrame(() => {
        el.style.height = height + "px";
        el.style.opacity = "1";
    });
    el.addEventListener("transitionend", done);
}
function afterEnter(el) {
    el.style.height = "auto";
}
function beforeLeave(el) {
    el.style.height = el.scrollHeight + "px";
    el.style.opacity = "1";
}
function leave(el, done) {
    el.style.transition = "height 0.4s ease, opacity 0.4s ease";
    requestAnimationFrame(() => {
        el.style.height = "0";
        el.style.opacity = "0";
    });
    el.addEventListener("transitionend", done);
}
function afterLeave(el) {
    // можно очистить стили, если нужно
}

/* =====================================
   2. Пользователи и преподаватели
===================================== */
const users = ref([]);

// Вычисляемый массив преподавателей (пользователи с role === 2)
const teachers = computed(() => {
    return users.value.filter((user) => user.role === 2);
});

async function loadUsers() {
    try {
        const response = await axios.get("/api/users");
        users.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке пользователей:", error);
    }
}

/* =====================================
   3. Курсы (upgradequalification)
===================================== */
const allCourses = ref([]);

// Фильтруем только те курсы, у которых upgradequalification = 1
const upgradeCourses = computed(() => {
    return allCourses.value.filter(
        (course) => course.upgradequalification === 1
    );
});

async function loadCourses() {
    try {
        const response = await axios.get("/api/courses");
        allCourses.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке курсов:", error);
    }
}

/* =====================================
   4. Маппинг сложности курсов
===================================== */
const difficultyBgClass = {
    basic: "block-info_bg-cyan",
    middle: "block-info_bg-fiolet",
    advanced: "block-info_bg-orange",
    mixed: "block-info_bg-green",
};

const difficultyTranslation = {
    basic: "Начинающий",
    middle: "Фундаментальный",
    advanced: "Олимпиадный",
    mixed: "Смешанный",
};
const showRequestModal = ref(false);
const requestForm = ref({
    name: "",
    workplace: "",
    email: "",
    phone: "",
});

function closeRequestModal() {
    showRequestModal.value = false;
}

// Отправляем заявку на бэкенд
async function submitRequest() {
    try {
        await axios.post("/api/qualification-requests", requestForm.value);
        alert("Заявка отправлена! В ближайшее время с вами свяжутся.");
        closeRequestModal();
        // Очистим форму
        requestForm.value = { name: "", workplace: "", email: "", phone: "" };
    } catch (e) {
        console.error(e);
        alert("Ошибка при отправке заявки.");
    }
}
/* =====================================
   5. onMounted: загрузка всех данных
===================================== */
onMounted(() => {
    loadFaqs();
    loadUsers();
    loadCourses();
});
function scrollToCourse() {
  const el = document.getElementById('course');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}
</script>

<style scoped>
.space-y-4{
    width: 100%;
    max-width: 500px;
}
.block__top{
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: 15px;
}
.block__difficul{
    margin: 0 0 20px;
}
.block__logo{
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
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(12, 24, 53, 0.25);
}

/* Основной контент модалки покупки */
::v-deep(.modal-overlay) {
    z-index: 10000 !important;
}
::v-deep(.modal-content__block) {
    z-index: 10001 !important;
}
.modal-content {
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 46%) minmax(0, 54%);
    padding: 0;
    background: #fff;
    max-width: 1000px;
    width: 100%;
    min-height: 300px;
    position: relative;
    border-radius: 18px;
    max-height: 85vh;
}
.editor {
    z-index: 1;
}

/* Кнопка закрытия */
.modal-close {
    font-size: 20px;
    background: #fff;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.18);
}
.modal-close:hover {
    background: #f2f4f8;
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
    text-align: center;
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
    padding: 28px 32px;
    width: 100%;
    min-height: 300px;
    height: 100%;
    color: #fff;
    gap: 16px;
    overflow-y: auto;
    max-height: 85vh;
}
.block-info h2 {
    font-size: 28px;
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
.form__input {
    width: 100%;
    max-width: 414px;
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
    padding: 50px 36px;
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
/* ---------- Оверлей ---------- */


.course-content__teacher-one img {
    width: 129px;
    height: 129px;
    border-radius: 50%;
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

/* Контейнер */
.questions__inner {
    padding: 0 10px;
    margin: 20px auto;
}

/* Весь блок вопроса */
.question {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto 10px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden; /* Чтобы анимация выглядела аккуратно */
}

/* Шапка (иконка слева, вопрос справа) */
.question__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    min-height: 55px;
    cursor: pointer;
}

/* Иконка слева (текст вопроса) */
.question__plus {
    user-select: none;
    font-size: 16px;
    font-weight: bold;
}

/* Знак + или - справа */
.question__quest {
    font-size: 25px;
    font-weight: bold;
    text-align: right;
    margin-left: auto;
}

/* Ответ (контейнер) */
.question__answer {
    font-size: 14px;
    color: #555;
    padding: 0 15px 15px 15px; /* отступы вокруг ответа */
    line-height: 1.4;
    border-top: 1px solid #eee; /* тонкая линия сверху */
}

/* ======= Анимация (slide + fade) ======= */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: max-height 0.4s linear, opacity 0.4s linear;
    overflow: hidden;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    max-height: 0;
    opacity: 0;
}

.slide-fade-enter-to,
.slide-fade-leave-from {
    max-height: 500px; /* или больше, если ответ длинный */
    opacity: 1;
}
@media (max-width: 1100px) {
    .space-y-4{
        max-width: 640px;
    }
    .form__input{
        max-width: 614px;
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
    .block-info {
        max-height: none;
        overflow-y: visible;
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
</style>
