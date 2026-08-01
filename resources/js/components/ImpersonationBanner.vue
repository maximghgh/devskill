<template>
    <div v-if="impersonator" class="impersonation-banner">
        <span class="impersonation-banner__text">
            Вы работаете под аккаунтом
            <b>{{ userName }}</b>
            <template v-if="impersonator.name">
                (администратор: {{ impersonator.name }})
            </template>
        </span>
        <button
            type="button"
            class="impersonation-banner__btn"
            :disabled="loading"
            @click="stop"
        >
            {{ loading ? "Возвращаемся..." : "Вернуться в админку" }}
        </button>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const impersonator = ref(null);
const user = ref(null);
const loading = ref(false);

const userName = computed(() => user.value?.name || "пользователя");

function readLocal(key) {
    try {
        return JSON.parse(localStorage.getItem(key) || "null");
    } catch {
        return null;
    }
}

async function stop() {
    loading.value = true;
    try {
        const { data } = await axios.post("/impersonate/stop");
        localStorage.setItem("user", JSON.stringify(data.user));
        localStorage.removeItem("impersonator");
        window.location.href = "/admin";
    } catch (e) {
        console.error("Не удалось вернуться в админку:", e);
        loading.value = false;
    }
}

onMounted(async () => {
    // Быстрый путь: метка уже лежит в localStorage.
    impersonator.value = readLocal("impersonator");
    user.value = readLocal("user");

    // Сверяемся с сервером: сессия — источник правды.
    // Это чинит и обратный случай (метка осталась, а сессия уже нет).
    try {
        const { data } = await axios.get("/impersonate/status");
        if (data?.impersonating) {
            impersonator.value = data.impersonator;
            user.value = data.user;
            localStorage.setItem(
                "impersonator",
                JSON.stringify(data.impersonator)
            );
        } else {
            impersonator.value = null;
            localStorage.removeItem("impersonator");
        }
    } catch (e) {
        // 401/419 — просто не показываем баннер.
        impersonator.value = null;
    }
});
</script>

<style scoped>
.impersonation-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 10px 20px;
    background: #ffb020;
    color: #2b2b2b;
    font-size: 14px;
    text-align: center;
}
.impersonation-banner__text b {
    font-weight: 700;
}
.impersonation-banner__btn {
    padding: 6px 16px;
    border: none;
    border-radius: 8px;
    background: #2b2b2b;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
    white-space: nowrap;
}
.impersonation-banner__btn:hover {
    background: #000;
}
.impersonation-banner__btn:disabled {
    opacity: 0.6;
    cursor: default;
}
</style>
