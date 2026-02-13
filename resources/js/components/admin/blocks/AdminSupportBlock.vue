<template>
    <div class="user-block">
        <h1 class="page__title">Обращения</h1>

        <div class="status-tabs" role="tablist" aria-label="Фильтр задач">
            <button
                class="status-tabs__tab"
                :class="{ 'status-tabs__tab--active': activeStatus === 'открыта' }"
                type="button"
                role="tab"
                :aria-selected="activeStatus === 'открыта'"
                @click="setActiveStatus('открыта')"
            >
                <span class="status-tabs__label">Открытые</span>
                <span class="status-tabs__badge">{{ openCount }}</span>
            </button>
            <div class="line"></div>
            <button
                class="status-tabs__tab"
                :class="{ 'status-tabs__tab--active': activeStatus === 'выполнена' }"
                type="button"
                role="tab"
                :aria-selected="activeStatus === 'выполнена'"
                @click="setActiveStatus('выполнена')"
            >
                <span class="status-tabs__label">Выполненные</span>
                <span class="status-tabs__badge">{{ completedCount }}</span>
            </button>
        </div>

        <div class="users-toolbar">
            <div class="asdf">
                <div class="users-toolbar__left">
                    <label class="users-show">
                        Показать
                        <span class="users-show__select-wrap">
                            <select
                                v-model.number="pageSize"
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
                        обращений
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

        <div v-if="paginatedRequests.length">
            <table class="light-push-table">
                <thead>
                    <tr>
                        <th class="th--xl">ФИО</th>
                        <th class="th--xl">Сообщение</th>
                        <th class="th--xl">Дата обращения</th>
                        <th class="th--xl">Статус</th>
                        <th class="th--xl size--xl">Дата ответа</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="item in paginatedRequests" :key="item.id">
                        <td>{{ item.user_name || "—" }}</td>
                        <td>{{ messagePreview(item.message) }}</td>
                        <td>{{ formatDateTime(item.created_at) }}</td>
                        <td>
                            <select
                                class="users-show__select support-status-select"
                                :value="item.status"
                                :disabled="updatingId === item.id"
                                @change="handleStatusChange(item, $event)"
                            >
                                <option value="открыта">открыта</option>
                                <option value="выполнена">выполнена</option>
                            </select>
                        </td>
                        <td class="size--xl">{{ supportAnsweredAt(item) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else>Нет обращений</div>

        <div class="pagination-users" v-if="totalPages > 1">
            <button
                :disabled="currentPage === 1"
                @click="currentPage--"
            >
                ‹ Назад
            </button>

            <button
                v-for="p in totalPages"
                :key="p"
                :class="{ active: currentPage === p }"
                @click="currentPage = p"
            >
                {{ p }}
            </button>

            <button
                :disabled="currentPage === totalPages"
                @click="currentPage++"
            >
                Вперёд ›
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";
import { useDateFormatters } from "../utils/useDateFormatters";

const props = defineProps({
    requests: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:requests"]);

const { formatDateTime } = useDateFormatters();

function setRequests(next) {
    emit("update:requests", next);
}

const activeStatus = ref("открыта");
const searchQuery = ref("");

const openCount = computed(
    () => props.requests.filter((r) => r.status === "открыта").length
);
const completedCount = computed(
    () => props.requests.filter((r) => r.status === "выполнена").length
);

function setActiveStatus(status) {
    activeStatus.value = status;
}

function messagePreview(message) {
    const text = (message || "").toString();
    return text.length > 140 ? `${text.slice(0, 140)}...` : text;
}

const filteredRequests = computed(() => {
    const base = props.requests.filter(
        (r) => r.status === activeStatus.value
    );

    const q = (searchQuery.value || "").trim().toLowerCase();
    if (!q) return base;

    const qDigits = q.replace(/\D/g, "");

    return base.filter((r) => {
        const name = (r.user_name || "").toLowerCase();
        const msg = (r.message || "").toLowerCase();
        const createdRaw = r.created_at || "";
        const createdFmt = (formatDateTime(r.created_at) || "").toLowerCase();

        return (
            name.includes(q) ||
            msg.includes(q) ||
            createdRaw.includes(q) ||
            createdFmt.includes(q) ||
            (qDigits && createdRaw.replace(/\D/g, "").includes(qDigits))
        );
    });
});

const currentPage = ref(1);
const pageSize = ref(10);

const totalPages = computed(() => {
    const size = pageSize.value || 1;
    return Math.max(1, Math.ceil(filteredRequests.value.length / size));
});

const paginatedRequests = computed(() => {
    const size = pageSize.value || 10;
    const start = (currentPage.value - 1) * size;
    return filteredRequests.value.slice(start, start + size);
});

watch([activeStatus, searchQuery, pageSize], () => {
    currentPage.value = 1;
});

watch(totalPages, (tp) => {
    if (currentPage.value > tp) currentPage.value = tp;
});

function supportAnsweredAt(item) {
    const val = item?.completed_at;
    if (!val) return "—";
    return formatDateTime(val);
}

const updatingId = ref(null);

async function handleStatusChange(item, event) {
    const nextStatus = event.target.value;
    const prevStatus = item.status;
    if (nextStatus === prevStatus) return;

    const updatedLocal = props.requests.map((r) =>
        r.id === item.id
            ? {
                  ...r,
                  status: nextStatus,
                  completed_at:
                      nextStatus === "выполнена" ? new Date().toISOString() : null,
              }
            : r
    );
    setRequests(updatedLocal);

    try {
        updatingId.value = item.id;
        await axios.patch(`/api/support-requests/${item.id}/status`, {
            status: nextStatus,
        });
        globalNotification.categoryMessage = "Статус обновлён";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        const reverted = props.requests.map((r) =>
            r.id === item.id ? { ...r, status: prevStatus } : r
        );
        setRequests(reverted);
        globalNotification.categoryMessage = "Ошибка обновления статуса";
        globalNotification.type = "error";
    } finally {
        updatingId.value = null;
    }
}
</script>

<style scoped>

.th--xl{
    width: 255px;
}

.size--xl{
    max-width: unset !important;
    width: unset !important;
}

/* Контрастный текст на зебре */
:deep(.light-push-table tbody tr:nth-child(odd)) {
    background-color: #F1F0FA;
}

/* Статус на фиолетовом фоне */
:deep(.light-push-table tbody tr:nth-child(odd) .support-status-select) {
    background: #f8f8f8;
    color: #41328f;
    border: 1px solid #e0dbf7;
}
:deep(.light-push-table tbody tr:nth-child(even) .support-status-select) {
    background: #f1f0fa;
    color: #41328f;
    border: 1px solid #e0dbf7;
}
</style>
