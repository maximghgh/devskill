<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="news">
                    <div class="news__inner">
                        <div class="news__header">
                            <h1>Новости</h1>
                        </div>
                        <div class="news__grid">
                            <article
                                v-for="newsItem in paginatedNews"
                                :key="newsItem.id"
                                class="news-card"
                            >
                                <a
                                    :href="`/news-single?id=${newsItem.id}`"
                                    class="news-card__link"
                                >
                                    <div class="news-card__media">
                                        <img
                                            :src="
                                                newsItem.image
                                                    ? `/storage/${newsItem.image}`
                                                    : 'img/image.jpg'
                                            "
                                            class="news-card__img"
                                            alt=""
                                            loading="lazy"
                                        />

                                    </div>
                                    <div class="news-card__meta">
                                        {{ formatDate(newsItem.created_at) }}
                                    </div>
                                    <h2 class="news-card__title">
                                        {{ newsItem.title }}
                                    </h2>
                                </a>
                            </article>
                        </div>
                        <div
                            v-if="totalPages > 1"
                            class="pagination-pages news__pagination"
                        >
                            <button
                                type="button"
                                :disabled="currentPage === 1"
                                @click="prevPage"
                            >
                                Назад
                            </button>
                            <button
                                v-for="page in pages"
                                :key="page"
                                type="button"
                                @click="goToPage(page)"
                                :class="{ active: currentPage === page }"
                            >
                                {{ page }}
                            </button>
                            <button
                                type="button"
                                :disabled="currentPage === totalPages"
                                @click="nextPage"
                            >
                                Вперёд
                            </button>
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
import dayjs from "dayjs";
import "dayjs/locale/ru";

dayjs.locale("ru");

import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";

// Массив для хранения всех новостей
const news = ref([]);
const pageSize = 8;
const currentPage = ref(1);
const paginatedNews = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return news.value.slice(start, start + pageSize);
});
const totalPages = computed(() =>
    Math.ceil(news.value.length / pageSize)
);
const pages = computed(() =>
    Array.from({ length: totalPages.value }, (_, i) => i + 1)
);

// Функция загрузки новостей с сервера
async function loadNews() {
    try {
        const response = await axios.get("/api/news");
        // Предполагаем, что новости приходят отсортированными по убыванию даты создания
        news.value = response.data;
    } catch (error) {
        console.error("Ошибка загрузки новостей:", error);
    }
}

function formatDate(dateStr) {
    return dayjs(dateStr).format("dddd, D MMMM YYYY");
}

function goToPage(page) {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
}
function nextPage() {
    if (currentPage.value < totalPages.value) currentPage.value += 1;
}
function prevPage() {
    if (currentPage.value > 1) currentPage.value -= 1;
}

watch(
    () => news.value.length,
    () => {
        currentPage.value = 1;
    }
);

// Загружаем новости при монтировании компонента
onMounted(() => {
    loadNews();
});
</script>

<style scoped>
.news {
    width: 100%;
    margin: 0;
    padding: 0;
}
.news__inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}
.news__header h1 {
    margin: 0 0 30px;
}
.news__grid {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 28px;
}
.news-card {
    display: flex;
    flex-direction: column;
}
.news-card__link {
    display: flex;
    flex-direction: column;
    gap: 10px;
    color: inherit;
    text-decoration: none;
}
.news-card__media {
    position: relative;
    border: 1px solid #d9d9df;
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
}
.news-card__img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    display: block;
}
.news-card__tag {
    position: absolute;
    left: 10px;
    bottom: 10px;
    background: #f39c12;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.02em;
    padding: 6px 10px;
    border-radius: 3px;
    text-transform: uppercase;
}
.news-card__meta {
    color: #6f6f76;
    font-size: 12px;
}
.news-card__title {
    font-size: 16px;
    font-weight: 700;
    line-height: 1.25;
    margin: 0;
    text-align: left;
}
.news__pagination {
    margin-top: 32px;
}
.pagination-pages {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.pagination-pages button {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ccc;
    padding: 0.5rem 0.8rem;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}
.pagination-pages button:hover:not(:disabled) {
    background-color: #e6e6e6;
}
.pagination-pages button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.pagination-pages button.active {
    background-color: #698dc9;
    color: #fff;
}

@media (max-width: 1200px) {
    .news__grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (max-width: 900px) {
    .news__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .news-card__img {
        height: 200px;
    }
}
@media (max-width: 600px) {
    .news__grid {
        grid-template-columns: 1fr;
    }
    .news-card__img {
        height: 220px;
    }
}
</style>
