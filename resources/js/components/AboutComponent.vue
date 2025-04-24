<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="offer offer_about">
                    <div class="offer__inner">
                        <h1 class="intro__title">
                            Об образовательном портале
                        </h1>
                        <p class="intro__text">
                            В современном мире, где технологический прогресс
                            неуклонно продвигается вперед, образование играет
                            всё более важную роль. Возможность получить
                            качественное образование становится доступной для
                            все большего числа людей благодаря развитию
                            онлайн-образования. В этом контексте образовательные
                            порталы предоставляют уникальные возможности для
                            саморазвития и приобретения новых навыков из любой
                            точки мира.
                        </p>
                    </div>
                </section>
                <section class="about-article">
                    <!-- Блок 1 -->
                    <div class="article-block">
                        <img
                            src="/img/image_3.jpg"
                            alt="Изображение 1"
                            class="article-img article-img--left"
                        />
                        <div class="article-text">
                            <p>
                                Один из таких порталов. Это
                                инновационная платформа, объединяющая
                                специалистов из разных областей информационных
                                технологий и предоставляющая широкий спектр
                                образовательных материалов и курсов.
                                становится незаменимым
                                инструментом для всех, кто желает расширить свои
                                знания и умения в IT-сфере.
                            </p>
                            <p>
                                Благодаря удобному интерфейсу и доступности
                                материалов на различных языках, каждый может
                                выбирать подходящие курсы и изучать их в любое
                                время и из любой точки мира.
                            </p>
                        </div>
                    </div>
                    <!-- Блок 2 -->
                    <div class="article-block">
                        <div class="article-text">
                            <p>
                                Портал открывает двери в мир новых возможностей
                                и перспектив. Здесь можно получить качественное
                                образование от лучших экспертов, развивать свои
                                навыки и применять их на практике. Одной из
                                особенностей платформы является возможность
                                получения сертификата по окончании курса,
                                подтверждающего уровень знаний.
                            </p>
                            <p>
                                Институтом образовательных технологий — это не только место для
                                обучения, но и площадка для создания
                                профессиональных контактов и обмена опытом.
                            </p>
                        </div>
                        <img
                            src="/img/image_2.jpg"
                            alt="Изображение 2"
                            class="article-img article-img--right"
                        />
                    </div>
                    <!-- Блок 3 -->
                    <div class="article-block">
                        <img
                            src="/img/image_3.jpg"
                            alt="Изображение 3"
                            class="article-img article-img--left"
                        />
                        <div class="article-text">
                            <p>
                                Курсы, разработанные профессионалами, предлагают
                                систематическое обучение с постепенным
                                углублением материала. Практические задания и
                                разбор кейсов помогают студентам закрепить
                                полученные знания.
                            </p>
                        </div>
                    </div>
                    <!-- Блок 4 -->
                    <div class="article-block">
                        <img
                            src="/img/image_4.jpg"
                            alt="Изображение 4"
                            class="article-img article-img--right"
                        />
                        <div class="article-text">
                            <p>
                                Одной из ключевых особенностей портала является
                                возможность участия в соревнованиях по
                                спортивному программированию, что способствует
                                развитию навыков быстрого мышления и командной
                                работы.
                            </p>
                        </div>
                    </div>
                    <!-- Блок 5 -->
                    <div class="article-block">
                        <div class="article-text">
                            <p>
                                Институтом образовательных технологий активно сотрудничает с ведущими
                                компаниями России, предоставляя студентам
                                возможность получать актуальные знания и
                                практический опыт от экспертов крупных
                                корпораций. Это делает платформу незаменимым В
                                итоге, Институтом образовательных технологий — это не просто
                                образовательный портал, а целая экосистема, где
                                каждый студент может найти поддержку, обменяться
                                опытом и получить качественное образование,
                                соответствующее требованиям современного рынка
                                труда.
                            </p>
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

<script></script>

<style scoped>
/* Общий стиль страницы */
.container {
    width: 1440px;
    margin: 0 auto;
}
.about-page {
    font-family: "Arial", sans-serif;
    color: #333;
    background-color: #fff;
}

/* MAIN CONTENT */
.main-content {
    margin: 40px 0;
}

/* Интро */
.intro {
    text-align: center;
    margin-bottom: 40px;
}
.intro__title {
    font-size: 37px;
    margin-bottom: 30px;
}
.intro__text {
    font-size: 18px;
    line-height: 1.6;
    max-width: 800px;
}

/* Статья */
.about-article {
    display: flex;
    flex-direction: column;
    gap: 40px;
    width: 1440px;
}
.article-block {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 20px;
}
.article-img {
    max-width: 100%;
    border-radius: 8px;
}
.article-img--left {
    order: 1;
    flex: 1 1 300px;
}
.article-img--right {
    order: 2;
    flex: 1 1 300px;
}
.article-text {
    flex: 2 1 500px;
    font-size: 18px;
    line-height: 1.7;
    text-align: justify;
}
</style>
