<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="offer offer_about">
                    <div class="offer__inner offer__inner_main">
                        <h1>
                            О нас
                        </h1>
                        <p class="offer__desc intro__text">
                            <span>
                                Хочешь сдать ОГЭ/ЕГЭ на высокий балл
                                и поступить в ИжГТУ?
                            </span>
                            <span>Начни готовиться уже сейчас!</span>
                            <span>
                                Курсы для школьников от ИжГТУ имени
                                М.Т. Калашникова — это возможность учиться
                                у тех, кто знает экзамен изнутри
                                и учит студентов.
                            </span>
                        </p>
                    </div>
                </section>
                <section class="about-article">
                  <div class="metrics-grid">
                    <div class="metric-card">
                      <p class="metric-value">{{ stats.courses }}</p>
                      <p class="metric-label">курсов</p>
                    </div>
                    <div class="metric-card">
                      <p class="metric-value">{{ stats.experts }}</p>
                      <p class="metric-label">экспертов</p>
                    </div>
                    <div class="metric-card">
                      <p class="metric-value">{{ stats.directions }}</p>
                      <p class="metric-label">направлений</p>
                    </div>
                  </div>
                </section>
                <!-- Что мы предлагаем -->
                <section class="offers-section">
                    <h2 class="section-title">Что мы предлагаем</h2>
                    <ul class="offers-list">
                        <li v-for="(item, i) in offers" :key="i" class="offers-item">
                            {{ item }}
                        </li>
                    </ul>
                </section>

                <!-- Направленность -->
                <section class="directions-section">
                    <h2 class="section-title">Направленность</h2>
                    <div class="directions-grid">
                        <article
                            v-for="direction in directionGroups"
                            :key="direction.name"
                            class="direction-card"
                        >
                            <h3 class="direction-card__title">{{ direction.name }}</h3>
                            <ul class="direction-card__list">
                                <li
                                    v-for="course in direction.courses"
                                    :key="course.title"
                                    class="direction-card__item"
                                >
                                    {{ course.title }}
                                    <span v-if="course.note" class="direction-card__note">
                                        {{ course.note }}
                                    </span>
                                </li>
                            </ul>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({
  courses:    0,
  experts:    0,
  directions: 0,
});

const offers = [
  "Программы для 5–11 классов",
  "Подготовка к ОГЭ и ЕГЭ по русскому языку, математике, физике и информатике",
  "Погружение в профессию задолго до поступления",
  "Уверенность, знания и высокие баллы",
];

const directionGroups = [
  {
    name: "Программирование",
    courses: [
      { title: "Введение в программирование на языках C и C++" },
      { title: "WEB-разработка" },
      { title: "Основы программирования C и C++. Базовый уровень" },
      { title: "Основы программирования C и C++. Фундаментальный уровень" },
      { title: "Основы программирования C и C++. Олимпиадный уровень (3 года обучения)" },
      { title: "Информационные технологии и программирование" },
      { title: "Основы программирования на Python" },
      { title: "Проектная деятельность на Python" },
    ],
  },
  {
    name: "Системное администрирование",
    courses: [
      { title: "Основы сетевых технологий" },
      { title: "Основы кибербезопасности" },
      { title: "Linux" },
    ],
  },
  {
    name: "Подготовка к ОГЭ",
    courses: [
      { title: "Подготовка к ОГЭ. Информатика" },
      { title: "Подготовка к ОГЭ. Математика" },
      { title: "Подготовка к ОГЭ. Русский язык" },
      { title: "Подготовка к ОГЭ. Физика" },
    ],
  },
  {
    name: "Подготовка к ЕГЭ",
    courses: [
      { title: "Подготовка к ЕГЭ. Информатика" },
      { title: "Подготовка к ЕГЭ. Математика" },
      { title: "Подготовка к ЕГЭ. Русский язык" },
      { title: "Подготовка к ЕГЭ. Физика" },
      { title: "Подготовка к творческому экзамену" },
    ],
  },
  {
    name: "Технико-прикладная",
    courses: [
      { title: "Основы оружейного дела (3 года обучения)" },
      {
        title: "Физико-технические основы стрелкового оружия",
        note: "курс в разработке",
      },
      { title: "Основы дизайна: графический дизайн, 3D-моделирование, 3D-печать" },
    ],
  },
  {
    name: "Общеобразовательные",
    courses: [
      { title: "Игра Го. Правила и теория для начинающих — стратегическое мышление, анализ, самоконтроль" },
      { title: "Программа по формированию цифровых навыков — широкий набор цифровых умений без узкой специализации" },
      { title: "Олимпиадная математика — нестандартное мышление, логика, культура рассуждений" },
      { title: "Методы решения физических задач — углубление понимания физики и развитие исследовательских навыков" },
      { title: "Общение без границ и потерь: слышать других, оставаясь собой — социально-коммуникативные навыки, эмпатия, уверенная коммуникация" },
      { title: "Подготовка к творческому экзамену — развитие креативности и навыков презентации идей" },
    ],
  },
];

