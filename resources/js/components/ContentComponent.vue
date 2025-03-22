<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="skill">
                    <div class="skill__title skill__title-second">
                        <div class="skill__title-inner" @click.prevent="goBack">
                            <span class="skill__title-arrow">&larr;</span>
                            <a class="skill__title-h1">{{
                                course.card_title
                            }}</a>
                        </div>
                    </div>
                    <div class="skill__inner">
                        <div class="skill__sidebar">
                            <div class="skill__sidebar-inner">
                                <!-- Прогресс курса -->
                                <div class="skill__progress">
                                    <div class="skill__progress-title">
                                        Прогресс курса <span>40%</span>
                                    </div>
                                    <div class="skill__progress-line">
                                        <span style="width: 40%"></span>
                                    </div>
                                </div>
                                <!-- Список тем (основное меню) -->
                                <div
                                    v-if="!selectedTopic"
                                    class="skill__sidebar-menu-main"
                                >
                                    <ul class="skill__menu-main-list">
                                        <li
                                            v-for="topic in topics"
                                            :key="topic.id"
                                            :class="[
                                                'skill__menu-main-item',
                                                {
                                                    'skill__menu-main-item_active':
                                                        selectedTopic &&
                                                        selectedTopic.id ===
                                                            topic.id,
                                                },
                                            ]"
                                        >
                                            <!-- При клике выбираем тему -->
                                            <a
                                                href="#"
                                                @click.prevent="
                                                    selectTopic(topic)
                                                "
                                            >
                                                {{ topic.title }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <!-- Дополнительное меню (главы), если выбрана тема -->
                                <div
                                    v-else-if="selectedTopic"
                                    class="skill__sidebar-menu-second"
                                >
                                    <div class="skill__menu-second-title">
                                        {{ selectedTopic.title }}
                                    </div>
                                    <div class="skill__menu-second-block">
                                        <!-- <div class="skill__menu-second-progress"></div> -->
                                        <ul class="skill__menu-second-list">
                                            <li
                                                v-for="chapter in selectedTopic.chapters"
                                                :key="chapter.id"
                                                :class="[
                                                    'skill__menu-second',
                                                    {
                                                        'skill__menu-second_active':
                                                            selectedChapter &&
                                                            selectedChapter.id ===
                                                                chapter.id,
                                                    },
                                                ]"
                                            >
                                                <!-- При клике выбираем главу -->
                                                <a
                                                    href="#"
                                                    @click.prevent="
                                                        selectChapter(chapter)
                                                    "
                                                >
                                                    <span>{{
                                                        chapter.title
                                                    }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <button
                                            @click="deselectTopic"
                                            class="button button_transparent button--xl"
                                        >
                                            Все темы
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="skill__content">
                            <div class="skill__content-inner">
                                <!-- 1) Если вообще ничего не выбрано — показываем приветствие -->
                                <div
                                    class="welcome_course"
                                    v-if="!selectedTopic"
                                >
                                    <h1>
                                        Добро пожаловать, на курс
                                        {{ course.card_title }}!
                                    </h1>
                                    <p>Выберите тему, чтобы начать изучение.</p>
                                </div>
                                <!-- 2) Если выбрана тема, но не выбрана глава — показываем что-то общее по теме -->
                                <div
                                    v-else-if="
                                        selectedTopic && !selectedChapter
                                    "
                                >
                                    <h1>{{ selectedTopic.title }}</h1>
                                    <p class="info__topic">
                                        {{ selectedTopic.description }}
                                    </p>
                                </div>
                                <div v-else>
                                    <!-- Заголовок: название темы + название главы -->
                                    <h1>{{ selectedChapter.title }}</h1>

                                    <!-- Если глава — видео -->
                                    <div
                                        v-if="selectedChapter.type === 'video'"
                                    >
                                        <!-- Допустим, в БД есть поле video_url -->
                                        <iframe
                                            v-if="selectedChapter.video_url"
                                            :src="selectedChapter.video_url"
                                            controls
                                            allowfullscreen
                                            class="iframe"
                                        ></iframe>
                                        <h2>Описание</h2>
                                        <!-- Если content хранится как HTML или текст, выводим -->
                                        <div id="editorjs"></div>
                                    </div>
                                    <!-- Если глава — просто текст -->
                                    <div
                                        v-else-if="
                                            selectedChapter.type === 'text'
                                        "
                                    >
                                        <div id="editorjs"></div>
                                    </div>
                                    <!-- Если глава — задание (task) -->
                                    <div
                                        v-else-if="
                                            selectedChapter.type === 'task'
                                        "
                                    >
                                        <div id="editorjs"></div>
                                        <button
                                            @click="toggleSolution"
                                            class="button button--sub"
                                        >
                                            {{
                                                showSolution
                                                    ? "Показать ответ"
                                                    : "Показать ответ"
                                            }}
                                        </button>
                                        <!-- Модальное окно (с переходом fade) -->
                                        <transition name="fade">
                                            <div
                                                class="modal"
                                                v-if="showSolution"
                                            >
                                                <!-- Полупрозрачный фон (оверлей). При клике закрываем модалку -->
                                                <div
                                                    class="modal-overlay"
                                                    @click="
                                                        showSolution = false
                                                    "
                                                ></div>

                                                <!-- Содержимое модалки -->
                                                <div class="modal-content">
                                                    <h3>Правильный ответ:</h3>
                                                    <!-- Выводим правильный ответ -->
                                                    <div
                                                        v-html="
                                                            selectedChapter.correct_answer
                                                        "
                                                    ></div>
                                                    <!-- Кнопка «Закрыть» -->
                                                    <button
                                                        @click="
                                                            showSolution = false
                                                        "
                                                        class="button"
                                                    >
                                                        Закрыть
                                                    </button>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                    <!-- Если глава — терминология (terms) -->
                                    <div
                                        v-else-if="
                                            selectedChapter.type === 'terms'
                                        "
                                    >
                                        <div id="editorjs"></div>
                                    </div>
                                    <!-- На всякий случай fallback для неизвестного типа -->
                                    <div v-else>
                                        <p>
                                            Неизвестный тип главы:
                                            {{ selectedChapter.type }}
                                        </p>
                                    </div>
                                    <!-- Общие кнопки -->

                                    <div class="skill__buttons">
                                        <!-- Кнопка "Назад" с анимацией плавного появления -->
                                        <transition name="fade">
                                            <button
                                                v-if="canGoPrev"
                                                @click="goToPrevChapter"
                                                class="button button_skill-prev"
                                            >
                                                Назад
                                            </button>
                                        </transition>

                                        <!-- Кнопка "Вперёд" с анимацией сдвига вправо -->
                                        <transition name="slide-right">
                                            <button
                                                v-if="canGoNext"
                                                @click="goToNextChapter"
                                                class="button button_skill-next"
                                            >
                                                Вперёд
                                            </button>
                                        </transition>
                                    </div>
                                </div>
                            </div>
                            <div class="comments" v-if="courseComments">
                                <h2>Комментарии</h2>
                                <div class="comments__inner">
                                    <div class="comment__title">
                                        <span class="comment__count">{{
                                            courseComments.length
                                        }}</span>
                                        комментариев
                                    </div>
                                    <!-- Форма для добавления нового комментария -->
                                    <div class="comment__area">
                                        <textarea
                                            v-model="newComment"
                                            placeholder="Мой комментарий..."
                                        ></textarea>
                                    </div>
                                    <div class="comment__button">
                                        <button
                                            class="button button_comment-cancel"
                                            @click="newComment = ''"
                                        >
                                            Отмена
                                        </button>
                                        <button
                                            class="button button_comment-send"
                                            @click="submitComment"
                                        >
                                            Оставить комментарий
                                        </button>
                                    </div>
                                    <!-- Список комментариев -->
                                    <div class="comment__list">
                                        <div
                                            class="comment__branch"
                                            v-for="comment in courseComments"
                                            :key="comment.id"
                                        >
                                            <div class="comment__one">
                                                <div class="comment__avatar">
                                                    <img
                                                        :src="
                                                            comment.user_avatar
                                                                ? `/storage/${comment.user_avatar}`
                                                                : 'img/avatar.jpg'
                                                        "
                                                        alt="Avatar"
                                                    />
                                                </div>
                                                <div class="comment__block">
                                                    <div class="comment__name">
                                                        <!-- Если есть user, выводим user.name, иначе "Аноним" -->
                                                        {{
                                                            comment.user
                                                                ? comment.user
                                                                      .name
                                                                : "Аноним"
                                                        }}
                                                    </div>
                                                    <div class="comment__time">
                                                        {{
                                                            formatTime(
                                                                comment.created_at
                                                            )
                                                        }}
                                                    </div>
                                                    <div class="comment__text">
                                                        {{ comment.body }}
                                                    </div>
                                                    <div class="comment__likes">
                                                        <div
                                                            class="comment__like comment__like-good"
                                                            @click="
                                                                likeComment(
                                                                    comment
                                                                )
                                                            "
                                                        >
                                                            <span>
                                                                <svg
                                                                    viewBox="0 0 800 800"
                                                                >
                                                                    <path
                                                                        d="M530,150c0-50-49.4-83.3-88-83.3c-26.9,0-29,20.4-33.1,60.7c-1.8,17.7-4,39.1-8.9,64.3 c-12.9,66.7-57.3,152-99.9,177.5v197.5c-0.1,75,24.9,100,133.2,100h125.8c72.5,0,90.1-47.8,96.6-65.5l0.4-1.2 c3.8-10.2,11.9-18.2,21.3-27.3c10.3-10.2,22.1-21.8,30.9-39.3c10.4-20.8,9-39.2,7.8-55.7c-0.8-10-1.5-19.2,0.6-27.7 c2.1-9,4.9-15.8,7.5-22.4c4.8-11.9,9.2-22.9,9.2-44.3c0-50-24.9-83.3-77.2-83.3H516.7C516.7,300.1,530,200,530,150z M183.3,333.3 c-27.6,0-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50s50-22.4,50-50V383.3C233.3,355.7,210.9,333.3,183.3,333.3z"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span
                                                                class="comment__like-count"
                                                                >{{
                                                                    comment.likes
                                                                }}</span
                                                            >
                                                        </div>

                                                        <!-- Кнопка "Дизлайк" -->
                                                        <div
                                                            class="comment__like comment__like-bad"
                                                            @click="
                                                                dislikeComment(
                                                                    comment
                                                                )
                                                            "
                                                        >
                                                            <span>
                                                                <svg
                                                                    viewBox="0 0 800 800"
                                                                >
                                                                    <path
                                                                        class="st0"
                                                                        d="M516.7,433.4h139.5c52.3,0,77.2-33.3,77.2-83.3c0-21.4-4.4-32.4-9.2-44.3c-2.6-6.6-5.4-13.4-7.5-22.4 c-2.1-8.5-1.4-17.7-0.6-27.7c1.2-16.5,2.6-34.9-7.8-55.7c-8.8-17.5-20.6-29.1-30.9-39.3c-9.4-9.1-17.5-17.1-21.3-27.3l-0.4-1.2 c-6.5-17.7-24.1-65.5-96.6-65.5H433.3c-108.3,0-133.3,25-133.2,100v197.5c42.6,25.5,87,110.8,99.9,177.5c4.9,25.2,7.1,46.6,8.9,64.3 c4.1,40.3,6.2,60.7,33.1,60.7c38.6,0,88-33.3,88-83.3S516.7,433.3,516.7,433.4z M233.3,350.1V116.8c0-27.6-22.4-50-50-50 s-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50S233.3,377.7,233.3,350.1z"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span
                                                                class="comment__like-count"
                                                                >{{
                                                                    comment.dislikes
                                                                }}</span
                                                            >
                                                        </div>
                                                        <div
                                                            class="comment__like-answer"
                                                            @click="
                                                                replyToComment(
                                                                    comment
                                                                )
                                                            "
                                                        >
                                                            Ответить
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Форма ответа (если выбран этот комментарий) -->
                                            <div
                                                v-if="replyTo === comment.id"
                                                class="comment__one comment__one_respond"
                                            >
                                                <div class="comment__avatar">
                                                    <img
                                                        src="img/avatar.jpg"
                                                        alt="Avatar"
                                                    />
                                                </div>
                                                <div class="comment__block">
                                                    <div class="comment__area">
                                                        <textarea
                                                            v-model="
                                                                replyComment
                                                            "
                                                            placeholder="Мой комментарий..."
                                                        ></textarea>
                                                    </div>
                                                    <div
                                                        class="comment__button"
                                                    >
                                                        <button
                                                            class="button button_comment-cancel"
                                                            @click="cancelReply"
                                                        >
                                                            Отмена
                                                        </button>
                                                        <button
                                                            class="button button_comment-send"
                                                            @click="
                                                                submitReply(
                                                                    comment
                                                                )
                                                            "
                                                        >
                                                            Оставить комментарий
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Вывод вложенных комментариев -->
                                            <div
                                                v-if="
                                                    comment.children &&
                                                    comment.children.length
                                                "
                                            >
                                                <!-- Цикл по дочерним комментариям -->
                                                <div
                                                    class="comment__branch"
                                                    v-for="child in comment.children"
                                                    :key="child.id"
                                                >
                                                    <div
                                                        class="comment__one comment__one_respond"
                                                    >
                                                        <div
                                                            class="comment__avatar"
                                                        >
                                                            <img
                                                                :src="
                                                                    child.user_avatar
                                                                        ? `/storage/${child.user_avatar}`
                                                                        : 'img/avatar.jpg'
                                                                "
                                                                alt="Avatar"
                                                            />
                                                        </div>
                                                        <div
                                                            class="comment__block"
                                                        >
                                                            <div
                                                                class="comment__name"
                                                            >
                                                                {{
                                                                    child.user_name ||
                                                                    currentUserName
                                                                }}
                                                            </div>
                                                            <div
                                                                class="comment__time"
                                                            >
                                                                {{
                                                                    formatTime(
                                                                        child.created_at
                                                                    )
                                                                }}
                                                            </div>
                                                            <div
                                                                class="comment__text"
                                                            >
                                                                {{ child.body }}
                                                            </div>
                                                            <div
                                                                class="comment__likes"
                                                            >
                                                                <div
                                                                    class="comment__like comment__like-good"
                                                                    @click="
                                                                        likeComment(
                                                                            comment
                                                                        )
                                                                    "
                                                                >
                                                                    <span>
                                                                        <svg
                                                                            viewBox="0 0 800 800"
                                                                        >
                                                                            <path
                                                                                d="M530,150c0-50-49.4-83.3-88-83.3c-26.9,0-29,20.4-33.1,60.7c-1.8,17.7-4,39.1-8.9,64.3 c-12.9,66.7-57.3,152-99.9,177.5v197.5c-0.1,75,24.9,100,133.2,100h125.8c72.5,0,90.1-47.8,96.6-65.5l0.4-1.2 c3.8-10.2,11.9-18.2,21.3-27.3c10.3-10.2,22.1-21.8,30.9-39.3c10.4-20.8,9-39.2,7.8-55.7c-0.8-10-1.5-19.2,0.6-27.7 c2.1-9,4.9-15.8,7.5-22.4c4.8-11.9,9.2-22.9,9.2-44.3c0-50-24.9-83.3-77.2-83.3H516.7C516.7,300.1,530,200,530,150z M183.3,333.3 c-27.6,0-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50s50-22.4,50-50V383.3C233.3,355.7,210.9,333.3,183.3,333.3z"
                                                                            />
                                                                        </svg>
                                                                    </span>
                                                                    <span
                                                                        class="comment__like-count"
                                                                        >{{
                                                                            comment.likes
                                                                        }}</span
                                                                    >
                                                                </div>

                                                                <!-- Кнопка "Дизлайк" -->
                                                                <div
                                                                    class="comment__like comment__like-bad"
                                                                    @click="
                                                                        dislikeComment(
                                                                            comment
                                                                        )
                                                                    "
                                                                >
                                                                    <span>
                                                                        <svg
                                                                            viewBox="0 0 800 800"
                                                                        >
                                                                            <path
                                                                                class="st0"
                                                                                d="M516.7,433.4h139.5c52.3,0,77.2-33.3,77.2-83.3c0-21.4-4.4-32.4-9.2-44.3c-2.6-6.6-5.4-13.4-7.5-22.4 c-2.1-8.5-1.4-17.7-0.6-27.7c1.2-16.5,2.6-34.9-7.8-55.7c-8.8-17.5-20.6-29.1-30.9-39.3c-9.4-9.1-17.5-17.1-21.3-27.3l-0.4-1.2 c-6.5-17.7-24.1-65.5-96.6-65.5H433.3c-108.3,0-133.3,25-133.2,100v197.5c42.6,25.5,87,110.8,99.9,177.5c4.9,25.2,7.1,46.6,8.9,64.3 c4.1,40.3,6.2,60.7,33.1,60.7c38.6,0,88-33.3,88-83.3S516.7,433.3,516.7,433.4z M233.3,350.1V116.8c0-27.6-22.4-50-50-50 s-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50S233.3,377.7,233.3,350.1z"
                                                                            />
                                                                        </svg>
                                                                    </span>
                                                                    <span
                                                                        class="comment__like-count"
                                                                        >{{
                                                                            comment.dislikes
                                                                        }}</span
                                                                    >
                                                                </div>
                                                                <div
                                                                    class="comment__like-answer"
                                                                    @click="
                                                                        replyToComment(
                                                                            child
                                                                        )
                                                                    "
                                                                >
                                                                    Ответить
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                        © 2011-2023 гг. Сайт не является
                                        публичной офертой и носит информационный
                                        характер. Все материалы данного сайта
                                        являются объектами авторского права (в
                                        том числе дизайн). Запрещается
                                        копирование, распространение (в том
                                        числе путем копирования на другие сайты
                                        и ресурсы в Интернете) или любое иное
                                        использование информации и объектов без
                                        предварительного согласия
                                        правообладателя.
                                    </div>
                                </div>
                            </footer>
                        </div>
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
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";

