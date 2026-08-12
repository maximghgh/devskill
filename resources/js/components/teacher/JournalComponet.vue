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
                                :disabled="lessonsLoading"
                            >
                                <option disabled value="">Выберите урок</option>
                                <option
                                    v-for="l in lessonsForGroup"
                                    :key="l.id"
                                    :value="l.id"
                                >
                                    {{ l.title }}
                                </option>
                                <option :value="REVIEW_OPTION">
                                    Отзыв за курс
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
                                            <th
                                                v-if="!isReviewMode"
                                                class="journal__head-cell journal__head-cell--score"
                                            >
                                                <span class="journal__head-title">Баллы</span>
                                            </th>
                                            <th
                                                v-else
                                                class="journal__head-cell journal__head-cell--review"
                                            >
                                                <span class="journal__head-title">Отзыв за курс</span>
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
                                                <td
                                                    v-if="!isReviewMode"
                                                    class="journal__cell journal__cell--value"
                                                >
                                                    <input
                                                        class="journal__score-input"
                                                        type="number"
                                                        min="0"
                                                        max="100"
                                                        placeholder="—"
                                                        v-model.number="scoresByStudent[student.id]"
                                                        :disabled="scoresLoading || scoreSaving[student.id]"
                                                        @change="saveScore(student.id)"
                                                    />
                                                </td>
                                                <td v-else class="journal__cell journal__cell--review">
                                                    <div class="journal__review">
                                                        <textarea
                                                            class="journal__review-input"
                                                            rows="2"
                                                            placeholder="Отзыв о ребёнке для родителя"
                                                            v-model="reviewsByStudent[student.id]"
                                                            :disabled="reviewsLoading || reviewSaving[student.id]"
                                                        ></textarea>
                                                        <div class="journal__review-actions">
                                                            <span
                                                                v-if="isReviewDirty(student.id)"
                                                                class="journal__review-hint"
                                                            >
                                                                Есть несохранённые изменения
                                                            </span>
                                                            <button
                                                                type="button"
                                                                class="journal__review-save"
                                                                :disabled="
                                                                    reviewsLoading ||
                                                                    reviewSaving[student.id] ||
                                                                    !isReviewDirty(student.id)
                                                                "
                                                                @click="saveReview(student.id)"
                                                            >
                                                                {{
                                                                    reviewSaving[student.id]
                                                                        ? "Сохранение…"
                                                                        : "Сохранить"
                                                                }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
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
import { globalNotification } from "../../globalNotification";

export default {
    data() {
        return {
            // Псевдо-урок в конце списка: отзыв ставится один на весь курс.
            REVIEW_OPTION: "__review__",

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

            scoresByStudent: {},
            scoresLoading: false,
            scoreSaving: {},

            // Отзыв преподавателя — на ученика в рамках курса,
            // поэтому не зависит от выбранного занятия.
            reviewsByStudent: {},
            savedReviews: {},
            reviewsLoading: false,
            reviewSaving: {},
        };
    },

    computed: {
        isReviewMode() {
            return this.selectedLessonId === this.REVIEW_OPTION;
        },
    },

    watch: {
        async selectedCourseId(newId) {
            this.selectedGroupId = "";
            this.selectedLessonId = "";
            this.groupsForCourse = [];
            this.lessonsForGroup = [];
            this.studentsForGroup = [];
            this.studentsLoading = false;
            this.reviewsByStudent = {};
            this.savedReviews = {};

            if (!newId) return;

            await Promise.all([this.loadGroups(newId), this.loadLessons(newId)]);
        },
        async selectedGroupId(newId) {
            this.selectedLessonId = "";
            this.studentsForGroup = [];
            this.studentsLoading = false;
            this.reviewsByStudent = {};
            this.savedReviews = {};
            if (!newId || !this.selectedCourseId) return;
            await this.loadGroupStudents(this.selectedCourseId, newId);
            // Отзывы грузим после состава группы — нужны id учеников.
            await this.loadReviews();
        },
        async selectedLessonId(newId) {
            this.scoresByStudent = {};
            if (!newId || !this.studentsForGroup.length) return;
            // В режиме отзыва баллы не нужны — отзывы уже загружены с составом группы.
            if (newId === this.REVIEW_OPTION) return;
            await this.loadScores(newId);
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
                if (this.selectedLessonId) {
                    await this.loadScores(this.selectedLessonId);
                }
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
        async loadScores(lessonId) {
            const studentIds = this.studentsForGroup.map((s) => s.id).filter(Boolean);
            if (!lessonId || lessonId === this.REVIEW_OPTION || !studentIds.length) {
                this.scoresByStudent = {};
                return;
            }
            this.scoresLoading = true;
            try {
                const { data } = await axios.get("/api/lesson-scores", {
                    params: {
                        chapter_id: lessonId,
                        student_ids: studentIds.join(","),
                    },
                });
                const map = {};
                (Array.isArray(data) ? data : []).forEach((row) => {
                    map[row.user_id] = row.score;
                });
                this.scoresByStudent = map;
            } catch (e) {
                console.error("Ошибка при загрузке баллов:", e);
                this.scoresByStudent = {};
            } finally {
                this.scoresLoading = false;
            }
        },
        async saveScore(studentId) {
            const teacherId = this.getTeacherId();
            if (!teacherId || !this.selectedLessonId) return;

            const value = this.scoresByStudent[studentId];
            if (value === null || value === undefined || value === "") return;
            if (Number(value) > 100 || Number(value) < 0) {
                if (Number(value) > 100) {
                    globalNotification.categoryMessage =
                        "Значение должно быть в диапазоне от 0 до 100";
                } else {
                    globalNotification.categoryMessage =
                        "Значение должно быть в диапазоне от 0 до 100";
                }
                globalNotification.type = "error";
                return;
            }

            this.scoreSaving = { ...this.scoreSaving, [studentId]: true };
            try {
                await axios.post("/api/lesson-scores", {
                    chapter_id: this.selectedLessonId,
                    user_id: studentId,
                    teacher_id: teacherId,
                    score: Number(value),
                });
            } catch (e) {
                console.error("Ошибка при сохранении балла:", e);
            } finally {
                this.scoreSaving = { ...this.scoreSaving, [studentId]: false };
            }
        },

        isReviewDirty(studentId) {
            const current = (this.reviewsByStudent[studentId] || "").trim();
            const saved = (this.savedReviews[studentId] || "").trim();
            return current !== saved;
        },

        async loadReviews() {
            const studentIds = this.studentsForGroup
                .map((s) => s.id)
                .filter(Boolean);
            if (!this.selectedCourseId || !studentIds.length) {
                this.reviewsByStudent = {};
                this.savedReviews = {};
                return;
            }

            this.reviewsLoading = true;
            try {
                const { data } = await axios.get("/api/student-reviews", {
                    params: {
                        course_id: this.selectedCourseId,
                        student_ids: studentIds.join(","),
                    },
                });
                const map = {};
                (Array.isArray(data) ? data : []).forEach((row) => {
                    map[row.user_id] = row.review || "";
                });
                this.reviewsByStudent = map;
                this.savedReviews = { ...map };
            } catch (e) {
                console.error("Ошибка при загрузке отзывов:", e);
                this.reviewsByStudent = {};
                this.savedReviews = {};
            } finally {
                this.reviewsLoading = false;
            }
        },

        async saveReview(studentId) {
            if (!this.selectedCourseId) return;

            const teacherId = this.getTeacherId();
            const text = (this.reviewsByStudent[studentId] || "").trim();

            this.reviewSaving = { ...this.reviewSaving, [studentId]: true };
            try {
                await axios.post("/api/student-reviews", {
                    user_id: studentId,
                    course_id: this.selectedCourseId,
                    teacher_id: teacherId,
                    review: text,
                });
                this.savedReviews = { ...this.savedReviews, [studentId]: text };
                globalNotification.categoryMessage = "Отзыв сохранён";
                globalNotification.type = "success";
            } catch (e) {
                console.error("Ошибка при сохранении отзыва:", e);
                globalNotification.categoryMessage = "Не удалось сохранить отзыв";
                globalNotification.type = "error";
            } finally {
                this.reviewSaving = { ...this.reviewSaving, [studentId]: false };
            }
        },
    },

    mounted() {
        this.loadCourses();
    },
};
</script>

<style scoped>
/* Таблица журнала занимает всю ширину, при нехватке места — скролл в обёртке */
.journal__table {
    min-width: 640px;
}

/* Заголовок ФИО должен остаться ячейкой таблицы, иначе колонки разъезжаются */
.journal__head-cell--sticky {
    display: table-cell;
    vertical-align: middle;
}
.journal__head-cell--sticky img {
    vertical-align: middle;
    margin-right: 9px;
}

.journal__head-cell--sticky,
.journal__cell--name {
    width: 40%;
}
.journal__head-cell--score,
.journal__cell--value {
    width: 120px;
}
.journal__head-cell--review,
.journal__cell--review {
    min-width: 260px;
    width: calc(60% - 120px);
}

/* строки: воздух и разделители, чтобы 10 учеников читались списком */
.journal__cell {
    padding: 10px 8px;
    vertical-align: middle;
}
.journal__row + .journal__row .journal__cell {
    border-top: 1px solid #ededf3;
}
.journal__row:hover .journal__cell {
    background: #faf9ff;
}
.journal__review-input {
    box-sizing: border-box;
    width: 100%;
    min-height: 44px;
    padding: 8px 10px;
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    font: inherit;
    resize: vertical;
    outline: none;
    background: #fff;
}
.journal__review-input:focus {
    border-color: #6c5ce7;
}
.journal__review-input:disabled {
    opacity: 0.6;
}

.journal__review {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.journal__review-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
}
.journal__review-hint {
    font-size: 13px;
    color: #a06a00;
}
.journal__review-save {
    padding: 8px 18px;
    border: 0;
    border-radius: 8px;
    background: #6c5ce7;
    color: #fff;
    font: inherit;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.journal__review-save:hover:not(:disabled) {
    background: #5a4bd1;
}
.journal__review-save:disabled {
    background: #c9c5e6;
    cursor: default;
}

.dialog__select {
    padding-right: 34px;
    text-overflow: ellipsis;
}
</style>
