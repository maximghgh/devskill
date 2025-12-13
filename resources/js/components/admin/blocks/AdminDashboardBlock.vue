<template>
  <div class="user-block" style="margin-top: 20px;">
    <h1 class="page__title">Дашборд</h1>

    <span class="dashboard__desc">
      Добро пожаловать, {{ currentUser?.name || "Администратор" }}!
    </span>

    <div class="info__block-statistics">
      <div class="statistics">
        <div>
          <h1 class="blocks__title--m">Статистика</h1>
          <ul class="statistics__list">
            <li class="statistics__item">
              <span class="statistics__text">Открытые обращения пользователей</span>
              <span class="statistics__text">12</span>
            </li>
            <li class="statistics__item">
              <span class="statistics__text">Активные курсы</span>
              <span class="statistics__text">{{ coursesCount }}</span>
            </li>
            <li class="statistics__item">
              <span class="statistics__text">Завершенные курсы</span>
              <span class="statistics__text">1</span>
            </li>
            <li class="statistics__item">
              <span class="statistics__text">Всего пользователей</span>
              <span class="statistics__text">{{ usersCount }}</span>
            </li>
          </ul>
        </div>

        <section class="activity">
          <h1 class="blocks__title--m">Последняя активность</h1>
          <ul class="activity__list">
            <li class="activity__item">
              <div class="activity__content">
                <p class="activity__text">Новое обращение от пользователя Вася</p>
                <p class="activity__meta">
                  <span class="activity__meta-icon">
                    <img width="15" height="15" src="../../../../img/admin/time.svg" alt="">
                  </span>
                  <span class="activity__meta-time">3 часа назад</span>
                </p>
              </div>
            </li>

            <li class="activity__item">
              <div class="activity__content">
                <p class="activity__text">Новый курс создан</p>
                <p class="activity__meta">
                  <span class="activity__meta-icon">
                    <img width="15" height="15" src="../../../../img/admin/time.svg" alt="">
                  </span>
                  <span class="activity__meta-time">5 часов назад</span>
                </p>
              </div>
            </li>
          </ul>
        </section>

        <section class="activity">
          <h1 class="blocks__title--m">Активность</h1>
          <img src="../../../../img/admin/active.png" alt="">
        </section>

        <section class="attention">
          <h1 class="blocks__title--m">Требуют внимания</h1>

          <ul class="attention__list">
            <li class="attention__item attention__item--muted">
              <span class="attention__status"></span>
              <div class="attention__content">
                <p class="attention__topic">Вопрос о записи на курс</p>
                <p class="attention__from">От: Вася</p>
                <p class="attention__message">Не могу зайти на курс. Помогите!</p>
                <span class="attention__tag attention__tag--light">Вопрос</span>
              </div>
            </li>

            <li class="attention__item attention__item--active">
              <span class="attention__status"></span>
              <div class="attention__content">
                <p class="attention__topic">Технические проблемы</p>
                <p class="attention__from">От: Отец</p>
                <p class="attention__message">Я случайно поменял сыну пароль</p>
                <span class="attention__tag">От родителя</span>
              </div>
            </li>
          </ul>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  currentUser: { type: Object, default: null },
  usersCount: { type: Number, default: 0 },
  coursesCount: { type: Number, default: 0 },
});
</script>
