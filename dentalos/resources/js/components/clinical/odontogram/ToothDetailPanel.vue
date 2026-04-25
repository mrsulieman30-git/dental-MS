<script setup>
import { computed } from 'vue';
import { getToothLabel, getOcclusalLabel, getFacialLabel, getSurfacePolygons, getToothDimensions, polygonToSvgPoints } from './toothPaths.js';
import { computeSurfaceFill, defaultSurfaceColor } from './toothColors.js';
import { Plus, Wrench, ClipboardList } from 'lucide-vue-next';

const props = defineProps({
  toothNumber: { type: Number, required: true },
  conditions: { type: Array, default: () => [] },
  restorations: { type: Array, default: () => [] },
  implants: { type: Array, default: () => [] },
  notation: { type: String, default: 'universal' },
});

const emit = defineEmits(['add-condition', 'add-restoration', 'propose-treatment']);

const toothConditions = computed(() => props.conditions.filter(c => c.tooth_number === props.toothNumber));
const toothRestorations = computed(() => props.restorations.filter(r => r.tooth_number === props.toothNumber));
const toothImplants = computed(() => props.implants.filter(i => i.tooth_number === props.toothNumber));

const label = computed(() => getToothLabel(props.toothNumber, props.notation));
const dims = computed(() => getToothDimensions(props.toothNumber));
const surfaces = computed(() => getSurfacePolygons(dims.value.w * 3, dims.value.h * 3));

const surfaceLabels = computed(() => ({
  O: getOcclusalLabel(props.toothNumber),
  B: getFacialLabel(props.toothNumber),
  M: 'M', D: 'D', L: 'L',
}));

const surfaceFills = computed(() => {
  const fills = {};
  Object.keys(surfaces.value).forEach(s => {
    fills[s] = computeSurfaceFill(props.toothNumber, s, props.conditions, props.restorations);
  });
  return fills;
});

function statusColor(status) {
  const map = { existing:'info', proposed:'warning', completed:'success', declined:'danger', new:'info', in_progress:'warning' };
  return map[status] || 'secondary';
}
</script>

<template>
  <div class="tooth-detail-panel">
    <div class="panel-header">
      <h3 class="text-xl font-black text-slate-800 dark:text-white">Tooth #{{ label }}</h3>
      <div class="flex items-center gap-2 mt-2">
        <button @click="emit('add-condition')" class="detail-btn bg-red-50 text-red-600 hover:bg-red-100">
          <Plus class="w-3.5 h-3.5" /> Condition
        </button>
        <button @click="emit('add-restoration')" class="detail-btn bg-blue-50 text-blue-600 hover:bg-blue-100">
          <Wrench class="w-3.5 h-3.5" /> Restoration
        </button>
        <button @click="emit('propose-treatment')" class="detail-btn bg-green-50 text-green-600 hover:bg-green-100">
          <ClipboardList class="w-3.5 h-3.5" /> Treatment
        </button>
      </div>
    </div>

    <!-- Zoomed Tooth View -->
    <div class="zoomed-tooth">
      <svg viewBox="0 0 150 150" class="w-full max-w-[200px] mx-auto">
        <g transform="translate(10, 10)">
          <rect x="0" y="0" :width="dims.w * 3" :height="dims.h * 3" rx="6" ry="6" fill="#fff" stroke="#d1d5db" stroke-width="2"/>
          <polygon
            v-for="(poly, sKey) in surfaces" :key="sKey"
            :points="polygonToSvgPoints(poly)"
            :fill="surfaceFills[sKey]?.fill || defaultSurfaceColor.fill"
            :stroke="surfaceFills[sKey]?.stroke || '#d1d5db'"
            stroke-width="1.5"
          />
          <text
            v-for="(lbl, sKey) in surfaceLabels" :key="'lbl-'+sKey"
            :x="sKey==='O' ? dims.w*1.5 : sKey==='B' ? dims.w*1.5 : sKey==='L' ? dims.w*1.5 : sKey==='M' ? dims.w*0.4 : dims.w*2.6"
            :y="sKey==='O' ? dims.h*1.5+4 : sKey==='B' ? dims.h*0.4 : sKey==='L' ? dims.h*2.7 : dims.h*1.5+4"
            text-anchor="middle" class="surface-label-text"
          >{{ lbl }}</text>
        </g>
      </svg>
    </div>

    <!-- Conditions List -->
    <div v-if="toothConditions.length" class="detail-section">
      <h4 class="section-title">Conditions</h4>
      <div v-for="c in toothConditions" :key="c.id" class="detail-card">
        <div class="flex items-center justify-between">
          <span class="font-bold text-sm capitalize">{{ c.condition_type?.replace('_',' ') }}</span>
          <p-tag :value="c.status" :severity="statusColor(c.status)" class="text-[10px]" />
        </div>
        <div class="text-xs text-slate-500 mt-1">
          <span v-if="c.surfaces?.length">Surfaces: {{ c.surfaces.join(', ') }}</span>
          <span v-if="c.severity"> · {{ c.severity }}</span>
        </div>
        <p v-if="c.notes" class="text-xs text-slate-400 mt-1 line-clamp-2">{{ c.notes }}</p>
      </div>
    </div>

    <!-- Restorations List -->
    <div v-if="toothRestorations.length" class="detail-section">
      <h4 class="section-title">Restorations</h4>
      <div v-for="r in toothRestorations" :key="r.id" class="detail-card">
        <div class="flex items-center justify-between">
          <span class="font-bold text-sm capitalize">{{ r.restoration_type?.replace('_',' ') }}</span>
          <p-tag :value="r.status" :severity="statusColor(r.status)" class="text-[10px]" />
        </div>
        <div class="text-xs text-slate-500 mt-1">
          <span v-if="r.material" class="capitalize">{{ r.material }}</span>
          <span v-if="r.surfaces?.length"> · {{ r.surfaces.join(', ') }}</span>
          <span v-if="r.shade"> · Shade: {{ r.shade }}</span>
        </div>
      </div>
    </div>

    <!-- Implants -->
    <div v-if="toothImplants.length" class="detail-section">
      <h4 class="section-title">Implants</h4>
      <div v-for="imp in toothImplants" :key="imp.id" class="detail-card">
        <div class="text-sm font-bold">{{ imp.implant_system || 'Implant' }}</div>
        <div class="text-xs text-slate-500">
          {{ imp.implant_brand }} · {{ imp.fixture_diameter }}×{{ imp.fixture_length }}mm
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="!toothConditions.length && !toothRestorations.length && !toothImplants.length" class="text-center py-8 text-slate-400">
      <p class="text-sm">No findings for this tooth</p>
      <p class="text-xs mt-1">Click a button above to add</p>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.tooth-detail-panel { @apply space-y-4; }
.panel-header { @apply pb-3 border-b border-slate-100 dark:border-slate-800; }
.detail-btn { @apply flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all active:scale-95; }
.zoomed-tooth { @apply py-4 flex justify-center; }
.surface-label-text { font-size: 9px; fill: #64748b; font-weight: 700; pointer-events: none; }
.detail-section { @apply space-y-2; }
.section-title { @apply text-xs font-black uppercase tracking-widest text-slate-400 mb-1; }
.detail-card { @apply p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700; }
</style>
