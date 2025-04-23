<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <header class="header">
                    <div class="header__inner">
                        <div class="header__logo">
                            <img src="img/logo.png">
                        </div>
                        <nav>
                            <a href="#">Банк знаний</a>
                            <a href="#">Каталог</a>
                            <a href="#">Профориентация</a>
                            <a href="#">Для учителей</a>
                            <a href="#">О нас</a>
                            <a href="#">Стажировки</a>
                            <a href="#">Новости</a>
                        </nav>
                        <div class="header__lk">
                            <div class="header__lk-img">
                                <img src="img/nofotolk.png">
                            </div>
                            <div class="header__lk-name">
                                Василий Пупкин
                            </div>
                        </div>
                    </div>
                </header>
                <section class="course">
                    <div class="course__inner">
                        <div class="course__content">
                            <div class="course__menu course__menu-persona">
                                <div class="course__menu-nickname">
                                    <img src="img/no_foto.jpg" class="course__menu-foto">
                                    <div class="course__menu-name">NickName</div>
                                </div>
                                <div class="course__menu-block">
                                    <div class="course__menu-one course__menu-one_active" data-id="id1">Мои курсы</div>
                                    <!-- <div class="course__menu-one" data-id="id2">Профореинтация+</div>
                                    <div class="course__menu-one" data-id="id3">Стажировки</div> -->
                                    <div class="course__menu-one" data-id="id4">Личные данные</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="course block-tab block-tab_active" id="id1">
                    <div class="course__inner">
                        <div class="course__content">
                            <div class="course__cards course__cards_personal">
                                <div class="course__cards-title">Мои курсы</div>
                                <div class="course__cards-inner">
                                    <div class="course__card course__card_personal course__card_bg-cyan course__card_bg1">
                                        <div class="course__card-image"><img src="img/logo_c++.png"></div>
                                        <div class="course__card-title">C++</div>
                                        <div class="course__card-desc">Базовый</div>
                                        <div class="course__card-task">
                                            <p>Пройдено тем</p>
                                            <p>19/47</p>
                                        </div>
                                        <div class="course__card-task">
                                            <p>Решено заданий</p>
                                            <p>7/58</p>
                                        </div>
                                        <div class="course__card-buttons">
                                            <a href="#" class="button button_transparent button_transparent-big">Перейти к курсу</a>
                                        </div>
                                    </div>
                                    <div class="course__card course__card_personal course__card_bg-fiolet course__card_bg1">
                                        <div class="course__card-image"><img src="img/logo_c++.png"></div>
                                        <div class="course__card-title">C++</div>
                                        <div class="course__card-desc">Фундаментальный</div>
                                        <div class="course__card-task">
                                            <p>Пройдено тем</p>
                                            <p>19/47</p>
                                        </div>
                                        <div class="course__card-task">
                                            <p>Решено заданий</p>
                                            <p>7/58</p>
                                        </div>
                                        <div class="course__card-buttons">
                                            <a href="#" class="button button_transparent button_transparent-big">Перейти к курсу</a>
                                        </div>
                                    </div>
                                    <div class="course__card course__card_personal course__card_bg-orange course__card_bg1">
                                        <div class="course__card-image"><img src="img/logo_c++.png"></div>
                                        <div class="course__card-title">C++</div>
                                        <div class="course__card-desc">Фундаментальный</div>
                                        <div class="course__card-task">
                                            <p>Пройдено тем</p>
                                            <p>19/47</p>
                                        </div>
                                        <div class="course__card-task">
                                            <p>Решено заданий</p>
                                            <p>7/58</p>
                                        </div>
                                        <div class="course__card-buttons">
                                            <a href="#" class="button button_transparent button_transparent-big">Перейти к курсу</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="course block-tab" id="id2">
                    <div class="course__inner">
                        <div class="course__content">
                            <div class="course__cards course__cards_additional">
                                <div class="course__cards-title">Профореинтация</div>
                                <div class="course__cards-inner">
                                    <div class="course__card course__card_personal course__card_bg-green course__card_bg1">
                                        <div class="course__card-image"><img src="img/prof.png"></div>
                                        <div class="course__card-title"></div>
                                        <div class="course__card-desc">Профориентация</div>
                                        <div class="course__card-buttons">
                                            <a href="#" class="button button_transparent button_transparent-big">Перейти</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="course block-tab" id="id3">
                    <div class="course__inner">
                        <div class="course__content">
                            <div class="course__cards course__cards_additional">
                                <div class="course__cards-title">Стажировки</div>
                                <div class="course__cards-inner">
                                    <div class="course__card course__card_personal course__card_bg-blue course__card_bg1">
                                        <div class="course__card-image"><img src="img/internship.png"></div>
                                        <div class="course__card-title"></div>
                                        <div class="course__card-desc">Стажировки</div>
                                        <div class="course__card-buttons">
                                            <a href="#" class="button button_transparent button_transparent-big">Перейти</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="infoblock block-tab" id="id4">
                    <div class="infoblock__wrapper">
                        <div class="infoblock__inner">
                            <div class="infoblock-title">Личные данные</div>
                            <div class="infoblock__info">
                                <div lang="infoblock__info-name">
                                    <div class="infoblock__info-name-image">
                                        <img src="img/no_foto.jpg">
                                    </div>
                                    <form action="" class="infoblock__info-form">
                                        <label class="infoblock__info-file">
                                            <input type="file" name="file" accept=".jpg, .png, .wbep,.jpeg">
                                            <span class="infoblock__info-filebtn">Загрузить фото</span>
                                            <span class="infoblock__info-filetext"></span>
                                        </label>
                                        <input type="submit" value="Добавить" class="infoblock__button">
                                    </form>
                                </div>
                                <form class="infoblock__data">
                                    <div class="infoblock__data-top">
                                        <input type="text" name="name" placeholder="Иванов Сергей Викторович">
                                        <input type="email" name="email" placeholder="E-mail">
                                        <input type="date" name="bithday" placeholder="1996-06-23">
                                        <input type="text" name="status" placeholder="Статус">
                                        <input type="tel" name="phone" placeholder="+7 900 000 00 00">
                                        <input type="text" name="country" placeholder="Страна + город">
                                    </div>
                                    <input type="submit" value="Сохранить">
                                </form>
                            </div>
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
                    <form method="post" name="mtForm1" id="mtForm1" class="forma">
                        <input type="hidden" name="data_form" id="data_form" value="">
                        <input type="hidden" name="no" value="no" />
                        <div class="form-field">
                            <label>
                                <span>Ваше имя:</span>
                                <input type="text" name="name" required="required">
                            </label>
                        </div>
                        <div class="form-field">
                            <label>
                                <span>Ваш телефон:</span>
                                <input type="tel" name="phone" required="required" class="phone_valid">
                            </label>
                        </div>
                        <div class="form-field">
                            <input type="submit" name="submit" value="Отправить заявку">
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
                <div class="popup-desc">Ваша заявка успешно отправлена. <br>C вами свяжутся в ближайшее время.</div>
                <div class="close-up close-up-all">
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
</script>