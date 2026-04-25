<script setup>
import { ref, inject, onMounted, computed, watch } from 'vue';
import { useClinicalStore } from '../../../stores/clinical.store';
import OdontogramChart from '../../../components/clinical/odontogram/OdontogramChart.vue';
import ToothDetailPanel from '../../../components/clinical/odontogram/ToothDetailPanel.vue';
import ConditionForm from '../../../components/clinical/forms/ConditionForm.vue';
import RestorationForm from '../../../components/clinical/forms/RestorationForm.vue';
import { legendItems } from '../../../components/clinical/odontogram/toothColors.js';
import { Printer, Settings2, ToggleLeft, Info, History } from 'lucide-vue-next';

const patient = inject('patient');
const store = useClinicalStore();

const showConditionForm = ref(false);
const showRestorationForm = ref(false);
const showLegend = ref(false);

const notation = computed(() => store.notation);
const selectedTooth = computed(() => store.selectedTooth);
const chartData = computed(() => store.chartData);

const notationOptions = [
  { label: 'Universal (1-32)', value: 'universal' },
  { label: 'FDI', value: 'fdi' },
  { label: 'Palmer', value: 'palmer' },
];

onMounted(async () => {
  if (patient.value?.id) {
    await store.fetchChart(patient.value.id);
  }
});

watch(() => patient.value?.id, async (id) => {
  if (id) await store.fetchChart(id);
});

function onToothSelected(toothNum) {
  store.selectTooth(toothNum);
}

function onSurfaceSelected({ tooth, surface }) {
  store.selectSurface(tooth, surface);
}

function openConditionForm() { showConditionForm.value = true; }
function openRestorationForm() { showRestorationForm.value = true; }

function onFormSaved() {
  if (patient.value?.id) store.fetchChart(patient.value.id);
}

function printChart() { window.print(); }
</script>

<template>
  <div class="chart-layout">
    <!-- Toolbar -->
    <div class="chart-toolbar">
      <div class="flex items-center gap-3">
        <h2 class="text-sm font-black text-slate-700 dark:text-slate-200">Dental Chart</h2>
        <p-select
          :modelValue="notation" @update:modelValue="store.setNotation($event)"
          :options="notationOptions" optionLabel="label" optionValue="value"
          class="w-44" size="small"
        />
      </div>
      <div class="flex items-center gap-2">
        <button @click="showLegend = !showLegend" class="toolbar-btn" :class="{ active: showLegend }">
          <Info class="w-4 h-4" /> <span>Legend</span>
        </button>
        <button @click="printChart" class="toolbar-btn">
          <Printer class="w-4 h-4" /> <span>Print</span>
        </button>
      </div>
    </div>

    <!-- Main content -->
    <div class="chart-content">
      <!-- Odontogram -->
      <div class="chart-left">
        <OdontogramChart
          :modelValue="chartData"
          mode="chart"
          :readonly="false"
          :notation="notation"
          :selectedTooth="selectedTooth"
          @tooth-selected="onToothSelected"
          @surface-selected="onSurfaceSelected"
        />

        <!-- Legend Card -->
        <div v-if="showLegend" class="legend-card">
          <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Chart Legend</h4>
          <div class="grid grid-cols-3 gap-2">
            <div v-for="item in legendItems" :key="item.key" class="flex items-center gap-2">
              <span class="w-4 h-4 rounded border" :style="{ background: item.fill || 'transparent', borderColor: item.stroke }"></span>
              <span class="text-[10px] text-slate-500">{{ item.label }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Context Panel -->
      <div class="chart-right">
        <div v-if="selectedTooth" class="context-panel">
          <ToothDetailPanel
            :toothNumber="selectedTooth"
            :conditions="chartData.conditions"
            :restorations="chartData.restorations"
            :implants="chartData.implants"
            :notation="notation"
            @add-condition="openConditionForm"
            @add-restoration="openRestorationForm"
            @propose-treatment="() => {}"
          />
        </div>
        <div v-else class="empty-context">
          <div class="text-center py-12">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 mx-auto mb-4 flex items-center justify-center">
              <Settings2 class="w-8 h-8 text-slate-300" />
            </div>
            <p class="text-sm font-bold text-slate-400">Select a tooth</p>
            <p class="text-xs text-slate-300 mt-1">Click on a tooth to view details and add findings</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Condition Form Dialog -->
    <ConditionForm
      v-model:visible="showConditionForm"
      :patientId="patient?.id"
      :toothNumber="selectedTooth"
      @saved="onFormSaved"
    />

    <!-- Restoration Form Dialog -->
    <RestorationForm
      v-model:visible="showRestorationForm"
      :patientId="patient?.id"
      :toothNumber="selectedTooth"
      @saved="onFormSaved"
    />
  </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.chart-layout { @apply flex flex-col h-full gap-4; }
.chart-toolbar { @apply flex items-center justify-between px-4 py-2 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200/50 dark:border-slate-700; }
.toolbar-btn { @apply flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all; }
.toolbar-btn.active { @apply bg-blue-50 text-blue-600; }
.chart-content { @apply flex gap-4 flex-1 min-h-0; }
.chart-left { @apply flex-1 min-w-0 space-y-4; }
.chart-right { @apply w-80 flex-shrink-0 overflow-y-auto; }
.context-panel { @apply bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm; }
.empty-context { @apply bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/50 dark:border-slate-700; }
.legend-card { @apply p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm; }

@media print {
  .chart-toolbar, .chart-right { display: none !important; }
  .chart-left { width: 100% !important; }
}
</style>