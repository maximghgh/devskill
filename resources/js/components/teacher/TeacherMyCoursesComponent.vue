<template>
    <div class="teacher__div teacher-courses">
        <TeacherCoursesBlock
            v-model:courses="courses"
            @requestCreateCourse="openCreateCourseDialog"
            @requestEditCourse="openEditCourseDialog"
        />
        <CreateCourseDialog
            v-model="showCreateCourse"
            @courseSaved="onCourseSaved"
        />
        <EditCourseDialog
            v-model="showEditCourse"
            :course="selectedCourse"
            @updated="onCourseSaved"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import TeacherCoursesBlock from "./TeacherCoursesBlock.vue";
import CreateCourseDialog from "../admin/CreateHackathon.vue";
import EditCourseDialog from "../admin/EditCourseDialog.vue";
import "../admin/style.css";

const courses = ref([]);
const showCreateCourse = ref(false);
const showEditCourse = ref(false);
const selectedCourse = ref(null);

function parseTeacherIds(teachers) {
    if (Array.isArray(teachers)) return teachers;
    if (teachers == null) return [];
    if (typeof teachers === "number") return [teachers];
    if (typeof teachers === "string") {
        try {
            const parsed = JSON.parse(teachers);
            if (Array.isArray(parsed)) return parsed;
            if (parsed == null) return [];
            return [parsed];
        } catch (e) {
            return [];
        }
    }
    return [];
}

function getTeacherId() {
    const stored = localStorage.getItem("user");
    if (!stored) return null;
    try {
        const parsed = JSON.parse(stored);
        return parsed?.id ?? null;
    } catch (e) {
        return null;
    }
}

function openCreateCourseDialog() {
    showCreateCourse.value = true;
}

function openEditCourseDialog(course) {
    selectedCourse.value = course;
    showEditCourse.value = true;
}

function onCourseSaved() {
    loadCourses();
}

async function loadCourses() {
    try {
        const teacherId = getTeacherId();
        const { data } = await axios.get("/api/courses");
        const list = Array.isArray(data) ? data : data?.data || [];

        if (!teacherId) {
            courses.value = [];
            return;
        }

        const teacherIdStr = String(teacherId);
        courses.value = list.filter((course) => {
            const ids = parseTeacherIds(course.teachers).map((id) =>
                String(id)
            );
            return ids.includes(teacherIdStr);
        });
    } catch (e) {
        console.error("Ошибка при загрузке курсов преподавателя:", e);
        courses.value = [];
    } finally {
        // no-op
    }
}

onMounted(loadCourses);
</script>

<style scoped>
.teacher-courses {
    padding: 30px 40px;
}
.teacher__div{
    /* margin: 0 auto; */
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
