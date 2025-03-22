<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="content">
          <!-- Вывод данных новости, если они загружены -->
          <div class="content__inner" v-if="newsItem">
            <h1>{{ newsItem.title }}</h1>
            <div class="content__data">
              {{ dayjs(newsItem.created_at).fromNow() }}
            </div>
            <!-- Отображаем изображение новости, если оно есть -->
            <img
              v-if="newsItem.image"
              :src="`/storage/${newsItem.image}`"
              class="content__img content__img_big"
              alt="News Image"
              width="885"
              height="515"
            />
            <!-- Вывод основного текста новости -->
            <p>{{ newsItem.content }}</p>
            <!-- Контейнер для EditorJS (отображается контент, если он есть) -->
            <div id="editorjs-news"></div>
          </div>

          <!-- Блок комментариев -->
          <div class="comments" v-if="newsItem">
            <div class="comments__inner">
              <div class="comment__title">
                <span class="comment__count">
                  {{ newsItem.comments ? newsItem.comments.length : 0 }}
                </span>
                комментариев
              </div>
              <div class="comment__area">
                <textarea v-model="newComment" placeholder="Мой комментарий..."></textarea>
              </div>
              <div class="comment__button">
                <div class="button button_comment-cancel" @click="newComment = ''">
                  Отмена
                </div>
                <div class="button button_comment-send" @click="submitComment">
                  Оставить комментарий
                </div>
              </div>
                <div class="comment__list">
                    <!-- Вывод списка комментариев -->
                    <div class="comment__branch" v-for="comment in newsItem.comments" :key="comment.id">
    <!-- Родительский комментарий -->
                        <div class="comment__one">
                            <div class="comment__avatar">
                            <img
                                :src="comment.user_avatar ? `/storage/${comment.user_avatar}` : 'img/avatar.jpg'"
                                alt="Avatar"
                            />
                            </div>
                            <div class="comment__block">
                            <div class="comment__name">
                                {{ comment.user_name ? comment.user_name : currentUserName }}
                            </div>
                            <div class="comment__time">
                                {{ dayjs(comment.created_at).fromNow() }}
                            </div>
                            <div class="comment__text">
                                {{ comment.body }}
                            </div>
                            <div class="comment__likes">
                                <!-- Лайк -->
                                <div
                                class="comment__like comment__like-good"
                                :class="{ active: userVotes[comment.id] === 'like' }"
                                @click="likeComment(comment)"
                                >
                                <span>
                                    <svg viewBox="0 0 800 800">
                                    <path d="M530,150c0-50-49.4-83.3-88-83.3c-26.9,0-29,20.4-33.1,60.7c-1.8,17.7-4,39.1-8.9,64.3 c-12.9,66.7-57.3,152-99.9,177.5v197.5c-0.1,75,24.9,100,133.2,100h125.8c72.5,0,90.1-47.8,96.6-65.5l0.4-1.2 c3.8-10.2,11.9-18.2,21.3-27.3c10.3-10.2,22.1-21.8,30.9-39.3c10.4-20.8,9-39.2,7.8-55.7c-0.8-10-1.5-19.2,0.6-27.7 c2.1-9,4.9-15.8,7.5-22.4c4.8-11.9,9.2-22.9,9.2-44.3c0-50-24.9-83.3-77.2-83.3H516.7C516.7,300.1,530,200,530,150z M183.3,333.3 c-27.6,0-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50s50-22.4,50-50V383.3C233.3,355.7,210.9,333.3,183.3,333.3z"/>
                                    </svg>
                                </span>
                                <span class="comment__like-count">{{ comment.likes }}</span>
                                </div>
                                <!-- Дизлайк -->
                                <div
                                class="comment__like comment__like-bad"
                                :class="{ active: userVotes[comment.id] === 'dislike' }"
                                @click="dislikeComment(comment)"
                                >
                                <span>
                                    <svg viewBox="0 0 800 800" >
                                    <path class="st0" d="M516.7,433.4h139.5c52.3,0,77.2-33.3,77.2-83.3c0-21.4-4.4-32.4-9.2-44.3c-2.6-6.6-5.4-13.4-7.5-22.4 c-2.1-8.5-1.4-17.7-0.6-27.7c1.2-16.5,2.6-34.9-7.8-55.7c-8.8-17.5-20.6-29.1-30.9-39.3c-9.4-9.1-17.5-17.1-21.3-27.3l-0.4-1.2 c-6.5-17.7-24.1-65.5-96.6-65.5H433.3c-108.3,0-133.3,25-133.2,100v197.5c42.6,25.5,87,110.8,99.9,177.5c4.9,25.2,7.1,46.6,8.9,64.3 c4.1,40.3,6.2,60.7,33.1,60.7c38.6,0,88-33.3,88-83.3S516.7,433.3,516.7,433.4z M233.3,350.1V116.8c0-27.6-22.4-50-50-50 s-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50S233.3,377.7,233.3,350.1z"/>
                                    </svg>
                                </span>
                                <span class="comment__like-count">{{ comment.dislikes }}</span>
                                </div>
                                <!-- Кнопка "Ответить" -->
                                <div class="comment__like-answer" @click="replyToComment(comment)">
                                Ответить
                                </div>
                            </div>
                            </div>
                        </div>

                        <!-- Форма ответа (подкомментария) для этого комментария -->
                        <div v-if="replyTo === comment.id" class="comment__one comment__one_respond">
                            <div class="comment__avatar">
                            <img src="img/avatar.jpg" alt="Avatar" />
                            </div>
                            <div class="comment__block">
                            <div class="comment__area">
                                <textarea v-model="replyComment" placeholder="Мой комментарий..."></textarea>
                            </div>
                            <div class="comment__button">
                                <div class="button button_comment-cancel" @click="cancelReply">Отмена</div>
                                <div class="button button_comment-send" @click="submitReply(comment)">Оставить комментарий</div>
                            </div>
                            </div>
                        </div>

                        <!-- Подкомментарии (массив comment.children) -->
                        <div
                            class="comment__one comment__one_respond"
                            v-for="child in comment.children"
                            :key="child.id"
                            >
                            <div class="comment__avatar">
                                <img
                                :src="child.user_avatar ? `/storage/${child.user_avatar}` : 'img/avatar.jpg'"
                                alt="Avatar"
                                />
                            </div>
                            <div class="comment__block">
                                <div class="comment__name">
                                {{ child.user_name ? child.user_name : currentUserName }}
                                </div>
                                <div class="comment__time">
                                {{ dayjs(child.created_at).fromNow() }}
                                </div>
                                <div class="comment__text">
                                {{ child.body }}
                                </div>
                                <div class="comment__likes">
                                <!-- Лайк для child -->
                                <div
                                    class="comment__like comment__like-good"
                                    :class="{ active: userVotes[child.id] === 'like' }"
                                    @click="likeComment(child)"
                                >
                                    <span>
                                        <svg viewBox="0 0 800 800">
                                            <path d="M530,150c0-50-49.4-83.3-88-83.3c-26.9,0-29,20.4-33.1,60.7c-1.8,17.7-4,39.1-8.9,64.3 c-12.9,66.7-57.3,152-99.9,177.5v197.5c-0.1,75,24.9,100,133.2,100h125.8c72.5,0,90.1-47.8,96.6-65.5l0.4-1.2 c3.8-10.2,11.9-18.2,21.3-27.3c10.3-10.2,22.1-21.8,30.9-39.3c10.4-20.8,9-39.2,7.8-55.7c-0.8-10-1.5-19.2,0.6-27.7 c2.1-9,4.9-15.8,7.5-22.4c4.8-11.9,9.2-22.9,9.2-44.3c0-50-24.9-83.3-77.2-83.3H516.7C516.7,300.1,530,200,530,150z M183.3,333.3 c-27.6,0-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50s50-22.4,50-50V383.3C233.3,355.7,210.9,333.3,183.3,333.3z"/>
                                        </svg>
                                    </span>
                                    <span class="comment__like-count">{{ child.likes }}</span>
                                </div>
                                <!-- Дизлайк для child -->
                                <div
                                    class="comment__like comment__like-bad"
                                    :class="{ active: userVotes[child.id] === 'dislike' }"
                                    @click="dislikeComment(child)"
                                >
                                    <span>
                                        <svg viewBox="0 0 800 800" >
                                            <path class="st0" d="M516.7,433.4h139.5c52.3,0,77.2-33.3,77.2-83.3c0-21.4-4.4-32.4-9.2-44.3c-2.6-6.6-5.4-13.4-7.5-22.4 c-2.1-8.5-1.4-17.7-0.6-27.7c1.2-16.5,2.6-34.9-7.8-55.7c-8.8-17.5-20.6-29.1-30.9-39.3c-9.4-9.1-17.5-17.1-21.3-27.3l-0.4-1.2 c-6.5-17.7-24.1-65.5-96.6-65.5H433.3c-108.3,0-133.3,25-133.2,100v197.5c42.6,25.5,87,110.8,99.9,177.5c4.9,25.2,7.1,46.6,8.9,64.3 c4.1,40.3,6.2,60.7,33.1,60.7c38.6,0,88-33.3,88-83.3S516.7,433.3,516.7,433.4z M233.3,350.1V116.8c0-27.6-22.4-50-50-50 s-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50S233.3,377.7,233.3,350.1z"/>
                                        </svg>
                                    </span>
                                    <span class="comment__like-count">{{ child.dislikes }}</span>
                                </div>
                                <!-- Кнопка "Ответить" для child (если хотите вложенные ответы на ответы) -->
                                <div class="comment__like-answer" @click="replyToComment(child)">
                                    Ответить
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            
              </div>
            </div>
          </div>
        </section>
            </div>
            <footer>
                <div class="footer__inner">
                    <div class="footer__row">
                        <div class="footer__block">API</div>
                        <div class="footer__block">
                            <div class="footer__logo">
                                <img src="img/logo.png" />
                            </div>
                        </div>
                    </div>
                    <div class="footer__row">
                        © 2011-2023 гг. Сайт не является публичной офертой и
                        носит информационный характер. Все материалы данного
                        сайта являются объектами авторского права (в том числе
                        дизайн). Запрещается копирование, распространение (в том
                        числе путем копирования на другие сайты и ресурсы в
                        Интернете) или любое иное использование информации и
                        объектов без предварительного согласия правообладателя.
                    </div>
                </div>
            </footer>
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
import { ref, onMounted } from "vue";
import axios from "axios";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";

