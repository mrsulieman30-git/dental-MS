<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import SignaturePadLib from 'signature_pad';

const props = defineProps({
    width: { type: Number, default: 600 },
    height: { type: Number, default: 200 },
    penColor: { type: String, default: '#1e293b' }
});
const emit = defineEmits(['save']);

const canvas = ref(null);
let signaturePad = null;

onMounted(() => {
    signaturePad = new SignaturePadLib(canvas.value, {
        penColor: props.penColor,
        backgroundColor: 'rgb(255, 255, 255)',
        minWidth: 1,
        maxWidth: 3,
    });
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
});

onUnmounted(() => {
    window.removeEventListener('resize', resizeCanvas);
});

const resizeCanvas = () => {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const c = canvas.value;
    const data = signaturePad?.toData();
    c.width = c.offsetWidth * ratio;
    c.height = c.offsetHeight * ratio;
    c.getContext('2d').scale(ratio, ratio);
    if (data) signaturePad.fromData(data);
};

const clear = () => signaturePad?.clear();
const undo = () => {
    const data = signaturePad?.toData();
    if (data?.length) {
        data.pop();
        signaturePad.fromData(data);
    }
};
const isEmpty = () => signaturePad?.isEmpty();
const save = () => {
    if (signaturePad?.isEmpty()) return null;
    const dataUrl = signaturePad.toDataURL('image/png');
    emit('save', dataUrl);
    return dataUrl;
};

defineExpose({ clear, undo, isEmpty, save });
</script>

<template>
    <div class="signature-pad-wrapper">
        <div class="relative border-2 border-dashed border-slate-300 rounded-2xl overflow-hidden bg-white">
            <canvas
                ref="canvas"
                :style="{ width: '100%', height: height + 'px' }"
                class="touch-none"
            ></canvas>
            <div class="absolute bottom-4 left-6 right-6 border-b border-slate-300"></div>
            <span class="absolute bottom-6 left-6 text-[10px] text-slate-400 font-medium">Sign above</span>
        </div>
        <div class="flex items-center gap-2 mt-3">
            <button @click="undo" class="px-3 py-1.5 text-xs font-bold text-slate-500 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                Undo
            </button>
            <button @click="clear" class="px-3 py-1.5 text-xs font-bold text-red-500 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                Clear
            </button>
        </div>
    </div>
</template>
