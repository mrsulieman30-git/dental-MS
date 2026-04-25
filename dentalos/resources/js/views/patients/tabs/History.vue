<script setup>
import { inject, ref, onMounted } from 'vue';
import { 
    Calendar, FileText, Clipboard, DollarSign, 
    Shield, FlaskConical, Share2, MessageSquare, 
    FileCheck, UserCheck, ChevronDown, Filter
} from 'lucide-vue-next';
import { formatDateTime } from '../../../utils/formatters';

const patient = inject('patient');
const timelineEvents = ref([]);
const loading = ref(false);

const eventTypes = {
    appointment: { icon: Calendar, color: 'border-blue-500', bg: 'bg-blue-50 text-blue-500' },
    clinical_note: { icon: FileText, color: 'border-purple-500', bg: 'bg-purple-50 text-purple-500' },
    treatment_plan: { icon: Clipboard, color: 'border-green-500', bg: 'bg-green-50 text-green-500' },
    payment: { icon: DollarSign, color: 'border-emerald-500', bg: 'bg-emerald-50 text-emerald-500' },
    insurance: { icon: Shield, color: 'border-amber-500', bg: 'bg-amber-50 text-amber-500' },
    lab_case: { icon: FlaskConical, color: 'border-indigo-500', bg: 'bg-indigo-50 text-indigo-500' },
    referral: { icon: Share2, color: 'border-pink-500', bg: 'bg-pink-50 text-pink-500' },
    communication: { icon: MessageSquare, color: 'border-cyan-500', bg: 'bg-cyan-50 text-cyan-500' },
    form_submission: { icon: FileCheck, color: 'border-teal-500', bg: 'bg-teal-50 text-teal-500' },
    portal_login: { icon: UserCheck, color: 'border-slate-500', bg: 'bg-slate-50 text-slate-500' },
};

const fetchTimeline = async () => {
    loading.value = true;
    try {
        // const response = await api.get(`/patients/${patient.value.id}/timeline`);
        // timelineEvents.value = response.data;
        
        // Mock data for demonstration
        timelineEvents.value = [
            { id: 1, type: 'appointment', title: 'Periodic Exam Completed', summary: 'Patient seen for routine 6-month checkup.', provider: 'Dr. Sarah Wilson', timestamp: new Date() },
            { id: 2, type: 'clinical_note', title: 'Clinical Note Locked', summary: 'SOAP note finalized for today\'s visit.', provider: 'Dr. Sarah Wilson', timestamp: new Date(Date.now() - 3600000) },
            { id: 3, type: 'payment', title: 'Payment Posted', summary: '$150.00 Credit Card payment processed.', provider: 'Receptionist Jane', timestamp: new Date(Date.now() - 86400000) },
            { id: 4, type: 'communication', title: 'SMS Reminder Sent', summary: 'Confirmed appointment for tomorrow.', provider: 'Automated System', timestamp: new Date(Date.now() - 172800000) },
        ];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchTimeline);
</script>

<template>
    <div class="space-y-8 animate-in fade-in duration-500 max-w-4xl mx-auto">
        
        <!-- Timeline Controls -->
        <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-2">
                <button class="filter-chip active">All Events</button>
                <button class="filter-chip">Clinical Only</button>
                <button class="filter-chip">Financial Only</button>
                <button class="filter-chip">Communications</button>
            </div>
            <p-button icon="pi pi-filter" class="p-button-text p-button-rounded p-button-secondary" label="Date Range" />
        </div>

        <!-- Vertical Timeline -->
        <div class="relative pl-8 sm:pl-32 py-4">
            <!-- Continuous Line -->
            <div class="absolute left-10 sm:left-[111px] top-0 bottom-0 w-0.5 bg-slate-100 dark:bg-slate-800"></div>

            <div v-for="event in timelineEvents" :key="event.id" class="relative mb-12 group">
                
                <!-- Date Label (Desktop) -->
                <div class="hidden sm:block absolute -left-32 w-24 text-right pt-2">
                    <p class="text-[10px] font-black uppercase text-slate-400 leading-none mb-1">{{ formatDateTime(event.timestamp, 'MMM dd') }}</p>
                    <p class="text-[9px] font-bold text-slate-300 uppercase tracking-tighter">{{ formatDateTime(event.timestamp, 'yyyy') }}</p>
                </div>

                <!-- Timeline Node (Icon) -->
                <div :class="['absolute left-[2px] sm:left-[101px] w-5 h-5 rounded-full border-4 border-white dark:border-slate-950 z-10', eventTypes[event.type]?.color]"></div>
                
                <!-- Event Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-5 ml-4 transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 cursor-pointer">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div :class="['p-2 rounded-xl shrink-0', eventTypes[event.type]?.bg]">
                                <component :is="eventTypes[event.type]?.icon" class="w-4 h-4" />
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white">{{ event.title }}</h4>
                                <p class="text-sm text-slate-500 mt-1">{{ event.summary }}</p>
                                <div class="mt-4 flex items-center gap-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    <span class="flex items-center gap-1">
                                        <Clock class="w-3 h-3" />
                                        {{ formatDateTime(event.timestamp, 'h:mm a') }}
                                    </span>
                                    <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                    <span>By {{ event.provider }}</span>
                                </div>
                            </div>
                        </div>
                        <ChevronDown class="w-4 h-4 text-slate-300 group-hover:text-primary-500 transition-colors" />
                    </div>
                </div>
            </div>

            <!-- Load More -->
            <div class="text-center pt-4">
                <p-button label="Load More Activity" class="p-button-outlined p-button-rounded p-button-secondary text-xs" :loading="loading" />
            </div>
        </div>
    </div>
</template>

<style scoped>
@reference "../../../../css/app.css";
.filter-chip {
    @apply px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-100 dark:border-slate-800 text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-all;
}

.filter-chip.active {
    @apply bg-primary-50 text-primary-600 border-primary-100;
}
</style>
