<template>
    <div class="user-block">
        <h1 class="page__title">Частые вопросы</h1>

        <table
            v-if="paginatedFaqs.length"
            class="light-push-table light-push-table--s"
        >
            <thead>
                <tr>
                    <th>№</th>
                    <th>Вопрос</th>
                    <th>Ответ</th>
                    <th>Изменить</th>
                    <th>Удалить</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(f, i) in paginatedFaqs" :key="f.id">
                    <td>{{ i + 1 + (currentPageFaq - 1) * pageSizeFaq }}</td>
                    <td>{{ f.question }}</td>
                    <td>{{ f.answer }}</td>
                    <td>
                        <button
                            class="btn__user--edit"
                            @click="startEditingFaq(f)"
                        >
                            Редактировать
                        </button>
                    </td>
                    <td>
                        <button
                            class="btn__user--delete"
                            @click="deleteFaq(f.id)"
                        >
                            Удалить
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <p v-else>Нет частых вопросов</p>

        <div class="pagination-users" v-if="totalPagesFaq > 1">
            <button :disabled="currentPageFaq === 1" @click="currentPageFaq--">
                ‹ Назад
            </button>

            <button
                v-for="p in totalPagesFaq"
                :key="p"
                :class="{ active: currentPageFaq === p }"
                @click="currentPageFaq = p"
            >
                {{ p }}
            </button>

            <button
                :disabled="currentPageFaq === totalPagesFaq"
                @click="currentPageFaq++"
            >
                Вперёд ›
            </button>
        </div>

        <!-- ===== МОДАЛКА РЕДАКТИРОВАНИЯ ===== -->
        <div
            v-if="editingFaq.id"
            class="modal-overlay"
            @click.self="cancelEditingFaq"
        >
            <div class="modal-content modal-flex">
                <button class="close-button" @click="cancelEditingFaq">
                    ×
                </button>

                <h4>Редактирование #{{ editingFaq.id }}</h4>

                <div class="form-group">
                    <label class="form-label">Вопрос</label>
                    <input
                        v-model="editingFaq.question"
                        class="form-input form-input--xl"
                        placeholder="Вопрос"
                    />
                </div>

                <div class="form-group">
                    <label class="form-label">Ответ на вопрос</label>
                    <textarea
                        v-model="editingFaq.answer"
                        class="form-input form-input--xl textarea"
                        placeholder="Ответ"
                    />
                </div>

                <div
                    class="button__edit__faq"
                    style="margin-top: 1em; text-align: right"
                >
                    <button @click="saveEditingFaq" class="form-button--small">
                        Сохранить
                    </button>
                    <button
                        @click="cancelEditingFaq"
                        class="form-button--small"
                    >
                        Отмена
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== СОЗДАНИЕ FAQ ===== -->
        <h2 class="h2__margin">Создание частых вопросов</h2>

        <form @submit.prevent="submitFaq" class="course-form">
            <div class="form-group">
                <label class="form-label">Вопрос</label>
                <input
                    v-model="faq.question"
                    type="text"
                    class="form-input"
                    placeholder="Введите вопрос"
                />
            </div>

            <div class="form-group">
                <label class="form-label">Ответ на вопрос</label>
                <textarea
                    v-model="faq.answer"
                    class="form-input"
                    placeholder="Введите ответ"
                />
            </div>

            <button type="submit" class="form-button--small">
                Создать ответ
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, watch, reactive } from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";

const props = defineProps({
    faqs: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:faqs"]);

function setFaqs(next) {
    emit("update:faqs", next);
}

async function reloadFaqs() {
    const { data } = await axios.get("/api/faqs");
    setFaqs(data);
}

/* ===== пагинация ===== */
const currentPageFaq = ref(1);
const pageSizeFaq = ref(3);

const totalPagesFaq = computed(() =>
    Math.max(1, Math.ceil(props.faqs.length / pageSizeFaq.value))
);

const paginatedFaqs = computed(() => {
    const start = (currentPageFaq.value - 1) * pageSizeFaq.value;
    return props.faqs.slice(start, start + pageSizeFaq.value);
});

watch(
    () => props.faqs,
    () => (currentPageFaq.value = 1)
);

/* ===== создание ===== */
const faq = ref({ question: "", answer: "" });

async function submitFaq() {
    try {
        await axios.post("/api/faqs", faq.value);
        globalNotification.categoryMessage = "FAQ успешно создан";
        globalNotification.type = "success";

        faq.value.question = "";
        faq.value.answer = "";

        await reloadFaqs();
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage =
            "Заполните все поля, для добавление FAQ";
        globalNotification.type = "error";
    }
}

/* ===== редактирование ===== */
const editingFaq = reactive({ id: null, question: "", answer: "" });

function startEditingFaq(f) {
    editingFaq.id = f.id;
    editingFaq.question = f.question;
    editingFaq.answer = f.answer;
}

function cancelEditingFaq() {
    editingFaq.id = null;
    editingFaq.question = "";
    editingFaq.answer = "";
}

async function saveEditingFaq() {
    try {
        await axios.put(`/api/faqs/${editingFaq.id}`, {
            question: editingFaq.question,
            answer: editingFaq.answer,
        });

        globalNotification.categoryMessage = "Вопрос изменен";
        globalNotification.type = "success";

        cancelEditingFaq();
        await reloadFaqs();
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка обновления FAQ";
        globalNotification.type = "error";
    }
}

/* ===== удаление ===== */
async function deleteFaq(id) {
    if (!confirm("Удалить этот вопрос?")) return;
    try {
        await axios.delete(`/api/faqs/${id}`);
        await reloadFaqs();
        globalNotification.categoryMessage = "FAQ удалён";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка удаления FAQ";
        globalNotification.type = "error";
    }
}
</script>
