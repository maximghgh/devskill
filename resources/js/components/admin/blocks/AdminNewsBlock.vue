<template>
    <div class="user-block">
        <h1 class="page__title">Новости</h1>

        <table v-if="paginatedNews.length" class="light-push-table">
            <thead>
                <tr>
                    <th>ID</th>
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
                    <td>
                        {{ index + 1 + (currentPageNews - 1) * pageSizeNews }}
                    </td>
                    <td>{{ newsItem.title }}</td>
                    <td>{{ formatBirthday(newsItem.created_at) }}</td>
                    <td>
                        <a href="#" @click.prevent="openComments(newsItem)"
                            >Просмотреть комментарии</a
                        >
                    </td>
                    <td>
                        <button
                            class="btn__user--delete"
                            @click="deleteNews(newsItem.id)"
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
                            @click="openNewsEditModal(newsItem)"
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

        <div v-else>Нет новостей</div>

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

        <!-- ===== МОДАЛКА РЕДАКТИРОВАНИЯ НОВОСТИ ===== -->
        <div v-if="showNewsEditModal" class="modal-overlay">
            <div class="modal-content">
                <h2>Редактирование новости</h2>
                <button class="close-button" @click="closeNewsEditModal">
                    ×
                </button>

                <form @submit.prevent="submitNewsEdit" class="course-form">
                    <div class="form-group">
                        <label class="form-label">Заголовок новости</label>
                        <input
                            v-model="editingNews.title"
                            type="text"
                            class="form-input"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Краткое описание</label>
                        <textarea
                            v-model="editingNews.content"
                            class="form-textarea form-textarea--m"
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Изображение новости</label>
                        <input
                            type="file"
                            accept="image/*"
                            class="form-input"
                            @change="onNewsImageChangeEdit"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Текущее изображение:</label>
                        <div v-if="currentNewsFileUrl" class="image_db">
                            <img
                                v-if="currentNewsFileIsImage"
                                :src="currentNewsFileUrl"
                                alt="Изображение новости"
                                class="image"
                            />
                            <a
                                :href="currentNewsFileUrl"
                                :download="currentNewsFileName"
                                target="_blank"
                                rel="noopener"
                            >
                                {{ currentNewsFileName }}
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Текст новости</label>
                        <div
                            id="editorjs-news-edit"
                            class="editor-container"
                        ></div>
                    </div>

                    <div class="modal-buttons">
                        <button type="submit" class="form-button">
                            Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===== МОДАЛКА КОММЕНТАРИЕВ ===== -->
        <div
            v-if="showCommentsModal"
            class="modal-overlay"
            @click.self="closeComments"
        >
            <div class="modal-content" style="max-width: 800px">
                <button class="close-button" @click="closeComments">×</button>
                <h2>Комментарии к: «{{ currentCommentsNewsTitle }}»</h2>

                <div v-if="commentsLoading" style="padding: 1rem">
                    Загрузка…
                </div>

                <div v-else>
                    <div v-if="!commentsFlat.length" style="padding: 0.5rem 0">
                        Комментариев пока нет
                    </div>

                    <div v-else class="comments-list" style="margin: 1rem 0">
                        <div
                            v-for="c in commentsFlat"
                            :key="c.id"
                            class="comment-item"
                            :style="{
                                marginLeft: c._level * 16 + 'px',
                                borderLeft: c._level
                                    ? '2px solid #eee'
                                    : 'none',
                                paddingLeft: '8px',
                                marginBottom: '12px',
                            }"
                        >
                            <div
                                class="comment-head"
                                style="font-size: 14px; color: #666"
                            >
                                <strong style="color: #222">{{
                                    c.user_name || "Пользователь #" + c.user_id
                                }}</strong>
                                <span style="margin-left: 8px">•</span>
                                <span style="margin-left: 8px">{{
                                    formatDateTime(c.created_at)
                                }}</span>
                            </div>

                            <div
                                class="comment-body"
                                style="
                                    white-space: pre-wrap;
                                    margin: 0.25rem 0 0.5rem;
                                "
                            >
                                {{ c.body }}
                            </div>

                            <div
                                class="comment-actions"
                                style="display: flex; gap: 8px"
                            >
                                <button
                                    class="btn__user--edit"
                                    @click="toggleReply(c.id)"
                                >
                                    Ответить
                                </button>
                                <button
                                    class="icon-btn icon-btn--danger"
                                    :disabled="commentDeleting[c.id]"
                                    :title="
                                        commentDeleting[c.id]
                                            ? 'Удаляем…'
                                            : 'Удалить комментарий'
                                    "
                                    @click="deleteComment(c.id)"
                                    aria-label="Удалить комментарий"
                                >
                                    Удалить комментарий
                                </button>
                            </div>

                            <div
                                v-if="replyOpen[c.id]"
                                class="reply-box"
                                style="margin-top: 0.5rem"
                            >
                                <textarea
                                    v-model="replyBodies[c.id]"
                                    class="form-textarea"
                                    placeholder="Ваш ответ..."
                                />
                                <div
                                    style="
                                        text-align: right;
                                        margin-top: 0.5rem;
                                    "
                                >
                                    <button
                                        class="form-button--small"
                                        @click="sendReply(c.id)"
                                    >
                                        Отправить
                                    </button>
                                    <button
                                        class="form-button--small"
                                        @click="toggleReply(c.id)"
                                    >
                                        Отмена
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr />
                    <h3 style="margin: 0.75rem 0">Новый комментарий</h3>
                    <textarea
                        v-model="newCommentBody"
                        class="form-textarea"
                        placeholder="Напишите комментарий..."
                    />
                    <div style="text-align: right; margin-top: 0.5rem">
                        <button class="form-button" @click="sendComment">
                            Отправить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== СОЗДАТЬ НОВОСТЬ ===== -->
        <h2 class="h2__margin">Опубликовать новость</h2>

        <form @submit.prevent="submitNews" class="course-form">
            <div class="form-group">
                <label class="form-label">Заголовок новости</label>
                <input
                    v-model="newNews.title"
                    type="text"
                    class="form-input"
                    placeholder="Введите заголовок новости"
                />
            </div>

            <div class="form-group">
                <label class="form-label">Краткое описание</label>
                <textarea
                    v-model="newNews.content"
                    class="form-textarea form-textarea--m"
                    placeholder="Введите краткое описание"
                ></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Изображение новости</label>
                <input
                    type="file"
                    accept="image/*"
                    class="form-input"
                    @change="onNewsImageChange"
                />
            </div>

            <div class="form-group">
                <label class="form-label">Текст новости</label>
                <div id="editorjs-news" class="editor-container"></div>
            </div>

            <button type="submit" class="form-button">
                Опубликовать новость
            </button>
        </form>
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
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";
import { globalNotification } from "../../../globalNotification";
import { useDateFormatters } from "../utils/useDateFormatters";

