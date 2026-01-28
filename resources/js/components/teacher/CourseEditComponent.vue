<template>
    <div class="center">
        <h2 class="courses_h2">
            Курс: {{ course?.course_name || "Без названия" }}
        </h2>
        <div class="info__course">
            <div class="info__card info__card--center">
                <p class="info__text">Всего студентов:</p>
                <span class="info__desc">{{ studentsCount }}</span>
            </div>
            <div class="info__card info__card--course">
                <p class="info__text">Текущий блок:</p>
                <span class="info__desc info__desc--s">
                    {{ currentTopicTitle }}
                </span>
            </div>
            <div class="info__card info__card--course">
                <p class="info__text">Ближайшие занятия:</p>
                <span class="info__desc info__desc--s">
                    {{ courseDatesText }}
                </span>
            </div>
        </div>
        <section class="blocks">
            <h1 class="blocks__title">Блоки</h1>

            <div class="blocks__list">
                <article
                    v-for="topic in topics"
                    :key="topic.id"
                    class="course-block"
                    :class="topicBlockClass"
                >
                    <div class="course-block__header">
                        <div class="course-block__info">
                            <h2 class="course-block__title">
                                {{ topic.title || "Без названия" }}
                            </h2>
                            <span
                                class="course-block__status-badge"
                                :class="topicBadgeClass"
                            >
                                {{ courseStatusLabel }}
                            </span>
                        </div>

                        <div class="course-block__controls">
                            <label class="switch">
                                <input
                                    class="switch__input"
                                    type="checkbox"
                                    checked
                                />
                                <span class="switch__slider"></span>
                            </label>

                            <button
                                class="course-block__collapse"
                                type="button"
                                aria-expanded="true"
                            >
                                <span
                                    class="course-block__collapse-icon"
                                ></span>
                            </button>
                        </div>
                    </div>

                    <ul class="course-block__lessons">
                        <li
                            v-for="(chapter, index) in topic.chapters"
                            :key="chapter.id"
                            class="course-block__lesson"
                        >
                            <span class="course-block__lesson-title">
                                {{ index + 1 }} урок. {{ chapter.title }}
                            </span>
                            <button
                                class="course-block__lesson-edit"
                                type="button"
                                @click="openChapterEdit(chapter)"
                            >
                                Редактировать
                            </button>
                        </li>
                        <li
                            v-if="!topic.chapters?.length"
                            class="course-block__lesson"
                        >
                            <span class="course-block__lesson-title">
                                Уроков нет
                            </span>
                        </li>
                    </ul>
                </article>
                <div v-if="!topics.length" class="course__empty">
                    Темы отсутствуют.
                </div>
            </div>
        </section>
        <section class="file">
            <h1 class="blocks__title blocks__title--margin">
                Полезные материалы
            </h1>

            <div class="file__content">
                <!-- ЛЕВАЯ КОЛОНКА: СПИСОК ФАЙЛОВ -->
                <div class="file__desc">
                    <ul class="file__list">
                        <li class="file__item file__item--pdf">
                            <img src="../../../img/teacher/PDF.svg" alt="" />
                            <div class="file__info">
                                <div class="file__name">PDF файл</div>
                                <div class="file__meta">10 окт 2023 18:47</div>
                                <button class="file__download" type="button">
                                    Скачать
                                </button>
                            </div>
                        </li>

                        <li class="file__item file__item--zip">
                            <img src="../../../img/teacher/ZIP.svg" alt="" />
                            <div class="file__info">
                                <div class="file__name">Архив с кодом</div>
                                <div class="file__meta">10 окт 2023 8:01</div>
                                <button class="file__download" type="button">
                                    Скачать
                                </button>
                            </div>
                        </li>

                        <li class="file__item file__item--rar">
                            <img src="../../../img/teacher/RAR.svg" alt="" />
                            <div class="file__info">
                                <div class="file__name">Еще один архив</div>
                                <div class="file__meta">2 окт 2023 7:44</div>
                                <button class="file__download" type="button">
                                    Скачать
                                </button>
                            </div>
                        </li>

                        <li class="file__item file__item--jpg">
                            <img src="../../../img/teacher/JPG.svg" alt="" />
                            <div class="file__info">
                                <div class="file__name">Картинка</div>
                                <div class="file__meta">1 сен 2023 19:37</div>
                                <button class="file__download" type="button">
                                    Скачать
                                </button>
                            </div>
                        </li>

                        <li class="file__item file__item--jpg">
                            <img src="../../../img/teacher/JPG.svg" alt="" />
                            <div class="file__info">
                                <div class="file__name">Картинка2</div>
                                <div class="file__meta">1 сен 2023 19:37</div>
                                <button class="file__download" type="button">
                                    Скачать
                                </button>
                            </div>
                        </li>
                    </ul>
                    <div class="pagination">
                        <button class="pag--active"></button>
                        <button class="pag"></button>
                    </div>
                </div>

                <!-- ПРАВАЯ ЧАСТЬ: DROPZONE -->
                <div class="file__drop">
                    <div class="file__drop-inner">
                        <p class="file__drop-text">
                            Перетащите файл сюда или нажмите на кнопку ниже
                        </p>

                        <label class="file__drop-button">
                            Добавить файл
                            <input class="file__input" type="file" multiple />
                        </label>
                    </div>
                </div>
            </div>
        </section>
        <section class="journal">
            <h1 class="blocks__title blocks__title--margin">
                Журнал
            </h1>

            <!-- МЕСЯЦЫ -->
            <nav class="journal__months">
                <button class="journal__month" type="button">Сентябрь</button>
                <button class="journal__month" type="button">Октябрь</button>
                <button class="journal__month" type="button">Ноябрь</button>
                <button class="journal__month" type="button">Декабрь</button>
                <button class="journal__month" type="button">Январь</button>
                <button class="journal__month" type="button">Февраль</button>
                <button class="journal__month journal__month--active" type="button">Март</button>
                <button class="journal__month" type="button">Апрель</button>
                <button class="journal__month" type="button">Май</button>
                <button class="journal__month" type="button">Июнь</button>
                <button class="journal__month" type="button">Июль</button>
                <button class="journal__month" type="button">Август</button>
            </nav>

            <!-- ТАБЛИЦА ЖУРНАЛА -->
            <div class="journal__table-wrapper">
                <table class="journal__table">
                <thead class="journal__head">
                    <tr class="journal__head-row">
                    <!-- первый столбец -->
                    <th class="journal__head-cell journal__head-cell--sticky">
                        <img width="20" height="20" src="../../../img/teacher/table_icon.svg" alt="">
                        <span class="journal__head-title">Оценки</span>
                    </th>

                    <!-- ДАТЫ (пример, можно генерировать циклом с бэка) -->
                    <th class="journal__head-cell" data-date="2024-03-01">
                        <span class="journal__day">
                        <span class="journal__day-week">Пт</span>
                        <span class="journal__day-date">1.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-04">
                        <span class="journal__day">
                        <span class="journal__day-week">Пн</span>
                        <span class="journal__day-date">4.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-05">
                        <span class="journal__day">
                        <span class="journal__day-week">Вт</span>
                        <span class="journal__day-date">5.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-06">
                        <span class="journal__day">
                        <span class="journal__day-week">Ср</span>
                        <span class="journal__day-date">6.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-07">
                        <span class="journal__day">
                        <span class="journal__day-week">Чт</span>
                        <span class="journal__day-date">7.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-11">
                        <span class="journal__day">
                        <span class="journal__day-week">Пн</span>
                        <span class="journal__day-date">11.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-12">
                        <span class="journal__day">
                        <span class="journal__day-week">Вт</span>
                        <span class="journal__day-date">12.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-13">
                        <span class="journal__day">
                        <span class="journal__day-week">Ср</span>
                        <span class="journal__day-date">13.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-14">
                        <span class="journal__day">
                        <span class="journal__day-week">Чт</span>
                        <span class="journal__day-date">14.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-15">
                        <span class="journal__day">
                        <span class="journal__day-week">Пт</span>
                        <span class="journal__day-date">15.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-18">
                        <span class="journal__day">
                        <span class="journal__day-week">Пн</span>
                        <span class="journal__day-date">18.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-19">
                        <span class="journal__day">
                        <span class="journal__day-week">Вт</span>
                        <span class="journal__day-date">19.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-20">
                        <span class="journal__day">
                        <span class="journal__day-week">Ср</span>
                        <span class="journal__day-date">20.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-21">
                        <span class="journal__day">
                        <span class="journal__day-week">Чт</span>
                        <span class="journal__day-date">21.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-22">
                        <span class="journal__day">
                        <span class="journal__day-week">Пт</span>
                        <span class="journal__day-date">22.03</span>
                        </span>
                    </th>

                    <!-- ТЕКУЩИЙ ДЕНЬ -->
                    <th class="journal__head-cell journal__head-cell--today" data-date="2024-03-25">
                        <span class="journal__day journal__day--today">
                            <span class="journal__day-week">Пн</span>
                            <span class="journal__day-date">25.03</span>
                        </span>
                    </th>

                    <!-- БУДУЩИЕ ДНИ -->
                    <th class="journal__head-cell" data-date="2024-03-26">
                        <span class="journal__day">
                        <span class="journal__day-week">Вт</span>
                        <span class="journal__day-date">26.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-27">
                        <span class="journal__day">
                        <span class="journal__day-week">Ср</span>
                        <span class="journal__day-date">27.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-28">
                        <span class="journal__day">
                        <span class="journal__day-week">Чт</span>
                        <span class="journal__day-date">28.03</span>
                        </span>
                    </th>
                    <th class="journal__head-cell" data-date="2024-03-29">
                        <span class="journal__day">
                        <span class="journal__day-week">Пт</span>
                        <span class="journal__day-date">29.03</span>
                        </span>
                    </th>
                    </tr>
                </thead>

                <tbody class="journal__body">
                    <!-- СТРОКА СТУДЕНТА 1 -->
                    <tr class="journal__row" data-student-id="1">
                    <td class="journal__cell journal__cell--name">
                        <span class="journal__student-index">1</span>
                        <span class="journal__student-name">Татьяна Зайцева</span>
                    </td>

                    <!-- обычные ячейки с оценкой -->
                    <td class="journal__cell journal__cell--value" data-date="2024-03-01">5</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-04">5</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-05">5+</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-06">Н/Я</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-07">3</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-11"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-12"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-13">4</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-14"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-15">3</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-18"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-19"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-20">5</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-21"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-22">5</td>

                    <!-- сегодня -->
                    <td class="journal__cell journal__cell--today" data-date="2024-03-25">
                        <span class="journal__slot journal__slot--filled">5</span>
                    </td>

                    <!-- будущие даты -->
                    <td class="journal__cell journal__cell--future" data-date="2024-03-26">
                        <span class="journal__slot"></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-27">
                        <span class="journal__slot"></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-28">
                        <span class="journal__slot"></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-29">
                        <span class="journal__slot"></span>
                    </td>
                    </tr>

                    <!-- СТУДЕНТ 2 -->
                    <tr class="journal__row" data-student-id="2">
                    <td class="journal__cell journal__cell--name">
                        <span class="journal__student-index">2</span>
                        <span class="journal__student-name">Дмитрий Петров</span>
                    </td>

                    <td class="journal__cell journal__cell--value" data-date="2024-03-01">Н/Я</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-04">5+</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-05">3</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-06">3</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-07"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-11">3</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-12"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-13">3</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-14"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-15"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-18">3</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-19"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-20">5</td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-21"></td>
                    <td class="journal__cell journal__cell--value" data-date="2024-03-22">5+</td>

                    <td class="journal__cell journal__cell--today" data-date="2024-03-25">
                        <span class="journal__slot journal__slot--filled">5</span>
                    </td>

                    <td class="journal__cell journal__cell--future" data-date="2024-03-26">
                        <span class="journal__slot"></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-27">
                        <span class="journal__slot "></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-28">
                        <span class="journal__slot "></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-29">
                        <span class="journal__slot"></span>
                    </td>
                    </tr>

                    <!-- СТУДЕНТ 3 (пример с «С» и пустыми квадратами, как внизу скрина) -->
                    <tr class="journal__row" data-student-id="3">
                    <td class="journal__cell journal__cell--name">
                        <span class="journal__student-index">9</span>
                        <span class="journal__student-name">Вася Попов</span>
                    </td>

                    <!-- серия пустых квадратиков -->
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-01">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-04">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-05">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-06">
                        <span class="journal__slot journal__slot--comment">С</span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-07">
                        <span class="journal__slot journal__slot--comment">С</span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-11">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-12">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-13">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-14">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-15">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-18">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-19">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-20">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-21">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>
                    <td class="journal__cell journal__cell--slot" data-date="2024-03-22">
                        <span class="journal__slot journal__slot--empty"></span>
                    </td>

                    <td class="journal__cell journal__cell--today" data-date="2024-03-25">
                        <span class="journal__slot journal__slot--filled">5</span>
                    </td>

                    <td class="journal__cell journal__cell--future" data-date="2024-03-26">
                        <span class="journal__slot "></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-27">
                        <span class="journal__slot "></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-28">
                        <span class="journal__slot "></span>
                    </td>
                    <td class="journal__cell journal__cell--future" data-date="2024-03-29">
                        <span class="journal__slot "></span>
                    </td>
                    </tr>
                </tbody>
                </table>
            </div>
        </section>
    </div>
    <EditChapterDialog
        v-model="showChapterModal"
        :chapter="selectedChapter"
        :topic-id="selectedChapter?.topic_id"
        @saved="onChapterUpdated"
    />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import EditChapterDialog from "../admin/modal_admin/EditChapter.vue";

