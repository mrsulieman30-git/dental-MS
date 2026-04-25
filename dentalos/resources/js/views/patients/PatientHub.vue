<script setup>
import { ref, onMounted, computed, provide, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePatients } from '../../composables/usePatients';
import { 
    formatDate, formatPatientNumber, formatAge 
} from '../../utils/formatters';
import { 
    Star, CalendarPlus, MessageSquare, 
    FilePlus, Printer, AlertTriangle, 
    Info, AlertCircle, ChevronLeft
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const { loading, fetchPatient } = usePatients();

const patient = ref(null);
const activeTabIndex = ref(0);

const tabs = [
    { label: 'Overview', path: '' },
    { label: 'Chart', path: 'chart' },
    { label: 'Perio', path: 'perio' },
    { label: 'Treatment Plans', path: 'treatment-plans' },
    { label: 'Imaging', path: 'imaging' },
    { label: 'Notes', path: 'notes' },
    { label: 'Insurance', path: 'insurance' },
    { label: 'Billing', path: 'billing' },
    { label: 'Recalls', path: 'recalls' },
    { label: 'Prescriptions', path: 'prescriptions' },
    { label: 'Lab Cases', path: 'lab-cases' },
    { label: 'Referrals', path: 'referrals' },
    { label: 'Forms', path: 'forms' },
    { label: 'Medical History', path: 'medical-history' },
    { label: 'History', path: 'history' },
];

const loadPatient = async () => {
    try {
        const response = await fetchPatient(route.params.id);
        patient.value = response;
    } catch (err) {
        console.error('Failed to load patient');
    }
};

// Sync tab index with URL
const updateTabIndexFromUrl = () => {
    const currentPath = route.path.split('/').pop();
    const index = tabs.findIndex(t => t.path === currentPath);
    activeTabIndex.value = index === -1 ? 0 : index;
};

const onTabChange = (event) => {
    const tab = tabs[event.index];
    const basePath = `/patients/${route.params.id}`;
    const newPath = tab.path ? `${basePath}/${tab.path}` : basePath;
    router.push(newPath);
};

provide('patient', patient);

onMounted(async () => {
    await loadPatient();
    updateTabIndexFromUrl();
});

watch(() => route.path, updateTabIndexFromUrl);

const alerts = computed(() => patient.value?.alerts || [
    { id: 1, type: 'critical', message: 'Latex Allergy' },
    { id: 2, type: 'warning', message: 'Medical Consult Required (Heart Condition)' },
    { id: 3, type: 'info', message: 'New Patient - Needs Full Exam' }
]);
</script>

<template>
    <div class="h-full flex flex-col gap-6">
        <!-- Patient Sticky Header -->
        <div class="sticky top-0 bg-white/80 dark:bg-slate-950/80 backdrop-blur-md z-30 -mx-8 px-8 py-4 border-b border-slate-200 dark:border-slate-800 shadow-sm transition-all duration-300">
            <div class="max-w-[1600px] mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <button @click="router.push('/patients')" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full text-slate-500 transition-colors">
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                    
                    <div class="flex items-center gap-4">
                        <p-avatar 
                            :image="patient?.avatar_url" 
                            :label="!patient?.avatar_url ? (patient?.first_name?.[0] + patient?.last_name?.[0]) : null" 
                            size="xlarge" 
                            shape="circle" 
                            class="border-4 border-white shadow-xl bg-primary-100 text-primary-600 font-bold"
                        />
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                                    {{ patient?.full_name }}
                                    <span v-if="patient?.preferred_name" class="text-slate-400 font-medium text-lg ml-1">({{ patient.preferred_name }})</span>
                                </h1>
                                <Star v-if="patient?.is_vip" class="w-5 h-5 fill-amber-400 text-amber-400" />
                                <p-tag v-if="patient?.is_new" value="New Patient" severity="info" class="text-[10px] uppercase font-bold" />
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-sm font-medium text-slate-500">
                                <span class="flex items-center gap-1">
                                    <span class="text-slate-300">DOB:</span> {{ formatDate(patient?.dob) }} ({{ formatAge(patient?.dob) }} yrs)
                                </span>
                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                <span class="flex items-center gap-1">
                                    <span class="text-slate-300">ID:</span> {{ formatPatientNumber(patient?.patient_number) }}
                                </span>
                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                <p-tag :value="patient?.status" severity="success" class="text-[10px] uppercase font-bold px-2 py-0.5" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button class="action-btn bg-primary-50 text-primary-600 hover:bg-primary-100">
                        <CalendarPlus class="w-4 h-4" />
                        <span>Schedule</span>
                    </button>
                    <button class="action-btn bg-slate-100 text-slate-600 hover:bg-slate-200">
                        <MessageSquare class="w-4 h-4" />
                        <span>Message</span>
                    </button>
                    <button class="action-btn bg-slate-100 text-slate-600 hover:bg-slate-200">
                        <FilePlus class="w-4 h-4" />
                        <span>New Note</span>
                    </button>
                    <p-button icon="pi pi-ellipsis-h" class="p-button-text p-button-rounded p-button-secondary" />
                </div>
            </div>
        </div>

        <!-- Alert Banners -->
        <div v-if="alerts.length" class="space-y-2">
            <div 
                v-for="alert in alerts" 
                :key="alert.id"
                :class="[
                    'px-6 py-3 rounded-2xl flex items-center justify-between animate-in fade-in slide-in-from-top-2 duration-300 shadow-sm border',
                    alert.type === 'critical' ? 'bg-red-50 text-red-700 border-red-100' : 
                    alert.type === 'warning' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-blue-50 text-blue-700 border-blue-100'
                ]"
            >
                <div class="flex items-center gap-3">
                    <AlertCircle v-if="alert.type === 'critical'" class="w-5 h-5 text-red-500" />
                    <AlertTriangle v-else-if="alert.type === 'warning'" class="w-5 h-5 text-amber-500" />
                    <Info v-else class="w-5 h-5 text-blue-500" />
                    <span class="font-bold tracking-tight">{{ alert.message }}</span>
                </div>
                <button class="p-1 hover:bg-black/5 rounded-full transition-colors">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Hub Tabs -->
        <div class="flex-1 min-h-0 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-2xl overflow-hidden flex flex-col">
            <p-tab-view 
                v-model:activeIndex="activeTabIndex" 
                @tab-change="onTabChange"
                class="flex-1 flex flex-col"
            >
                <p-tab-panel v-for="tab in tabs" :key="tab.label" :header="tab.label">
                    <div class="p-8">
                        <router-view v-if="patient"></router-view>
                        <div v-else class="space-y-6">
                            <p-skeleton width="20rem" height="2rem"></p-skeleton>
                            <p-skeleton height="10rem"></p-skeleton>
                            <div class="grid grid-cols-2 gap-6">
                                <p-skeleton height="20rem"></p-skeleton>
                                <p-skeleton height="20rem"></p-skeleton>
                            </div>
                        </div>
                    </div>
                </p-tab-panel>
            </p-tab-view>
        </div>
    </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.action-btn {
    @apply flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all active:scale-95 whitespace-nowrap;
}

:deep(.p-tabview-nav-container) {
    @apply bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800;
}

:deep(.p-tabview-nav) {
    @apply px-4;
}

:deep(.p-tabview-nav-link) {
    @apply py-4 text-xs uppercase tracking-widest font-black;
}

:deep(.p-tabview-panels) {
    @apply flex-1 overflow-y-auto p-0;
}

:deep(.p-tabview-panel) {
    @apply bg-transparent;
}
</style>
