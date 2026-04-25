<script setup>
const props = defineProps({
    modelValue: { type: Array, default: () => [] }
});
const emit = defineEmits(['update:modelValue']);

const surfaces = [
    { key: 'M', label: 'Mesial', pos: 'left' },
    { key: 'O', label: 'Occlusal', pos: 'center' },
    { key: 'D', label: 'Distal', pos: 'right' },
    { key: 'B', label: 'Buccal/Facial', pos: 'top' },
    { key: 'L', label: 'Lingual', pos: 'bottom' },
    { key: 'I', label: 'Incisal', pos: 'incisal' }
];

const toggle = (key) => {
    const current = [...props.modelValue];
    const idx = current.indexOf(key);
    if (idx === -1) current.push(key);
    else current.splice(idx, 1);
    emit('update:modelValue', current);
};

const isActive = (key) => props.modelValue.includes(key);
</script>

<template>
    <div class="surface-selector flex items-center gap-2">
        <!-- Visual tooth cross-section -->
        <div class="relative w-14 h-14 flex-shrink-0">
            <!-- Top/Buccal -->
            <div
                @click="toggle('B')"
                :class="['surf-zone top', { active: isActive('B') }]"
                title="Buccal/Facial"
            ></div>
            <!-- Left/Mesial -->
            <div
                @click="toggle('M')"
                :class="['surf-zone left', { active: isActive('M') }]"
                title="Mesial"
            ></div>
            <!-- Center/Occlusal -->
            <div
                @click="toggle('O')"
                :class="['surf-zone center', { active: isActive('O') }]"
                title="Occlusal"
            ></div>
            <!-- Right/Distal -->
            <div
                @click="toggle('D')"
                :class="['surf-zone right', { active: isActive('D') }]"
                title="Distal"
            ></div>
            <!-- Bottom/Lingual -->
            <div
                @click="toggle('L')"
                :class="['surf-zone bottom', { active: isActive('L') }]"
                title="Lingual"
            ></div>
        </div>
        <!-- Checkbox labels -->
        <div class="flex flex-wrap gap-1">
            <button
                v-for="s in surfaces" :key="s.key"
                @click="toggle(s.key)"
                :class="[
                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase transition-all',
                    isActive(s.key)
                        ? 'bg-primary-500 text-white shadow-sm'
                        : 'bg-slate-100 text-slate-500 hover:bg-slate-200'
                ]"
                :title="s.label"
            >{{ s.key }}</button>
        </div>
    </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.surf-zone {
    @apply absolute cursor-pointer border border-slate-300 transition-all;
}
.surf-zone:hover { @apply bg-primary-100; }
.surf-zone.active { @apply bg-primary-400 border-primary-500; }

.surf-zone.top { @apply top-0 left-[25%] w-[50%] h-[25%] rounded-t; }
.surf-zone.bottom { @apply bottom-0 left-[25%] w-[50%] h-[25%] rounded-b; }
.surf-zone.left { @apply top-[25%] left-0 w-[25%] h-[50%] rounded-l; }
.surf-zone.right { @apply top-[25%] right-0 w-[25%] h-[50%] rounded-r; }
.surf-zone.center { @apply top-[25%] left-[25%] w-[50%] h-[50%] bg-slate-50; }
</style>
