<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="skill">
                    <div class="skill__title skill__title-second">
                        <div class="skill__title-inner">
                                <span class="skill__title-arrow">&larr;</span>
                                <a class="skill__title-h1" @click.prevent="goBack" >{{ course.card_title }}</a>
                            </div>
                        </div>
                    <div class="skill__inner">
                        <div class="skill__sidebar">
                            <div class="skill__sidebar-inner">
                            <!-- Прогресс курса -->
                            <div class="skill__progress">
                                <div class="skill__progress-title">
                                Прогресс курса <span>40%</span>
                                </div>
                                <div class="skill__progress-line">
                                <span style="width: 40%"></span>
                                </div>
                            </div>
                            <!-- Список тем (основное меню) -->
                            <div v-if="!selectedTopic" class="skill__sidebar-menu-main">
                                <ul class="skill__menu-main-list">
                                <li
                                    v-for="topic in topics"
                                    :key="topic.id"
                                    :class="[
                                    'skill__menu-main-item',
                                    { 'skill__menu-main-item_active': selectedTopic && selectedTopic.id === topic.id }
                                    ]"
                                >
                                    <!-- При клике выбираем тему -->
                                    <a href="#" @click.prevent="selectTopic(topic)">
                                    {{ topic.title }}
                                    </a>
                                </li>
                                </ul>
                            </div>
                            <!-- Дополнительное меню (главы), если выбрана тема -->
                            <div v-else-if="selectedTopic" class="skill__sidebar-menu-second">
                                <div class="skill__menu-second-title">
                                {{ selectedTopic.title }}
                                </div>
                                <div class="skill__menu-second-block">
                                <!-- <div class="skill__menu-second-progress"></div> -->
                                <ul class="skill__menu-second-list">
                                    <li
                                    v-for="chapter in selectedTopic.chapters"
                                    :key="chapter.id"
                                    :class="[
                                        'skill__menu-second',
                                        { 'skill__menu-second_active': selectedChapter && selectedChapter.id === chapter.id }
                                    ]"
                                    >
                                    <!-- При клике выбираем главу -->
                                    <a href="#" @click.prevent="selectChapter(chapter)">
                                        <span>{{ chapter.title }}</span>
                                    </a>
                                    </li>
                                </ul>
                                <button @click="deselectTopic" class="button button_transparent button--xl">Все темы</button>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="skill__content">
                            <div class="skill__content-inner">
                                <!-- 1) Если вообще ничего не выбрано — показываем приветствие -->
                                <div class="welcome_course" v-if="!selectedTopic">
                                    <h1>Добро пожаловать, на курс {{ course.card_title }}!</h1>
                                    <p>Выберите тему, чтобы начать изучение.</p>
                                </div>
                                <!-- 2) Если выбрана тема, но не выбрана глава — показываем что-то общее по теме -->
                                <div v-else-if="selectedTopic && !selectedChapter">
                                    <h1>{{ selectedTopic.title }}</h1>
                                    <p class="info__topic">{{ selectedTopic.description}}</p>
                                </div>
                                <div v-else>
                                    <!-- Заголовок: название темы + название главы -->
                                    <h1>{{ selectedChapter.title }}</h1>

                                    <!-- Если глава — видео -->
                                    <div v-if="selectedChapter.type === 'video'">
                                        <!-- Допустим, в БД есть поле video_url -->
                                        <iframe
                                        v-if="selectedChapter.video_url"
                                        :src="selectedChapter.video_url"
                                        controls
                                        allowfullscreen
                                        class="iframe"
                                        ></iframe>
                                        <h2>Описание</h2>
                                        <!-- Если content хранится как HTML или текст, выводим -->
                                        <div id="editorjs"></div>
                                    </div>
                                    <!-- Если глава — просто текст -->
                                    <div v-else-if="selectedChapter.type === 'text'">                                       
                                        <div id="editorjs"></div>
                                    </div>
                                    <!-- Если глава — задание (task) -->
                                    <div v-else-if="selectedChapter.type === 'task'">                                       
                                        <div id="editorjs"></div>
                                    </div>
                                    <!-- Если глава — терминология (terms) -->
                                    <div v-else-if="selectedChapter.type === 'terms'">
                                        <div id="editorjs"></div>
                                    </div>
                                    <!-- На всякий случай fallback для неизвестного типа -->
                                    <div v-else>
                                        <p>Неизвестный тип главы: {{ selectedChapter.type }}</p>
                                    </div>
                                    <!-- Общие кнопки -->
                                    
                                    <div class="skill__buttons">
                                        <!-- Кнопка "Назад" с анимацией плавного появления -->
                                        <transition name="fade">
                                        <button v-if="canGoPrev" @click="goToPrevChapter" class="button button_skill-prev">
                                            Назад
                                        </button>
                                        </transition>
                                        
                                        <!-- Кнопка "Вперёд" с анимацией сдвига вправо -->
                                        <transition name="slide-right">
                                            <button v-if="canGoNext" @click="goToNextChapter" class="button button_skill-next">
                                                Вперёд
                                            </button>
                                        </transition>
                                    </div>
                                </div>
                            </div>
                            <div class="comments">
                                <h2>Комментарии</h2>
                                <div class="comments__inner">
                                <div class="comment__title">
                                    <span class="comment__count">3</span> комментария
                                </div>
                                <div class="comment__area">
                                    <textarea placeholder="Мой комментарий..."></textarea>
                                </div>
                                <div class="comment__button">
                                    <div class="button button_comment-cancel">Отмена</div>
                                    <div class="button button_comment-send">Оставить комментарий</div>
                                </div>
                                <div class="comment__list">
                                    <div class="comment__branch">
                                        <div class="comment__one">
                                            <div class="comment__avatar">
                                                <img src="img/avatar.jpg">
                                            </div>
                                            <div class="comment__block">
                                                <div class="comment__name">Егений</div>
                                                <div class="comment__time">Два часа назад</div>
                                                <div class="comment__text">Загрузка, распаковка и прочие операции обновления выполняются в фоновом режиме, давая пользователю возможность продолжать работать с обновляемым приложением. После завершения подготовительного этапа появится предупреждение о том, что необходима перезагрузка для копирования новых файлов. Обновление применится при ближайшем закрытии инструмента.</div>
                                                <div class="comment__likes">
                                                    <div class="comment__like comment__like-good">
                                                        <span>
                                                            <svg viewBox="0 0 800 800">
                                                            <path d="M530,150c0-50-49.4-83.3-88-83.3c-26.9,0-29,20.4-33.1,60.7c-1.8,17.7-4,39.1-8.9,64.3 c-12.9,66.7-57.3,152-99.9,177.5v197.5c-0.1,75,24.9,100,133.2,100h125.8c72.5,0,90.1-47.8,96.6-65.5l0.4-1.2 c3.8-10.2,11.9-18.2,21.3-27.3c10.3-10.2,22.1-21.8,30.9-39.3c10.4-20.8,9-39.2,7.8-55.7c-0.8-10-1.5-19.2,0.6-27.7 c2.1-9,4.9-15.8,7.5-22.4c4.8-11.9,9.2-22.9,9.2-44.3c0-50-24.9-83.3-77.2-83.3H516.7C516.7,300.1,530,200,530,150z M183.3,333.3 c-27.6,0-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50s50-22.4,50-50V383.3C233.3,355.7,210.9,333.3,183.3,333.3z"/>
                                                            </svg>
                                                        </span>
                                                        <span class="comment__like-count">3</span>
                                                    </div>
                                                    <div class="comment__like comment__like-bad">
                                                        <span>
                                                            <svg viewBox="0 0 800 800" >
                                                            <path class="st0" d="M516.7,433.4h139.5c52.3,0,77.2-33.3,77.2-83.3c0-21.4-4.4-32.4-9.2-44.3c-2.6-6.6-5.4-13.4-7.5-22.4 c-2.1-8.5-1.4-17.7-0.6-27.7c1.2-16.5,2.6-34.9-7.8-55.7c-8.8-17.5-20.6-29.1-30.9-39.3c-9.4-9.1-17.5-17.1-21.3-27.3l-0.4-1.2 c-6.5-17.7-24.1-65.5-96.6-65.5H433.3c-108.3,0-133.3,25-133.2,100v197.5c42.6,25.5,87,110.8,99.9,177.5c4.9,25.2,7.1,46.6,8.9,64.3 c4.1,40.3,6.2,60.7,33.1,60.7c38.6,0,88-33.3,88-83.3S516.7,433.3,516.7,433.4z M233.3,350.1V116.8c0-27.6-22.4-50-50-50 s-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50S233.3,377.7,233.3,350.1z"/>
                                                            </svg>
                                                        </span>
                                                        <span class="comment__like-count">3</span>
                                                    </div>
                                                    <div class="comment__like-answer">Ответить</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="comment__one comment__one_respond">
                                            <div class="comment__avatar">
                                                <img src="img/avatar.jpg">
                                            </div>
                                            <div class="comment__block">
                                                <div class="comment__name">Егений</div>
                                                <div class="comment__time">Два часа назад</div>
                                                <div class="comment__text">Загрузка, распаковка и прочие операции обновления выполняются в фоновом режиме, давая пользователю возможность продолжать работать с обновляемым приложением. После завершения подготовительного этапа появится предупреждение о том, что необходима перезагрузка для копирования новых файлов. Обновление применится при ближайшем закрытии инструмента.</div>
                                                <div class="comment__likes">
                                                    <div class="comment__like comment__like-good">
                                                        <span>
                                                            <svg viewBox="0 0 800 800">
                                                            <path d="M530,150c0-50-49.4-83.3-88-83.3c-26.9,0-29,20.4-33.1,60.7c-1.8,17.7-4,39.1-8.9,64.3 c-12.9,66.7-57.3,152-99.9,177.5v197.5c-0.1,75,24.9,100,133.2,100h125.8c72.5,0,90.1-47.8,96.6-65.5l0.4-1.2 c3.8-10.2,11.9-18.2,21.3-27.3c10.3-10.2,22.1-21.8,30.9-39.3c10.4-20.8,9-39.2,7.8-55.7c-0.8-10-1.5-19.2,0.6-27.7 c2.1-9,4.9-15.8,7.5-22.4c4.8-11.9,9.2-22.9,9.2-44.3c0-50-24.9-83.3-77.2-83.3H516.7C516.7,300.1,530,200,530,150z M183.3,333.3 c-27.6,0-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50s50-22.4,50-50V383.3C233.3,355.7,210.9,333.3,183.3,333.3z"/>
                                                            </svg>
                                                        </span>
                                                        <span class="comment__like-count">3</span>
                                                    </div>
                                                    <div class="comment__like comment__like-bad">
                                                        <span>
                                                            <svg viewBox="0 0 800 800" >
                                                            <path class="st0" d="M516.7,433.4h139.5c52.3,0,77.2-33.3,77.2-83.3c0-21.4-4.4-32.4-9.2-44.3c-2.6-6.6-5.4-13.4-7.5-22.4 c-2.1-8.5-1.4-17.7-0.6-27.7c1.2-16.5,2.6-34.9-7.8-55.7c-8.8-17.5-20.6-29.1-30.9-39.3c-9.4-9.1-17.5-17.1-21.3-27.3l-0.4-1.2 c-6.5-17.7-24.1-65.5-96.6-65.5H433.3c-108.3,0-133.3,25-133.2,100v197.5c42.6,25.5,87,110.8,99.9,177.5c4.9,25.2,7.1,46.6,8.9,64.3 c4.1,40.3,6.2,60.7,33.1,60.7c38.6,0,88-33.3,88-83.3S516.7,433.3,516.7,433.4z M233.3,350.1V116.8c0-27.6-22.4-50-50-50 s-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50S233.3,377.7,233.3,350.1z"/>
                                                            </svg>
                                                        </span>
                                                        <span class="comment__like-count">3</span>
                                                    </div>
                                                    <div class="comment__like-answer">Ответить</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="comment__one comment__one_respond">
                                            <div class="comment__avatar">
                                                <img src="img/avatar.jpg">
                                            </div>
                                            <div class="comment__block">
                                                <div class="comment__area">
                                                    <textarea placeholder="Мой комментарий..."></textarea>
                                                </div>
                                                <div class="comment__button">
                                                    <div class="button button_comment-cancel">Отмена</div>
                                                    <div class="button button_comment-send">Оставить комментарий</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="comment__branch">
                                        <div class="comment__one">
                                            <div class="comment__avatar">
                                                <img src="img/avatar.jpg">
                                            </div>
                                            <div class="comment__block">
                                                <div class="comment__name">Егений</div>
                                                <div class="comment__time">Два часа назад</div>
                                                <div class="comment__text">Загрузка, распаковка и прочие операции обновления выполняются в фоновом режиме, давая пользователю возможность продолжать работать с обновляемым приложением. После завершения подготовительного этапа появится предупреждение о том, что необходима перезагрузка для копирования новых файлов. Обновление применится при ближайшем закрытии инструмента.</div>
                                                <div class="comment__likes">
                                                    <div class="comment__like comment__like-good">
                                                        <span>
                                                            <svg viewBox="0 0 800 800">
                                                            <path d="M530,150c0-50-49.4-83.3-88-83.3c-26.9,0-29,20.4-33.1,60.7c-1.8,17.7-4,39.1-8.9,64.3 c-12.9,66.7-57.3,152-99.9,177.5v197.5c-0.1,75,24.9,100,133.2,100h125.8c72.5,0,90.1-47.8,96.6-65.5l0.4-1.2 c3.8-10.2,11.9-18.2,21.3-27.3c10.3-10.2,22.1-21.8,30.9-39.3c10.4-20.8,9-39.2,7.8-55.7c-0.8-10-1.5-19.2,0.6-27.7 c2.1-9,4.9-15.8,7.5-22.4c4.8-11.9,9.2-22.9,9.2-44.3c0-50-24.9-83.3-77.2-83.3H516.7C516.7,300.1,530,200,530,150z M183.3,333.3 c-27.6,0-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50s50-22.4,50-50V383.3C233.3,355.7,210.9,333.3,183.3,333.3z"/>
                                                            </svg>
                                                        </span>
                                                        <span class="comment__like-count">3</span>
                                                    </div>
                                                    <div class="comment__like comment__like-bad">
                                                        <span>
                                                            <svg viewBox="0 0 800 800" >
                                                            <path class="st0" d="M516.7,433.4h139.5c52.3,0,77.2-33.3,77.2-83.3c0-21.4-4.4-32.4-9.2-44.3c-2.6-6.6-5.4-13.4-7.5-22.4 c-2.1-8.5-1.4-17.7-0.6-27.7c1.2-16.5,2.6-34.9-7.8-55.7c-8.8-17.5-20.6-29.1-30.9-39.3c-9.4-9.1-17.5-17.1-21.3-27.3l-0.4-1.2 c-6.5-17.7-24.1-65.5-96.6-65.5H433.3c-108.3,0-133.3,25-133.2,100v197.5c42.6,25.5,87,110.8,99.9,177.5c4.9,25.2,7.1,46.6,8.9,64.3 c4.1,40.3,6.2,60.7,33.1,60.7c38.6,0,88-33.3,88-83.3S516.7,433.3,516.7,433.4z M233.3,350.1V116.8c0-27.6-22.4-50-50-50 s-50,22.4-50,50v233.3c0,27.6,22.4,50,50,50S233.3,377.7,233.3,350.1z"/>
                                                            </svg>
                                                        </span>
                                                        <span class="comment__like-count">3</span>
                                                    </div>
                                                    <div class="comment__like-answer">Ответить</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        <footer>
                <div class="footer__inner">
                    <div class="footer__row">
                        <div class="footer__block">API</div>
                        <div class="footer__block">
                            <div class="footer__logo">
                                <img src="img/logo.png">
                            </div>
                        </div>
                    </div>
                    <div class="footer__row">© 2011-2023 гг. Сайт не является публичной офертой и носит информационный характер. Все материалы данного сайта являются объектами авторского права (в том числе дизайн). Запрещается копирование, распространение (в том числе путем копирования на другие сайты и ресурсы в Интернете) или любое иное использование информации и объектов без предварительного согласия правообладателя.</div>
                </div>
            </footer>
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
                    <form method="post" name="mtForm1" id="mtForm1" class="forma">
                        <input type="hidden" name="data_form" id="data_form" value="">
                        <input type="hidden" name="no" value="no" />
                        <div class="form-field">
                            <label>
                                <span>Ваше имя:</span>
                                <input type="text" name="name" required="required">
                            </label>
                        </div>
                        <div class="form-field">
                            <label>
                                <span>Ваш телефон:</span>
                                <input type="tel" name="phone" required="required" class="phone_valid">
                            </label>
                        </div>
                        <div class="form-field">
                            <input type="submit" name="submit" value="Отправить заявку">
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
                <div class="popup-desc">Ваша заявка успешно отправлена. <br>C вами свяжутся в ближайшее время.</div>
                <div class="close-up close-up-all">
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import EditorJS from '@editorjs/editorjs'
import Header from '@editorjs/header'
import List from '@editorjs/list'
import ImageTool from '@editorjs/image'

