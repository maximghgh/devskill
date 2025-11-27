<template>
    <div>
        <div class="containers">
            <aside>
                <div class="menus">
                    <div class="aside__menu">
                        <!-- Меню -->
                        <ul class="aside__list">
                            <li
                            class="aside__item"
                            v-for="(item, index) in menuItems"
                            :key="index"
                            >
                            <a
                                :href="item.href"
                                :class="[
                                'aside__link',
                                { 'aside__link--active': activeIndex === index },
                                ]"
                                @click.prevent="setActive(index)"
                            >
                                <span class="aside__icon" v-if="item.icon">
                                <img :src="item.icon" width="24" height="24" alt="" />
                                </span>
                                <span class="aside__text">
                                {{ item.label }}
                                </span>
                            </a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu-block">
                        <div v-for="(item, index) in menuItems"
                            :key="index"
                            :id="item.id"
                            class="block"
                            style="margin-top: 20px"
                            v-show="activeIndex === index"
                        >
                            <h1 class="page__title">{{ item.label }}</h1>
                            <div v-if="item.id === 'dashboard'" class="user-block">
                                <span class="dashboard__desc">Добро пожаловать, Анастасия Филатова!</span>
                                <div class="info__block-statistics">
                                    <div class="statistics">
                                        <div class="">
                                            <h1 class="blocks__title--m">Статистика</h1>
                                            <ul class="statistics__list">
                                                <li class="statistics__item">
                                                    <span class="statistics__text">Открытые обращения пользователей</span>
                                                    <span class="statistics__text">12</span>            
                                                </li>
                                                <li class="statistics__item">
                                                    <span class="statistics__text">Активные курсы</span>
                                                    <span class="statistics__text">12</span>            
                                                </li>
                                                <li class="statistics__item">
                                                    <span class="statistics__text">Завершенные курсы</span>
                                                    <span class="statistics__text">1</span>            
                                                </li>
                                                <li class="statistics__item">
                                                    <span class="statistics__text">Всего пользователей</span>
                                                    <span class="statistics__text">123</span>            
                                                </li>
                                            </ul>
                                        </div>
                                        <section class="activity">
                                            <h1 class="blocks__title--m">Последняя активность</h1>

                                            <ul class="activity__list">
                                                <li class="activity__item">
                                                    <div class="activity__content">
                                                        <p class="activity__text">
                                                            Новое обращение от пользователя Вася
                                                        </p>
                                                        <p class="activity__meta">
                                                            <span class="activity__meta-icon">
                                                                <img width="15" height="15" src="../../../img/admin/time.svg" alt="">
                                                            </span>
                                                            <span class="activity__meta-time">3 часа назад</span>
                                                        </p>
                                                    </div>
                                                </li>

                                                <li class="activity__item">
                                                    <div class="activity__content">
                                                        <p class="activity__text">
                                                            Новый курс создан
                                                        </p>
                                                        <p class="activity__meta">
                                                            <span class="activity__meta-icon">
                                                                <img width="15" height="15" src="../../../img/admin/time.svg" alt="">
                                                            </span>
                                                            <span class="activity__meta-time">5 часов назад</span>
                                                        </p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </section>
                                        <section class="activity">
                                            <h1 class="blocks__title--m">Активность</h1>

                                            <img src="../../../img/admin/active.png" alt="">
                                        </section>
                                        <section class="attention">
                                            <h1 class="blocks__title--m">Требуют внимания</h1>

                                            <ul class="attention__list">
                                                <!-- Заявка 1 -->
                                                <li class="attention__item attention__item--muted">
                                                    <span class="attention__status"></span>

                                                    <div class="attention__content">
                                                        <p class="attention__topic">Вопрос о записи на курс</p>

                                                        <p class="attention__from">От: Вася</p>
                                                        <p class="attention__message">
                                                            Не могу зайти на курс. Помогите!
                                                        </p>

                                                        <span class="attention__tag attention__tag--light">
                                                            Вопрос
                                                        </span>
                                                    </div>
                                                </li>

                                                <!-- Заявка 2 -->
                                                <li class="attention__item attention__item--active">
                                                    <span class="attention__status"></span>

                                                    <div class="attention__content">
                                                        <p class="attention__topic">Технические проблемы</p>

                                                        <p class="attention__from">От: Отец</p>
                                                        <p class="attention__message">
                                                            Я случайно поменял сыну пароль
                                                        </p>

                                                        <span class="attention__tag">
                                                            От родителя
                                                        </span>
                                                    </div>
                                                </li>
                                            </ul>
                                        </section>

                                    </div>
                                </div>
                            </div>
                            <!-- Блок "Все пользователи" -->
                            <div v-if="item.id === 'users'" class="user-block" id="users">
                                <!-- верхняя строка: заголовок + счетчик + кнопка -->
                                <div class="users-header">
                                    <p class="users-subtitle">{{ usersTotalLabel }}</p>                                    
                                </div>

                                <!-- панель фильтров -->
                                <div class="users-toolbar">
                                    <div class="asdf">
                                        <div class="users-toolbar__left">
                                            <!-- Показать N пользователей -->
                                            <label class="users-show">
                                                Показать
                                                <span class="users-show__select-wrap">
                                                    <select v-model.number="pageSizeUsers" class="users-show__select">
                                                        <option :value="10">10</option>
                                                        <option :value="25">25</option>
                                                        <option :value="50">50</option>
                                                    </select>
                                                </span>
                                                пользователей
                                            </label>

                                            <!-- Фильтр ролей -->
                                            <div class="users-roles">
                                                <button
                                                    type="button"
                                                    class="users-roles__btn"
                                                    @click="roleDropdownOpen = !roleDropdownOpen"
                                                >
                                                    Роли
                                                    <span
                                                        class="users-roles__chevron"
                                                        :class="{ 'users-roles__chevron--open': roleDropdownOpen }"
                                                    ></span>
                                                </button>

                                                <div
                                                    v-if="roleDropdownOpen"
                                                    class="users-roles__dropdown"
                                                    @click.stop
                                                >
                                                    <p class="users-roles__title">Фильтр роль</p>

                                                    <!-- используем RADIO, но стилизуем как чекбоксы -->
                                                    <label class="users-roles__option">
                                                        <input
                                                            type="radio"
                                                            value="all"
                                                            v-model="selectedRole"
                                                        />
                                                        <span>Все роли</span>
                                                    </label>
                                                    <label class="users-roles__option">
                                                        <input
                                                            type="radio"
                                                            value="1"
                                                            v-model="selectedRole"
                                                        />
                                                        <span>Ученик</span>
                                                    </label>
                                                    <label class="users-roles__option">
                                                        <input
                                                            type="radio"
                                                            value="2"
                                                            v-model="selectedRole"
                                                        />
                                                        <span>Преподаватель</span>
                                                    </label>
                                                    <label class="users-roles__option">
                                                        <input
                                                            type="radio"
                                                            value="3"
                                                            v-model="selectedRole"
                                                        />
                                                        <span>Администратор</span>
                                                    </label>

                                                    <div class="users-roles__actions">
                                                        <button
                                                            type="button"
                                                            class="btn users-roles__apply"
                                                            @click="roleDropdownOpen = false"
                                                        >
                                                            Фильтр
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn users-roles__reset"
                                                            @click="resetRoleFilter"
                                                        >
                                                            Сброс
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Поиск -->
                                        <div class="users-toolbar__search">
                                            <div class="users-search">
                                                <span class="users-search__icon">
                                                    <img width="13" height="13" src="../../../img/admin/search.svg" alt="">
                                                </span>
                                                <input
                                                    v-model="searchQuery"
                                                    type="text"
                                                    class="users-search__input"
                                                    placeholder="Поиск..."
                                                />
                                                <button
                                                    v-if="searchQuery"
                                                    type="button"
                                                    class="users-search__clear"
                                                    @click="searchQuery = ''"
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="users-btn-new">
                                        <span class="users-btn-desc">+</span> Новый пользователь
                                    </button>
                                </div>

                                <!-- таблица пользователей -->
                                <div v-if="filteredUsers.length > 0">
                                    <table class="light-push-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>ФИО</th>
                                                <th>Email</th>
                                                <th>Телефон</th>
                                                <th>День рождения</th>
                                                <th>Местоположение</th>
                                                <th>Роль</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(userItem) in paginatedUsers"
                                                :key="userItem.id"
                                            >
                                                <td>#{{ userItem.id }}</td>
                                                <td class="avatar__user">
                                                    <img 
                                                        :src="userItem.photo ? `/storage/${userItem.photo}` : 'https://devskills.foncode.ru/img/no_foto.jpg'" 
                                                        alt="Фото пользователя" width="32" height="32" class="avatar__admin"
                                                    />
                                                    {{ userItem.name }}</td>
                                                <td>{{ userItem.email }}</td>
                                                <td>{{ userItem.phone }}</td>
                                                <td>{{ formatBirthday(userItem.birthday) }}</td>
                                                <td>{{ userItem.country }}</td>

                                                <!-- роли -->
                                                <td>
                                                    <div v-if="editingUserId === userItem.id">
                                                        <select
                                                            v-model="editingUser.role"
                                                            class="select"
                                                            style="
                                                                padding: 5px;
                                                                border: 1px solid #ccc;
                                                                border-radius: 4px;
                                                            "
                                                        >
                                                            <option :value="3">Админ</option>
                                                            <option :value="2">Преподаватель</option>
                                                            <option :value="1">Ученик</option>
                                                        </select>
                                                    </div>
                                                    <div
                                                        v-else
                                                        @click="startEditing(userItem)"
                                                        class="users-role-pill"
                                                    >
                                                        {{ getRoleName(userItem.role) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <button
                                                        class="btn__user--edit"
                                                        @click="openUserEditModal(userItem)"
                                                    >
                                                        <img width="24" height="24" src="../../../img/admin/edit.svg" alt="">
                                                    </button>
                                                    <button
                                                        class="btn__user--delete"
                                                        @click="deleteUser(userItem.id)"
                                                    >
                                                        <img width="24" height="24" src="../../../img/admin/trash.png" alt="">
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else>Нет пользователей</div>

                                <!-- пагинация -->
                                <div class="pagination-users" v-if="totalPagesUsers > 1">
                                    <button
                                        :disabled="currentPageUsers === 1"
                                        @click="currentPageUsers--"
                                    >
                                        ‹ Назад
                                    </button>

                                    <button
                                        v-for="p in totalPagesUsers"
                                        :key="p"
                                        :class="{ active: currentPageUsers === p }"
                                        @click="currentPageUsers = p"
                                    >
                                        {{ p }}
                                    </button>

                                    <button
                                        :disabled="currentPageUsers === totalPagesUsers"
                                        @click="currentPageUsers++"
                                    >
                                        Вперёд ›
                                    </button>
                                </div>

                                <!-- модалка редактирования пользователя (оставляем как было) -->
                                <div v-if="showUserEditModal" class="modal-overlay">
                                    <div class="modal-content modal-content--s">
                                        <button class="close-button" @click="closeUserEditModal">×</button>
                                        <h2>Редактировать пользователя</h2>
                                        <form @submit.prevent="saveUserModal" class="form-column form-column--s">
                                        <div class="form-group">
                                            <label class="form-label">ФИО</label>
                                            <input v-model="editingUser.name" type="text" class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input v-model="editingUser.email" type="email" class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Телефон</label>
                                            <input v-model="editingUser.phone" placeholder="+7 (999) 999-99-99" v-mask="'+7 (###) ###-##-##'" type="text" class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Дата рождения</label>
                                            <input
                                                v-model="editingUser.birthday"
                                                type="date"
                                                class="form-input"
                                            />
                                        </div>
                                        <!-- Местоположение -->
                                        <div class="form-group">
                                            <label class="form-label">Местоположение</label>
                                            <input
                                                v-model="editingUser.country"
                                                type="text"
                                                class="form-input"
                                                placeholder="Город, страна"
                                            />
                                        </div>
                                        <!-- Роль -->
                                        <div class="form-group">
                                            <label class="form-label">Роль</label>
                                                <select v-model="editingUser.role" class="form-input">
                                                    <option :value="3">Администратор</option>
                                                    <option :value="2">Преподаватель</option>
                                                    <option :value="1">Ученик</option>
                                                </select>
                                        </div>
                                        <!-- Должность (только для ролей 3 и 2) -->
                                        <div class="form-group" v-if="editingUser.role === 3 || editingUser.role === 2">
                                            <label class="form-label">Должность</label>
                                            <input
                                                v-model="editingUser.position"
                                                type="text"
                                                class="form-input"
                                                placeholder="Введите должность"
                                            />
                                        </div>
                                        <!-- Добавьте остальные поля по аналогии -->
                                        <div class="modal-actions">
                                            <button type="submit" class="btn form-button form-button--s">Сохранить</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Блок "Курсы" -->
                            <div v-else-if="item.id === 'courses'" class="user-block" id="courses">
                                <!-- шапка как у пользователей -->
                                <div class="users-header">
                                    <p class="users-subtitle">{{ coursesTotalLabel }}</p>
                                </div>

                                <!-- тулбар: количество, фильтр, поиск -->
                                <div class="users-toolbar">
                                    <div class="asdf">
                                        <div class="users-toolbar__left">
                                            <!-- Показать N курсов -->
                                            <label class="users-show">
                                                Показать
                                                <span class="users-show__select-wrap">
                                                    <select
                                                        v-model.number="pageSizeCourses"
                                                        class="users-show__select"
                                                    >
                                                        <option :value="10">10</option>
                                                        <option :value="25">25</option>
                                                        <option :value="50">50</option>
                                                    </select>
                                                </span>
                                                курсов
                                            </label>

                                            <!-- Фильтр по уровню сложности -->
                                            <div class="users-roles">
                                                <button
                                                    type="button"
                                                    class="users-roles__btn"
                                                    @click="
                                                        courseDifficultyDropdownOpen =
                                                            !courseDifficultyDropdownOpen
                                                    "
                                                >
                                                    Уровень
                                                    <span
                                                        class="users-roles__chevron"
                                                        :class="{
                                                            'users-roles__chevron--open':
                                                                courseDifficultyDropdownOpen,
                                                        }"
                                                    ></span>
                                                </button>

                                                <div
                                                    v-if="courseDifficultyDropdownOpen"
                                                    class="users-roles__dropdown"
                                                    @click.stop
                                                >
                                                    <p class="users-roles__title">Фильтр по уровню</p>

                                                    <label class="users-roles__option">
                                                        <input
                                                            type="radio"
                                                            value="basic"
                                                            v-model="selectedDifficulty"
                                                        />
                                                        <span class="users-roles__span">Базовый</span>
                                                    </label>
                                                    <label class="users-roles__option">
                                                        <input
                                                            type="radio"
                                                            value="middle"
                                                            v-model="selectedDifficulty"
                                                        />
                                                        <span class="users-roles__span">Средний</span>
                                                    </label>
                                                    <label class="users-roles__option">
                                                        <input
                                                            type="radio"
                                                            value="advanced"
                                                            v-model="selectedDifficulty"
                                                        />
                                                        <span class="users-roles__span">Продвинутый</span>
                                                    </label>
                                                    <label class="users-roles__option">
                                                        <input
                                                            type="radio"
                                                            value="mixed"
                                                            v-model="selectedDifficulty"
                                                        />
                                                        <span class="users-roles__span">Смешанный</span>
                                                    </label>

                                                    <div class="users-roles__actions">
                                                        <button
                                                            type="button"
                                                            class="btn users-roles__apply"
                                                            @click="courseDifficultyDropdownOpen = false"
                                                        >
                                                            Фильтр
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn users-roles__reset"
                                                            @click="resetCourseDifficultyFilter"
                                                        >
                                                            Сброс
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Поиск по курсам -->
                                        <div class="users-toolbar__search">
                                            <div class="users-search">
                                                <span class="users-search__icon">
                                                    <img
                                                        width="13"
                                                        height="13"
                                                        src="../../../img/admin/search.svg"
                                                        alt=""
                                                    />
                                                </span>
                                                <input
                                                    v-model="searchCourseQuery"
                                                    type="text"
                                                    class="users-search__input"
                                                    placeholder="Поиск курса..."
                                                />
                                                <button
                                                    v-if="searchCourseQuery"
                                                    type="button"
                                                    class="users-search__clear"
                                                    @click="searchCourseQuery = ''"
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="users-btn-new" href="/admin/addcourse">
                                        <span class="users-btn-desc">+</span> Новый курс
                                    </a>
                                </div>

                                <!-- старый admin__block оставляем, только без link__add -->
                                <div class="admin__block">
                                    <!-- <div class="cou">
                                        <div class="div__link">
                                            <a class="course__links" href="/admin/statistics">
                                                Статистика учеников
                                            </a>
                                            <a
                                                class="course__links"
                                                href="/admin/statisticspurchase"
                                            >
                                                Cтатистика покупок пользователей
                                            </a>
                                            <a class="course__links" href="/admin/consultations">
                                                Записи на консультации
                                            </a>
                                        </div>
                                    </div> -->

                                    <div v-if="paginatedCourses.length">
                                        <table class="light-push-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Курс</th>
                                                    <th>Активен</th>
                                                    <th>Дата создания</th>
                                                    <th>Участники</th>
                                                    <th>Уровень</th>
                                                    <th>Действия</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(course, index) in paginatedCourses"
                                                    :key="course.id"
                                                >
                                                    <td>{{ index + 1 }}</td>
                                                    <td class="avatar__user">
                                                    <img 
                                                        :src="course.card_image ? `${course.card_image}` : 'https://devskills.foncode.ru/img/no_foto.jpg'" 
                                                        alt="Фото пользователя" width="25" height="25" class="avatar__admin"
                                                    />
                                                    {{ course.course_name }}</td>
                                                    <td>Да</td>
                                                    <td>{{ formatBirthday(course.created_at) }}</td>
                                                    <td>0</td>
                                                    <td>
                                                        <div class="users-role-pill">
                                                            {{ difical(course.difficulty) }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <!-- твои кнопки действий как были -->
                                                        <button
                                                            class="btn__user--delete"
                                                            @click.prevent="deleteCourse(course.id)"
                                                        >
                                                            <img
                                                                width="24"
                                                                height="24"
                                                                src="../../../img/admin/trash.png"
                                                                alt=""
                                                            />
                                                        </button>
                                                        <button
                                                            class="btn__user--edit"
                                                            @click.prevent="openEditModal(course)"
                                                        >
                                                            <img
                                                                width="24"
                                                                height="24"
                                                                src="../../../img/admin/edit.svg"
                                                                alt=""
                                                            />
                                                        </button>
                                                        <a
                                                            class="btn--control"
                                                            :href="`/admin/course/${course.id}/topics/json`"
                                                        >
                                                            <img
                                                                width="24"
                                                                height="24"
                                                                src="../../../img/admin/book.png"
                                                                alt=""
                                                            />
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <!-- пагинация курсов (можно оставить как есть) -->
                                        <div
                                            class="pagination-users"
                                            v-if="totalPagesCourses > 1"
                                            style="margin-top: 20px"
                                        >
                                            <button
                                                :disabled="currentPageCourses === 1"
                                                @click="currentPageCourses--"
                                            >
                                                ‹ Назад
                                            </button>

                                            <button
                                                v-for="p in totalPagesCourses"
                                                :key="p"
                                                :class="{ active: currentPageCourses === p }"
                                                @click="currentPageCourses = p"
                                            >
                                                {{ p }}
                                            </button>

                                            <button
                                                :disabled="currentPageCourses === totalPagesCourses"
                                                @click="currentPageCourses++"
                                            >
                                                Вперёд ›
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else>Нет курсов</div>
                                </div>
                                <div v-if="showEditModal" class="modal-overlay">
                                    <div class="modal-content">
                                        <button
                                            class="close-button"
                                            @click="closeEditModal"
                                        >
                                            ×
                                        </button>
                                        <h2>Редактирование курса</h2>
                                        <div class="edit-course-form-container">
                                            <form
                                                @submit.prevent="updateCourse"
                                                class="edit-course-form"
                                            >
                                                <!-- Первая колонка -->
                                                <div class="form-column">
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Название на
                                                            карточке</label
                                                        >
                                                        <input
                                                            v-model="
                                                                editCourse.cardTitle
                                                            "
                                                            type="text"
                                                            placeholder="Введите название для карточки"
                                                            class="form-input"
                                                        />
                                                    </div>
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Название
                                                            курса</label
                                                        >
                                                        <input
                                                            v-model="
                                                                editCourse.courseName
                                                            "
                                                            type="text"
                                                            placeholder="Введите название курса"
                                                            class="form-input"
                                                        />
                                                    </div>
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Цена</label
                                                        >
                                                        <input
                                                            v-model="
                                                                editCourse.price
                                                            "
                                                            type="number"
                                                            placeholder="Введите цену"
                                                            class="form-input"
                                                        />
                                                    </div>
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Длительность (в
                                                            месяцах от
                                                            1-24)</label
                                                        >
                                                        <input
                                                            v-model="
                                                                editCourse.duration
                                                            "
                                                            type="text"
                                                            placeholder="Введите длительность"
                                                            class="form-input"
                                                        />
                                                    </div>
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Описание</label
                                                        >
                                                        <textarea
                                                            v-model="
                                                                editCourse.description
                                                            "
                                                            placeholder="Введите описание"
                                                            class="form-textarea"
                                                        ></textarea>
                                                    </div>
                                                </div>
                                                <!-- Вторая колонка -->
                                                <div class="form-column">
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Количество
                                                            часов</label
                                                        >
                                                        <input
                                                            v-model="
                                                                editCourse.hours
                                                            "
                                                            type="number"
                                                            placeholder="Введите количество часов"
                                                            class="form-input"
                                                        />
                                                    </div>
                                                        <input
                                                            v-model="
                                                                editCourse.simulators
                                                            "
                                                            type="hidden"
                                                            placeholder="Введите количество тренажёров"
                                                            class="form-input"
                                                        />
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Уровень сложности
                                                            курса</label
                                                        >
                                                        <select
                                                            v-model="
                                                                editCourse.difficulty
                                                            "
                                                            class="form-input"
                                                        >
                                                            <option
                                                                value="basic"
                                                            >
                                                                Начинающий
                                                            </option>
                                                            <option
                                                                value="middle"
                                                            >
                                                                Средний
                                                            </option>
                                                            <option
                                                                value="advanced"
                                                            >
                                                                Продвинутый
                                                            </option>
                                                            <option
                                                                value="mixed"
                                                            >
                                                                Смешанный
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Выберите
                                                            преподавателей</label
                                                        >
                                                        <select
                                                            v-model="
                                                                editCourse.selectedTeachers
                                                            "
                                                            multiple
                                                            class="form-input"
                                                        >
                                                            <option
                                                                class="option-form"
                                                                v-for="teacher in teachers"
                                                                :key="
                                                                    teacher.id
                                                                "
                                                                :value="
                                                                    teacher.id
                                                                "
                                                            >
                                                                {{
                                                                    teacher.name
                                                                }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Выберите
                                                            направление</label
                                                        >
                                                        <select
                                                            v-model="
                                                                editCourse.selectedDirection
                                                            "
                                                            class="form-input"
                                                        >
                                                            <option>
                                                                -- Выберите
                                                                направление --
                                                            </option>
                                                            <option
                                                                class="option-form"
                                                                v-for="direction in directions"
                                                                :key="
                                                                    direction.id
                                                                "
                                                                :value="
                                                                    direction.id
                                                                "
                                                            >
                                                                {{
                                                                    direction.name
                                                                }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >Выберите языки
                                                            программирования</label
                                                        >
                                                        <Multiselect v-model="editCourse.selectedLanguages"
                                                            :options="languages"
                                                            :multiple="true"
                                                            track-by="id"
                                                            label="name"
                                                            placeholder="Нажмите на поле, чтобы выбрать язык"
                                                            :close-on-select="
                                                                false
                                                            "
                                                            :clear-on-select="
                                                                false
                                                            "
                                                            :preserve-search="
                                                                true
                                                            "
                                                        >
                                                            <template
                                                                #option="{
                                                                    option,
                                                                    selected,
                                                                }"
                                                            >
                                                                <div
                                                                    class="multiselect__option"
                                                                >
                                                                    <input
                                                                        type="checkbox"
                                                                        class="checkbox"
                                                                        :class="{
                                                                            'checkbox-checked':
                                                                                selected,
                                                                        }"
                                                                    />
                                                                    <span
                                                                        class="checkmark"
                                                                    >
                                                                        <svg
                                                                            class="checkmark__icon"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            viewBox="0 0 24 24"
                                                                        >
                                                                            <path
                                                                                d="M20.292969 5.2929688L9 16.585938 4.7070312 12.292969 3.2929688 13.707031 9 19.414062 21.707031 6.7070312 20.292969 5.2929688z"
                                                                            />
                                                                        </svg>
                                                                    </span>
                                                                    <span
                                                                        class="option-name"
                                                                        >{{
                                                                            option.name
                                                                        }}</span
                                                                    >
                                                                </div>
                                                            </template>
                                                            <template
                                                                #selection="{
                                                                    values,
                                                                }"
                                                            >
                                                                <span
                                                                    class="selection-header"
                                                                >
                                                                    {{
                                                                        values
                                                                            .map(
                                                                                (
                                                                                    v
                                                                                ) =>
                                                                                    v.name
                                                                            )
                                                                            .join(
                                                                                ", "
                                                                            )
                                                                    }}
                                                                </span>
                                                            </template>
                                                        </Multiselect>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Повышение квалификации</label>
                                                        <select v-model="editCourse.upgradeQualification" class="form-input">
                                                            <!-- Используем числовые значения -->
                                                            <option :value="0">
                                                                Нет
                                                            </option>
                                                            <option :value="1">
                                                                Да
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Логотипкурса</label>
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            class="form-input"
                                                            @change="
                                                                onFileChange
                                                            "
                                                        />
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Изображение для описания курса</label>
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            class="form-input"
                                                            @change="
                                                                onDescriptionImageChange
                                                            "
                                                        />
                                                    </div>
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label"
                                                            >PDF фаил курса</label
                                                        >
                                                        <input
                                                            type="file"
                                                            accept="application/pdf"
                                                            class="form-input"
                                                            @change="onPdfChange"
                                                        />
                                                    </div>
                                                </div>
                                                <!-- Editor.js контейнер -->
                                                <div id="editorjs-edit" class="editor-container"></div>
                                                <button type="submit" class="form-button">Сохранить</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Другие блоки (аналитика, FAQ, и т.д.) -->
                            <div v-else-if="item.id === 'news'">
                                <table v-if="paginatedNews.length" class="light-push-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Название новости</th>
                                            <th>Просмотреть комментарии</th>
                                            <th>Действие</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(newsItem, index) in paginatedNews" :key="newsItem.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ newsItem.title }}</td>
                                            <td><a href="#" @click.prevent="openComments(newsItem)">Просмотреть комментарии</a></td>
                                            <td>
                                                <!-- твои кнопки действий как были -->
                                                <button
                                                    class="btn__user--delete"
                                                    @click="deleteNews(newsItem.id)"
                                                >
                                                    <img
                                                        width="24"
                                                        height="24"
                                                        src="../../../img/admin/trash.png"
                                                        alt=""
                                                    />
                                                </button>
                                                <button
                                                    class="btn__user--edit"
                                                    @click="openNewsEditModal(newsItem)"
                                                >
                                                    <img
                                                        width="24"
                                                        height="24"
                                                        src="../../../img/admin/edit.svg"
                                                        alt=""
                                                    />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="pagination-users" v-if="totalPagesNews > 1">
                                    <button :disabled="currentPageNews === 1" @click="currentPageNews--">‹ Назад</button>
                                    <button
                                        v-for="p in totalPagesNews"
                                        :key="p"
                                        :class="{ active: currentPageNews === p }"
                                        @click="currentPageNews = p"
                                    >{{ p }}</button>
                                    <button :disabled="currentPageNews === totalPagesNews" @click="currentPageNews++">Вперёд ›</button>
                                </div>
                                <!-- Модальное окно редактирования новости -->
                                <div v-if="showNewsEditModal" class="modal-overlay">
                                    <div class="modal-content">
                                        <h2>Редактирование новости</h2>
                                        <button class="close-button" @click="closeNewsEditModal">×</button>
                                        <form @submit.prevent="submitNewsEdit" class="course-form">
                                            <!-- Заголовок новости -->
                                            <div class="form-group">
                                                <label class="form-label"
                                                    >Заголовок новости</label
                                                >
                                                <input
                                                    v-model="editingNews.title"
                                                    type="text"
                                                    placeholder="Введите заголовок новостиa"
                                                    class="form-input"
                                                />
                                            </div>
                                            <!-- Краткое описание -->
                                            <div class="form-group">
                                                <label class="form-label"
                                                    >Краткое описание</label
                                                >
                                                <textarea
                                                    v-model="
                                                        editingNews.content
                                                    "
                                                    placeholder="Введите краткое описание"
                                                    class="form-textarea form-textarea--m"
                                                ></textarea>
                                            </div>
                                            <!-- Изображение новости -->
                                            <div class="form-group">
                                                <label class="form-label">Изображение новости</label>
                                                <input type="file" accept="image/*" class="form-input" @change="onNewsImageChangeEdit" />
                                            </div>
                                            <div class="form-group">
                                                <!-- превью и ссылка из БД -->
                                                <label class="form-label">Текущее изображение:</label>
                                                <div v-if="currentNewsFileUrl" class="image_db">
                                                    <img v-if="currentNewsFileIsImage"
                                                        :src="currentNewsFileUrl"
                                                        alt="Изображение новости"
                                                        class="image">
                                                    <a :href="currentNewsFileUrl"
                                                    :download="currentNewsFileName"
                                                    target="_blank" rel="noopener">
                                                    {{ currentNewsFileName }}
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- Текст новости (EditorJS) -->
                                            <div class="form-group">
                                                <label class="form-label"
                                                    >Текст новости</label
                                                >
                                                <div
                                                    id="editorjs-news-edit"
                                                    class="editor-container"
                                                ></div>
                                            </div>
                                            <div class="modal-buttons">
                                                <button
                                                    type="submit"
                                                    class="form-button"
                                                >
                                                    Сохранить изменения
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- Модалка комментариев -->
                                <div v-if="showCommentsModal" class="modal-overlay" @click.self="closeComments">
                                    <div class="modal-content" style="max-width: 800px;">
                                        <button class="close-button" @click="closeComments">×</button>
                                        <h2>Комментарии к: «{{ currentCommentsNewsTitle }}»</h2>

                                        <div v-if="commentsLoading" style="padding: 1rem;">Загрузка…</div>

                                        <div v-else>
                                        <div v-if="!commentsFlat.length" style="padding: .5rem 0;">Комментариев пока нет</div>

                                        <div v-else class="comments-list" style="margin: 1rem 0;">
                                            <div v-for="c in commentsFlat" :key="c.id"
                                                class="comment-item"
                                                :style="{ marginLeft: (c._level * 16) + 'px', borderLeft: c._level ? '2px solid #eee' : 'none', paddingLeft: '8px', marginBottom: '12px' }">
                                            <div class="comment-head" style="font-size:14px;color:#666;">
                                                <strong style="color:#222;">{{ c.user_name || ('Пользователь #'+c.user_id) }}</strong>
                                                <span style="margin-left:8px;">•</span>
                                                <span style="margin-left:8px;">{{ formatDateTime(c.created_at) }}</span>
                                            </div>
                                            <div class="comment-body" style="white-space:pre-wrap; margin:.25rem 0 .5rem;">
                                                {{ c.body }}
                                            </div>
                                            <div class="comment-actions">
                                                <button class="btn__user--edit" @click="toggleReply(c.id)">Ответить</button>
                                                <button class="icon-btn icon-btn--danger"
                                                    :disabled="commentDeleting[c.id]"
                                                    :title="commentDeleting[c.id] ? 'Удаляем…' : 'Удалить комментарий'"
                                                    @click="deleteComment(c.id)"
                                                    aria-label="Удалить комментарий"
                                                >
                                                    Удалить комментарий
                                                </button>
                                            </div>

                                            <div v-if="replyOpen[c.id]" class="reply-box" style="margin-top:.5rem;">
                                                <textarea v-model="replyBodies[c.id]" class="form-textarea" placeholder="Ваш ответ..." />
                                                <div style="text-align:right; margin-top:.5rem;">
                                                <button class="form-button--small" @click="sendReply(c.id)">Отправить</button>
                                                <button class="form-button--small" @click="toggleReply(c.id)">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr/>
                                    <h3 style="margin:.75rem 0;">Новый комментарий</h3>
                                    <textarea v-model="newCommentBody" class="form-textarea" placeholder="Напишите комментарий..." />
                                    <div style="text-align:right; margin-top:.5rem;">
                                        <button class="form-button" @click="sendComment">Отправить</button>
                                    </div>
                                    </div>
                                </div>
                                </div>

                                <h2 class="h2__margin">Опубликовать новость</h2>
                                <form
                                    @submit.prevent="submitNews"
                                    class="course-form"
                                >
                                    <div class="form-group">
                                        <label class="form-label"
                                            >Заголовок новости</label
                                        >
                                        <input
                                            v-model="newNews.title"
                                            type="text"
                                            placeholder="Введите заголовок новости"
                                            class="form-input"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label"
                                            >Краткое описание</label
                                        >
                                        <textarea
                                            v-model="newNews.content"
                                            placeholder="Введите краткое описание"
                                            class="form-textarea form-textarea--m"
                                        ></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label"
                                            >Изображение новости</label
                                        >
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="form-input"
                                            @change="onNewsImageChange"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label"
                                            >Текст новости</label
                                        >
                                        <!-- Контейнер для EditorJS -->
                                        <div
                                            id="editorjs-news"
                                            class="editor-container"
                                        ></div>
                                    </div>
                                    <button type="submit" class="form-button">
                                        Опубликовать новость
                                    </button>
                                </form>
                                <!-- Другой контент -->
                            </div>
                            <div v-else-if="item.id === 'faq'">
                                <table v-if="faqs.length" class="light-push-table light-push-table--s">
                                    <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Вопрос</th>
                                        <th>Ответ</th>
                                        <th>Изменить</th>
                                        <th>Удалить</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(f, i) in paginatedFaqs" :key="f.id">
                                        <td>{{ i + 1 }}</td>
                                        <td>{{ f.question }}</td>
                                        <td>{{ f.answer }}</td>
                                        <td>
                                            <button class="btn__user--edit" @click="startEditingFaq(f)">Редактировать</button>
                                        </td>
                                        <td>
                                            <button class="btn__user--delete" @click="deleteFaq(f.id)">Удалить</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <p v-else>Нет частых вопросов</p>
                                <div class="pagination-users" v-if="totalPagesFaq > 1">
                                    <button
                                        :disabled="currentPageFaq === 1"
                                        @click="currentPageFaq--"
                                    >‹ Назад</button>

                                    <button
                                        v-for="p in totalPagesFaq"
                                        :key="p"
                                        :class="{ active: currentPageFaq === p }"
                                        @click="currentPageFaq = p"
                                    >{{ p }}</button>

                                    <button
                                        :disabled="currentPageFaq === totalPagesFaq"
                                        @click="currentPageFaq++"
                                    >Вперёд ›</button>
                                </div>
                                
                                <div
                                    v-if="editingFaq.id"
                                    class="modal-overlay"
                                    @click.self="cancelEditingFaq"
                                    >
                                    <div class="modal-content modal-flex">
                                        <!-- крестик закрытия -->
                                        <button class="close-button" @click="cancelEditingFaq">×</button>

                                        <h4>Редактирование #{{ editingFaq.id }}</h4>
                                            <div class="form-group">
                                                <label class="form-label"
                                                    >Вопрос</label
                                                >
                                                <input
                                                    v-model="editingFaq.question"
                                                    placeholder="Вопрос"
                                                    class="form-input form-input--xl"
                                                />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label"
                                                    >Ответ на вопрос</label
                                                >
                                                <textarea
                                                    v-model="editingFaq.answer"
                                                    placeholder="Вопрос"
                                                    class="form-input form-input--xl textarea"
                                                />
                                            </div>
                                        <div class="button__edit__faq" style="margin-top:1em; text-align:right">
                                            <button @click="saveEditingFaq" class="form-button--small">
                                                Сохранить
                                            </button>
                                            <button @click="cancelEditingFaq" class="form-button--small">
                                                Отмена
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <h2
                                    class="h2__margin"
                                    for="question"
                                    >Создание частых вопросов</h2
                                >
                                <form @submit.prevent="submitFaq" class="course-form">
                                    <div class="form-group">
                                        <label class="form-label">Вопрос</label>
                                        <input
                                            type="text"
                                            name="question"
                                            v-model="faq.question"
                                            placeholder="Введите вопрос"
                                            class="form-input"
                                        />
                                    </div>
                                    <label class="form-label">Ответ на вопрос</label>
                                    <textarea
                                        name="answer"
                                        v-model="faq.answer"
                                        placeholder="Введите ответ"
                                        class="form-input"
                                    ></textarea>
                                    <button
                                        type="submit"
                                        class="form-button--small"
                                    >
                                        Создать ответ
                                    </button>
                                </form>
                            </div>
                            <div v-else-if="item.id === 'other'">
                                <div class="category">
                                    <!-- Таблица языков -->
                                    <div class="languages-list">
                                        <h2>Существующие категории (языки)</h2>
                                        <!-- Проверяем, есть ли языки -->
                                        <table
                                            v-if="paginatedLangs.length"
                                            class="light-push-table"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>№</th>
                                                    <th>Название языка</th>
                                                    <th>Действия</th>
                                                    <th>Удалить</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(lang, index) in paginatedLangs" :key="lang.id">
                                                    <!-- ID -->
                                                    <td>{{ index + 1 }}</td>

                                                    <!-- Название языка -->
                                                    <td>
                                                        <!-- Если редактируем, показываем input -->
                                                        <div
                                                            v-if="
                                                                editingLanguageId ===
                                                                lang.id
                                                            "
                                                        >
                                                            <input
                                                                class="input__user--edit"
                                                                v-model="
                                                                    editingLanguage.name
                                                                "
                                                            />
                                                        </div>
                                                        <!-- Иначе показываем текст, по клику переходим в режим редактирования -->
                                                        <div
                                                            v-else
                                                            @click="
                                                                startEditingLanguage(
                                                                    lang
                                                                )
                                                            "
                                                            style="
                                                                cursor: pointer;
                                                            "
                                                        >
                                                            {{ lang.name }}
                                                        </div>
                                                    </td>

                                                    <!-- Кнопки сохранить/отмена или редактировать -->
                                                    <td
                                                        v-if="
                                                            editingLanguageId ===
                                                            lang.id
                                                        "
                                                    >
                                                        <button
                                                            class="btn__user--edit"
                                                            @click="
                                                                saveLanguage
                                                            "
                                                        >
                                                            Сохранить
                                                        </button>
                                                        <button
                                                            class="btn__user--edit"
                                                            @click="
                                                                cancelEditingLanguage
                                                            "
                                                        >
                                                            Отмена
                                                        </button>
                                                    </td>
                                                    <td v-else>
                                                        <button
                                                            class="btn__user--edit"
                                                            @click="
                                                                startEditingLanguage(
                                                                    lang
                                                                )
                                                            "
                                                        >
                                                            Редактировать
                                                        </button>
                                                    </td>

                                                    <!-- Кнопка удаления языка -->
                                                    <td>
                                                        <button
                                                            class="btn__user--delete"
                                                            @click="
                                                                deleteLanguage(
                                                                    lang.id
                                                                )
                                                            "
                                                        >
                                                            Удалить
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="pagination-users" v-if="totalPagesLangs > 1">
                                            <button :disabled="currentPageLangs === 1" @click="currentPageLangs--">‹ Назад</button>
                                            <button
                                                v-for="p in totalPagesLangs"
                                                :key="p"
                                                :class="{ active: currentPageLangs === p }"
                                                @click="currentPageLangs = p"
                                            >{{ p }}</button>
                                            <button :disabled="currentPageLangs === totalPagesLangs" @click="currentPageLangs++">Вперёд ›</button>
                                        </div>
                                        <!-- Если массив пуст, выводим текст -->
                                        <p v-else>Нет языков</p>
                                    </div>
                                    <!-- Таблица направлений -->
                                    <div class="directions-list">
                                        <h2>Существующие направления</h2>
                                        <!-- Проверяем, есть ли направления -->
                                        <table
                                            v-if="paginatedDirs.length"
                                            class="light-push-table"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>№</th>
                                                    <th>Название направления</th>
                                                    <th>Редактировать</th>
                                                    <th>Удалить</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(dir, index) in paginatedDirs"
                                                    :key="dir.id"
                                                >
                                                    <td>{{ index + 1 }}</td>
                                                    <td>
                                                        <!-- Если данное направление редактируется, показываем input -->
                                                        <div
                                                            v-if="
                                                                editingDirectionId ===
                                                                dir.id
                                                            "
                                                        >
                                                            <input
                                                                class="input__user--edit"
                                                                v-model="
                                                                    editingDirection.name
                                                                "
                                                            />
                                                        </div>
                                                        <!-- Иначе показываем текст. По клику запускаем режим редактирования -->
                                                        <div
                                                            v-else
                                                            @click="
                                                                startEditingDirection(
                                                                    dir
                                                                )
                                                            "
                                                            style="
                                                                cursor: pointer;
                                                            "
                                                        >
                                                            {{ dir.name }}
                                                        </div>
                                                    </td>
                                                    <td
                                                        v-if="
                                                            editingDirectionId ===
                                                            dir.id
                                                        "
                                                    >
                                                        <button
                                                            class="btn__user--edit"
                                                            @click="
                                                                saveDirection
                                                            "
                                                        >
                                                            Сохранить
                                                        </button>
                                                        <button
                                                            class="btn__user--edit"
                                                            @click="
                                                                cancelEditingDirection
                                                            "
                                                        >
                                                            Отмена
                                                        </button>
                                                    </td>
                                                    <td v-else>
                                                        <button
                                                            class="btn__user--edit"
                                                            @click="
                                                                startEditingDirection(
                                                                    dir
                                                                )
                                                            "
                                                        >
                                                            Редактировать
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <button
                                                            class="btn__user--delete"
                                                            @click="
                                                                deleteDirection(
                                                                    dir.id
                                                                )
                                                            "
                                                        >
                                                            Удалить
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="pagination-users" v-if="totalPagesDirs > 1">
                                            <button :disabled="currentPageDirs === 1" @click="currentPageDirs--">‹ Назад</button>
                                            <button
                                                v-for="p in totalPagesDirs"
                                                :key="p"
                                                :class="{ active: currentPageDirs === p }"
                                                @click="currentPageDirs = p"
                                            >{{ p }}</button>
                                            <button :disabled="currentPageDirs === totalPagesDirs" @click="currentPageDirs++">Вперёд ›</button>
                                        </div>
                                        <!-- Если массив пуст, выводим текст -->
                                        <p v-else>Нет направлений</p>
                                    </div>
                                    <div>
                                        <h2>
                                            Создание новой категории обучения
                                        </h2>
                                        <form
                                            @submit.prevent="
                                                createLanguageCategory
                                            "
                                            class="edit-course-form--small"
                                        >
                                            <label
                                                class="form-label--small"
                                                for="name"
                                                >Название языка
                                                программирования</label
                                            >
                                            <input
                                                v-model="languageCategory"
                                                type="text"
                                                placeholder="Введите язык программирования"
                                                class="form-input"
                                            />
                                            <button
                                                data-v-4274cdba=""
                                                type="submit"
                                                class="form-button--small"
                                            >
                                                Создать категорию
                                            </button>
                                        </form>
                                    </div>
                                    <div>
                                        <h2>Создание нового направления</h2>
                                        <form
                                            class="edit-course-form--small"
                                            @submit.prevent="submitDirection"
                                        >
                                            <label
                                                class="form-label--small"
                                                for="name"
                                                >Название направления</label
                                            >
                                            <input
                                                class="form-input"
                                                type="text"
                                                id="name"
                                                v-model="directionName"
                                                placeholder="Введите направление"
                                            />
                                            <button
                                                data-v-4274cdba=""
                                                type="submit"
                                                class="form-button--small"
                                            >
                                                Создать направление
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <!-- Другой контент -->
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick, watch } from "vue";
import axios from "axios";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";
import { globalNotification } from "../../globalNotification";
import "./style.css"

