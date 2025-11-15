<template>
    <div class="center">
        <section class="blocks">
            <a href="/teacher/course" class="link__back">
                <img width="29" height="21" src="../../../img/teacher/back.svg" alt="">
                Назад к курсу
            </a>

            <div class="blocks__list">
                <!-- Блок 1 - завершен -->
                <article class="course-block course-block--active">
                    <div class="course-block__header">
                        <div class="course-block__info">
                            <h2 class="course-block__title">
                                Основная информация
                            </h2>
                        </div>
                    </div>

                    <ul class="course-block__lessons">
                        <li class="course-block__item">
                            <label>Название урока</label>
                            <div class="course-block__lesson">
                                <input 
                                    class="course-block__input course-block__lesson-title"
                                    value="1 урок. Основы HTML"
                                    placeholder="Название урока"
                                >
                            </div>
                        </li>
                        <li class="course-block__item">
                            <label>Описание</label>
                            <div class="course-block__lesson">
                                <textarea 
                                    class="course-block__input course-block__lesson-title course-block__input--textarea"
                                    value="1 урок. Основы HTML"
                                    placeholder="Введите описание"
                                ></textarea>
                            </div>
                        </li>
                        <li class="course-block__item">
                            <label>Срок сдачи теста</label>
                            <div class="course-block__lesson">
                                <input 
                                    class="course-block__input course-block__lesson-title course-block__input--date"
                                    value="1 урок. Основы HTML"
                                    placeholder="Название урока"
                                    type="date"
                                >
                                <img class="course-block__img" src="../../../img/teacher/date.svg" width="22" height="24" alt="">
                            </div>
                        </li>
                    </ul>
                </article>

                <!-- Блок 2 - активен -->
                <article class="course-block course-block--active">
                    <div class="course-block__header">
                        <div class="course-block__info">
                            <h2 class="course-block__title">
                                Основная информация
                            </h2>
                        </div>
                    </div>

                    <ul class="course-block__lessons">
                        <li class="course-block__item">
                            <label>Название урока</label>
                            <div class="course-block__lesson">
                                <input 
                                    class="course-block__input course-block__lesson-title"
                                    value="1 урок. Основы HTML"
                                    placeholder="Название урока"
                                >
                            </div>
                        </li>
                        <li class="course-block__item">
                            <label>Описание</label>
                            <div class="course-block__lesson">
                                <textarea 
                                    class="course-block__input course-block__lesson-title course-block__input--textarea"
                                    value="1 урок. Основы HTML"
                                    placeholder="Введите описание"
                                ></textarea>
                            </div>
                        </li>
                        <li class="course-block__item">
                            <label>Срок сдачи теста</label>
                            <div class="course-block__lesson">
                                <input 
                                    class="course-block__input course-block__lesson-title course-block__input--date"
                                    value="1 урок. Основы HTML"
                                    placeholder="Название урока"
                                    type="date"
                                >
                                <img class="course-block__img" src="../../../img/teacher/date.svg" width="22" height="24" alt="">
                            </div>
                        </li>
                    </ul>
                </article>
                <article class="course-block course-block--active">
                    <div class="course-block__header">
                        <div class="course-block__info">
                            <h2 class="course-block__title">
                                Ссылки на Тест
                            </h2>
                        </div>
                    </div>

                    <ul class="course-block__lessons">
                        <li class="course-block__item">
                            <label>Ссылка на видео</label>
                            <div class="course-block__lesson">
                                <input 
                                    class="course-block__input course-block__lesson-title"
                                    value="1 урок. Основы HTML"
                                    placeholder="Название урока"
                                >
                            </div>
                        </li>
                        <li class="course-block__item">
                            <label>Ссылка на тест</label>
                            <div class="course-block__lesson">
                                <textarea 
                                    class="course-block__input course-block__lesson-title course-block__input--textarea"
                                    value="1 урок. Основы HTML"
                                    placeholder="Введите описание"
                                ></textarea>
                            </div>
                        </li>
                    </ul>
                </article>
                <div class="file__drop file__drop--flex">
                    <h2 class="course-block__title">Учебный материал</h2>
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
            <div class="blocks__button">
                <button
                    type="submit"
                    class="infoblock__button"
                >Сохранить урок</button>
                <button
                    type="submit"
                    value="Отменить"
                    class="infoblock__info-filebtn"
                >Отменить</button>
            </div>
        </section>
    </div>
</template>