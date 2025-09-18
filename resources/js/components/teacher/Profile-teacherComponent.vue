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
                                        :src="user.photo ? `/storage/${user.photo}` : 'https://devskills.foncode.ru/img/no_foto.jpg'" 
                                        alt="Фото пользователя" class="course__menu-foto"
                                    />
                                    <div class="course__menu-name-admin">
                                        {{ user.name }}
                                        <span class="course__menu-desc">(Преподаватель)</span>
                                    </div>
                                </div>
                                <div class="course__menu-block">
                                    <a href="/teacher" class="course__menu-one">Вернуться на главную</a>
                                    <div class="course__menu-one course__menu-one_active">
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
                                            :src="user.photo ? `/storage/${user.photo}` : 'https://devskills.foncode.ru/img/no_foto.jpg'" 
                                            alt="Фото пользователя" 
                                        />
                                    </div>
                                    <form action="" class="infoblock__info-form">
                                        <label class="infoblock__info-file">
                                            <input
                                                type="file"
                                                name="file"
                                                accept=".jpg, .png, .wbep,.jpeg"
                                                @change="onFileSelected" 
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
                                            @click="uploadPhoto"
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
                                            <a class="modal__link" href="/admin/profile">Вернуться в профиль</a>
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

<script setup>
import { ref, reactive, onMounted } from "vue";
import axios from "axios";

// Управление модальным окном
const showModal = ref(false);

// Данные пользователя и форма для редактирования профиля
const user = ref({});
const form = reactive({
  id: "",
  name: "",
  email: "",
  birthday: "",
  status: "Ученик",
  phone: "",
  country: "",
});

const isFocused = ref(false);

// Переменная для выбранного файла
const selectedFile = ref(null);

// onMounted – загрузка данных из localStorage и API
onMounted(() => {
  // Попытка загрузить пользователя из localStorage
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    try {
      const parsedUser = JSON.parse(storedUser);
      user.value = parsedUser;

      // Заполняем поля формы
      form.id = parsedUser.id || "";
      form.name = parsedUser.name || "";
      form.email = parsedUser.email || "";
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
      console.error("Ошибка при парсинге пользователя из localStorage:", error);
    }
  }

  // Загружаем актуальные данные из API
  loadUserData();
});

// Функции для управления фокусом поля телефона
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

// Загрузка данных пользователя из API
const loadUserData = async () => {
  try {
    const response = await axios.get("/api/user"); // Laravel API
    const userData = response.data;
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
  } catch (error) {
    console.error("Ошибка загрузки данных:", error);
  }
};

// Функция обновления профиля
const updateProfile = async () => {
  try {
    const response = await axios.post("/api/profile", { ...form });
    // Убираем пароль из данных, если он есть
    delete response.data.user.password;
    // Обновляем localStorage и реактивные данные пользователя
    localStorage.setItem("user", JSON.stringify(response.data.user));
    user.value = response.data.user;
    // Показываем модальное окно
    showModal.value = true;
  } catch (error) {
    console.error("Ошибка сохранения:", error.response?.data || error.message);
  }
};

const closeModal = () => {
  showModal.value = false;
};

// Функция обработки выбора файла
function onFileSelected(event) {
  const file = event.target.files[0];
  selectedFile.value = file;
}

// Функция загрузки фото
async function uploadPhoto() {
  if (!selectedFile.value) {
    alert("Сначала выберите файл!");
    return;
  }
  try {
    const formData = new FormData();
    formData.append("file", selectedFile.value);

    // Берём ID пользователя из реактивной переменной user (или localStorage, если необходимо)
    const userId =
      user.value.id ||
      (localStorage.getItem("user") && JSON.parse(localStorage.getItem("user")).id);
      
    const response = await axios.post(`/api/users/${userId}/photo`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    console.log("Фото обновлено:", response.data.user);
    alert("Фото успешно загружено!");
    // Обновляем данные пользователя в localStorage и в реактивной переменной
    localStorage.setItem("user", JSON.stringify(response.data.user));
    user.value = response.data.user;
  } catch (error) {
    console.error("Ошибка при загрузке фото:", error);
    alert("Ошибка при загрузке фото.");
  }
}
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
.course__menu-desc{
    font-size: 20px;
    font-style: italic;
}
.course__menu-name-admin{
    font-size: 1.9em;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
</style>