import iconDashboard from "./../../../img/admin/info.svg";
import iconUsers     from "./../../../img/admin/users.svg";
import iconCourses   from "./../../../img/admin/couse.svg";
import iconCategory  from "./../../../img/admin/couse.svg";
import iconNews      from "./../../../img/admin/newspaper.svg";
import iconFaq       from "./../../../img/admin/newspaper.svg";



const fmtShort = new Intl.DateTimeFormat('ru-RU', {
  day: '2-digit', month: '2-digit', year: 'numeric'
}); // 05.03.2025

const fmtLong = new Intl.DateTimeFormat('ru-RU', {
  day: '2-digit', month: 'long', year: 'numeric'
}); // 05 марта 2025

function difical(difical) {
    if (difical === "basic") {
        return "Базовый";
    } else if (difical === "mixed") {
        return "Смешанный";
    } else if (difical === "advanced") {
        return "Продвинутый";
    } else if (difical === "middle") {
        return "Средний";
    } 
    else {
        return ""; // или "—"
    }
}

function toDateFromISO(iso) {
  if (!iso) return null;
  // Надёжный парсинг 'YYYY-MM-DD' без часовых поясов
  if (typeof iso === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(iso)) {
    const [y, m, d] = iso.split('-').map(Number);
    return new Date(y, m - 1, d);
  }
  const d = new Date(iso);
  return isNaN(+d) ? null : d;
}

