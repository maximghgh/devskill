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
                            <a href="/news">Новости</a>
                            <a href="/contacts">Контакты</a>
                            <a href="/about">О нас</a>
                        </nav>
                        <div class="header__lk">
                            <a href="https://devskills.foncode.ru/cabinet/profile" class="header__lk-img">
                                <img src="https://devskills.foncode.ru/img/nofotolk.png">
                            </a>
                            <div class="header__lk-name">
                                <div class="personal-area personal-area_active">
                                <div class="personal-area__inner">
                                    <!-- Показываем, если пользователь вошел -->
                                    <div v-if="user" class="header__lk">
                                        <a href="/profile" class="header__lk-img">
                                            <img src="https://devskills.foncode.ru/img/nofotolk.png" width="40" height="40">
                                        </a>
                                        <div class="header__lk-name">
                                            <a class="personal-area__username">{{ user.name }}</a>
                                            <a href="#" @click="logout" class="personal-area__button">Выйти</a>
                                        </div>
                                    </div>
                                    <!-- Показываем, если НЕ вошел -->
                                    <div v-else>
                                        <a href="/login" class="personal-area__username">Войти</a>
                                        <a href="/register" class="personal-area__username">Регистрация</a>
                                    </div>
                                </div>
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
                
                <div class="b-popup_enter">
                    <div class="b-popup__content">
                        <div class="b-popup__title">Проверьте свой адрес электронной почты</div>
                        <div class="b-popup__block">
                                            </div>
                        <div class="b-popup__block">
                            Прежде чем продолжить, проверьте свою электронную почту на наличие ссылки для подтверждения.
                            Если вы не получили письмо, перейдите по ссылке
                        </div>
                        <div class="b-popup__block">
                            <div class="b-popup__block-right">
                                <form class="d-inline" method="POST" action="https://devskills.foncode.ru/email/resend">
                                    <input type="hidden" name="_token" value="gzADg3WKZ3cWAuZQpAWAD5VFBQhhImrqhWIOsAVr" autocomplete="off">                            
                                    <input type="submit" class="button" value="ссылка">
                                </form>
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

