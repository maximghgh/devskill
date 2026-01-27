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

const hasStudents = computed(() => students.value.length > 0);
const noStudents = computed(
    () => studentsLoaded.value && !studentsLoading.value && !hasStudents.value
);

const filteredStudents = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return students.value;
    return students.value.filter((s) => {
        const name = String(s?.name || "").toLowerCase();
        const email = String(s?.email || "").toLowerCase();
        return name.includes(q) || email.includes(q);
    });
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

function resetForm() {
    groupName.value = "";
    searchQuery.value = "";
    selectedStudentIds.value = [];
}

async function loadStudents() {
    if (!courseId.value) return;
    studentsLoading.value = true;
    try {
        const { data } = await axios.get(`/api/students/${courseId.value}`);
        students.value = Array.isArray(data) ? data : [];
    } catch (e) {
        console.error("Ошибка загрузки учеников:", e);
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
    modalOpen.value = false;
    modalMode.value = "create";
    editingGroupId.value = null;
    resetForm();
}

async function openEditGroup(group) {
    if (!courseId.value || !group?.id) return;

    if (!studentsLoaded.value && !studentsLoading.value) {
        await loadStudents();
    }

    modalMode.value = "edit";
    editingGroupId.value = group.id;
    modalOpen.value = true;
    modalLoading.value = true;

    try {
        const { data } = await axios.get(
            `/api/admin/course/${courseId.value}/groups/${group.id}`
        );
        groupName.value = data?.name_group || group.name_group || "";
        const ids = Array.isArray(data?.students)
            ? data.students.map((s) => s.id)
            : [];
        selectedStudentIds.value = ids;
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
    if (!courseId.value) return;

    const name = groupName.value.trim();
    if (!name) {
        globalNotification.categoryMessage = "Введите название группы";
        globalNotification.type = "error";
        return;
    }
    if (!selectedStudentIds.value.length) {
        globalNotification.categoryMessage = "Выберите учеников";
        globalNotification.type = "error";
        return;
    }

    createLoading.value = true;
    emit("saving", true);
    try {
        const payload = {
            name_group: name,
            student_ids: selectedStudentIds.value,
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

        closeModal();
    } catch (e) {
        console.error("Ошибка создания группы:", e);
        globalNotification.categoryMessage = "Ошибка создания группы";
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

        <div v-if="modalOpen" class="dialog" @click.self="closeModal" style="z-index: 1">
            <div
                class="dialog__container_custom dialog__container_custom--s"
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
                                    :disabled="modalLoading"
                                />
                            </div>

                            <div class="dialog__component">
                                <p class="dialog__title">Ученики</p>

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
                                        class="users-search__input"
                                        placeholder="Поиск ученика..."
                                        :disabled="modalLoading"
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

                                <table class="light-push-table light-push-table--s topics-table group-table">
                                    <thead>
                                        <tr>
                                            <th class="col-check"></th>
                                            <th class="col-name">ФИО</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="studentsLoading">
                                            <td colspan="2">Загрузка...</td>
                                        </tr>
                                        <tr v-else-if="noStudents">
                                            <td colspan="2">Нет пользователей</td>
                                        </tr>
                                        <tr v-else-if="!filteredStudents.length">
                                            <td colspan="2">Ученики не найдены</td>
                                        </tr>
                                        <tr
                                            v-else
                                            v-for="student in filteredStudents"
                                            :key="student.id"
                                        >
                                            <td class="col-check">
                                                <input
                                                    type="checkbox"
                                                    :checked="isSelected(student.id)"
                                                    @change="toggleStudent(student.id)"
                                                    :disabled="modalLoading"
                                                />
                                            </td>
                                            <td class="col-name">{{ student.name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="dialog__btns">
                                <button
                                    v-if="modalMode === 'edit'"
                                    type="button"
                                    class="btn-delete"
                                    @click="deleteGroup(editingGroupId, true)"
                                    :disabled="createLoading || modalLoading"
                                >
                                    Удалить группу
                                </button>
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
                                        createLoading ||
                                        noStudents ||
                                        !groupName.trim() ||
                                        !selectedStudentIds.length
                                    "
                                >
                                    {{ modalMode === "edit" ? "Сохранить" : "Создать группу" }}
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
                    <th class="col-actions">Действие</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="groupsLoading">
                    <td colspan="4">Загрузка...</td>
                </tr>
                <tr v-else-if="!groups.length">
                    <td colspan="4">
                        {{ noStudents ? "Группы не сформированы: нет пользователей." : "Группы отсутствуют." }}
                    </td>
                </tr>
                <tr v-else v-for="(group, idx) in groups" :key="group.id">
                    <td>{{ idx + 1 }}</td>
                    <td>{{ group.name_group }}</td>
                    <td>{{ group.students_count ?? 0 }}</td>
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