function formatBirthday(value, variant = 'short') {
  const date = toDateFromISO(value);
  if (!date) return '—';
  return variant === 'long' ? fmtLong.format(date) : fmtShort.format(date);
}

/* =====================================
   1. Пользователи
===================================== */
// Inline-редактирование всей строки:
const newsItems = ref([]);
const currentPageNews    = ref(1);
const pageSizeNews       = ref(5);  // сколько новостей на страницу
const totalPagesNews     = computed(() =>
  Math.ceil(newsItems.value.length / pageSizeNews.value)
);
const paginatedNews      = computed(() => {
  const start = (currentPageNews.value - 1) * pageSizeNews.value;
  return newsItems.value.slice(start, start + pageSizeNews.value);
});
watch(newsItems, () => (currentPageNews.value = 1));

const editingNewsId = ref(null); // ID редактируемой новости
const editingNews = ref({
    // Копия редактируемой новости
    title: "",
    content: "",
    newsImage: null,
    editorData: {},
});

const editingLanguageId = ref(null);
const editingLanguage = ref(null);
const editingDirectionId = ref(null);
const editingDirection = ref(null);

// Флаг для показа модального окна редактирования
const showNewsEditModal = ref(false);
let editorNewsEdit = null;

/* Функция открытия модального окна редактирования новости */
async function openNewsEditModal(newsItem) {
    console.log("Данные выбранной новости:", newsItem);

    editingNewsId.value = newsItem.id;

    // 1) Если на бэкенде поле называется editor_data, а приходит как строка JSON, парсим:
    let parsedEditorData = {};
    if (newsItem.editor_data) {
        if (typeof newsItem.editor_data === "string") {
            try {
                parsedEditorData = JSON.parse(newsItem.editor_data);
            } catch (err) {
                console.error("Ошибка парсинга editor_data:", err);
                parsedEditorData = {};
            }
        } else {
            // Если уже объект
            parsedEditorData = newsItem.editor_data;
        }
    }

    // 2) Копируем остальные поля (title, content и т.д.)
    editingNews.value = {
        ...newsItem,
        // У нас в коде Vue поле editorData:
        editorData: parsedEditorData,
        newsImage: null, // если нужно редактировать изображение, изначально null
    };

    showNewsEditModal.value = true;

    // Даем Vue отрендерить модалку
    await nextTick();

    // 3) Инициализируем EditorJS
    if (editorNewsEdit) {
        editorNewsEdit.destroy();
        editorNewsEdit = null;
    }
    editorNewsEdit = new EditorJS({
        holder: "editorjs-news-edit",
        data: editingNews.value.editorData,
        placeholder: "Редактируйте контент новости...",
        tools: {
            header: { class: Header, inlineToolbar: ["link"] },
            list: { class: List, inlineToolbar: true },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: "/api/uploadFile",
                        byUrl: "/api/fetchUrl",
                    },
                },
            },
        },
        onChange: async () => {
            const savedData = await editorNewsEdit.save();
            editingNews.value.editorData = savedData;
            console.log("Содержимое EditorJS (savedData):", savedData);
            console.log(
                "editingNews.value.editorData:",
                editingNews.value.editorData
            );
        },
    });
}
/* Функция закрытия модального окна редактирования новости */
function closeNewsEditModal() {
    showNewsEditModal.value = false;
    editingNewsId.value = null;
    editingNews.value = {
        title: "",
        content: "",
        newsImage: null,
        editorData: {},
    };
    if (editorNewsEdit) {
        editorNewsEdit.destroy();
        editorNewsEdit = null;
    }
}

