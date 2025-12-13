<template>
  <div class="user-block">
    <h1 class="page__title">Категории</h1>

    <!-- Tabs -->
    <div class="tabs dialog__tabs">
      <button type="button" class="dialog__tabs_item tab" :class="{ active: activeTab === 'langs' }" @click="setTab('langs')">
        Языки программирования
      </button>
      <button type="button" class="dialog__tabs_item tab" :class="{ active: activeTab === 'dirs' }" @click="setTab('dirs')">
        Направления
      </button>
    </div>

    <!-- TAB: LANGUAGES -->
    <div v-if="activeTab === 'langs'" class="languages-list">
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
              <input v-model="searchQuery" type="text" class="users-search__input" placeholder="Поиск..." />
              <button v-if="searchQuery" type="button" class="users-search__clear" @click="searchQuery = ''">×</button>
            </div>
          </div>

          <button type="button" class="users-btn-new" @click="showAddLang = true">
            <span class="users-btn-desc">+</span> Добавить язык
          </button>
        </div>
      </div>

      <table v-if="paginatedLangs.length" class="light-push-table">
        <thead>
          <tr>
            <th>№</th>
            <th>Название языка</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(lang, index) in paginatedLangs" :key="lang.id">
            <td>{{ index + 1 + (currentPage - 1) * pageSize }}</td>
            <td>{{ lang.name }}</td>
            <td class="hadle">
                <div class="tooltip-container">
                    <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openEditLang(lang)">
                        <img
                            width="24"
                            height="24"
                            src="../../../../img/admin/edit.svg"
                            alt=""
                        />
                    </button>
                    <div role="tooltip" id="help-tooltip" class="tooltip">
                        Редактировать язык
                    </div>
                </div>
                <div class="tooltip-container">
                    <button aria-describedby="help-tooltip" class="btn__user--edit" @click="deleteLanguage(lang.id)">
                        <img
                        width="24"
                        height="24"
                        src="../../../../img/admin/trash.png"
                        alt=""
                    />
                    </button>
                    <div role="tooltip" id="help-tooltip" class="tooltip">
                        Удалить язык
                    </div>
                </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination-users" v-if="totalPages > 1 && filteredLangs.length">
        <button :disabled="currentPage === 1" @click="currentPage--">‹ Назад</button>
        <button v-for="p in totalPages" :key="p" :class="{ active: currentPage === p }" @click="currentPage = p">
          {{ p }}
        </button>
        <button :disabled="currentPage === totalPages" @click="currentPage++">Вперёд ›</button>
      </div>

      <p v-if="!filteredLangs.length">Нет языков</p>
    </div>

    <!-- TAB: DIRECTIONS -->
    <div v-else class="directions-list">
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
              <input v-model="searchQuery" type="text" class="users-search__input" placeholder="Поиск..." />
              <button v-if="searchQuery" type="button" class="users-search__clear" @click="searchQuery = ''">×</button>
            </div>
          </div>

          <button type="button" class="users-btn-new" @click="showAddDir = true">
            <span class="users-btn-desc">+</span> Добавить направление
          </button>
        </div>
      </div>

      <table v-if="paginatedDirs.length" class="light-push-table">
        <thead>
          <tr>
            <th>№</th>
            <th>Название направления</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(dir, index) in paginatedDirs" :key="dir.id">
            <td>{{ index + 1 + (currentPage - 1) * pageSize }}</td>
            <td>{{ dir.name }}</td>
            <td class="hadle">
                <div class="tooltip-container">
                    <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openEditDir(dir)">
                        <img
                            width="24"
                            height="24"
                            src="../../../../img/admin/edit.svg"
                            alt=""
                        />
                    </button>
                    <div role="tooltip" id="help-tooltip" class="tooltip">
                        Редактировать направление
                    </div>
                </div>
                <div class="tooltip-container">
                    <button aria-describedby="help-tooltip" class="btn__user--edit" @click="deleteDirection(dir.id)">
                        <img
                        width="24"
                        height="24"
                        src="../../../../img/admin/trash.png"
                        alt=""
                    />
                    </button>
                    <div role="tooltip" id="help-tooltip" class="tooltip">
                        Удалить направление
                    </div>
                </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination-users" v-if="totalPages > 1 && filteredDirs.length">
        <button :disabled="currentPage === 1" @click="currentPage--">‹ Назад</button>
        <button v-for="p in totalPages" :key="p" :class="{ active: currentPage === p }" @click="currentPage = p">
          {{ p }}
        </button>
        <button :disabled="currentPage === totalPages" @click="currentPage++">Вперёд ›</button>
      </div>

      <p v-if="!filteredDirs.length">Нет направлений</p>
    </div>

    <!-- dialogs -->
    <AddLanguageDialog v-model="showAddLang" @saved="reloadLanguages" />
    <AddDirectionDialog v-model="showAddDir" @saved="reloadDirections" />

    <EditLanguageDialog v-model="showEditLang" :language="selectedLang" @saved="onLangSaved" />
    <EditDirectionDialog v-model="showEditDir" :direction="selectedDir" @saved="onDirSaved" />
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";

