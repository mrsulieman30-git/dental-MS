<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: { type: Number, default: null },
    compact: { type: Boolean, default: false },
    highlightedTeeth: { type: Array, default: () => [] },
    highlightColor: { type: String, default: '#6366f1' }
});
const emit = defineEmits(['update:modelValue']);

const hoveredTooth = ref(null);

// Universal numbering: 1-16 upper right to left, 17-32 lower left to right
const upperTeeth = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16];
const lowerTeeth = [32,31,30,29,28,27,26,25,24,23,22,21,20,19,18,17];

const toothNames = {
    1:'UR 3rd Molar',2:'UR 2nd Molar',3:'UR 1st Molar',4:'UR 2nd Premolar',5:'UR 1st Premolar',
    6:'UR Canine',7:'UR Lateral Incisor',8:'UR Central Incisor',9:'UL Central Incisor',10:'UL Lateral Incisor',
    11:'UL Canine',12:'UL 1st Premolar',13:'UL 2nd Premolar',14:'UL 1st Molar',15:'UL 2nd Molar',16:'UL 3rd Molar',
    17:'LL 3rd Molar',18:'LL 2nd Molar',19:'LL 1st Molar',20:'LL 2nd Premolar',21:'LL 1st Premolar',
    22:'LL Canine',23:'LL Lateral Incisor',24:'LL Central Incisor',25:'LR Central Incisor',26:'LR Lateral Incisor',
    27:'LR Canine',28:'LR 1st Premolar',29:'LR 2nd Premolar',30:'LR 1st Molar',31:'LR 2nd Molar',32:'LR 3rd Molar'
};

const isSelected = (num) => props.modelValue === num;
const isHighlighted = (num) => props.highlightedTeeth.includes(num);

const selectTooth = (num) => {
    emit('update:modelValue', props.modelValue === num ? null : num);
};

const getToothFill = (num) => {
    if (isSelected(num)) return props.highlightColor;
    if (isHighlighted(num)) return props.highlightColor + '40';
    return hoveredTooth.value === num ? '#e2e8f0' : '#f1f5f9';
};

const size = computed(() => props.compact ? 18 : 28);
const gap = computed(() => props.compact ? 1 : 2);
</script>

<template>
    <div class="mini-odontogram select-none" :class="{ 'compact': compact }">
        <div v-if="!compact && modelValue" class="text-center mb-2">
            <span class="text-xs font-bold text-primary-600 bg-primary-50 px-2 py-0.5 rounded-full">
                #{{ modelValue }} — {{ toothNames[modelValue] }}
            </span>
        </div>
        <svg :width="upperTeeth.length * (size + gap) + 8" :height="(size * 2) + gap + 8" class="mx-auto">
            <!-- Upper arch -->
            <g v-for="(tooth, i) in upperTeeth" :key="'u'+tooth">
                <rect
                    :x="4 + i * (size + gap)" :y="4" :width="size" :height="size"
                    :rx="compact ? 2 : 4"
                    :fill="getToothFill(tooth)"
                    :stroke="isSelected(tooth) ? highlightColor : '#cbd5e1'"
                    :stroke-width="isSelected(tooth) ? 2 : 1"
                    class="cursor-pointer transition-all duration-150"
                    @click="selectTooth(tooth)"
                    @mouseenter="hoveredTooth = tooth"
                    @mouseleave="hoveredTooth = null"
                />
                <text
                    :x="4 + i * (size + gap) + size/2" :y="4 + size/2 + (compact ? 3 : 4)"
                    text-anchor="middle" :font-size="compact ? 7 : 10"
                    font-weight="700" fill="#475569"
                    class="pointer-events-none select-none"
                >{{ tooth }}</text>
            </g>
            <!-- Lower arch -->
            <g v-for="(tooth, i) in lowerTeeth" :key="'l'+tooth">
                <rect
                    :x="4 + i * (size + gap)" :y="4 + size + gap" :width="size" :height="size"
                    :rx="compact ? 2 : 4"
                    :fill="getToothFill(tooth)"
                    :stroke="isSelected(tooth) ? highlightColor : '#cbd5e1'"
                    :stroke-width="isSelected(tooth) ? 2 : 1"
                    class="cursor-pointer transition-all duration-150"
                    @click="selectTooth(tooth)"
                    @mouseenter="hoveredTooth = tooth"
                    @mouseleave="hoveredTooth = null"
                />
                <text
                    :x="4 + i * (size + gap) + size/2" :y="4 + size + gap + size/2 + (compact ? 3 : 4)"
                    text-anchor="middle" :font-size="compact ? 7 : 10"
                    font-weight="700" fill="#475569"
                    class="pointer-events-none select-none"
                >{{ tooth }}</text>
            </g>
        </svg>
    </div>
</template>

<style scoped>
.mini-odontogram { @apply inline-block; }
.mini-odontogram.compact svg { max-width: 320px; }
</style>
