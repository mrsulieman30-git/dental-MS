<script setup>
import { ref, inject, onMounted, computed, watch } from 'vue';
import { usePerioStore } from '../../../stores/perio.store';
import PerioGrid from '../../../components/clinical/perio/PerioGrid.vue';
import { getUpperTeeth, getLowerTeeth } from '../../../components/clinical/odontogram/toothPaths.js';
import { parsePerioVoiceInput, isSpeechRecognitionAvailable, createSpeechRecognition } from '../../../utils/perioVoiceParser.js';
import { formatDate } from '../../../utils/formatters.js';
import {
  Plus, Mic, MicOff, Save, FileText, Eye, ArrowLeftRight,
  AlertTriangle, CheckCircle, Activity
} from 'lucide-vue-next';

const patient = inject('patient');
const store = usePerioStore();

const voiceActive = ref(false);
const voiceSupported = ref(false);
const compareMode = ref(false);
const selectedCompareChart = ref(null);
let recognition = null;

const upperTeeth = getUpperTeeth();
const lowerTeeth = computed(() => {
  // Lower teeth visually: 32 down to 17
  const arr = [];
  for (let i = 32; i >= 17; i--) arr.push(i);
  return arr;
});

onMounted(async () => {
  voiceSupported.value = isSpeechRecognitionAvailable();
  if (patient.value?.id) await store.fetchCharts(patient.value.id);
});

watch(() => patient.value?.id, async (id) => {
  if (id) await store.fetchCharts(id);
});

function startNewChart() { store.startNewChart(); }

async function viewChart(chart) {
  await store.loadChart(chart.id);
  compareMode.value = false;
  selectedCompareChart.value = null;
}

async function saveChart() {
  try {
    await store.saveChart(patient.value.id);
  } catch (err) {
    console.error('Failed to save perio chart');
  }
}

function onUpdateMeasurement(tooth, surface, field, value) {
  store.updateMeasurement(tooth, surface, field, value);
}

function calculateAAP() { store.calculateAAP(); }

async function toggleCompare(chart) {
  if (compareMode.value && selectedCompareChart.value?.id === chart.id) {
    compareMode.value = false;
    selectedCompareChart.value = null;
  } else {
    if (!store.currentChart || store.currentChart.id === chart.id) {
      const baselineChart = store.latestChart?.id && store.latestChart.id !== chart.id
        ? store.latestChart
        : store.charts.find((item) => item.id !== chart.id);

      if (baselineChart?.id) {
        await store.loadChart(baselineChart.id);
      } else {
        await store.loadChart(chart.id);
        compareMode.value = false;
        selectedCompareChart.value = null;
        return;
      }
    }

    const detail = chart.measurementsMap ? chart : await store.fetchChartDetail(chart.id);
    compareMode.value = true;
    selectedCompareChart.value = detail;
    store.isCharting = true;
  }
}

function toggleVoice() {
  if (!voiceSupported.value) return;
  if (voiceActive.value) {
    recognition?.stop();
    voiceActive.value = false;
  } else {
    recognition = createSpeechRecognition(
      (text, isFinal) => {
        if (isFinal) {
          const parsed = parsePerioVoiceInput(text);
          if (parsed.toothNumber && parsed.surface && parsed.probes.length) {
            parsed.probes.forEach((val, idx) => {
              if (idx < 3) store.updateMeasurement(parsed.toothNumber, parsed.surface, `pos${idx+1}_probe`, val);
            });
            parsed.recession.forEach((val, idx) => {
              if (idx < 3) store.updateMeasurement(parsed.toothNumber, parsed.surface, `pos${idx+1}_recession`, val);
            });
            parsed.bleeding.forEach(pos => {
              if (pos >= 1 && pos <= 3) store.updateMeasurement(parsed.toothNumber, parsed.surface, `pos${pos}_bleeding`, true);
            });
          }
        }
      },
      () => { voiceActive.value = false; },
      (err) => { console.error('Voice error:', err); voiceActive.value = false; }
    );
    recognition?.start();
    voiceActive.value = true;
  }
}

function riskLevelColor(level) {
  return { low: 'success', moderate: 'warning', high: 'danger', very_high: 'danger' }[level] || 'secondary';
}

function printReport() { window.print(); }
</script>

