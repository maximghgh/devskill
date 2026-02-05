<template>
    <div class="teacher__block">
        <h1 class="page__title">Мои курсы</h1>

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
                            <img
                                class="select__icon"
                                :src="selectIcon"
                                alt=""
                            />
                        </span>
                        курсов
                    </label>
                </div>

                <div class="users-toolbar__search">
                    <div class="users-search">
                        <span class="users-search__icon">
                            <img
                                width="13"
                                height="13"
                                :src="searchIcon"
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

        <div class="teacher__block">
            <div v-if="paginatedCourses.length">
                <table class="light-push-table">
                    <thead>
                        <tr>
                            <th>Курс</th>
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
                                            ? normalizeImageUrl(course.card_image)
                                            : '/img/no_foto.jpg'
                                    "
                                    alt=""
                                    width="25"
                                    height="25"
                                    class="avatar__admin"
                                />
                                {{ course.course_name || "Неизвестно" }}
                            </td>

                            <td>Да</td>
                            <td>{{ formatBirthday(course.created_at) }}</td>
                            <td>0</td>

                            <td>
                                <div class="users-role-pill" :class="difficultyClass(course.difficulty)">
                                    {{ difficultyLabel(course.difficulty) }}
                                </div>
                            </td>

                            <td class="hadle">
                                <div class="tooltip-container">
                                    <button
                                        aria-describedby="help-tooltip"
                                        class="btn__user--edit"
                                        @click.prevent="$emit('requestEditCourse', course)"
                                    >
                                        <img
                                            width="24"
                                            height="24"
                                            :src="editIcon"
                                            alt=""
                                        />
                                    </button>
                                    <div role="tooltip" id="help-tooltip" class="tooltip">
                                        Редактировать курс
                                    </div>
                                </div>
                                <div class="tooltip-container">
                                    <button
                                        aria-describedby="help-tooltip"
                                        class="btn__user--edit"
                                        @click.prevent="deleteCourse(course.id)"
                                    >
                                        <img
                                            width="24"
                                            height="24"
                                            :src="trashIcon"
                                            alt=""
                                        />
                                    </button>
                                    <div role="tooltip" id="help-tooltip" class="tooltip">
                                        Удалить курс
                                    </div>
                                </div>
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
import { globalNotification } from "../../globalNotification";
import { useDateFormatters } from "../admin/utils/useDateFormatters";
import { useDifficultyLabel } from "../admin/utils/useDifficultyLabel";

const selectIcon = new URL("../../../img/admin/select.svg", import.meta.url)
    .href;
const searchIcon = new URL("../../../img/admin/search.png", import.meta.url)
    .href;
const editIcon = new URL("../../../img/admin/edit.svg", import.meta.url).href;
const trashIcon = new URL("../../../img/admin/trash.png", import.meta.url).href;

const props = defineProps({
    courses: { type: Array, default: () => [] },
});
const emit = defineEmits([
    "update:courses",
    "requestCreateCourse",
    "requestEditCourse",
]);

const { formatBirthday } = useDateFormatters();
const { difficultyLabel } = useDifficultyLabel();

function difficultyClass(diff) {
    const d = String(diff || "").toLowerCase();

    if (d === "basic") return "users-role-pill--basic";
    if (d === "middle") return "users-role-pill--middle";
    if (d === "advanced") return "users-role-pill--advanced";

    return "users-role-pill--default";
}

function setCourses(next) {
    emit("update:courses", next);
}

const searchCourseQuery = ref("");

const filteredCourses = computed(() => {
    const q = (searchCourseQuery.value || "").trim().toLowerCase();
    if (!q) return props.courses;

    return props.courses.filter((c) => {
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

watch([searchCourseQuery, pageSizeCourses], () => {
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

function normalizeImageUrl(value) {
    if (!value) return "";
    if (value.startsWith("http://") || value.startsWith("https://")) {
        return value;
    }
    if (value.startsWith("/")) {
        return value;
    }
    return `/${value}`;
}
</script>

<style scoped>
.users-role-pill--basic {
    background: #bde5b0;
}
.users-role-pill--middle {
    background: #e5dfb0;
}
.users-role-pill--advanced {
    background: #e5b0b0;
}
.users-role-pill--default {
    background: #e9e9e9;
}
</style>