dayjs.extend(relativeTime);
dayjs.locale("ru");

/* ======================================================
   1. Работа с курсом, темами и главами
===================================================== */
const course = ref(window.initialCourseData || {});
const topics = ref(course.value.topics || []);
const selectedTopic = ref(null);
const selectedChapter = ref(null);

let editorInstance = null;

const showSolution = ref(false);

function toggleSolution() {
    showSolution.value = !showSolution.value;
}

function goBack() {
    window.history.back();
}

function selectTopic(topic) {
    console.log("Выбрали тему:", topic);
    selectedTopic.value = topic;
    selectedChapter.value = null;
    destroyEditor();
}

function deselectTopic() {
    selectedTopic.value = null;
    selectedChapter.value = null;
    destroyEditor();
}

function selectChapter(chapter) {
    console.log("Выбрали главу:", chapter);
    selectedChapter.value = chapter;
}

function goToPrevChapter() {
    if (
        selectedTopic.value &&
        selectedTopic.value.chapters &&
        selectedChapter.value
    ) {
        const chapters = selectedTopic.value.chapters;
        const index = chapters.findIndex(
            (ch) => ch.id === selectedChapter.value.id
        );
        if (index > 0) selectChapter(chapters[index - 1]);
    }
}

function goToNextChapter() {
    if (
        selectedTopic.value &&
        selectedTopic.value.chapters &&
        selectedChapter.value
    ) {
        const chapters = selectedTopic.value.chapters;
        const index = chapters.findIndex(
            (ch) => ch.id === selectedChapter.value.id
        );
        if (index < chapters.length - 1) selectChapter(chapters[index + 1]);
    }
}

