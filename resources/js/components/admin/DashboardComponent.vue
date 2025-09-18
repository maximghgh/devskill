<template>
    <div>
        <div class="container">
            <aside>
                <div class="menu">
                    <div class="aside__menu">
                        <!-- Меню -->
                        <ul class="aside__list">
                            <li class="aside__item"
                                v-for="(item, index) in menuItems"
                                :key="index"
                            >
                                <a
                                    :href="item.href"
                                    :class="[
                                        'aside__link',
                                        {
                                            'aside__link--active':
                                                activeIndex === index,
                                        },
                                    ]"
                                    @click.prevent="setActive(index)"
                                >
                                    {{ item.label }}
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
                            <!-- Блок "Все пользователи" -->
                            <div v-if="item.id === 'users'" class="user-block">
                                <div class="filters">
                                    <label for="roleFilter">Поиск пользователя:</label>
                                    <div class="search">
                                        <select id="roleFilter" v-model="selectedRole" class="role-filter">
                                            <option value="all">Все роли</option>
                                            <option value="3">Админ</option>
                                            <option value="2">Преподаватель</option>
                                            <option value="1">Ученик</option>
                                        </select>

                                        <!-- Новое: поиск -->
                                        <div class="searsh__users">
                                            <input
                                                v-model="searchQuery"
                                                type="text"
                                                class="search-filter"
                                                placeholder="Поиск: ФИО, email, телефон, город, дата"
                                                style="min-width:260px"
                                            />
                                            <button
                                                v-if="searchQuery"
                                                class="btn__user--edit"
                                                @click="searchQuery = ''"
                                                style="padding:6px 10px"
                                            >
                                                Очистить
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="filteredUsers.length > 0">
                                    <table class="light-push-table">
                                        <thead>
                                            <tr>
                                                <th>№</th>
                                                <th>ФИО</th>
                                                <th>Email</th>
                                                <th>Телефон</th>
                                                <th>День рождения</th>
                                                <th>Местоположение</th>
                                                <th>Роль</th>
                                                <th>Действия</th>
                                                <th>Удалить пользователя</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(userItem, index) in paginatedUsers" :key="userItem.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>{{ userItem.name }}</td>
                                                <td>{{ userItem.email }}</td>
                                                <td>{{ userItem.phone }}</td>
                                                <td>{{ formatBirthday(userItem.birthday) }}</td>
                                                <td>{{ userItem.country }}</td>
                                                <!-- роли -->
                                                <td>
                                                    <div v-if="editingUserId ===userItem.id">
                                                        <select
                                                            v-model="
                                                                editingUser.role
                                                            "
                                                            class="select"
                                                            style="
                                                                padding: 5px;
                                                                border: 1px solid
                                                                    #ccc;
                                                                border-radius: 4px;
                                                            "
                                                        >
                                                            <option :value="3">
                                                                Админ
                                                            </option>
                                                            <option :value="2">
                                                                Преподаватель
                                                            </option>
                                                            <option :value="1">
                                                                Ученик
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div v-else @click="startEditing(userItem)" style="cursor: pointer">
                                                        {{
                                                            getRoleName(
                                                                userItem.role
                                                            )
                                                        }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <button class="btn__user--edit" @click="openUserEditModal(userItem)">Редактировать</button>
                                                </td>
                                                <td>
                                                    <button class="btn__user--delete" @click="deleteUser(userItem.id)">Удалить пользователя</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else>Нет пользователей</div>
                                <div class="pagination-users" v-if="totalPagesUsers > 1">
                                    <button
                                        :disabled="currentPageUsers === 1"
                                        @click="currentPageUsers--"
                                    >‹ Назад</button>

                                    <button
                                        v-for="p in totalPagesUsers"
                                        :key="p"
                                        :class="{ active: currentPageUsers === p }"
                                        @click="currentPageUsers = p"
                                    >{{ p }}</button>

                                    <button
                                        :disabled="currentPageUsers === totalPagesUsers"
                                        @click="currentPageUsers++"
                                    >Вперёд ›</button>
                                </div>
                                
                                <!-- Модальное окно -->
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
                            <div v-else-if="item.id === 'courses'">
                                <a class="link__add" href="/admin/addcourse">Cоздать курс +</a>
                                <div class="admin__block">
                                    <div class="cou">
                                        <div class="div__link">
                                            <a
                                                class="course__links"
                                                href="/admin/statistics"
                                                >Статистика учеников</a
                                            >
                                            <a
                                                class="course__links"
                                                href="/admin/statisticspurchase"
                                                >Cтатистика покупок пользователей</a
                                            >
                                            <a
                                                class="course__links"
                                                href="/admin/consultations"
                                                >Записи на консультации</a
                                            >
                                        </div>
                                    </div>
                                    <div v-if="paginatedCourses.length">
                                        <table class="light-push-table">
                                            <thead>
                                                <tr>
                                                    <th>№</th>
                                                    <th>Название курса</th>
                                                    <th>Уровень</th>
                                                    <th>Изменить курс</th>
                                                    <th>Управлять курсом</th>
                                                    <th>Удалить курс</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(course, index) in paginatedCourses"
                                                    :key="course.id"
                                                >
                                                    <td>
                                                        {{ index + 1 }}
                                                    </td>
                                                    <td>
                                                        {{ course.course_name }}
                                                    </td>
                                                    <td>{{ course.difficulty }}</td>
                                                    <td>
                                                        <button
                                                            class="btn__user--edit"
                                                            @click.prevent="
                                                                openEditModal(
                                                                    course
                                                                )
                                                            "
                                                        >
                                                            Редактировать
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <a
                                                            class="btn--control"
                                                            :href="`/admin/course/${course.id}/topics/json`"
                                                            >Управлять курсом</a
                                                        >
                                                    </td>
                                                    <td>
                                                        <button class="btn__user--delete" @click.prevent="deleteCourse(course.id)">Удалить курс</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="pagination-users" v-if="totalPagesCourses > 1" style="margin-top: 20px;">
                                            <button
                                            :disabled="currentPageCourses === 1"
                                            @click="currentPageCourses--"
                                            >‹ Назад</button>

                                            <button
                                            v-for="p in totalPagesCourses"
                                            :key="p"
                                            :class="{ active: currentPageCourses === p }"
                                            @click="currentPageCourses = p"
                                            >{{ p }}</button>

                                            <button
                                            :disabled="currentPageCourses === totalPagesCourses"
                                            @click="currentPageCourses++"
                                            >Вперёд ›</button>
                                        </div>
                                    </div>
                                    <div v-else>Нет курсов</div>
                                </div>

                                <!-- Модальное окно редактирования курса -->
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
                                <!-- Конец модального окна -->
                            </div>
                            <!-- Другие блоки (аналитика, FAQ, и т.д.) -->
                            <div v-else-if="item.id === 'news'">
                                <table v-if="paginatedNews.length" class="light-push-table">
                                    <thead>
                                        <tr>
                                            <th>№</th>
                                            <th>Название новости</th>
                                            <th>Просмотреть комментарии</th>
                                            <th>Редактировать</th>
                                            <th>Удалить</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(newsItem, index) in paginatedNews" :key="newsItem.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ newsItem.title }}</td>
                                            <td><a href="#" @click.prevent="openComments(newsItem)">Просмотреть комментарии</a></td>
                                            <td>
                                                <button class="btn__user--edit" @click="openNewsEditModal(newsItem)">Редактировать</button>
                                            </td>
                                            <td>
                                                <button class="btn__user--delete" @click="deleteNews(newsItem.id)">Удалить</button>
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

