<script setup>
import { computed } from 'vue';
import { getToothLabel } from '../odontogram/toothPaths.js';

const props = defineProps({
  teeth: { type: Array, required: true }, // Array of tooth numbers for this arch
  measurements: { type: Object, required: true },
  notation: { type: String, default: 'universal' },
  readonly: { type: Boolean, default: false },
  compareData: { type: Object, default: null },
  archLabel: { type: String, default: 'Upper' },
});
const emit = defineEmits(['update-measurement']);

function getVal(tooth, surface, field) {
  const key = `${tooth}_${surface}`;
  return props.measurements[key]?.[field] ?? null;
}

function setVal(tooth, surface, field, value) {
  emit('update-measurement', tooth, surface, field, value);
}

function probeClass(val) {
  if (val == null) return '';
  if (val <= 3) return 'probe-green';
  if (val <= 5) return 'probe-yellow';
  return 'probe-red';
}

function cal(tooth, surface, pos) {
  const probe = getVal(tooth, surface, `pos${pos}_probe`);
  const recession = getVal(tooth, surface, `pos${pos}_recession`);
  if (probe == null) return '-';
  return (probe || 0) + (recession || 0);
}

function toggleBool(tooth, surface, field) {
  const key = `${tooth}_${surface}`;
  const current = props.measurements[key]?.[field] || false;
  emit('update-measurement', tooth, surface, field, !current);
}

function compareArrow(tooth, surface, pos) {
  if (!props.compareData) return null;
  const key = `${tooth}_${surface}`;
  const oldVal = props.compareData[key]?.[`pos${pos}_probe`];
  const newVal = getVal(tooth, surface, `pos${pos}_probe`);
  if (oldVal == null || newVal == null) return null;
  if (newVal < oldVal) return 'improved';
  if (newVal > oldVal) return 'worsened';
  return 'same';
}

const furcationOptions = ['none', 'I', 'II', 'III'];
function cycleFurcation(tooth, surface) {
  const key = `${tooth}_${surface}`;
  const current = props.measurements[key]?.furcation_class || 'none';
  const idx = furcationOptions.indexOf(current);
  const next = furcationOptions[(idx + 1) % furcationOptions.length];
  emit('update-measurement', tooth, surface, 'furcation_class', next);
}

// Generate unique IDs for tab order
function inputId(tooth, surface, pos, type='probe') {
  return `perio-${type}-${tooth}-${surface}-${pos}`;
}

// Get next input in tab order
function nextInput(tooth, surface, pos) {
  // Within same tooth+surface: pos 1→2→3
  if (pos < 3) return inputId(tooth, surface, pos + 1);
  // Move to next tooth
  const idx = props.teeth.indexOf(tooth);
  if (surface === 'buccal') {
    // After buccal pos3, go to next tooth buccal pos1
    if (idx < props.teeth.length - 1) return inputId(props.teeth[idx + 1], 'buccal', 1);
    // After last buccal, go to first lingual
    return inputId(props.teeth[0], 'lingual', 1);
  }
  // After lingual pos3, go to next tooth lingual
  if (idx < props.teeth.length - 1) return inputId(props.teeth[idx + 1], 'lingual', 1);
  return null;
}

function onProbeInput(tooth, surface, pos, event) {
  const val = parseInt(event.target.value);
  if (!isNaN(val) && val >= 0 && val <= 12) {
    setVal(tooth, surface, `pos${pos}_probe`, val);
    // Auto-advance
    const next = nextInput(tooth, surface, pos);
    if (next) {
      const el = document.getElementById(next);
      if (el) setTimeout(() => el.focus(), 10);
    }
  }
}
</script>