import AddLanguageDialog from "../modal_admin/AddLanguageDialog.vue";
import EditLanguageDialog from "../modal_admin/EditLanguageDialog.vue";
import AddDirectionDialog from "../modal_admin/AddDirectionDialog.vue";
import EditDirectionDialog from "../modal_admin/EditDirectionDialog.vue";

const props = defineProps({
  languages: { type: Array, default: () => [] },
  directions: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:languages", "update:directions"]);

function setLanguages(next) {
  emit("update:languages", next);
}
function setDirections(next) {
  emit("update:directions", next);
}

/* tabs + toolbar */
const activeTab = ref("langs");
const searchQuery = ref("");
const pageSize = ref(10);
const currentPage = ref(1);

function setTab(tab) {
  activeTab.value = tab;
  searchQuery.value = "";
  currentPage.value = 1;
}

watch([searchQuery, pageSize, activeTab], () => {
  currentPage.value = 1;
});

const normalizedQuery = computed(() => searchQuery.value.trim().toLowerCase());

const filteredLangs = computed(() => {
  const q = normalizedQuery.value;
  const arr = props.languages ?? [];
  if (!q) return arr;
  return arr.filter((l) => (l.name ?? "").toLowerCase().includes(q));
});

const filteredDirs = computed(() => {
  const q = normalizedQuery.value;
  const arr = props.directions ?? [];
  if (!q) return arr;
  return arr.filter((d) => (d.name ?? "").toLowerCase().includes(q));
});

const activeList = computed(() => (activeTab.value === "langs" ? filteredLangs.value : filteredDirs.value));

const totalPages = computed(() => Math.max(1, Math.ceil(activeList.value.length / pageSize.value)));

const paginatedLangs = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  return filteredLangs.value.slice(start, start + pageSize.value);
});

const paginatedDirs = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  return filteredDirs.value.slice(start, start + pageSize.value);
});

watch(totalPages, (tp) => {
  if (currentPage.value > tp) currentPage.value = tp;
});

/* delete */
async function deleteLanguage(id) {
  if (!confirm("Вы действительно хотите удалить этот язык?")) return;
  try {
    await axios.delete(`/api/languages/${id}`);
    setLanguages(props.languages.filter((l) => l.id !== id));
    globalNotification.categoryMessage = "Язык успешно удалён";
    globalNotification.type = "success";
  } catch (e) {
    console.error(e);
    globalNotification.categoryMessage = "Ошибка при удалении языка";
    globalNotification.type = "error";
  }
}

async function deleteDirection(id) {
  if (!confirm("Вы действительно хотите удалить направление?")) return;
  try {
    await axios.delete(`/api/directions/${id}`);
    setDirections(props.directions.filter((d) => d.id !== id));
    globalNotification.categoryMessage = "Направление удалено";
    globalNotification.type = "success";
  } catch (e) {
    console.error(e);
    globalNotification.categoryMessage = "Ошибка удаления направления";
    globalNotification.type = "error";
  }
}

/* dialogs state */
const showAddLang = ref(false);
const showAddDir = ref(false);

const showEditLang = ref(false);
const showEditDir = ref(false);

const selectedLang = ref(null);
const selectedDir = ref(null);

function openEditLang(lang) {
  selectedLang.value = lang;
  showEditLang.value = true;
}

function openEditDir(dir) {
  selectedDir.value = dir;
  showEditDir.value = true;
}

/* reload lists after create */
async function reloadLanguages() {
  try {
    const res = await axios.get("/api/languages");
    setLanguages(Array.isArray(res.data) ? res.data : res.data.data ?? []);
  } catch (e) {
    console.error(e);
  }
}

async function reloadDirections() {
  try {
    const res = await axios.get("/api/directions");
    setDirections(Array.isArray(res.data) ? res.data : res.data.data ?? []);
  } catch (e) {
    console.error(e);
  }
}

/* after edit: update list without full reload */
function onLangSaved(updated) {
  if (!updated?.id) return reloadLanguages();
  setLanguages(props.languages.map((l) => (l.id === updated.id ? updated : l)));
}

function onDirSaved(updated) {
  if (!updated?.id) return reloadDirections();
  setDirections(props.directions.map((d) => (d.id === updated.id ? updated : d)));
}
</script>

<style scoped>
.tabs {
  display: flex;
  gap: 10px;
  margin: 16px 0 40px;
}
.tab {
  font-family: JanoSansProRegular;
  padding: 10px 14px 10px 0px;
  border-radius: 10px;
  background: #fff;
  cursor: pointer;
  border: none;
}
.tab.active {
  color: #005c94;
  font-weight: 600;
}
</style>