const fmtShort = new Intl.DateTimeFormat('ru-RU', {
  day: '2-digit', month: '2-digit', year: 'numeric'
}); // 05.03.2025

const fmtLong = new Intl.DateTimeFormat('ru-RU', {
  day: '2-digit', month: 'long', year: 'numeric'
}); // 05 марта 2025

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
const pageSizeUsers = 10;

const totalPagesUsers = computed(() =>
  Math.ceil(filteredUsers.value.length / pageSizeUsers)
);

const paginatedUsers = computed(() => {
  const start = (currentPageUsers.value - 1) * pageSizeUsers;
  return filteredUsers.value.slice(start, start + pageSizeUsers);
});

watch(selectedRole, () => { currentPageUsers.value = 1; });
watch(searchQuery,   () => { currentPageUsers.value = 1; });

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
const pageSizeCourses    = ref(8)

const totalPagesCourses = computed(() =>
  Math.ceil(courses.value.length / pageSizeCourses.value)
)

const paginatedCourses = computed(() => {
  const start = (currentPageCourses.value - 1) * pageSizeCourses.value
  return courses.value.slice(start, start + pageSizeCourses.value)
})
watch(courses, () => {
  currentPageCourses.value = 1
})
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
    { id: "users", label: "Пользователи", href: "#users" },
    { id: "courses", label: "Курсы", href: "#courses" },
    { id: "other", label: "Категории", href: "#other" },
    { id: "news", label: "Новости", href: "#news" },
    { id: "faq", label: "Частые вопросы", href: "#faq" },
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

