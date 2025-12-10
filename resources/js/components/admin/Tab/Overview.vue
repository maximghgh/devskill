<script setup>
import { computed, nextTick, onMounted, ref, watch, reactive } from "vue";

function createForm(initial) {
  const state = reactive({
    ...initial,
    errors: {}
  })

  state.clearErrors = (field) => {
    if (!field) {
      state.errors = {}
      return
    }
    delete state.errors[field]
  }

  state.setError = (field, message) => {
    state.errors[field] = message
  }

  return state
}

const props = defineProps({
    hackathonSlug : { type:String, required:true },
    draft         : Object,
    allTags  : { type:Array, default:() => [] },
    isEdit   : { type:Boolean, default:false },
    admin      : { type:Boolean, default:() => false },
})
const emit = defineEmits(['saved', 'cancel', 'dirty', 'saving'])

const isAdmin = computed(() => !!props.admin)


const form = createForm({
  sections: [
    { title: 'Описание', content: '', items: [] },
    { title: 'План проведения', content: '', items: [] },
    { title: 'Время на выполнение', content: '', items: [] },
  ],
  partners: [],
  files: [],
  delete_media_ids: []
})

const partnersErrors = computed(() => {
    const out = [];
    for (const [k, v] of Object.entries(form.errors ?? {})) {
        if (k.startsWith('partners') || k.startsWith('files') || k.startsWith('delete_media_ids')) {
            out.push(typeof v === 'string' ? v : String(v));
        }
    }
    return out;
});

function applyBackendErrors(errs) {
    form.clearErrors();
    if (!errs || typeof errs !== 'object') return;
    Object.entries(errs).forEach(([field, messages]) => {
        const msg = Array.isArray(messages) ? messages.join(' ') : String(messages);
        form.setError(field, msg);
    });
}

const loaded = ref(false)
const dirty = ref(false)

const dlgShown     = ref(false)
const editingIndex = ref(null)

const description     = ref(null)
const plan            = ref(null)
const partnerLogos    = ref([])
const newPartnerFiles = ref([])   // File[]
const deletedMediaIds = ref([])   // number[]

const nominations = ref(null)
const overviewTabId = ref(null)

const partnerKey = ref(0)

async function fetchHackathon ({
   refreshEditors = true,
   refreshPartners = true,
   refreshNominations = true,
} = {}) {
    try {
        const { data } = await axios.get(
            route('hackathons.show', { hackathon: props.hackathonSlug }),
            { headers: { Accept: 'application/json' } }
        )

        if (props.isEdit) {
            const overviewTab = data.tabs.original[0]
            const h = data.hackathon.original
            overviewTabId.value = overviewTab?.id ?? null

            if (refreshEditors) {
                const desc = overviewTab.sections.find(s => s.title === 'Описание')?.content || ''
                const pln  = overviewTab.sections.find(s => s.title === 'План проведения')?.content || ''

                form.sections[0].content = desc
                form.sections[1].content = pln
                description.value = desc
                plan.value = pln
            }

            if (refreshPartners) {
                await getPartner(overviewTabId.value)
            }
        }

        if (refreshNominations) {
            const raw = data.hackathon.original.nominations || []
            nominations.value = raw.map(n => ({
                ...n,
                    distribution: Array.isArray(n.distribution) ? n.distribution : (n.distribution && typeof n.distribution === 'object' ? Object.values(n.distribution).sort((a,b)=>(a.place??0)-(b.place??0))
                            : [])
                }))
        }

        await nextTick()
        loaded.value = true
    } catch (err) {
        console.error('fetch-hackathon-error', err?.response ?? err)
    }
}
async function getPartner(tabId) {
    try {
        const response = await axios.get(
            route('hackathons.tabs.partner-images', { hackathon: props.hackathonSlug, tab: tabId }),
            { headers: { Accept: 'application/json' } }
        );
        partnerLogos.value = response.data.partners || []
        newPartnerFiles.value = []
        deletedMediaIds.value = []
        partnerKey.value++
    } catch (e) {
        console.error('hackathon-load', e?.response ?? e);
    }
}
onMounted(() => {
    if (props.isEdit) {
        fetchHackathon({ refreshDates: true });
    }
})

function openAdd() { editingIndex.value = null; dlgShown.value = true }
function openEdit(idx)          { editingIndex.value = idx;  dlgShown.value = true }
async function onSaved() {
    dlgShown.value = false
    await fetchHackathon({ refreshEditors: false, refreshPartners: false, refreshNominations: true })
}

async function removeNomination(idx){
    const id = nominations.value[idx].id
    try {
        await router.delete(
            route('hackathons.nominations.destroy', { hackathon: props.hackathonSlug, nomination: id }),
            { preserveScroll:true }
        )
        nominations.value.splice(idx,1)
    } catch (err){
        console.error('nomination-delete-error', err?.response ?? err)
    }
}

watch(description,  v => { form.sections[0].content = v ?? '' })
watch(plan,         v => { form.sections[1].content = v ?? '' })

const handleFilesUpdate = (newFiles) => {
    newPartnerFiles.value = Array.isArray(newFiles) ? newFiles : []
}

const handleDeletingIds = (ids) => {
    deletedMediaIds.value = Array.isArray(ids)
        ? ids.filter(n => Number.isFinite(+n)).map(n => +n)
        : []
}

