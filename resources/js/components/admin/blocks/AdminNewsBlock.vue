<template>
    <div class="user-block">
        <h1 class="page__title">Новости</h1>
        <div v-if="viewMode === 'news'">
        <div>
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
                            новостей
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
                                placeholder="Поиск..."
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

                    <button type="button" class="users-btn-new" @click="openCreateNews">
                        <span class="users-btn-desc">+</span> Добавить новость
                    </button>
                </div>
            </div>

            <table v-if="paginatedNews.length" class="light-push-table">
                <thead>
                    <tr>
                        <th style="text-align: center">ID</th>
                        <th>Заголовок</th>
                        <th>Дата</th>
                        <th>Комментарии</th>
                        <th>Действие</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(newsItem, index) in paginatedNews"
                        :key="newsItem.id"
                    >
                        <td style="text-align: center">
                            #{{ index + 1 + (currentPageNews - 1) * pageSizeNews }}
                        </td>
                        <td class="avatar__user">
                            <img
                                :src="
                                            newsItem.image
                                                ? `${newsItem.image}`
                                                : 'https://devskills.foncode.ru/img/no_foto.jpg'
                                        "
                                alt=""
                                width="25"
                                height="25"
                                class="avatar__admin"
                            />
                            {{ newsItem.title || "Неизвестно" }}
                        </td>
                        <td>{{ formatBirthday(newsItem.created_at) }}</td>
                        <td class="avatar__user">
                            <span>
                                {{ commentCountsLoading[newsItem.id] ? "…" : (pureCommentCounts[newsItem.id] ?? 0) }}
                            </span>
                            <img
                                title="Перейти к комментариям"
                                width="24"
                                height="24"
                                src="../../../../img/admin/eye.svg"
                                alt=""
                                style="cursor: pointer"
                                @click="openComments(newsItem)"
                            />
                        </td>
                        <td>

                            <div class="tooltip-container">
                                <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openEditNews(newsItem)">
                                    <img width="24" height="24" src="../../../../img/admin/edit.svg" alt="" />
                                </button>
                                <div role="tooltip" id="help-tooltip" class="tooltip">
                                    Редактировать новость
                                </div>
                            </div>
                            <div class="tooltip-container">
                                <button aria-describedby="help-tooltip" class="btn__user--edit" @click="deleteNews(newsItem.id)">
                                    <img
                                        width="24"
                                        height="24"
                                        src="../../../../img/admin/trash.png"
                                        alt=""
                                    />
                                </button>
                                <div role="tooltip" id="help-tooltip" class="tooltip">
                                    Удалить новость
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-else>Нет новостей</div>

            <NewsDialog
                v-model="showNewsDialog"
                :news="dialogNews"
                @saved="setNewsItems"
            />

            <div class="pagination-users" v-if="totalPagesNews > 1">
                <button
                    :disabled="currentPageNews === 1"
                    @click="currentPageNews--"
                >
                    ‹ Назад
                </button>
                <button
                    v-for="p in totalPagesNews"
                    :key="p"
                    :class="{ active: currentPageNews === p }"
                    @click="currentPageNews = p"
                >
                    {{ p }}
                </button>
                <button
                    :disabled="currentPageNews === totalPagesNews"
                    @click="currentPageNews++"
                >
                    Вперёд ›
                </button>
            </div>
        </div>
        </div>
        <div v-else>
            <div class="breadcrumbs">
                <a href="#" @click.prevent="backToNews" class="breadcrumbs__old">Новости</a>
                <img src="../../../../img/admin/line.svg" alt="" />
                <span class="breadcrumbs__new">{{ currentCommentsNewsTitle }}</span>
            </div>

            <div class="users-toolbar">
                <div class="asdf">
                    <div class="users-toolbar__left">
                        <label class="users-show">
                            Показать
                            <span class="users-show__select-wrap">
                            <select v-model.number="pageSizeComments" class="users-show__select">
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </span>
                            комментариев
                        </label>
                    </div>

                    <div class="users-toolbar__search">
                        <div class="users-search">
                        <span class="users-search__icon">
                            <img width="13" height="13" src="../../../../img/admin/search.png" alt="" />
                        </span>
                            <input
                                v-model="searchCommentsQuery"
                                type="text"
                                class="users-search__input"
                                placeholder="Поиск..."
                            />
                            <button
                                v-if="searchCommentsQuery"
                                type="button"
                                class="users-search__clear"
                                @click="searchCommentsQuery = ''"
                            >
                                ×
                            </button>
                        </div>
                    </div>

                    <button type="button" class="users-btn-new" @click="openAddCommentDialog">
                        <span class="users-btn-desc">+</span> Добавить комментарий
                    </button>
                </div>
            </div>

            <table v-if="!commentsLoading && paginatedComments.length" class="light-push-table">
                <thead>
                <tr>
                    <th>Комментарий</th>
                    <th>Дата и время</th>
                    <th>Автор</th>
                    <th>Действие</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="c in paginatedComments" :key="c.id">
                    <td>{{ c.body }}</td>
                    <td>{{ formatDateTime(c.created_at) }}</td>

                    <td class="avatar__user">
                        {{ c.user_name || ("Пользователь #" + c.user_id) }}
                        <img
                            src="../../../../img/admin/eye.svg"
                            alt=""
                        />
                    </td>

                    <td>
                        <div class="tooltip-container">
                            <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openReplyCommentDialog(c)">
                                <img width="24" height="24" src="../../../../img/admin/letter.svg" alt="" />
                            </button>
                            <div role="tooltip" id="help-tooltip" class="tooltip">
                                Ответить пользователю
                            </div>
                        </div>
                        <div class="tooltip-container">
                            <button aria-describedby="help-tooltip" class="btn__user--edit" @click="deleteComment(c.id)">
                                <img width="24" height="24" src="../../../../img/admin/trash.png" alt="" />
                            </button>
                            <div role="tooltip" id="help-tooltip" class="tooltip">
                                Удалить комментарий
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

            <div v-else-if="commentsLoading">Загрузка…</div>
            <div v-else>Комментариев нет</div>

            <div class="pagination-users" v-if="totalPagesComments > 1">
                <button :disabled="currentPageComments === 1" @click="currentPageComments--">‹ Назад</button>
                <button
                    v-for="p in totalPagesComments"
                    :key="p"
                    :class="{ active: currentPageComments === p }"
                    @click="currentPageComments = p"
                >
                    {{ p }}
                </button>
                <button
                    :disabled="currentPageComments === totalPagesComments"
                    @click="currentPageComments++"
                >
                    Вперёд ›
                </button>
            </div>
        </div>
        <AddCommentDialog
            v-model="showAddCommentDialog"
            :news-id="commentsNewsId"
            @saved="onAddCommentSaved"
        />
        <ReplyCommentDialog
            v-model="showReplyCommentDialog"
            :news-id="commentsNewsId"
            :parent-id="replyParentId"
            :comment-text="replyCommentText"
            @saved="onReplyCommentSaved"
        />
    </div>