const props = defineProps({
    newsItems: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:newsItems"]);

function setNewsItems(next) {
    emit("update:newsItems", next);
}

const { formatBirthday, formatDateTime } = useDateFormatters();

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
let editorNews = null;

const newNews = ref({
    title: "",
    content: "",
    newsImage: null,
    editorData: {},
});

function onNewsImageChange(e) {
    newNews.value.newsImage = e.target.files?.[0] || null;
}

onMounted(() => {
    if (editorNews) return;
    editorNews = new EditorJS({
        holder: "editorjs-news",
        placeholder: "Введите текст новости...",
        tools: {
            header: { class: Header, inlineToolbar: ["link"] },
            list: { class: List, inlineToolbar: true },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: "/api/uploadFile",
                        byUrl: "/api/fetchUrl",
                    },
                },
            },
        },
    });
});

onBeforeUnmount(() => {
    if (editorNews) {
        editorNews.destroy();
        editorNews = null;
    }
    if (editorNewsEdit) {
        editorNewsEdit.destroy();
        editorNewsEdit = null;
    }
});

/* ===== создание новости ===== */
async function submitNews() {
    try {
        const editorData = await editorNews.save();
        newNews.value.editorData = editorData;

        const fd = new FormData();
        fd.append("title", newNews.value.title);
        fd.append("content", newNews.value.content);
        fd.append("editor_data", JSON.stringify(newNews.value.editorData));
        if (newNews.value.newsImage)
            fd.append("image", newNews.value.newsImage);

        const { data } = await axios.post("/api/news", fd, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        globalNotification.categoryMessage = "Новость успешно создана";
        globalNotification.type = "success";

        // обновим список (на случай любого формата ответа)
        const res = await axios.get("/api/news");
        setNewsItems(res.data);

        newNews.value = {
            title: "",
            content: "",
            newsImage: null,
            editorData: {},
        };
        editorNews.clear();
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при создании новости";
        globalNotification.type = "error";
    }
}

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

/* ===== редактирование новости ===== */
const showNewsEditModal = ref(false);
const editingNewsId = ref(null);
const editingNews = ref({
    id: null,
    title: "",
    content: "",
    newsImage: null,
    editorData: {},
});

let editorNewsEdit = null;

async function openNewsEditModal(newsItem) {
    editingNewsId.value = newsItem.id;

    let parsedEditorData = {};
    if (newsItem.editor_data) {
        if (typeof newsItem.editor_data === "string") {
            try {
                parsedEditorData = JSON.parse(newsItem.editor_data);
            } catch {
                parsedEditorData = {};
            }
        } else {
            parsedEditorData = newsItem.editor_data;
        }
    }

    editingNews.value = {
        ...newsItem,
        id: newsItem.id,
        title: newsItem.title || "",
        content: newsItem.content || "",
        newsImage: null,
        editorData: parsedEditorData,
    };

    showNewsEditModal.value = true;
    await nextTick();

    if (editorNewsEdit) {
        editorNewsEdit.destroy();
        editorNewsEdit = null;
    }

    editorNewsEdit = new EditorJS({
        holder: "editorjs-news-edit",
        data: editingNews.value.editorData,
        placeholder: "Редактируйте контент новости...",
        tools: {
            header: { class: Header, inlineToolbar: ["link"] },
            list: { class: List, inlineToolbar: true },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: "/api/uploadFile",
                        byUrl: "/api/fetchUrl",
                    },
                },
            },
        },
        onChange: async () => {
            const saved = await editorNewsEdit.save();
            editingNews.value.editorData = saved;
        },
    });
}

