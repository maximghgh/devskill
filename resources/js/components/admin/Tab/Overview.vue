<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import { globalNotification } from "@/globalNotification";
import CreateTopicDialog from "./../modal_admin/CreateTopic.vue";
import EditTopicDialog from "./../modal_admin/EditTopic.vue";
import CreateChapterDialog from "./../modal_admin/CreateChapter.vue";
import EditChapterDialog from "./../modal_admin/EditChapter.vue";



const props = defineProps({
  draft: { type: Object, default: () => ({}) },
  isEdit: { type: Boolean, default: false },
  admin: { type: Boolean, default: false },
});

const emit = defineEmits(["saved", "cancel", "dirty", "saving"]);

// ✅ если у тебя роуты как в старой странице (/admin/...), поставь '/admin'
const API = "/admin";

// id курса берём из draft, который лежит в обёртке CreateHackathon
const courseId = computed(() => props.draft?.courseId ?? props.draft?.slug ?? null);

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
      class="light-push-table light-push-table--s topics-table"
    >
      <thead>
        <tr>
          <th>№</th>
          <th>Название</th>
          <th>Описание</th>
          <th class="col-actions">Действия</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(chapter, index) in chapters" :key="chapter.id">
          <td>{{ index + 1 }}</td>
          <td>{{ chapter.title }}</td>
          <td>{{ chapter.type }}</td>

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