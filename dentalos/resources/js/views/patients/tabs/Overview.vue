<script setup>
import { inject, computed } from 'vue';
import { 
    formatDate, formatCurrency, formatDateTime 
} from '../../../utils/formatters';
import { 
    Calendar, ClipboardList, Wallet, 
    ShieldCheck, RefreshCcw, Bell, 
    MessageCircle, ArrowUpRight, ArrowRight
} from 'lucide-vue-next';

const patient = inject('patient');

const upcomingAppointments = computed(() => patient.value?.upcoming_appointments || []);
const recentAppointments = computed(() => patient.value?.recent_appointments || []);
const activePlan = computed(() => patient.value?.active_treatment_plan);
const financialSummary = computed(() => patient.value?.financial_summary || {
    balance: 0,
    insurance_ar: 0,
    has_payment_plan: false
});
const insuranceBenefits = computed(() => patient.value?.primary_insurance?.benefits || {
    remaining: 0,
    deductible_met: 0,
    deductible_total: 0
});
const recalls = computed(() => patient.value?.recalls || []);
const medicalAlerts = computed(() => patient.value?.alerts?.slice(0, 3) || []);
const lastComm = computed(() => patient.value?.last_communication);

const getStatusSeverity = (status) => {
    switch (status) {
        case 'scheduled': return 'info';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        case 'no_show': return 'warning';
        default: return 'secondary';
    }
};
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-in fade-in duration-500">
        
        <!-- Column 1: Schedule & Clinical -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Upcoming Appointments -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-5 h-5 text-primary-500" />
                        <h3 class="font-bold text-slate-900 dark:text-white">Upcoming Appointments</h3>
                    </div>
                    <button class="text-xs font-bold text-primary-600 hover:underline">View All</button>
                </div>
                <div class="p-0">
                    <div v-if="upcomingAppointments.length" class="divide-y divide-slate-50 dark:divide-slate-800">
                        <div v-for="appt in upcomingAppointments" :key="appt.id" class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/30 rounded-xl flex flex-col items-center justify-center text-primary-600">
                                    <span class="text-[10px] font-bold uppercase leading-none">{{ formatDate(appt.start_time, 'MMM') }}</span>
                                    <span class="text-sm font-black leading-none mt-1">{{ formatDate(appt.start_time, 'dd') }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ appt.appointment_type?.name }}</p>
                                    <p class="text-xs text-slate-500">{{ formatDateTime(appt.start_time, 'h:mm a') }} • {{ appt.provider?.full_name }}</p>
                                </div>
                            </div>
                            <p-tag :value="appt.status" :severity="getStatusSeverity(appt.status)" class="text-[10px] uppercase font-bold" />
                        </div>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-sm text-slate-400">No upcoming appointments</p>
                        <p-button label="Schedule Now" icon="pi pi-plus" class="p-button-text p-button-sm mt-2" />
                    </div>
                </div>
            </div>

            <!-- Active Treatment Plan -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <ClipboardList class="w-5 h-5 text-primary-500" />
                        <h3 class="font-bold text-slate-900 dark:text-white">Active Treatment Plan</h3>
                    </div>
                    <p-tag v-if="activePlan" :value="activePlan.status" severity="success" class="text-[10px] uppercase font-bold" />
                </div>
                
                <div v-if="activePlan" class="space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="space-y-1">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Total Fee</p>
                            <p class="text-lg font-black text-slate-900 dark:text-white">{{ formatCurrency(activePlan.total_fee) }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Est. Patient</p>
                            <p class="text-lg font-black text-primary-600">{{ formatCurrency(activePlan.patient_portion) }}</p>
                        </div>
                        <div class="space-y-1 text-center">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Progress</p>
                            <p class="text-lg font-black text-slate-900 dark:text-white">{{ activePlan.progress_percent }}%</p>
                        </div>
                        <div class="space-y-1 text-right">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Procedures</p>
                            <p class="text-lg font-black text-slate-900 dark:text-white">{{ activePlan.completed_count }}/{{ activePlan.total_count }}</p>
                        </div>
                    </div>
                    <p-progress-bar :value="activePlan.progress_percent" :showValue="false" class="h-2 rounded-full" />
                    <button class="w-full py-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 flex items-center justify-center gap-2 transition-all">
                        Open Plan Manager
                        <ArrowUpRight class="w-4 h-4" />
                    </button>
                </div>
                <div v-else class="py-8 text-center border-2 border-dashed border-slate-100 dark:border-slate-800 rounded-3xl">
                    <p class="text-sm text-slate-400">No active treatment plans</p>
                    <button class="text-sm font-bold text-primary-600 mt-2 hover:underline">Create Proposed Plan</button>
                </div>
            </div>

            <!-- Recent Activity / Appointments -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <RefreshCcw class="w-5 h-5 text-primary-500" />
                    <h3 class="font-bold text-slate-900 dark:text-white">Recent Activity</h3>
                </div>
                <div class="space-y-4">
                    <div v-for="appt in recentAppointments" :key="appt.id" class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ appt.appointment_type?.name }}</span>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">{{ formatDate(appt.start_time) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2: Financial & Administrative -->
        <div class="space-y-6">
            
            <!-- Financial Summary -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <Wallet class="w-5 h-5 text-primary-500" />
                    <h3 class="font-bold text-slate-900 dark:text-white">Financial Summary</h3>
                </div>
                <div class="space-y-6">
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 text-center">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">Current Balance</p>
                        <p :class="[
                            'text-3xl font-black',
                            financialSummary.balance > 0 ? 'text-red-500' : 'text-green-500'
                        ]">
                            {{ formatCurrency(financialSummary.balance) }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/30 rounded-xl">
                            <p class="text-[9px] uppercase text-slate-400 font-bold mb-1">Insurance A/R</p>
                            <p class="text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(financialSummary.insurance_ar) }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/30 rounded-xl">
                            <p class="text-[9px] uppercase text-slate-400 font-bold mb-1">Pmt Plan</p>
                            <p class="text-sm font-black" :class="financialSummary.has_payment_plan ? 'text-blue-500' : 'text-slate-400'">
                                {{ financialSummary.has_payment_plan ? 'Active' : 'None' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insurance Benefits -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="w-5 h-5 text-primary-500" />
                        <h3 class="font-bold text-slate-900 dark:text-white">Insurance</h3>
                    </div>
                    <span class="text-[10px] font-bold text-primary-600 bg-primary-50 px-2 py-0.5 rounded uppercase">Primary</span>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 font-medium">Benefits Remaining</span>
                        <span class="text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(insuranceBenefits.remaining) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 font-medium">Deductible</span>
                        <span class="text-sm font-black text-slate-900 dark:text-white">
                            {{ formatCurrency(insuranceBenefits.deductible_met) }} / {{ formatCurrency(insuranceBenefits.deductible_total) }}
                        </span>
                    </div>
                    <p-progress-bar :value="(insuranceBenefits.deductible_met / insuranceBenefits.deductible_total) * 100" :showValue="false" class="h-1.5" />
                </div>
            </div>

            <!-- Recall Status -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <Bell class="w-5 h-5 text-primary-500" />
                    <h3 class="font-bold text-slate-900 dark:text-white">Recall & Hygiene</h3>
                </div>
                <div class="space-y-4">
                    <div v-for="recall in recalls" :key="recall.id" class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-900 dark:text-white">{{ recall.type }}</p>
                            <p class="text-[10px] text-slate-500">{{ formatDate(recall.due_date) }}</p>
                        </div>
                        <button class="p-2 bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100 transition-colors">
                            <Calendar class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Last Communication -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <MessageCircle class="w-5 h-5 text-primary-500" />
                    <h3 class="font-bold text-slate-900 dark:text-white">Last Communication</h3>
                </div>
                <div v-if="lastComm" class="bg-slate-50 dark:bg-slate-800/30 p-4 rounded-2xl relative">
                    <p class="text-xs text-slate-700 dark:text-slate-300 italic">"{{ lastComm.body.substring(0, 80) }}..."</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400">{{ formatDate(lastComm.sent_at) }} via {{ lastComm.channel }}</span>
                        <ArrowRight class="w-3 h-3 text-slate-400" />
                    </div>
                </div>
                <div v-else class="text-center py-4">
                    <p class="text-xs text-slate-400">No recent communications</p>
                </div>
            </div>

        </div>
    </div>
</template>
