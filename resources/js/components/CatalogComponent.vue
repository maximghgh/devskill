<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="course">
                    <div class="course__inner">
                        <div class="course__sidebar">
                            <div class="course__sidebar-block">
                                <div class="course__sidebar-title">
                                    Тематика
                                </div>
                                <div class="course__sidebar-check">
                                    <div
                                        class="course__sidebar-oncheck"
                                        v-for="language in languages"
                                        :key="language.id"
                                    >
                                        <!-- Привязываем состояние конкретного языка -->
                                        <input
                                            :id="'lang_' + language.id"
                                            type="checkbox"
                                            :name="'lang_' + language.id"
                                            v-model="language.checked"
                                        />
                                        <label :for="'lang_' + language.id">
                                            <span>{{ language.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="course__sidebar-block">
                                <div class="course__sidebar-title">
                                    Уровень
                                </div>
                                <div class="course__sidebar-check">
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_1"
                                            name="lvl_1"
                                            type="checkbox"
                                            value="basic"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_1"><span>Начинающий</span></label>
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_2"
                                            name="lvl_2"
                                            type="checkbox"
                                            value="middle"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_2"><span>Средний</span></label>
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_3"
                                            name="lvl_3"
                                            type="checkbox"
                                            value="advanced"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_3"><span>Продвинутый</span></label>
                                    </div>
                                    <div class="course__sidebar-oncheck">
                                        <input
                                            id="lvl_4"
                                            name="lvl_4"
                                            type="checkbox"
                                            value="mixed"
                                            v-model="selectedDifficulties"
                                        />
                                        <label for="lvl_4"><span>Смешанный</span></label>
                                    </div>
                                </div>
                            </div>
                            <div class="course__sidebar-block course__sidebar-block_noline">
                                <div class="course__sidebar-title">
                                    Длительность
                                </div>
                                <div class="course__sidebar-check">
                                    <div class="course__sidebar-input">
                                        <label>
                                            <input
                                                type="number"
                                                min="1"
                                                max="24"
                                                v-model="selectedDuration"
                                                @input="validateDuration"
                                            />
                                            <span>От 1 до 24 месяцев</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>   
                        <div class="course__content">
                            <h2>Все программы обучения</h2>
                            <div class="course__menu">
                                <!-- Пункт "Все направления" -->
                                <div
                                    class="course__menu-one"
                                    :class="{
                                        active: selectedDirection === 'all',
                                    }"
                                    @click="selectedDirection = 'all'"
                                >
                                    Все направления
                                </div>
                                <!-- Остальные направления -->
                                <div
                                    class="course__menu-one"
                                    v-for="direction in directions"
                                    :key="direction.id"
                                    :class="{
                                        active:
                                            selectedDirection === direction.id,
                                    }"
                                    @click="selectedDirection = direction.id"
                                >
                                    {{ direction.name }}
                                </div>
                            </div>
                            <div class="course__cards">
                                <div
                                    v-for="course in paginatedCourses"
                                    :key="course.id"
                                    :class="[
                                        'course__card',
                                        'course__card_bg1 ',
                                        difficultyColorClass[course.difficulty],
                                    ]"
                                >
                                    <!-- Изображение курса -->
                                    <div class="course__card-image">
                                        <!-- Проверяем, есть ли путь к файлу. Если нет, можно подставить заглушку -->
                                        <img
                                            :src="
                                                course.card_image
                                                    ? course.card_image
                                                    : '/img/logo_placeholder.png'
                                            "
                                            alt="Изображение курса"
                                        />
                                    </div>
                                    <!-- Название на карточке -->
                                    <div class="course__card-title">
                                        {{ course.card_title }}
                                    </div>
                                    <!-- Уровень сложности -->

                                    <!-- Цена -->
                                    <!-- Кнопки -->
                                    <div class="course__card-buttons">
                                        <div class="course__card-price">
                                            <p class="course__card-desc">
                                                {{
                                                    difficultyTranslation[
                                                        course.difficulty
                                                    ]
                                                }}
                                            </p>
                                            <span>{{ course.price }} P</span>
                                        </div>
                                        <!-- Ссылка «Подробнее» может вести на страницу с подробной информацией, например /courses/ID -->
                                        <div class="menu__button">
                                            <a
                                                :href="`/cpp/${course.id}`"
                                                class="button button_transparent"
                                                >Подробнее</a
                                            >
                                            <a
                                                href="#"
                                                class="button button_white button_white-card"
                                                >Купить</a
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                                <!-- Список номеров страниц -->
                                
                        </div>
                    </div>
                    <div class="pagination-pages">
                        <button
                            v-for="page in pages"
                            :key="page"
                            @click="currentPage = page"
                            :class="{ active: currentPage === page }"
                        >
                            {{ page }}
                        </button>
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

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";

