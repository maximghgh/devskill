<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import { globalNotification } from "@/globalNotification";
import CreateTopicDialog from "./../modal_admin/CreateTopic.vue";
import EditTopicDialog from "./../modal_admin/EditTopic.vue";
import CreateChapterDialog from "./../modal_admin/CreateChapter.vue";
import EditChapterDialog from "./../modal_admin/EditChapter.vue";
import { getChapterFileIcon, getChapterFileName } from "@/utils/chapterFiles";



const props = defineProps({
  draft: { type: Object, default: () => ({}) },
  isEdit: { type: Boolean, default: false },
  admin: { type: Boolean, default: false },
});

const emit = defineEmits(["saved", "cancel", "dirty", "saving"]);

// ✅ если у тебя роуты как в старой странице (/admin/...), поставь '/admin'
const API = "/admin";

// id курса берём из draft, который лежит в обёртке CreateHackathon
const courseId = computed(() =>
  props.draft?.courseId ??
  props.draft?.id ??
  (Number.isFinite(+props.draft?.slug) ? +props.draft.slug : null)
);

const topics = ref([]);
// экран: список тем или уроки конкретной темы
const viewMode = ref("topics"); // "topics" | "chapters"
const activeTopic = ref(null);

// уроки (главы) выбранной темы
const chapters = ref([]);
const chaptersLoading = ref(false);

const showCreateChapterDialog = ref(false);
const showCreateTopicDialog = ref(false);

const showEditChapterDialog = ref(false);
const chapterToEdit = ref(null);


// ✅ inline-редактирование
const editingTopicId = ref(null);
const editingTopic = ref({ id: null, title: "", description: "" });

const showEditTopicDialog = ref(false);
const topicToEdit = ref(null);

function openEditTopic(topic) {
  topicToEdit.value = topic;
  showEditTopicDialog.value = true;
}

function onTopicUpdated(updated) {
  const idx = topics.value.findIndex(t => t.id === updated.id);
  if (idx !== -1) topics.value[idx] = updated;
}


// загрузка тем
async function loadTopics() {
  if (!courseId.value) return;
  try {
    const { data } = await axios.get(`${API}/course/${courseId.value}/topics`);
    topics.value = Array.isArray(data) ? data : (data.topics || []);
  } catch (e) {
    console.error("Ошибка загрузки тем", e);
  }
}


// загрузка уроков
async function loadChapters(topicId) {
  if (!topicId) return;

  chaptersLoading.value = true;
  chapters.value = [];

  try {
    // твой роут, судя по запросу: /admin/topic/17/chapters
    const { data } = await axios.get(`${API}/topic/${topicId}/chapters`);

    // твой контроллер возвращает { topic, chapters }
    chapters.value = data?.chapters || [];
  } catch (e) {
    console.error("Ошибка загрузки уроков:", e);
    chapters.value = [];
  } finally {
    chaptersLoading.value = false;
  }
}

function extractChapterFiles(chapter) {
  const raw =
    chapter?.presentation_paths ||
    chapter?.presentation_path ||
    chapter?.attachment_path ||
    chapter?.file_path ||
    chapter?.file ||
    null;

  if (!raw) return [];

  if (Array.isArray(raw)) {
    return raw.filter(Boolean);
  }

  if (typeof raw === "string") {
    const trimmed = raw.trim();

    if (trimmed.startsWith("[") && trimmed.endsWith("]")) {
      try {
        const parsed = JSON.parse(trimmed);
        if (Array.isArray(parsed)) {
          return parsed.filter(Boolean);
        }
      } catch {
        // fall through to single value
      }
    }

    return [raw];
  }

  return [raw];
}

function chapterFilesLabel(chapter) {
  const names = extractChapterFiles(chapter)
    .map((file) => getChapterFileName(file))
    .filter(Boolean);

  return names.length ? names.join(", ") : "—";
}

function chapterFiles(chapter) {
  return extractChapterFiles(chapter)
    .map((file, index) => ({
      key: `${String(file)}-${index}`,
      name: getChapterFileName(file),
      icon: getChapterFileIcon(file),
    }))
    .filter((file) => file.name);
}


function openTopicChapters(topic) {
  activeTopic.value = topic;
  viewMode.value = "chapters";
  loadChapters(topic.id);
}

function backToTopics() {
  viewMode.value = "topics";
  activeTopic.value = null;
  chapters.value = [];
}

function onTopicSaved(topic) {
  if (topic) topics.value.push(topic);
  showCreateTopicDialog.value = false;
  emit("dirty");
}

function onChapterSaved(chapter) {
  if (chapter) chapters.value.push(chapter);
  showCreateChapterDialog.value = false;
}

