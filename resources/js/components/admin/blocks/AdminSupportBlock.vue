<template>
    <div class="user-block">
        <h1 class="page__title">Обращения</h1>

        <div class="status-tabs" role="tablist" aria-label="Фильтр задач">
            <button
                class="status-tabs__tab status-tabs__tab--active"
                type="button"
                role="tab"
                aria-selected="true"
            >
                <span class="status-tabs__label">Открытые</span>
                <span class="status-tabs__badge">124</span>
            </button>
            <div class="line"></div>
            <button
                class="status-tabs__tab"
                type="button"
                role="tab"
                aria-selected="false"
            >
                <span class="status-tabs__label">Выполненные</span>
                <span class="status-tabs__badge">124</span>
            </button>
        </div>

        <div class="users-toolbar">
            <div class="asdf">
                <div class="users-toolbar__left">
                    <label class="users-show">
                        Показать
                        <span class="users-show__select-wrap">
                            <select class="users-show__select">
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
                        пользователей
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
                            type="text"
                            class="users-search__input"
                            placeholder="Поиск..."
                        />
                        <button type="button" class="users-search__clear">
                            ×
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <table class="light-push-table">
                <thead>
                    <tr>
                        <th class="th--xl">ФИО</th>
                        <th class="th--xl">Тип обращения</th>
                        <th class="th--xl">Дата обращения</th>
                        <th class="th--xl size--xl">Дата ответа</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>ФИО</td>

                        <td>Ошибка</td>

                        <td>дд.мм.гггг 00:00</td>

                        <td class="size--xl">дд.мм.гггг 00:00</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- <div class="pagination-users" v-if="totalPagesUsers > 1">
            <button
                :disabled="currentPageUsers === 1"
                @click="currentPageUsers--"
            >
                ‹ Назад
            </button>

            <button
                v-for="p in totalPagesUsers"
                :key="p"
                :class="{ active: currentPageUsers === p }"
                @click="currentPageUsers = p"
            >
                {{ p }}
            </button>

            <button
                :disabled="currentPageUsers === totalPagesUsers"
                @click="currentPageUsers++"
            >
                Вперёд ›
            </button>
        </div> -->
    </div>
</template>

<script setup>
import { ref, computed, watch, reactive } from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";
import { useDateFormatters } from "../utils/useDateFormatters";
import EditRoleUser from "./../modal_admin/EditRoleUser.vue";

function rolePillClass(role) {
    const r = Number(role);
    return {
        "users-role-pill--student": r === 1,
        "users-role-pill--teacher": r === 2,
        "users-role-pill--admin": r === 3,
        "users-role-pill--parent": r === 4,
    };
}

const showEditRoleModal = ref(false);
const userToEditRole = ref(null);

function openEditRoleModal(user) {
    userToEditRole.value = user;
    showEditRoleModal.value = true;
}

function onRoleUpdated(updated) {
    const next = props.users.map((u) => (u.id === updated.id ? updated : u));
    setUsers(next);
}

