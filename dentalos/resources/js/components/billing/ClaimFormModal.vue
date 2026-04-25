<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useClaimStore } from '../../stores/claim.store';
import { formatCurrency, formatDate } from '../../utils/formatters';
import {
    X, CheckCircle2, AlertCircle, FileText,
    Image as ImageIcon, Upload, Send, Printer,
    Trash2, ChevronRight, MessageSquare
} from 'lucide-vue-next';

const props = defineProps({
    visible: Boolean,
    claimId: [Number, String]
});
const emit = defineEmits(['update:visible', 'submitted']);

const store = useClaimStore();
const scrubbing = ref(false);
const uploading = ref(false);
const activeTab = ref('form'); // form, attachments, history

const claim = computed(() => store.activeClaim);

onMounted(async () => {
    if (props.claimId) {
        await store.fetchClaim(props.claimId);
    }
});

watch(() => props.claimId, async (newId) => {
    if (newId) await store.fetchClaim(newId);
});

const close = () => emit('update:visible', false);

const runScrubber = async () => {
    scrubbing.value = true;
    try {
        await store.scrubClaim(props.claimId);
    } finally { scrubbing.value = false; }
};

const submitClaim = async () => {
    try {
        await store.submitClaim(props.claimId);
        emit('submitted');
        close();
    } catch (err) {
        console.error(err);
    }
};

const onFileUpload = async (event, type) => {
    const file = event.target.files[0];
    if (!file) return;
    
    uploading.value = true;
    try {
        await store.addAttachment(props.claimId, { file, type });
    } finally { uploading.value = false; }
};

const statusConfig = {
    draft: { label: 'Draft', color: 'bg-slate-100 text-slate-600' },
    submitted: { label: 'Submitted', color: 'bg-blue-100 text-blue-700' },
    paid: { label: 'Paid', color: 'bg-emerald-100 text-emerald-700' },
    denied: { label: 'Denied', color: 'bg-red-100 text-red-700' },
};
</script>

