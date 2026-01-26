<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import axios from "axios";
import { globalNotification } from "@/globalNotification";
import AttachMaxQrDialog from "./../modal_admin/AttachMaxQr.vue";

const props = defineProps({
  draft: { type: Object, default: () => ({}) },
  isEdit: { type: Boolean, default: false },
  admin: { type: Boolean, default: false },
});

const emit = defineEmits(["saved", "cancel", "dirty", "saving"]);

const showQrDialog = ref(false);
const qrForm = ref({ link: "" });
const qrFile = ref(null);
const qrPreviewUrl = ref("");
const qrLoading = ref(false);
const qrError = ref("");
const qrFetching = ref(false);

const attachedQr = ref({ previewUrl: "", link: "" });

const API = "/admin";
const courseId = computed(() =>
  props.draft?.courseId ??
  props.draft?.id ??
  (Number.isFinite(+props.draft?.slug) ? +props.draft.slug : null)
);
const titleLabel = computed(() =>
  props.isEdit ? "Закрепить QR code" : "Закрепить MAX"
);
const actionLabel = computed(() =>
  props.isEdit ? "Редактировать QR" : "Закрепить MAX"
);

function normalizeQrSrc(raw) {
  if (!raw) return "";
  if (raw.startsWith("http://") || raw.startsWith("https://")) return raw;
  if (raw.startsWith("data:") || raw.startsWith("blob:")) return raw;
  if (raw.startsWith("/")) return raw;
  return `/${raw}`;
}

function revokeObjectUrl(url) {
  if (url && url.startsWith("blob:")) {
    URL.revokeObjectURL(url);
  }
}

function setAttachedQr(qr) {
  revokeObjectUrl(attachedQr.value.previewUrl);
  attachedQr.value = {
    previewUrl: normalizeQrSrc(qr?.qr_image || ""),
    link: qr?.link || "",
  };
}

async function loadQr() {
  if (!courseId.value) return;

  qrFetching.value = true;
  try {
    const { data } = await axios.get(`${API}/course/${courseId.value}/qr`);
    if (data?.qr) {
      setAttachedQr(data.qr);
    } else {
      setAttachedQr({ qr_image: "", link: "" });
    }
  } catch (e) {
    console.error("Ошибка загрузки QR-кода:", e);
  } finally {
    qrFetching.value = false;
  }
}

function openQrDialog() {
  qrError.value = "";
  qrForm.value.link = attachedQr.value.link || "";
  if (qrPreviewUrl.value) {
    URL.revokeObjectURL(qrPreviewUrl.value);
    qrPreviewUrl.value = "";
  }
  qrFile.value = null;
  showQrDialog.value = true;
}

function resetQrForm() {
  if (qrPreviewUrl.value) {
    URL.revokeObjectURL(qrPreviewUrl.value);
  }
  qrForm.value = { link: "" };
  qrFile.value = null;
  qrPreviewUrl.value = "";
  qrError.value = "";
}

function closeQrDialog() {
  resetQrForm();
  showQrDialog.value = false;
}

function onQrFileChange(event) {
  const file = event.target.files && event.target.files[0];
  if (!file) {
    if (qrPreviewUrl.value) {
      URL.revokeObjectURL(qrPreviewUrl.value);
    }
    qrFile.value = null;
    qrPreviewUrl.value = "";
    return;
  }

  if (qrPreviewUrl.value) {
    URL.revokeObjectURL(qrPreviewUrl.value);
  }

  qrFile.value = file;
  qrPreviewUrl.value = URL.createObjectURL(file);
}

async function submitQr() {
  qrError.value = "";

  if (!courseId.value) {
    qrError.value = "Сначала сохраните курс.";
    return;
  }

  if (!qrFile.value && !attachedQr.value.previewUrl) {
    qrError.value = "Прикрепите QR-код.";
    return;
  }

  if (!qrForm.value.link.trim()) {
    qrError.value = "Добавьте ссылку.";
    return;
  }

  qrLoading.value = true;
  emit("saving", true);

  try {
    const formData = new FormData();
    formData.append("link", qrForm.value.link.trim());
    if (qrFile.value) {
      formData.append("qr", qrFile.value);
    }

    const { data } = await axios.post(
      `${API}/course/${courseId.value}/qr`,
      formData,
      { headers: { "Content-Type": "multipart/form-data" } }
    );

    if (data?.qr) {
      setAttachedQr(data.qr);
    }

    globalNotification.categoryMessage = "MAX закреплен";
    globalNotification.type = "success";
    emit("saved", { qr: data?.qr || null });
    closeQrDialog();
  } catch (e) {
    console.error("Ошибка сохранения QR-кода:", e);
    qrError.value = "Ошибка сохранения QR-кода.";
    globalNotification.categoryMessage = "Ошибка сохранения QR-кода";
    globalNotification.type = "error";
  } finally {
    qrLoading.value = false;
    emit("saving", false);
  }
}

watch(courseId, (nextId) => {
  if (!nextId) {
    setAttachedQr({ qr_image: "", link: "" });
    return;
  }
  loadQr();
});

onMounted(() => {
  if (courseId.value) {
    loadQr();
  }
});

onBeforeUnmount(() => {
  revokeObjectUrl(qrPreviewUrl.value);
  revokeObjectUrl(attachedQr.value.previewUrl);
});
</script>

<template>
  <div class="pin-max">
    <div class="topic__top">
      <p class="block__title">{{ titleLabel }}</p>
      <button
        type="button"
        class="users-btn-new"
        @click="openQrDialog"
        :disabled="!courseId || qrFetching"
      >
        {{ actionLabel }}
      </button>
    </div>

    <div v-if="attachedQr.previewUrl" class="pin-max__preview">
      <img :src="attachedQr.previewUrl" alt="QR code" />
      <a
        class="pin-max__link"
        :href="attachedQr.link"
        target="_blank"
        rel="noopener"
      >
        {{ attachedQr.link }}
      </a>
    </div>
    <div v-else-if="!qrFetching" class="pin-max__empty">
      QR MAX не прикреплен
    </div>

    <AttachMaxQrDialog
      v-model="showQrDialog"
      :qr-preview-url="qrPreviewUrl"
      :attached-preview-url="attachedQr.previewUrl"
      :qr-loading="qrLoading"
      :qr-error="qrError"
      :link-value="qrForm.link"
      @file-change="onQrFileChange"
      @update:link-value="(val) => (qrForm.link = val)"
      @submit="submitQr"
      @update:model-value="(val) => { if (!val) closeQrDialog(); }"
    />
  </div>
</template>

<style scoped>
.pin-max{
    
}
.pin-max__empty {
  margin-top: 24px;
  color: #8d8d8d;
  text-align: center;
}

.pin-max__preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  margin-top: 24px;
}

.pin-max__preview img {
  max-width: 260px;
  max-height: 260px;
  object-fit: contain;
}

.pin-max__link {
  max-width: 520px;
  word-break: break-all;
  text-align: center;
}
</style>