// при монтировании забираем цифры
onMounted(async () => {
  try {
    const { data } = await axios.get('/api/stats');
    stats.value = data;
  } catch (err) {
    console.error('Не удалось загрузить статистику:', err);
  }
});
</script>

<style scoped>
.about-article,
.offers-section,
.directions-section {
  box-sizing: border-box;
  max-width: var(--max-width);
  padding: var(--padding);
  margin-left: auto;
  margin-right: auto;
}

.section-title {
  font-size: 2.25rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 32px;
}

/* Что мы предлагаем */
.offers-section {
  margin-bottom: 80px;
}
.offers-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
  list-style: none;
  margin: 0;
  padding: 0;
}
.offers-item {
  position: relative;
  padding: 20px 20px 20px 56px;
  border-radius: 14px;
  background-color: #f1f0fa;
  font-size: 1.0625rem;
  line-height: 1.5;
  color: #2b2b3a;
}
.offers-item::before {
  content: "✓";
  position: absolute;
  left: 20px;
  top: 20px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: #7a2abd;
  color: #ffffff;
  font-size: 14px;
  line-height: 24px;
  text-align: center;
}

/* Направленность */
.directions-section {
  margin-bottom: 80px;
}
.directions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 24px;
}
.direction-card {
  padding: 28px 24px;
  border-radius: 18px;
  background-color: #ffffff;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}
.direction-card__title {
  margin: 0 0 16px;
  font-size: 1.25rem;
  font-weight: 600;
  color: #4e187b;
}
.direction-card__list {
  margin: 0;
  padding: 0 0 0 20px;
}
.direction-card__item {
  margin-bottom: 10px;
  font-size: 1rem;
  line-height: 1.5;
  color: #4b5563;
}
.direction-card__item:last-child {
  margin-bottom: 0;
}
.direction-card__note {
  display: inline-block;
  margin-left: 6px;
  padding: 2px 8px;
  border-radius: 10px;
  background-color: #f0ecf7;
  font-size: 0.8125rem;
  color: #7a2abd;
  white-space: nowrap;
}

@media (max-width: 768px) {
  .section-title {
    font-size: 1.75rem;
    margin-bottom: 24px;
  }
  .offers-section,
  .directions-section {
    margin-bottom: 56px;
  }
}
.metrics-grid {
  margin: 0 0 95px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
}
.metric-card {
  background-color: #f9fafb; /* почти белый */
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  text-align: center;
}
.metric-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #2563eb;
  margin: 0;
}
.metric-label {
  margin-top: 8px;
  font-size: 1rem;
  color: #6b7280; /* серый */
}

.about-page {
    font-family: "Arial", sans-serif;
    color: #333;
    background-color: #fff;
}

/* MAIN CONTENT */
.main-content {
    margin: 40px 0;
}

/* Интро */
.intro {
    text-align: center;
    margin-bottom: 40px;
}
.intro__text {
    max-width: 800px;
}
.intro__text span {
    display: block;
}

/* Статья */
.about-article {
    display: flex;
    flex-direction: column;
    gap: 40px;
}
.article-block {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 20px;
}
.article-img {
    max-width: 100%;
    border-radius: 8px;
}
.article-img--left {
    order: 1;
    flex: 1 1 300px;
}
.article-img--right {
    order: 2;
    flex: 1 1 300px;
}
.article-text {
    flex: 2 1 500px;
    font-size: 18px;
    line-height: 1.7;
    text-align: justify;
}
</style>
