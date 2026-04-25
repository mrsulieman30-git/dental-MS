<script setup>
import { ref, onMounted } from 'vue';
import { useBillingStore } from '../../stores/billing.store';
import { formatCurrency } from '../../utils/formatters';
import {
    DollarSign, TrendingUp, CreditCard, Clock,
    ArrowUpRight, ArrowDownRight, Users,
    PieChart, Activity, Download, Calendar
} from 'lucide-vue-next';

const store = useBillingStore();
const timeframe = ref('mtd');

onMounted(async () => {
    await store.fetchDashboardKpis();
    await store.fetchAgingReport();
});

const kpis = [
    { label: 'Production', key: 'mtd_production', icon: TrendingUp, color: 'text-primary-600', bg: 'bg-primary-50' },
    { label: 'Collections', key: 'mtd_collections', icon: DollarSign, color: 'text-emerald-600', bg: 'bg-emerald-50' },
    { label: 'Adjustments', key: 'mtd_adjustments', icon: Activity, color: 'text-blue-600', bg: 'bg-blue-50' },
    { label: 'Total A/R', key: 'total_ar', icon: Clock, color: 'text-amber-600', bg: 'bg-amber-50' },
];

const agingBuckets = [
    { label: 'Current', key: '0_30', color: 'bg-emerald-500' },
    { label: '31 - 60 Days', key: '31_60', color: 'bg-amber-400' },
    { label: '61 - 90 Days', key: '61_90', color: 'bg-orange-500' },
    { label: '91+ Days', key: '91_plus', color: 'bg-red-500' },
];

const getMaxAging = () => {
    return Math.max(...Object.values(store.aging), 1);
};
</script>

<template>
    <div class="p-8 max-w-[1600px] mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tighter">Billing Dashboard</h1>
                <p class="text-slate-500 font-medium mt-1">Real-time financial performance and revenue cycle metrics.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white p-1 rounded-2xl border border-slate-200 flex shadow-sm">
                    <button @click="timeframe = 'today'" :class="['px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all', timeframe === 'today' ? 'bg-slate-900 text-white' : 'text-slate-400']">Today</button>
                    <button @click="timeframe = 'mtd'" :class="['px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all', timeframe === 'mtd' ? 'bg-slate-900 text-white' : 'text-slate-400']">MTD</button>
                </div>
                <button class="p-3 bg-white border border-slate-200 rounded-2xl shadow-sm hover:bg-slate-50 transition-all">
                    <Download class="w-5 h-5 text-slate-600" />
                </button>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="kpi in kpis" :key="kpi.label" class="bg-white p-6 rounded-[2rem] border border-slate-200/50 shadow-xl shadow-slate-200/10 group hover:scale-[1.02] transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center', kpi.bg]">
                        <component :is="kpi.icon" :class="['w-6 h-6', kpi.color]" />
                    </div>
                    <div class="flex items-center gap-1 text-emerald-500 font-black text-xs">
                        <ArrowUpRight class="w-4 h-4" /> 12%
                    </div>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ kpi.label }}</p>
                <h3 class="text-3xl font-black text-slate-900 tracking-tighter mt-1">{{ formatCurrency(store.kpis[kpi.key] || 0) }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- AR Aging Chart -->
            <div class="lg:col-span-2 bg-white p-8 rounded-[3rem] border border-slate-200/50 shadow-xl shadow-slate-200/10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tighter">Accounts Receivable Aging</h3>
                        <p class="text-xs text-slate-400 font-bold mt-1">Breakdown of outstanding balances by age.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase">Total A/R</p>
                        <p class="text-2xl font-black text-primary-600">{{ formatCurrency(store.kpis.total_ar || 0) }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div v-for="bucket in agingBuckets" :key="bucket.key" class="space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-black uppercase tracking-wider">
                            <span class="text-slate-500">{{ bucket.label }}</span>
                            <span class="text-slate-900">{{ formatCurrency(store.aging[bucket.key] || 0) }}</span>
                        </div>
                        <div class="h-3 bg-slate-50 rounded-full overflow-hidden">
                            <div 
                                :class="['h-full transition-all duration-1000', bucket.color]"
                                :style="{ width: ( (store.aging[bucket.key] || 0) / getMaxAging() * 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-100 grid grid-cols-4 gap-4">
                    <div v-for="bucket in agingBuckets" :key="bucket.key" class="text-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ bucket.label }}</p>
                        <p class="text-sm font-black text-slate-900">{{ Math.round((store.aging[bucket.key] || 0) / (store.kpis.total_ar || 1) * 100) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity / Task List -->
            <div class="bg-slate-900 p-8 rounded-[3rem] text-white shadow-2xl shadow-primary-900/40 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <PieChart class="w-32 h-32" />
                </div>
                
                <h3 class="text-lg font-black uppercase tracking-tighter mb-6 relative">Billing Tasks</h3>
                
                <div class="space-y-4 relative">
                    <div class="bg-white/10 p-4 rounded-2xl border border-white/10 hover:bg-white/20 transition-all cursor-pointer group">
                        <div class="flex items-start justify-between">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                                    <AlertCircle class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-black">12 Claims Denied</h4>
                                    <p class="text-[10px] text-white/60 font-medium">Require manual review & appeal</p>
                                </div>
                            </div>
                            <ChevronRight class="w-4 h-4 text-white/20 group-hover:text-white transition-all" />
                        </div>
                    </div>

                    <div class="bg-white/10 p-4 rounded-2xl border border-white/10 hover:bg-white/20 transition-all cursor-pointer group">
                        <div class="flex items-start justify-between">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center">
                                    <FileText class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-black">Ready to Bill</h4>
                                    <p class="text-[10px] text-white/60 font-medium">85 statements pending generation</p>
                                </div>
                            </div>
                            <ChevronRight class="w-4 h-4 text-white/20 group-hover:text-white transition-all" />
                        </div>
                    </div>

                    <div class="bg-white/10 p-4 rounded-2xl border border-white/10 hover:bg-white/20 transition-all cursor-pointer group">
                        <div class="flex items-start justify-between">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <CreditCard class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-black">Payment Plans</h4>
                                    <p class="text-[10px] text-white/60 font-medium">3 installments failed this morning</p>
                                </div>
                            </div>
                            <ChevronRight class="w-4 h-4 text-white/20 group-hover:text-white transition-all" />
                        </div>
                    </div>
                </div>

                <button class="w-full mt-8 py-4 bg-white text-slate-900 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-100 transition-all shadow-xl shadow-white/5">
                    View All Billing Tasks
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
</style>