/* Функция отправки изменений новости на сервер */
async function submitNewsEdit () {
  try {
    const fd = new FormData();
    fd.append('title',       editingNews.value.title);
    fd.append('content',      editingNews.value.content);
    fd.append('editor_data', JSON.stringify(editingNews.value.editorData));

    if (editingNews.value.newsImage) {         // ← файл теперь есть
      fd.append('image', editingNews.value.newsImage);
    }
    fd.append('_method', 'PATCH');        // spoof-patch

    const { data } = await axios.post(`/api/news/${editingNews.value.id}`, fd);

    // реактивно меняем объект в списке
    const i = newsItems.value.findIndex(n => n.id === data.news.id);
    if (i !== -1) newsItems.value.splice(i, 1, data.news);

    globalNotification.categoryMessage = 'Новость обновлена';
    globalNotification.type = 'success';
  } catch (err) {
    console.error('Ошибка обновления новости', err);
    globalNotification.categoryMessage = 'Ошибка обновления новости';
    globalNotification.type = 'error';
  } finally {
    editingNews.newsImage = null;
    closeNewsEditModal();
  }
}
/* Функция обработки выбора файла для редактирования изображения новости */
function onNewsImageChangeEdit (e) {
  editingNews.value.newsImage = e.target.files[0] || null;
}