</template>

<script setup>
import {
    ref,
    computed,
    watch,
    reactive,
    nextTick,
    onMounted,
    onBeforeUnmount,
} from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";
import { useDateFormatters } from "../utils/useDateFormatters";
import NewsDialog from "../modal_admin/NewsDialog.vue";
import AddCommentDialog from "../modal_admin/AddCommentDialog.vue";
import ReplyCommentDialog from "../modal_admin/ReplyCommentDialog.vue";

const showNewsDialog = ref(false);
const dialogNews = ref(null);

function openCreateNews() {
    dialogNews.value = null;
    showNewsDialog.value = true;
}

function openEditNews(newsItem) {
    dialogNews.value = newsItem;
    showNewsDialog.value = true;
}

const viewMode = ref("news");

const pageSizeComments = ref(10);
const currentPageComments = ref(1);
const searchCommentsQuery = ref("");

const pureComments = computed(() =>
    (commentsRaw.value || []).filter((c) => c.parent_id === null)
);

const filteredComments = computed(() => {
    const q = (searchCommentsQuery.value || "").trim().toLowerCase();
    if (!q) return pureComments.value;

    return pureComments.value.filter((c) => {
        const body = (c.body || "").toLowerCase();
        const user = (c.user_name || "").toLowerCase();
        return body.includes(q) || user.includes(q);
    });
});

const totalPagesComments = computed(() =>
    Math.max(1, Math.ceil(filteredComments.value.length / pageSizeComments.value))
);

const paginatedComments = computed(() => {
    const start = (currentPageComments.value - 1) * pageSizeComments.value;
    return filteredComments.value.slice(start, start + pageSizeComments.value);
});

watch([pageSizeComments, searchCommentsQuery], () => {
    currentPageComments.value = 1;
});

const showAddCommentDialog = ref(false);

function openAddCommentDialog() {
    showAddCommentDialog.value = true;
}

async function onAddCommentSaved() {
    await loadComments(commentsNewsId.value);
}

