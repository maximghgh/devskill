<template>
    <div class="user-block">
        <h1 class="page__title">Пользователи</h1>

        <div class="users-toolbar">
            <div class="asdf">
                <div class="users-toolbar__left">
                    <label class="users-show">
                        Показать
                        <span class="users-show__select-wrap">
                            <select
                                v-model.number="pageSizeUsers"
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
                        пользователей
                    </label>

                    <div class="users-roles">
                        <button
                            type="button"
                            class="users-roles__btn"
                            @click="roleDropdownOpen = !roleDropdownOpen"
                        >
                            Роли
                            <span
                                class="users-roles__chevron"
                                :class="{
                                    'users-roles__chevron--open':
                                        roleDropdownOpen,
                                }"
                            ></span>
                        </button>

                        <div
                            v-if="roleDropdownOpen"
                            class="users-roles__dropdown"
                            @click.stop
                        >
                            <p class="users-roles__title">Фильтр роль</p>

                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="all"
                                    v-model="selectedRole"
                                />
                                <span>Все роли</span>
                            </label>
                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="1"
                                    v-model="selectedRole"
                                />
                                <span>Ученик</span>
                            </label>
                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="2"
                                    v-model="selectedRole"
                                />
                                <span>Преподаватель</span>
                            </label>
                            <label class="users-roles__option">
                                <input
                                    type="radio"
                                    value="3"
                                    v-model="selectedRole"
                                />
                                <span>Администратор</span>
                            </label>

                            <div class="users-roles__actions">
                                <button
                                    type="button"
                                    class="btn users-roles__apply"
                                    @click="roleDropdownOpen = false"
                                >
                                    Фильтр
                                </button>
                                <button
                                    type="button"
                                    class="btn users-roles__reset"
                                    @click="resetRoleFilter"
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
                                src="../../../img/admin/search.svg"
                                alt=""
                            />
                        </span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="users-search__input"
                            placeholder="Поиск..."
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
                </div>
            </div>
        </div>

        <div v-if="filteredUsers.length > 0">
            <table class="light-push-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ФИО</th>
                        <th>Дата регистрации</th>
                        <th>Телефон</th>
                        <th>Роль</th>
                        <th>Действие</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="userItem in paginatedUsers" :key="userItem.id">
                        <td>#{{ userItem.id }}</td>

                        <td class="avatar__user">
                            <img
                                :src="
                                    userItem.photo
                                        ? `/storage/${userItem.photo}`
                                        : 'https://devskills.foncode.ru/img/no_foto.jpg'
                                "
                                alt="Фото пользователя"
                                width="32"
                                height="32"
                                class="avatar__admin"
                            />
                            {{ userItem.name }}
                        </td>

                        <td>{{ formatBirthday(userItem.created_at) }}</td>
                        <td>{{ userItem.phone }}</td>

                        <td>
                            <!-- Inline-редактирование роли -->
                            <div
                                v-if="inlineRoleEdit.id === userItem.id"
                                style="
                                    display: flex;
                                    gap: 8px;
                                    align-items: center;
                                "
                            >
                                <select
                                    v-model.number="inlineRoleEdit.role"
                                    class="select"
                                    style="
                                        padding: 5px;
                                        border: 1px solid #ccc;
                                        border-radius: 4px;
                                    "
                                >
                                    <option :value="3">Админ</option>
                                    <option :value="2">Преподаватель</option>
                                    <option :value="1">Ученик</option>
                                    <option :value="4">Родитель</option>
                                </select>
                                <button
                                    class="btn__user--edit"
                                    @click="saveInlineRole"
                                >
                                    OK
                                </button>
                                <button
                                    class="btn__user--delete"
                                    @click="cancelInlineRole"
                                >
                                    ×
                                </button>
                            </div>

                            <div
                                v-else
                                class="users-role-pill"
                                :class="rolePillClass(userItem.role)"
                                @click="startInlineRole(userItem)"
                            >
                                {{ getRoleName(userItem.role) }}
                            </div>
                        </td>

                        <td class="hadle">
                            <div class="tooltip-container">
                                <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openEditRoleModal(userItem)">
                                    <img
                                    width="24"
                                    height="24"
                                    src="../../../../img/admin/edit.svg"
                                    alt=""
                                />
                                </button>
                                <div role="tooltip" id="help-tooltip" class="tooltip">
                                    Изменить роль
                                </div>
                            </div>
                            <div class="tooltip-container">
                                <button
                                    aria-describedby="help-tooltip"
                                    class="btn__user--delete"
                                    @click="deleteUser(userItem.id)"
                                >
                                    <img
                                        width="24"
                                        height="24"
                                        src="../../../../img/admin/trash.png"
                                        alt=""
                                    />
                                </button>
                                <div role="tooltip" id="help-tooltip" class="tooltip">
                                    Удалить пользователя
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else>Нет пользователей</div>

        <div class="pagination-users" v-if="totalPagesUsers > 1">
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
        </div>

        <EditRoleUser
            v-model="showEditRoleModal"
            :user="userToEditRole"
            @saved="onRoleUpdated"
        />
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

/* Ученик */
.users-role-pill--student {
  background: #BDE5B0;
  border: 1px solid transparent;
}

/* Родитель */
.users-role-pill--parent {
  background: #E5B0B0;
  border: 1px solid transparent;
}

/* Преподаватель */
.users-role-pill--teacher {
  background: #E5DFB0;
  border: 1px solid transparent;
}

/* Администратор */
.users-role-pill--admin {
  background: #FFFFFF;
  border: 1px solid #41328F;
}
</style>




