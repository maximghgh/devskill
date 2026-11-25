<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="contacts">
                    <div class="contacts__inner">
                        <p class="contacts__name">Контакты</p>
                        <div class="contacts-main">
                            <p class="contacts-lead">По всем вопросам:</p>
                            <div class="contacts-grid">
                                <a
                                    v-for="phone in phones"
                                    :key="phone.href"
                                    class="contacts-item"
                                    :href="`tel:${phone.href}`"
                                >
                                    <span class="contacts-item__icon">📞</span>
                                    <span class="contacts-item__text">{{ phone.label }}</span>
                                </a>
                                <a
                                    class="contacts-item"
                                    href="https://vk.ru/istuschooluniverity"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <span class="contacts-item__icon">📲</span>
                                    <span class="contacts-item__text">
                                        ВК: vk.ru/istuschooluniverity
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="contacts" style="margin-top: 100px">
                    <div class="contacts__inner">
                        <p class="contacts__name">Столкнулись с проблемой или ошибкой на сайте?</p>
                        <div class="contacts__form">
                            <div class="contacts__title">Сообщить об ошибке</div>
                            <template v-if="isAuthenticated">
                                <template v-if="!isSubmitted">
                                    <div class="dialog__component">
                                        <p class="dialog__title">Описание проблемы</p>
                                        <textarea
                                            style="background: #FFFFFF"
                                            class="dialog__textarea"
                                            v-model="message"
                                            placeholder="Введите описание"
                                        />
                                    </div>
                                    <div class="contacts__checkbox">
                                        <input
                                            style="-webkit-appearance: auto; width: 20px; height: 20px;"
                                            type="checkbox"
                                            name="privacy"
                                            id="privacy"
                                            v-model="consent"
                                        >
                                        <p>Я соглашаюсь на <a href="#">обработку персональных данных</a></p>
                                    </div>
                                    <div class="dialog__btns">
                                        <button
                                            type="button"
                                            class="main__btn"
                                            style="margin-top: 30px;"
                                            :disabled="isSubmitting"
                                            @click="submitSupportRequest"
                                        >
                                            {{ isSubmitting ? "Отправка..." : "Отправить" }}
                                        </button>
                                    </div>
                                    <p v-if="errorMessage" style="color: #c00; margin-top: 12px;">
                                        {{ errorMessage }}
                                    </p>
                                </template>
                                <template v-else>
                                    <div style="display: flex; align-items: center; gap: 12px; color: #118a37;">
                                        <span style="display: inline-flex; width: 26px; height: 26px;">
                                            <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true">
                                                <path
                                                    fill="#118a37"
                                                    d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.2 14.2-3.4-3.4 1.4-1.4 2 2 4.8-4.8 1.4 1.4-6.2 6.2Z"
                                                />
                                            </svg>
                                        </span>
                                        <p style="margin: 0; font-size: 18px;">Обращение отправлено</p>
                                    </div>
                                </template>
                            </template>
                            <template v-else>
                                <p class="contacts__desc">
                                    Чтобы написать обращение, войдите в аккаунт.
                                </p>
                                <div class="dialog__btns" style="margin-top: 20px;">
                                    <a href="/login" class="main__btn">Войти</a>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="b-popup" id="popup">
            <div class="closer-big"></div>
            <div class="b-popup-content">
                <div class="popup-title"></div>
                <div class="popup-desc"></div>
                <div class="form-block">
                    <form
                        method="post"
                        name="mtForm1"
                        id="mtForm1"
                        class="forma"
                    >
                        <input
                            type="hidden"
                            name="data_form"
                            id="data_form"
                            value=""
                        />
                        <input type="hidden" name="no" value="no" />
                        <div class="form-field">
                            <label>
                                <span>Ваше имя:</span>
                                <input
                                    type="text"
                                    name="name"
                                    required="required"
                                />
                            </label>
                        </div>
                        <div class="form-field">
                            <label>
                                <span>Ваш телефон:</span>
                                <input
                                    type="tel"
                                    name="phone"
                                    required="required"
                                    class="phone_valid"
                                />
                            </label>
                        </div>
                        <div class="form-field">
                            <input
                                type="submit"
                                name="submit"
                                value="Отправить заявку"
                            />
                        </div>
                    </form>
                </div>
                <div class="close-up">
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
        <div class="b-popup" id="message">
            <div class="closer-big closer-big-all"></div>
            <div class="b-popup-content">
                <div class="popup-title">Cпасибо вам!</div>
                <div class="popup-desc">
                    Ваша заявка успешно отправлена. <br />C вами свяжутся в
                    ближайшее время.
                </div>
                <div class="close-up close-up-all">
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const phones = [
    { label: "57-60-30", href: "+73412576030" },
    { label: "+7 982 117 60 30", href: "+79821176030" },
    { label: "+7 982 990 37 89", href: "+79829903789" },
];

const user = ref(null);
const isAuthenticated = computed(() => Boolean(user.value && user.value.id));
const message = ref("");
const consent = ref(false);
const isSubmitting = ref(false);
const errorMessage = ref("");
const isSubmitted = ref(false);

onMounted(() => {
    const storedUser = localStorage.getItem("user");
    if (!storedUser) return;
    try {
        const parsed = JSON.parse(storedUser);
        if (parsed && parsed.id) {
            user.value = parsed;
        }
    } catch (error) {
        console.error("Ошибка при чтении пользователя из localStorage:", error);
    }
});

const submitSupportRequest = async () => {
    errorMessage.value = "";
    isSubmitted.value = false;

    if (!message.value.trim()) {
        errorMessage.value = "Введите описание проблемы.";
        return;
    }
    if (!consent.value) {
        errorMessage.value = "Нужно согласие на обработку персональных данных.";
        return;
    }
    if (!isAuthenticated.value) {
        errorMessage.value = "Нужно войти в аккаунт.";
        return;
    }

    try {
        isSubmitting.value = true;
        const payload = {
            user_id: user.value.id,
            user_name: user.value.name || user.value.email || "Пользователь",
            message: message.value.trim(),
        };
        await axios.post("/api/support-requests", payload);
        isSubmitted.value = true;
        message.value = "";
        consent.value = false;
    } catch (error) {
        console.error("Ошибка при отправке обращения:", error);
        errorMessage.value =
            error.response?.data?.message || "Ошибка при отправке обращения.";
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
.contacts-lead {
    margin: 0 0 20px;
    font-size: 1.0625rem;
    color: #4b5563;
}
.contacts-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}
.contacts-item {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 20px;
    border-radius: 14px;
    background-color: #f1f0fa;
    font-size: 1.0625rem;
    color: #2b2b3a;
    text-decoration: none;
    transition: background-color 0.2s ease;
}
.contacts-item:hover {
    background-color: #e6e3f5;
}
.contacts-item__icon {
    flex: 0 0 auto;
    font-size: 1.25rem;
}
.contacts-item__text {
    min-width: 0;
    overflow-wrap: anywhere;
}
</style>
