<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import { globalNotification } from "@/globalNotification";
import CreateTopicDialog from "./../modal_admin/CreateTopic.vue";

const props = defineProps({
  draft: { type: Object, default: () => ({}) },
  isEdit: { type: Boolean, default: false },
  admin: { type: Boolean, default: false },
});

const emit = defineEmits(["saved", "cancel", "dirty", "saving"]);

// id курса берём из draft, который лежит в обёртке CreateHackathon
const courseId = computed(() => props.draft?.courseId ?? props.draft?.slug ?? null);

const topics = ref([]);
const showCreateTopicDialog = ref(false);

async function loadTopics() {
  if (!courseId.value) return;
  try {
    const { data } = await axios.get(`/api/admin/course/${courseId.value}/topics`);
    topics.value = Array.isArray(data) ? data : (data.topics || []);
  } catch (e) {
    console.error("Ошибка загрузки тем", e);
    // globalNotification.categoryMessage = "Ошибка загрузки тем";
    // globalNotification.type = "error";
  }
}

function onTopicSaved(topic) {
  if (topic) topics.value.push(topic);
  showCreateTopicDialog.value = false;
}

async function save() {
  globalNotification.categoryMessage = "Темы сохранены";
  globalNotification.type = "success";

  emit("saved", { topics: topics.value }); // опционально, если родителю нужно
}

function cancel() {
  emit("cancel");
}

// если курс только что создали и в draft положили id — подгружаем темы
watch(courseId, () => {
  loadTopics();
});

onMounted(() => {
  loadTopics();
});
</script>

<template>
  <div>
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
      class="light-push-table light-push-table--s"
    >
      <thead>
      <tr>
        <th>#</th>
        <th>Название темы</th>
        <th>Описание</th>
        <th>Количество материала</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(topic, index) in topics" :key="topic.id ?? index">
        <td>{{ index + 1 }}</td>
        <td>{{ topic.title }}</td>
        <td>{{ topic.description }}</td>
        <td>{{ topic.chapters_count }}</td>
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
  </div>
</template>