const courseId = (() => {
    const parts = window.location.pathname.split("/").filter(Boolean);
    const idx = parts.indexOf("course");
    if (idx === -1) return null;
    return parts[idx + 1] ?? null;
})();

const course = ref(null);
const topics = ref([]);
const studentsCount = ref(0);
const showChapterModal = ref(false);
const selectedChapter = ref(null);

const courseStatus = computed(() => {
    if (!course.value) return "current";
    const now = new Date();
    const start = course.value.start_date
        ? new Date(course.value.start_date)
        : null;
    const end = course.value.end_date ? new Date(course.value.end_date) : null;

    if (end && now > end) return "past";
    if (start && now < start) return "future";
    return "current";
});

const courseStatusLabel = computed(() => {
    if (courseStatus.value === "past") return "Завершен";
    if (courseStatus.value === "future") return "Закрыт";
    return "Активен";
});

const topicBlockClass = computed(() => {
    if (courseStatus.value === "past") return "course-block--completed";
    if (courseStatus.value === "future") return "course-block--closed";
    return "course-block--active";
});

const topicBadgeClass = computed(() => {
    if (courseStatus.value === "past")
        return "course-block__status-badge--completed";
    if (courseStatus.value === "future")
        return "course-block__status-badge--closed";
    return "course-block__status-badge--active";
});

