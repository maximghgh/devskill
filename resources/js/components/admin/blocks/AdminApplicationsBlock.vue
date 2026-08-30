<template>
    <div class="user-block">
        <h1 class="page__title">Заявки</h1>

        <div class="status-tabs" role="tablist" aria-label="Фильтр заявок">
            <template v-for="(tab, index) in statusTabs" :key="tab.value">
                <div v-if="index" class="line"></div>
                <button
                    class="status-tabs__tab"
                    :class="{ 'status-tabs__tab--active': activeStatus === tab.value }"
                    type="button"
                    role="tab"
                    :aria-selected="activeStatus === tab.value"
                    @click="activeStatus = tab.value"
                >
                    <span class="status-tabs__label">{{ tab.label }}</span>
                    <span class="status-tabs__badge">{{ countByStatus(tab.value) }}</span>
                </button>
            </template>
        </div>

        <div class="users-toolbar">
            <div class="asdf">
                <div class="users-toolbar__left">
                    <label class="users-show">
                        Показать
                        <span class="users-show__select-wrap">
                            <select v-model.number="pageSize" class="users-show__select">
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                            <img class="select__icon" src="../../../../img/admin/select.svg" alt="" />
                        </span>
                        заявок
                    </label>
                </div>

                <div class="users-toolbar__search">
                    <div class="users-search">
                        <span class="users-search__icon">
                            <img width="13" height="13" src="../../../../img/admin/search.png" alt="" />
                        </span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="users-search__input"
                            placeholder="Поиск по ФИО, телефону, почте, курсу, статусу..."
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

        <div v-if="paginated.length" class="applications__table-wrap">
            <table class="light-push-table">
                <thead>
                    <tr>
                        <th class="th--xl">Курс</th>
                        <th class="th--xl">ФИО</th>
                        <th class="th--xl">Телефон</th>
                        <th class="th--xl">Почта</th>
                        <th class="th--xl">Дата</th>
                        <th class="th--xl">Пользователь</th>
                        <th class="th--xl size--xl">Статус</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="item in paginated" :key="item.id">
                        <td>{{ courseTitle(item) }}</td>
                        <td>{{ item.full_name }}</td>
                        <td>{{ item.phone }}</td>
                        <td>{{ item.email }}</td>
                        <td>{{ formatDateTime(item.created_at) }}</td>
                        <td>
                            <button
                                type="button"
                                class="applications__control"
                                :class="{ 'applications__control--empty': !item.user_id }"
                                :disabled="savingId === item.id"
                                @click="openUserPicker(item, $event)"
                            >
                                <span class="applications__control-text">
                                    {{ userLabel(item) }}
                                </span>
                                <span class="applications__control-caret"></span>
                            </button>
                        </td>
                        <td class="size--xl">
                            <div class="applications__control applications__control--select">
                                <select
                                    class="applications__status-select"
                                    :value="item.status"
                                    :disabled="savingId === item.id"
                                    @change="changeStatus(item, $event)"
                                >
                                    <option
                                        v-for="s in statusOptions"
                                        :key="s.value"
                                        :value="s.value"
                                    >
                                        {{ s.label }}
                                    </option>
                                </select>
                                <span class="applications__control-caret"></span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="applications__empty">
            {{ searchQuery ? "Ничего не найдено" : "Заявок пока нет" }}
        </div>

        <div class="pagination-users" v-if="totalPages > 1">
            <button :disabled="currentPage === 1" @click="currentPage--">‹ Назад</button>
            <button
                v-for="p in totalPages"
                :key="p"
                :class="{ active: currentPage === p }"
                @click="currentPage = p"
            >
                {{ p }}
            </button>
            <button :disabled="currentPage === totalPages" @click="currentPage++">
                Вперёд ›
            </button>
        </div>

        <!-- Выбор пользователя с поиском: таблица скроллится,
             поэтому панель позиционируем фиксированно поверх страницы -->
        <teleport to="body">
            <div
                v-if="picker.open"
                class="applications-picker__backdrop"
                @mousedown.self="closeUserPicker"
            >
                <div
                    class="applications-picker"
                    :style="{ top: picker.top + 'px', left: picker.left + 'px' }"
                >
                    <input
                        ref="pickerInput"
                        v-model="picker.query"
                        type="text"
                        class="applications-picker__search"
                        placeholder="Поиск по имени, логину, почте..."
                    />
                    <ul class="applications-picker__list">
                        <li>
                            <button
                                type="button"
                                class="applications-picker__item"
                                @click="pickUser(null)"
                            >
                                Не привязан
                            </button>
                        </li>
                        <li v-for="u in pickerResults" :key="u.id">
                            <button
                                type="button"
                                class="applications-picker__item"
                                :class="{
                                    'applications-picker__item--active':
                                        picker.item?.user_id === u.id,
                                }"
                                @click="pickUser(u.id)"
                            >
                                <span class="applications-picker__name">
                                    {{ u.name || u.login }}
                                </span>
                                <span class="applications-picker__meta">
                                    {{ u.login }}<template v-if="u.email"> · {{ u.email }}</template>
                                </span>
                            </button>
                        </li>
                        <li v-if="!pickerResults.length" class="applications-picker__empty">
                            Никого не нашли
                        </li>
                    </ul>
                </div>
            </div>
        </teleport>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";