const canGoPrev = computed(() => {
    if (
        !selectedTopic.value ||
        !selectedTopic.value.chapters ||
        !selectedChapter.value
    )
        return false;
    const chapters = selectedTopic.value.chapters;
    const index = chapters.findIndex(
        (ch) => ch.id === selectedChapter.value.id
    );
    return index > 0;
});
const canGoNext = computed(() => {
    if (
        !selectedTopic.value ||
        !selectedTopic.value.chapters ||
        !selectedChapter.value
    )
        return false;
    const chapters = selectedTopic.value.chapters;
    const index = chapters.findIndex(
        (ch) => ch.id === selectedChapter.value.id
    );
    return index < chapters.length - 1;
});

function destroyEditor() {
    if (editorInstance && typeof editorInstance.destroy === "function") {
        console.log("Уничтожаем предыдущий экземпляр Editor.js");
        editorInstance.destroy();
        editorInstance = null;
    }
}

function initEditor(contentData) {
    console.log("initEditor: пришли данные контента:", contentData);
    destroyEditor();
    if (typeof contentData === "string") {
        try {
            contentData = JSON.parse(contentData);
            console.log("initEditor: успешно распарсили JSON:", contentData);
        } catch (error) {
            console.error("Ошибка парсинга JSON контента главы:", error);
            contentData = {};
        }
    }
    editorInstance = new EditorJS({
        holder: "editorjs",
        readOnly: true,
        data: contentData,
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
    console.log("EditorJS инициализирован в режиме read-only.");
}

watch(selectedChapter, (newChapter) => {
    console.log("watch selectedChapter -> newChapter:", newChapter);
    if (newChapter && newChapter.content) {
        console.log(
            "Тип главы:",
            newChapter.type,
            "Содержимое content:",
            newChapter.content
        );
        if (["text", "task", "terms"].includes(newChapter.type)) {
            initEditor(newChapter.content);
        } else if (newChapter.type === "video") {
            console.log(
                "Глава с видео. Editor.js не нужен, уничтожаем экземпляр."
            );
            destroyEditor();
        } else {
            console.warn(
                "Неизвестный тип главы:",
                newChapter.type,
                "— уничтожаем Editor.js."
            );
            destroyEditor();
        }
    } else {
        console.log(
            "Глава не выбрана или нет поля content. Уничтожаем Editor.js."
        );
        destroyEditor();
    }
});

/* ======================================================
   2. Комментарии для курса
===================================================== */
const courseComments = ref([]);
const newComment = ref("");
const replyTo = ref(null);
const replyComment = ref("");
const currentUserName = ref("Аноним");

// Делаем courseId реактивной переменной
const courseId = ref(null);

function formatTime(dateString) {
    return dayjs(dateString).fromNow();
}

async function loadCourseComments() {
    try {
        const response = await axios.get(
            `/api/courses/${courseId.value}/comments`
        );
        courseComments.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке комментариев:", error);
    }
}

async function submitComment() {
    if (!newComment.value.trim()) return;
    try {
        // Формируем payload для комментария
        const payload = { body: newComment.value };

        // Извлекаем данные пользователя из localStorage, если они есть
        const storedUser = localStorage.getItem("user");
        if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            if (parsedUser && parsedUser.id) {
                payload.user_id = parsedUser.id;
            }
        }
        const response = await axios.post(
            `/api/courses/${courseId.value}/comments`,
            payload
        );
        if (courseComments.value) {
            courseComments.value.push(response.data);
        } else {
            courseComments.value = [response.data];
        }
        newComment.value = "";
    } catch (error) {
        console.error("Ошибка при отправке комментария:", error);
    }
}

function replyToComment(comment) {
    replyTo.value = comment.id;
    replyComment.value = "";
}

async function submitReply(parentComment) {
    if (!replyComment.value.trim()) return;
    try {
        const payload = {
            body: replyComment.value,
            parent_id: parentComment.id,
        };
        // Также добавляем данные пользователя, если они есть
        const storedUser = localStorage.getItem("user");
        if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            if (parsedUser && parsedUser.id) {
                payload.user_id = parsedUser.id;
            }
            if (parsedUser && parsedUser.name) {
                payload.user_name = parsedUser.name;
            }
        }
        const response = await axios.post(
            `/api/courses/${courseId.value}/comments`,
            payload
        );
        if (!parentComment.children) {
            parentComment.children = [];
        }
        parentComment.children.push(response.data);
        replyComment.value = "";
        replyTo.value = null;
    } catch (error) {
        console.error("Ошибка при отправке ответа:", error);
    }
}