/* --- Реактивные переменные --- */
const user = ref(null);
const courses = ref([]);
const languages = ref([]);
const directions = ref([]);

// Количество карточек на одной странице

// Пагинация
const pageSize = 6;
const currentPage = ref(1);

// paginatedCourses — это срез массива courses для текущей страницы
const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return courses.value.slice(start, start + pageSize);
});

// Общее количество страниц
const totalPages = computed(() => {
  return Math.ceil(courses.value.length / pageSize);
});

// Кнопки перехода
function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
}

function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
}

// Вычисляем массив номеров страниц
const pages = computed(() => {
  return Array.from({ length: totalPages.value }, (_, i) => i + 1);
});

// Сброс страницы на 1 при изменении количества курсов
watch(() => courses.value.length, () => {
  currentPage.value = 1;
});


/* --- Фильтры --- */
const selectedDifficulties = ref([]);
const selectedDirection = ref("all");
const selectedDuration = ref(null);

// Вычисляемое свойство для выбранных языков
const selectedLanguages = computed(() => {
    return languages.value
        .filter((language) => language.checked)
        .map((language) => language.id);
});
const difficultyTranslation = {
    basic: "Начинающий",
    middle: "Средний",
    advanced: "Продвинутый",
    mixed: "Смешанный",
};
const difficultyColorClass = {
    basic: "course__card_bg-cyan",
    middle: "course__card_bg-fiolet",
    advanced: "course__card_bg-orange",
    mixed: "course__card_bg-green",
};
/* --- Вспомогательные функции --- */
function base64ToUtf8(str) {
    return decodeURIComponent(escape(atob(str)));
}

function validateDuration(event) {
    let value = event.target.value;
    // Если поле пустое, ничего не меняем
    if (value === "") {
        selectedDuration.value = "";
        return;
    }

    const numericValue = Number(value);
    if (numericValue > 24) {
        event.target.value = 24;
        selectedDuration.value = 24;
    } else if (numericValue < 1) {
        // Если значение меньше 1, оставляем поле пустым
        event.target.value = "";
        selectedDuration.value = "";
    } else {
        selectedDuration.value = numericValue;
    }
}

const logout = () => {
    localStorage.removeItem("user");
    user.value = null;
    window.location.href = "/";
};

function goToCourse(courseId) {
    console.log("Переход на курс с id:", courseId);
    window.location.href = `/cpp/${courseId}`;
}

/* --- Загрузка направлений --- */
async function loadDirections() {
    try {
        const response = await axios.get("/api/directions");
        console.log("Ответ API (направления):", response.data);
        if (Array.isArray(response.data)) {
            directions.value = response.data;
        } else if (response.data && Array.isArray(response.data.data)) {
            directions.value = response.data.data;
        } else {
            console.warn(
                "Непредвиденный формат данных направлений:",
                response.data
            );
            directions.value = [];
        }
    } catch (error) {
        console.error("Ошибка при загрузке направлений:", error);
    }
}

/* --- Функция получения курсов с параметрами фильтров --- */
async function fetchCourses() {
    try {
        let params = {
        };

        if (selectedLanguages.value.length > 0) {
            params.languages = selectedLanguages.value.join(',');
        }
        if (selectedDifficulties.value.length > 0) {
            params.difficulties = selectedDifficulties.value;
        }
        if (selectedDirection.value !== "all") {
            params.direction = selectedDirection.value;
        }
        if (selectedDuration.value) {
            params.duration = selectedDuration.value;
        }

        const response = await axios.get("/api/courses", { params });
        console.log("Ответ API (курсы):", response.data);

        // Если данные приходят в виде объекта с ключом data, используем его
        const coursesData = response.data.data
            ? response.data.data
            : response.data;
        if (Array.isArray(coursesData)) {
            courses.value = coursesData;
        } else {
            console.warn("Непредвиденный формат данных курсов:", response.data);
            courses.value = [];
        }
    } catch (error) {
        console.error("Ошибка при загрузке курсов:", error);
    }
}