const props = defineProps({
    users: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:users"]);

const { formatBirthday } = useDateFormatters();

function setUsers(next) {
    emit("update:users", next);
}

const selectedRole = ref("all");
const searchQuery = ref("");
const roleDropdownOpen = ref(false);

function resetRoleFilter() {
    selectedRole.value = "all";
    roleDropdownOpen.value = false;
}

function getRoleName(role) {
    switch (role) {
        case 4:
            return "Родитель";
        case 3:
            return "Админ";
        case 2:
            return "Преподаватель";
        case 1:
            return "Ученик";
        default:
            return "Неизвестно";
    }
}

const filteredUsers = computed(() => {
    const base =
        selectedRole.value === "all"
            ? props.users
            : props.users.filter((u) => String(u.role) === selectedRole.value);

    const q = (searchQuery.value || "").trim().toLowerCase();
    if (!q) return base;

    const qDigits = q.replace(/\D/g, "");

    return base.filter((u) => {
        const name = (u.name || "").toLowerCase();
        const email = (u.email || "").toLowerCase();
        const phone = u.phone || "";
        const phoneLc = phone.toLowerCase();
        const phoneDg = phone.replace(/\D/g, "");
        const country = (u.country || "").toLowerCase();
        const bRaw = u.birthday || "";
        const bFmt = (formatBirthday(u.birthday) || "").toLowerCase();

        return (
            name.includes(q) ||
            email.includes(q) ||
            country.includes(q) ||
            phoneLc.includes(q) ||
            (qDigits && phoneDg.includes(qDigits)) ||
            bRaw.includes(q) ||
            bFmt.includes(q)
        );
    });
});

const currentPageUsers = ref(1);
const pageSizeUsers = ref(10);

const totalPagesUsers = computed(() => {
    const size = pageSizeUsers.value || 1;
    return Math.max(1, Math.ceil(filteredUsers.value.length / size));
});

const paginatedUsers = computed(() => {
    const size = pageSizeUsers.value || 10;
    const start = (currentPageUsers.value - 1) * size;
    return filteredUsers.value.slice(start, start + size);
});

watch([selectedRole, searchQuery, pageSizeUsers], () => {
    currentPageUsers.value = 1;
});

/* ===== inline роль ===== */
const inlineRoleEdit = reactive({ id: null, role: null });

function startInlineRole(user) {
    inlineRoleEdit.id = user.id;
    inlineRoleEdit.role = Number(user.role);
}

function cancelInlineRole() {
    inlineRoleEdit.id = null;
    inlineRoleEdit.role = null;
}

async function saveInlineRole() {
    if (!inlineRoleEdit.id) return;
    try {
        const id = inlineRoleEdit.id;
        const resp = await axios.patch(`/api/users/${id}`, {
            role: inlineRoleEdit.role,
        });
        const updated = resp.data.user || resp.data;

        const next = props.users.map((u) => (u.id === id ? updated : u));
        setUsers(next);

        globalNotification.categoryMessage = "Роль обновлена";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка обновления роли";
        globalNotification.type = "error";
    } finally {
        cancelInlineRole();
    }
}

/* ===== модалка пользователя ===== */
const showUserEditModal = ref(false);
const editingUser = ref(null);

function openUserEditModal(user) {
    editingUser.value = { ...user };
    showUserEditModal.value = true;
}

function closeUserEditModal() {
    showUserEditModal.value = false;
    editingUser.value = null;
}

async function saveUserModal() {
    try {
        const { id, name, email, phone, birthday, country, role, position } =
            editingUser.value;
        const resp = await axios.patch(`/api/users/${id}`, {
            name,
            email,
            phone,
            birthday,
            country,
            role,
            position,
        });
        const updated = resp.data.user || resp.data;

        const next = props.users.map((u) => (u.id === id ? updated : u));
        setUsers(next);

        globalNotification.categoryMessage = "Пользователь обновлён";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка обновления пользователя";
        globalNotification.type = "error";
    } finally {
        closeUserEditModal();
    }
}

async function deleteUser(userId) {
    if (!confirm("Вы действительно хотите удалить пользователя?")) return;
    try {
        await axios.delete(`/api/users/${userId}`);
        setUsers(props.users.filter((u) => u.id !== userId));
        globalNotification.categoryMessage = "Пользователь успешно удалён";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при удалении пользователя";
        globalNotification.type = "error";
    }
}

watch([paginatedUsers, currentPageUsers], () => {
    refresh();
});
</script>

<style scoped>

.th--xl{
    width: 255px;
}

.size--xl{
    max-width: unset !important;
    width: unset !important;
}

/* Ученик */
.users-role-pill--student {
    background: #bde5b0;
    border: 1px solid transparent;
}

/* Родитель */
.users-role-pill--parent {
    background: #e5b0b0;
    border: 1px solid transparent;
}

/* Преподаватель */
.users-role-pill--teacher {
    background: #e5dfb0;
    border: 1px solid transparent;
}

/* Администратор */
.users-role-pill--admin {
    background: #ffffff;
    border: 1px solid #41328f;
}
</style>
