<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import { globalNotification } from "@/globalNotification";

const props = defineProps({
    draft: { type: Object, default: () => ({}) },
    isEdit: { type: Boolean, default: false },
    admin: { type: Boolean, default: false },
});

const emit = defineEmits(["saved", "cancel", "dirty", "saving"]);

const courseId = computed(() =>
    props.draft?.courseId ??
    props.draft?.id ??
    (Number.isFinite(+props.draft?.slug) ? +props.draft.slug : null)
);

const modalOpen = ref(false);
const modalMode = ref("create"); // create | edit
const editingGroupId = ref(null);
const modalLoading = ref(false);
const groupName = ref("");
const searchQuery = ref("");
const students = ref([]);
const studentsLoading = ref(false);
const studentsLoaded = ref(false);
const groupsLoading = ref(false);
const createLoading = ref(false);
const groups = ref([]);
const selectedStudentIds = ref([]);
const originalStudentIds = ref([]);
const originalGroupMembers = ref([]);
const pendingRemovalId = ref(null);
const studentsError = ref(false);

const hasStudents = computed(() => students.value.length > 0);
const noStudents = computed(
    () => studentsLoaded.value && !studentsLoading.value && !hasStudents.value
);

const filteredStudents = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    const available = students.value.filter((student) =>
        !originalStudentIds.value.includes(student.id) || !isSelected(student.id));
    if (!q) return available;
    return available.filter((s) => {
        const name = String(s?.name || "").toLowerCase();
        const email = String(s?.email || "").toLowerCase();
        const login = String(s?.login || "").toLowerCase();
        return name.includes(q) || email.includes(q) || login.includes(q);
    });
});

const addedStudents = computed(() => students.value.filter((student) =>
    isSelected(student.id) && !originalStudentIds.value.includes(student.id)));
const existingGroupMembers = computed(() => {
    const byId = new Map(students.value.map((student) => [student.id, student]));
    return originalGroupMembers.value.filter((student) => isSelected(student.id))
        .map((student) => byId.get(student.id) ?? student);
});
const removedCount = computed(() => originalStudentIds.value.filter((id) => !isSelected(id)).length);

function canSelect(student) {
    return originalStudentIds.value.includes(student.id) || !student.groups?.some((group) => group.id !== editingGroupId.value);
}

const studentPageSize = ref(10);
const studentPage = ref(1);

const totalStudentPages = computed(() => {
    const size = studentPageSize.value || 1;
    return Math.max(1, Math.ceil(filteredStudents.value.length / size));
});

const studentPageNumbers = computed(() => {
    const start = Math.max(1, Math.min(studentPage.value - 2, totalStudentPages.value - 4));
    return Array.from({ length: Math.min(5, totalStudentPages.value) }, (_, i) => start + i);
});

const paginatedStudents = computed(() => {
    const size = studentPageSize.value || 5;
    const start = (studentPage.value - 1) * size;
    return filteredStudents.value.slice(start, start + size);
});

function isSelected(id) {
    return selectedStudentIds.value.includes(id);
}

function toggleStudent(id) {
    const list = selectedStudentIds.value.slice();
    const idx = list.indexOf(id);
    if (idx === -1) list.push(id);
    else list.splice(idx, 1);
    selectedStudentIds.value = list;
}

function confirmRemoval(id) {
    if (pendingRemovalId.value !== id || createLoading.value || modalLoading.value) return;
    selectedStudentIds.value = selectedStudentIds.value.filter((studentId) => studentId !== id);
    pendingRemovalId.value = null;
}

function resetForm() {
    groupName.value = "";
    searchQuery.value = "";
    selectedStudentIds.value = [];
    originalStudentIds.value = [];
    originalGroupMembers.value = [];
    pendingRemovalId.value = null;
    studentPage.value = 1;
}

async function loadStudents() {
    if (!courseId.value) return;
    studentsLoading.value = true;
    studentsError.value = false;
    try {
        const { data } = await axios.get(`/api/admin/course/${courseId.value}/group-students`);
        students.value = Array.isArray(data) ? data : [];
    } catch (e) {
        console.error("Ошибка загрузки учеников:", e);
        studentsError.value = true;
        students.value = [];
    } finally {
        studentsLoading.value = false;
        studentsLoaded.value = true;
    }
}

