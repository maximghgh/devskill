<template>
    <div>
        <div class="block__info">
            <a class="span__sctrelca" href="#" @click.prevent="goBack">🠔</a>
            <h2>Статистика покупок</h2>
        </div>
        <table class="light-push-table">
            <thead>
                <tr>
                    <th>Покупатель</th>
                    <th>Курс</th>
                    <th>Метод оплаты</th>
                    <th>Статус</th>
                    <th>Чек</th>
                    <th>Дата покупки</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="purchase in paginatedPurchases" :key="purchase.id">
                    <td>{{ purchase.user_name }}</td>
                    <td>{{ purchase.course_title }}</td>
                    <td>{{ purchase.payment_method }}</td>
                    <td>{{ purchase.status === 'completed' ? 'Успешно' : purchase.status }}</td>
                    <td>
                        <a
                            v-if="purchase.receipt_url"
                            :href="purchase.receipt_url"
                            target="_blank"
                            rel="noopener"
                        >Открыть</a>
                        <span v-else>—</span>
                    </td>
                    <td>
                        {{ new Date(purchase.purchase_date).toLocaleString() }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="pagination-users" v-if="totalPagesPurchases > 1">
            <button
                :disabled="currentPagePurchases === 1"
                @click="currentPagePurchases--"
            >‹ Назад</button>

            <button
                v-for="p in totalPagesPurchases"
                :key="p"
                :class="{ active: currentPagePurchases === p }"
                @click="currentPagePurchases = p"
            >{{ p }}</button>

            <button
                :disabled="currentPagePurchases === totalPagesPurchases"
                @click="currentPagePurchases++"
            >Вперёд ›</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";

const purchases = ref([]);

// 1) Текущая страница и размер страницы
const currentPagePurchases = ref(1);
const pageSizePurchases    = ref(5); // сколько строк на страницу

// 2) Вычисляем общее число страниц
const totalPagesPurchases = computed(() =>
  Math.ceil(purchases.value.length / pageSizePurchases.value)
);

// 3) Вычисляем массив текущей страницы
const paginatedPurchases = computed(() => {
  const start = (currentPagePurchases.value - 1) * pageSizePurchases.value;
  return purchases.value.slice(start, start + pageSizePurchases.value);
});

// 4) Сброс страницы на 1 при загрузке/обновлении данных
watch(purchases, () => {
  currentPagePurchases.value = 1;
});

const statusMap = {
  completed: 'Успешно',
  pending:   'Ожидается оплата',
  canceled:  'Отменено',
  // ...
};

onMounted(async () => {
  try {
    const response = await fetch("/api/purchases");
    purchases.value = await response.json();
  } catch (error) {
    console.error("Ошибка при загрузке покупок:", error);
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