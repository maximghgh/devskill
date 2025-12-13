<template>
  <div class="user-block">
    <h1 class="page__title">Частые вопросы</h1>

    <!-- Toolbar -->
    <div class="users-toolbar">
      <div class="asdf">
        <div class="users-toolbar__left">
          <label class="users-show">
            Показать
            <span class="users-show__select-wrap">
              <select v-model.number="pageSizeFaq" class="users-show__select">
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
            записей
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

        <button type="button" class="users-btn-new" @click="openCreateFaq">
          <span class="users-btn-desc">+</span> Добавить вопрос
        </button>
      </div>
    </div>

    <!-- Table -->
    <table v-if="paginatedFaqs.length" class="light-push-table light-push-table--s">
      <thead>
        <tr>
          <th>№</th>
          <th>Вопрос</th>
          <th>Ответ</th>
          <th>Действия</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(f, i) in paginatedFaqs" :key="f.id">
          <td>{{ i + 1 + (currentPageFaq - 1) * pageSizeFaq }}</td>
          <td>{{ f.question }}</td>
          <td>{{ f.answer }}</td>
          <td class="hadle">
              <div class="tooltip-container">
                  <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openEditFaq(f)">
                      <img
                          width="24"
                          height="24"
                          src="../../../../img/admin/edit.svg"
                          alt=""
                      />
                  </button>
                  <div role="tooltip" id="help-tooltip" class="tooltip">
                      Редактировать вопрос
                  </div>
              </div>
              <div class="tooltip-container">
                  <button aria-describedby="help-tooltip" class="btn__user--edit" @click="deleteFaq(f.id)">
                      <img
                      width="24"
                      height="24"
                      src="../../../../img/admin/trash.png"
                      alt=""
                  />
                  </button>
                  <div role="tooltip" id="help-tooltip" class="tooltip">
                      Удалить вопрос
                  </div>
              </div>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else>Нет частых вопросов</p>

    <!-- Pagination -->
    <div class="pagination-users" v-if="totalPagesFaq > 1">
      <button :disabled="currentPageFaq === 1" @click="currentPageFaq--">‹ Назад</button>

      <button
        v-for="p in totalPagesFaq"
        :key="p"
        :class="{ active: currentPageFaq === p }"
        @click="currentPageFaq = p"
      >
        {{ p }}
      </button>

      <button :disabled="currentPageFaq === totalPagesFaq" @click="currentPageFaq++">
        Вперёд ›
      </button>
    </div>

    <!-- Dialogs -->
    <CreateFaqDialog v-model="showCreateFaq" @created="onFaqCreated" />

    <EditFaqDialog v-model="showEditFaq" :faq="selectedFaq" @saved="onFaqSaved" />
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";

import EditFaqDialog from "../modal_admin/EditFaqDialog.vue";      // путь проверь
import CreateFaqDialog from "../modal_admin/CreateFaqDialog.vue";  // новый файл ниже

const props = defineProps({
  faqs: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:faqs"]);

function setFaqs(next) {
  emit("update:faqs", next);
}

async function reloadFaqs() {
  try {
    const { data } = await axios.get("/api/faqs");
    setFaqs(Array.isArray(data) ? data : data?.data ?? []);
  } catch (e) {
    console.error(e);
  }
}

/* ================= Search ================= */
const searchQuery = ref("");

const normalizedQuery = computed(() => searchQuery.value.trim().toLowerCase());

const filteredFaqs = computed(() => {
  const q = normalizedQuery.value;
  const arr = props.faqs ?? [];
  if (!q) return arr;

  return arr.filter((x) => {
    const question = (x.question ?? "").toLowerCase();
    const answer = (x.answer ?? "").toLowerCase();
    return question.includes(q) || answer.includes(q);
  });
});

/* ================= Pagination ================= */
const currentPageFaq = ref(1);
const pageSizeFaq = ref(10);

const totalPagesFaq = computed(() =>
  Math.max(1, Math.ceil((filteredFaqs.value.length ?? 0) / pageSizeFaq.value))
);

const paginatedFaqs = computed(() => {
  const start = (currentPageFaq.value - 1) * pageSizeFaq.value;
  return filteredFaqs.value.slice(start, start + pageSizeFaq.value);
});

watch([() => props.faqs, searchQuery, pageSizeFaq], () => {
  currentPageFaq.value = 1;
});

watch(totalPagesFaq, (tp) => {
  if (currentPageFaq.value > tp) currentPageFaq.value = tp;
});

/* ================= Create dialog ================= */
const showCreateFaq = ref(false);
function openCreateFaq() {
  showCreateFaq.value = true;
}

async function onFaqCreated(created) {
  // если API вернул объект — добавим/обновим; иначе перечитаем
  if (created?.id) {
    setFaqs([created, ...(props.faqs ?? [])]);
  } else {
    await reloadFaqs();
  }
}

/* ================= Edit dialog ================= */
const showEditFaq = ref(false);
const selectedFaq = ref(null);

function openEditFaq(f) {
  selectedFaq.value = f;
  showEditFaq.value = true;
}

async function onFaqSaved(updated) {
  if (updated?.id) {
    setFaqs((props.faqs ?? []).map((x) => (x.id === updated.id ? updated : x)));
  } else {
    await reloadFaqs();
  }
}

/* ================= Delete ================= */
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
