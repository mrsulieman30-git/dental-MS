<script setup>
import { ref, computed, watch, inject, nextTick } from 'vue';
import { useTreatmentPlanStore } from '../../../stores/treatmentPlan.store';
import { useInsuranceStore } from '../../../stores/insurance.store';
import { formatCurrency } from '../../../utils/formatters';
import CdtCodeSearch from './CdtCodeSearch.vue';
import MiniOdontogram from './MiniOdontogram.vue';
import SurfaceSelector from './SurfaceSelector.vue';
import draggable from 'vuedraggable';
import {
    Plus, Trash2, GripVertical, ChevronDown, ChevronUp,
    Save, Send, Copy, StickyNote, X, Layers
} from 'lucide-vue-next';

const props = defineProps({
    visible: Boolean,
    patientId: [Number, String],
    planId: { type: [Number, String], default: null },
    patientInsurance: { type: Object, default: null }
});
const emit = defineEmits(['update:visible', 'saved']);

const store = useTreatmentPlanStore();
const insStore = useInsuranceStore();

const planName = ref('');
const phaseNames = ref(['Phase 1']);
const phases = ref({ 1: [] });
const activePhase = ref(1);
const notes = ref('');
const expandedNotes = ref({});
const showOdontogram = ref(null);
const alternatives = ref([]);
const activeAlt = ref(0);
const saving = ref(false);
const planId = ref(props.planId);

// Insurance data for auto-calculation
const coveredPercentages = computed(() => props.patientInsurance?.covered_percentages || {
    preventive: 100, basic: 80, major: 50, orthodontics: 50, implants: 0
});
const deductibleRemaining = computed(() => {
    const ins = props.patientInsurance;
    if (!ins) return 0;
    return Math.max(0, (ins.deductible_individual || 0) - (ins.deductible_met || 0));
});

// Load existing plan
watch(() => props.planId, async (id) => {
    if (id) {
        const plan = await store.fetchPlan(id);
        planName.value = plan.name;
        phaseNames.value = plan.phase_names || ['Phase 1'];
        notes.value = plan.notes || '';
        // Group procedures by phase
        const grouped = {};
        (plan.procedures || []).forEach(p => {
            if (!grouped[p.phase]) grouped[p.phase] = [];
            grouped[p.phase].push({ ...p, _expanded: false });
        });
        phases.value = grouped;
        if (!Object.keys(grouped).length) phases.value = { 1: [] };
    }
}, { immediate: true });

// Phase management
const addPhase = () => {
    const nextNum = Math.max(...Object.keys(phases.value).map(Number), 0) + 1;
    phases.value[nextNum] = [];
    phaseNames.value.push(`Phase ${nextNum}`);
    activePhase.value = nextNum;
};

const removePhase = (phaseNum) => {
    if (Object.keys(phases.value).length <= 1) return;
    delete phases.value[phaseNum];
    phaseNames.value.splice(phaseNum - 1, 1);
    activePhase.value = Number(Object.keys(phases.value)[0]);
};

// Procedure management
const addProcedure = (phaseNum) => {
    if (!phases.value[phaseNum]) phases.value[phaseNum] = [];
    phases.value[phaseNum].push({
        _temp_id: Date.now(),
        cdt_code_id: null,
        cdt_code: null,
        procedure_name: '',
        tooth_number: null,
        surfaces: [],
        priority: 'routine',
        fee: 0,
        insurance_estimated: 0,
        patient_portion: 0,
        notes: '',
        phase: phaseNum,
        sequence_order: phases.value[phaseNum].length,
        _expanded: false,
    });
};

const removeProcedure = (phaseNum, index) => {
    phases.value[phaseNum].splice(index, 1);
};

const onCdtSelect = (proc, selected) => {
    proc.cdt_code_id = selected.id;
    proc.cdt_code = selected;
    proc.procedure_name = selected.short_description;
    if (selected.default_fee) {
        proc.fee = Number(selected.default_fee);
        recalcInsurance(proc, selected.category);
    }
};

const recalcInsurance = (proc, category = null) => {
    const cat = category || proc.cdt_code?.category || 'basic';
    const result = store.calculateInsuranceEstimate(
        proc.fee, cat, coveredPercentages.value, deductibleRemaining.value
    );
    proc.insurance_estimated = result.insuranceEstimate;
    proc.patient_portion = result.patientPortion;
};

const onFeeChange = (proc) => {
    recalcInsurance(proc);
};