function closeNewsEditModal() {
    showNewsEditModal.value = false;
    editingNewsId.value = null;
    editingNews.value = {
        id: null,
        title: "",
        content: "",
        newsImage: null,
        editorData: {},
    };
    if (editorNewsEdit) {
        editorNewsEdit.destroy();
        editorNewsEdit = null;
    }
}

function onNewsImageChangeEdit(e) {
    editingNews.value.newsImage = e.target.files?.[0] || null;
}

async function submitNewsEdit() {
    try {
        const fd = new FormData();
        fd.append("title", editingNews.value.title);
        fd.append("content", editingNews.value.content);
        fd.append("editor_data", JSON.stringify(editingNews.value.editorData));

        if (editingNews.value.newsImage)
            fd.append("image", editingNews.value.newsImage);
        fd.append("_method", "PATCH");

        const { data } = await axios.post(
            `/api/news/${editingNews.value.id}`,
            fd
        );

        const updated = data.news ?? data;
        setNewsItems(
            props.newsItems.map((n) => (n.id === updated.id ? updated : n))
        );

        globalNotification.categoryMessage = "Новость обновлена";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка обновления новости";
        globalNotification.type = "error";
    } finally {
        closeNewsEditModal();
    }
}

/* ===== текущий файл/картинка ===== */
const PUBLIC_STORAGE_PREFIX = "/storage/";

