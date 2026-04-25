<script setup>
import { ref, onMounted } from 'vue';
import { usePatients } from '../../composables/usePatients';
import { 
    Clock, Calendar, User, 
    MoreHorizontal, Check, Bell, 
    CalendarPlus, Trash2, Search 
} from 'lucide-vue-next';
import { formatPatientNumber, formatDate } from '../../utils/formatters';

const waitlist = ref([
    { id: 1, patient_name: 'Mark Taylor', type: 'Emergency', provider: 'Dr. Sarah Wilson', days_waiting: 12, flexibility: 'High', status: 'pending' },
    { id: 2, patient_name: 'Emily Blunt', type: 'Prophy', provider: 'Any', days_waiting: 5, flexibility: 'Medium', status: 'pending' },
]);

const getFlexibilitySeverity = (level) => {
    switch (level) {
        case 'High': return 'success';
        case 'Medium': return 'warning';
        default: return 'info';
    }
};
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Appointment Waitlist</h2>
                <p class="text-sm text-slate-500">Patients waiting for sooner availability</p>
            </div>
            <button class="px-6 py-2.5 bg-[#1A3C5E] text-white rounded-xl font-bold shadow-lg shadow-primary-500/20 hover:bg-[#15304b] transition-all flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Add to Waitlist
            </button>
        </div>

        <!-- Waitlist Table -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-xl overflow-hidden">
            <p-data-table :value="waitlist" class="p-datatable-sm">
                <p-column field="patient_name" header="Patient" class="font-bold"></p-column>
                <p-column field="type" header="Type"></p-column>
                <p-column field="provider" header="Provider Pref."></p-column>
                <p-column field="days_waiting" header="Days Waiting" sortable>
                    <template #body="{ data }">
                        <span class="text-xs font-bold px-2 py-0.5 bg-slate-100 rounded-full">{{ data.days_waiting }} days</span>
                    </template>
                </p-column>
                <p-column field="flexibility" header="Flexibility">
                    <template #body="{ data }">
                        <p-tag :value="data.flexibility" :severity="getFlexibilitySeverity(data.flexibility)" class="text-[9px] font-black uppercase" />
                    </template>
                </p-column>
                <p-column headerStyle="width: 8rem" bodyStyle="text-align: center">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2 justify-end">
                            <button class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-colors" title="Notify of Slot">
                                <Bell class="w-4 h-4" />
                            </button>
                            <button class="p-2 hover:bg-green-50 text-green-600 rounded-lg transition-colors" title="Schedule Now">
                                <CalendarPlus class="w-4 h-4" />
                            </button>
                            <p-button icon="pi pi-trash" class="p-button-text p-button-rounded p-button-danger p-button-sm" />
                        </div>
                    </template>
                </p-column>
            </p-data-table>
        </div>
    </div>
</template>