function cancelReply() {
    replyComment.value = "";
    replyTo.value = null;
}

/* ======================================================
   3. Прочие данные: FAQ, курсы для повышения квалификации и маппинг сложности
===================================================== */
const faqQuestions = ref([]);
async function loadFaqs() {
    try {
        const response = await axios.get("/api/faqs");
        console.log("Полученные FAQ:", response.data);
        faqQuestions.value = response.data.map((item) => ({
            ...item,
            isOpen: false,
        }));
    } catch (error) {
        console.error("Ошибка при загрузке FAQ:", error);
    }
}

const allCourses = ref([]);
const upgradeCourses = computed(() => {
    return allCourses.value.filter((c) => c.upgradequalification === 1);
});
async function loadCourses() {
    try {
        const response = await axios.get("/api/courses");
        allCourses.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке курсов:", error);
    }
}

const difficultyBgClass = {
    basic: "block-info_bg-cyan",
    middle: "block-info_bg-fiolet",
    advanced: "block-info_bg-orange",
    mixed: "block-info_bg-green",
};
const difficultyTranslation = {
    basic: "Начинающий",
    middle: "Средний",
    advanced: "Продвинутый",
    mixed: "Смешанный",
};

/* ======================================================
   4. onMounted – загрузка всех данных
===================================================== */
onMounted(() => {
    // Если course.value.id уже есть, используем его:
    if (course.value.id) {
        courseId.value = course.value.id;
        console.log("Курс уже есть, id =", courseId.value);
    } else {
        // Если нет, пытаемся извлечь из URL
        const parts = window.location.pathname.split("/");
        const courseIdFromUrl = parts[parts.length - 1];
        console.log("onMounted: извлекли courseId:", courseIdFromUrl);
        courseId.value = courseIdFromUrl; // при необходимости можно parseInt

        // Дополнительно подгружаем темы
        axios
            .get(`/api/course/${courseIdFromUrl}/topics`)
            .then((response) => {
                // Сортируем темы по возрастанию даты создания
                topics.value = (response.data.topics || []).sort((a, b) => {
                    return new Date(a.created_at) - new Date(b.created_at);
                });

                course.value = response.data.course || {};
                console.log("Успешно загрузили темы курса:", topics.value);
            })
            .catch((error) =>
                console.error("Ошибка при загрузке тем курса:", error)
            );
    }

    loadCourseComments();
    loadFaqs();
    loadCourses();
});

