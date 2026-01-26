<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  qrPreviewUrl: { type: String, default: "" },
  attachedPreviewUrl: { type: String, default: "" },
  qrLoading: { type: Boolean, default: false },
  qrError: { type: String, default: "" },
  linkValue: { type: String, default: "" },
});

const emit = defineEmits([
  "update:modelValue",
  "file-change",
  "submit",
  "update:linkValue",
]);

function close() {
  emit("update:modelValue", false);
}

function onFileChange(event) {
  emit("file-change", event);
}

function onSubmit() {
  emit("submit");
}
</script>

<template>
  <div v-if="modelValue" class="dialog" @click.self="close" style="z-index: 1">
    <div class="dialog__container_custom dialog__container_custom--s" @click.stop>
      <div class="dialog__inner" :class="{ 'is-saving': qrLoading }">
        <div class="dialog__header">
          <p>Закрепить MAX</p>
          <div class="dialog__close" @click="close">
            <svg
              width="13"
              height="12"
              viewBox="0 0 13 12"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M7.90994 6.00019L12.2099 1.71019C12.3982 1.52188 12.504 1.26649 12.504 1.00019C12.504 0.733884 12.3982 0.478489 12.2099 0.290185C12.0216 0.101882 11.7662 -0.00390625 11.4999 -0.00390625C11.2336 -0.00390625 10.9782 0.101882 10.7899 0.290185L6.49994 4.59019L2.20994 0.290185C2.02164 0.101882 1.76624 -0.00390625 1.49994 -0.00390625C1.23364 -0.00390625 0.978243 0.101882 0.789939 0.290185C0.601635 0.478489 0.495847 0.733884 0.495847 1.00019C0.495847 1.26649 0.601635 1.52188 0.789939 1.71019L5.08994 6.00019L0.789939 10.2902C0.696211 10.3831 0.621816 10.4937 0.571048 10.6156C0.520279 10.7375 0.494141 10.8682 0.494141 11.0002C0.494141 11.1322 0.520279 11.2629 0.571048 11.3848C0.621816 11.5066 0.696211 11.6172 0.789939 11.7102C0.882902 11.8039 0.993503 11.8783 1.11536 11.9291C1.23722 11.9798 1.36793 12.006 1.49994 12.006C1.63195 12.006 1.76266 11.9798 1.88452 11.9291C2.00638 11.8783 2.11698 11.8039 2.20994 11.7102L6.49994 7.41019L10.7899 11.7102C10.8829 11.8039 10.9935 11.8783 11.1154 11.9291C11.2372 11.9798 11.3679 12.006 11.4999 12.006C11.632 12.006 11.7627 11.9798 11.8845 11.9291C12.0064 11.8783 12.117 11.8039 12.2099 11.7102C12.3037 11.6172 12.3781 11.5066 12.4288 11.3848C12.4796 11.2629 12.5057 11.1322 12.5057 11.0002C12.5057 10.8682 12.4796 10.7375 12.4288 10.6156C12.3781 10.4937 12.3037 10.3831 12.2099 10.2902L7.90994 6.00019Z"
                fill="#999999"
              />
            </svg>
          </div>
        </div>

        <div class="dialog__content">
          <div class="form__admin">
            <div class="qr-preview">
              <img
                v-if="qrPreviewUrl || attachedPreviewUrl"
                :src="qrPreviewUrl || attachedPreviewUrl"
                alt="QR code preview"
              />
              <span v-else>QR-код не прикреплен</span>
            </div>

            <div class="dialog__component">
              <p class="dialog__title">Прикрепить QR-код</p>
              <input
                type="file"
                class="dialog__input"
                accept="image/*"
                :disabled="qrLoading"
                @change="onFileChange"
              />
            </div>

            <div class="dialog__component">
              <p class="dialog__title">Ссылка</p>
              <input
                :value="linkValue"
                type="url"
                class="dialog__input"
                placeholder="https://example.com"
                :disabled="qrLoading"
                required
                @input="emit('update:linkValue', $event.target.value)"
              />
            </div>

            <p v-if="qrError" class="dialog__error">
              {{ qrError }}
            </p>

            <div class="dialog__btns">
              <button
                type="button"
                class="main__btn main__btn--white"
                @click="close"
                :disabled="qrLoading"
              >
                Отмена
              </button>

              <button
                type="button"
                class="main__btn"
                @click="onSubmit"
                :disabled="qrLoading"
              >
                {{ qrLoading ? "Сохранение..." : "Закрепить" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dialog__error {
  color: #d40000;
  font-size: 13px;
  margin-top: -8px;
  margin-bottom: 8px;
}

.qr-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 180px;
  margin-bottom: 16px;
  border: 1px dashed #d9d9d9;
  border-radius: 12px;
  background: #fafafa;
  text-align: center;
}

.qr-preview img {
  max-width: 220px;
  max-height: 220px;
}

.qr-preview span {
  color: #8d8d8d;
  font-size: 13px;
}
</style>
