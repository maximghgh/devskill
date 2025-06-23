<template>
  <div>
    <div class="block__info">
      <a class="span__sctrelca" href="#" @click.prevent="goBack">🠔</a>
      <h2>Статистика по ученикам</h2>
    </div>

    <table class="light-push-table">
      <thead>
        <tr>
          <th>Имя ученика</th>
          <th>Прогресс (%)</th>
          <th>Последняя дата прохождения</th>
        </tr>
      </thead>
      <tbody>
        <!-- 1) используем paginatedStats -->
        <tr v-for="user in paginatedStats" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ Math.round(user.progress_percent) }}%</td>
          <td v-if="user.last_completed_at">
            {{ new Date(user.last_completed_at).toLocaleString() }}
          </td>
          <td v-else>—</td>
        </tr>
      </tbody>
    </table>

    <!-- 2) Навигация по страницам -->
    <div class="pagination-users" v-if="totalPagesStats > 1">
      <button
        :disabled="currentPageStats === 1"
        @click="currentPageStats--"
      >‹ Назад</button>

      <button
        v-for="p in totalPagesStats"
        :key="p"
        :class="{ active: currentPageStats === p }"
        @click="currentPageStats = p"
      >{{ p }}</button>

      <button
        :disabled="currentPageStats === totalPagesStats"
        @click="currentPageStats++"
      >Вперёд ›</button>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, onMounted, watch } from "vue";

const stats = ref([]);

// 1. Пагинация
const currentPageStats = ref(1);
const pageSizeStats    = ref(5); // сколько строк на страницу, можно поменять
const totalPagesStats  = computed(() =>
  Math.ceil(stats.value.length / pageSizeStats.value)
);
const paginatedStats   = computed(() => {
  const start = (currentPageStats.value - 1) * pageSizeStats.value;
  return stats.value.slice(start, start + pageSizeStats.value);
});
// сбрасываем страницу на 1 при изменении данных
watch(stats, () => { currentPageStats.value = 1 });

onMounted(async () => {
  try {
    const response = await fetch("/api/chapters/stats");
    stats.value = await response.json();
  } catch (error) {
    console.error("Ошибка при загрузке статистики:", error);
  }
});

function goBack() {
    window.location.href = "/admin";
}
</script>


<style scoped>
.pagination-users {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 20px;
  font-family: Arial, sans-serif;
}

.pagination-users button {
  min-width: 40px;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #f9f9f9;
  color: #333;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.2s, border-color 0.2s, transform 0.1s;
}

.pagination-users button:hover:not(:disabled) {
  background-color: #fff;
  border-color: #888;
  transform: translateY(-1px);
}

.pagination-users button:disabled {
  opacity: 0.5;
  cursor: default;
}

.pagination-users button.active {
  background-color: #698dc9;
  border-color: #698dc9;
  color: #fff;
  font-weight: bold;
}
.block__info{
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 30px 0 40px;
}
.span__sctrelca{
    cursor: pointer;
    user-select: none;
    color: #ffffff;
    background-color: #007bff59;
    padding: 2px 7px;
    border-radius: 25px;
    position: absolute;
    top: 50%;
    left: 25%;
    transform: translateY(-50%);
    display: block;
    font-size: 48px;
    list-style: none;
    text-decoration: none;
}
table.light-push-table {
    margin: 0 auto;
    width: 1000px;
    border-collapse: collapse;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.light-push-table th,
.light-push-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e0e0e0;
    text-align: left;
    font-size: 14px;
    white-space: nowrap;
    /* Запрещает перенос текста */
    text-overflow: ellipsis;
    /* Добавляет многоточие при обрезке */
}

.light-push-table th {
    text-align: center;
    background-color: #f0f8ff;
    /* Нежно-голубой цвет */
    font-size: 17px;
    font-weight: 600;
    border-right: 1px solid #d0d0d0;
    padding: 14px;
}

.light-push-table td {
    border-right: 1px solid #f0f0f0;
}

.light-push-table tbody tr:last-child td {
    border-bottom: none;
}

/* Выравнивание номера по центру и фиксированная ширина */
.light-push-table td.number-cell {
    text-align: center;
    font-weight: bold;
    font-size: 15px;
    width: 120px;
    /* Фиксированная ширина для номера */
}

/* Анимация при наведении */
@keyframes rowHover {
    from {
        background-color: #ffffff;
        transform: scale(1);
    }

    to {
        background-color: #e0f7fa;
        transform: scale(1.02);
    }
}

.light-push-table tbody tr:hover {
    animation: rowHover 0.3s ease forwards;
}

/* Убираем рамку справа у последнего столбца */
.light-push-table th:last-child,
.light-push-table td:last-child {
    border-right: none;
}
</style>