const showReplyCommentDialog = ref(false);
const replyParentId = ref(null);
const replyCommentText = ref("");

function openReplyCommentDialog(c) {
    replyParentId.value = c.id;
    replyCommentText.value = c.body || "";
    showReplyCommentDialog.value = true;
}

async function onReplyCommentSaved() {
    await loadComments(commentsNewsId.value);
}

const props = defineProps({
    newsItems: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:newsItems"]);

function setNewsItems(next) {
    emit("update:newsItems", next);
}

const { formatBirthday, formatDateTime } = useDateFormatters();

const pageSizeCourses = ref(10);

const pureCommentCounts = reactive({});

function countPureComments(list) {
    return (list || []).filter((c) => c.parent_id === null).length;
}

const commentCountsLoading = reactive({}); // { [newsId]: true }

async function ensureCount(newsId) {
    if (pureCommentCounts[newsId] !== undefined || commentCountsLoading[newsId]) return;

    commentCountsLoading[newsId] = true;
    try {
        const { data } = await axios.get(`/api/news/${newsId}/comments`);
        const list = Array.isArray(data) ? data : data.comments || [];
        pureCommentCounts[newsId] = countPureComments(list);
    } catch (e) {
        console.error(e);
        pureCommentCounts[newsId] = 0; // чтобы не было "—"
    } finally {
        delete commentCountsLoading[newsId];
    }
}

async function preloadAllCounts(list, limit = 5) {
    const ids = (list || []).map(n => n.id);
    let i = 0;

    const workers = Array.from({ length: Math.min(limit, ids.length) }, async () => {
        while (i < ids.length) {
            const id = ids[i++];
            await ensureCount(id);
        }
    });

    await Promise.all(workers);
}

watch(
    () => props.newsItems,
    (list) => preloadAllCounts(list, 5),
    { immediate: true }
);

/* ===== пагинация ===== */
const currentPageNews = ref(1);
const pageSizeNews = ref(5);

const totalPagesNews = computed(() =>
    Math.max(1, Math.ceil(props.newsItems.length / pageSizeNews.value))
);

const paginatedNews = computed(() => {
    const start = (currentPageNews.value - 1) * pageSizeNews.value;
    return props.newsItems.slice(start, start + pageSizeNews.value);
});

watch(
    () => props.newsItems,
    () => (currentPageNews.value = 1)
);

/* ===== editor create ===== */

const newNews = ref({
    title: "",
    content: "",
    newsImage: null,
    editorData: {},
});

function onNewsImageChange(e) {
    newNews.value.newsImage = e.target.files?.[0] || null;
}


/* ===== создание новости ===== */

/* ===== удаление новости ===== */
async function deleteNews(id) {
    if (!confirm("Вы действительно хотите удалить новость?")) return;
    try {
        await axios.delete(`/api/news/${id}`);
        setNewsItems(props.newsItems.filter((n) => n.id !== id));
        globalNotification.categoryMessage = "Новость успешно удалена";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при удалении новости";
        globalNotification.type = "error";
    }
}

const commentsLoading = ref(false);
const commentsNewsId = ref(null);
const currentCommentsNewsTitle = ref("");
const commentsRaw = ref([]);

async function openComments(newsItem) {
    commentsNewsId.value = newsItem.id;
    currentCommentsNewsTitle.value = newsItem.title || "";

    viewMode.value = "comments";
    currentPageComments.value = 1;
    searchCommentsQuery.value = "";

    await loadComments(newsItem.id);
}

function backToNews() {
    viewMode.value = "news";
    closeComments();
}

function closeComments() {
    commentsRaw.value = [];
    commentsNewsId.value = null;
    currentCommentsNewsTitle.value = "";
}

async function loadComments(newsId) {
    commentsLoading.value = true;
    try {
        const { data } = await axios.get(`/api/news/${newsId}/comments`);
        const list = Array.isArray(data) ? data : data.comments || [];
        commentsRaw.value = list;

        pureCommentCounts[newsId] = countPureComments(list);
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка загрузки комментариев";
        globalNotification.type = "error";
    } finally {
        commentsLoading.value = false;
    }
}

const commentDeleting = reactive({});

async function deleteComment(id) {
    if (!confirm("Удалить этот комментарий и все его ответы?")) return;
    try {
        commentDeleting[id] = true;
        await axios.delete(`/api/comments/${id}`);
        await loadComments(commentsNewsId.value);

        globalNotification.categoryMessage = "Комментарий удалён";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка удаления комментария";
        globalNotification.type = "error";
    } finally {
        delete commentDeleting[id];
    }
}
</script>

