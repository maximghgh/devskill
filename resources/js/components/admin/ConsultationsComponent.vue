<template>
    <div>
        <div class="block__info">
            <a class="span__sctrelca" href="#" @click.prevent="goBack">🠔</a>
            <h2>Список заявок на консультацию</h2>
        </div>
        <table class="light-push-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Курс</th>
                    <th>Статус</th>
                    <th>Дата создания</th>
                </tr>
            </thead>
            <tbody>
                <!-- (item, index) чтобы иметь индекс -->
                <tr v-for="(item, index) in consultations" :key="item.id">
                    <td>{{ item.id }}</td>
                    <td>{{ item.name }}</td>
                    <td>{{ item.email }}</td>
                    <td>{{ item.phone }}</td>
                    <td>{{ item.course_title || "—" }}</td>
                    <td class="status">
                        <!-- Если editingIndex совпадает с этой строкой, показываем <select> -->
                        <template v-if="editingIndex === index">
                            <select
                                v-model="item.status"
                                @change="handleStatusChange(item, index)"
                            >
                                <option value="новый">новый</option>
                                <option value="в процессе">в процессе</option>
                                <option value="выполнено">выполнено</option>
                            </select>
                        </template>
                        <!-- Иначе просто текст статуса -->
                        <template v-else>
                            <span @click="startEditing(index)">
                                {{ item.status }}
                            </span>
                        </template>
                    </td>
                    <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Массив консультаций
const consultations = ref([]);

// Индекс текущей "редактируемой" строки
const editingIndex = ref(null);

// При монтировании — загрузка с бэкенда
onMounted(async () => {
    try {
        const response = await fetch("/api/consultations");
        consultations.value = await response.json();
    } catch (error) {
        console.error("Ошибка при загрузке консультаций:", error);
    }
});

// При клике на статус — переходим в режим редактирования (показываем <select>)
function startEditing(index) {
    editingIndex.value = index;
}

// Когда пользователь выбрал новый статус в <select>
async function handleStatusChange(item, index) {
    try {
        // Запрос на бэкенд для смены статуса
        const response = await fetch(`/api/consultations/${item.id}/status`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ status: item.status }),
        });

        if (!response.ok) {
            throw new Error(
                `Ошибка при обновлении статуса: ${response.statusText}`
            );
        }

        // Если выбрали "выполнено", удаляем строку из массива
        if (item.status === "выполнено") {
            consultations.value.splice(index, 1);
        }

        // Выходим из режима редактирования
        editingIndex.value = null;
    } catch (error) {
        console.error("Ошибка при обновлении статуса:", error);
    }
}

// Метод для кнопки «Назад»
function goBack() {
    window.history.back();
}
</script>

<style scoped>
h2{
    margin: unset !important;
}
.status{
    cursor: pointer;
    color: #1940f0;
}
.block__info {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 30px 0 40px;
}

.span__sctrelca {
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

/* Таблица */
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
    text-overflow: ellipsis;
}

.light-push-table th {
    text-align: center;
    background-color: #f0f8ff; /* Нежно-голубой цвет */
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
