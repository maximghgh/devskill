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
                            <img
                                class="select__icon"
                                src="../../../../img/admin/select.svg"
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
                                src="../../../../img/admin/search.png"
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
                    v-if="showCreate"
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
                            <th v-if="showAuthor">Автор</th>
                            <th>Активен</th>
                            <th>Дата создания</th>
                            <th>Участники</th>
                            <th v-if="showActions">Действия</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="course in paginatedCourses" :key="course.id">
                            <td class="avatar__user">
                                <img
                                    :src="
                                        course.card_image
                                            ? `${course.card_image}`
                                            : '/img/no_foto.jpg'
                                    "
                                    alt=""
                                    width="25"
                                    height="25"
                                    class="avatar__admin"
                                />
                                {{ course.course_name || "Неизвестно" }}
                            </td>

                            <td v-if="showAuthor">{{ teacherLabel(course) }}</td>
                            <td>Да</td>
                            <td>{{ formatBirthday(course.created_at) }}</td>
                            <td>0</td>

                            <td v-if="showActions" class="hadle">
                                <div class="tooltip-container">
                                    <button aria-describedby="help-tooltip" class="btn__user--edit" @click.prevent="$emit('requestEditCourse', course)">
                                        <img
                                            width="24"
                                            height="24"
                                            src="../../../../img/admin/edit.svg"
                                            alt=""
                                        />
                                    </button>
                                    <div role="tooltip" id="help-tooltip" class="tooltip">
                                        Редактировать курс
                                    </div>
                                </div>
                                <div class="tooltip-container">
                                    <button aria-describedby="help-tooltip" class="btn__user--edit" @click.prevent="deleteCourse(course.id)">
                                        <img
                                        width="24"
                                        height="24"
                                        src="../../../../img/admin/trash.png"
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
import { globalNotification } from "../../../globalNotification";
import { useDateFormatters } from "../utils/useDateFormatters";

const props = defineProps({
    courses: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    showCreate: { type: Boolean, default: true },
    showActions: { type: Boolean, default: true },
    showAuthor: { type: Boolean, default: true },
});
const emit = defineEmits([
    "update:courses",
    "requestCreateCourse",
    "requestEditCourse",
]);

const { formatBirthday } = useDateFormatters();

const usersById = computed(() => {
    const map = new Map();
    props.users.forEach((user) => {
        if (!user || user.id == null) return;
        const name =
            user.name ||
            user.full_name ||
            user.email ||
            `ID ${user.id}`;
        map.set(String(user.id), name);
    });
    return map;
});

function normalizeTeacherIds(teachers) {
    if (Array.isArray(teachers)) {
        return teachers
            .map((t) => (typeof t === "object" && t ? t.id : t))
            .filter((t) => t !== null && t !== undefined);
    }
    if (teachers == null) return [];
    if (typeof teachers === "number") return [teachers];
    if (typeof teachers === "string") {
        try {
            const parsed = JSON.parse(teachers);
            if (Array.isArray(parsed)) {
                return parsed
                    .map((t) => (typeof t === "object" && t ? t.id : t))
                    .filter((t) => t !== null && t !== undefined);
            }
            if (parsed == null) return [];
            return [parsed];
        } catch {
            return [];
        }
    }
    return [];
}

function teacherLabel(course) {
    const ids = normalizeTeacherIds(course?.teachers);
    const names = ids
        .map((id) => usersById.value.get(String(id)))
        .filter(Boolean);
    if (names.length) return names.join(", ");
    return course?.teacher || "Неизвестно";
}

function setCourses(next) {
    emit("update:courses", next);
}

const searchCourseQuery = ref("");

const filteredCourses = computed(() => {
    const base = props.courses;

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

watch([searchCourseQuery, pageSizeCourses], () => {
    currentPageCourses.value = 1;
});
watch(totalPagesCourses, (tp) => {
    if (currentPageCourses.value > tp) currentPageCourses.value = tp;
});

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

<style scoped>
.users-role-pill--beginner-year-1{
  background: #D8E9FF;
}

.users-role-pill--beginner-year-2{
  background: #D6F5D6;
}

.users-role-pill--basic{
  background: #BDE5B0;
}

.users-role-pill--fundamental{
  background: #E5DFB0;
}

.users-role-pill--olympiad{
  background: #E5B0B0;
}

.users-role-pill--legacy{
  background: #d7d7d7;
}

.users-role-pill--default{
  background: #e9e9e9;
}
</style>
