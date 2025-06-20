<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="offer offer_about">
                    <div class="offer__inner">
                        <h1 class="intro__title">
                            Об образовательном портале
                        </h1>
                        <p class="intro__text">
                            В современном мире, где технологический прогресс
                            неуклонно продвигается вперед, образование играет
                            всё более важную роль. Возможность получить
                            качественное образование становится доступной для
                            все большего числа людей благодаря развитию
                            онлайн-образования. В этом контексте образовательные
                            порталы предоставляют уникальные возможности для
                            саморазвития и приобретения новых навыков из любой
                            точки мира.
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
                <!-- Features Section -->
                <section class="features-section">
                    <div class="container">
                        <h2 class="features-title">Преимущества платформы</h2>
                        <div class="features-grid">
                            <div class="feature-card">
                                <img src="../../assets/img/image_3.jpg" alt="24/7 доступ" class="feature-icon">
                                <h3 class="feature-name">Инновационная платформа</h3>
                                <p class="feature-desc">
                                Один из таких порталов. Это инновационная платформа, объединяющая специалистов из разных областей информационных технологий и предоставляющая широкий спектр образовательных материалов и курсов. становится незаменимым инструментом для всех, кто желает расширить свои знания и умения в IT-сфере. Благодаря удобному интерфейсу и доступности материалов на различных языках, каждый может выбирать подходящие курсы и изучать их в любое время и из любой точки мира.
                                </p>
                            </div>
                            <div class="feature-card">
                                <img src="../../assets/img/image_2.jpg" alt="Портал" class="feature-icon">
                                <h3 class="feature-name">Обучение, практика и сертификат в одном месте</h3>
                                <p class="feature-desc">
                                Портал открывает двери в мир новых возможностей и перспектив. Здесь можно получить качественное образование от лучших экспертов, развивать свои навыки и применять их на практике. Одной из особенностей платформы является возможность получения сертификата по окончании курса, подтверждающего уровень знаний. Институтом образовательных технологий — это не только место для обучения, но и площадка для создания профессиональных контактов и обмена опытом.
                                </p>
                            </div>
                            <div class="feature-card">
                                <img src="../../assets/img/image_7.jpg" alt="Менторская поддержка" class="feature-icon">
                                <h3 class="feature-name">Курсы с глубокой проработкой</h3>
                                <p class="feature-desc">
                                Курсы, разработанные профессионалами, предлагают систематическое обучение с постепенным углублением материала. Практические задания и разбор кейсов помогают студентам закрепить полученные знания.
                                </p>
                            </div>
                            <div class="feature-card">
                                <img src="../../assets/img/image_4.jpg" alt="Соревнования" class="feature-icon">
                                <h3 class="feature-name">Соревнуйтесь и прокачивайте навыки</h3>
                                <p class="feature-desc">
                                Одной из ключевых особенностей портала является возможность участия в соревнованиях по спортивному программированию, что способствует развитию навыков быстрого мышления и командной работы.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="ecosystem-section">
                    <div class="container">
                        <h2 class="ecosystem-title">
                        Экосистема Института образовательных технологий
                        </h2>
                        <p class="ecosystem-text">
                        Институт образовательных технологий активно сотрудничает с 
                        <span class="highlight">ведущими компаниями России</span>, 
                        предоставляя студентам возможность получать 
                        <span class="highlight">актуальные знания и практический опыт</span> 
                        от экспертов крупных корпораций. Это делает платформу незаменимой. 
                        В итоге наш портал — это не просто образовательный ресурс, а 
                        <span class="highlight">целая экосистема</span>, где каждый студент может 
                        найти поддержку, обменяться опытом и получить качественное образование, 
                        соответствующее требованиям современного рынка труда.
                        </p>
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
.ecosystem-section {
  background-color: #fff;
  padding: 80px 20px;
}

.ecosystem-title {
  font-size: 2.25rem;
  font-weight: 700;
  text-align: center;
  color: #1f2937;
  margin-bottom: 24px;
}

.ecosystem-text {
  text-align: center;
  margin: 0 auto;
  font-size: 1.125rem;
  line-height: 1.8;
  color: #4b5563;
}

.ecosystem-text .highlight {
  color: #2563eb;
  font-weight: 600;
  background: rgba(37, 99, 235, 0.1);
  padding: 2px 4px;
  border-radius: 4px;
}

.features-section {
  border-radius: 12px;
  background-color: #f9fafb;
  padding: 80px 0;
}
.features-title {
  text-align: center;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 60px;
  color: #1f2937;
}

/* Сетка карточек */
.features-grid {
  display: grid;
  justify-content: center;
  grid-template-columns: repeat(2, minmax(240px, 600px));
  gap: 32px;
}

/* Карточка преимущества */
.feature-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 32px 24px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.feature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.10);
}

/* Иконка */
.feature-icon {
  width: 100%;
  border-radius: 60px;
  margin-bottom: 24px;
}

/* Заголовок и текст */
.feature-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2563eb;
  margin-bottom: 16px;
}
.feature-desc {
  font-size: 1rem;
  line-height: 1.6;
  color: #4b5563;
}
@media (max-width: 768px) {
  .features-grid{
    grid-template-columns: repeat(1, minmax(240px, 600px));
  }
  .features-section {
    padding: 60px 0;
  }
  .features-title {
    font-size: 2rem;
    margin-bottom: 40px;
  }
  .feature-card {
    margin: 0 20px;
    padding: 24px 16px;
  }
  .ecosystem-title{
    font-size: 35px;
  }
}
/* Адаптив */
@media (max-width: 600px) {
  .intro__title{
    font-size: 31px !important;
  }
}
@media (max-width: 510px) {
  .intro__title{
    font-size: 28px !important;
  }
  .intro__text{
    font-size: 15px !important;
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
.intro__title {
    font-size: 37px;
    margin-bottom: 30px;
}
.intro__text {
    font-size: 18px;
    line-height: 1.6;
    max-width: 800px;
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