function openEditChapter(chapter) {
  chapterToEdit.value = chapter;
  showEditChapterDialog.value = true;
}
function onChapterUpdated(updated) {
  const idx = chapters.value.findIndex(c => c.id === updated.id);
  if (idx !== -1) chapters.value[idx] = updated;
}

async function deleteChapter(chapterId) {
  if (!activeTopic.value?.id) return;
  if (!confirm("Удалить урок?")) return;

  try {
    await axios.delete(`/api/admin/topic/${activeTopic.value.id}/chapters/${chapterId}`);
    chapters.value = chapters.value.filter(c => c.id !== chapterId);

    globalNotification.categoryMessage = "Урок удалён";
    globalNotification.type = "success";
  } catch (e) {
    console.error(e);
    globalNotification.categoryMessage = "Ошибка удаления урока";
    globalNotification.type = "error";
  }
}


// ✅ старт редактирования
function startEditingTopic(topic) {
  editingTopicId.value = topic.id;
  editingTopic.value = {
    id: topic.id,
    title: topic.title ?? "",
    description: topic.description ?? "",
  };
  emit("dirty");
}

function cancelEditingTopic() {
  editingTopicId.value = null;
  editingTopic.value = { id: null, title: "", description: "" };
}

// ✅ сохранить изменения темы
async function saveTopic() {
  if (!editingTopicId.value) return;

  emit("saving", true);
  try {
    const payload = {
      title: editingTopic.value.title,
      description: editingTopic.value.description,
    };

    // если у тебя endpoint как в старом файле: `/admin/topics/${id}` — поменяй API выше на '/admin'
    const { data } = await axios.patch(`${API}/topics/${editingTopicId.value}`, payload);

    const updated = data.topic ?? data;
    const idx = topics.value.findIndex(t => t.id === updated.id);
    if (idx !== -1) topics.value[idx] = updated;

    globalNotification.categoryMessage = "Тема изменена";
    globalNotification.type = "success";

    cancelEditingTopic();
  } catch (e) {
    console.error("Ошибка обновления темы:", e);
    globalNotification.categoryMessage = "Ошибка обновления";
    globalNotification.type = "error";
  } finally {
    emit("saving", false);
  }
}

// ✅ удалить тему
async function deleteTopic(id) {
  if (!id) return;
  if (!confirm("Удалить тему?")) return;

  emit("saving", true);
  try {
    await axios.delete(`${API}/topics/${id}`);

    topics.value = topics.value.filter(t => t.id !== id);

    // если удалили редактируемую — сбросим
    if (editingTopicId.value === id) cancelEditingTopic();

    globalNotification.categoryMessage = "Тема удалена";
    globalNotification.type = "success";

    emit("dirty");
  } catch (e) {
    console.error("Ошибка удаления темы:", e);
    globalNotification.categoryMessage = "Ошибка удаления";
    globalNotification.type = "error";
  } finally {
    emit("saving", false);
  }
}

async function save() {
  globalNotification.categoryMessage = "Темы сохранены";
  globalNotification.type = "success";
  emit("saved", { topics: topics.value });
}

function cancel() {
  emit("cancel");
}

watch(courseId, () => loadTopics());

onMounted(() => loadTopics());
</script>


