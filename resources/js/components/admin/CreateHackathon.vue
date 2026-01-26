<script setup>
import {computed, defineAsyncComponent, onMounted, reactive, ref, watch} from 'vue'
// import ConfirmDialog from './Tab/ConfirmDialog.vue'

import OverviewTab      from './Tab/Overview.vue'
import PinMaxTab from './Tab/PinMax.vue'
// import ResourcesTab     from './Tab/Resources.vue'
// import RulesTab         from './Tab/Rules.vue'
// import ContactsTab      from './Tab/Contacts.vue'
// import EvaluationTab    from './Tab/Evaluation.vue'
// import AwardTab         from './Tab/Award.vue'
// import CertificatesTab  from './Tab/Certificates.vue'

const props = defineProps({
    modelValue : Boolean,
    hackathon  : { type:Array, default:() => [] },
    isEdit     : { type: Boolean, default: false },
    tabs       : { type:Array, default:() => [] },
    tags       : { type:Array, default:() => [] },
    admin      : { type:Boolean, default:() => false },
    update_published: { type:Boolean, default:true },
})

const isAdmin = computed(() => !!props.admin)
const updatePublished = computed(() => props.update_published !== false)

const draft = reactive({ slug: null, courseId: null, id: null })
const created    = ref(false)

const saving = ref(false)
function onSaving(flag) { saving.value = !!flag }

const tabs = [
    defineAsyncComponent(() => import('./Tab/MainInfo.vue')),
    OverviewTab,
    PinMaxTab,
]

const active = ref(0)
const pendingTab     = ref(null)
const showLeaveDlg   = ref(false)
const hasUnsaved     = ref(false)
const tabsRu = ref(['Основная информация','Темы', 'Закрепить MAX']);
const tCert = (t) => capitalizeFirstLetter(t?.certificates || 'Сертификаты')

function onDirty (flag) { hasUnsaved.value = flag }

function isTabLocked(i) {
    return false
}

const lastReadonlyStart = computed(() => Math.max(0, tabsRu.value.length - 3))

const isReadonlyActive = computed(() =>
    !updatePublished.value && active.value >= lastReadonlyStart.value
)

const isEdit = computed(() => {
    const hasHackathon = !!(props.hackathon && Object.keys(props.hackathon).length)
    const hasTabs      = !!(props.tabs && props.tabs.length)
    return hasHackathon || hasTabs
})

function getHackathonSlugFromProps() {
    const h = props.hackathon
    return h?.slug
        ?? h?.data?.slug
        ?? (Array.isArray(h) ? h[0]?.slug : undefined)
        ?? draft.slug
}

function initMode () {
    if (isEdit.value) {
        created.value = true
        const slug = getHackathonSlugFromProps()
        if (slug) draft.slug = slug
        tabs[0] = defineAsyncComponent(() => import('./Tab/MainInfo.vue'))
    } else {
        created.value = false
        draft.slug = null
        tabs[0] = defineAsyncComponent(() => import('./Tab/MainInfo.vue'))
        active.value = 0
    }
}

function toTab (i) {
    if (isTabLocked(i)) return
    if (i === active.value)    return

    if (hasUnsaved.value) {
        pendingTab.value  = i
        showLeaveDlg.value = true
    } else {
        active.value = i
    }
}
function confirmLeave () {
    hasUnsaved.value = false
    active.value     = pendingTab.value
    pendingTab.value = null
}
function cancelLeave () {
    pendingTab.value   = null
}
function resetDialog () {
    draft.id = null
    draft.slug  = null
    draft.courseId = null
    created.value = false
    active.value  = 0
    tabs[0] = defineAsyncComponent(() => import('./Tab/MainInfo.vue'))
}

const emit  = defineEmits(['update:modelValue', 'courseSaved'])

function close () {
    resetDialog()
    emit('update:modelValue', false)
    // if (isEdit.value) {
    //     window.location.reload()
    // }
}

function onTabSaved(payload) {
  const course =
    payload?.course ??
    payload?.data?.course ??
    payload?.data ??
    null;

  const courseId =
    payload?.courseId ??
    course?.id ??
    payload?.slug ??       // на случай если ты отправил id в slug
    course?.slug ??
    null;

  if (course) emit('courseSaved', course);

  if (courseId != null) {
    draft.courseId = courseId;
    draft.id = courseId;          // ✅
    if (course?.slug) draft.slug = course.slug;      // у тебя “slug” фактически используется как id
    created.value = true;
    active.value = 1;           // <-- 100% переход на вкладку "Темы"
  }
}


function capitalizeFirstLetter(str) {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}


watch(() => [props.hackathon, props.tabs], initMode, { deep:true })

watch(active, (newTab) => {
    console.log(newTab)
    localStorage.setItem('activeTab', newTab);
});