<template>
  <div class="perio-grid-wrapper">
    <div class="arch-header">{{ archLabel }} Arch</div>
    <div class="perio-grid-scroll">
      <table class="perio-table">
        <thead>
          <tr>
            <th class="row-label-th"></th>
            <th v-for="t in teeth" :key="t" class="tooth-header">
              {{ getToothLabel(t, notation) }}
            </th>
          </tr>
        </thead>
        <tbody>
          <!-- Buccal Probing -->
          <tr>
            <td class="row-label">Buccal PD</td>
            <td v-for="t in teeth" :key="'bp-'+t" class="probe-cell-group">
              <div class="probe-inputs">
                <input v-for="pos in 3" :key="pos"
                  :id="inputId(t,'buccal',pos)"
                  type="number" min="0" max="12"
                  :value="getVal(t,'buccal',`pos${pos}_probe`)"
                  @input="onProbeInput(t,'buccal',pos,$event)"
                  :class="['probe-input', probeClass(getVal(t,'buccal',`pos${pos}_probe`))]"
                  :disabled="readonly"
                  :aria-label="`Tooth ${t} buccal position ${pos} probe depth`"
                />
              </div>
              <!-- Compare arrows -->
              <div v-if="compareData" class="compare-arrows">
                <span v-for="pos in 3" :key="'ca-'+pos" :class="['compare-arrow', compareArrow(t,'buccal',pos)]">
                  {{ compareArrow(t,'buccal',pos) === 'improved' ? '↓' : compareArrow(t,'buccal',pos) === 'worsened' ? '↑' : '' }}
                </span>
              </div>
            </td>
          </tr>

          <!-- Buccal Recession -->
          <tr>
            <td class="row-label">Recession</td>
            <td v-for="t in teeth" :key="'br-'+t" class="probe-cell-group">
              <div class="probe-inputs">
                <input v-for="pos in 3" :key="pos"
                  type="number" min="0" max="10"
                  :value="getVal(t,'buccal',`pos${pos}_recession`)"
                  @input="setVal(t,'buccal',`pos${pos}_recession`, parseInt($event.target.value)||0)"
                  class="probe-input recession-input"
                  :disabled="readonly"
                />
              </div>
            </td>
          </tr>

          <!-- Buccal CAL (auto-calculated) -->
          <tr>
            <td class="row-label">CAL</td>
            <td v-for="t in teeth" :key="'bc-'+t" class="probe-cell-group">
              <div class="probe-inputs">
                <span v-for="pos in 3" :key="pos" class="cal-display">{{ cal(t,'buccal',pos) }}</span>
              </div>
            </td>
          </tr>

          <!-- Bleeding & Suppuration -->
          <tr>
            <td class="row-label">BOP / Sup</td>
            <td v-for="t in teeth" :key="'bb-'+t" class="probe-cell-group">
              <div class="dot-row">
                <button v-for="pos in 3" :key="'bl-'+pos"
                  @click="toggleBool(t,'buccal',`pos${pos}_bleeding`)"
                  :class="['bleed-dot', { active: getVal(t,'buccal',`pos${pos}_bleeding`) }]"
                  :disabled="readonly"
                  :title="'Bleeding site '+pos"
                />
              </div>
              <div class="dot-row">
                <button v-for="pos in 3" :key="'sp-'+pos"
                  @click="toggleBool(t,'buccal',`pos${pos}_suppuration`)"
                  :class="['sup-dot', { active: getVal(t,'buccal',`pos${pos}_suppuration`) }]"
                  :disabled="readonly"
                  :title="'Suppuration site '+pos"
                />
              </div>
            </td>
          </tr>

          <!-- Separator -->
          <tr class="section-sep"><td :colspan="teeth.length + 1"></td></tr>

          <!-- Lingual Probing -->
          <tr>
            <td class="row-label">Lingual PD</td>
            <td v-for="t in teeth" :key="'lp-'+t" class="probe-cell-group">
              <div class="probe-inputs">
                <input v-for="pos in 3" :key="pos"
                  :id="inputId(t,'lingual',pos)"
                  type="number" min="0" max="12"
                  :value="getVal(t,'lingual',`pos${pos}_probe`)"
                  @input="onProbeInput(t,'lingual',pos,$event)"
                  :class="['probe-input', probeClass(getVal(t,'lingual',`pos${pos}_probe`))]"
                  :disabled="readonly"
                />
              </div>
            </td>
          </tr>

          <!-- Lingual Recession -->
          <tr>
            <td class="row-label">Recession</td>
            <td v-for="t in teeth" :key="'lr-'+t" class="probe-cell-group">
              <div class="probe-inputs">
                <input v-for="pos in 3" :key="pos"
                  type="number" min="0" max="10"
                  :value="getVal(t,'lingual',`pos${pos}_recession`)"
                  @input="setVal(t,'lingual',`pos${pos}_recession`, parseInt($event.target.value)||0)"
                  class="probe-input recession-input"
                  :disabled="readonly"
                />
              </div>
            </td>
          </tr>

          <!-- Lingual CAL -->
          <tr>
            <td class="row-label">CAL</td>
            <td v-for="t in teeth" :key="'lc-'+t" class="probe-cell-group">
              <div class="probe-inputs">
                <span v-for="pos in 3" :key="pos" class="cal-display">{{ cal(t,'lingual',pos) }}</span>
              </div>
            </td>
          </tr>

          <!-- Separator -->
          <tr class="section-sep"><td :colspan="teeth.length + 1"></td></tr>

          <!-- Per-tooth: Furcation, Mobility, Plaque, Calculus -->
          <tr>
            <td class="row-label">Furcation</td>
            <td v-for="t in teeth" :key="'fur-'+t" class="text-center">
              <button @click="cycleFurcation(t,'buccal')" class="furc-btn" :disabled="readonly">
                {{ getVal(t,'buccal','furcation_class') === 'none' ? '-' : getVal(t,'buccal','furcation_class') }}
              </button>
            </td>
          </tr>
          <tr>
            <td class="row-label">Mobility</td>
            <td v-for="t in teeth" :key="'mob-'+t" class="text-center">
              <input type="number" min="0" max="3"
                :value="getVal(t,'buccal','mobility_grade')"
                @input="setVal(t,'buccal','mobility_grade', parseInt($event.target.value)||0)"
                class="mob-input" :disabled="readonly"
              />
            </td>
          </tr>
          <tr>
            <td class="row-label">Plaque</td>
            <td v-for="t in teeth" :key="'plq-'+t" class="text-center">
              <input type="checkbox" :checked="getVal(t,'buccal','plaque_present')"
                @change="toggleBool(t,'buccal','plaque_present')" :disabled="readonly" class="perio-check" />
            </td>
          </tr>
          <tr>
            <td class="row-label">Calculus</td>
            <td v-for="t in teeth" :key="'calc-'+t" class="text-center">
              <input type="checkbox" :checked="getVal(t,'buccal','calculus_present')"
                @change="toggleBool(t,'buccal','calculus_present')" :disabled="readonly" class="perio-check" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.perio-grid-wrapper { @apply border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden; }
