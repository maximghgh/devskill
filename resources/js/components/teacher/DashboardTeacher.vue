<template>
    <div class="center">
        <h2>Панель преподавателя</h2>
        <div class="info__block">
            <div class="info__cards">
                <div class="info__card">
                    <p class="info__text">Активных курсов</p>
                    <span class="info__desc">2</span>
                </div>
                <div class="info__card">
                    <p class="info__text">Всего студентов</p>
                    <span class="info__desc">54</span>
                </div>
            </div>
            <div class="info__events">
                <p class="info__text">Ближайшие занятия</p>
                <span class="info__events-desc"
                    >Веб- разработка: 12 ноября 18.00, 3 корпус Ижгту, 3-1</span
                >
                <span class="info__events-desc"
                    >Дизайн: 15 ноября 18.00, 3 корпус Ижгту, 3-1</span
                >
                <span class="info__events-desc"
                    >Дизайн: 15 ноября 18.00, 3 корпус Ижгту, 3-1</span
                >
            </div>
        </div>
        <div class="main">
            <div class="div">
                <h3>Текущие курсы</h3>
                <div class="course__block">
                    <div class="course_card">
                        <div class="course__text">
                            <h4 class="course__title">Дизайн</h4>
                            <div class="course__info">
                                <span class="course__desc">12 модулей</span>
                                <span class="course__desc">19 студентов</span>
                            </div>
                            <span class="course__desc"
                                >Дата завершения: 22.02.2026</span
                            >
                        </div>
                        <a href="/teacher" class="course__link">Открыть курс</a>

                        <span class="course__status">Активен</span>
                    </div>
                    <div class="course_card">
                        <div class="course__text">
                            <h4 class="course__title">Дизайн</h4>
                            <div class="course__info">
                                <span class="course__desc">12 модулей</span>
                                <span class="course__desc">19 студентов</span>
                            </div>
                            <span class="course__desc"
                                >Дата завершения: 22.02.2026</span
                            >
                        </div>
                        <a href="/teacher" class="course__link">Открыть курс</a>

                        <span class="course__status">Активен</span>
                    </div>
                    <div class="pagination">
                        <button class="pag--active"></button>
                        <button class="pag"></button>
                    </div>
                </div>
            </div>
            
            <div class="div">
                <h3>Ближайшие мероприятия</h3>
                <div class="events__block">
                    <div class="course_card">
                        <div class="course__text">
                            <h4 class="course__title">
                                Выступление по разработке сайтов
                            </h4>
                            <span class="course__desc">Дата проведения: 22 ноября в 18.00</span>
                            <span class="course__desc">Адрес: Ижгту, Интеграл</span>
                        </div>
                    </div>
                    <div class="course_card">
                        <div class="course__text">
                            <h4 class="course__title">
                                Выступление по разработке сайтов
                            </h4>
                            <span class="course__desc">Дата проведения: 22 ноября в 18.00</span>
                            <span class="course__desc">Адрес: Ижгту, Интеграл</span>
                        </div>
                    </div>
                    <div class="course_card">
                        <div class="course__text">
                            <h4 class="course__title">
                                Выступление по разработке сайтов
                            </h4>
                            <span class="course__desc">Дата проведения: 22 ноября в 18.00</span>
                            <span class="course__desc">Адрес: Ижгту, Интеграл</span>
                        </div>
                    </div>
                    <div class="pagination">
                        <button class="pag--active"></button>
                        <button class="pag"></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="course__later">
            <div class="div">
                <h3>Будущие курсы</h3>
                <div class="course__block">
                    <div class="course_card course_card--s">
                        <div class="course__text">
                            <h4 class="course__title">Дизайн</h4>
                            <div class="course__info">
                                <span class="course__desc">12 модулей</span>
                                <span class="course__desc">19 студентов</span>
                            </div>
                            <span class="course__desc"
                                >Дата завершения: 22.02.2026</span
                            >
                        </div>
                        <a href="/teacher" class="course__link">Открыть курс</a>

                        <span class="course__status course__status--red">Закрыт</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import "./style.css";

const rows = ref([]);
const searchQuery = ref("");
const showHints = ref(false);
const showModal = ref(false);
const modalData = ref({ user: "", questions: [], answers: {} });

onMounted(async () => {
    const { data } = await axios.get("/api/final-test-results");
    rows.value = data;
});

// Для подсказок: достаём уникальные имена
const allNames = computed(() =>
    Array.from(
        new Set(
            rows.value
                .map((r) => r.user?.name)
                .filter((n) => typeof n === "string")
        )
    )
);

// Вычисляем подсказки: начинаются с searchQuery, максимум 5 штук
const hints = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return [];
    return allNames.value
        .filter((name) => name.toLowerCase().startsWith(q))
        .slice(0, 5);
});

// После выбора подсказки подставляем её и скрываем hint-лист
function selectHint(name) {
    searchQuery.value = name;
    showHints.value = false;
}

// Скрываем подсказки с небольшой задержкой,
// чтобы клик по пункту успел обработаться
function onBlur() {
    setTimeout(() => {
        showHints.value = false;
    }, 100);
}

// Фильтрация таблицы
const filteredRows = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return rows.value;
    return rows.value.filter((r) => {
        const name = (r.user?.name || "").toLowerCase();
        const idStr = String(r.user_id);
        return name.includes(q) || idStr.includes(q);
    });
});

// Открыть модалку с деталями
async function openModal(resultId) {
    try {
        const { data } = await axios.get(`/api/final-test-results/${resultId}`);
        const quizBlock = data.questions.blocks.find((b) => b.type === "quiz");
        const qs = quizBlock?.data?.questions || [];
        modalData.value = {
            user: data.user,
            questions: qs,
            answers: data.answers,
        };
        showModal.value = true;
    } catch (e) {
        console.error("Не удалось загрузить подробности:", e);
    }
}

function closeModal() {
    showModal.value = false;
}
</script>
