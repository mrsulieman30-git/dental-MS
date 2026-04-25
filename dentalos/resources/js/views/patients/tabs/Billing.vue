<script setup>
import { ref, inject, onMounted, computed } from 'vue';
import { useBillingStore } from '../../../stores/billing.store';
import { useClaimStore } from '../../../stores/claim.store';
import { formatCurrency, formatDate } from '../../../utils/formatters';
import ClaimFormModal from '../../../components/billing/ClaimFormModal.vue';
import {
    DollarSign, CreditCard, Receipt, FileText,
    Plus, History, Filter, Search, MoreHorizontal,
    ArrowUpRight, ArrowDownLeft, Ban, Printer, Send
} from 'lucide-vue-next';

const patient = inject('patient');
const store = useBillingStore();
const claimStore = useClaimStore();

const showPaymentModal = ref(false);
const showAdjustmentModal = ref(false);
const showClaimModal = ref(false);
const activeClaimId = ref(null);
const filters = ref({
    type: 'all',
    dateRange: null
});

onMounted(async () => {
    if (patient.value?.id) {
        await store.fetchLedger(patient.value.id);
    }
});

const getEntryIcon = (type) => {
    switch (type) {
        case 'charge': return ArrowUpRight;
        case 'payment': return ArrowDownLeft;
        case 'adjustment': return History;
        case 'refund': return Ban;
        default: return FileText;
    }
};

const getEntryColor = (type, isVoid) => {
    if (isVoid) return 'text-slate-300 line-through';
    switch (type) {
        case 'charge': return 'text-slate-900 dark:text-white';
        case 'payment': return 'text-emerald-600';
        case 'adjustment': return 'text-blue-600';
        case 'refund': return 'text-red-600';
        default: return 'text-slate-600';
    }
};

const openClaim = (claimId) => {
    activeClaimId.value = claimId;
    showClaimModal.value = true;
};

const voidEntry = async (entry) => {
    // In a real app, show a confirmation dialog with reason input
    const reason = prompt('Reason for voiding:');
    if (reason) {
        await store.voidEntry(entry.id, patient.value.id, reason);
    }
};

const printLedger = () => window.print();