// Totals
const phaseTotals = computed(() => {
    const totals = {};
    for (const [phaseNum, procs] of Object.entries(phases.value)) {
        totals[phaseNum] = {
            fee: procs.reduce((sum, p) => sum + Number(p.fee || 0), 0),
            insurance: procs.reduce((sum, p) => sum + Number(p.insurance_estimated || 0), 0),
            patient: procs.reduce((sum, p) => sum + Number(p.patient_portion || 0), 0),
        };
    }
    return totals;
});

const grandTotals = computed(() => {
    return Object.values(phaseTotals.value).reduce((acc, t) => ({
        fee: acc.fee + t.fee,
        insurance: acc.insurance + t.insurance,
        patient: acc.patient + t.patient,
    }), { fee: 0, insurance: 0, patient: 0 });
});

// Save
const saveDraft = async () => {
    saving.value = true;
    try {
        const procedures = [];
        for (const [phaseNum, procs] of Object.entries(phases.value)) {
            procs.forEach((p, idx) => {
                const proc = {
                    phase: Number(phaseNum),
                    sequence_order: idx,
                    cdt_code_id: p.cdt_code_id,
                    procedure_name: p.procedure_name,
                    tooth_number: p.tooth_number,
                    surfaces: p.surfaces,
                    priority: p.priority,
                    fee: p.fee,
                    insurance_estimated: p.insurance_estimated,
                    patient_portion: p.patient_portion,
                    notes: p.notes,
                };
                if (p.id) proc.id = p.id;
                procedures.push(proc);
            });
        }

        if (planId.value) {
            await store.savePlan(planId.value, {
                name: planName.value,
                notes: notes.value,
                phase_names: phaseNames.value,
                procedures,
            });
        } else {
            const created = await store.createPlan(props.patientId, {
                name: planName.value,
                notes: notes.value,
                phase_names: phaseNames.value,
            });
            planId.value = created.id;
            // Now save procedures
            await store.savePlan(created.id, { procedures });
        }
        emit('saved');
    } finally { saving.value = false; }
};

const presentPlan = async () => {
    await saveDraft();
    if (planId.value) {
        await store.updateStatus(planId.value, 'presented');
        emit('saved');
        emit('update:visible', false);
    }
};

const addAlternative = () => {
    alternatives.value.push({
        name: `Plan ${String.fromCharCode(66 + alternatives.value.length)}`,
        phases: { 1: [] },
        phaseNames: ['Phase 1'],
    });
    activeAlt.value = alternatives.value.length;
};

const toggleNotes = (procId) => {
    expandedNotes.value[procId] = !expandedNotes.value[procId];
};

const close = () => emit('update:visible', false);

const priorityOptions = [
    { label: 'Urgent', value: 'immediate' },
    { label: 'Soon', value: 'soon' },
    { label: 'Routine', value: 'routine' },
    { label: 'Elective', value: 'elective' },
];

const prioritySeverity = (p) => {
    const map = { immediate: 'danger', soon: 'warn', routine: 'info', elective: 'secondary' };
    return map[p] || 'info';
};
</script>

