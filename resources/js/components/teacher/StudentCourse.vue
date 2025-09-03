<template>
  <div>
    <h2>Список учеников, купивших курс</h2>
    <ul class="course-list">
      <li v-for="student in students" :key="student.id" class="course-item">
        <a :href="`/studentsubmissions/${courseId}/${student.id}`" class="course-link">{{ student.name }} ({{ student.email }})</a>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Состояние для хранения списка студентов
const students = ref([]);

// Получаем courseId из URL
const courseId = window.location.pathname.split('/').pop(); // Получаем последний сегмент пути

// Загружаем студентов при монтировании компонента
onMounted(async () => {
  try {
    // Отправляем запрос на сервер, чтобы получить студентов для данного курса
    const response = await axios.get(`/api/students/${courseId}`);
    
    // Отладочная информация
    console.log("Ответ от сервера:", response.data);
    
    // Сохраняем данные студентов
    students.value = response.data; 
  } catch (error) {
    console.error('Ошибка при загрузке студентов:', error);
  }
});
</script>

<style scoped>

/* Можно добавить стили для списка студентов */
ul {
  list-style-type: none;
  padding: 0;
}

li {
  margin: 10px 0;
  font-size: 18px;
}
.course-list {
    width: 1200px;
    margin: 0 auto;
    padding: 0;
    display: flex;
    flex-direction: column;
}
.course-item {
    border-bottom: 1px solid #2980b9;
    padding: 10px;
    margin: 10px 0;
}

.course-link {
    text-decoration: none;
    color: #3498db;
    font-size: 20px;
}
</style>
