<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAppointments } from '../../composables/useAppointments';
import { 
    Calendar, User, Clock, MapPin, 
    CheckCircle2, AlertCircle, Phone, 
    Mail, ShieldCheck, FlaskConical, 
    FileText, RefreshCw, XCircle, ChevronLeft,
    ArrowRight, MessageSquare
} from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import { formatDateTime, formatCurrency, formatDate } from '../../utils/formatters';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { fetchAppointment, updateStatus } = useAppointments();

const appt = ref(null);
const loading = ref(true);

const loadData = async () => {
    try {
        appt.value = await fetchAppointment(route.params.id);
    } finally {
        loading.value = false;
    }
};

const setStatus = async (status) => {
    try {
        await updateStatus(appt.value.id, status);
        toast.success(`Appointment status: ${status}`);
        loadData();
    } catch (err) {
        toast.error('Failed to update status');
    }
};

onMounted(loadData);

const statusSteps = [
    { label: 'Scheduled', status: 'scheduled', icon: Calendar },
    { label: 'Confirmed', status: 'confirmed', icon: CheckCircle2 },
    { label: 'Checked In', status: 'checked_in', icon: User },
    { label: 'In Chair', status: 'in_chair', icon: Clock },
    { label: 'Completed', status: 'completed', icon: CheckCircle2 },
];
</script>

