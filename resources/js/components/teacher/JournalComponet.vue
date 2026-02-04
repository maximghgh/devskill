<template>
    <div class="center">
        <h2>Журнал</h2>
        <section class="courses">
            <div class="course-accordion">

                <!-- КУРС 1: открыт по умолчанию -->
                <article class="course-accordion__item course-accordion__item--open">
<!--                    <button class="course-accordion__header" type="button">-->
<!--                        <span class="course-accordion__title">Курс: Веб-разработка</span>-->
<!--                        <span class="course-accordion__chevron"></span>-->
<!--                    </button>-->

                    <div class="course-accordion__filter">
                        <div class="dialog__component">
                            <p class="dialog__title">Курс</p>
                            <select
                                class="dialog__input dialog__select"
                                v-model="selectedCourseId"
                            >
                                <option disabled value="">Выберите курс</option>
                                <option
                                    v-for="c in courses"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.title }}
                                </option>
                            </select>
                        </div>

                        <!-- 2) Группа (появляется после выбора курса) -->
                        <div class="dialog__component" v-if="selectedCourseId">
                            <p class="dialog__title">Группа</p>
                            <select
                                class="dialog__input dialog__select"
                                v-model="selectedGroupId"
                                :disabled="!groupsForCourse.length"
                            >
                                <option disabled value="">Выберите группу</option>
                                <option
                                    v-for="g in groupsForCourse"
                                    :key="g.id"
                                    :value="g.id"
                                >
                                    {{ g.title }}
                                </option>
                            </select>
                        </div>

                        <!-- 3) Урок (появляется после выбора группы) -->
                        <div class="dialog__component" v-if="selectedGroupId">
                            <p class="dialog__title">Урок</p>
                            <select
                                class="dialog__input dialog__select"
                                v-model="selectedLessonId"
                                :disabled="!lessonsForGroup.length"
                            >
                                <option disabled value="">Выберите урок</option>
                                <option
                                    v-for="l in lessonsForGroup"
                                    :key="l.id"
                                    :value="l.id"
                                >
                                    {{ l.title }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="course-accordion__body">
                        <!-- ВСТАВЛЯЕШЬ СВОЙ JOURNAL СЮДА -->
                        <section class="journal" v-if="selectedLessonId">

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
                </article>
            </div>
        </section>

    </div>
</template>

<script>
export default {
    data() {
        return {
            selectedCourseId: "",
            selectedGroupId: "",
            selectedLessonId: "",

            courses: [
                { id: "web", title: "Веб-разработка" },
                { id: "py", title: "Python" },
            ],
            groupsByCourse: {
                web: [
                    { id: "web-1", title: "Группа 1" },
                    { id: "web-2", title: "Группа 2" },
                ],
                py: [
                    { id: "py-1", title: "Группа A" },
                ],
            },
            lessonsByGroup: {
                "web-1": [
                    { id: "l-101", title: "Урок 1" },
                    { id: "l-102", title: "Урок 2" },
                ],
                "web-2": [
                    { id: "l-201", title: "Урок 1" },
                ],
                "py-1": [
                    { id: "l-301", title: "Введение" },
                ],
            },
        };
    },

    computed: {
        groupsForCourse() {
            return this.groupsByCourse[this.selectedCourseId] || [];
        },
        lessonsForGroup() {
            return this.lessonsByGroup[this.selectedGroupId] || [];
        },
    },

    watch: {
        selectedCourseId() {
            this.selectedGroupId = "";
            this.selectedLessonId = "";
        },
        selectedGroupId() {
            this.selectedLessonId = "";
        },
    },
};
</script>
