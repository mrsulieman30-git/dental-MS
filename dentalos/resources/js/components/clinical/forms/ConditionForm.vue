<script setup>
import { ref, watch } from 'vue';
import { useClinicalStore } from '../../../stores/clinical.store';

const props = defineProps({
  visible: Boolean,
  patientId: { type: [Number, String], required: true },
  toothNumber: { type: Number, default: null },
});
const emit = defineEmits(['update:visible', 'saved']);

const store = useClinicalStore();
const saving = ref(false);

const form = ref({
  tooth_number: props.toothNumber,
  condition_type: 'caries',
  surfaces: [],
  severity: 'watch',
  status: 'existing',
  cdt_code: '',
  notes: '',
});

const conditionTypes = [
  { label: 'Caries', value: 'caries' },
  { label: 'Fracture', value: 'fracture' },
  { label: 'Wear', value: 'wear' },
  { label: 'Sensitivity', value: 'sensitivity' },
  { label: 'Mobility', value: 'mobility' },
  { label: 'Peri-implantitis', value: 'peri_implantitis' },
  { label: 'Periodontal', value: 'perio' },
  { label: 'Other', value: 'other' },
];

const severities = [
  { label: 'Watch', value: 'watch' },
  { label: 'Initial', value: 'initial' },
  { label: 'Moderate', value: 'moderate' },
  { label: 'Severe', value: 'severe' },
];

const statuses = [
  { label: 'Existing', value: 'existing' },
  { label: 'Proposed', value: 'proposed' },
  { label: 'In Progress', value: 'in_progress' },
  { label: 'Completed', value: 'completed' },
  { label: 'Declined', value: 'declined' },
  { label: 'Monitored', value: 'monitored' },
];

const surfaceOptions = ['M', 'D', 'O', 'B', 'L'];

watch(() => props.toothNumber, (value) => {
  form.value.tooth_number = value;
}, { immediate: true });

function toggleSurface(s) {
  const idx = form.value.surfaces.indexOf(s);
  if (idx === -1) form.value.surfaces.push(s);
  else form.value.surfaces.splice(idx, 1);
}

async function save() {
  saving.value = true;
  try {
    await store.addCondition(props.patientId, form.value);
    emit('saved');
    emit('update:visible', false);
  } catch (err) {
    console.error('Failed to save condition', err);
  } finally {
    saving.value = false;
  }
}

function close() { emit('update:visible', false); }
</script>

<template>
  <p-dialog
    :visible="visible"
    @update:visible="close"
    header="Add Condition"
    :modal="true"
    :style="{ width: '480px' }"
    :closable="true"
    class="condition-dialog"
  >
    <div class="space-y-4">
      <!-- Tooth Number -->
      <div class="form-field">
        <label class="field-label">Tooth Number</label>
        <p-input-number v-model="form.tooth_number" :min="1" :max="32" class="w-full" />
      </div>

      <!-- Condition Type -->
      <div class="form-field">
        <label class="field-label">Condition Type</label>
        <p-select v-model="form.condition_type" :options="conditionTypes" optionLabel="label" optionValue="value" class="w-full" />
      </div>

      <!-- Surfaces -->
      <div class="form-field">
        <label class="field-label">Surfaces</label>
        <div class="flex gap-2">
          <button
            v-for="s in surfaceOptions" :key="s"
            @click="toggleSurface(s)"
            :class="['surface-btn', { active: form.surfaces.includes(s) }]"
          >{{ s }}</button>
        </div>
      </div>

      <!-- Severity -->
      <div class="form-field">
        <label class="field-label">Severity</label>
        <p-select v-model="form.severity" :options="severities" optionLabel="label" optionValue="value" class="w-full" />
      </div>

      <!-- Status -->
      <div class="form-field">
        <label class="field-label">Status</label>
        <p-select v-model="form.status" :options="statuses" optionLabel="label" optionValue="value" class="w-full" />
      </div>

      <!-- CDT Code -->
      <div class="form-field">
        <label class="field-label">CDT Code</label>
        <p-input-text v-model="form.cdt_code" placeholder="e.g. D0120" class="w-full" />
      </div>

      <!-- Notes -->
      <div class="form-field">
        <label class="field-label">Notes</label>
        <p-textarea v-model="form.notes" rows="3" class="w-full" placeholder="Additional notes..." />
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2">
        <p-button label="Cancel" severity="secondary" text @click="close" />
        <p-button label="Save Condition" icon="pi pi-check" :loading="saving" @click="save" />
      </div>
    </template>
  </p-dialog>
</template>

<style scoped>
@reference "tailwindcss/theme";
.form-field { @apply space-y-1; }
.field-label { @apply text-xs font-bold uppercase tracking-wider text-slate-500; }
.surface-btn {
  @apply w-10 h-10 rounded-lg border-2 border-slate-200 text-sm font-bold text-slate-500
         flex items-center justify-center transition-all cursor-pointer hover:border-blue-300;
}
.surface-btn.active { @apply border-blue-500 bg-blue-50 text-blue-700; }
</style>
