<template>
  <div class="user-block">
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
              <span class="statistics__text">{{ openSupportRequestsCount }}</span>
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
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  currentUser: { type: Object, default: null },
  usersCount: { type: Number, default: 0 },
  coursesCount: { type: Number, default: 0 },
  openSupportRequestsCount: { type: Number, default: 0 },
});
</script>