function startEditingLanguage(langItem) {
    editingLanguageId.value = langItem.id;
    editingLanguage.value = { ...langItem }; // копируем, чтобы не менять оригинал напрямую
}
function startEditingDirection(direction) {
    editingDirectionId.value = direction.id;
    // Создаем копию для редактирования, чтобы не менять исходный объект напрямую
    editingDirection.value = { ...direction };
}

// изменение пользователя
 const showUserEditModal = ref(false);
 const editingUser      = ref(null);

 // открыть модалку
 function openUserEditModal(user) {
   editingUser.value = { ...user };
   showUserEditModal.value = true;
 }

// закрыть без сохранения
function closeUserEditModal() {
   showUserEditModal.value = false;
   editingUser.value = null;
}

 // сохранить и закрыть
 async function saveUserModal() {
   try {
     const { id, name, email, phone, birthday, country, role, position } = editingUser.value;     const resp = await axios.patch(`/api/users/${id}`, { name, email, phone, birthday, country, role, position });
     const idx = users.value.findIndex(u => u.id === id);
     if (idx !== -1) users.value[idx] = resp.data.user;
     globalNotification.categoryMessage = 'Пользователь обновлён';
     globalNotification.type = 'success';
   } catch (err) {
    console.error('Ошибка при сохранении пользователя', err);
     globalNotification.categoryMessage = 'Ошибка обновления пользователя';
     globalNotification.type = 'error';
   } finally {
     closeUserEditModal();
   }
}

// Удалени комментария
const commentDeleting = reactive({}); // { [id]: true|false }
async function deleteComment(id) {
  if (!id) return;
  if (!confirm('Удалить этот комментарий и все его ответы?')) return;

  try {
    commentDeleting[id] = true;

    // ВАРИАНТ 1 (проще): отдельный маршрут удаления по id
    // ожидается DELETE /api/comments/{id}
    await axios.delete(`/api/comments/${id}`);

    // ВАРИАНТ 2 (если у тебя удаление повешено на news/{news}/comments/{id})
    // раскомментируй строку ниже и закомментируй предыдущую:
    // await axios.delete(`/api/news/${commentsNewsId.value}/comments/${id}`);

    // Перечитываем список, чтобы убрать удалённые ветки
    await loadComments(commentsNewsId.value);

    globalNotification.categoryMessage = 'Комментарий удалён';
    globalNotification.type = 'success';
  } catch (e) {
    console.error('Ошибка удаления комментария', e);
    globalNotification.categoryMessage = 'Ошибка удаления комментария';
    globalNotification.type = 'error';
  } finally {
    delete commentDeleting[id];
  }
}

// модалка комментариев
function formatDateTime(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (isNaN(+d)) return '—';
  return d.toLocaleString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
}
// ===== Комментарии к новости =====
const showCommentsModal     = ref(false);
const commentsLoading       = ref(false);
const commentsNewsId        = ref(null);
const currentCommentsNewsTitle = ref('');
const commentsRaw           = ref([]);   // плоский список с бэка
const replyOpen             = reactive({}); // { [commentId]: true|false }
const replyBodies           = reactive({}); // { [commentId]: 'text' }
const newCommentBody        = ref('');

// Построение дерева и "списка с уровнями" для удобного рендера
function buildTree(list) {
  const map = {};
  list.forEach(c => { c.children = []; map[c.id] = c; });
  const roots = [];
  list.forEach(c => {
    const pid = c.parent_id;
    if (pid && map[pid]) map[pid].children.push(c);
    else roots.push(c);
  });
  return roots;
}
const commentsTree = computed(() => buildTree(commentsRaw.value));
const commentsFlat = computed(() => {
  const out = [];
  const walk = (nodes, level = 0) => {
    nodes.forEach(n => {
      out.push({ ...n, _level: level });
      if (n.children?.length) walk(n.children, level + 1);
    });
  };
  walk(commentsTree.value);
  return out;
});

// Открыть/закрыть модалку
async function openComments(newsItem) {
  commentsNewsId.value = newsItem.id;
  currentCommentsNewsTitle.value = newsItem.title || '';
  await loadComments(newsItem.id);
  showCommentsModal.value = true;
}
function closeComments() {
  showCommentsModal.value = false;
  commentsRaw.value = [];
  commentsNewsId.value = null;
  currentCommentsNewsTitle.value = '';
  // очистим состояния ответов
  Object.keys(replyOpen).forEach(k => delete replyOpen[k]);
  Object.keys(replyBodies).forEach(k => delete replyBodies[k]);
  newCommentBody.value = '';
}