const currentTopicTitle = computed(() => {
    if (!topics.value.length) return "—";
    return topics.value[0]?.title || "—";
});

const courseDatesText = computed(() => {
    if (!course.value) return "—";
    if (course.value.start_date && course.value.end_date) {
        return `С ${formatDate(course.value.start_date)} до ${formatDate(
            course.value.end_date
        )}`;
    }
    if (course.value.start_date) {
        return `Старт: ${formatDate(course.value.start_date)}`;
    }
    if (course.value.end_date) {
        return `Окончание: ${formatDate(course.value.end_date)}`;
    }
    return "—";
});

function formatDate(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleDateString("ru-RU");
}

function openChapterEdit(chapter) {
    selectedChapter.value = chapter;
    showChapterModal.value = true;
}

function onChapterUpdated(payload) {
    const updated = payload?.chapter ?? payload;
    if (!updated?.id) {
        showChapterModal.value = false;
        selectedChapter.value = null;
        return;
    }
    topics.value = topics.value.map((topic) => {
        const nextChapters = Array.isArray(topic.chapters)
            ? topic.chapters.map((ch) =>
                  ch.id === updated.id ? updated : ch
              )
            : [];
        return { ...topic, chapters: nextChapters };
    });
    showChapterModal.value = false;
    selectedChapter.value = null;
}