<template>
  <div class="perio-tab">
    <!-- History List -->
    <div v-if="!store.isCharting" class="perio-history">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-black text-slate-800 dark:text-white">Periodontal Charts</h2>
        <p-button label="Start New Perio Chart" icon="pi pi-plus" @click="startNewChart" size="small" />
      </div>

      <div v-if="store.isLoading" class="space-y-3">
        <p-skeleton height="4rem" v-for="i in 3" :key="i" />
      </div>

      <div v-else-if="store.charts.length === 0" class="empty-state">
        <Activity class="w-12 h-12 text-slate-300 mx-auto mb-3" />
        <p class="text-sm text-slate-400">No periodontal charts found</p>
        <p class="text-xs text-slate-300 mt-1">Start a new chart to begin recording measurements</p>
      </div>

      <div v-else class="space-y-3">
        <div v-for="chart in store.charts" :key="chart.id" class="chart-history-card">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-bold text-sm text-slate-700 dark:text-white">{{ formatDate(chart.chart_date) }}</div>
              <div class="text-xs text-slate-400">Provider: {{ chart.provider?.full_name || 'Unknown' }}</div>
            </div>
            <div class="flex items-center gap-2">
              <p-tag v-if="chart.aap_stage" :value="`Stage ${chart.aap_stage}`" severity="info" class="text-[10px]" />
              <p-tag v-if="chart.aap_grade" :value="`Grade ${chart.aap_grade}`" severity="info" class="text-[10px]" />
              <p-tag v-if="chart.overall_risk_level" :value="chart.overall_risk_level" :severity="riskLevelColor(chart.overall_risk_level)" class="text-[10px] capitalize" />
            </div>
          </div>
          <div class="flex items-center gap-2 mt-3">
            <p-button label="View" icon="pi pi-eye" size="small" severity="secondary" text @click="viewChart(chart)" />
            <p-button label="Compare" icon="pi pi-arrows-h" size="small" severity="secondary" text @click="toggleCompare(chart)" />
          </div>
        </div>
      </div>
    </div>

    <!-- Active Charting -->
    <div v-else class="perio-charting">
      <!-- Charting toolbar -->
      <div class="charting-toolbar">
        <div class="flex items-center gap-3">
          <h2 class="text-sm font-black text-slate-700 dark:text-white">
            {{ store.currentChart ? 'Viewing Chart' : 'New Perio Chart' }}
          </h2>
          <button v-if="voiceSupported" @click="toggleVoice" :class="['voice-btn', { active: voiceActive }]">
            <Mic v-if="!voiceActive" class="w-4 h-4" /><MicOff v-else class="w-4 h-4" />
            <span>{{ voiceActive ? 'Stop' : 'Voice' }}</span>
          </button>
        </div>
        <div class="flex items-center gap-2">
          <p-button label="Calculate AAP" icon="pi pi-calculator" size="small" severity="info" outlined @click="calculateAAP" />
          <p-button label="Print Report" icon="pi pi-print" size="small" severity="secondary" outlined @click="printReport" />
          <p-button v-if="!store.currentChart" label="Save Chart" icon="pi pi-save" size="small" :loading="store.isSaving" @click="saveChart" />
          <p-button label="Back" icon="pi pi-arrow-left" size="small" severity="secondary" text @click="store.isCharting = false" />
        </div>
      </div>

      <!-- AAP Result -->
      <div v-if="store.aapResult" class="aap-result">
        <div class="flex items-center gap-4">
          <div class="text-center">
            <div class="text-2xl font-black text-blue-600">Stage {{ store.aapResult.stage }}</div>
            <div class="text-xs text-slate-400">AAP Stage</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-black text-purple-600">Grade {{ store.aapResult.grade }}</div>
            <div class="text-xs text-slate-400">AAP Grade</div>
          </div>
          <div class="text-center">
            <div class="text-lg font-bold capitalize" :class="{ 'text-green-600': store.aapResult.riskLevel === 'low', 'text-yellow-600': store.aapResult.riskLevel === 'moderate', 'text-red-600': store.aapResult.riskLevel === 'high' || store.aapResult.riskLevel === 'very_high' }">
              {{ store.aapResult.riskLevel?.replace('_', ' ') }}
            </div>
            <div class="text-xs text-slate-400">Risk Level</div>
          </div>
          <div class="text-center">
            <div class="text-lg font-bold text-slate-600">{{ store.aapResult.maxPD }}mm</div>
            <div class="text-xs text-slate-400">Max PD</div>
          </div>
          <div class="text-center">
            <div class="text-lg font-bold text-slate-600">{{ store.aapResult.bop }}%</div>
            <div class="text-xs text-slate-400">BOP</div>
          </div>
        </div>
      </div>

      <!-- Grids -->
      <div class="charting-grids">
        <PerioGrid
          :teeth="upperTeeth"
          :measurements="store.measurements"
          :readonly="!!store.currentChart"
          :compareData="compareMode ? selectedCompareChart?.measurementsMap : null"
          archLabel="Upper"
          @update-measurement="onUpdateMeasurement"
        />
        <PerioGrid
          :teeth="lowerTeeth"
          :measurements="store.measurements"
          :readonly="!!store.currentChart"
          :compareData="compareMode ? selectedCompareChart?.measurementsMap : null"
          archLabel="Lower"
          @update-measurement="onUpdateMeasurement"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.perio-tab { @apply space-y-4; }
.chart-history-card { @apply p-4 bg-white dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow; }
.empty-state { @apply text-center py-16; }
.charting-toolbar { @apply flex items-center justify-between px-4 py-2 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200/50 dark:border-slate-700; }
.voice-btn { @apply flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 hover:bg-slate-100 transition-all; }
.voice-btn.active { @apply bg-red-100 text-red-600 animate-pulse; }
.aap-result { @apply p-4 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl border border-blue-200/50 dark:border-blue-700/30; }
.charting-grids { @apply space-y-6; }

@media print {
  .charting-toolbar { display: none !important; }
}
</style>