async function loadComments(newsId) {
  commentsLoading.value = true;
  try {
    const { data } = await axios.get(`/api/news/${newsId}/comments`);
    commentsRaw.value = Array.isArray(data) ? data : (data.comments || []);
  } catch (e) {
    console.error('Не удалось загрузить комментарии', e);
    globalNotification.categoryMessage = 'Ошибка загрузки комментариев';
    globalNotification.type = 'error';
  } finally {
    commentsLoading.value = false;
  }
}

function toggleReply(id) {
  replyOpen[id] = !replyOpen[id];
}

async function sendComment() {
  const body = (newCommentBody.value || '').trim();
  if (!body) return;

  const current = JSON.parse(localStorage.getItem('user') || '{}');
  console.log(current.id);
  try {
    await axios.post(`/api/news/${commentsNewsId.value}/comments`, { 
        body,
        user_id: current.id,
        user_name: current.name
    });
    newCommentBody.value = '';
    await loadComments(commentsNewsId.value);
  } catch (e) {
    console.error('Ошибка отправки комментария', e);
    globalNotification.categoryMessage = 'Ошибка отправки комментария';
    globalNotification.type = 'error';
  }
}

async function sendReply(parentId) {
  const body = (replyBodies[parentId] || '').trim();
  if (!body) return;
  try {
    await axios.post(`/api/news/${commentsNewsId.value}/comments`, {
      body, parent_id: parentId
    });
    replyBodies[parentId] = '';
    replyOpen[parentId] = false;
    await loadComments(commentsNewsId.value);
  } catch (e) {
    console.error('Ошибка отправки ответа', e);
    globalNotification.categoryMessage = 'Ошибка отправки ответа';
    globalNotification.type = 'error';
  }
}

async function saveLanguage() {
    try {
        // Отправляем PATCH-запрос
        const response = await axios.patch(
            `/api/languages/${editingLanguage.value.id}`,
            {
                name: editingLanguage.value.name,
                // если есть другие поля, добавьте их
            }
        );
        // Находим индекс языка в локальном массиве
        const index = languages.value.findIndex(
            (lang) => lang.id === editingLanguage.value.id
        );
        if (index !== -1) {
            languages.value[index] = response.data.language;
        }
        globalNotification.categoryMessage = "Язык успешно обновлён";
        globalNotification.type = "success";
    } catch (error) {
        console.error("Ошибка при обновлении языка:", error);
        globalNotification.categoryMessage = "Ошибка при обновлении языка";
        globalNotification.type = "error";
    } finally {
        editingLanguageId.value = null;
        editingLanguage.value = null;
    }
}

function cancelEditingLanguage() {
    editingLanguageId.value = null;
    editingLanguage.value = null;
}
async function saveDirection() {
    try {
        const response = await axios.patch(
            `/api/directions/${editingDirection.value.id}`,
            {
                name: editingDirection.value.name,
            }
        );
        // Обновляем локальный массив направлений
        const index = directions.value.findIndex(
            (dir) => dir.id === editingDirection.value.id
        );
        if (index !== -1) {
            directions.value[index] = response.data.direction;
        }
        globalNotification.categoryMessage = "Направление обновлено";
        globalNotification.type = "success";
    } catch (error) {
        console.error("Ошибка обновления направления:", error);
        globalNotification.categoryMessage = "Ошибка обновления направления";
        globalNotification.type = "error";
    } finally {
        editingDirectionId.value = null;
        editingDirection.value = null;
    }
}
function cancelEditingDirection() {
    editingDirectionId.value = null;
    editingDirection.value = null;
}
async function deleteUser(userId) {
    // Добавляем подтверждение удаления
    if (!confirm("Вы действительно хотите удалить пользователя?")) return;

    try {
        // Отправляем DELETE-запрос на сервер по адресу /api/users/{id}
        const response = await axios.delete(`/api/users/${userId}`);
        console.log("Пользователь удалён:", response.data);
        globalNotification.categoryMessage = "Пользователь успешно удалён";
        globalNotification.type = "success";

        // Удаляем пользователя из локального массива
        users.value = users.value.filter((userItem) => userItem.id !== userId);
    } catch (error) {
        console.error("Ошибка при удалении пользователя:", error);
        globalNotification.categoryMessage = "Ошибка при удалении пользователя";
        globalNotification.type = "error";
    }
}
async function deleteLanguage(langId) {
    if (!confirm("Вы действительно хотите удалить этот язык?")) return;
    try {
        const response = await axios.delete(`/api/languages/${langId}`);
        console.log("Язык удалён:", response.data);
        globalNotification.categoryMessage = "Язык успешно удалён";
        globalNotification.type = "success";
        languages.value = languages.value.filter((lang) => lang.id !== langId);
    } catch (error) {
        console.error("Ошибка при удалении языка:", error);
        globalNotification.categoryMessage = "Ошибка при удалении языка";
        globalNotification.type = "error";
    }
}
async function deleteDirection(directionId) {
    if (!confirm("Вы действительно хотите удалить направление?")) return;
    try {
        const response = await axios.delete(`/api/directions/${directionId}`);
        globalNotification.categoryMessage = "Направление удалено";
        globalNotification.type = "success";
        directions.value = directions.value.filter(
            (dir) => dir.id !== directionId
        );
    } catch (error) {
        console.error("Ошибка удаления направления:", error);
        globalNotification.categoryMessage = "Ошибка удаления направления";
        globalNotification.type = "error";
    }
}
async function deleteNews(newsId) {
    if (!confirm("Вы действительно хотите удалить новость?")) return;
    try {
        const response = await axios.delete(`/api/news/${newsId}`);
        console.log("Новость удалена:", response.data);
        globalNotification.categoryMessage = "Новость успешно удалена";
        globalNotification.type = "success";
        // Удаляем новость из локального массива
        newsItems.value = newsItems.value.filter((item) => item.id !== newsId);
    } catch (error) {
        console.error("Ошибка при удалении новости:", error);
        globalNotification.categoryMessage = "Ошибка при удалении новости";
        globalNotification.type = "error";
    }
}

const user = ref(null);
const users = ref([]);
const selectedRole = ref("all");
const searchQuery = ref("");
const roleDropdownOpen = ref(false);

const usersTotalLabel = computed(() => {
  const n = users.value.length;
  const mod10 = n % 10;
  const mod100 = n % 100;
  let word = "пользователей";
  if (mod10 === 1 && mod100 !== 11) word = "пользователь";
  else if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) word = "пользователя";
  return `Всего ${n} ${word}`;
});

function resetRoleFilter() {
  selectedRole.value = "all";
  roleDropdownOpen.value = false;
}

const filteredUsers = computed(() => {
  // 1) фильтр по роли
  const base = selectedRole.value === "all"
    ? users.value
    : users.value.filter(u => String(u.role) === selectedRole.value);

  // 2) текстовый поиск
  const q = (searchQuery.value || "").trim().toLowerCase();
  if (!q) return base;

  const qDigits = q.replace(/\D/g, ""); // для поиска по телефону цифрами

  return base.filter(u => {
    const name    = (u.name    || "").toLowerCase();
    const email   = (u.email   || "").toLowerCase();
    const phone   = (u.phone   || "");
    const phoneLc = phone.toLowerCase();
    const phoneDg = phone.replace(/\D/g, "");
    const country = (u.country || "").toLowerCase();
    const bRaw    = (u.birthday || "");               // 'YYYY-MM-DD'
    const bFmt    = formatBirthday(u.birthday) || ""; // 'ДД.ММ.ГГГГ'

    return (
      name.includes(q) ||
      email.includes(q) ||
      country.includes(q) ||
      phoneLc.includes(q) ||
      (qDigits && phoneDg.includes(qDigits)) ||
      bRaw.includes(q) ||
      bFmt.toLowerCase().includes(q)
    );
  });
});


const currentPageUsers = ref(1);
const pageSizeUsers = ref(10);

const totalPagesUsers = computed(() => {
  const size = pageSizeUsers.value || 1;
  return Math.max(1, Math.ceil(filteredUsers.value.length / size));
});

const paginatedUsers = computed(() => {
  const size = pageSizeUsers.value || 10;
  const start = (currentPageUsers.value - 1) * size;
  return filteredUsers.value.slice(start, start + size);
});

watch([selectedRole, searchQuery, pageSizeUsers], () => {
  currentPageUsers.value = 1;
});

function getRoleName(role) {
    switch (role) {
        case 3:
            return "Админ";
        case 2:
            return "Преподаватель";
        case 1:
            return "Ученик";
        default:
            return "Неизвестно";
    }
}
/* =====================================
   2. Курсы, языки и направления
===================================== */
const courses = ref([]);
const languages = ref([]); // Языки программирования

const selectedDifficulty = ref("all");          // basic | middle | advanced | mixed | all
const searchCourseQuery = ref("");
const courseDifficultyDropdownOpen = ref(false);

const coursesTotalLabel = computed(() => {
  const n = courses.value.length;
  if (!n) return "Нет курсов";
  const mod10 = n % 10;
  const mod100 = n % 100;
  let word = "курсов";
  if (mod10 === 1 && mod100 !== 11) word = "курс";
  else if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) word = "курса";
  return `Всего ${n} ${word}`;
});

function resetCourseDifficultyFilter() {
  selectedDifficulty.value = "all";
  courseDifficultyDropdownOpen.value = false;
}

// отфильтрованный список курсов
const filteredCourses = computed(() => {
  let base = courses.value;

  // фильтр по уровню
  if (selectedDifficulty.value !== "all") {
    base = base.filter(
      (c) => (c.difficulty || "").toString() === selectedDifficulty.value
    );
  }

  // текстовый поиск
  const q = (searchCourseQuery.value || "").trim().toLowerCase();
  if (!q) return base;

  return base.filter((c) => {
    const name  = (c.course_name  || "").toLowerCase();
    const title = (c.card_title   || "").toLowerCase();
    const desc  = (c.description  || "").toLowerCase();
    return (
      name.includes(q) ||
      title.includes(q) ||
      desc.includes(q)
    );
  });
});

// ————— пагинация для Языков —————
const currentPageLangs = ref(1);
const pageSizeLangs    = ref(5);
const totalPagesLangs  = computed(() =>
  Math.ceil(languages.value.length / pageSizeLangs.value)
);
const paginatedLangs   = computed(() => {
  const start = (currentPageLangs.value - 1) * pageSizeLangs.value;
  return languages.value.slice(start, start + pageSizeLangs.value);
});
watch(languages, () => (currentPageLangs.value = 1));


//пагинация курсов
const currentPageCourses = ref(1)
const pageSizeCourses    = ref(10)

const totalPagesCourses = computed(() => {
  const size = pageSizeCourses.value || 1;
  return Math.max(1, Math.ceil(filteredCourses.value.length / size));
});

const paginatedCourses = computed(() => {
  const size = pageSizeCourses.value || 10;
  const start = (currentPageCourses.value - 1) * size;
  return filteredCourses.value.slice(start, start + size);
});
watch(courses, () => {
  currentPageCourses.value = 1;
});
watch([selectedDifficulty, searchCourseQuery, pageSizeCourses], () => {
  currentPageCourses.value = 1;
});
//открытие формы создания курса