async function loadCourse() {
    if (!courseId) return;
    try {
        const { data } = await axios.get(`/api/courses/${courseId}`);
        course.value = data?.course ?? data;
    } catch (e) {
        console.error("Ошибка загрузки курса:", e);
        course.value = null;
    }
}

async function loadStudents() {
    if (!courseId) return;
    try {
        const { data } = await axios.get(`/api/students/${courseId}`);
        const list = Array.isArray(data) ? data : [];
        studentsCount.value = list.length;
    } catch (e) {
        console.error("Ошибка загрузки студентов:", e);
        studentsCount.value = 0;
    }
}

async function loadTopics() {
    if (!courseId) return;
    try {
        const { data } = await axios.get(`/admin/course/${courseId}/topics`);
        const list = Array.isArray(data?.topics) ? data.topics : [];
        const topicsWithChapters = await Promise.all(
            list.map(async (topic) => {
                try {
                    const resp = await axios.get(
                        `/admin/topic/${topic.id}/chapters`
                    );
                    const chapters = Array.isArray(resp?.data?.chapters)
                        ? resp.data.chapters
                        : [];
                    return { ...topic, chapters };
                } catch (e) {
                    console.error(
                        "Ошибка загрузки глав для темы:",
                        topic.id,
                        e
                    );
                    return { ...topic, chapters: [] };
                }
            })
        );
        topics.value = topicsWithChapters;
    } catch (e) {
        console.error("Ошибка загрузки тем:", e);
        topics.value = [];
    }
}

onMounted(async () => {
    await Promise.all([loadCourse(), loadStudents(), loadTopics()]);
});
</script>
