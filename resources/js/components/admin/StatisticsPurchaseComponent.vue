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
                    <th>Дата покупки</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="purchase in purchases" :key="purchase.id">
                    <td>{{ purchase.user_name }}</td>
                    <td>{{ purchase.course_title }}</td>
                    <td>{{ purchase.payment_method }}</td>
                    <td>{{ purchase.status === 'completed' ? 'Успешно' : purchase.status }}</td>
                    <td>
                        {{ new Date(purchase.purchase_date).toLocaleString() }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const purchases = ref([]);

const statusMap = {
  completed: 'Успешно',
  pending: 'Ожидается оплата',
  canceled: 'Отменено'
  // и т.д.
}

onMounted(async () => {
    try {
        const response = await fetch("/api/purchases");
        purchases.value = await response.json();
    } catch (error) {
        console.error("Ошибка при загрузке покупок:", error);
    }
});

function goBack() {
  window.history.back();
}
</script>
<style scoped>
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