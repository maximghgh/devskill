<template>
    <div class="user-block">
        <h1 class="page__title">Категории</h1>

        <div class="category">
            <!-- ===== ЯЗЫКИ ===== -->
            <div class="languages-list">
                <h2>Существующие категории (языки)</h2>

                <table v-if="paginatedLangs.length" class="light-push-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Название языка</th>
                            <th>Действия</th>
                            <th>Удалить</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(lang, index) in paginatedLangs"
                            :key="lang.id"
                        >
                            <td>
                                {{
                                    index +
                                    1 +
                                    (currentPageLangs - 1) * pageSizeLangs
                                }}
                            </td>

                            <td>
                                <div v-if="editingLanguageId === lang.id">
                                    <input
                                        class="input__user--edit"
                                        v-model="editingLanguage.name"
                                    />
                                </div>
                                <div
                                    v-else
                                    @click="startEditingLanguage(lang)"
                                    style="cursor: pointer"
                                >
                                    {{ lang.name }}
                                </div>
                            </td>

                            <td v-if="editingLanguageId === lang.id">
                                <button
                                    class="btn__user--edit"
                                    @click="saveLanguage"
                                >
                                    Сохранить
                                </button>
                                <button
                                    class="btn__user--edit"
                                    @click="cancelEditingLanguage"
                                >
                                    Отмена
                                </button>
                            </td>
                            <td v-else>
                                <button
                                    class="btn__user--edit"
                                    @click="startEditingLanguage(lang)"
                                >
                                    Редактировать
                                </button>
                            </td>

                            <td>
                                <button
                                    class="btn__user--delete"
                                    @click="deleteLanguage(lang.id)"
                                >
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="pagination-users" v-if="totalPagesLangs > 1">
                    <button
                        :disabled="currentPageLangs === 1"
                        @click="currentPageLangs--"
                    >
                        ‹ Назад
                    </button>
                    <button
                        v-for="p in totalPagesLangs"
                        :key="p"
                        :class="{ active: currentPageLangs === p }"
                        @click="currentPageLangs = p"
                    >
                        {{ p }}
                    </button>
                    <button
                        :disabled="currentPageLangs === totalPagesLangs"
                        @click="currentPageLangs++"
                    >
                        Вперёд ›
                    </button>
                </div>

                <p v-else>Нет языков</p>
            </div>

            <!-- ===== НАПРАВЛЕНИЯ ===== -->
            <div class="directions-list">
                <h2>Существующие направления</h2>

                <table v-if="paginatedDirs.length" class="light-push-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Название направления</th>
                            <th>Редактировать</th>
                            <th>Удалить</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(dir, index) in paginatedDirs" :key="dir.id">
                            <td>
                                {{
                                    index +
                                    1 +
                                    (currentPageDirs - 1) * pageSizeDirs
                                }}
                            </td>

                            <td>
                                <div v-if="editingDirectionId === dir.id">
                                    <input
                                        class="input__user--edit"
                                        v-model="editingDirection.name"
                                    />
                                </div>
                                <div
                                    v-else
                                    @click="startEditingDirection(dir)"
                                    style="cursor: pointer"
                                >
                                    {{ dir.name }}
                                </div>
                            </td>

                            <td v-if="editingDirectionId === dir.id">
                                <button
                                    class="btn__user--edit"
                                    @click="saveDirection"
                                >
                                    Сохранить
                                </button>
                                <button
                                    class="btn__user--edit"
                                    @click="cancelEditingDirection"
                                >
                                    Отмена
                                </button>
                            </td>
                            <td v-else>
                                <button
                                    class="btn__user--edit"
                                    @click="startEditingDirection(dir)"
                                >
                                    Редактировать
                                </button>
                            </td>

                            <td>
                                <button
                                    class="btn__user--delete"
                                    @click="deleteDirection(dir.id)"
                                >
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="pagination-users" v-if="totalPagesDirs > 1">
                    <button
                        :disabled="currentPageDirs === 1"
                        @click="currentPageDirs--"
                    >
                        ‹ Назад
                    </button>
                    <button
                        v-for="p in totalPagesDirs"
                        :key="p"
                        :class="{ active: currentPageDirs === p }"
                        @click="currentPageDirs = p"
                    >
                        {{ p }}
                    </button>
                    <button
                        :disabled="currentPageDirs === totalPagesDirs"
                        @click="currentPageDirs++"
                    >
                        Вперёд ›
                    </button>
                </div>

                <p v-else>Нет направлений</p>
            </div>

            <!-- ===== СОЗДАНИЕ ===== -->
            <div>
                <h2>Создание новой категории обучения</h2>
                <form
                    @submit.prevent="createLanguageCategory"
                    class="edit-course-form--small"
                >
                    <label class="form-label--small" for="lang_name"
                        >Название языка программирования</label
                    >
                    <input
                        id="lang_name"
                        v-model="languageCategory"
                        type="text"
                        placeholder="Введите язык программирования"
                        class="form-input"
                    />
                    <button type="submit" class="form-button--small">
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
                    <label class="form-label--small" for="dir_name"
                        >Название направления</label
                    >
                    <input
                        id="dir_name"
                        class="form-input"
                        type="text"
                        v-model="directionName"
                        placeholder="Введите направление"
                    />

                    <!-- если описание не нужно — можно удалить -->
                    <label
                        class="form-label--small"
                        for="dir_desc"
                        style="margin-top: 0.5rem"
                        >Описание</label
                    >
                    <textarea
                        id="dir_desc"
                        class="form-input"
                        v-model="directionDescription"
                        placeholder="Описание (необязательно)"
                        style="min-height: 80px"
                    />

                    <button type="submit" class="form-button--small">
                        Создать направление
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";
import { globalNotification } from "../../../globalNotification";

