import './bootstrap';
import { createApp } from 'vue';
import VueTheMask from 'vue-the-mask';
// import Register from './components/RegisterComponent.vue';
import HeaderComponent from './components/HeaderComponent.vue';

// Импорт всех компонентов для админ-панели

import DashboardComponent from './components/admin/DashboardComponent.vue';
import HeaderadminComponent from './components/admin/Header-adminComponent.vue';
import CabinetadminComponent from './components/admin/Cabinet-adminComponent.vue';
import ProfileadminComponent from './components/admin/Profile-adminComponent.vue';
import CourseComponent from './components/admin/CourseComponent.vue';
import TopicsComponent from './components/admin/TopicsComponent.vue';

// Импорт всех компонентов 
import ExampleComponent from './components/ExampleComponent.vue';
import ProfileComponent from './components/ProfileComponent.vue';
import CabinetComponent from './components/CabinetComponent.vue';
import RegisterComponent from './components/RegisterComponent.vue';
import VerifyComponent from './components/VerifyComponent.vue';
import LoginComponent from './components/LoginComponent.vue';
import ResetpasswordComponent from './components/Reset-passwordComponent.vue';
import AboutComponent from './components/AboutComponent.vue';
import ContactComponent from './components/ContactComponent.vue';
import CategoryCoursesComponent from './components/Category-coursesComponent.vue';
import CatalogComponent from './components/CatalogComponent.vue';
import ContentComponent from './components/ContentComponent.vue';
import NewsSingleComponent from './components/News-singleComponent.vue';
import NewsComponent from './components/NewsComponent.vue';
import PageCoursesComponent from './components/Page-coursesComponent.vue';
import PersonalAreaComponent from './components/Personal-areaComponent.vue';
import TrainingComponent from './components/TrainingComponent.vue';
import CppComponent from './components/CppComponent.vue';

// Создание приложения Vue
const app = createApp({});

// Регистрация компонентов (имена в kebab-case) для админ-панели
app.component('dashboard-component', DashboardComponent);
app.component('course-component', CourseComponent);
app.component('topic-component', TopicsComponent);
app.component('header-admin-component', HeaderadminComponent);
app.component('cabinet-admin-component', CabinetadminComponent);
app.component('profile-admin-component', ProfileadminComponent);
// Регистрация компонентов (имена в kebab-case)
app.component('header-component', HeaderComponent);
app.component('cpp-component', CppComponent);
app.component('profile-component', ProfileComponent);
app.component('cabinet-component', CabinetComponent);
app.component('example-component', ExampleComponent);
app.component('verify-component', VerifyComponent);
app.component('register-component', RegisterComponent);
app.component('reset-password-component', ResetpasswordComponent);
app.component('login-component', LoginComponent);
app.component('about-component', AboutComponent);
app.component('contact-component', ContactComponent);
app.component('category-courses-component', CategoryCoursesComponent);
app.component('catalog-component', CatalogComponent);
app.component('content-component', ContentComponent);
app.component('news-component', NewsComponent);
app.component('news-single-component', NewsSingleComponent);
app.component('page-courses-component', PageCoursesComponent);
app.component('personal-area-component', PersonalAreaComponent);
app.component('training-component', TrainingComponent);

// Монтируем приложение к элементу #app
app.use(VueTheMask);
app.mount('#app');
// createApp(Register).mount('#app');

window.globalNotification = {
    message: ''
  };