// Если данные курса переданы из Laravel, они будут в глобальной переменной
const course = ref(window.initialCourseData || {})
// Массив тем
const topics = ref(course.value.topics || [])
// Выбранная тема/глава
const selectedTopic = ref(null)
const selectedChapter = ref(null)

let editorInstance = null  // Ссылка на экземпляр Editor.js

// Функция для возврата назад
function goBack() {
  window.history.back()
}

// Функция для выбора темы
function selectTopic(topic) {
  console.log('Выбрали тему:', topic)
  selectedTopic.value = topic
  selectedChapter.value = null
  destroyEditor() // уничтожаем редактор при смене темы
}
function deselectTopic() {
  // Возвращаемся к списку тем
  selectedTopic.value = null
  selectedChapter.value = null
  destroyEditor()
}
// Функция для выбора главы
function selectChapter(chapter) {
  console.log('Выбрали главу:', chapter)
  selectedChapter.value = chapter
}

// Функция для переключения на предыдущую главу
function goToPrevChapter() {
  if (selectedTopic.value && selectedTopic.value.chapters && selectedChapter.value) {
    const chapters = selectedTopic.value.chapters
    const index = chapters.findIndex(ch => ch.id === selectedChapter.value.id)
    if (index > 0) {
      selectChapter(chapters[index - 1])
    }
  }
}