const props = defineProps({
    languages: { type: Array, default: () => [] },
    directions: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:languages", "update:directions"]);

function setLanguages(next) {
    emit("update:languages", next);
}
function setDirections(next) {
    emit("update:directions", next);
}

/* ===== пагинация языков ===== */
const currentPageLangs = ref(1);
const pageSizeLangs = ref(5);

const totalPagesLangs = computed(() =>
    Math.max(1, Math.ceil(props.languages.length / pageSizeLangs.value))
);

const paginatedLangs = computed(() => {
    const start = (currentPageLangs.value - 1) * pageSizeLangs.value;
    return props.languages.slice(start, start + pageSizeLangs.value);
});

watch(
    () => props.languages,
    () => (currentPageLangs.value = 1)
);

/* ===== пагинация направлений ===== */
const currentPageDirs = ref(1);
const pageSizeDirs = ref(5);

const totalPagesDirs = computed(() =>
    Math.max(1, Math.ceil(props.directions.length / pageSizeDirs.value))
);

const paginatedDirs = computed(() => {
    const start = (currentPageDirs.value - 1) * pageSizeDirs.value;
    return props.directions.slice(start, start + pageSizeDirs.value);
});

watch(
    () => props.directions,
    () => (currentPageDirs.value = 1)
);

/* ===== редактирование языков/направлений ===== */
const editingLanguageId = ref(null);
const editingLanguage = ref(null);

const editingDirectionId = ref(null);
const editingDirection = ref(null);

function startEditingLanguage(lang) {
    editingLanguageId.value = lang.id;
    editingLanguage.value = { ...lang };
}
function cancelEditingLanguage() {
    editingLanguageId.value = null;
    editingLanguage.value = null;
}

async function saveLanguage() {
    try {
        const { data } = await axios.patch(
            `/api/languages/${editingLanguage.value.id}`,
            {
                name: editingLanguage.value.name,
            }
        );

        const updated = data.language ?? data;
        setLanguages(
            props.languages.map((l) => (l.id === updated.id ? updated : l))
        );

        globalNotification.categoryMessage = "Язык успешно обновлён";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при обновлении языка";
        globalNotification.type = "error";
    } finally {
        cancelEditingLanguage();
    }
}

async function deleteLanguage(id) {
    if (!confirm("Вы действительно хотите удалить этот язык?")) return;
    try {
        await axios.delete(`/api/languages/${id}`);
        setLanguages(props.languages.filter((l) => l.id !== id));
        globalNotification.categoryMessage = "Язык успешно удалён";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при удалении языка";
        globalNotification.type = "error";
    }
}

function startEditingDirection(dir) {
    editingDirectionId.value = dir.id;
    editingDirection.value = { ...dir };
}
function cancelEditingDirection() {
    editingDirectionId.value = null;
    editingDirection.value = null;
}

async function saveDirection() {
    try {
        const { data } = await axios.patch(
            `/api/directions/${editingDirection.value.id}`,
            {
                name: editingDirection.value.name,
            }
        );

        const updated = data.direction ?? data;
        setDirections(
            props.directions.map((d) => (d.id === updated.id ? updated : d))
        );

        globalNotification.categoryMessage = "Направление обновлено";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка обновления направления";
        globalNotification.type = "error";
    } finally {
        cancelEditingDirection();
    }
}

async function deleteDirection(id) {
    if (!confirm("Вы действительно хотите удалить направление?")) return;
    try {
        await axios.delete(`/api/directions/${id}`);
        setDirections(props.directions.filter((d) => d.id !== id));
        globalNotification.categoryMessage = "Направление удалено";
        globalNotification.type = "success";
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка удаления направления";
        globalNotification.type = "error";
    }
}

/* ===== создание ===== */
const languageCategory = ref("");

async function createLanguageCategory() {
    try {
        const { data } = await axios.post("/api/languages", {
            name: languageCategory.value,
        });
        globalNotification.categoryMessage =
            data.message || "Категория успешно создана!";
        globalNotification.type = "success";
        languageCategory.value = "";

        // перечитаем список, чтобы не гадать формат ответа
        const res = await axios.get("/api/languages");
        setLanguages(res.data);
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage =
            "Заполните все поля для добавления категории";
        globalNotification.type = "error";
    }
}

const directionName = ref("");
const directionDescription = ref("");

async function submitDirection() {
    try {
        const { data } = await axios.post("/api/directions", {
            name: directionName.value,
            description: directionDescription.value,
        });

        globalNotification.categoryMessage =
            data.message || "Направление успешно создано!";
        globalNotification.type = "success";

        directionName.value = "";
        directionDescription.value = "";

        const res = await axios.get("/api/directions");
        setDirections(res.data);
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage =
            "Заполните все поля для добавления направления";
        globalNotification.type = "error";
    }
}
</script>
