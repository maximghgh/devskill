<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <header class="header header_main">
                    <div class="header__inner">
                        <a href="/" class="header__logo">
                            <img src="https://devskills.foncode.ru/img/logo.png">
                        </a>
                        <nav>
                            <a href="#">Банк знаний</a>
                            <a href="#">Каталог</a>
                            <a href="#">Для учителей</a>
                            <a href="#">Стажировки</a>
                            <a href="https://devskills.foncode.ru/news">Новости</a>
                            <a href="https://devskills.foncode.ru/page/contacts">Контакты</a>
                            <a href="https://devskills.foncode.ru/page/about">О нас</a>
                        </nav>
                        <div class="header__lk">
                            <div class="personal-area personal-area_active">
                                <div class="personal-area__inner">
                                    <a href="https://devskills.foncode.ru/login" class="personal-area__button personal-area_enter">Войти</a>
                                    <a href="https://devskills.foncode.ru/register" class="personal-area__button personal-area_register">Регистрация</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="menu-btn">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </header>
                <div class="b-popup_recovery">
                    <div class="b-popup__content">
                        <div class="b-popup__title">Восстановление пароля</div>
                        <form method="POST" action="https://devskills.foncode.ru/password/email">
                            <input type="hidden" name="_token" value="raHIxcqEYvQCxXOs1aGPTajulasHl7uDn86jEUDQ" autocomplete="off">                    <div class="b-popup__block">
                                <p>E-mail</p>
                                <div class="b-popup__block-right">
                                    <input id="login" type="text" name="login" value="" required autofocus>
                                    <span>Введите e-mail для восстановления пароля.</span>
                                </div>
                            </div>
                            <div class="b-popup__block">
                                <div class="b-popup__block-right">
                                    <input type="submit" name="submit" class="button" value="Восстановить">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <footer>
                <div class="footer__inner">
                    <div class="footer__row">
                        <div class="footer__block">API</div>
                        <div class="footer__block">
                            <div class="footer__logo">
                                <img src="https://devskills.foncode.ru/img/logo.png">
                            </div>
                        </div>
                    </div>
                    <div class="footer__row">© 2011-2023 гг. Сайт не является публичной офертой и носит информационный характер. Все материалы данного сайта являются объектами авторского права (в том числе дизайн). Запрещается копирование, распространение (в том числе путем копирования на другие сайты и ресурсы в Интернете) или любое иное использование информации и объектов без предварительного согласия правообладателя.</div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
</script>