<template>
    <div class="rte">
        <div ref="host"></div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import Quill from "quill";
import "quill/dist/quill.snow.css";

const props = defineProps({
    modelValue: { type: String, default: "" },
    placeholder: { type: String, default: "Введите текст..." },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const host = ref(null);
let quill = null;

onMounted(() => {
    quill = new Quill(host.value, {
        theme: "snow",
        placeholder: props.placeholder,
        modules: {
            toolbar: [
                ["bold", "italic", "underline"],
                [{ list: "bullet" }, { list: "ordered" }],
                ["link"],
                ["clean"],
            ],
        },
    });

    if (props.modelValue) {
        quill.clipboard.dangerouslyPasteHTML(props.modelValue);
    }

    quill.enable(!props.disabled);

    quill.on("text-change", () => {
        // Пустой редактор отдаёт "<p><br></p>" — считаем это пустотой
        const html = quill.getText().trim() === "" ? "" : quill.root.innerHTML;
        emit("update:modelValue", html);
    });
});

onBeforeUnmount(() => {
    quill = null;
});

watch(
    () => props.disabled,
    (value) => quill?.enable(!value)
);

watch(
    () => props.modelValue,
    (value) => {
        if (!quill) return;
        if (quill.root.innerHTML === value) return;

        // Внешнее значение (открыли другой вопрос) — перезаливаем содержимое
        if (!value) {
            quill.setText("");
            return;
        }

        quill.clipboard.dangerouslyPasteHTML(value);
    }
);
</script>

<style scoped>
.rte :deep(.ql-toolbar) {
    border-color: #9e9e9e;
    border-radius: 12px 12px 0 0;
    background: #f7f6fb;
}

.rte :deep(.ql-container) {
    border-color: #9e9e9e;
    border-radius: 0 0 12px 12px;
    font-family: inherit;
    font-size: 16px;
}

.rte :deep(.ql-editor) {
    min-height: 160px;
    max-height: 320px;
}

/* Жирный ломался дважды: глобальный сброс в app.css задаёт этим тегам
   font-weight: inherit, а сам шрифт одноначертательный — на сайте
   жирное начертание подключено отдельным семейством. */
.rte :deep(.ql-editor b),
.rte :deep(.ql-editor strong) {
    font-family: JanoSansProBold;
    font-weight: 700;
}
</style>
