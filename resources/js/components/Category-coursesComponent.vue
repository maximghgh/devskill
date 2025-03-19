<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <header class="header header_main">
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
                <section class="category">
                    <h2>Категории курсов</h2>
                    <div class="category__inner">
                        <div class="category__one category__one_back1">
                            <div class="category__one-inner">
                                <div class="category__one-title">
                                    Серия курсов по C++
                                </div>
                                <div class="category__one-info">
                                    <p>• Базовый</p>
                                    <p>• Фундаментальный</p>
                                    <p>• Олимпиадный</p>
                                </div>
                                <a href="#" class="button button_white button_cat-link">Перейти в категорию</a>
                            </div>
                        </div>
                        <div class="category__one category__one_defualt">
                            <div class="category__one-inner">
                                В ближайшее время здесь <br>появятся другие категории
                            </div>
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
                                <img src="img/logo.png">
                            </div>
                        </div>
                    </div>
                    <div class="footer__row">© 2011-2023 гг. Сайт не является публичной офертой и носит информационный характер. Все материалы данного сайта являются объектами авторского права (в том числе дизайн). Запрещается копирование, распространение (в том числе путем копирования на другие сайты и ресурсы в Интернете) или любое иное использование информации и объектов без предварительного согласия правообладателя.</div>
                </div>
            </footer>
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