/* --- Watch для автоматической фильтрации --- */
watch(selectedDifficulties, fetchCourses, { deep: true });
watch(selectedDirection, fetchCourses);
watch(selectedDuration, fetchCourses);
watch(selectedLanguages, fetchCourses);

/* --- onMounted: начальная загрузка данных --- */
onMounted(async () => {
    // Обработка параметра verifiedUser
    const urlParams = new URLSearchParams(window.location.search);
    const verifiedUserParam = urlParams.get("verifiedUser");
    if (verifiedUserParam) {
        try {
            const pureBase64 = decodeURIComponent(verifiedUserParam);
            const decodedString = base64ToUtf8(pureBase64);
            const verifiedUserData = JSON.parse(decodedString);
            localStorage.setItem("user", JSON.stringify(verifiedUserData));
            localStorage.removeItem("pendingUser");
            console.log(
                "✅ Email подтвержден, данные пользователя сохранены:",
                verifiedUserData
            );
        } catch (error) {
            console.error(
                "❌ Ошибка при декодировании verifiedUserParam:",
                error
            );
        }
    }

    // Загрузка данных пользователя
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
        try {
            user.value = JSON.parse(storedUser);
            console.log("✅ Пользователь авторизован:", user.value);
        } catch (error) {
            console.error("❌ Ошибка при парсинге localStorage:", error);
        }
    } else {
        console.warn("⚠️ Пользователь НЕ авторизован.");
    }
    loadDirections();
    // Загрузка языков
    try {
        const langResponse = await axios.get("/api/languages");
        console.log("Языки (API /api/languages):", langResponse.data);
        if (Array.isArray(langResponse.data)) {
            languages.value = langResponse.data.map((lang) => ({
                ...lang,
                checked: lang.checked ?? false,
            }));
        } else if (langResponse.data && Array.isArray(langResponse.data.data)) {
            languages.value = langResponse.data.data.map((lang) => ({
                ...lang,
                checked: lang.checked ?? false,
            }));
        } else {
            console.warn(
                "Непонятная структура данных языков:",
                langResponse.data
            );
            languages.value = [];
        }
    } catch (error) {
        console.error("Ошибка при получении языков:", error);
    }

    // Загружаем курсы и направления
    fetchCourses();
});
</script>

<style scoped>
@media (max-width: 650px) {
    .course__menu {
        display: flex;
        align-items: unset;
        justify-content: unset;
        flex-wrap: unset;
        gap: 20px;
        overflow: auto;
        margin: 0 -10px 40px;
        padding: 0 10px 0;
    }
    .course__menu::-webkit-scrollbar {
        display: none;
    }
    .course__menu-one {
        height: 70px;
        padding: 0px 20px;
        border-radius: 25px;
    }
}
    .pagination-pages {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 3rem;
    }

    .pagination-pages button {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ccc;
    padding: 0.5rem 0.8rem;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    }

    .pagination-pages button:hover:not(:disabled) {
    background-color: #e6e6e6;
    }

    .pagination-pages button.active {
        background-color: #698dc9;
        color: #fff;
    }


    .menu__button {
        display: flex;
        gap: 19px;
    }
    .course__card-buttons {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    .course__card-price {
        display: flex;
        flex-direction: column;
        gap: 25px;
        font-family: JanoSansProSemiBold;
        font-size: 1.4em;
    }
    .course__card-desc {
        font-size: 21px;
        font-family: JanoSansProRegular;
    }
    .course__menu-one {
        cursor: pointer;
    }
    .course__card_bg-cyan {
        background-color: #698dc9;
    }
    .course__card_bg-fiolet {
        background-color: #727dcc;
    }
    .course__card_bg-orange {
        background-color: #d48a66;
    }
    .course__card_bg-green {
        background-color: #5bcaa7;
    }
</style>