// Функция для переключения на следующую главу
function goToNextChapter() {
  if (selectedTopic.value && selectedTopic.value.chapters && selectedChapter.value) {
    const chapters = selectedTopic.value.chapters
    const index = chapters.findIndex(ch => ch.id === selectedChapter.value.id)
    if (index < chapters.length - 1) {
      selectChapter(chapters[index + 1])
    }
  }
}

// Вычисляемые свойства для управления видимостью кнопок в шаблоне
const canGoPrev = computed(() => {
  if (!selectedTopic.value || !selectedTopic.value.chapters || !selectedChapter.value) return false
  const chapters = selectedTopic.value.chapters
  const index = chapters.findIndex(ch => ch.id === selectedChapter.value.id)
  return index > 0
})
const canGoNext = computed(() => {
  if (!selectedTopic.value || !selectedTopic.value.chapters || !selectedChapter.value) return false
  const chapters = selectedTopic.value.chapters
  const index = chapters.findIndex(ch => ch.id === selectedChapter.value.id)
  return index < chapters.length - 1
})

// Универсальная функция для уничтожения старого экземпляра Editor.js
function destroyEditor() {
  if (editorInstance && typeof editorInstance.destroy === 'function') {
    console.log('Уничтожаем предыдущий экземпляр Editor.js')
    editorInstance.destroy()
    editorInstance = null
  }
}