dayjs.extend(relativeTime);
dayjs.locale("ru");

// Извлечение ID из URL
function getParameterByName(name, url = window.location.href) {
  name = name.replace(/[\[\]]/g, "\\$&");
  const regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return "";
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}

const newsId = getParameterByName("id");

// Основные реактивные переменные
const newsItem = ref(null);
const currentUserName = ref("Аноним");
const newComment = ref("");
const replyTo = ref(null);     // ID комментария, на который отвечаем
const replyComment = ref("");  // Текст подкомментария

let editorNews = null;

// userVotes: ключ – ID комментария, значение: 'like' или 'dislike'
const userVotes = ref({});

/**
 * Загрузка новости и инициализация EditorJS
 */
async function loadSingleNews() {
  try {
    const response = await axios.get(`/api/news-single/${newsId}`);
    newsItem.value = response.data;

    // Парсим editor_data, если есть
    let editorData = {};
    if (newsItem.value.editor_data) {
      try {
        editorData = JSON.parse(newsItem.value.editor_data);
      } catch (e) {
        console.error("Ошибка парсинга editor_data:", e);
      }
    }

    // Инициализация EditorJS
    editorNews = new EditorJS({
      holder: "editorjs-news",
      data: editorData,
      tools: {
        header: { class: Header, inlineToolbar: ["link"] },
        list: { class: List, inlineToolbar: true },
        image: {
          class: ImageTool,
          config: {
            endpoints: {
              byFile: "/api/uploadFile",
              byUrl: "/api/fetchUrl",
            },
          },
        },
      },
      readOnly: true,
    });
  } catch (error) {
    console.error("Ошибка при загрузке новости:", error);
  }
}