const currentNewsFileUrl = computed(() => {
    const raw =
        editingNews.value?.image ||
        editingNews.value?.image_path ||
        editingNews.value?.image_url ||
        "";

    if (!raw) return "";
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.startsWith("/")) return raw;

    return PUBLIC_STORAGE_PREFIX + raw.replace(/^storage\//, "");
});

const currentNewsFileName = computed(() => {
    const u = currentNewsFileUrl.value || "";
    const name = u.split("/").pop() || "";
    try {
        return decodeURIComponent(name);
    } catch {
        return name;
    }
});

const currentNewsFileIsImage = computed(() =>
    /\.(png|jpe?g|gif|webp|svg)$/i.test(currentNewsFileUrl.value || "")
);

/* ===== комментарии ===== */
const showCommentsModal = ref(false);
const commentsLoading = ref(false);
const commentsNewsId = ref(null);
const currentCommentsNewsTitle = ref("");
const commentsRaw = ref([]);

const replyOpen = reactive({});
const replyBodies = reactive({});
const newCommentBody = ref("");

function buildTree(list) {
    const map = {};
    list.forEach((c) => {
        c.children = [];
        map[c.id] = c;
    });
    const roots = [];
    list.forEach((c) => {
        const pid = c.parent_id;
        if (pid && map[pid]) map[pid].children.push(c);
        else roots.push(c);
    });
    return roots;
}

const commentsTree = computed(() => buildTree(commentsRaw.value));
const commentsFlat = computed(() => {
    const out = [];
    const walk = (nodes, level = 0) => {
        nodes.forEach((n) => {
            out.push({ ...n, _level: level });
            if (n.children?.length) walk(n.children, level + 1);
        });
    };
    walk(commentsTree.value);
    return out;
});

async function openComments(newsItem) {
    commentsNewsId.value = newsItem.id;
    currentCommentsNewsTitle.value = newsItem.title || "";
    await loadComments(newsItem.id);
    showCommentsModal.value = true;
}

function closeComments() {
    showCommentsModal.value = false;
    commentsRaw.value = [];
    commentsNewsId.value = null;
    currentCommentsNewsTitle.value = "";
    Object.keys(replyOpen).forEach((k) => delete replyOpen[k]);
    Object.keys(replyBodies).forEach((k) => delete replyBodies[k]);
    newCommentBody.value = "";
}

async function loadComments(newsId) {
    commentsLoading.value = true;
    try {
        const { data } = await axios.get(`/api/news/${newsId}/comments`);
        commentsRaw.value = Array.isArray(data) ? data : data.comments || [];
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка загрузки комментариев";
        globalNotification.type = "error";
    } finally {
        commentsLoading.value = false;
    }
}

function toggleReply(id) {
    replyOpen[id] = !replyOpen[id];
}

async function sendComment() {
    const body = (newCommentBody.value || "").trim();
    if (!body) return;

    const current = JSON.parse(localStorage.getItem("user") || "{}");

    try {
        await axios.post(`/api/news/${commentsNewsId.value}/comments`, {
            body,
            user_id: current.id,
            user_name: current.name,
        });

        newCommentBody.value = "";
        await loadComments(commentsNewsId.value);
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка отправки комментария";
        globalNotification.type = "error";
    }
}

async function sendReply(parentId) {
    const body = (replyBodies[parentId] || "").trim();
    if (!body) return;

    const current = JSON.parse(localStorage.getItem("user") || "{}");

    try {
        await axios.post(`/api/news/${commentsNewsId.value}/comments`, {
            body,
            parent_id: parentId,
            user_id: current.id,
            user_name: current.name,
        });

        replyBodies[parentId] = "";
        replyOpen[parentId] = false;
        await loadComments(commentsNewsId.value);
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка отправки ответа";
        globalNotification.type = "error";
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
