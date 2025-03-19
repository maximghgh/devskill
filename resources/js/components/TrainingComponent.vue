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
                            <div class="button button_white button_offer-main">
                                Подать заявку
                            </div>
                            <div
                                class="button button_offer-main-transparent button_offer-main"
                            >
                                Подать коллективную заявку
                            </div>
                        </div>
                    </div>
                </section>
                <section class="course course__traning">
                    <div class="course__inner">
                        <div class="course__content">
                            <h2>Курсы</h2>
                            <div class="course__cards">
                                <div
                                    class="course__card course__card_bg-cyan course__card_bg1"
                                >
                                    <div class="course__card-image">
                                        <img src="img/logo_c++.png" />
                                    </div>
                                    <div class="course__card-title">C++</div>
                                    <div class="course__card-desc">Базовый</div>
                                    <div class="course__card-price">
                                        <span>5000 P</span>
                                        <del>8900 P</del>
                                    </div>
                                    <div class="course__card-buttons">
                                        <a
                                            href="#"
                                            class="button button_transparent"
                                            >Подробнее</a
                                        >
                                        <a
                                            href="#"
                                            class="button button_white button_white-card"
                                            >Купить</a
                                        >
                                    </div>
                                </div>
                                <div
                                    class="course__card course__card_bg-green course__card_bg2"
                                >
                                    <div class="course__card-image">
                                        <img src="img/logo_python.png" />
                                    </div>
                                    <div class="course__card-title">Phyton</div>
                                    <div class="course__card-desc">Базовый</div>
                                    <div class="course__card-price">
                                        <span>5000 P</span>
                                        <del>8900 P</del>
                                    </div>
                                    <div class="course__card-buttons">
                                        <a
                                            href="#"
                                            class="button button_transparent"
                                            >Подробнее</a
                                        >
                                        <a
                                            href="#"
                                            class="button button_white button_white-card"
                                            >Купить</a
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="course-content__teachers">
                    <h2>Наши преподаватели</h2>
                    <div class="course-content__teacher">
                        <div
                            class="course-content__teacher-one course-content__teacher-one_column"
                        >
                            <img src="img/teacher.jpg" />
                            <div class="course-content__teacher-info">
                                <p>Иванов Иван Иванович</p>
                                <p>Доцент кафедры ИВТ, ИжГТУ</p>
                            </div>
                        </div>
                        <div
                            class="course-content__teacher-one course-content__teacher-one_column"
                        >
                            <img src="img/teacher.jpg" />
                            <div class="course-content__teacher-info">
                                <p>Иванов Иван Иванович</p>
                                <p>Доцент кафедры ИВТ, ИжГТУ</p>
                            </div>
                        </div>
                        <div
                            class="course-content__teacher-one course-content__teacher-one_column"
                        >
                            <img src="img/teacher.jpg" />
                            <div class="course-content__teacher-info">
                                <p>Иванов Иван Иванович</p>
                                <p>Доцент кафедры ИВТ, ИжГТУ</p>
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
                                    src="img/begin.png"
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
                            <img src="img/univer_1.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="img/univer_2.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="img/univer_3.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="img/univer_4.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="img/univer_5.png" />
                        </div>
                        <div class="cooperation__slide">
                            <img src="img/univer_6.png" />
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
                        <div class="form-field">
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
import { ref, onMounted } from "vue";
import axios from "axios";

const questions = ref([]);

function toggleAnswer(index) {
    questions.value[index].isOpen = !questions.value[index].isOpen;
}

async function loadFaqs() {
    try {
        const response = await axios.get("/api/faqs");
        // Проверим, что приходит
        console.log("Полученные FAQ:", response.data);
        // Дополнительно добавим флаг isOpen
        questions.value = response.data.map((item) => ({
            ...item,
            isOpen: false,
        }));
    } catch (error) {
        console.error("Ошибка при загрузке FAQ:", error);
    }
}

onMounted(() => {
    loadFaqs();
});

function beforeEnter(el) {
    el.style.height = "0";
    el.style.opacity = "0";
}

function enter(el, done) {
    // Вычисляем полную высоту содержимого
    const height = el.scrollHeight;
    el.style.transition = "height 0.4s ease, opacity 0.4s ease";
    // Используем requestAnimationFrame для корректного запуска анимации
    requestAnimationFrame(() => {
        el.style.height = height + "px";
        el.style.opacity = "1";
    });
    el.addEventListener("transitionend", done);
}

function afterEnter(el) {
    // После анимации сбрасываем высоту, чтобы при изменении контента не было ограничений
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
    // Можно добавить очистку или сброс, если потребуется
}
</script>

<style scoped>
/* Контейнер */
.questions__inner {
    padding: 0 10px;
    margin: 20px auto;
}

/* Весь блок вопроса */
.question {
    width: 1200px;
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
</style>