import { useDateFormatters } from "../utils/useDateFormatters";

const props = defineProps({
    applications: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:applications"]);

const { formatDateTime } = useDateFormatters();

const statusOptions = [
    { value: "awaiting_signing", label: "Ожидает подписания" },
    { value: "awaiting_payment", label: "Договор подписан, ожидает оплаты" },
    { value: "issued", label: "Курс выдан" },
    { value: "closed", label: "Курс закрыт" },
];

const statusTabs = [{ value: "all", label: "Все" }, ...statusOptions];

const activeStatus = ref("all");
const searchQuery = ref("");
const savingId = ref(null);

function setApplications(next) {
    emit("update:applications", next);
}

function courseTitle(item) {
    return item.course?.card_title || item.course?.course_name || "—";
}

function countByStatus(status) {
    if (status === "all") return props.applications.length;
    return props.applications.filter((a) => a.status === status).length;
}

const byStatus = computed(() =>
    activeStatus.value === "all"
        ? props.applications
        : props.applications.filter((a) => a.status === activeStatus.value)
);

// Поиск идёт по всем видимым полям строки, включая курс, пользователя и статус.
const filtered = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return byStatus.value;

    return byStatus.value.filter((a) => {
        const haystack = [
            a.full_name,
            a.phone,
            a.email,
            a.status_label,
            a.admin_comment,
            courseTitle(a),
            a.course?.course_name,
            a.user?.name,
            a.user?.login,
            a.user?.email,
            formatDateTime(a.created_at),
        ];

        return haystack.some((v) => String(v ?? "").toLowerCase().includes(q));
    });
});

const pageSize = ref(10);
const currentPage = ref(1);
const totalPages = computed(() =>
    Math.max(1, Math.ceil(filtered.value.length / pageSize.value))
);
const paginated = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return filtered.value.slice(start, start + pageSize.value);
});

watch([searchQuery, activeStatus, pageSize], () => {
    currentPage.value = 1;
});
watch(totalPages, (tp) => {
    if (currentPage.value > tp) currentPage.value = tp;
});

async function patchApplication(item, payload, revert) {
    savingId.value = item.id;
    try {
        const { data } = await axios.patch(
            `/api/course-applications/${item.id}`,
            payload
        );
        const updated = data?.application;
        if (updated?.id) {
            setApplications(
                props.applications.map((a) => (a.id === updated.id ? updated : a))
            );
        }
        globalNotification.categoryMessage = "Заявка обновлена";
        globalNotification.type = "success";
    } catch (e) {
        revert?.();
        const message =
            e?.response?.data?.message || "Не удалось обновить заявку";
        globalNotification.categoryMessage = message;
        globalNotification.type = "error";
    } finally {
        savingId.value = null;
    }
}

function changeStatus(item, event) {
    const next = event.target.value;
    if (next === item.status) return;
    // select не привязан к модели, поэтому при ошибке возвращаем прежнее значение
    patchApplication(item, { status: next }, () => {
        event.target.value = item.status;
    });
}

/* ===== Привязка пользователя: список с поиском ===== */
const picker = ref({ open: false, item: null, query: "", top: 0, left: 0 });
const pickerInput = ref(null);

function userLabel(item) {
    if (!item.user_id) return "Не привязан";
    const u = item.user || props.users.find((x) => x.id === item.user_id);
    return u?.name || u?.login || `ID ${item.user_id}`;
}

function openUserPicker(item, event) {
    const rect = event.currentTarget.getBoundingClientRect();
    // панель не должна вылезать за нижний край окна
    const height = 320;
    const top =
        rect.bottom + height > window.innerHeight
            ? Math.max(8, rect.top - height - 6)
            : rect.bottom + 6;

    picker.value = {
        open: true,
        item,
        query: "",
        top,
        left: Math.min(rect.left, window.innerWidth - 340),
    };

    nextTick(() => pickerInput.value?.focus());
}

