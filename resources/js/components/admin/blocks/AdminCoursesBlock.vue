<template>
    <div class="user-block">
        <h1 class="page__title">Курсы</h1>

        <div class="users-toolbar">
            <div class="asdf">
                <div class="users-toolbar__left">
                    <label class="users-show">
                        Показать
                        <span class="users-show__select-wrap">
                            <select
                                v-model.number="pageSizeCourses"
                                class="users-show__select"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </span>
                        курсов
                    </label>

                    <div class="users-roles">
                        <button
                            type="button"
                            class="users-roles__btn"
                            @click="
                                courseDifficultyDropdownOpen =
                                    !courseDifficultyDropdownOpen
                            "
                        >
                            Уровень
                            <span
                                class="users-roles__chevron"
                                :class="{
                                    'users-roles__chevron--open':
                                        courseDifficultyDropdownOpen,
                                }"
                            ></span>
                        </button>

                        <div
                            v-if="courseDifficultyDropdownOpen"
                            class="users-roles__dropdown"
                            @click.stop
                        >
                            <p class="users-roles__title">Фильтр по уровню</p>

                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="all"
                                    v-model="selectedDifficulty"
                                />
                                <span class="users-roles__span">Все</span>
                            </label>
                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="basic"
                                    v-model="selectedDifficulty"
                                />
                                <span class="users-roles__span">Базовый</span>
                            </label>
                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="middle"
                                    v-model="selectedDifficulty"
                                />
                                <span class="users-roles__span">Средний</span>
                            </label>
                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="advanced"
                                    v-model="selectedDifficulty"
                                />
                                <span class="users-roles__span"
                                    >Продвинутый</span
                                >
                            </label>
                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="mixed"
                                    v-model="selectedDifficulty"
                                />
                                <span class="users-roles__span">Смешанный</span>
                            </label>

                            <div class="users-roles__actions">
                                <button
                                    type="button"
                                    class="btn users-roles__apply"
                                    @click="
                                        courseDifficultyDropdownOpen = false
                                    "
                                >
                                    Фильтр
                                </button>
                                <button
                                    type="button"
                                    class="btn users-roles__reset"
                                    @click="resetCourseDifficultyFilter"
                                >
                                    Сброс
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="users-toolbar__search">
                    <div class="users-search">
                        <span class="users-search__icon">
                            <img
                                width="13"
                                height="13"
                                src="../../../img/admin/search.png"
                                alt=""
                            />
                        </span>
                        <input
                            v-model="searchCourseQuery"
                            type="text"
                            class="users-search__input"
                            placeholder="Поиск курса..."
                        />
                        <button
                            v-if="searchCourseQuery"
                            type="button"
                            class="users-search__clear"
                            @click="searchCourseQuery = ''"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    class="users-btn-new"
                    @click="$emit('requestCreateCourse')"
                >
                    <span class="users-btn-desc">+</span> Добавить курс
                </button>
            </div>
        </div>

        <div class="admin__block">
            <div v-if="paginatedCourses.length">
                <table class="light-push-table">
                    <thead>
                        <tr>
                            <th>Курс</th>
                            <th>Автор</th>
                            <th>Активен</th>
                            <th>Дата создания</th>
                            <th>Участники</th>
                            <th>Уровень</th>
                            <th>Действия</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="course in paginatedCourses" :key="course.id">
                            <td class="avatar__user">
                                <img
                                    :src="
                                        course.card_image
                                            ? `${course.card_image}`
                                            : 'https://devskills.foncode.ru/img/no_foto.jpg'
                                    "
                                    alt=""
                                    width="25"
                                    height="25"
                                    class="avatar__admin"
                                />
                                {{ course.course_name || "Неизвестно" }}
                            </td>

                            <td>{{ course.teacher || "Неизвестно" }}</td>
                            <td>Да</td>
                            <td>{{ formatBirthday(course.created_at) }}</td>
                            <td>0</td>

                            <td>
                                <div class="users-role-pill">
                                    {{ difficultyLabel(course.difficulty) }}
                                </div>
                            </td>

                            <td>
                                <button
                                    class="btn__user--delete"
                                    @click.prevent="deleteCourse(course.id)"
                                >
                                    <img
                                        width="24"
                                        height="24"
                                        src="../../../img/admin/trash.png"
                                        alt=""
                                    />
                                </button>

                                <button
                                    class="btn__user--edit"
                                    @click.prevent="
                                        $emit('requestEditCourse', course)
                                    "
                                >
                                    <img
                                        width="24"
                                        height="24"
                                        src="../../../img/admin/edit.svg"
                                        alt=""
                                    />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div
                    class="pagination-users"
                    v-if="totalPagesCourses > 1"
                    style="margin-top: 20px"
                >
                    <button
                        :disabled="currentPageCourses === 1"
                        @click="currentPageCourses--"
                    >
                        ‹ Назад
                    </button>

                    <button
                        v-for="p in totalPagesCourses"
                        :key="p"
                        :class="{ active: currentPageCourses === p }"
                        @click="currentPageCourses = p"
                    >
                        {{ p }}
                    </button>

                    <button
                        :disabled="currentPageCourses === totalPagesCourses"
                        @click="currentPageCourses++"
                    >
                        Вперёд ›
                    </button>
                </div>
            </div>

            <div v-else>Нет курсов</div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";
import { useDateFormatters } from "../utils/useDateFormatters";
import { useDifficultyLabel } from "../utils/useDifficultyLabel";

const props = defineProps({
    courses: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});
const emit = defineEmits([
    "update:courses",
    "requestCreateCourse",
    "requestEditCourse",
]);

const { formatBirthday } = useDateFormatters();
const { difficultyLabel } = useDifficultyLabel();

function setCourses(next) {
    emit("update:courses", next);
}

const selectedDifficulty = ref("all");
const searchCourseQuery = ref("");
const courseDifficultyDropdownOpen = ref(false);

function resetCourseDifficultyFilter() {
    selectedDifficulty.value = "all";
    courseDifficultyDropdownOpen.value = false;
}

const filteredCourses = computed(() => {
    let base = props.courses;

    if (selectedDifficulty.value !== "all") {
        base = base.filter(
            (c) => String(c.difficulty || "") === selectedDifficulty.value
        );
    }

    const q = (searchCourseQuery.value || "").trim().toLowerCase();
    if (!q) return base;

    return base.filter((c) => {
        const name = (c.course_name || "").toLowerCase();
        const title = (c.card_title || "").toLowerCase();
        const desc = (c.description || "").toLowerCase();
        return name.includes(q) || title.includes(q) || desc.includes(q);
    });
});

const currentPageCourses = ref(1);
const pageSizeCourses = ref(10);

const totalPagesCourses = computed(() => {
    const size = pageSizeCourses.value || 1;
    return Math.max(1, Math.ceil(filteredCourses.value.length / size));
});

const paginatedCourses = computed(() => {
    const size = pageSizeCourses.value || 10;
    const start = (currentPageCourses.value - 1) * size;
    return filteredCourses.value.slice(start, start + size);
});

watch([selectedDifficulty, searchCourseQuery, pageSizeCourses], () => {
    currentPageCourses.value = 1;
});
watch(
    () => props.courses,
    () => {
        currentPageCourses.value = 1;
    }
);

async function deleteCourse(courseId) {
    try {
        await axios.delete(`/api/courses/${courseId}`);
        setCourses(props.courses.filter((c) => c.id !== courseId));
        globalNotification.categoryMessage = "Курс удалён";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при удалении курса";
        globalNotification.type = "error";
    }
}
</script>
