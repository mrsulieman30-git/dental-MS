<script setup>
import { inject, ref, computed } from 'vue';
import { 
    HeartPulse, Pill, AlertOctagon, 
    FileCheck, Printer, Send, 
    Edit3, CheckCircle, Clock, Save, X 
} from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import { formatDate } from '../../../utils/formatters';

const patient = inject('patient');
const toast = useToast();

const isEditing = ref(false);
const showVersionHistory = ref(false);

const medicalData = computed(() => ({
    conditions: patient.value?.medical_conditions || [],
    medications: patient.value?.medications || [],
    allergies: patient.value?.allergies || [],
    questionnaire: patient.value?.questionnaire || [],
    last_reviewed_at: patient.value?.medical_history_last_reviewed_at,
    last_reviewed_by: patient.value?.medical_history_last_reviewed_by_name,
}));

const markAsReviewed = async () => {
    try {
        // await api.post(`/patients/${patient.value.id}/medical-history/review`);
        toast.success('Medical history marked as reviewed');
    } catch (err) {
        toast.error('Failed to review history');
    }
};

const getAllergySeverityColor = (severity) => {
    switch (severity?.toLowerCase()) {
        case 'severe': return 'bg-red-50 text-red-700 border-red-100';
        case 'moderate': return 'bg-orange-50 text-orange-700 border-orange-100';
        default: return 'bg-green-50 text-green-700 border-green-100';
    }
};
</script>

<template>
    <div class="space-y-8 animate-in fade-in duration-500">
        
        <!-- Header Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div v-if="medicalData.last_reviewed_at" class="flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
                    <CheckCircle class="w-3.5 h-3.5 text-green-500" />
                    Last Reviewed: {{ formatDate(medicalData.last_reviewed_at) }} by {{ medicalData.last_reviewed_by }}
                </div>
                <div v-else class="text-xs font-bold text-red-500 bg-red-50 px-3 py-1.5 rounded-full flex items-center gap-2">
                    <AlertOctagon class="w-3.5 h-3.5" />
                    Needs Review
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <button 
                    @click="markAsReviewed"
                    class="action-btn bg-green-50 text-green-700 hover:bg-green-100"
                >
                    <CheckCircle class="w-4 h-4" />
                    <span>Review Now</span>
                </button>
                <button 
                    @click="isEditing = !isEditing"
                    class="action-btn bg-primary-50 text-primary-700 hover:bg-primary-100"
                >
                    <component :is="isEditing ? Save : Edit3" class="w-4 h-4" />
                    <span>{{ isEditing ? 'Save Changes' : 'Edit History' }}</span>
                </button>
                <p-button icon="pi pi-history" class="p-button-text p-button-rounded p-button-secondary" v-tooltip="'Version History'" />
                <p-button icon="pi pi-print" class="p-button-text p-button-rounded p-button-secondary" v-tooltip="'Print PDF'" />
                <p-button icon="pi pi-send" class="p-button-text p-button-rounded p-button-secondary" v-tooltip="'Request Patient Update'" />
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            
            <!-- Column 1: Conditions & Medications -->
            <div class="xl:col-span-2 space-y-8">
                
                <!-- Medical Conditions -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <HeartPulse class="w-5 h-5 text-red-500" />
                            <h3 class="font-bold text-slate-900 dark:text-white">Medical Conditions</h3>
                        </div>
                        <button v-if="isEditing" class="text-xs font-bold text-primary-600">+ Add New</button>
                    </div>
                    <div class="p-6">
                        <div v-if="medicalData.conditions.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="cond in medicalData.conditions" :key="cond.id" class="p-4 bg-slate-50 dark:bg-slate-800/30 rounded-2xl border border-transparent hover:border-slate-200 transition-all group">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-slate-900 dark:text-white">{{ cond.condition_name }}</h4>
                                    <p-tag :value="cond.status" :severity="cond.status === 'active' ? 'danger' : 'secondary'" class="text-[9px] uppercase" />
                                </div>
                                <p class="text-xs text-slate-500 mb-2">{{ cond.icd10_code || 'No ICD-10' }}</p>
                                <p class="text-xs text-slate-400 italic">Notes: {{ cond.notes || 'No notes provided' }}</p>
                                <button v-if="isEditing" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <X class="w-3 h-3" />
                                </button>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center border-2 border-dashed border-slate-100 rounded-3xl">
                            <p class="text-sm text-slate-400">No medical conditions reported</p>
                        </div>
                    </div>
                </div>

                <!-- Medications -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Pill class="w-5 h-5 text-blue-500" />
                            <h3 class="font-bold text-slate-900 dark:text-white">Current Medications</h3>
                        </div>
                        <button v-if="isEditing" class="text-xs font-bold text-primary-600">+ Add New</button>
                    </div>
                    <div class="p-0">
                        <p-data-table :value="medicalData.medications" class="p-datatable-sm">
                            <p-column field="drug_name" header="Medication" class="text-sm font-bold"></p-column>
                            <p-column field="dosage" header="Dosage" class="text-sm"></p-column>
                            <p-column field="frequency" header="Frequency" class="text-sm"></p-column>
                            <p-column field="prescriber" header="Prescriber" class="text-sm text-slate-500"></p-column>
                            <p-column v-if="isEditing" headerStyle="width: 3rem">
                                <template #body>
                                    <button class="text-red-400 hover:text-red-600"><X class="w-4 h-4" /></button>
                                </template>
                            </p-column>
                        </p-data-table>
                    </div>
                </div>

            </div>

            <!-- Column 2: Allergies & Questionnaire -->
            <div class="space-y-8">
                
                <!-- Allergies -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <AlertOctagon class="w-5 h-5 text-orange-500" />
                            <h3 class="font-bold text-slate-900 dark:text-white">Allergies</h3>
                        </div>
                        <button v-if="isEditing" class="text-xs font-bold text-primary-600">+ Add New</button>
                    </div>
                    <div class="p-6 space-y-3">
                        <div 
                            v-for="allergy in medicalData.allergies" 
                            :key="allergy.id"
                            :class="['px-4 py-3 rounded-2xl border flex items-center justify-between', getAllergySeverityColor(allergy.severity)]"
                        >
                            <div class="flex items-center gap-3">
                                <AlertOctagon class="w-4 h-4" />
                                <span class="font-bold tracking-tight">{{ allergy.allergen }}</span>
                            </div>
                            <span class="text-[10px] uppercase font-black opacity-60">{{ allergy.severity }}</span>
                        </div>
                        <div v-if="!medicalData.allergies.length" class="text-center py-4 text-sm text-slate-400">No allergies reported</div>
                    </div>
                </div>

                <!-- Medical Questionnaire -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <FileCheck class="w-5 h-5 text-green-500" />
                            <h3 class="font-bold text-slate-900 dark:text-white">Health Questionnaire</h3>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="q in medicalData.questionnaire" :key="q.id" class="flex items-start justify-between gap-4">
                            <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">{{ q.question_text }}</p>
                            <span :class="[
                                'px-2 py-0.5 rounded text-[10px] font-black uppercase shrink-0',
                                q.answer === 'yes' ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-500'
                            ]">
                                {{ q.answer }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.action-btn {
    @apply flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all active:scale-95 whitespace-nowrap shadow-sm border border-transparent;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    @apply bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase tracking-wider font-black;
}
</style>
