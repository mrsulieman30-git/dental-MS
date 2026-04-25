<script setup>
import { ref, onMounted } from 'vue';
import { useClaimStore } from '../../stores/claim.store';
import { formatCurrency, formatDate } from '../../utils/formatters';
import ClaimFormModal from '../../components/billing/ClaimFormModal.vue';
import {
    Search, Filter, MoreHorizontal, FileText,
    Send, AlertCircle, CheckCircle2, Clock,
    ChevronRight, Download, Plus
} from 'lucide-vue-next';

const store = useClaimStore();
const showClaimModal = ref(false);
const activeClaimId = ref(null);
const activeFilter = ref('all');

onMounted(async () => {
    await store.fetchClaims();
});

const openClaim = (id) => {
    activeClaimId.value = id;
    showClaimModal.value = true;
};

const statusConfig = {
    draft: { label: 'Draft', color: 'bg-slate-100 text-slate-600', icon: Clock },
    submitted: { label: 'Submitted', color: 'bg-blue-100 text-blue-700', icon: Send },
    paid: { label: 'Paid', color: 'bg-emerald-100 text-emerald-700', icon: CheckCircle2 },
    denied: { label: 'Denied', color: 'bg-red-100 text-red-700', icon: AlertCircle },
};

const filters = [
    { label: 'All Claims', value: 'all' },
    { label: 'Draft', value: 'draft' },
    { label: 'Pending', value: 'submitted' },
    { label: 'Paid', value: 'paid' },
    { label: 'Denied', value: 'denied' },
];
</script>

<template>
    <div class="p-8 max-w-[1600px] mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tighter">Insurance Claims</h1>
                <p class="text-slate-500 font-medium mt-1">Track and manage all dental insurance claims and submissions.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-6 py-3 bg-white border border-slate-200 rounded-2xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                    <Download class="w-4 h-4" /> Export Report
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="flex items-center justify-between bg-white p-2 rounded-2xl border border-slate-200/50 shadow-sm">
            <div class="flex items-center gap-1">
                <button 
                    v-for="f in filters" :key="f.value"
                    @click="activeFilter = f.value"
                    :class="['px-6 py-2 rounded-xl text-xs font-black transition-all uppercase tracking-wider', activeFilter === f.value ? 'bg-slate-900 text-white' : 'text-slate-400 hover:text-slate-600']"
                >
                    {{ f.label }}
                </button>
            </div>
            <div class="flex items-center gap-2 px-4 border-l border-slate-100">
                <Search class="w-4 h-4 text-slate-300" />
                <input type="text" placeholder="Search by patient, claim #..." class="bg-transparent border-0 text-xs focus:ring-0 w-64 font-bold" />
            </div>
        </div>

        <!-- Claims Table -->
        <div class="bg-white rounded-[2.5rem] border border-slate-200/50 shadow-xl shadow-slate-200/20 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase font-black text-slate-400 tracking-widest bg-slate-50/50">
                        <th class="px-8 py-6">Claim Details</th>
                        <th class="px-4 py-6">Patient</th>
                        <th class="px-4 py-6">Insurance Carrier</th>
                        <th class="px-4 py-6">Status</th>
                        <th class="px-4 py-6 text-right">Billed</th>
                        <th class="px-4 py-6 text-right">Paid</th>
                        <th class="px-8 py-6 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="claim in store.claims" :key="claim.id" class="group hover:bg-slate-50/50 transition-all cursor-pointer" @click="openClaim(claim.id)">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-white group-hover:shadow-sm transition-all">
                                    <FileText class="w-5 h-5 text-slate-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-900">{{ claim.claim_number }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">{{ formatDate(claim.created_at) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-6">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center text-[10px] font-black text-primary-700">
                                    {{ claim.patient?.first_name[0] }}{{ claim.patient?.last_name[0] }}
                                </div>
                                <p class="text-xs font-bold text-slate-700">{{ claim.patient?.full_name }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-6">
                            <p class="text-xs font-bold text-slate-700">{{ claim.insurance?.carrier?.name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">EDI: {{ claim.insurance?.carrier?.payer_id || '—' }}</p>
                        </td>
                        <td class="px-4 py-6">
                            <div :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider', statusConfig[claim.status]?.color]">
                                <component :is="statusConfig[claim.status]?.icon" class="w-3.5 h-3.5" />
                                {{ statusConfig[claim.status]?.label }}
                            </div>
                        </td>
                        <td class="px-4 py-6 text-right">
                            <p class="text-xs font-black text-slate-900">{{ formatCurrency(claim.total_billed) }}</p>
                        </td>
                        <td class="px-4 py-6 text-right">
                            <p class="text-xs font-black text-emerald-600">{{ formatCurrency(claim.total_paid) }}</p>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <button class="p-2 text-slate-300 hover:text-slate-600 hover:bg-white hover:shadow-sm rounded-lg transition-all">
                                <ChevronRight class="w-5 h-5" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!store.claims.length && !store.loading">
                        <td colspan="7" class="py-24 text-center">
                            <div class="max-w-xs mx-auto">
                                <div class="w-16 h-16 bg-slate-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                    <FileText class="w-8 h-8 text-slate-300" />
                                </div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">No Claims Found</h3>
                                <p class="text-xs text-slate-400 font-medium mt-1">There are no claims matching your current filters.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination Placeholder -->
            <div class="px-8 py-6 border-t border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-400 font-bold tracking-tight">Showing {{ store.claims.length }} results</p>
                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-wider disabled:opacity-50" disabled>Previous</button>
                    <button class="px-4 py-2 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-wider disabled:opacity-50" disabled>Next</button>
                </div>
            </div>
        </div>

        <!-- Claim Modal -->
        <ClaimFormModal v-model:visible="showClaimModal" :claimId="activeClaimId" @submitted="store.fetchClaims()" />
    </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
</style>