async function loadGroups() {
    if (!courseId.value) return;
    groupsLoading.value = true;
    try {
        const { data } = await axios.get(
            `/api/admin/course/${courseId.value}/groups`
        );
        groups.value = Array.isArray(data) ? data : (data?.groups ?? []);
    } catch (e) {
        console.error("Ошибка загрузки групп:", e);
        groups.value = [];
    } finally {
        groupsLoading.value = false;
    }
}

function openCreateGroup() {
    if (!courseId.value) {
        globalNotification.categoryMessage = "Сначала сохраните курс";
        globalNotification.type = "error";
        return;
    }
    if (studentsLoading.value) return;
    if (studentsError.value) {
        loadStudents();
        return;
    }
    if (noStudents.value) {
        globalNotification.categoryMessage =
            "Нет пользователей — группу создать нельзя";
        globalNotification.type = "error";
        return;
    }
    modalMode.value = "create";
    editingGroupId.value = null;
    resetForm();
    modalOpen.value = true;
    if (!studentsLoaded.value && !studentsLoading.value) loadStudents();
}

function closeModal() {
    if (createLoading.value) return;
    modalOpen.value = false;
    modalMode.value = "create";
    editingGroupId.value = null;
    resetForm();
}

async function openEditGroup(group) {
    if (!courseId.value || !group?.id || createLoading.value) return;
    resetForm();

    if (!studentsLoaded.value && !studentsLoading.value) {
        await loadStudents();
    }

    modalMode.value = "edit";
    editingGroupId.value = group.id;
    modalOpen.value = true;
    modalLoading.value = true;
    studentPage.value = 1;

    try {
        const { data } = await axios.get(
            `/api/admin/course/${courseId.value}/groups/${group.id}`
        );
        groupName.value = data?.name_group || group.name_group || "";
        const ids = Array.isArray(data?.students)
            ? data.students.map((s) => s.id)
            : [];
        selectedStudentIds.value = ids;
        originalStudentIds.value = [...ids];
        originalGroupMembers.value = Array.isArray(data?.students) ? data.students : [];
    } catch (e) {
        console.error("Ошибка загрузки группы:", e);
        globalNotification.categoryMessage = "Ошибка загрузки группы";
        globalNotification.type = "error";
        closeModal();
    } finally {
        modalLoading.value = false;
    }
}

async function deleteGroup(groupId, closeAfter = false) {
    if (!courseId.value || !groupId) return;
    if (!confirm("Удалить группу?")) return;

    createLoading.value = true;
    emit("saving", true);
    try {
        await axios.delete(
            `/api/admin/course/${courseId.value}/groups/${groupId}`
        );
        groups.value = groups.value.filter((g) => g.id !== groupId);

        globalNotification.categoryMessage = "Группа удалена";
        globalNotification.type = "success";

        if (closeAfter) {
            closeModal();
        }
    } catch (e) {
        console.error("Ошибка удаления группы:", e);
        globalNotification.categoryMessage = "Ошибка удаления группы";
        globalNotification.type = "error";
    } finally {
        createLoading.value = false;
        emit("saving", false);
    }
}

