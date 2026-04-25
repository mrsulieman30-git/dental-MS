<script setup>
import { ref, inject, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useTreatmentPlanStore } from '../../../stores/treatmentPlan.store';
import { formatCurrency, formatDate } from '../../../utils/formatters';
import TreatmentPlanEditor from '../../../components/treatment/TreatmentPlanEditor.vue';
import {
    Plus, Eye, Send, Copy, Archive, FileText,
    Layers, ClipboardList, DollarSign, Calendar
} from 'lucide-vue-next';

const patient = inject('patient');
const router = useRouter();
const store = useTreatmentPlanStore();

const showEditor = ref(false);
const editingPlanId = ref(null);

onMounted(async () => {
    if (patient.value?.id) {
        await store.fetchPlans(patient.value.id);
    }
});

const statusConfig = {
    draft: { label: 'Draft', severity: 'secondary', color: 'bg-slate-100 text-slate-600' },
    presented: { label: 'Presented', severity: 'info', color: 'bg-blue-100 text-blue-700' },
    accepted: { label: 'Accepted', severity: 'success', color: 'bg-emerald-100 text-emerald-700' },
    in_progress: { label: 'In Progress', severity: 'warn', color: 'bg-amber-100 text-amber-700' },
    declined: { label: 'Declined', severity: 'danger', color: 'bg-red-100 text-red-700' },
    completed: { label: 'Completed', severity: 'success', color: 'bg-green-100 text-green-700' },
    expired: { label: 'Expired', severity: 'secondary', color: 'bg-gray-100 text-gray-600' },
};

const openNewPlan = () => {
    editingPlanId.value = null;
    showEditor.value = true;
};

const openPlan = (plan) => {
    editingPlanId.value = plan.id;
    showEditor.value = true;
};

const presentPlan = (plan) => {
    router.push(`/present/${plan.id}`);
};

const duplicatePlan = async (plan) => {
    await store.duplicatePlan(plan.id);
};

const archivePlan = async (plan) => {
    await store.archivePlan(plan.id);
};

const onEditorSaved = async () => {
    await store.fetchPlans(patient.value.id);
};

const patientInsurance = computed(() => {
    return patient.value?.insurances?.find(i => i.is_primary) || null;
});
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/20">
                    <ClipboardList class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Treatment Plans</h2>
                    <p class="text-xs text-slate-400 font-medium">{{ store.plans.length }} plan(s)</p>
                </div>
            </div>
            <button
                @click="openNewPlan"
                class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-sm font-bold rounded-xl hover:shadow-lg hover:shadow-primary-500/25 transition-all active:scale-95"
            >
                <Plus class="w-4 h-4" />
                New Treatment Plan
            </button>
        </div>

        <!-- Loading state -->
        <div v-if="store.loading" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <p-skeleton v-for="i in 4" :key="i" height="180px" class="rounded-2xl"></p-skeleton>
        </div>

        <!-- Empty state -->
        <div v-else-if="!store.plans.length" class="text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800">
            <FileText class="w-12 h-12 text-slate-300 mx-auto mb-4" />
            <h3 class="text-lg font-bold text-slate-500 mb-2">No Treatment Plans</h3>
            <p class="text-sm text-slate-400 mb-6">Create a treatment plan to track procedures and costs</p>
            <button @click="openNewPlan" class="px-6 py-2.5 bg-primary-500 text-white text-sm font-bold rounded-xl hover:bg-primary-600 transition-colors">
                Create First Plan
            </button>
        </div>

        <!-- Plan cards -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div
                v-for="plan in store.plans" :key="plan.id"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/50 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group"
            >
                <!-- Card header -->
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div :class="['w-2 h-8 rounded-full', statusConfig[plan.status]?.color?.split(' ')[0] || 'bg-slate-200']"></div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ plan.name }}</h3>
                            <p class="text-[10px] text-slate-400 font-medium flex items-center gap-2 mt-0.5">
                                <Calendar class="w-3 h-3" />
                                {{ formatDate(plan.created_at) }}
                                <span v-if="plan.accepted_at" class="text-emerald-500">• Accepted {{ formatDate(plan.accepted_at) }}</span>
                            </p>
                        </div>
                    </div>
                    <span :class="['px-2.5 py-1 rounded-lg text-[10px] uppercase font-black', statusConfig[plan.status]?.color || 'bg-slate-100 text-slate-500']">
                        {{ statusConfig[plan.status]?.label || plan.status }}
                    </span>
                </div>

                <!-- Card body -->
                <div class="px-5 py-4">
                    <div class="grid grid-cols-4 gap-3">
                        <div class="text-center">
                            <p class="text-[9px] uppercase text-slate-400 font-bold mb-1">Phases</p>
                            <p class="text-lg font-black text-slate-700 dark:text-white">{{ plan.phases_count || 1 }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[9px] uppercase text-slate-400 font-bold mb-1">Procedures</p>
                            <p class="text-lg font-black text-slate-700 dark:text-white">{{ plan.procedures_count || 0 }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[9px] uppercase text-slate-400 font-bold mb-1">Total Fee</p>
                            <p class="text-lg font-black text-slate-700 dark:text-white">{{ formatCurrency(plan.total_fee) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[9px] uppercase text-slate-400 font-bold mb-1">Patient</p>
                            <p class="text-lg font-black text-primary-600">{{ formatCurrency(plan.patient_estimated) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card actions -->
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="openPlan(plan)" class="flex-1 py-2 text-xs font-bold text-slate-600 hover:bg-white rounded-lg transition-colors flex items-center justify-center gap-1">
                        <Eye class="w-3.5 h-3.5" /> View
                    </button>
                    <button @click="presentPlan(plan)" class="flex-1 py-2 text-xs font-bold text-blue-600 hover:bg-blue-50 rounded-lg transition-colors flex items-center justify-center gap-1">
                        <Send class="w-3.5 h-3.5" /> Present
                    </button>
                    <button @click="duplicatePlan(plan)" class="flex-1 py-2 text-xs font-bold text-slate-600 hover:bg-white rounded-lg transition-colors flex items-center justify-center gap-1">
                        <Copy class="w-3.5 h-3.5" /> Duplicate
                    </button>
                    <button @click="archivePlan(plan)" class="flex-1 py-2 text-xs font-bold text-red-500 hover:bg-red-50 rounded-lg transition-colors flex items-center justify-center gap-1">
                        <Archive class="w-3.5 h-3.5" /> Archive
                    </button>
                </div>
            </div>
        </div>

        <!-- Editor Dialog -->
        <TreatmentPlanEditor
            v-model:visible="showEditor"
            :patientId="patient?.id"
            :planId="editingPlanId"
            :patientInsurance="patientInsurance"
            @saved="onEditorSaved"
        />
    </div>
</template>