<template>
    <p-dialog
        :visible="visible"
        @update:visible="close"
        modal
        maximizable
        class="claim-form-dialog"
        :style="{ width: '90vw', maxWidth: '1200px' }"
        :contentStyle="{ padding: 0 }"
        :header="'Insurance Claim: ' + (claim?.claim_number || 'Loading...')"
    >
        <div v-if="store.loading" class="p-12 text-center">
            <p-progress-spinner style="width: 50px; height: 50px" />
        </div>

        <div v-else-if="claim" class="flex flex-col h-[85vh]">
            <!-- Header Status Bar -->
            <div class="px-6 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Status</span>
                        <span :class="['px-2 py-0.5 rounded text-[10px] font-black uppercase', statusConfig[claim.status]?.color]">
                            {{ statusConfig[claim.status]?.label }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Scrubbing</span>
                        <div v-if="claim.is_scrubbed" class="flex items-center gap-1">
                            <CheckCircle2 v-if="!claim.scrubbing_errors?.length" class="w-4 h-4 text-emerald-500" />
                            <AlertCircle v-else class="w-4 h-4 text-red-500" />
                            <span :class="['text-[10px] font-bold', claim.scrubbing_errors?.length ? 'text-red-500' : 'text-emerald-500']">
                                {{ claim.scrubbing_errors?.length ? claim.scrubbing_errors.length + ' Errors' : 'Clean' }}
                            </span>
                        </div>
                        <button v-else @click="runScrubber" class="text-[10px] font-bold text-primary-600 hover:underline">
                            Run Scrubber
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="activeTab = 'form'" :class="['px-4 py-1.5 rounded-lg text-xs font-bold transition-all', activeTab === 'form' ? 'bg-white shadow text-primary-600' : 'text-slate-500']">Claim Form</button>
                    <button @click="activeTab = 'attachments'" :class="['px-4 py-1.5 rounded-lg text-xs font-bold transition-all', activeTab === 'attachments' ? 'bg-white shadow text-primary-600' : 'text-slate-500']">Attachments ({{ claim.attachments?.length || 0 }})</button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-100/50">
                <!-- Errors List -->
                <div v-if="claim.scrubbing_errors?.length" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl">
                    <h4 class="text-xs font-black text-red-700 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <AlertCircle class="w-4 h-4" />
                        Validation Errors
                    </h4>
                    <ul class="space-y-1">
                        <li v-for="(err, i) in claim.scrubbing_errors" :key="i" class="text-xs text-red-600 font-medium list-disc ml-4">{{ err }}</li>
                    </ul>
                </div>

                <div v-if="activeTab === 'form'" class="space-y-6">
                    <!-- ADA Form Mockup -->
                    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden p-8 font-sans">
                        <div class="flex justify-between border-b-2 border-slate-900 pb-4 mb-6">
                            <h2 class="text-xl font-black text-slate-900">ADA Dental Claim Form</h2>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-slate-400">HEADER INFORMATION</p>
                                <p class="text-sm font-bold">{{ claim.claim_type.toUpperCase() }} CLAIM</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <!-- Left Col: Insurance & Patient -->
                            <div class="space-y-6">
                                <section>
                                    <h5 class="text-[10px] font-black text-slate-400 mb-2 uppercase">Insurance Carrier</h5>
                                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                        <p class="text-sm font-black text-slate-900">{{ claim.insurance?.carrier?.name }}</p>
                                        <p class="text-xs text-slate-500">{{ claim.insurance?.carrier?.address?.line1 }}</p>
                                        <p class="text-xs text-slate-500">{{ claim.insurance?.carrier?.address?.city }}, {{ claim.insurance?.carrier?.address?.state }}</p>
                                    </div>
                                </section>

                                <section>
                                    <h5 class="text-[10px] font-black text-slate-400 mb-2 uppercase">Patient Information</h5>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[9px] text-slate-400 font-bold">NAME</label>
                                            <p class="text-xs font-bold">{{ claim.patient?.full_name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-[9px] text-slate-400 font-bold">DOB</label>
                                            <p class="text-xs font-bold">{{ formatDate(claim.patient?.date_of_birth) }}</p>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- Right Col: Provider & Billing -->
                            <div class="space-y-6">
                                <section>
                                    <h5 class="text-[10px] font-black text-slate-400 mb-2 uppercase">Rendering Provider</h5>
                                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                        <p class="text-sm font-black text-slate-900">{{ claim.rendering_provider?.name }}</p>
                                        <p class="text-xs font-bold text-primary-600">NPI: {{ claim.rendering_provider?.npi || 'NOT SET' }}</p>
                                    </div>
                                </section>

                                <section>
                                    <h5 class="text-[10px] font-black text-slate-400 mb-2 uppercase">Location</h5>
                                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                        <p class="text-xs font-bold">{{ claim.location?.name }}</p>
                                        <p class="text-[10px] text-slate-500">{{ claim.location?.address }}</p>
                                    </div>
                                </section>Section
                            </div>
                        </div>

                        <!-- Service Line Items -->
                        <div class="mt-8">
                            <h5 class="text-[10px] font-black text-slate-400 mb-2 uppercase">Service Information</h5>
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-slate-900 text-white text-[9px] font-black uppercase">
                                        <th class="p-2 text-left rounded-tl-lg">Date of Service</th>
                                        <th class="p-2 text-left">Tooth</th>
                                        <th class="p-2 text-left">Surf</th>
                                        <th class="p-2 text-left">CDT Code</th>
                                        <th class="p-2 text-left">Description</th>
                                        <th class="p-2 text-right rounded-tr-lg">Fee Billed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in claim.line_items" :key="item.id" class="border-b border-slate-100 text-xs font-medium">
                                        <td class="p-2">{{ formatDate(claim.created_at) }}</td>
                                        <td class="p-2">#{{ item.tooth_number || '—' }}</td>
                                        <td class="p-2">{{ item.surfaces?.join('') || '—' }}</td>
                                        <td class="p-2 font-mono font-bold">{{ item.cdt_code?.code }}</td>
                                        <td class="p-2">{{ item.description }}</td>
                                        <td class="p-2 text-right font-bold">{{ formatCurrency(item.fee_billed) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-50">
                                        <td colspan="5" class="p-2 text-right text-[10px] font-black">TOTAL BILLED</td>
                                        <td class="p-2 text-right text-sm font-black">{{ formatCurrency(claim.total_billed) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'attachments'" class="space-y-6">
                    <!-- Upload Area -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 text-center flex flex-col items-center justify-center">
                            <ImageIcon class="w-8 h-8 text-slate-300 mb-2" />
                            <h4 class="text-xs font-bold mb-1">X-Rays & Photos</h4>
                            <p class="text-[10px] text-slate-400 mb-4">Required for major procedures</p>
                            <input type="file" ref="fileInput" class="hidden" @change="(e) => onFileUpload(e, 'xray')" />
                            <button @click="$refs.fileInput.click()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-[10px] font-bold transition-all">Upload Image</button>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 text-center flex flex-col items-center justify-center">
                            <MessageSquare class="w-8 h-8 text-slate-300 mb-2" />
                            <h4 class="text-xs font-bold mb-1">Clinical Narrative</h4>
                            <p class="text-[10px] text-slate-400 mb-4">Explain clinical necessity</p>
                            <button class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-[10px] font-bold transition-all">Add Narrative</button>
                        </div>
                    </div>

                    <!-- Existing Attachments -->
                    <div class="space-y-2">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Included Attachments</h4>
                        <div v-for="att in claim.attachments" :key="att.id" class="flex items-center justify-between p-3 bg-white rounded-xl border border-slate-200">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-slate-50 rounded flex items-center justify-center">
                                    <FileText class="w-4 h-4 text-slate-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold">{{ att.file_name }}</p>
                                    <p class="text-[9px] uppercase font-black text-slate-400">{{ att.attachment_type }}</p>
                                </div>
                            </div>
                            <button class="text-red-400 hover:text-red-600 transition-colors">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                        <div v-if="!claim.attachments?.length" class="text-center py-12 text-slate-300 italic text-sm">No attachments yet</div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-white border-t border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-all" title="Print Form">
                        <Printer class="w-5 h-5" />
                    </button>
                    <a :href="'/api/claims/' + claimId + '/preview'" target="_blank" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition-all flex items-center gap-2">
                        <FileText class="w-4 h-4" /> Preview PDF
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="close" class="px-4 py-2 text-xs font-bold text-slate-500">Cancel</button>
                    <button 
                        v-if="claim.status === 'draft'"
                        @click="submitClaim" 
                        :disabled="!claim.is_scrubbed || claim.scrubbing_errors?.length"
                        class="px-6 py-2.5 bg-primary-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-600/20 hover:shadow-xl transition-all flex items-center gap-2 disabled:opacity-50"
                    >
                        <Send class="w-4 h-4" /> Submit Claim
                    </button>
                </div>
            </div>
        </div>
    </p-dialog>
</template>

<style scoped>
@reference "tailwindcss/theme";
.claim-form-dialog :deep(.p-dialog-content) {
    @apply bg-slate-50;
}
</style>