onMounted(() => {
    initMode()
}) 
</script>

<template>
    <div
        v-if="modelValue"
        class="dialog"
        style="z-index: 1"
        @click.self="close"
    >
        <div class="dialog__container_custom" @click.stop>
            <div class="dialog__inner" :class="{ 'is-saving': saving }">
                <div class="dialog__header">
                    <p>Добавить курс</p>
                    <div class="dialog__close" @click="close">
                        <svg width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M7.90994 6.00019L12.2099 1.71019C12.3982 1.52188 12.504 1.26649 12.504 1.00019C12.504 0.733884 12.3982 0.478489 12.2099 0.290185C12.0216 0.101882 11.7662 -0.00390625 11.4999 -0.00390625C11.2336 -0.00390625 10.9782 0.101882 10.7899 0.290185L6.49994 4.59019L2.20994 0.290185C2.02164 0.101882 1.76624 -0.00390625 1.49994 -0.00390625C1.23364 -0.00390625 0.978243 0.101882 0.789939 0.290185C0.601635 0.478489 0.495847 0.733884 0.495847 1.00019C0.495847 1.26649 0.601635 1.52188 0.789939 1.71019L5.08994 6.00019L0.789939 10.2902C0.696211 10.3831 0.621816 10.4937 0.571048 10.6156C0.520279 10.7375 0.494141 10.8682 0.494141 11.0002C0.494141 11.1322 0.520279 11.2629 0.571048 11.3848C0.621816 11.5066 0.696211 11.6172 0.789939 11.7102C0.882902 11.8039 0.993503 11.8783 1.11536 11.9291C1.23722 11.9798 1.36793 12.006 1.49994 12.006C1.63195 12.006 1.76266 11.9798 1.88452 11.9291C2.00638 11.8783 2.11698 11.8039 2.20994 11.7102L6.49994 7.41019L10.7899 11.7102C10.8829 11.8039 10.9935 11.8783 11.1154 11.9291C11.2372 11.9798 11.3679 12.006 11.4999 12.006C11.632 12.006 11.7627 11.9798 11.8845 11.9291C12.0064 11.8783 12.117 11.8039 12.2099 11.7102C12.3037 11.6172 12.3781 11.5066 12.4288 11.3848C12.4796 11.2629 12.5057 11.1322 12.5057 11.0002C12.5057 10.8682 12.4796 10.7375 12.4288 10.6156C12.3781 10.4937 12.3037 10.3831 12.2099 10.2902L7.90994 6.00019Z"
                                fill="#999999"
                            />
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="dialog__tabs">
                        <div v-for="(t,i) in tabsRu" :key="t"
                             :class="[
                                  'dialog__tabs_item',
                                  {
                                      active:active===i,
                                      locked: isTabLocked(i),
                                      done: created || isEdit
                                  }
                             ]"
                             @click="toTab(i)">
                            <p>{{ t }}</p>
                        </div>
                    </div>
                </div>
                <div class="dialog__content" :class="{ 'is-readonly': isAdmin }">
                    <keep-alive>
                        <component
                            :is="tabs[active]"
                            :draft="draft"
                            v-bind="(created || isEdit) ? { hackathonSlug:draft.slug } : {}"
                            :all-tags="props.tags"
                            :isEdit="created || isEdit"
                            :readonly="isReadonlyActive"
                            @saved="onTabSaved"
                            @cancel="close"
                            @dirty="onDirty"
                            @saving="onSaving"
                            :admin="props.admin"
                        />
                    </keep-alive>
                </div>
            </div>
        </div>
    </div>
    <ConfirmDialog
        v-model="showLeaveDlg"
        text="Внесённые изменения не сохранены. Всё-таки перейти на другую вкладку?"
        @confirm="confirmLeave"
        @cancel="cancelLeave"
    />
</template>

<style scoped>
.dialog {
    position: fixed;
    inset: 0;
}

/* контейнер модалки теперь референсная область для абсолютного оверлея */
.dialog__container {
    position: relative;
}

/* Блюрим и блокируем ТОЛЬКО контент модалки */
.dialog__inner.is-saving {
    filter: blur(3px);
    user-select: none;
    pointer-events: none;
}

/* Оверлей в рамках модалки */
.dialog__saving-overlay {
    position: absolute;   /* <— не fixed */
    inset: 0;
    z-index: 10;          /* достаточно больше, чем содержимое */
    display: grid;
    place-items: center;
    height: 100%;
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(2px);
    border-radius: inherit;  /* повторяет скругление модалки */
    pointer-events: all;     /* гасим клики */
}

.dialog__saving-spinner {
    width: 150px;
    height: 150px;
    object-fit: contain;
    pointer-events: none;
}

</style>
