<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/formatters';
import {
    FileJson, Upload, CheckCircle2, AlertCircle,
    Search, Filter, ChevronRight, FileText,
    ArrowRight, DollarSign, Building2
} from 'lucide-vue-next';

const eras = ref([]);
const loading = ref(false);
const uploading = ref(false);
const posting = ref(null); // ID of ERA being posted

const fetchEras = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/eras');
        eras.value = data.data;
    } finally { loading.value = false; }
};

const onFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    uploading.value = true;
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        await axios.post('/api/eras/upload', formData);
        await fetchEras();
    } finally { uploading.value = false; }
};

const postEra = async (era) => {
    posting.value = era.id;
    try {
        await axios.post(`/api/eras/${era.id}/post`);
        await fetchEras();
    } finally { posting.value = null; }
};

onMounted(fetchEras);
</script>

<template>
    <div class="p-8 max-w-[1600px] mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tighter">Electronic Remittance (ERA)</h1>
                <p class="text-slate-500 font-medium mt-1">Process EDI 835 files and auto-post insurance payments.</p>
            </div>
            <div class="flex items-center gap-3">
                <input type="file" ref="fileInput" class="hidden" @change="onFileUpload" accept=".edi,.txt,.x12" />
                <button 
                    @click="$refs.fileInput.click()" 
                    :disabled="uploading"
                    class="px-6 py-3 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-primary-600/20 hover:bg-primary-700 transition-all flex items-center gap-2"
                >
                    <Upload v-if="!uploading" class="w-4 h-4" />
                    <p-progress-spinner v-else style="width: 16px; height: 16px" strokeWidth="8" />
                    {{ uploading ? 'Uploading...' : 'Upload 835 File' }}
                </button>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-3xl border border-slate-200/50 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Unposted Total</p>
                <h3 class="text-2xl font-black text-slate-900">{{ formatCurrency(eras.filter(e => !e.is_posted).reduce((s, e) => s + Number(e.total_payment), 0)) }}</h3>
                <p class="text-[10px] text-amber-500 font-bold mt-1">{{ eras.filter(e => !e.is_posted).length }} files awaiting posting</p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-200/50 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Processed (MTD)</p>
                <h3 class="text-2xl font-black text-emerald-600">{{ formatCurrency(eras.filter(e => e.is_posted).reduce((s, e) => s + Number(e.total_payment), 0)) }}</h3>
                <p class="text-[10px] text-slate-400 font-bold mt-1">Successfully auto-posted to ledger</p>
            </div>
        </div>

        <!-- ERA Table -->
        <div class="bg-white rounded-[2.5rem] border border-slate-200/50 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase font-black text-slate-400 tracking-widest bg-slate-50/50">
                        <th class="px-8 py-6">ERA Details</th>
                        <th class="px-4 py-6">Payer</th>
                        <th class="px-4 py-6">Check Info</th>
                        <th class="px-4 py-6 text-right">Payment</th>
                        <th class="px-4 py-6">Status</th>
                        <th class="px-8 py-6 w-40"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="era in eras" :key="era.id" class="group hover:bg-slate-50/50 transition-all">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                                    <FileJson class="w-5 h-5 text-slate-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-900">{{ era.file_name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold">RECV: {{ formatDate(era.received_at) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-6">
                            <div class="flex items-center gap-2">
                                <Building2 class="w-4 h-4 text-slate-300" />
                                <div>
                                    <p class="text-xs font-bold text-slate-700">{{ era.payer_name }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium">ID: {{ era.payer_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-6">
                            <p class="text-xs font-bold text-slate-700">#{{ era.check_number }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ formatDate(era.check_date) }}</p>
                        </td>
                        <td class="px-4 py-6 text-right">
                            <p class="text-sm font-black text-slate-900">{{ formatCurrency(era.total_payment) }}</p>
                        </td>
                        <td class="px-4 py-6">
                            <div v-if="era.is_posted" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-wider">
                                <CheckCircle2 class="w-3.5 h-3.5" /> Posted
                            </div>
                            <div v-else class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-black uppercase tracking-wider">
                                <Clock class="w-3.5 h-3.5" /> Unposted
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <button 
                                v-if="!era.is_posted"
                                @click="postEra(era)"
                                :disabled="posting === era.id"
                                class="px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2"
                            >
                                <ArrowRight v-if="posting !== era.id" class="w-3.5 h-3.5" />
                                <p-progress-spinner v-else style="width: 12px; height: 12px" strokeWidth="8" />
                                Post to Ledger
                            </button>
                            <p v-else class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Posted: {{ formatDate(era.posted_at) }}</p>
                        </td>
                    </tr>
                    <tr v-if="!eras.length && !loading">
                        <td colspan="6" class="py-24 text-center">
                            <div class="max-w-xs mx-auto">
                                <FileJson class="w-12 h-12 text-slate-200 mx-auto mb-4" />
                                <p class="text-sm text-slate-400 font-bold">No ERA files uploaded yet.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
</style>