async function submitGroup() {
    if (!courseId.value || createLoading.value || modalLoading.value || studentsLoading.value || studentsError.value) return;

    const name = groupName.value.trim();
    if (!name) {
        globalNotification.categoryMessage = "Введите название группы";
        globalNotification.type = "error";
        return;
    }
    if (!selectedStudentIds.value.length && modalMode.value !== "edit") {
        globalNotification.categoryMessage = "Выберите учеников";
        globalNotification.type = "error";
        return;
    }

    createLoading.value = true;
    emit("saving", true);
    try {
        // Набор открытых тем здесь не трогаем — им управляет преподаватель
        // на странице курса, а сохранение группы его не должно сбрасывать.
        const payload = {
            name_group: name,
            student_ids: selectedStudentIds.value,
            ...(modalMode.value === "edit" ? { expected_student_ids: originalStudentIds.value } : {}),
        };

        if (modalMode.value === "edit" && editingGroupId.value) {
            const { data } = await axios.patch(
                `/api/admin/course/${courseId.value}/groups/${editingGroupId.value}`,
                payload
            );
            const updated = data?.group ?? data;
            if (updated) {
                const idx = groups.value.findIndex((g) => g.id === updated.id);
                if (idx !== -1) groups.value[idx] = updated;
            }
            globalNotification.categoryMessage = "Группа обновлена";
            globalNotification.type = "success";
        } else {
            const { data } = await axios.post(
                `/api/admin/course/${courseId.value}/groups`,
                payload
            );
            const group = data?.group ?? data;
            if (group) groups.value = [group, ...groups.value];
            globalNotification.categoryMessage = "Группа создана";
            globalNotification.type = "success";
        }

        createLoading.value = false;
        closeModal();
        await loadStudents();
    } catch (e) {
        console.error("Ошибка создания группы:", e);
        globalNotification.categoryMessage = Object.values(e.response?.data?.errors ?? {}).flat()[0]
            || e.response?.data?.message || "Не удалось сохранить группу";
        globalNotification.type = "error";
    } finally {
        createLoading.value = false;
        emit("saving", false);
    }
}

watch(courseId, (id) => {
    if (!id) {
        groups.value = [];
        students.value = [];
        studentsLoaded.value = false;
        return;
    }
    loadGroups();
    loadStudents();
});

watch(searchQuery, () => {
    studentPage.value = 1;
});

watch(filteredStudents, () => {
    if (studentPage.value > totalStudentPages.value) {
        studentPage.value = 1;
    }
});

onMounted(() => {
    if (courseId.value) {
        loadGroups();
        loadStudents();
    }
});
</script>