/* ======================================================
   5. Лайки и дизлайки
===================================================== */
// userVotes хранит текущее состояние голосов пользователя
const userVotes = ref({});

// Поиск комментария по ID в дереве при помощи BFS (без рекурсии).
function findCommentByIdInTree(commentId, commentsArray) {
    const queue = [...commentsArray];
    while (queue.length > 0) {
        const comment = queue.shift();
        if (comment.id === commentId) {
            return comment;
        }
        if (comment.children && comment.children.length > 0) {
            queue.push(...comment.children);
        }
    }
    return null;
}

// Функция для оптимистичного обновления поля (likes или dislikes) в одном комментарии.
function updateLocalCommentOptimistic(commentId, field, delta) {
    const target = findCommentByIdInTree(commentId, courseComments.value);
    if (target) {
        if (typeof target[field] !== "number") {
            target[field] = 0;
        }
        target[field] += delta;
        console.log(
            `Комментарий ${commentId}: поле ${field} изменено на ${delta}, теперь = ${target[field]}`
        );
    }
}

// Функция для сохранения голосов в localStorage
function saveUserVotes() {
    localStorage.setItem("userVotes", JSON.stringify(userVotes.value));
}

// Функция для установки лайка
async function likeComment(comment) {
    const prevVote = userVotes.value[comment.id];

    // Если уже стоит "like", то отменяем
    if (prevVote === "like") {
        updateLocalCommentOptimistic(comment.id, "likes", -1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/unlike`
            );
            userVotes.value[comment.id] = undefined;
            console.log("Лайк отменён");
        } catch (error) {
            console.error("Ошибка при отмене лайка:", error);
            updateLocalCommentOptimistic(comment.id, "likes", 1); // откат
        }
    }
    // Если стоит "dislike", снимаем дизлайк и ставим лайк
    else if (prevVote === "dislike") {
        updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        updateLocalCommentOptimistic(comment.id, "likes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/undislike`
            );
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/like`
            );
            userVotes.value[comment.id] = "like";
            console.log("Переключили с дизлайка на лайк");
        } catch (error) {
            console.error("Ошибка при переключении с дизлайка на лайк:", error);
            // откат
            updateLocalCommentOptimistic(comment.id, "dislikes", 1);
            updateLocalCommentOptimistic(comment.id, "likes", -1);
        }
    }
    // Если голос не был поставлен, ставим лайк
    else {
        updateLocalCommentOptimistic(comment.id, "likes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/like`
            );
            userVotes.value[comment.id] = "like";
            console.log("Лайк поставлен");
        } catch (error) {
            console.error("Ошибка при лайке комментария:", error);
            updateLocalCommentOptimistic(comment.id, "likes", -1); // откат
        }
    }
    saveUserVotes();
}