/**
 * Отправка основного комментария
 */
async function submitComment() {
  if (!newComment.value.trim()) return;
  try {
    const payload = {
      news_id: newsId,
      body: newComment.value,
    };
    // Если пользователь авторизован, добавляем user_name
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      const parsedUser = JSON.parse(storedUser);
      if (parsedUser && parsedUser.name) {
        payload.user_name = parsedUser.name;
      }
    }
    const response = await axios.post("/api/comments", payload);
    if (newsItem.value.comments) {
      newsItem.value.comments.push(response.data);
    } else {
      newsItem.value.comments = [response.data];
    }
    newComment.value = "";
  } catch (error) {
    console.error("Ошибка при отправке комментария:", error);
  }
}

/**
 * Клик на "Ответить": устанавливаем replyTo = item.id
 */
function replyToComment(item) {
  replyTo.value = item.id;
  replyComment.value = "";
}

/**
 * Отправка ответа (подкомментария)
 */
async function submitReply(parentComment) {
  if (!replyComment.value.trim()) return;
  try {
    const payload = {
      news_id: newsId,
      body: replyComment.value,
      parent_id: parentComment.id, // Указываем, что это подкомментарий
    };
    // Если пользователь авторизован, добавляем user_name
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      const parsedUser = JSON.parse(storedUser);
      if (parsedUser && parsedUser.name) {
        payload.user_name = parsedUser.name;
      }
    }
    const response = await axios.post("/api/comments", payload);
    // Если у родителя нет массива children, создаем
    if (!parentComment.children) {
      parentComment.children = [];
    }
    // Добавляем полученный подкомментарий
    parentComment.children.push(response.data);
    // Сбрасываем форму
    replyComment.value = "";
    replyTo.value = null;
  } catch (error) {
    console.error("Ошибка при отправке ответа:", error);
  }
}

/**
 * Отмена ответа
 */
function cancelReply() {
  replyComment.value = "";
  replyTo.value = null;
}

/**
 * Лайк (оптимистичное обновление)
 */
