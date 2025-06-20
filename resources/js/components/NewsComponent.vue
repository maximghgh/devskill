<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="content content_news">
                    <div class="content__inner">
                        <h1>Новости</h1>
                    </div>
                    <div
                        v-for="newsItem in latestNews"
                        :key="newsItem.id"
                        class="content__inner"
                    >
                        <h2>{{ newsItem.title }}</h2>
                        <div class="content__data">
                            {{ dayjs(newsItem.created_at).fromNow() }}
                        </div>
                        <img
                            :src="
                                newsItem.image
                                    ? `/storage/${newsItem.image}`
                                    : 'img/image.jpg'
                            "
                            class="content__img content__img_big"
                            width="885"
                            height="515"
                        />
                        <p v-html="newsItem.content"></p>
                        <div class="content__button">
                            <a
                                :href="`/news-single?id=${newsItem.id}`"
                                class="button button__content"
                                >Читать подробнее</a
                            >
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
import relativeTime from "dayjs/plugin/relativeTime";
import "dayjs/locale/ru";

dayjs.extend(relativeTime);
dayjs.locale("ru");

import { ref, computed, onMounted } from "vue";
import axios from "axios";

// Массив для хранения всех новостей
const news = ref([]);

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

// computed-свойство, которое возвращает только 3 последних новости
const latestNews = computed(() => {
    return news.value.slice(0, 3);
});

// Загружаем новости при монтировании компонента
onMounted(() => {
    loadNews();
});
</script>

<style scoped>
.content p {
    display: block;
}
.content__img_big {
    border-radius: 60px;
}
@media (max-width: 991px) {
    .content__img_big {
        width: 100%;
        height: 250px;
    }
    .content__button{
        margin: 0 0 70px;
    }
}
@media (max-width: 370px) {
    .content__img_big {
        border-radius: 15px;
    }
    .content p {
        text-align: justify;
        text-indent: 23px;
        font-size: 0.7em;
        line-height: 1.2em;
    }
}
</style>
