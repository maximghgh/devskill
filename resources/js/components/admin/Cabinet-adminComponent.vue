<template>
    <div>
        <div class="maincontainer">
        <div class="container">            
            <section class="course">
                <div class="course__inner">
                    <div class="course__content">
                        <div class="course__menu course__menu-persona">
                            <div class="course__menu-nickname">
                                <img src="https://devskills.foncode.ru/img/no_foto.jpg" class="course__menu-foto">
                                <div class="course__menu-name">{{ user.name }}</div>
                            </div>
                            <div class="course__menu-block">
                                <div class="course__menu-one course__menu-one_active">Мои курсы</div>
                                <a href="/admin/profile" class="course__menu-one">Личные данные</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="course block-tab block-tab_active">
                <div class="course__inner">
                    <div class="course__content">
                        <div class="course__cards course__cards_personal">
                            <div class="course__cards-title">Мои курсы</div>
                            <div class="course__cards-inner">
                                <div>Вы не выбрали ещё ни один курс!</div>
                            </div>
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
                            <img src="../../img/logo.png" class="header__logo">
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
import { ref, onMounted } from 'vue';

export default {
  name: 'UserHeader',
  setup() {
    const user = ref({ name: '' });
    
    onMounted(() => {
      const storedUser = localStorage.getItem('user');
      if (storedUser) {
        try {
          // Предполагается, что user хранится в формате JSON
          user.value = JSON.parse(storedUser);
        } catch (error) {
          console.error('Ошибка при парсинге данных пользователя из localStorage:', error);
        }
      }
    });
    
    return { user };
  }
};
</script>