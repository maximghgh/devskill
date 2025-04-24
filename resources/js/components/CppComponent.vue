<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="course-content">
                    <div class="course-content__inner">
                        <!-- Верхний блок -->
                        <div v-if="course" class="course-content__top">
                            <div class="course-content__top-block">
                                <h1>{{ course.course_name }}</h1>
                                <p>{{ course.description }}</p>
                            </div>
                            <div class="course-content__top-block">
                                <img
                                    :src="
                                        course.description_image
                                            ? '/' + course.description_image
                                            : '/img/course_image_main.jpg'
                                    "
                                    class="course-content__top-img"
                                    alt="Изображение курса"
                                    width="600"
                                    height="350"
                                />
                            </div>
                        </div>
                        <div v-else>Загрузка...</div>

                        <!-- Основная часть -->
                        <div class="course-content__main">
                            <!-- Левая часть (контент курса) -->
                            <div class="course-content__main-block">
                                <!-- Элемент для Editor.js -->
                                <div class="editor" id="editorjs"></div>
                                <section
                                    class="course-teachers"
                                    v-if="
                                        course &&
                                        course.teachersData &&
                                        course.teachersData.length
                                    "
                                >
                                    <h2 class="teachers-title">
                                        Наши преподаватели
                                    </h2>
                                    <div class="teachers-list">
                                        <div
                                            class="teacher-card"
                                            v-for="teacher in course.teachersData"
                                            :key="teacher.id"
                                        >
                                            <!-- Если у преподавателя есть фото, выводим его, иначе заглушку -->
                                            <img class="teacher-photo"
                                                :src="
                                                    teacher.photo
                                                        ? `/storage/${teacher.photo}`
                                                        : '/img/teacher_placeholder.png'
                                                "
                                                alt="Преподаватель"
                                                
                                            />
                                            <div class="teacher__info">
                                                <p class="teacher-name">
                                                {{ teacher.name }}
                                                </p>
                                                <!-- Можно вывести и другие поля, например email -->
                                                <p class="teacher-email">
                                                    {{ teacher.email }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- Правая часть (сайдбар) -->
                            <div class="course-content__sidebar" v-if="course">
                                <div class="course-content__sidebar-inner">
                                    <!-- Цена -->
                                    <div class="course-content__sidebar-cost">
                                        {{ course.price }} руб.
                                    </div>
                                    <!-- Дополнительная информация -->
                                    <div class="course-content__sidebar-info">
                                        <p>
                                            В курс входят
                                            <b>{{ course.topics_count }}</b>
                                            темы
                                        </p>
                                        <p>
                                            <b>{{ course.simulators }}</b>
                                            тренажера
                                        </p>
                                        <p>
                                            <b>{{ course.hours }}</b> часа
                                            занятий
                                        </p>
                                    </div>
                                    <a
                                        v-if="course.pdf_path"
                                        :href="`/${course.pdf_path}`"
                                        download                     
                                        class="course-content__sidebar-program"
                                    >
                                        Программа курса
                                    </a>
                                    <small v-else class="no-pdf">PDF не загружен</small>
                                    <a href="" class="button button_sidebar">Купить</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";

const course = ref(null);
let editorInstance = null;

// Функция скачивания PDF
function downloadPdf(path) {
  // Создаём скрытую ссылку и эмулируем клик
  const link = document.createElement('a');
  // Путь может содержать уже '/storage/...'
  link.href = `/${path}`;
  link.download = '';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

onMounted(async () => {
    try {
        // 1. Получаем ID курса из URL
        const path = window.location.pathname;
        const segments = path.split("/");
        let courseId = segments[2];
        if (!courseId) {
            const urlParams = new URLSearchParams(window.location.search);
            courseId = urlParams.get("id");
        }
        if (!courseId) {
            console.error("ID курса не найден в URL");
            return;
        }

        // 2. Запрос курса через API
        const response = await axios.get(`/api/courses/${courseId}`);
        course.value = response.data;

        if (course.value.teachers && Array.isArray(course.value.teachers)) {
            const responseUsers = await axios.post("/api/users/by-ids", {
                ids: course.value.teachers,
            });
            course.value.teachersData = responseUsers.data;
        }

        // 3. Ждём рендеринга контейнера EditorJS
        await nextTick();

        // 4. Данные для EditorJS
        const editorData =
            typeof course.value.editor_data === "string"
                ? JSON.parse(course.value.editor_data)
                : course.value.editor_data || {};

        // 5. Инициализация Editor.js в режиме "только чтение"
        editorInstance = new EditorJS({
            holder: "editorjs",
            readOnly: true,
            data: editorData,
            tools: {
                header: {
                    class: Header,
                    inlineToolbar: ["link"],
                    config: {
                        placeholder: "Введите заголовок",
                        levels: [1, 2, 3, 4],
                        defaultLevel: 2,
                    },
                },
                list: { class: List, inlineToolbar: true },
                image: {
                    class: ImageTool,
                    config: {
                        endpoints: {
                            byFile: "/api/uploadFile",
                            byUrl: "/api/fetchUrl",
                        },
                    },
                },
            },
        });
    } catch (error) {
        console.error("Ошибка при загрузке курса:", error);
    }
});
</script>

<style scoped>
.teacher__info{
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
}
.course-content p {
    font-size: 0.8em;
    margin-bottom: 0;
    text-indent: unset ;
}
.teacher-photo{
    width: 129px;
    height: 129px;
    border-radius: 50%;
}
::v-deep(.ce-block__content) {
    width: 100%;
    max-width: 800px;
}
::v-deep .codex-editor__redactor {
    padding-bottom: 50px !important;
}

/* Пример базовых стилей для блока преподавателя */
.course-teachers {
    border-radius: 10px;
    padding: 20px;
    border: 3px solid #d3d3dd;
    text-align: center;
}

.teacher-title {
    font-size: 2.3em;
    margin-bottom: 55px;
    line-height: 1.2;
    text-align: center;
}

.teachers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    justify-content: center;
    max-width: 1000px;
    margin: 0 auto;
}

.teacher-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    height: 100%;
    gap: 20px;
    background: #fff;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.411);
    text-align: center;
}


.teacher-name {
    text-align: center;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 8px;
}

.teacher-email {
    font-size: 14px;
    color: #666;
}
</style>
