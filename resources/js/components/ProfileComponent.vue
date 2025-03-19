<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="course">
                    <div class="course__inner">
                        <div class="course__content">
                            <div class="course__menu course__menu-persona">
                                <div class="course__menu-nickname">
                                    <img
                                        src="https://devskills.foncode.ru/img/no_foto.jpg"
                                        class="course__menu-foto"
                                    />
                                    <div class="course__menu-name">
                                        {{ user.name }}
                                    </div>
                                </div>
                                <div class="course__menu-block">
                                    <a href="/cabinet" class="course__menu-one"
                                        >Мои курсы</a
                                    >
                                    <div
                                        class="course__menu-one course__menu-one_active"
                                    >
                                        Личные данные
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="infoblock block-tab block-tab_active">
                    <div class="infoblock__wrapper">
                        <div class="infoblock__inner">
                            <div class="infoblock-title">Личные данные</div>
                            <div class="infoblock__info">
                                <div lang="infoblock__info-name">
                                    <div class="infoblock__info-name-image">
                                        <img
                                            src="https://devskills.foncode.ru/img/no_foto.jpg"
                                        />
                                    </div>
                                    <form
                                        action=""
                                        class="infoblock__info-form"
                                    >
                                        <label class="infoblock__info-file">
                                            <input
                                                type="file"
                                                name="file"
                                                accept=".jpg, .png, .wbep,.jpeg"
                                            />
                                            <span
                                                class="infoblock__info-filebtn"
                                                >Загрузить фото</span
                                            >
                                            <span
                                                class="infoblock__info-filetext"
                                            ></span>
                                        </label>
                                        <input
                                            type="submit"
                                            value="Добавить"
                                            class="infoblock__button"
                                        />
                                    </form>
                                </div>
                                <form
                                    @submit.prevent="updateProfile"
                                    class="infoblock__data"
                                >
                                    <div class="infoblock__data-top">
                                        <div class="custom-input">
                                            <input
                                                type="text"
                                                v-model="form.name"
                                                placeholder="ФИО"
                                            />
                                            <label class="custom-label"
                                                >ФИО</label
                                            >
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                type="email"
                                                v-model="form.email"
                                                placeholder="E-mail"
                                            />
                                            <label class="custom-label"
                                                >E-mail</label
                                            >
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                type="date"
                                                v-model="form.birthday"
                                                placeholder="Дата рождения"
                                            />
                                            <label class="custom-label"
                                                >Дата рождения</label
                                            >
                                        </div>
                                        <div class="custom-input">
                                            <label class="custom-label"
                                                >Статус</label
                                            >
                                            <input
                                                class="custom-status"
                                                type="text"
                                                v-model="form.status"
                                                placeholder="Ученик"
                                                disabled
                                            />
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                id="phone"
                                                v-model="form.phone"
                                                v-mask="'+7 (###) ###-##-##'"
                                                :placeholder="
                                                    isFocused
                                                        ? ''
                                                        : '+7 999 999-99-99'
                                                "
                                            />
                                            <label class="custom-label"
                                                >Телефон</label
                                            >
                                        </div>
                                        <div class="custom-input">
                                            <input
                                                type="text"
                                                v-model="form.country"
                                                placeholder="Страна + город"
                                            />
                                            <label class="custom-label"
                                                >Местоположение</label
                                            >
                                        </div>
                                    </div>
                                    <input type="submit" value="Сохранить" />
                                </form>
                                <div v-if="showModal" class="modal-overlay">
                                    <div class="modal-content">
                                        <h2>Данные изменены</h2>
                                        <p>Перейти в профиль: 
                                            <a class="modal__link" href="/profile">Вернуться в профиль</a>
                                        </p>
                                    </div>
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
                                <img
                                    src="https://devskills.foncode.ru/img/logo.png"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="footer__row">
                        © 2011-2023 гг. Сайт не является публичной офертой и
                        носит информационный характер. Все материалы данного
                        сайта являются объектами авторского права (в том числе
                        дизайн). Запрещается копирование, распространение (в том
                        числе путем копирования на другие сайты и ресурсы в
                        Интернете) или любое иное использование информации и
                        объектов без предварительного согласия правообладателя.
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted } from "vue";
import axios from "axios";