function closeUserPicker() {
    picker.value = { ...picker.value, open: false, item: null, query: "" };
}

const pickerResults = computed(() => {
    const q = picker.value.query.trim().toLowerCase();
    const list = q
        ? props.users.filter((u) =>
              [u.name, u.login, u.email].some((v) =>
                  String(v ?? "").toLowerCase().includes(q)
              )
          )
        : props.users;

    // длинный список не рендерим целиком — поиск сузит выборку
    return list.slice(0, 50);
});

function pickUser(userId) {
    const item = picker.value.item;
    closeUserPicker();
    if (!item || userId === (item.user_id ?? null)) return;
    patchApplication(item, { user_id: userId });
}
</script>

<style scoped>
.applications__empty {
    padding: 24px 0;
    color: #777777;
}
/* Семь колонок не влезают в блок: таблица держит свою ширину
   и скроллится по горизонтали внутри страницы. */
.applications__table-wrap {
    max-width: 100%;
    overflow-x: auto;
    padding-bottom: 6px;
}
.applications__table-wrap table {
    min-width: 1470px;
}
/* самый длинный статус — «Договор подписан, ожидает оплаты»:
   239px текста плюс отступы и стрелка */
.applications__table-wrap .size--xl {
    min-width: 330px;
}
.applications__table-wrap::-webkit-scrollbar {
    height: 10px;
}
.applications__table-wrap::-webkit-scrollbar-thumb {
    border-radius: 5px;
    background: #c9c5e6;
}
.applications__table-wrap::-webkit-scrollbar-track {
    border-radius: 5px;
    background: #f1f0fa;
}

/* Статус и пользователь редактируются прямо в строке —
   поэтому оформлены как кнопки, а не как текст. */
.applications__control {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    width: 100%;
    min-width: 150px;
    min-height: 38px;
    padding: 8px 12px;
    border: 1px solid #c9c5e6;
    border-radius: 10px;
    background: #ffffff;
    color: #2b2b3a;
    font: inherit;
    font-size: 14px;
    text-align: left;
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.applications__control:hover:not(:disabled) {
    border-color: #6352c1;
    background: #f7f6fd;
}
.applications__control:disabled {
    opacity: 0.6;
    cursor: default;
}
.applications__control--empty {
    color: #8a8a99;
}
.applications__control-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.applications__control-caret {
    flex: 0 0 auto;
    width: 8px;
    height: 8px;
    border-right: 2px solid #6352c1;
    border-bottom: 2px solid #6352c1;
    transform: translateY(-2px) rotate(45deg);
}

/* Селект статуса рисуем внутри такой же рамки со своей стрелкой */
.applications__control--select {
    position: relative;
    padding: 0;
}
.applications__control--select .applications__control-caret {
    position: absolute;
    right: 12px;
    pointer-events: none;
}
.applications__status-select {
    width: 100%;
    padding: 8px 32px 8px 12px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    color: inherit;
    font: inherit;
    font-size: 14px;
    text-overflow: ellipsis;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
}
.applications__status-select:focus {
    outline: none;
}

/* ===== Панель выбора пользователя ===== */
.applications-picker__backdrop {
    position: fixed;
    inset: 0;
    z-index: 1000;
}
.applications-picker {
    position: fixed;
    width: 330px;
    max-height: 320px;
    display: flex;
    flex-direction: column;
    padding: 12px;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 12px 32px rgba(43, 43, 58, 0.18);
}
.applications-picker__search {
    box-sizing: border-box;
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #c9c5e6;
    border-radius: 10px;
    font: inherit;
    font-size: 14px;
    outline: none;
}
.applications-picker__search:focus {
    border-color: #6352c1;
}
.applications-picker__list {
    margin: 10px 0 0;
    padding: 0;
    list-style: none;
    overflow-y: auto;
}
.applications-picker__item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    width: 100%;
    padding: 8px 10px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    font: inherit;
    font-size: 14px;
    color: #2b2b3a;
    text-align: left;
    cursor: pointer;
}
.applications-picker__item:hover {
    background: #f1f0fa;
}
.applications-picker__item--active {
    background: #eae7f8;
}
.applications-picker__meta {
    font-size: 12px;
    color: #8a8a99;
}
.applications-picker__empty {
    padding: 10px;
    font-size: 14px;
    color: #8a8a99;
}
</style>
