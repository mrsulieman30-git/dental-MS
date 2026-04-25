<script setup>
import { ref, watch } from 'vue';
import { useTreatmentPlanStore } from '../../../stores/treatmentPlan.store';

const props = defineProps({
    modelValue: { type: Object, default: null },
    feeScheduleId: { type: [Number, String], default: null }
});
const emit = defineEmits(['update:modelValue', 'select']);

const store = useTreatmentPlanStore();
const query = ref('');
const suggestions = ref([]);
const searching = ref(false);

const searchCdt = async (event) => {
    if (!event.query || event.query.length < 1) return;
    searching.value = true;
    try {
        suggestions.value = await store.searchCdtCodes(event.query, props.feeScheduleId);
    } finally { searching.value = false; }
};

const onSelect = (event) => {
    const selected = event.value;
    emit('update:modelValue', selected);
    emit('select', selected);
    query.value = `${selected.code} - ${selected.short_description}`;
};

watch(() => props.modelValue, (val) => {
    if (val) query.value = `${val.code} - ${val.short_description}`;
}, { immediate: true });
</script>

<template>
    <p-auto-complete
        v-model="query"
        :suggestions="suggestions"
        @complete="searchCdt"
        @item-select="onSelect"
        optionLabel="code"
        :loading="searching"
        placeholder="Search CDT code or description..."
        class="w-full cdt-search"
        inputClass="w-full"
        :delay="300"
        :minLength="1"
    >
        <template #option="{ option }">
            <div class="flex items-center gap-3 py-1">
                <span class="font-mono font-bold text-primary-600 text-sm min-w-[60px]">{{ option.code }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ option.short_description }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ option.description }}</p>
                </div>
                <span v-if="option.default_fee" class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">
                    ${{ Number(option.default_fee).toFixed(2) }}
                </span>
                <span class="text-[10px] uppercase font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                    {{ option.category }}
                </span>
            </div>
        </template>
        <template #empty>
            <div class="p-4 text-center text-sm text-slate-400">
                No matching CDT codes found
            </div>
        </template>
    </p-auto-complete>
</template>

<style scoped>
@reference "tailwindcss/theme";
:deep(.p-autocomplete-input) {
    @apply text-sm font-medium;
}
</style>