async function likeComment(comment) {
  const prevVote = userVotes.value[comment.id];
  if (prevVote === "like") {
    updateLocalCommentOptimistic(comment.id, "likes", -1);
    try {
      await axios.post(`/api/comments/${comment.id}/unlike`);
      userVotes.value[comment.id] = undefined;
    } catch (error) {
      console.error("Ошибка при отмене лайка:", error);
      updateLocalCommentOptimistic(comment.id, "likes", 1);
    }
  } else if (prevVote === "dislike") {
    updateLocalCommentOptimistic(comment.id, "dislikes", -1);
    updateLocalCommentOptimistic(comment.id, "likes", 1);
    try {
      await axios.post(`/api/comments/${comment.id}/undislike`);
      await axios.post(`/api/comments/${comment.id}/like`);
      userVotes.value[comment.id] = "like";
    } catch (error) {
      console.error("Ошибка при переключении с дизлайка на лайк:", error);
      updateLocalCommentOptimistic(comment.id, "dislikes", 1);
      updateLocalCommentOptimistic(comment.id, "likes", -1);
    }
  } else {
    updateLocalCommentOptimistic(comment.id, "likes", 1);
    try {
      await axios.post(`/api/comments/${comment.id}/like`);
      userVotes.value[comment.id] = "like";
    } catch (error) {
      console.error("Ошибка при лайке комментария:", error);
      updateLocalCommentOptimistic(comment.id, "likes", -1);
    }
  }
  saveUserVotes();
}

/**
 * Дизлайк (оптимистичное обновление)
 */
async function dislikeComment(comment) {
  const prevVote = userVotes.value[comment.id];
  if (prevVote === "dislike") {
    updateLocalCommentOptimistic(comment.id, "dislikes", -1);
    try {
      await axios.post(`/api/comments/${comment.id}/undislike`);
      userVotes.value[comment.id] = undefined;
    } catch (error) {
      console.error("Ошибка при отмене дизлайка:", error);
      updateLocalCommentOptimistic(comment.id, "dislikes", 1);
    }
  } else if (prevVote === "like") {
    updateLocalCommentOptimistic(comment.id, "likes", -1);
    updateLocalCommentOptimistic(comment.id, "dislikes", 1);
    try {
      await axios.post(`/api/comments/${comment.id}/unlike`);
      await axios.post(`/api/comments/${comment.id}/dislike`);
      userVotes.value[comment.id] = "dislike";
    } catch (error) {
      console.error("Ошибка при переключении с лайка на дизлайк:", error);
      updateLocalCommentOptimistic(comment.id, "likes", 1);
      updateLocalCommentOptimistic(comment.id, "dislikes", -1);
    }
  } else {
    updateLocalCommentOptimistic(comment.id, "dislikes", 1);
    try {
      await axios.post(`/api/comments/${comment.id}/dislike`);
      userVotes.value[comment.id] = "dislike";
    } catch (error) {
      console.error("Ошибка при дизлайке комментария:", error);
      updateLocalCommentOptimistic(comment.id, "dislikes", -1);
    }
  }
  saveUserVotes();
}

/**
 * Рекурсивный поиск комментария (или подкомментария) по ID
 */
function findCommentByIdInTree(commentId, commentsArray) {
  for (let c of commentsArray) {
    if (c.id === commentId) {
      return c;
    }
    if (c.children) {
      const found = findCommentByIdInTree(commentId, c.children);
      if (found) return found;
    }
  }
  return null;
}

/**
 * Оптимистичное обновление поля (likes/dislikes) в дереве комментариев
 */
function updateLocalCommentOptimistic(commentId, field, delta) {
  if (!newsItem.value || !newsItem.value.comments) return;
  const target = findCommentByIdInTree(commentId, newsItem.value.comments);
  if (target) {
    target[field] += delta;
  }
}

/**
 * Сохранение голосов в localStorage
 */
function saveUserVotes() {
  localStorage.setItem("userVotes", JSON.stringify(userVotes.value));
}

onMounted(() => {
  // Проверяем, есть ли имя пользователя
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    const parsedUser = JSON.parse(storedUser);
    if (parsedUser && parsedUser.name) {
      currentUserName.value = parsedUser.name;
    }
  }
  // Загружаем голоса
  const storedVotes = localStorage.getItem("userVotes");
  if (storedVotes) {
    userVotes.value = JSON.parse(storedVotes);
  }
  loadSingleNews();
});
</script>







<style scoped>
@media (max-width: 991px) {
    .content__img_big{
        width: 100%;
        height: 100%;
    }
}
.ce-block__content{
  max-width: unset !important;
}
.comment__one_respond {
    margin: 0 0 35px 90px;
}
.content__img_big {
    border-radius: 60px;
}
.comment__like svg {
    fill: #C1C2C5;
    width: 21px;
    transition: .3s;
}

.comment__like.active svg {
    fill: #7173c8;
}

</style>
