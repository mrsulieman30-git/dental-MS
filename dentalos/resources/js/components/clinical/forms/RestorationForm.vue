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
  restoration_type: 'filling',
  material: 'composite',
  surfaces: [],
  shade: '',
  placement_date: new Date(),
  status: 'new',
  notes: '',
  lab_case_id: null,
});

const restorationTypes = [
  { label: 'Filling', value: 'filling' }, { label: 'Crown', value: 'crown' },
  { label: 'Bridge', value: 'bridge' }, { label: 'Implant', value: 'implant' },
  { label: 'Veneer', value: 'veneer' }, { label: 'Onlay', value: 'onlay' },
  { label: 'Inlay', value: 'inlay' }, { label: 'Sealant', value: 'sealant' },
  { label: 'RCT', value: 'rct' }, { label: 'Post & Core', value: 'post_core' },
  { label: 'Buildup', value: 'buildup' }, { label: 'Partial Denture', value: 'denture_partial' },
  { label: 'Full Denture', value: 'denture_full' }, { label: 'Other', value: 'other' },
];

const materials = [
  { label: 'Composite', value: 'composite' }, { label: 'Amalgam', value: 'amalgam' },
  { label: 'Gold', value: 'gold' }, { label: 'Porcelain', value: 'porcelain' },
  { label: 'Zirconia', value: 'zirconia' }, { label: 'PFM', value: 'pfm' },
  { label: 'Acrylic', value: 'acrylic' }, { label: 'Other', value: 'other' },
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
    await store.addRestoration(props.patientId, {
      ...form.value,
      placement_date: form.value.placement_date?.toISOString?.()?.slice(0,10) || null,
    });
    emit('saved');
    emit('update:visible', false);
  } catch (err) {
    console.error('Failed to save restoration', err);
  } finally {
    saving.value = false;
  }
}

function close() { emit('update:visible', false); }
</script>

<template>
  <p-dialog :visible="visible" @update:visible="close" header="Add Restoration" :modal="true" :style="{ width: '500px' }" :closable="true">
    <div class="space-y-4">
      <div class="form-field">
        <label class="field-label">Tooth Number</label>
        <p-input-number v-model="form.tooth_number" :min="1" :max="32" class="w-full" />
      </div>
      <div class="form-field">
        <label class="field-label">Restoration Type</label>
        <p-select v-model="form.restoration_type" :options="restorationTypes" optionLabel="label" optionValue="value" class="w-full" />
      </div>
      <div class="form-field">
        <label class="field-label">Material</label>
        <p-select v-model="form.material" :options="materials" optionLabel="label" optionValue="value" class="w-full" />
      </div>
      <div class="form-field">
        <label class="field-label">Surfaces</label>
        <div class="flex gap-2">
          <button v-for="s in surfaceOptions" :key="s" @click="toggleSurface(s)"
            :class="['surface-btn', { active: form.surfaces.includes(s) }]">{{ s }}</button>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-field">
          <label class="field-label">Shade</label>
          <p-input-text v-model="form.shade" placeholder="e.g. A2" class="w-full" />
        </div>
        <div class="form-field">
          <label class="field-label">Placement Date</label>
          <p-calendar v-model="form.placement_date" dateFormat="yy-mm-dd" class="w-full" />
        </div>
      </div>
      <div class="form-field">
        <label class="field-label">Notes</label>
        <p-textarea v-model="form.notes" rows="2" class="w-full" />
      </div>
    </div>
    <template #footer>
      <div class="flex justify-end gap-2">
        <p-button label="Cancel" severity="secondary" text @click="close" />
        <p-button label="Save Restoration" icon="pi pi-check" :loading="saving" @click="save" />
      </div>
    </template>
  </p-dialog>
</template>

<style scoped>
@reference "tailwindcss/theme";
.form-field { @apply space-y-1; }
.field-label { @apply text-xs font-bold uppercase tracking-wider text-slate-500; }
.surface-btn { @apply w-10 h-10 rounded-lg border-2 border-slate-200 text-sm font-bold text-slate-500 flex items-center justify-center transition-all cursor-pointer hover:border-blue-300; }
.surface-btn.active { @apply border-blue-500 bg-blue-50 text-blue-700; }
</style>