<style scoped>
.icon-btn{
  border:none; background:#f3f4f6; color:#6b7280; cursor:pointer;
  padding:6px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center;
  transition:background .15s,color .15s, transform .05s;
}
.icon-btn:hover{ background:#e5e7eb; color:#111827 }
.icon-btn:active{ transform:translateY(1px) }
.icon-btn--danger{ background:#fee2e2; color:#b91c1c }
.icon-btn--danger:hover{ background:#fecaca; color:#7f1d1d }
.icon-btn[disabled]{ opacity:.6; cursor:not-allowed }

.image{
    width: 10%;
    /* height: 75px; */
}
.image_db{
    display: flex;
    align-items: center;
    gap: 10px;
}
.search{
    display: flex;
    gap: 20px;
}
.search-filter{
    font-family: JanoSansProLight;
    padding: 7px 9px;
    border: 1px solid #d0d0d0;
    border-radius: 6px;
    background-color: #f8f9fa;
}
.form-textarea--m{
    width: 678px !important;
}
.modal-flex{
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.button__edit__faq{
    display: flex; gap: 20px;
}
.textarea{
    resize: unset;
    height: 80px;
}
.form-input--xl{
    width: 800px;
}
.form-textarea{
    width: 100%;
}
/* пагинация */

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
/* // */
.form-button--s{
    width: 280px !important;
}
.modal-content--s{
    width: 600px !important;
}
.course__links {
    color: #0056b3;
    text-decoration: none;
}
.course__links:hover {
    text-decoration: underline;
}
.cou {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 0 0 30px;
}
.h2__margin {
    margin: 40px 0;
}
.btn--control {
    text-decoration: none;
    color: green;
}
.btn__user--delete {
    cursor: pointer;
    background: none;
    border: none;
    color: red;
}
.select {
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #fff;
}
.btn__user--edit {
    cursor: pointer;
    border: none;
    background: none;
    color: #007bff;
}
.input__user--edit {
    font-family: JanoSansProLight;
    height: 100%;
    font-size: 20px;
    color: #000;
    border: 1px solid #e0e0e0;
    padding: 5px;
    font-size: 14px;
}
::v-deep .multiselect__tags {
    overflow: hidden;
    padding: 12px 40px 0 12px; /* или больше, чтобы текст расположился ниже */
}
::v-deep .multiselect__input {
    margin-top: 15px;
    padding: 10px; /* или больше, чтобы текст расположился ниже */
}
::v-deep .multiselect__option {
    display: flex;
    align-items: center;
    cursor: pointer;
    gap: 8px;
    margin: 2px 0;
    padding: 6px 8px;
    border-radius: 6px;
    transition: background-color 0.3s, transform 0.2s;
}

/* Лёгкий эффект при наведении */
::v-deep .multiselect__option:hover {
    background-color: #f7f5fa; /* светлый оттенок */
    transform: scale(1.01);
}

/* Квадратик-превращённый-в-пузырь */
::v-deep .checkbox {
    width: 18px;
    height: 18px;
    border-radius: 50%; /* делаем круг вместо квадрата */
    border: 2px solid #b4b0c4; /* пастельный фиолетовый */
    background-color: transparent;
    transition: background-color 0.3s, border-color 0.3s;
}

/* Когда пункт выбран, цвет пузыря меняется */
::v-deep .checkbox.checkbox-checked {
    background-color: #a26ce9; /* насыщенный пастельный фиолетовый */
    border-color: #a26ce9;
}

/* Обёртка для SVG */
::v-deep .checkmark {
    position: relative;
    display: inline-block;
    width: 18px;
    height: 18px;
    /* Можно наложить иконку поверх круга 
     через position: absolute, если хотите */
}

/* SVG-галочка */
::v-deep .checkmark__icon {
    display: none;
    fill: #fff;
    width: 18px;
    height: 18px;
}

/* Показываем галочку, когда чекбокс залит */
::v-deep .checkbox.checkbox-checked + .checkmark .checkmark__icon {
    display: block;
}

/* Название языка */
::v-deep .option-name {
    font-size: 14px;
    color: #333;
}

/* Заголовок (слот selection) 
   где перечисляются выбранные языки */
::v-deep .selection-header {
    font-weight: 500;
    color: #6b6680;
}

::v-deep .selection-header {
    margin: 0 0 20px;
}

.page__title {
    margin: 0 0 60px;
}

.link__add{
    position: absolute;
    top: 0px;
    right: 0px;
    text-decoration: none;
    list-style: none;
    color: #007bff;
    border: 1px solid #007bff;
    border-radius: 10px;
    padding: 8px 12px;
    transition: 0.3s ease-in-out;
    z-index: 1000;
}

.link__add:hover{
    background: #007bff;
    color: #ffffff;
}

h2 {
    font-size: 30px;
    margin: 0 0 25px;
}

.option-form {
    margin-bottom: 5px;
}

.course-form-container {
    margin: 35px auto;
    font-family: Arial, sans-serif;
}

.course-form,
.edit-course-form {
    display: grid;
    grid-template-columns: 1fr;
    width: 700px;
    gap: 20px;
    margin: 0 auto 40px;
}

.edit-course-form--small {
    display: grid;
    grid-template-columns: 1fr;
    width: 100%;
    gap: 20px;
    margin: 0 auto 40px;
}

.category {
    display: grid;
    grid-template-columns: repeat(2, 600px);
    justify-content: space-evenly;
    row-gap: 50px;
}

.edit-course-form-container {
    width: 100%;
}

.form-column {
    flex: 1;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-family: JanoSansProLight;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
}

.form-label--small {
    font-family: JanoSansProLight;
    font-weight: bold;
    margin-bottom: 8px;
    text-align: center;
    color: #333;
}

.form-input,
.form-textarea {
    font-family: JanoSansProLight;
    outline: none;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

.form-textarea {
    resize: none;
    min-height: 80px;
}

.editor-container {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-height: 150px;
    background-color: #fff;
    max-width: 700px;
}

.form-button {
    width: 700px;
    background-color: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin: 0 auto;
}
.form-button--small {
    width: 100%;
    background-color: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin: 0 auto;
}

.form-button:hover {
    background-color: #0056b3;
}

.user-block {
    width: 100%;
}

.menu {
    width: 1440px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.container {
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.filters {
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Стилизация контейнера select */
.role-filter {
    /* Установите максимальную ширину */
    padding: 5px 6px;
    font-size: 16px;
    border: 1px solid #d0d0d0;
    border-radius: 6px;
    background-color: #f8f9fa;
    /* Светлый фон */
    color: #333;
    outline: none;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

/* Стилизация при наведении */
.role-filter:hover {
    border-color: #007bff;
    /* Голубой оттенок */
}

/* Стилизация при фокусе */
.role-filter:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.4);
}

/* Стилизация опций */
.role-filter option {
    background-color: #ffffff;
    padding: 10px;
    font-size: 16px;
}

.aside__menu {
    border-radius: 15px;
    height: 100%;
    padding: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.aside__list {
    display: flex;
    gap: 20px;
}

.aside__item {
    padding: 5px;
}

.aside__link {
    color: #575adf;
    text-decoration: none;
    transition: 0.5s;
}

.aside__link--active {
    color: #000;
    text-decoration: underline;
}

.aside__link:hover {
    transition: 0.5s;
    text-decoration: underline;
}



table.light-push-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    margin: 0 0 30px;
}


.light-push-table--small {
  width: 50%;        /* таблица займет 50% контейнера */
  max-width: 600px;  /* при желании ограничьте максимальную ширину */
  margin: 0 auto;    /* по центру */
  box-sizing: border-box;
}

.light-push-table--small .light-push-table th,
.light-push-table--small .light-push-table td {
    font-size: 12px;
    white-space: unset;
}

.light-push-table th,
.light-push-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e0e0e0;
    text-align: left;
    font-size: 14px;
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

/* Стили модального окна */
.modal-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: flex-start;
    /* Прижимаем окно к верхней границе, если контента много */
    justify-content: center;
    padding: 40px 20px;
    /* Отступы вокруг окна */
    overflow-y: auto;
    /* Скролл при большом контенте */
}

.modal-content {
    background: #fff;
    width: 900px;
    max-width: 90%;
    /* Убираем max-height и overflow-y */
    margin: 40px 0;
    /* Дополнительные отступы сверху/снизу */
    padding: 20px;
    border-radius: 8px;
    position: relative;
}

.close-button {
    position: absolute;
    top: 18px;
    right: 18px;
    background: transparent;
    border: none;
    font-size: 20px;
    cursor: pointer;
}
</style>
