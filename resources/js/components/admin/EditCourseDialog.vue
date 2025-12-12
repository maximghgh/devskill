<template>
  <div v-if="modelValue" class="dialog" style="z-index: 10">
    <div class="dialog__container_custom dialog__container_custom--xl" @click.stop>
      <div class="dialog__inner">
        <!-- Заголовок -->
        <div class="dialog__header">
          <p>Редактировать курс</p>
          <div class="dialog__close" @click="closeDialog">✕</div>
        </div>

        <!-- Вкладки -->
        <div class="dialog__tabs">
          <p
            class="dialog__tabs_item"
            :class="{ active: currentTab === 'main' }"
            @click="currentTab = 'main'"
          >
            Основная информация
          </p>
          <p
            class="dialog__tabs_item"
            :class="{ active: currentTab === 'topics' }"
            @click="currentTab = 'topics'"
          >
            Темы
          </p>
        </div>

        <!-- Контент вкладок -->
        <div class="dialog__content">
          <!-- Основная информация -->
          <EditMainInfo
            v-if="currentTab === 'main'"
            :course="course"
            @saved="handleSaved"
          />

          <!-- Темы -->
          <Overview
            v-else
            :draft="{ courseId: course.id }"
            :isEdit="true"
            :admin="true"
            @cancel="closeDialog"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import EditMainInfo from "./Tab/EditMainInfo.vue";
import Overview     from './Tab/Overview.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  course:     { type: Object, default: null },
});

const emit = defineEmits(["update:modelValue", "updated", "cancel"]);

const currentTab = ref("main");

function closeDialog() {
  emit("update:modelValue", false);
  emit("cancel");
}

function handleSaved(updatedCourse) {
  // родителю отдаём обновлённый курс
  emit("updated", updatedCourse);
  // остаёмся в этой же модалке, но можно по желанию переключиться на вкладку "Темы":
  // currentTab.value = 'topics';
}

// При открытии всегда показываем "Основная информация"
watch(
  () => props.modelValue,
  (open) => {
    if (open) currentTab.value = "main";
  }
);
</script>