// Функция для установки дизлайка
async function dislikeComment(comment) {
    const prevVote = userVotes.value[comment.id];

    // Если уже стоит "dislike", то отменяем
    if (prevVote === "dislike") {
        updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/undislike`
            );
            userVotes.value[comment.id] = undefined;
            console.log("Дизлайк отменён");
        } catch (error) {
            console.error("Ошибка при отмене дизлайка:", error);
            updateLocalCommentOptimistic(comment.id, "dislikes", 1); // откат
        }
    }
    // Если стоит "like", то снимаем лайк и ставим дизлайк
    else if (prevVote === "like") {
        updateLocalCommentOptimistic(comment.id, "likes", -1);
        updateLocalCommentOptimistic(comment.id, "dislikes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/unlike`
            );
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/dislike`
            );
            userVotes.value[comment.id] = "dislike";
            console.log("Переключили с лайка на дизлайк");
        } catch (error) {
            console.error("Ошибка при переключении с лайка на дизлайк:", error);
            // откат
            updateLocalCommentOptimistic(comment.id, "likes", 1);
            updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        }
    }
    // Если голос не был поставлен, просто ставим дизлайк
    else {
        updateLocalCommentOptimistic(comment.id, "dislikes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/dislike`
            );
            userVotes.value[comment.id] = "dislike";
            console.log("Дизлайк поставлен");
        } catch (error) {
            console.error("Ошибка при дизлайке комментария:", error);
            updateLocalCommentOptimistic(comment.id, "dislikes", -1); // откат
        }
    }
    saveUserVotes();
}
</script>