<template>
    <div>
        <div class="topic__top">
            <p class="block__title">Группы</p>
            <button
                type="button"
                class="users-btn-new"
                @click="openCreateGroup"
                :disabled="!courseId"
            >
                Сформировать группу
            </button>
        </div>

        <p v-if="!courseId" class="dialog__hint">
            Сначала сохраните курс, чтобы сформировать группы.
        </p>
        <p v-else-if="noStudents && !groups.length" class="dialog__hint">
            Нет пользователей — группы не сформированы.
        </p>

        <div v-if="modalOpen" class="dialog group-enrollment-overlay" @mousedown.self="closeModal">
            <div
                class="dialog__container_custom group-enrollment-dialog"
                @click.stop
            >
                <div class="dialog__inner" :class="{ 'is-saving': createLoading || modalLoading }">
                    <div class="dialog__header">
                        <p>
                            {{ modalMode === "edit" ? "Редактировать группу" : "Сформировать группу" }}
                        </p>
                        <div class="dialog__close" @click="closeModal">
                            <svg
                                width="13"
                                height="12"
                                viewBox="0 0 13 12"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M7.90994 6.00019L12.2099 1.71019C12.3982 1.52188 12.504 1.26649 12.504 1.00019C12.504 0.733884 12.3982 0.478489 12.2099 0.290185C12.0216 0.101882 11.7662 -0.00390625 11.4999 -0.00390625C11.2336 -0.00390625 10.9782 0.101882 10.7899 0.290185L6.49994 4.59019L2.20994 0.290185C2.02164 0.101882 1.76624 -0.00390625 1.49994 -0.00390625C1.23364 -0.00390625 0.978243 0.101882 0.789939 0.290185C0.601635 0.478489 0.495847 0.733884 0.495847 1.00019C0.495847 1.26649 0.601635 1.52188 0.789939 1.71019L5.08994 6.00019L0.789939 10.2902C0.696211 10.3831 0.621816 10.4937 0.571048 10.6156C0.520279 10.7375 0.494141 10.8682 0.494141 11.0002C0.494141 11.1322 0.520279 11.2629 0.571048 11.3848C0.621816 11.5066 0.696211 11.6172 0.789939 11.7102C0.882902 11.8039 0.993503 11.8783 1.11536 11.9291C1.23722 11.9798 1.36793 12.006 1.49994 12.006C1.63195 12.006 1.76266 11.9798 1.88452 11.9291C2.00638 11.8783 2.11698 11.8039 2.20994 11.7102L6.49994 7.41019L10.7899 11.7102C10.8829 11.8039 10.9935 11.8783 11.1154 11.9291C11.2372 11.9798 11.3679 12.006 11.4999 12.006C11.632 12.006 11.7627 11.9798 11.8845 11.9291C12.0064 11.8783 12.117 11.8039 12.2099 11.7102C12.3037 11.6172 12.3781 11.5066 12.4288 11.3848C12.4796 11.2629 12.5057 11.1322 12.5057 11.0002C12.5057 10.8682 12.4796 10.7375 12.4288 10.6156C12.3781 10.4937 12.3037 10.3831 12.2099 10.2902L7.90994 6.00019Z"
                                    fill="#999999"
                                />
                            </svg>
                        </div>
                    </div>

                    <div class="dialog__content">
                        <div class="form__admin">
                            <div class="dialog__component">
                                <p class="dialog__title">Название группы</p>
                                <input
                                    v-model="groupName"
                                    type="text"
                                    class="dialog__input"
                                    placeholder="Например, Группа 1"
                                    :disabled="modalLoading || createLoading"
                                />
                            </div>

                            <div class="dialog__component">
                                <p class="dialog__title">Ученики<span v-if="modalMode === 'edit' && !modalLoading">: {{ existingGroupMembers.length }}</span></p>

                                <div v-if="modalMode === 'edit'" class="enrollment-members">
                                    <p v-if="modalLoading" class="enrollment-account">Загрузка...</p>
                                    <div v-else-if="existingGroupMembers.length" class="enrollment-members__scroll" tabindex="0" aria-label="Участники группы">
                                        <table class="light-push-table light-push-table--s enrollment-members__table">
                                            <thead>
                                                <tr><th>Ученик</th><th>Действие</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="student in existingGroupMembers" :key="student.id">
                                                    <td>
                                                        <strong>{{ student.name }}</strong>
                                                        <span v-if="student.login" class="enrollment-account">{{ student.login }}</span>
                                                        <span v-if="student.email" class="enrollment-account">{{ student.email }}</span>
                                                    </td>
                                                    <td>
                                                        <div v-if="pendingRemovalId === student.id" class="enrollment-members__confirm" role="group" :aria-label="`Убрать из группы: ${student.name}?`">
                                                            <span>Убрать из группы?</span>
                                                            <div class="enrollment-members__actions">
                                                                <button type="button" class="enrollment-members__remove" @click="confirmRemoval(student.id)"
                                                                    :disabled="createLoading || modalLoading">Подтвердить</button>
                                                                <button type="button" class="enrollment-members__cancel" @click="pendingRemovalId = null"
                                                                    :disabled="createLoading || modalLoading">Отмена</button>
                                                            </div>
                                                        </div>
                                                        <button v-else type="button" class="enrollment-members__remove"
                                                            :aria-label="`Убрать из группы: ${student.name}`"
                                                            @click="pendingRemovalId = student.id" :disabled="createLoading || modalLoading">
                                                            Убрать
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p v-else class="enrollment-account">В группе пока нет участников</p>
                                </div>

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
                                        v-model="searchQuery"
                                        type="text"
                                        class="users-search__input search__input--xl"
                                        placeholder="Поиск по ФИО, логину или почте"
                                        :disabled="modalLoading || createLoading"
                                    />
                                    <button
                                        v-if="searchQuery"
                                        type="button"
                                        class="users-search__clear"
                                        @click="searchQuery = ''"
                                    >
                                        ×
                                    </button>
                                </div>

                                <p v-if="studentsError" role="alert" class="enrollment-error">
                                    Не удалось загрузить учеников.
                                    <button type="button" @click="loadStudents">Повторить</button>
                                </p>
                                <div class="enrollment-selection">
                                    <span>Выбрано: {{ selectedStudentIds.length }}</span>
                                </div>
                                <div class="enrollment-table-scroll" tabindex="0" aria-label="Список учеников">
                                    <table class="light-push-table light-push-table--s enrollment-table">
                                        <thead>
                                            <tr>
                                                <th class="enrollment-check" aria-label="Выбор ученика"></th>
                                                <th>Ученик</th>
                                                <th>Доступ к курсу</th>
                                                <th>Группа</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="studentsLoading"><td colspan="4">Загрузка...</td></tr>
                                            <tr v-else-if="noStudents"><td colspan="4">Нет учеников</td></tr>
                                            <tr v-else-if="!filteredStudents.length"><td colspan="4">Ученики не найдены</td></tr>
                                            <tr v-else v-for="student in paginatedStudents" :key="student.id">
                                                <td class="enrollment-check">
                                                    <input type="checkbox" :checked="isSelected(student.id)"
                                                        :aria-label="`Выбрать: ${student.name}`"
                                                        @change="toggleStudent(student.id)"
                                                        :disabled="modalLoading || createLoading || !canSelect(student)" />
                                                </td>
                                                <td>
                                                    <strong>{{ student.name }}</strong>
                                                    <span class="enrollment-account">{{ student.login }}</span>
                                                    <span class="enrollment-account">{{ student.email || "Почта не указана" }}</span>
                                                </td>
                                                <td><span class="enrollment-access" :class="{ 'is-open': student.has_access }">
                                                    {{ student.has_access ? "Открыт" : "Закрыт / не выдан" }}
                                                </span></td>
                                                <td>
                                                    {{ student.groups?.map((group) => group.name_group).join(", ") || "—" }}
                                                    <span v-if="!canSelect(student)" class="enrollment-account">Уже в другой группе курса</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-if="removedCount" class="enrollment-summary">
                                    Убрать из группы: {{ removedCount }}. Доступ к курсу и прогресс сохранятся.
                                </p>

                                <div
                                    class="pagination-users"
                                    v-if="totalStudentPages > 1"
                                    style="margin-top: 12px"
                                >
                                    <button
                                        :disabled="studentPage === 1"
                                        @click="studentPage--"
                                    >
                                        ‹ Назад
                                    </button>

                                    <button
                                        v-for="p in studentPageNumbers"
                                        :key="p"
                                        :class="{ active: studentPage === p }"
                                        @click="studentPage = p"
                                    >
                                        {{ p }}
                                    </button>

                                    <button
                                        :disabled="studentPage === totalStudentPages"
                                        @click="studentPage++"
                                    >
                                        Вперёд ›
                                    </button>
                                </div>
                            </div>

                            <div class="dialog__btns">
                                <button
                                    type="button"
                                    class="main__btn main__btn--white"
                                    @click="closeModal"
                                >
                                    Отмена
                                </button>
                                <button
                                    type="button"
                                    class="main__btn"
                                    @click="submitGroup"
                                    :disabled="
                                        createLoading || modalLoading || studentsLoading || studentsError ||
                                        (modalMode === 'create' && noStudents) ||
                                        !groupName.trim() ||
                                        (modalMode === 'create' && !selectedStudentIds.length)
                                    "
                                >
                                    {{ createLoading ? "Сохранение…" : (addedStudents.length ? (modalMode === "edit" ? "Добавить и выдать курс" : "Создать группу и выдать курс") : (modalMode === "edit" ? "Сохранить" : "Создать группу")) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <table class="light-push-table light-push-table--s topics-table group-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Название группы</th>
                    <th>Количество (учеников)</th>
                    <th>Открыто тем</th>
                    <th class="col-actions">Действие</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="groupsLoading">
                    <td colspan="5">Загрузка...</td>
                </tr>
                <tr v-else-if="!groups.length">
                    <td colspan="5">
                        {{ noStudents ? "Группы не сформированы: нет пользователей." : "Группы отсутствуют." }}
                    </td>
                </tr>
                <tr v-else v-for="(group, idx) in groups" :key="group.id">
                    <td>{{ idx + 1 }}</td>
                    <td>{{ group.name_group }}</td>
                    <td>{{ group.students_count ?? 0 }}</td>
                    <!-- Только для справки: набор тем задаёт преподаватель на странице курса -->
                    <td>{{ group.topic_ids?.length ?? 0 }}</td>
                    <td class="col-btn">
                        <div class="btn__edit">
                            <div class="tooltip-container">
                                <button
                                    aria-describedby="help-tooltip"
                                    class="btn__user--edit"
                                    @click="openEditGroup(group)"
                                >
                                    <img
                                        width="24"
                                        height="24"
                                        src="../../../../img/admin/edit.svg"
                                        alt=""
                                    />
                                </button>
                                <div role="tooltip" id="help-tooltip" class="tooltip">
                                    Редактировать группу
                                </div>
                            </div>
                            <div class="tooltip-container">
                                <button
                                    aria-describedby="help-tooltip"
                                    class="btn__user--edit"
                                    @click="deleteGroup(group.id)"
                                >
                                    <img width="24" height="24" src="../../../../img/admin/trash.png" alt="delete" />
                                </button>
                                <div role="tooltip" id="help-tooltip" class="tooltip">
                                    Удалить группу
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.group-enrollment-overlay { z-index: 1001 !important; padding: 24px 16px; }
.group-enrollment-dialog .dialog__inner { box-sizing: border-box; }
.group-enrollment-dialog .form__admin { gap: 20px; }
.group-enrollment-dialog .pagination-users { padding-bottom: 0; }
.group-enrollment-dialog .users-search__input { box-sizing: border-box; width: 100%; min-width: 0; max-width: 100% !important; padding-right: 28px; }
.group-enrollment-dialog { width: min(960px, calc(100vw - 32px)); max-width: 960px; }
.enrollment-table-scroll { max-width: 100%; overflow: auto; max-height: 42vh; margin-top: 16px; }
table.enrollment-table { width: 100%; min-width: 650px; table-layout: fixed; margin: 0; }
.enrollment-table th, .enrollment-table td { padding: 12px; text-align: left; overflow-wrap: anywhere; white-space: normal; max-width: none; }
.enrollment-table th:nth-child(2) { width: 35%; }
.enrollment-table th:nth-child(3) { width: 23%; }
.enrollment-table th:last-child, .enrollment-table td:last-child { width: auto; max-width: none; white-space: normal; }
.enrollment-table .enrollment-check { width: 44px; text-align: center; }
.enrollment-check input { appearance: auto; width: 18px; height: 18px; accent-color: #41328f; cursor: pointer; }
.enrollment-account { display: block; margin-top: 4px; font-size: 14px; color: #626277; }
.enrollment-access { display: inline-block; padding: 5px 9px; border-radius: 8px; background: #f0f0f3; white-space: nowrap; }
.enrollment-access.is-open { background: #eee9fa; color: #41328f; }
.enrollment-members { margin: 8px 0 12px; }
.enrollment-members__scroll { max-height: 220px; overflow: auto; }
table.enrollment-members__table { width: 100%; min-width: 460px; table-layout: fixed; margin: 0; }
.enrollment-members__table th, .enrollment-members__table td { padding: 12px; text-align: left; overflow-wrap: anywhere; white-space: normal; max-width: none; }
.enrollment-members__table th:last-child, .enrollment-members__table td:last-child { width: 210px; max-width: none; white-space: normal; }
.enrollment-members__actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
.enrollment-members__confirm { font-size: 14px; line-height: 1.4; }
.enrollment-members__remove, .enrollment-members__cancel { padding: 6px 10px; border: 1px solid currentColor; border-radius: 8px; background: transparent; font: inherit; cursor: pointer; }
.enrollment-members__remove { color: #b42318; }
.enrollment-members__cancel { color: #41328f; }
.enrollment-members__remove:disabled, .enrollment-members__cancel:disabled { opacity: .5; cursor: default; }
.enrollment-selection { display: flex; align-items: center; flex-wrap: wrap; gap: 16px; margin-top: 16px; }
.enrollment-summary { margin-top: 14px; font-size: 14px; line-height: 1.5; color: #505064; }
.enrollment-error { margin-top: 12px; color: #b42318; }
.group-enrollment-dialog .dialog__btns { flex-wrap: wrap; gap: 12px; }
.group-enrollment-dialog .main__btn { white-space: normal; }
</style>