const sendStatement = async () => {
    // Mock sending statement
    alert('Statement sent to ' + patient.value.full_name);
};
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Billing Header KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-primary-50 rounded-xl flex items-center justify-center">
                        <DollarSign class="w-4 h-4 text-primary-600" />
                    </div>
                    <span class="text-[10px] uppercase font-black text-slate-400 tracking-wider">Account Balance</span>
                </div>
                <p :class="['text-2xl font-black', store.currentBalance > 0 ? 'text-red-500' : 'text-emerald-600']">
                    {{ formatCurrency(store.currentBalance) }}
                </p>
                <p class="text-[10px] text-slate-400 font-bold mt-1">Total outstanding due</p>
            </div>

            <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-emerald-50 rounded-xl flex items-center justify-center">
                        <CreditCard class="w-4 h-4 text-emerald-600" />
                    </div>
                    <span class="text-[10px] uppercase font-black text-slate-400 tracking-wider">Unapplied Credits</span>
                </div>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ formatCurrency(0) }}</p>
                <p class="text-[10px] text-slate-400 font-bold mt-1">Payments not yet allocated</p>
            </div>

            <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                        <Receipt class="w-4 h-4 text-blue-600" />
                    </div>
                    <span class="text-[10px] uppercase font-black text-slate-400 tracking-wider">Last Statement</span>
                </div>
                <p class="text-sm font-black text-slate-900 dark:text-white">{{ patient?.last_statement_at ? formatDate(patient.last_statement_at) : 'Never Sent' }}</p>
                <button @click="sendStatement" class="text-[10px] text-primary-600 font-bold mt-1 hover:underline">Send Statement Now</button>
            </div>

            <div class="flex flex-col gap-2">
                <button @click="showPaymentModal = true" class="flex-1 bg-primary-600 text-white rounded-2xl font-bold text-xs flex items-center justify-center gap-2 hover:bg-primary-700 transition-all shadow-lg shadow-primary-600/20">
                    <Plus class="w-4 h-4" /> Post Payment
                </button>
                <div class="flex gap-2 h-1/2">
                    <button @click="showAdjustmentModal = true" class="flex-1 bg-white border border-slate-200 rounded-2xl font-bold text-[10px] flex items-center justify-center gap-1.5 hover:bg-slate-50 transition-all">
                        <History class="w-3.5 h-3.5" /> Adjust
                    </button>
                    <button @click="printLedger" class="flex-1 bg-white border border-slate-200 rounded-2xl font-bold text-[10px] flex items-center justify-center gap-1.5 hover:bg-slate-50 transition-all">
                        <Printer class="w-3.5 h-3.5" /> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/50 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-8 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tighter text-sm">Patient Ledger</h3>
                    <div class="flex items-center bg-slate-50 rounded-lg px-2 py-1">
                        <Search class="w-3.5 h-3.5 text-slate-400" />
                        <input type="text" placeholder="Search entries..." class="bg-transparent border-0 text-[10px] focus:ring-0 w-32 font-medium" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="p-2 text-slate-400 hover:bg-slate-50 rounded-lg transition-all"><Filter class="w-4 h-4" /></button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-slate-400 tracking-wider bg-slate-50/50 dark:bg-slate-800/50">
                            <th class="px-8 py-4 w-32">Date</th>
                            <th class="px-4 py-4">Description</th>
                            <th class="px-4 py-4">Code</th>
                            <th class="px-4 py-4 text-right">Amount</th>
                            <th class="px-8 py-4 text-right">Balance</th>
                            <th class="px-4 py-4 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="entry in store.ledgerEntries" :key="entry.id" class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-8 py-4 text-[11px] font-bold text-slate-500">{{ formatDate(entry.entry_date) }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <component :is="getEntryIcon(entry.entry_type)" :class="['w-4 h-4', entry.is_void ? 'text-slate-200' : 'text-slate-400']" />
                                    <div>
                                        <p :class="['text-xs font-black', getEntryColor(entry.entry_type, entry.is_void)]">{{ entry.description }}</p>
                                        <p v-if="entry.notes" class="text-[9px] text-slate-400 font-medium italic mt-0.5">{{ entry.notes }}</p>
                                    </div>
                                    <button v-if="entry.claim_id" @click="openClaim(entry.claim_id)" class="px-1.5 py-0.5 bg-blue-50 text-blue-600 text-[8px] font-black rounded uppercase tracking-wider hover:bg-blue-100 transition-all">Claim</button>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-mono text-[10px] font-bold text-slate-400">{{ entry.cdt_code?.code || '—' }}</td>
                            <td :class="['px-4 py-4 text-right text-xs font-black', getEntryColor(entry.entry_type, entry.is_void)]">
                                {{ formatCurrency(entry.amount) }}
                            </td>
                            <td class="px-8 py-4 text-right text-xs font-bold text-slate-400">
                                {{ entry.is_void ? '—' : formatCurrency(entry.running_balance) }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <p-menu :model="[]" popup ref="menu" />
                                <button v-if="!entry.is_void" @click="voidEntry(entry)" class="opacity-0 group-hover:opacity-100 p-1.5 text-slate-300 hover:text-red-500 transition-all">
                                    <Ban class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!store.ledgerEntries.length" class="text-center py-20">
                            <td colspan="6" class="py-20 text-slate-300 italic text-sm">No ledger entries found for this patient.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Ledger Footer -->
            <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase">Total Charges</p>
                        <p class="text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(store.ledgerEntries.filter(e => e.entry_type === 'charge' && !e.is_void).reduce((s, e) => s + Number(e.amount), 0)) }}</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase">Total Credits</p>
                        <p class="text-sm font-black text-emerald-600">{{ formatCurrency(Math.abs(store.ledgerEntries.filter(e => ['payment', 'refund', 'adjustment'].includes(e.entry_type) && !e.is_void && Number(e.amount) < 0).reduce((s, e) => s + Number(e.amount), 0))) }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black text-slate-400 uppercase">Outstanding Balance</p>
                    <p :class="['text-xl font-black', store.currentBalance > 0 ? 'text-red-500' : 'text-emerald-600']">{{ formatCurrency(store.currentBalance) }}</p>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <ClaimFormModal v-model:visible="showClaimModal" :claimId="activeClaimId" />
        
        <!-- TODO: Add PaymentModal and AdjustmentModal components -->
    </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
</style>