// Реактивная переменная для направлений
const directions = ref([]);
// ————— пагинация для Направлений —————
const currentPageDirs = ref(1);
const pageSizeDirs    = ref(5);
const totalPagesDirs  = computed(() =>
  Math.ceil(directions.value.length / pageSizeDirs.value)
);
const paginatedDirs   = computed(() => {
  const start = (currentPageDirs.value - 1) * pageSizeDirs.value;
  return directions.value.slice(start, start + pageSizeDirs.value);
});
watch(directions, () => (currentPageDirs.value = 1));



// Состояние для создания курса
const newCourse = ref({
    cardTitle: "",
    courseName: "",
    price: "",
    duration: "",
    description: "",
    hours: "",
    simulators: "",
    difficulty: "basic",
    selectedTeachers: [],
    selectedLanguages: [],
    selectedDirection: null, // <-- новое поле
    upgradeQualification: 0, // по умолчанию "Нет"
    cardImage: null,
    descriptionImage: null,
    pdfFile: null,  
    editorData: {},
});

// Состояние для редактирования курса (в модальном окне)
const editCourse = ref({
    cardTitle: "",
    courseName: "",
    price: "",
    duration: "",
    description: "",
    hours: "",
    simulators: "",
    difficulty: "basic",
    selectedTeachers: [],
    selectedLanguage: "javascript",
    cardImage: null,
    descriptionImage: null,
    pdfFile: null,  
    editorData: {},
});

// Модальное окно редактирования курса
const showEditModal = ref(false);
const selectedCourse = ref(null);

// Список преподавателей (фильтрация по роли)
const teachers = computed(() => {
    return users.value.filter((u) => u.role === 2);
});

/* =====================================
   3. Editor.js
===================================== */
let editorCreate = ref(null); // для создания курса
let editorEdit = ref(null); // для редактирования курса

/* =====================================
   4. Функции работы с файлами
===================================== */
function onFileChange(e) {
    newCourse.value.cardImage = e.target.files[0] || null;
    editCourse.value.cardImage = e.target.files[0] || null;
}
function onDescriptionImageChange(e) {
    newCourse.value.descriptionImage = e.target.files[0] || null;
    editCourse.value.descriptionImage = e.target.files[0] || null;
}

/* =====================================
   5. Создание нового курса
===================================== */
function onPdfChange(e) {
  newCourse.value.pdfFile = e.target.files[0] || null;
  editCourse.value.pdfFile = e.target.files[0] || null;
}

async function submitForm() {
    try {
        // Сохраняем данные из Editor.js
        const editorData = await editorCreate.value.save();
        newCourse.value.editorData = editorData;

        // Формируем formData
        const formData = new FormData();
        formData.append("cardTitle", newCourse.value.cardTitle);
        formData.append("courseName", newCourse.value.courseName);
        formData.append("price", newCourse.value.price);
        formData.append("duration", newCourse.value.duration);
        formData.append("description", newCourse.value.description);
        formData.append("hours", newCourse.value.hours);
        if (newCourse.value.simulators != null) {
            formData.append('simulators', newCourse.value.simulators);
        }
        formData.append("difficulty", newCourse.value.difficulty);
        formData.append(
            "editorData",
            JSON.stringify(newCourse.value.editorData)
        );
        formData.append(
            "teachers",
            JSON.stringify(newCourse.value.selectedTeachers)
        );

        // Пример, если вам нужны массивы языков
        const languageIds = newCourse.value.selectedLanguages.map(
            (lang) => lang.id
        );
        formData.append("language", JSON.stringify(languageIds));
        formData.append("direction", newCourse.value.selectedDirection);

        formData.append(
            "upgradequalification",
            newCourse.value.upgradeQualification
        );

        // Добавляем файлы, если есть
        if (newCourse.value.cardImage) {
            formData.append("cardImage", newCourse.value.cardImage);
        }
        if (newCourse.value.descriptionImage) {
            formData.append(
                "descriptionImage",
                newCourse.value.descriptionImage
            );
        }
        if (newCourse.value.pdfFile) {
            formData.append('pdf', newCourse.value.pdfFile);
        }

        // Отправляем POST-запрос на /api/courses
        const response = await axios.post("/api/courses", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        console.log("Курс создан:", response.data);
        globalNotification.categoryMessage = "Курс создан";
        globalNotification.type = "success";

        // 1) Бэкенд вернул JSON, где есть "redirect_url"
        // 2) Переходим на redirect_url
        window.location.href = response.data.redirect_url;

        // Если редирект делается мгновенно, код дальше может не выполниться.
        // Но если хотите очистить форму до редиректа (или если редирект в другом месте), можете это сделать:
        newCourse.value = {
            cardTitle: "",
            courseName: "",
            price: "",
            duration: "",
            description: "",
            hours: "",
            simulators: "",
            difficulty: "basic",
            selectedTeachers: [],
            selectedLanguages: [],
            selectedDirection: null,
            upgradeQualification: "0", // сброс к значению по умолчанию
            cardImage: null,
            descriptionImage: null,
            pdfFile: null,  
            editorData: {},
        };

        // Очистка EditorJS (по желанию)
        editorCreate.value.clear();
    } catch (error) {
        console.error("Ошибка при создании курса:", error);
        globalNotification.categoryMessage =
            "Заполните все поля для создания курса";
        globalNotification.type = "error";
    }
}

/* =====================================
   6. Редактирование курса
===================================== */
async function updateCourse() {
    if (!selectedCourse.value) return;
    try {
        const editorData = await editorEdit.value.save();
        editCourse.value.editorData = editorData;

        const languageIds = editCourse.value.selectedLanguages.map(
            (lang) => lang.id
        );

        const formData = new FormData();
        formData.append("cardTitle", editCourse.value.cardTitle);
        formData.append("courseName", editCourse.value.courseName);
        formData.append("price", editCourse.value.price);
        formData.append("duration", editCourse.value.duration);
        formData.append("description", editCourse.value.description);
        formData.append("hours", editCourse.value.hours);
        formData.append("simulators", editCourse.value.simulators);
        formData.append("difficulty", editCourse.value.difficulty);
        
        const uniqueTeachers = [...new Set(editCourse.value.selectedTeachers)];
        formData.append("teachers", JSON.stringify(uniqueTeachers));

        formData.append(
            "selectedDirection",
            editCourse.value.selectedDirection
        );
        formData.append(
            "upgradequalification",
            editCourse.value.upgradeQualification
        );

        formData.append("language", JSON.stringify(languageIds));
        // поменял
        formData.append(
            "editorData",
            JSON.stringify(editCourse.value.editorData)
        );

        if (editCourse.value.cardImage) {
            formData.append("cardImage", editCourse.value.cardImage);
        }
        if (editCourse.value.descriptionImage) {
            formData.append(
                "descriptionImage",
                editCourse.value.descriptionImage
            );
        }
        if (editCourse.value.pdfFile) {
            formData.append('pdf', editCourse.value.pdfFile);
        }


        const response = await axios.post(
            `/api/courses/${selectedCourse.value.id}`,
            formData,
            { headers: { "Content-Type": "multipart/form-data" } }
        );
        console.log("Курс обновлён:", response.data);
        globalNotification.categoryMessage = "Курс обновлён";
        globalNotification.type = "success";
        await loadCourses();
        closeEditModal();
    } catch (error) {
        console.error("Ошибка при обновлении курса:", error);
        globalNotification.categoryMessage = "Ошибка при обновлении курса";
        globalNotification.type = "error";
    }
}

/* =====================================
   7. Модальное окно редактирования курса
===================================== */
async function openEditModal(course) {
    selectedCourse.value = course;

    // Если в БД "language" = [5,7,8], то course.language — это массив чисел
    // Если "language" = [{id:5,name:'C++'},...], нужно другое сопоставление

    let selectedLangs = [];
    if (Array.isArray(course.language) && languages.value.length > 0) {
        // Предположим, course.language - массив ID
        selectedLangs = languages.value.filter((lang) =>
            course.language.includes(lang.id)
        );
    }
    const teacherIds = Array.isArray(course.teachers)
    ? course.teachers.map(t => typeof t === 'object' ? t.id : t)
    : []

  // Кладём в v-model
    editCourse.value.selectedTeachers = teacherIds;

    editCourse.value = {
        cardTitle: course.card_title || "",
        courseName: course.course_name || "",
        price: course.price || "",
        duration: course.duration || "",
        description: course.description || "",
        hours: course.hours || "",
        simulators: course.simulators || "",
        difficulty: course.difficulty || "basic",
        selectedTeachers: teacherIds || [],

        // Если course.direction — это ID, то просто присваиваем:
        selectedDirection: course.direction ?? null,

        // Массив объектов (или IDs) для Multiselect:
        selectedLanguages: selectedLangs,

        // 0 или 1:
        upgradeQualification: course.upgradequalification ?? 0,

        cardImage: null,
        descriptionImage: null,
        editorData: course.editor_data || {},
    };

    showEditModal.value = true;
    await nextTick();

    if (editorEdit.value) {
        editorEdit.value.destroy();
        editorEdit.value = null;
    }
    editorEdit.value = new EditorJS({
        holder: "editorjs-edit",
        data: editCourse.value.editorData,
        placeholder: "Редактируйте контент...",
        tools: {
            header: { class: Header, inlineToolbar: ["link"] },
            list: { class: List, inlineToolbar: true },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: "/api/uploadFile",
                        byUrl: "/api/fetchUrl",
                    },
                },
            },
        },
        onChange: async () => {
            const savedData = await editorEdit.value.save();
            editCourse.value.editorData = savedData;
        },
    });
}

function closeEditModal() {
    showEditModal.value = false;
    selectedCourse.value = null;
    if (editorEdit.value) {
        editorEdit.value.destroy();
        editorEdit.value = null;
    }
}

async function deleteCourse(courseId) {
    try {
        // Отправляем DELETE-запрос
        const response = await axios.delete(`/api/courses/${courseId}`);

        // Уведомляем об успехе (если есть глобальная система уведомлений)
        globalNotification.categoryMessage = "Курс удалён";
        globalNotification.type = "success";

        // Локально удаляем курс из массива courses
        courses.value = courses.value.filter(
            (course) => course.id !== courseId
        );
    } catch (error) {
        console.error("Ошибка при удалении курса:", error);
        globalNotification.categoryMessage = "Ошибка при удалении курса";
        globalNotification.type = "error";
    }
}

/* =====================================
   8. Меню, навигация, выход
===================================== */
const activeIndex = ref(0);
const menuItems = [
    { id: "dashboard", label: "Дашборд",        href: "#dashboard", icon: iconDashboard },
    { id: "users",     label: "Пользователи",   href: "#users",     icon: iconUsers },
    { id: "courses",   label: "Курсы",          href: "#courses",   icon: iconCourses },
    { id: "other",     label: "Категории",      href: "#other",     icon: iconCategory },
    { id: "news",      label: "Новости",        href: "#news",      icon: iconNews },
    { id: "faq",       label: "Частые вопросы", href: "#faq",       icon: iconFaq },
];