<template>
    <p-dialog
        :visible="visible"
        @update:visible="close"
        modal
        maximizable
        :style="{ width: '95vw', maxWidth: '1600px' }"
        :contentStyle="{ padding: 0 }"
        :header="planId ? 'Edit Treatment Plan' : 'New Treatment Plan'"
        class="tp-editor-dialog"
    >
        <div class="flex flex-col h-[80vh]">
            <!-- Header bar -->
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center gap-4 flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-1">Plan Name</label>
                    <input
                        v-model="planName"
                        class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none"
                        placeholder="e.g., Comprehensive Restoration Plan"
                    />
                </div>

                <!-- Alternative plan tabs -->
                <div class="flex items-center gap-2">
                    <button
                        :class="['px-3 py-2 rounded-lg text-xs font-bold transition-all', activeAlt === 0 ? 'bg-primary-500 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100']"
                        @click="activeAlt = 0"
                    >Plan A</button>
                    <button
                        v-for="(alt, i) in alternatives" :key="i"
                        :class="['px-3 py-2 rounded-lg text-xs font-bold transition-all', activeAlt === i+1 ? 'bg-primary-500 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100']"
                        @click="activeAlt = i+1"
                    >{{ alt.name }}</button>
                    <button @click="addAlternative" class="p-2 text-slate-400 hover:text-primary-500 hover:bg-primary-50 rounded-lg transition-all" title="Add Alternative Plan">
                        <Plus class="w-4 h-4" />
                    </button>
                </div>

                <!-- Grand totals -->
                <div class="flex items-center gap-4 bg-white dark:bg-slate-800 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="text-center">
                        <p class="text-[9px] uppercase text-slate-400 font-bold">Total Fee</p>
                        <p class="text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(grandTotals.fee) }}</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-center">
                        <p class="text-[9px] uppercase text-slate-400 font-bold">Insurance</p>
                        <p class="text-sm font-black text-emerald-600">{{ formatCurrency(grandTotals.insurance) }}</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-center">
                        <p class="text-[9px] uppercase text-slate-400 font-bold">Patient</p>
                        <p class="text-sm font-black text-primary-600">{{ formatCurrency(grandTotals.patient) }}</p>
                    </div>
                </div>
            </div>

            <!-- Scrollable content -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Phase tabs -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button
                        v-for="(name, idx) in phaseNames" :key="idx"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all',
                            activePhase === idx+1
                                ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/20'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                        ]"
                        @click="activePhase = idx+1"
                    >
                        <span>{{ name }}</span>
                        <span v-if="phaseTotals[idx+1]" class="ml-2 text-[10px] opacity-80">
                            ({{ formatCurrency(phaseTotals[idx+1]?.fee || 0) }})
                        </span>
                    </button>
                    <button @click="addPhase" class="p-2 text-primary-500 hover:bg-primary-50 rounded-xl transition-all" title="Add Phase">
                        <Plus class="w-4 h-4" />
                    </button>
                </div>

                <!-- Phase rename input -->
                <div class="flex items-center gap-3">
                    <Layers class="w-4 h-4 text-slate-400" />
                    <input
                        v-model="phaseNames[activePhase - 1]"
                        class="px-3 py-1.5 bg-transparent border-b border-dashed border-slate-300 text-sm font-bold focus:border-primary-500 outline-none"
                        placeholder="Phase name..."
                    />
                    <button v-if="Object.keys(phases).length > 1" @click="removePhase(activePhase)" class="text-red-400 hover:text-red-600 text-xs font-bold">
                        Remove Phase
                    </button>
                </div>

                <!-- Procedure table -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="grid grid-cols-[40px_minmax(120px,1fr)_minmax(150px,2fr)_80px_100px_80px_100px_100px_100px_40px] gap-0 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 px-3 py-2 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                        <div></div>
                        <div>CDT Code</div>
                        <div>Procedure</div>
                        <div>Tooth</div>
                        <div>Surfaces</div>
                        <div>Priority</div>
                        <div class="text-right">Fee</div>
                        <div class="text-right">Ins. Est.</div>
                        <div class="text-right">Patient</div>
                        <div></div>
                    </div>

                    <draggable
                        :list="phases[activePhase] || []"
                        group="procedures"
                        item-key="_temp_id"
                        handle=".drag-handle"
                        ghost-class="opacity-30"
                        animation="200"
                    >
                        <template #item="{ element: proc, index }">
                            <div class="border-b border-slate-100 dark:border-slate-800 last:border-0">
                                <div class="grid grid-cols-[40px_minmax(120px,1fr)_minmax(150px,2fr)_80px_100px_80px_100px_100px_100px_40px] gap-0 px-3 py-3 items-center hover:bg-slate-50/50 transition-colors">
                                    <div class="drag-handle cursor-grab text-slate-300 hover:text-slate-500">
                                        <GripVertical class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <CdtCodeSearch
                                            :modelValue="proc.cdt_code"
                                            @select="(s) => onCdtSelect(proc, s)"
                                        />
                                    </div>
                                    <div>
                                        <input v-model="proc.procedure_name" class="w-full px-2 py-1 text-sm bg-transparent border-b border-transparent hover:border-slate-300 focus:border-primary-500 outline-none font-medium" placeholder="Procedure name" />
                                    </div>
                                    <div>
                                        <button
                                            @click="showOdontogram = showOdontogram === proc ? null : proc"
                                            :class="['px-2 py-1 rounded text-xs font-bold transition-all', proc.tooth_number ? 'bg-primary-50 text-primary-600' : 'bg-slate-100 text-slate-400']"
                                        >
                                            {{ proc.tooth_number ? '#' + proc.tooth_number : '—' }}
                                        </button>
                                    </div>
                                    <div>
                                        <div class="flex gap-0.5">
                                            <span v-for="s in (proc.surfaces || [])" :key="s" class="text-[10px] font-bold bg-primary-100 text-primary-600 px-1 rounded">{{ s }}</span>
                                            <button v-if="!(proc.surfaces?.length)" class="text-[10px] text-slate-400">—</button>
                                        </div>
                                    </div>
                                    <div>
                                        <select v-model="proc.priority" class="text-[10px] font-bold bg-transparent border-0 outline-none cursor-pointer p-0">
                                            <option v-for="opt in priorityOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                        </select>
                                    </div>
                                    <div class="text-right">
                                        <input
                                            v-model.number="proc.fee"
                                            @change="onFeeChange(proc)"
                                            type="number" step="0.01" min="0"
                                            class="w-full text-right text-sm font-bold bg-transparent border-b border-transparent hover:border-slate-300 focus:border-primary-500 outline-none"
                                        />
                                    </div>
                                    <div class="text-right text-sm font-medium text-emerald-600">
                                        {{ formatCurrency(proc.insurance_estimated) }}
                                    </div>
                                    <div class="text-right">
                                        <input
                                            v-model.number="proc.patient_portion"
                                            type="number" step="0.01" min="0"
                                            class="w-full text-right text-sm font-bold text-primary-600 bg-transparent border-b border-transparent hover:border-slate-300 focus:border-primary-500 outline-none"
                                        />
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="toggleNotes(proc._temp_id || proc.id)" class="text-slate-300 hover:text-amber-500 transition-colors" title="Notes">
                                            <StickyNote class="w-3.5 h-3.5" />
                                        </button>
                                        <button @click="removeProcedure(activePhase, index)" class="text-slate-300 hover:text-red-500 transition-colors" title="Remove">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Inline odontogram picker -->
                                <div v-if="showOdontogram === proc" class="px-12 py-3 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 flex items-start gap-6">
                                    <MiniOdontogram v-model="proc.tooth_number" />
                                    <SurfaceSelector v-model="proc.surfaces" />
                                </div>

                                <!-- Inline notes -->
                                <div v-if="expandedNotes[proc._temp_id || proc.id]" class="px-12 py-3 bg-amber-50/50 border-t border-amber-100">
                                    <textarea
                                        v-model="proc.notes"
                                        rows="2"
                                        class="w-full px-3 py-2 text-sm bg-white border border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-300 outline-none resize-none"
                                        placeholder="Procedure notes..."
                                    ></textarea>
                                </div>
                            </div>
                        </template>
                    </draggable>

                    <!-- Add procedure button -->
                    <button
                        @click="addProcedure(activePhase)"
                        class="w-full py-4 text-sm font-bold text-primary-500 hover:bg-primary-50 transition-all flex items-center justify-center gap-2"
                    >
                        <Plus class="w-4 h-4" />
                        Add Procedure
                    </button>

                    <!-- Phase totals footer -->
                    <div v-if="phaseTotals[activePhase]" class="grid grid-cols-[40px_minmax(120px,1fr)_minmax(150px,2fr)_80px_100px_80px_100px_100px_100px_40px] gap-0 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 px-3 py-3 text-sm font-black">
                        <div></div>
                        <div></div>
                        <div class="text-slate-500">{{ phaseNames[activePhase - 1] }} Subtotal</div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div class="text-right">{{ formatCurrency(phaseTotals[activePhase]?.fee) }}</div>
                        <div class="text-right text-emerald-600">{{ formatCurrency(phaseTotals[activePhase]?.insurance) }}</div>
                        <div class="text-right text-primary-600">{{ formatCurrency(phaseTotals[activePhase]?.patient) }}</div>
                        <div></div>
                    </div>
                </div>
            </div>

            <!-- Footer actions -->
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <button @click="close" class="px-4 py-2 text-sm font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-all">
                    Cancel
                </button>
                <div class="flex items-center gap-3">
                    <button
                        @click="saveDraft"
                        :disabled="saving"
                        class="px-6 py-2.5 bg-white border border-slate-200 text-sm font-bold text-slate-700 rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        Save Draft
                    </button>
                    <button
                        @click="presentPlan"
                        :disabled="saving"
                        class="px-6 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-sm font-bold rounded-xl hover:shadow-lg hover:shadow-primary-500/25 transition-all flex items-center gap-2 disabled:opacity-50"
                    >
                        <Send class="w-4 h-4" />
                        Present to Patient
                    </button>
                </div>
            </div>
        </div>
    </p-dialog>
</template>

<style scoped>
@reference "tailwindcss/theme";
:deep(.tp-editor-dialog .p-dialog-content) {
    @apply p-0;
}
</style>