function clearFieldError(field) {
    form.clearErrors(field)
}

watch(
    [description, plan, partnerLogos],
    () => {
        if (!loaded.value) return
        if (!dirty.value) {
            dirty.value = true
            emit('dirty', true)
        }
    },
    { deep:true }
)

function pad2(n) { return String(n).padStart(2, '0') }

/** "2025-10-01T10:00" (локаль) → "2025-10-01T08:00:00.000Z" (UTC) */
function localDateTimeToUtcISO(localStr) {
    if (!localStr) return ''
    const [datePart, timePart] = localStr.split('T')
    if (!datePart || !timePart) return ''
    const [y, m, d] = datePart.split('-').map(Number)
    const [hh, mm]  = timePart.split(':').map(Number)
    const local = new Date(y, (m ?? 1) - 1, d ?? 1, hh ?? 0, mm ?? 0, 0)
    return isNaN(local.getTime()) ? '' : local.toISOString()
}

/** Приводим всё, что похоже на UTC без суффикса, к ISO с Z */
function normalizeToIsoZ(s) {
    const t = s.trim()
    // "YYYY-MM-DD HH:mm[:ss[.ms]]" или "YYYY-MM-DDTHH:mm[:ss[.ms]]"
    const re = /^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2}(\.\d+)?)?$/
    if (re.test(t) && !/[zZ]|[+\-]\d{2}:\d{2}$/.test(t)) {
        return t.replace(' ', 'T') + 'Z'
    }
    return t
}

/** "2025-10-01T08:00:00Z" (UTC) → "2025-10-01T10:00" (локаль для <input type="datetime-local">) */
function utcToLocalInputValue(utcStr) {
    if (!utcStr) return ''
    const d = new Date(normalizeToIsoZ(utcStr))
    if (isNaN(d.getTime())) return ''
    const y = d.getFullYear()
    const m = pad2(d.getMonth() + 1)
    const day = pad2(d.getDate())
    const hh = pad2(d.getHours())
    const mm = pad2(d.getMinutes())
    return `${y}-${m}-${day}T${hh}:${mm}`
}

async function save () {
    form.sections[0].content = description.value ?? ''
    form.sections[1].content = plan.value        ?? ''

    emit('saving', true)

    const fd = new FormData();
    fd.append('title', 'Обзор');
    form.sections.forEach((s, si) => {
        if (s.title === 'Время на выполнение') return;
        fd.append(`sections[${si}][title]`, s.title);
        const content = s.content == null ? '' : (typeof s.content === 'object' ? JSON.stringify(s.content) : String(s.content));
        fd.append(`sections[${si}][content]`, content);
    });
    newPartnerFiles.value.forEach(f => {
        if (f instanceof File) fd.append('partners[]', f)
    })
    deletedMediaIds.value.forEach(id => {
        fd.append('delete_media_ids[]', String(id))
    })

    fd.append('_method', 'PATCH');

    console.log('FD →', [...fd.entries()].map(([k, v]) => [k, v instanceof File ? v.name : v]));

    try {
        await axios.post(
            route('hackathons.tabs.update', { hackathon: props.hackathonSlug }),
            fd,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        )
        newPartnerFiles.value = []
        deletedMediaIds.value = []
        if (overviewTabId.value != null) {
            await getPartner(overviewTabId.value)
        } else {
            await fetchHackathon({ refreshEditors:false, refreshPartners:true, refreshNominations:false })
        }
    } catch (e) {
        if (e?.response?.status === 422) {
            applyBackendErrors(e.response.data?.errors || {});
            return;
        }
        console.error('tab-errors', e?.response ?? e);
        return;
    }finally {
        emit('saving', false)
    }
    dirty.value = false
    loaded.value = true
    emit('dirty', false)
    emit('saved', { slug : props?.hackathonSlug })
}

watch(description, () => form.clearErrors('sections.0.content'));
watch(plan,        () => form.clearErrors('sections.1.content'));
watch(newPartnerFiles, () => {
    Object.keys(form.errors).forEach(k => {
        if (k.startsWith('partners') || k.startsWith('files')) form.clearErrors(k);
    });
});

function humanizeErrors(errors, files) {
    if (!errors || typeof errors !== 'object') return 'Ошибка валидации'
    const out = []
    for (const [k, msgs] of Object.entries(errors)) {
        const msg = Array.isArray(msgs) ? msgs[0] : String(msgs)
        const m = k.match(/^partners\.(\d+)/)
        if (m) {
            const i = +m[1]
            const name = files?.[i]?.name || `файл №${i+1}`
            out.push(`«${name}»: ${msg}`)
        } else if (k.startsWith('delete_media_ids')) {
            out.push(`Удаление файла: ${msg}`)
        } else {
            out.push(msg)
        }
    }
    return out.join('\n')
}

defineExpose({ save })

const resetState = () => {
    description.value  = null
    plan.value         = null
    partnerLogos.value = []
    nominations.value  = []
    dlgShown.value     = false
    editingIndex.value = null

    form.sections[0].content = ''
    form.sections[1].content = ''
    form.files               = []
    form.partners            = []
}

function cancel () {
    resetState()
    emit('cancel')
}

function capitalizeFirstLetter(str) {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

</script>

<template>
    <div>
        ТЕМЫ
    </div>
</template>

<style scoped>

</style>