function saveActiveIndex() {
    localStorage.setItem("activeIndex", activeIndex.value);
}
function setActive(index) {
    activeIndex.value = index;
    saveActiveIndex();
    const targetElement = document.getElementById(menuItems[index].id);
    if (targetElement) {
        targetElement.scrollIntoView({ behavior: "smooth" });
    }
}
function base64ToUtf8(str) {
    return decodeURIComponent(escape(atob(str)));
}
function logout() {
    localStorage.removeItem("user");
    user.value = null;
    window.location.href = "/";
}

/* =====================================
   9. Загрузка данных (пользователи, курсы, языки)
===================================== */
async function loadUsers() {
    try {
        const response = await axios.get("/api/users");
        users.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке пользователей:", error);
        globalNotification.categoryMessage =
            "Ошибка при загрузке пользователей";
        globalNotification.type = "error";
    }
}
async function loadCourses() {
    try {
        const response = await axios.get("/api/courses");
        courses.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке курсов:", error);
        globalNotification.categoryMessage = "Ошибка при загрузке курсов";
        globalNotification.type = "error";
    }
}
async function loadLanguages() {
    try {
        const response = await axios.get("/api/languages");
        languages.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке языков:", error);
        globalNotification.categoryMessage = "Ошибка при загрузке языков";
        globalNotification.type = "error";
    }
}
// Функция загрузки направлений
async function loadDirections() {
    try {
        const response = await axios.get("/api/directions");
        console.log("Направления получены:", response.data); // для отладки
        // Если /api/directions возвращает массив, сохраняем сразу
        directions.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке направлений:", error);
        globalNotification.categoryMessage = "Ошибка при загрузке направлений";
        globalNotification.type = "error";
    }
}
async function loadNews() {
    try {
        const response = await axios.get("/api/news");
        newsItems.value = response.data; // ожидается, что сервер вернет массив объектов новостей
    } catch (error) {
        console.error("Ошибка при загрузке новостей:", error);
        globalNotification.categoryMessage = "Ошибка при загрузке новостей";
        globalNotification.type = "error";
    }
}
/* =====================================
   10. onMounted
===================================== */
onMounted(() => {
    editorCreate.value = new EditorJS({
        holder: "editorjs-create",
        placeholder: "Добавьте контент для нового курса...",
        tools: {
            header: { class: Header, inlineToolbar: ["link"] },
            list: { class: List, inlineToolbar: true },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: "/api/uploadFile",
                        byUrl: "/api/fetchUrl",
                    },
                },
            },
        },
    });

    const urlParams = new URLSearchParams(window.location.search);
    const verifiedUserParam = urlParams.get("verifiedUser");
    if (verifiedUserParam) {
        try {
            const pureBase64 = decodeURIComponent(verifiedUserParam);
            const decodedString = base64ToUtf8(pureBase64);
            const verifiedUserData = JSON.parse(decodedString);
            localStorage.setItem("user", JSON.stringify(verifiedUserData));
            localStorage.removeItem("pendingUser");
            console.log(
                "✅ Email подтвержден, данные пользователя сохранены:",
                verifiedUserData
            );
        } catch (error) {
            console.error(
                "❌ Ошибка при декодировании verifiedUserParam:",
                error
            );
            globalNotification.categoryMessage =
                "Ошибка при подтверждении email";
            globalNotification.type = "error";
        }
    }
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
        try {
            user.value = JSON.parse(storedUser);
            console.log("✅ Пользователь авторизован:", user.value);
        } catch (error) {
            console.error("❌ Ошибка при парсинге localStorage:", error);
            globalNotification.categoryMessage =
                "Ошибка при загрузке данных пользователя";
            globalNotification.type = "error";
        }
    } else {
        console.warn("⚠️ Пользователь НЕ авторизован.");
    }
    const savedIndex = localStorage.getItem("activeIndex");
    if (savedIndex !== null) {
        activeIndex.value = parseInt(savedIndex, 10);
    }

    // Загружаем все необходимые данные
    loadUsers();
    loadCourses();
    loadLanguages();
    loadDirections();
    loadNews();
    loadFaqs();

    // Инициализация EditorJS для создания новости
    editorNews = new EditorJS({
        holder: "editorjs-news", // Элемент с этим id должен быть в шаблоне
        placeholder: "Введите текст новости...",
        tools: {
            header: { class: Header, inlineToolbar: ["link"] },
            list: { class: List, inlineToolbar: true },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: "/api/uploadFile",
                        byUrl: "/api/fetchUrl",
                    },
                },
            },
        },
    });
});

/* =====================================
   11. Добавление категории языка (пример)
===================================== */
const languageCategory = ref("");

async function createLanguageCategory() {
    try {
        const response = await axios.post("/api/languages", {
            name: languageCategory.value,
        });
        globalNotification.categoryMessage =
            response.data.message || "Категория успешно создана!";
        globalNotification.type = "success";
        languageCategory.value = "";
    } catch (error) {
        console.error("Ошибка при создании категории:", error);
        globalNotification.categoryMessage =
            "Заполните все поля для добавления категории";
        globalNotification.type = "error";
    }
}

/* =====================================
   12. Добавление нового направления
===================================== */

const directionName = ref("");
const directionDescription = ref("");


watch(directions, () => (currentPageDirs.value = 1));
async function submitDirection() {
    try {
        const response = await axios.post("/api/directions", {
            name: directionName.value,
            description: directionDescription.value,
        });
        globalNotification.categoryMessage =
            response.data.message || "Направление успешно создано!";
        globalNotification.type = "success";
        // Очистка формы
        directionName.value = "";
        directionDescription.value = "";
    } catch (error) {
        console.error("Ошибка при создании направления:", error);
        globalNotification.categoryMessage =
            "Заполните все поля для добавления направления";
        globalNotification.type = "error";
    }
}

/* =====================================
   13. Дополнительная навигация
===================================== */
function goToCourse(courseId) {
    console.log("Переход на курс с id:", courseId);
    window.location.href = `/cpp/${courseId}`;
}
// Путь, по которому Laravel раздаёт public-disk (storage:link)
const PUBLIC_STORAGE_PREFIX = '/storage/';

const currentNewsFileUrl = computed(() => {
  // сервер может вернуть image / image_path / image_url — берём что есть
  const raw = editingNews.value?.image
          || editingNews.value?.image_path
          || editingNews.value?.image_url
          || '';
  if (!raw) return '';

  // уже абсолютный URL
  if (/^https?:\/\//i.test(raw)) return raw;

  // уже корневой путь, например "/storage/news_images/…"
  if (raw.startsWith('/')) return raw;

  // в БД просто "news_images/…"
  // => делаем публичный URL: "/storage/news_images/…"
  return PUBLIC_STORAGE_PREFIX + raw.replace(/^storage\//, '');
});

const currentNewsFileName = computed(() => {
  const u = currentNewsFileUrl.value || '';
  const name = u.split('/').pop() || '';
  try { return decodeURIComponent(name); } catch { return name; }
});

const currentNewsFileIsImage = computed(() =>
  /\.(png|jpe?g|gif|webp|svg)$/i.test(currentNewsFileUrl.value || '')
);

/* =====================================
   14. Добавление FAQ
   (Новый блок: вставлено после раздела "13. Дополнительная навигация")
===================================== */
const faq = ref({
    question: "",
    answer: "",
});
const faqs = ref([]);
const editingFaq   = reactive({ id:null, question:'', answer:'' });
const currentPageFaq = ref(1);
const pageSizeFaq    = ref(3);
const totalPagesFaq  = computed(() =>
  Math.ceil(faqs.value.length / pageSizeFaq.value)
);
const paginatedFaqs  = computed(() => {
  const start = (currentPageFaq.value - 1) * pageSizeFaq.value;
  return faqs.value.slice(start, start + pageSizeFaq.value);
});
watch(faqs, () => {
  currentPageFaq.value = 1;
});
// — 3.2 Загрузка списка FAQ
async function loadFaqs() {
  try {
    const { data } = await axios.get('/api/faqs');
    faqs.value = data;
  } catch (err) {
    console.error('Не удалось загрузить FAQ:', err);
  }
}
async function submitFaq() {
    try {
        const response = await axios.post("/api/faqs", faq.value);
        console.log("FAQ добавлен:", response.data);
        globalNotification.categoryMessage = "FAQ успешно создан";
        globalNotification.type = "success";
        // Очистка формы
        faq.value.question = "";
        faq.value.answer = "";
    } catch (error) {
        console.error("Ошибка при добавлении FAQ:", error);
        globalNotification.categoryMessage =
            "Заполните все поля, для добавление FAQ";
        globalNotification.type = "error";
    }
}
function startEditingFaq(f) {
  editingFaq.id       = f.id;
  editingFaq.question = f.question;
  editingFaq.answer   = f.answer;
}
async function saveEditingFaq() {
  await axios.put(`/api/faqs/${editingFaq.id}`, {
    question: editingFaq.question,
    answer:   editingFaq.answer
  });
  editingFaq.id = null;
  await loadFaqs();
    globalNotification.categoryMessage = "Вопрос изменен";
    globalNotification.type = "success";
}
function cancelEditingFaq() {
  editingFaq.id = null;
}
// 3.5 Удалить
async function deleteFaq(id) {
  if (!confirm('Удалить этот вопрос?')) return;
  await axios.delete(`/api/faqs/${id}`);
  await loadFaqs();
}
/* =====================================
   15. Создание новости (News)
   (Новый блок: вставлено после раздела "14. Добавление FAQ")
===================================== */
const newNews = ref({
    title: "",
    summary: "",
    newsImage: null,
    editorData: {},
});

// Обработка выбора файла для изображения новости
function onNewsImageChange(e) {
    const file = e.target.files[0] || null;
    console.log("Выбранный файл:", file);
    newNews.value.newsImage = file;
}

// Переменная для EditorJS для новостей
let editorNews = null;

// Функция отправки формы для создания новости
async function submitNews() {
    try {
        // Сохраняем данные из EditorJS
        const editorData = await editorNews.save();
        newNews.value.editorData = editorData;

        // Формируем данные для отправки
        const formData = new FormData();
        formData.append("title", newNews.value.title);
        formData.append("content", newNews.value.content);
        formData.append(
            "editor_data",
            JSON.stringify(newNews.value.editorData)
        );

        // Если загружено изображение новости, добавляем его в форму
        if (newNews.value.newsImage) {
            formData.append("image", newNews.value.newsImage);
        }

        // Отправляем POST-запрос для создания новости на сервер
        const response = await axios.post("/api/news", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        console.log("Новость создана:", response.data);
        globalNotification.categoryMessage = "Новость успешно создана";
        globalNotification.type = "success";

        // Очистка формы: сбрасываем поля объекта newNews
        newNews.value = {
            title: "",
            summary: "",
            newsImage: null,
            editorData: {},
        };

        // Очистка EditorJS: сброс содержимого редактора
        editorNews.clear();
    } catch (error) {
        console.error("Ошибка при создании новости:", error);
        globalNotification.categoryMessage = "Ошибка при создании новости";
        globalNotification.type = "error";
    }
}
</script>