// Функция для инициализации Editor.js в режиме read-only
function initEditor(contentData) {
  console.log('initEditor: пришли данные контента:', contentData)

  // 1. Уничтожаем старый экземпляр, если есть
  destroyEditor()

  // 2. Если contentData — строка, попробуем распарсить JSON
  if (typeof contentData === 'string') {
    try {
      contentData = JSON.parse(contentData)
      console.log('initEditor: успешно распарсили JSON:', contentData)
    } catch (error) {
      console.error('Ошибка парсинга JSON контента главы:', error)
      contentData = {}
    }
  }

  // 3. Создаём новый Editor.js
  editorInstance = new EditorJS({
    holder: 'editorjs',    // ID контейнера в шаблоне
    readOnly: true,        // Только чтение
    data: contentData,     // JSON-данные Editor.js (должен содержать blocks)
    tools: {
      header: {
        class: Header,
        inlineToolbar: ['link'],
      },
      list: {
        class: List,
        inlineToolbar: true,
      },
      image: {
        class: ImageTool,
        config: {
          endpoints: {
            byFile: '/api/uploadFile', // ваш маршрут на бэкенде
            byUrl: '/api/fetchUrl',    // ваш маршрут на бэкенде
          },
        },
      },
    },
  })

  console.log('EditorJS инициализирован в режиме read-only.')
}