<style scoped>
.button--sub {
    background: rgb(56, 184, 56) !important;
}
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    /* Центрируем содержимое по вертикали и горизонтали */
    align-items: center;
    justify-content: center;
    z-index: 999; /* Поверх всего */
}

.modal-overlay {
    position: absolute;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

/* Блок с контентом */
.modal-content {
    position: relative;
    z-index: 10000;
    background: #fff;
    border-radius: 6px;
    padding: 20px;
    max-width: 600px;
    width: 90%;
}

.modal-content h3 {
    margin: 0 0 10px;
}

/* Анимация появления/исчезновения (fade) */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Пример оформления кнопки (опционально) */
.button {
    margin-top: 16px;
    padding: 8px 16px;
    background: #617aff;
    color: #fff;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}
.iframe {
    width: 900px;
    max-width: 900px;
    height: 600px;
}
.button {
    border: none;
}
.button_skill-next {
    display: inline-block;
    transition: transform 0.5s ease, opacity 0.5s ease;
    transform: translateX(0);
    opacity: 1;
}
.button_skill-prev.hidden,
.button_skill-next.hidden {
    display: none; /* или visibility: hidden */
}
.button--xl {
    margin-top: 20px;
    color: #000;
    width: 75%;
    max-width: unset;
}
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
.fade-enter-active {
    animation: fadeIn 0.5s ease forwards;
}
.fade-leave-active {
    animation: fadeOut 0.5s ease forwards;
}
.slide-right-enter-active {
    animation: slideInRight 1s ease forwards;
}
.slide-right-leave-active {
    animation: slideOutRight 1s ease forwards;
}
/* Анимация для кнопки "Вперёд" через keyframes */
@keyframes slideInRight {
    from {
        transform: translateX(50px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(50px);
        opacity: 0;
    }
}

.chapter__title {
    text-align: center;
}
.info__topic {
    text-align: center;
}
.skill__content h1 {
    margin-bottom: 40px;
    text-align: left;
    font-size: 2.1em;
    text-align: center;
}
.welcome_course {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 50vh;
}
.skill__menu-main-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
</style>