.arch-header { @apply px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-500 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700; }
.perio-grid-scroll { @apply overflow-x-auto; }
.perio-table { @apply w-full text-xs border-collapse; min-width: 900px; }
.perio-table th, .perio-table td { @apply px-1 py-1 text-center; }
.tooth-header { @apply text-[10px] font-black text-slate-500 py-2 min-w-[48px]; }
.row-label-th { @apply w-20; }
.row-label { @apply text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right pr-2 whitespace-nowrap w-20; }
.probe-cell-group { @apply px-0.5; }
.probe-inputs { @apply flex gap-0.5 justify-center; }
.probe-input {
  @apply w-7 h-7 text-center text-xs font-bold rounded border border-slate-200 outline-none
         focus:ring-2 focus:ring-blue-300 transition-all;
  -moz-appearance: textfield;
}
.probe-input::-webkit-outer-spin-button,
.probe-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.probe-green { @apply border-green-400 bg-green-50 text-green-700; }
.probe-yellow { @apply border-yellow-400 bg-yellow-50 text-yellow-700; }
.probe-red { @apply border-red-400 bg-red-50 text-red-700 animate-pulse; }
.recession-input { @apply bg-slate-50 text-slate-600; }
.cal-display { @apply w-7 h-7 flex items-center justify-center text-[10px] font-bold text-slate-400 bg-slate-100 rounded; }
.dot-row { @apply flex gap-1 justify-center py-0.5; }
.bleed-dot { @apply w-3.5 h-3.5 rounded-full border border-slate-300 bg-white transition-colors cursor-pointer; }
.bleed-dot.active { @apply bg-red-500 border-red-500; }
.sup-dot { @apply w-3.5 h-3.5 rounded-full border border-slate-300 bg-white transition-colors cursor-pointer; }
.sup-dot.active { @apply bg-yellow-400 border-yellow-400; }
.furc-btn { @apply w-7 h-7 rounded border border-slate-200 text-[10px] font-bold text-slate-500 hover:bg-slate-100 transition-colors; }
.mob-input { @apply w-7 h-7 text-center text-xs font-bold rounded border border-slate-200 outline-none; -moz-appearance: textfield; }
.mob-input::-webkit-outer-spin-button, .mob-input::-webkit-inner-spin-button { -webkit-appearance: none; }
.perio-check { @apply w-4 h-4 accent-blue-500 cursor-pointer; }
.section-sep td { @apply h-2 bg-slate-50 dark:bg-slate-800/30 border-t border-b border-slate-200 dark:border-slate-700; }
.compare-arrows { @apply flex gap-0.5 justify-center; }
.compare-arrow { @apply text-[10px] font-bold w-7 text-center; }
.compare-arrow.improved { @apply text-green-500; }
.compare-arrow.worsened { @apply text-red-500; }
</style>