// Слежение за изменением выбранной главы.
// Если выбранная глава имеет тип text, task или terms – инициализируем Editor.js
watch(selectedChapter, (newChapter) => {
  console.log('watch selectedChapter -> newChapter:', newChapter)

  if (newChapter && newChapter.content) {
    console.log('Тип главы:', newChapter.type, 'Содержимое content:', newChapter.content)

    if (newChapter.type === 'text' || newChapter.type === 'task' || newChapter.type === 'terms') {
      initEditor(newChapter.content)
    } else if (newChapter.type === 'video') {
      console.log('Глава с видео. Editor.js не нужен, уничтожаем старый экземпляр.')
      destroyEditor()
    } else {
      console.warn('Неизвестный тип главы:', newChapter.type, '— уничтожаем Editor.js.')
      destroyEditor()
    }
  } else {
    // Если нет выбранной главы или нет content – уничтожаем Editor.js, если он существует.
    console.log('Глава не выбрана или нет поля content. Уничтожаем Editor.js.')
    destroyEditor()
  }
})

// Если данные курса не переданы, выполняем запрос через axios
onMounted(async () => {
  if (!course.value.id) {
    // Извлекаем courseId из URL, если необходимо
    const parts = window.location.pathname.split('/')
    const courseId = parts[parts.length - 1]
    console.log('onMounted: извлекли courseId:', courseId)

    try {
      const response = await axios.get(`/api/course/${courseId}/topics`)
      topics.value = response.data.topics || []
      // Если нужно, можно дополнительно сохранить данные курса
      course.value = response.data.course || {}
      console.log('Успешно загрузили темы курса:', topics.value)
    } catch (error) {
      console.error('Ошибка при загрузке тем курса:', error)
    }
  } else {
    console.log('Данные курса уже переданы через window.initialCourseData.')
  }
})
</script>


