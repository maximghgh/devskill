import './bootstrap';
import '../css/quiz.css';
import { createApp } from 'vue';
import VueTheMask from 'vue-the-mask';
// import Register from './components/RegisterComponent.vue';
import HeaderComponent from './components/HeaderComponent.vue';
import FooterComponent from './components/FooterComponent.vue';

// Импорт всех компонентов преподователя 
import DashboardTeacher from './components/teacher/DashboardTeacher.vue';
import HeaderteacherComponent from './components/teacher/Header-teacherComponent.vue';
import TaskStudent from './components/teacher/TaskStudent.vue';
import StudentCourse from './components/teacher/StudentCourse.vue';
import StudentSubmissions from './components/teacher/StudentSubmissions.vue';
// Импорт всех компонентов для админ-панели

import DashboardComponent from './components/admin/DashboardComponent.vue';
import HeaderadminComponent from './components/admin/Header-adminComponent.vue';
import CabinetadminComponent from './components/admin/Cabinet-adminComponent.vue';
import ProfileadminComponent from './components/admin/Profile-adminComponent.vue';
import ProfileteacherComponent from './components/teacher/Profile-teacherComponent.vue';
import CourseComponent from './components/admin/CourseComponent.vue';
import TopicsComponent from './components/admin/TopicsComponent.vue';
import StatisticsComponent from './components/admin/StatisticsComponent.vue';
import StatisticsPurchaseComponent from './components/admin/StatisticsPurchaseComponent.vue';
import ConsultationsComponent from './components/admin/ConsultationsComponent.vue';
import AddCourseComponent from './components/admin/AddCourseComponent.vue';

// Импорт всех компонентов 
import FinalTestPage from './components/FinalTestPage.vue';
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
import ChapterView from './components/ChapterView.vue';

// Создание приложения Vue
const app = createApp({});

//Регистрация компонентов преподователя 
app.component('header-teacher-component', HeaderteacherComponent);
app.component('dashboardteacher-component', DashboardTeacher);
app.component('taskstudent-component', TaskStudent);
app.component('studentcours-component', StudentCourse);
app.component('studentsubmissions-component', StudentSubmissions);
app.component('profile-teacher-component', ProfileteacherComponent);

// Регистрация компонентов (имена в kebab-case) для админ-панели
app.component('addcourse-component', AddCourseComponent);
app.component('consultations-component', ConsultationsComponent);
app.component('statistics-component', StatisticsComponent);
app.component('statisticspurchase-component', StatisticsPurchaseComponent);
app.component('dashboard-component', DashboardComponent);
app.component('course-component', CourseComponent);
app.component('topic-component', TopicsComponent);
app.component('header-admin-component', HeaderadminComponent);
app.component('cabinet-admin-component', CabinetadminComponent);
app.component('profile-admin-component', ProfileadminComponent);
// Регистрация компонентов (имена в kebab-case)
app.component('header-component', HeaderComponent);
app.component('finaltest-component', FinalTestPage);
app.component('chapter-component', ChapterView);
app.component('footer-component', FooterComponent);
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