export default {
    name: "UserHeader",
    setup() {
        // Модальное окно
        const showModal = ref(false);

        const user = ref({});
        const isFocused = ref(false);

        // Добавляем поле id в объект form
        const form = reactive({
            id: "",
            name: "",
            email: "",
            birthday: "",
            status: "Ученик",
            phone: "",
            country: "",
        });

        onMounted(() => {
            console.log(
                "Начальное localStorage:",
                localStorage.getItem("user")
            );
            const storedUser = localStorage.getItem("user");
            if (storedUser) {
                try {
                    const parsedUser = JSON.parse(storedUser);
                    user.value = parsedUser;

                    // Заполняем все нужные поля формы
                    form.id = parsedUser.id || "";
                    form.name = parsedUser.name || "";
                    form.email = parsedUser.email || "";
                    // Добавляем поля phone, country, birthday, role => status
                    form.phone = parsedUser.phone || "";
                    form.country = parsedUser.country || "";
                    form.birthday = parsedUser.birthday || "";
                    form.status =
                        parsedUser.role === 1
                            ? "Ученик"
                            : parsedUser.role === 2
                            ? "Преподаватель"
                            : "Администратор";
                } catch (error) {
                    console.error(
                        "Ошибка при парсинге пользователя из localStorage:",
                        error
                    );
                }
            }

            // Загружаем данные пользователя из API
            loadUserData();
        });

        const onFocus = () => {
            isFocused.value = true;
            if (!form.phone || form.phone === "+7") {
                form.phone = "+7 ";
            }
        };

        const onBlur = () => {
            isFocused.value = false;
            if (form.phone === "+7 ") {
                form.phone = "";
            }
        };

        const loadUserData = async () => {
            try {
                const response = await axios.get("/api/user"); // Laravel API
                const userData = response.data;
                // Обновляем id и остальные поля из ответа API
                form.id = userData.id;
                form.name = userData.name || "";
                form.email = userData.email || "";
                form.birthday = userData.birthday || "";
                form.status =
                    userData.role === 1
                        ? "Ученик"
                        : userData.role === 2
                        ? "Преподаватель"
                        : "Администратор";
                form.phone = userData.phone || "";
                form.country = userData.country || "";
                console.log("Ответ API /api/user:", response.data);
            } catch (error) {
                console.error("Ошибка загрузки данных:", error);
            }
        };

        const updateProfile = async () => {
        try {
          const response = await axios.post('/api/profile', { ...form });
          // Вместо alert() теперь показываем модалку
          // alert(response.data.message);

          // Удаляем password, если есть
          delete response.data.user.password;

          // Сохраняем обновлённые данные пользователя в localStorage
          localStorage.setItem('user', JSON.stringify(response.data.user));
          console.log("LocalStorage после обновления:", localStorage.getItem('user'));

          // Обновляем реактивную переменную user
          user.value = response.data.user;

          // Открываем модальное окно
          showModal.value = true;
        } catch (error) {
          console.error('Ошибка сохранения:', error.response?.data || error.message);
        }
      };
      const closeModal = () => {
        showModal.value = false;
      };

        // Возвращаем новые переменные (showModal) и всё остальное
        return {
            showModal, // чтобы управлять модальным окном
            user,
            form,
            isFocused,
            onFocus,
            onBlur,
            loadUserData,
            updateProfile,
        };
    },
};
</script>

<style>
.custom-input {
    position: relative;
}
.custom-label {
    font-size: 14px;
    position: absolute;
    left: 20px;
    top: -8px;
    background-color: #ffffff;
    border: 1px solid #eeeef4;
    border-radius: 5px;
    padding: 5px 10px;
}
.custom-status{
    color: #575adf;
}
.custom-status::placeholder {
    color: #575adf;
}
.modal-overlay {
  position: fixed;
  top: 0; 
  left: 0;
  right: 0; 
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5); 
  display: flex;
  align-items: center; 
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: #fff; 
  padding: 20px 30px;
  border-radius: 8px;
  width: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
}
.modal__link{
  color: #575adf;
  text-decoration: none;
}
.modal__link:hover{
  text-decoration: underline;
}
</style>