<template>
    <div v-if="!loading" class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="router.back()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full text-slate-500">
                    <ChevronLeft class="w-5 h-5" />
                </button>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Appointment Details</h1>
                    <p class="text-sm text-slate-500">{{ formatDateTime(appt?.start_time) }} • {{ appt?.operatory?.name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all flex items-center gap-2">
                    <RefreshCw class="w-4 h-4" />
                    Reschedule
                </button>
                <button class="px-4 py-2 bg-red-50 text-red-600 rounded-xl text-sm font-bold hover:bg-red-100 transition-all flex items-center gap-2">
                    <XCircle class="w-4 h-4" />
                    Cancel
                </button>
            </div>
        </div>

        <!-- Status Workflow Bar -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <div 
                    v-for="(step, index) in statusSteps" 
                    :key="step.status"
                    class="flex items-center gap-3"
                >
                    <div 
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center transition-all',
                            appt.status === step.status ? 'bg-primary-500 text-white shadow-lg' : 
                            'bg-slate-50 dark:bg-slate-800 text-slate-400'
                        ]"
                    >
                        <component :is="step.icon" class="w-5 h-5" />
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-xs font-bold" :class="appt.status === step.status ? 'text-primary-600' : 'text-slate-400'">{{ step.label }}</p>
                    </div>
                    <ArrowRight v-if="index < statusSteps.length - 1" class="w-4 h-4 text-slate-200 mx-4" />
                </div>
            </div>
            
            <!-- Contextual Action -->
            <div class="mt-8 flex justify-center">
                <button 
                    v-if="appt.status === 'scheduled'"
                    @click="setStatus('confirmed')"
                    class="px-10 py-4 bg-[#1A3C5E] text-white rounded-2xl font-black shadow-xl shadow-primary-500/20 hover:scale-[1.02] transition-all"
                >
                    CONFIRM APPOINTMENT
                </button>
                <button 
                    v-else-if="appt.status === 'confirmed'"
                    @click="setStatus('checked_in')"
                    class="px-10 py-4 bg-green-600 text-white rounded-2xl font-black shadow-xl shadow-green-500/20 hover:scale-[1.02] transition-all"
                >
                    CHECK IN PATIENT
                </button>
                <button 
                    v-else-if="appt.status === 'checked_in'"
                    @click="setStatus('in_chair')"
                    class="px-10 py-4 bg-orange-500 text-white rounded-2xl font-black shadow-xl shadow-orange-500/20 hover:scale-[1.02] transition-all"
                >
                    PATIENT IN CHAIR
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Patient & Clinical -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Patient Card -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <p-avatar :label="appt.patient.first_name[0] + appt.patient.last_name[0]" size="xlarge" shape="circle" class="bg-primary-100 text-primary-600 font-bold" />
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ appt.patient.full_name }}</h3>
                                <div class="flex items-center gap-3 text-sm text-slate-500 mt-1">
                                    <span>{{ formatDate(appt.patient.dob) }}</span>
                                    <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                    <span>Balance: <span class="font-bold" :class="appt.patient.balance > 0 ? 'text-red-500' : 'text-green-500'">{{ formatCurrency(appt.patient.balance) }}</span></span>
                                </div>
                            </div>
                        </div>
                        <p-button label="View Patient Hub" class="p-button-outlined p-button-rounded p-button-sm" />
                    </div>
                    
                    <!-- Patient Alerts -->
                    <div class="flex flex-wrap gap-2">
                        <div v-for="alert in appt.patient.alerts" :key="alert.id" class="px-3 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-bold flex items-center gap-2 border border-red-100">
                            <AlertCircle class="w-3.5 h-3.5" />
                            {{ alert.message }}
                        </div>
                    </div>
                </div>

                <!-- Linked Procedures -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <FileText class="w-5 h-5 text-primary-500" />
                            <h3 class="font-bold text-slate-900 dark:text-white">Planned Procedures</h3>
                        </div>
                        <button class="text-xs font-bold text-primary-600 bg-primary-50 px-3 py-1.5 rounded-lg hover:bg-primary-100 transition-all">Quick Charge to Ledger</button>
                    </div>
                    <div class="p-0">
                        <p-data-table :value="appt.procedures || []" class="p-datatable-sm">
                            <p-column field="cdt_code" header="Code" class="text-xs font-mono font-bold"></p-column>
                            <p-column field="description" header="Procedure" class="text-sm font-medium"></p-column>
                            <p-column field="fee" header="Fee" class="text-sm font-bold">
                                <template #body="{ data }">{{ formatCurrency(data.fee) }}</template>
                            </p-column>
                            <p-column field="status" header="Status" class="text-xs uppercase font-bold text-slate-400"></p-column>
                        </p-data-table>
                    </div>
                </div>

            </div>

            <!-- Right Column: Administrative & Logistics -->
            <div class="space-y-6">
                
                <!-- Insurance Status -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <ShieldCheck class="w-5 h-5 text-blue-500" />
                        <h3 class="font-bold text-slate-900 dark:text-white">Insurance & Eligibility</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500">Carrier</span>
                            <span class="font-bold">{{ appt.patient.primary_insurance?.carrier_name || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500">Eligibility</span>
                            <p-tag value="Verified Today" severity="success" class="text-[10px] font-bold" />
                        </div>
                    </div>
                </div>

                <!-- Lab Case -->
                <div v-if="appt.lab_case" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <FlaskConical class="w-5 h-5 text-indigo-500" />
                        <h3 class="font-bold text-slate-900 dark:text-white">Lab Case Status</h3>
                    </div>
                    <div class="bg-indigo-50/50 p-4 rounded-2xl">
                        <div class="flex justify-between mb-2">
                            <span class="text-xs text-indigo-700 font-bold">#{{ appt.lab_case.case_number }}</span>
                            <p-tag :value="appt.lab_case.status" severity="info" class="text-[10px] font-bold" />
                        </div>
                        <p class="text-xs text-slate-600">{{ appt.lab_case.lab_name }}</p>
                    </div>
                </div>

                <!-- Communication History -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <MessageSquare class="w-5 h-5 text-cyan-500" />
                        <h3 class="font-bold text-slate-900 dark:text-white">Reminders Sent</h3>
                    </div>
                    <div class="space-y-4">
                        <div v-for="rem in appt.reminders" :key="rem.id" class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0">
                                <Mail v-if="rem.type === 'email'" class="w-4 h-4 text-slate-400" />
                                <Phone v-else class="w-4 h-4 text-slate-400" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700">{{ rem.message_type }}</p>
                                <p class="text-[10px] text-slate-400">{{ formatDate(rem.sent_at) }} • {{ rem.status }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
:deep(.p-datatable .p-datatable-thead > tr > th) {
    @apply bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase tracking-wider font-black;
}
</style>
