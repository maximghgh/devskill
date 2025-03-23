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
                                        :src="
                                            user.photo
                                                ? `/storage/${user.photo}`
                                                : 'https://devskills.foncode.ru/img/no_foto.jpg'
                                        "
                                        alt="Фото пользователя"
                                        class="course__menu-foto"
                                    />
                                    <div class="course__menu-name">
                                        {{ user.name }}
                                    </div>
                                </div>
                                <div class="course__menu-block">
                                    <div
                                        class="course__menu-one course__menu-one_active"
                                    >
                                        Мои курсы
                                    </div>
                                    <a href="/profile" class="course__menu-one"
                                        >Личные данные</a
                                    >
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
                                    <!-- Если есть хотя бы один купленный курс -->
                                    <div
                                        v-if="purchasedCourses.length > 0"
                                        class="course__cards"
                                    >
                                        <div
                                            v-for="course in purchasedCourses"
                                            :key="course.id"
                                            :class="[
                                                'course__card',
                                                'course__card_personal',
                                                'course__card_bg1',
                                                difficultyColorClass[
                                                    course.difficulty
                                                ],
                                            ]"
                                        >
                                            <div class="course__card-image">
                                                <img
                                                    :src="
                                                        course.card_image
                                                            ? course.card_image
                                                            : '/img/logo_placeholder.png'
                                                    "
                                                    alt="Изображение курса"
                                                />
                                            </div>
                                            <div class="course__card-title">
                                                {{ course.card_title }}
                                            </div>
                                            <div class="course__card-buttons">
                                                <p class="course__card-desc">
                                                    {{
                                                        difficultyTranslation[
                                                            course.difficulty
                                                        ]
                                                    }}
                                                </p>
                                                <div class="course__card-task">
                                                    <p>Пройдено тем</p>
                                                    <p>
                                                        {{
                                                            getCourseProgress(
                                                                course
                                                            ).completedTopics
                                                        }}/{{
                                                            getCourseProgress(
                                                                course
                                                            ).totalTopics
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="course__card-task">
                                                    <p>Решено заданий</p>
                                                    <p>
                                                        {{
                                                            getCourseProgress(
                                                                course
                                                            ).completedTasks
                                                        }}/{{
                                                            getCourseProgress(
                                                                course
                                                            ).totalTasks
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="menu__button">
                                                    <a
                                                        :href="`/content/${course.id}`"
                                                        class="button button_transparent button_transparent--xl"
                                                    >
                                                        Приступить к курсу
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Если список пуст, показываем другое сообщение -->
                                    <div v-else>
                                        Вы не выбрали ещё ни один курс!
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
import { ref, onMounted } from "vue";
import axios from "axios";

// Опционально можно задать имя компонента (начиная с Vue 3.3)
if (import.meta.env.VITE_COMPONENT_NAME) {
    defineOptions({ name: "UserHeader" });
}

const user = ref({ name: "" });
const purchasedCourses = ref([]);

const props = defineProps({
    courseData: {
        type: Object,
        required: true,
    },
});

const difficultyColorClass = {
    basic: "course__card_bg-cyan",
    middle: "course__card_bg-fiolet",
    advanced: "course__card_bg-orange",
    mixed: "course__card_bg-green",
};
const difficultyTranslation = {
    basic: "Начинающий",
    middle: "Средний",
    advanced: "Продвинутый",
    mixed: "Смешанный",
};

function getCourseProgress(course) {
    let totalTopics = 0;
    let completedTopics = 0;
    let totalTasks = 0;
    let completedTasks = 0;

    if (course.topics && course.topics.length) {
        totalTopics = course.topics.length;
        course.topics.forEach((topic) => {
            // Если тема имеет главы, то считаем тему завершённой,
            // если все главы завершены. Если глав нет — можно считать тему пройденной или нет.
            if (topic.chapters && topic.chapters.length) {
                const allChaptersCompleted = topic.chapters.every(
                    (chapter) => chapter.is_completed
                );
                if (allChaptersCompleted) {
                    completedTopics++;
                }
                // Подсчёт заданий (глав с type === 'task')
                topic.chapters.forEach((chapter) => {
                    if (chapter.type === "task") {
                        totalTasks++;
                        if (chapter.is_completed) {
                            completedTasks++;
                        }
                    }
                });
            }
        });
    }
    return {
        completedTopics,
        totalTopics,
        completedTasks,
        totalTasks,
    };
}

onMounted(async () => {
    // Загрузка данных пользователя из localStorage
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
        try {
            user.value = JSON.parse(storedUser);
        } catch (error) {
            console.error(
                "Ошибка при парсинге данных пользователя из localStorage:",
                error
            );
        }
    }

    // Загрузка купленных курсов
    if (user.value.id) {
        try {
            const response = await axios.get(
                `/api/user/${user.value.id}/purchased-courses`
            );
            console.log("Ответ сервера:", response.data);
            console.log("purchasedCourses:", purchasedCourses.value);
            purchasedCourses.value = response.data.courses;
        } catch (error) {
            console.error("Ошибка при загрузке купленных курсов", error);
        }
    }
});
</script>

<style scoped>
.course__card_personal {
    width: 336px;
    padding: 26px 31px;
}
.button_transparent--xl {
    max-width: unset;
}
.course__card-buttons {
    display: grid;
    grid-template-columns: 1fr;
}
</style>
