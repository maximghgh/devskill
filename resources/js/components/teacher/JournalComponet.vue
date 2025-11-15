<template>
    <div class="center">
        <h2>Журнал</h2>
        <section class="courses">
            <div class="course-accordion">

                <!-- КУРС 1: открыт по умолчанию -->
                <article class="course-accordion__item course-accordion__item--open">
                    <button class="course-accordion__header" type="button">
                        <span class="course-accordion__title">Курс: Веб-разработка</span>
                        <span class="course-accordion__chevron"></span>
                    </button>

                    <div class="course-accordion__body">
                        <!-- ВСТАВЛЯЕШЬ СВОЙ JOURNAL СЮДА -->
                        <section class="journal">

                            <!-- МЕСЯЦЫ -->
                            <nav class="journal__months journal__months--bg">
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
                </article>

                <!-- КУРС 2 -->
                <article class="course-accordion__item">
                    <button class="course-accordion__header" type="button">
                        <span class="course-accordion__title">Курс: C#</span>
                        <span class="course-accordion__chevron"></span>
                    </button>

                    <div class="course-accordion__body">
                        <section class="journal">

                            <!-- МЕСЯЦЫ -->
                            <nav class="journal__months journal__months--bg">
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
                </article>

                <!-- КУРС 3 -->
                <article class="course-accordion__item">
                    <button class="course-accordion__header" type="button">
                        <span class="course-accordion__title">
                            Курс: Python разработка для начинающих
                        </span>
                        <span class="course-accordion__chevron"></span>
                    </button>

                    <div class="course-accordion__body">
                        <section class="journal">

                            <!-- МЕСЯЦЫ -->
                            <nav class="journal__months journal__months--bg">
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
                </article>

            </div>
        </section>

    </div>
</template>

<script>
document.addEventListener('click', function (e) {
    const header = e.target.closest('.course-accordion__header');
    if (!header) return;

    const item = header.closest('.course-accordion__item');
    const accordion = item.closest('.course-accordion');

    // если нужен только один открытый курс – закрываем остальные
    accordion.querySelectorAll('.course-accordion__item--open')
        .forEach(function (openItem) {
            if (openItem !== item) {
                openItem.classList.remove('course-accordion__item--open');
            }
        });

    // переключаем текущий
    item.classList.toggle('course-accordion__item--open');
});
</script>