<template>
  <div v-if="viewMode === 'topics'">
    <div class="topic__top">
      <p class="block__title">Темы курса</p>
      <button
        type="button"
        class="users-btn-new"
        @click="showCreateTopicDialog = true"
        :disabled="!courseId"
      >
        Добавить еще
      </button>
    </div>

    <table
      v-if="topics.length"
      class="light-push-table light-push-table--s topics-table"
    >
      <thead>
      <tr>
        <th>#</th>
        <th>Название темы</th>
        <th>Описание</th>
        <th>Количество материала</th>
        <th class="col-actions">Действие</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(topic, index) in topics" :key="topic.id ?? index">
        <td>{{ index + 1 }}</td>
        <td>{{ topic.title }}</td>
        <td>{{ topic.description }}</td>
        <td>{{ topic.chapters_count }}</td>
        <td class="col-btn">
          <div class="btn__edit">
            <div class="tooltip-container">
                <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openEditTopic(topic)">
                    <img
                        width="24"
                        height="24"
                        src="../../../../img/admin/edit.svg"
                        alt=""
                    />
                </button>
                <div role="tooltip" id="help-tooltip" class="tooltip">
                    Редактировать тему
                </div>
            </div>
            <div class="tooltip-container">
                <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openTopicChapters(topic)">
                    <img
                      width="24"
                      height="24"
                      src="../../../../img/admin/edit_file.svg"
                      alt=""
                    />
                </button>
                <div role="tooltip" id="help-tooltip" class="tooltip">
                    Добавить уроки
                </div>
            </div>
            <div class="tooltip-container">
                <button aria-describedby="help-tooltip" class="btn__user--edit" @click="deleteTopic(topic.id)">
                    <img width="24" height="24" src="../../../../img/admin/trash.png" alt="delete" />
                </button>
                <div role="tooltip" id="help-tooltip" class="tooltip">
                    Удалить тему
                </div>
            </div>
          </div>
        </td>
      </tr>
      </tbody>
    </table>

    <p v-else class="block__title block__title--clear">
      У курса отсутствуют темы
    </p>

    <div class="dialog__btns">
      <button
        type="button"
        class="main__btn main__btn--white"
        @click="cancel"
      >
        Отмена
      </button>

      <button
        type="button"
        class="main__btn"
        @click="save"
        :disabled="!courseId"
      >
        Сохранить
      </button>
    </div>

    <CreateTopicDialog
      v-model="showCreateTopicDialog"
      :course-id="courseId"
      @saved="onTopicSaved"
    />
    <EditTopicDialog
      v-model="showEditTopicDialog"
      :topic="topicToEdit"
      @saved="onTopicUpdated"
    />
  </div>
  <div v-else>
    <div class="topic__top">
      <button type="button" class="users-btn-new" @click="backToTopics">
        ← Назад
      </button>

      <!-- 1) вместо "Темы курса" тут название темы -->
      <p class="block__title">{{ activeTopic?.title }}</p>

      <!-- 1) и кнопка добавить урок -->
      <button
        type="button"
        class="users-btn-new"
        @click="showCreateChapterDialog = true"
        :disabled="!activeTopic?.id"
      >
        Добавить урок
      </button>
    </div>

    <!-- 2) таблица как в файле глав (без кнопки перехода по центру) -->
    <table
      v-if="chapters.length"
      class="light-push-table light-push-table--s topics-table topics-table--chapters"
    >
      <thead>
        <tr>
          <th>№</th>
          <th class="col-title">Название</th>
          <th class="col-files">Файлы</th>
          <th class="col-actions">Действия</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(chapter, index) in chapters" :key="chapter.id">
          <td>{{ index + 1 }}</td>
          <td class="cell-title">{{ chapter.title }}</td>
          <td class="cell-files" :title="chapterFilesLabel(chapter)">
            <div v-if="chapterFiles(chapter).length" class="cell-files__list">
              <div
                v-for="(file, fileIndex) in chapterFiles(chapter)"
                :key="file.key"
                class="cell-files__item"
              >
                <img
                  :src="file.icon"
                  :alt="file.name"
                  class="cell-files__icon"
                />
                <span class="cell-files__name">
                  {{ file.name }}<span v-if="fileIndex < chapterFiles(chapter).length - 1">,</span>
                </span>
              </div>
            </div>
            <span v-else>—</span>
          </td>

          <!-- пока можешь оставить заглушки, потом подключим EditChapterDialog -->
            <td class="col-btn">
            <div class="btn__edit">
              <div class="tooltip-container">
                  <button aria-describedby="help-tooltip" class="btn__user--edit" @click="openEditChapter(chapter)">
                      <img
                        width="24"
                        height="24"
                        src="../../../../img/admin/edit.svg"
                        alt=""
                    />
                  </button>
                  <div role="tooltip" id="help-tooltip" class="tooltip">
                      Редактировать урок
                  </div>
              </div>
              <div class="tooltip-container">
                  <button aria-describedby="help-tooltip" class="btn__user--edit" @click="deleteChapter(chapter.id)">
                      <img width="24" height="24" src="../../../../img/admin/trash.png" alt="delete" />
                  </button>
                  <div role="tooltip" id="help-tooltip" class="tooltip">
                      Удалить урок
                  </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else class="block__title block__title--clear">
      {{ chaptersLoading ? "Загрузка..." : "У темы нет уроков" }}
    </p>

    <CreateChapterDialog
      v-model="showCreateChapterDialog"
      :topic-id="activeTopic?.id"
      @saved="onChapterSaved"
    />
    <EditChapterDialog
      v-model="showEditChapterDialog"
      :topic-id="activeTopic?.id"
      :chapter="chapterToEdit"
      @saved="onChapterUpdated"
    />

  </div>
</template>

<style scoped>
.topics-table--chapters {
  table-layout: fixed;
  width: 100%;
}

.col-title {
  width: 340px;
}

.col-files {
  width: 52%;
}

.cell-title {
  min-width: 340px;
  max-width: 340px;
  white-space: normal;
  overflow-wrap: anywhere;
}

.cell-files {
  white-space: normal;
  overflow-wrap: anywhere;
  vertical-align: top;
}

.cell-files__list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 12px;
}

.cell-files__item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  max-width: 100%;
}

.cell-files__icon {
  width: 26px;
  height: 26px;
  object-fit: contain;
  flex: 0 0 auto;
}

.cell-files__name {
  min-width: 0;
  line-height: 1.35;
  overflow-wrap: anywhere;
}
</style>