<style scoped>
.iframe{
    width: 900px;
    max-width: 900px;
    height: 600px;
}
.button{
    border: none;
}
.button_skill-next {
    display: inline-block;
  transition: transform 0.5s ease, opacity 0.5s ease;
  transform: translateX(0);
  opacity: 1;
}
.button_skill-prev.hidden,
.button_skill-next.hidden {
  display: none; /* или visibility: hidden */
}
.button--xl{
    margin-top: 20px;
    color: #000;
    width: 75%;
    max-width: unset;
}
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}
.fade-enter-active {
  animation: fadeIn 0.5s ease forwards;
}
.fade-leave-active {
  animation: fadeOut 0.5s ease forwards;
}
.slide-right-enter-active {
  animation: slideInRight 1s ease forwards;
}
.slide-right-leave-active {
  animation: slideOutRight 1s ease forwards;
}
/* Анимация для кнопки "Вперёд" через keyframes */
@keyframes slideInRight {
  from {
    transform: translateX(50px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(50px);
    opacity: 0;
  }
}


.chapter__title{
    text-align: center;
}
.info__topic{
    text-align: center;
}
.skill__content h1 {
    margin-bottom: 40px;
    text-align: left;
    font-size: 2.1em;
    text-align: center;
}
.welcome_course{
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 50vh;
}
.skill__menu-main-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
</style>