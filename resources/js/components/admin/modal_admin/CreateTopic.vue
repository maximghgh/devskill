<template>
    <div v-if="modelValue" class="dialog" @click.self="close" style="z-index: 1">
        <div
            class="dialog__container_custom dialog__container_custom--s"
            @click.stop
        >
            <div class="dialog__inner" :class="{ 'is-saving': loading }">
                <!-- Заголовок модалки -->
                <div class="dialog__header">
                    <p>Добавить тему</p>
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

                <!-- Контент модалки -->
                <div class="dialog__content">
                    <div class="form__admin">
                        <!-- Название темы -->
                        <div class="dialog__component">
                            <p class="dialog__title">Название темы</p>
                            <input
                                v-model="newTopic.title"
                                type="text"
                                class="dialog__input"
                                placeholder="Введите название темы"
                                :disabled="loading"
                                required
                            />
                        </div>

                        <!-- Описание темы -->
                        <div class="dialog__component">
                            <p class="dialog__title">Описание</p>
                            <textarea
                                v-model="newTopic.description"
                                class="dialog__textarea dialog__textarea--s"
                                placeholder="Введите описание темы"
                                :disabled="loading"
                            ></textarea>
                        </div>

                        <!-- Ошибка -->
                        <p v-if="error" class="dialog__error">
                            {{ error }}
                        </p>

                        <!-- Кнопки -->
                        <div class="dialog__btns">
                            <button
                                type="button"
                                class="main__btn main__btn--white"
                                @click="close"
                                :disabled="loading"
                            >
                                Отмена
                            </button>

                            <button
                                type="button"
                                class="main__btn"
                                @click="submitTopic"
                                :disabled="loading || !newTopic.title.trim()"
                            >
                                {{ loading ? "Сохранение..." : "Далее" }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
// import { globalNotification } from "../../globalNotification";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    // опционально — можно передать courseId из родителя
    courseId: {
        type: [String, Number],
        required: true,
    },
});

const emit = defineEmits(["update:modelValue", "saved"]);

const newTopic = ref({
    title: "",
    description: "",
});

const loading = ref(false);
const error = ref("");

function resetForm() {
    newTopic.value = { title: "", description: "" };
    error.value = "";
}

function close() {
    resetForm();
    emit("update:modelValue", false);
}

async function submitTopic() {
    error.value = "";
    loading.value = true;

    try {
        const { data } = await axios.post(
            `/admin/course/${props.courseId}/topics`,
            newTopic.value
        );

        // уведомление, как в твоём старом коде
        globalNotification.categoryMessage = "Тема создана";
        globalNotification.type = "success";

        // отдаём созданную тему родителю
        emit("saved", data.topic);

        close();
    } catch (e) {
        console.error("Ошибка создания темы:", e);
        error.value = "Ошибка создания темы. Заполните все поля.";

        globalNotification.categoryMessage = "Заполните все поля";
        globalNotification.type = "error";
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.dialog__error {
    color: #d40000;
    font-size: 13px;
    margin-top: -8px;
    margin-bottom: 8px;
}
</style>
