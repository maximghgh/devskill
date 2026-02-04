<template>
    <div class="center">
        <h2>Журнал</h2>
        <section class="courses">
            <div class="course-accordion">

                <!-- КУРС 1: открыт по умолчанию -->
                <article class="course-accordion__item course-accordion__item--open">
<!--                    <button class="course-accordion__header" type="button">-->
<!--                        <span class="course-accordion__title">Курс: Веб-разработка</span>-->
<!--                        <span class="course-accordion__chevron"></span>-->
<!--                    </button>-->

                    <div class="course-accordion__filter">
                        <div class="dialog__component">
                            <p class="dialog__title">Курс</p>
                            <select
                                class="dialog__input dialog__select"
                                v-model="selectedCourseId"
                            >
                                <option disabled value="">Выберите курс</option>
                                <option
                                    v-for="c in courses"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.course_name || c.card_title || "Без названия" }}
                                </option>
                            </select>
                        </div>

                        <!-- 2) Группа (появляется после выбора курса) -->
                        <div class="dialog__component" v-if="selectedCourseId">
                            <p class="dialog__title">Группа</p>
                            <select
                                class="dialog__input dialog__select"
                                v-model="selectedGroupId"
                                :disabled="groupsLoading || !groupsForCourse.length"
                            >
                                <option disabled value="">Выберите группу</option>
                                <option
                                    v-for="g in groupsForCourse"
                                    :key="g.id"
                                    :value="g.id"
                                >
                                    {{ g.name_group || g.title || `Группа #${g.id}` }}
                                </option>
                            </select>
                            <p v-if="groupsLoading" class="course-filter__note">
                                Загрузка групп...
                            </p>
                            <p
                                v-else-if="!groupsForCourse.length"
                                class="course-filter__note"
                            >
                                Группы еще не сформированы
                            </p>
                        </div>

                        <!-- 3) Урок (появляется после выбора группы) -->
                        <div class="dialog__component" v-if="selectedGroupId">
                            <p class="dialog__title">Урок</p>
                            <select
                                class="dialog__input dialog__select"
                                v-model="selectedLessonId"
                                :disabled="lessonsLoading || !lessonsForGroup.length"
                            >
                                <option disabled value="">Выберите урок</option>
                                <option
                                    v-for="l in lessonsForGroup"
                                    :key="l.id"
                                    :value="l.id"
                                >
                                    {{ l.title }}
                                </option>
                            </select>
                            <p v-if="lessonsLoading" class="course-filter__note">
                                Загрузка уроков...
                            </p>
                            <p
                                v-else-if="!lessonsForGroup.length"
                                class="course-filter__note"
                            >
                                Уроки не найдены
                            </p>
                        </div>
                    </div>

                    <div class="course-accordion__body">
                        <!-- ВСТАВЛЯЕШЬ СВОЙ JOURNAL СЮДА -->
                        <section class="journal" v-if="selectedLessonId">

                            <!-- ТАБЛИЦА ЖУРНАЛА -->
                            <div class="journal__table-wrapper">
                                <table class="journal__table">
                                    <thead class="journal__head">
                                        <tr class="journal__head-row">
                                            <th class="journal__head-cell journal__head-cell--sticky">
                                                <img width="20" height="20" src="../../../img/teacher/table_icon.svg" alt="">
                                                <span class="journal__head-title">ФИО</span>
                                            </th>
                                            <th class="journal__head-cell journal__head-cell--score">
                                                <span class="journal__head-title">Баллы</span>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="journal__body">
                                        <tr v-if="studentsLoading" class="journal__row">
                                            <td class="journal__cell" colspan="2">Загрузка...</td>
                                        </tr>
                                        <tr v-else-if="!studentsForGroup.length" class="journal__row">
                                            <td class="journal__cell" colspan="2">В группе нет участников</td>
                                        </tr>
                                        <template v-else>
                                            <tr
                                                v-for="(student, index) in studentsForGroup"
                                                :key="student.id"
                                                class="journal__row"
                                            >
                                                <td class="journal__cell journal__cell--name">
                                                    <span class="journal__student-index">{{ index + 1 }}</span>
                                                    <span class="journal__student-name">{{ student.name || "Без имени" }}</span>
                                                </td>
                                                <td class="journal__cell journal__cell--value">—</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </article>
            </div>
        </section>

    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            selectedCourseId: "",
            selectedGroupId: "",
            selectedLessonId: "",

            courses: [],
            groupsForCourse: [],
            lessonsForGroup: [],
            studentsForGroup: [],
            groupsLoading: false,
            lessonsLoading: false,
            studentsLoading: false,
        };
    },

    watch: {
        async selectedCourseId(newId) {
            this.selectedGroupId = "";
            this.selectedLessonId = "";
            this.groupsForCourse = [];
            this.lessonsForGroup = [];
            this.studentsForGroup = [];
            this.studentsLoading = false;

            if (!newId) return;

            await Promise.all([this.loadGroups(newId), this.loadLessons(newId)]);
        },
        async selectedGroupId(newId) {
            this.selectedLessonId = "";
            this.studentsForGroup = [];
            this.studentsLoading = false;
            if (!newId || !this.selectedCourseId) return;
            await this.loadGroupStudents(this.selectedCourseId, newId);
        },
    },

    methods: {
        getTeacherId() {
            const stored = localStorage.getItem("user");
            if (!stored) return null;
            try {
                const parsed = JSON.parse(stored);
                return parsed?.id ?? null;
            } catch (e) {
                return null;
            }
        },
        parseTeacherIds(teachers) {
            if (Array.isArray(teachers)) return teachers;
            if (teachers == null) return [];
            if (typeof teachers === "number") return [teachers];
            if (typeof teachers === "string") {
                try {
                    const parsed = JSON.parse(teachers);
                    if (Array.isArray(parsed)) return parsed;
                    if (parsed == null) return [];
                    return [parsed];
                } catch {
                    return [];
                }
            }
            return [];
        },
        async loadCourses() {
            try {
                const teacherId = this.getTeacherId();
                const { data } = await axios.get("/api/courses");
                const list = Array.isArray(data) ? data : data?.data || [];

                if (!teacherId) {
                    this.courses = [];
                    return;
                }

                const teacherIdStr = String(teacherId);
                this.courses = list.filter((course) => {
                    const ids = this.parseTeacherIds(course.teachers).map((id) =>
                        String(id)
                    );
                    return ids.includes(teacherIdStr);
                });
            } catch (e) {
                console.error("Ошибка при загрузке курсов:", e);
                this.courses = [];
            }
        },
        async loadGroups(courseId) {
            this.groupsLoading = true;
            try {
                const { data } = await axios.get(
                    `/api/admin/course/${courseId}/groups`
                );
                this.groupsForCourse = Array.isArray(data)
                    ? data
                    : data?.groups || [];
            } catch (e) {
                console.error("Ошибка при загрузке групп:", e);
                this.groupsForCourse = [];
            } finally {
                this.groupsLoading = false;
            }
        },
        async loadGroupStudents(courseId, groupId) {
            this.studentsLoading = true;
            try {
                const { data } = await axios.get(
                    `/api/admin/course/${courseId}/groups/${groupId}`
                );
                this.studentsForGroup = Array.isArray(data?.students)
                    ? data.students
                    : data?.group?.students || [];
            } catch (e) {
                console.error("Ошибка при загрузке участников группы:", e);
                this.studentsForGroup = [];
            } finally {
                this.studentsLoading = false;
            }
        },
        async loadLessons(courseId) {
            this.lessonsLoading = true;
            try {
                const teacherId = this.getTeacherId();
                if (!teacherId) {
                    this.lessonsForGroup = [];
                    return;
                }
                const { data } = await axios.get(
                    `/api/course/${courseId}/topics`,
                    { params: { user_id: teacherId } }
                );
                const topics = Array.isArray(data?.topics) ? data.topics : [];
                const lessons = [];
                topics.forEach((topic) => {
                    const chapters = Array.isArray(topic?.chapters)
                        ? topic.chapters
                        : [];
                    chapters.forEach((chapter) => {
                        lessons.push({
                            id: chapter.id,
                            title: chapter.title || "Урок",
                        });
                    });
                });
                this.lessonsForGroup = lessons;
            } catch (e) {
                console.error("Ошибка при загрузке уроков:", e);
                this.lessonsForGroup = [];
            } finally {
                this.lessonsLoading = false;
            }
        },
    },

    mounted() {
        this.loadCourses();
    },
};
</script>
