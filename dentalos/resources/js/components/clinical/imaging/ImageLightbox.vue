<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { ZoomIn, ZoomOut, RotateCcw, Sun, Contrast, Eye, Pen, Circle, ArrowRight, Type, Ruler, X, ChevronLeft, ChevronRight, Download, Sparkles } from 'lucide-vue-next';

const props = defineProps({
  visible: Boolean,
  image: { type: Object, default: null },
  images: { type: Array, default: () => [] },
  aiAnalysis: { type: Object, default: null },
});
const emit = defineEmits(['update:visible', 'save-annotations']);

const zoom = ref(1);
const panX = ref(0);
const panY = ref(0);
const brightness = ref(100);
const contrast = ref(100);
const inverted = ref(false);
const showAiFindings = ref(false);
const currentIndex = ref(0);
const annotationTool = ref(null); // 'freehand','circle','arrow','text','ruler'
const annotations = ref([]);
const isPanning = ref(false);
const panStart = ref({ x: 0, y: 0 });

const currentImage = computed(() => {
  if (props.images.length) return props.images[currentIndex.value];
  return props.image;
});

const activeAiAnalysis = computed(() => currentImage.value?.ai_analysis || props.aiAnalysis);

const imageStyle = computed(() => ({
  transform: `scale(${zoom.value}) translate(${panX.value}px, ${panY.value}px)`,
  filter: `brightness(${brightness.value}%) contrast(${contrast.value}%) ${inverted.value ? 'invert(1)' : ''}`,
  transition: isPanning.value ? 'none' : 'transform 0.2s ease',
}));

watch(() => props.image, (image) => {
  if (image && props.images.length) {
    currentIndex.value = props.images.findIndex(i => i.id === image.id) || 0;
  }
}, { immediate: true });

watch(currentImage, (image) => {
  annotations.value = image?.annotations || [];
  resetView();
}, { immediate: true });

function resetView() { zoom.value = 1; panX.value = 0; panY.value = 0; brightness.value = 100; contrast.value = 100; inverted.value = false; }
function zoomIn() { zoom.value = Math.min(zoom.value + 0.25, 5); }
function zoomOut() { zoom.value = Math.max(zoom.value - 0.25, 0.25); }
function onWheel(e) { e.preventDefault(); e.deltaY < 0 ? zoomIn() : zoomOut(); }

function onPointerDown(e) {
  if (annotationTool.value) return;
  isPanning.value = true;
  panStart.value = { x: e.clientX - panX.value, y: e.clientY - panY.value };
}
function onPointerMove(e) {
  if (!isPanning.value) return;
  panX.value = e.clientX - panStart.value.x;
  panY.value = e.clientY - panStart.value.y;
}
function onPointerUp() { isPanning.value = false; }

function navigate(dir) {
  const len = props.images.length;
  if (!len) return;
  currentIndex.value = (currentIndex.value + dir + len) % len;
  resetView();
}

function onKeyDown(e) {
  if (!props.visible) return;
  if (e.key === 'Escape') emit('update:visible', false);
  if (e.key === 'ArrowLeft') navigate(-1);
  if (e.key === 'ArrowRight') navigate(1);
  if (e.key === '+' || e.key === '=') zoomIn();
  if (e.key === '-') zoomOut();
}

function saveAnnotations() {
  emit('save-annotations', { imageId: currentImage.value?.id, annotations: annotations.value });
}

function close() { emit('update:visible', false); }

onMounted(() => document.addEventListener('keydown', onKeyDown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeyDown));
</script>

<template>
  <Teleport to="body">
    <div v-if="visible" class="lightbox-overlay" @click.self="close">
      <!-- Close button -->
      <button @click="close" class="lightbox-close"><X class="w-6 h-6" /></button>

      <!-- Navigation -->
      <button v-if="images.length > 1" @click="navigate(-1)" class="lightbox-nav lightbox-nav-left"><ChevronLeft class="w-8 h-8" /></button>
      <button v-if="images.length > 1" @click="navigate(1)" class="lightbox-nav lightbox-nav-right"><ChevronRight class="w-8 h-8" /></button>

      <!-- Image container -->
      <div class="lightbox-viewport" @wheel="onWheel" @pointerdown="onPointerDown" @pointermove="onPointerMove" @pointerup="onPointerUp">
        <img
          v-if="currentImage"
          :src="currentImage.file_path || currentImage.thumbnail_path || '/images/placeholder-xray.jpg'"
          :style="imageStyle"
          class="lightbox-image"
          draggable="false"
        />

        <!-- AI Findings Overlay -->
        <div v-if="showAiFindings && activeAiAnalysis?.findings" class="ai-overlay" :style="imageStyle">
          <div
            v-for="(finding, idx) in activeAiAnalysis.findings" :key="idx"
            class="ai-bounding-box"
            :style="{
              left: finding.bounding_box?.x + '%',
              top: finding.bounding_box?.y + '%',
              width: finding.bounding_box?.w + '%',
              height: finding.bounding_box?.h + '%',
            }"
            :title="`${finding.finding_type} (${finding.confidence}%)`"
          >
            <div class="ai-label">{{ finding.finding_type }} {{ finding.confidence }}%</div>
          </div>
        </div>
      </div>

      <!-- Bottom Toolbar -->
      <div class="lightbox-toolbar">
        <div class="toolbar-section">
          <div class="flex items-center gap-1">
            <button @click="zoomOut" class="lt-btn"><ZoomOut class="w-4 h-4" /></button>
            <span class="text-xs text-white/70 w-12 text-center">{{ Math.round(zoom * 100) }}%</span>
            <button @click="zoomIn" class="lt-btn"><ZoomIn class="w-4 h-4" /></button>
            <button @click="resetView" class="lt-btn"><RotateCcw class="w-4 h-4" /></button>
          </div>
        </div>

        <div class="toolbar-section">
          <div class="flex items-center gap-3">
            <label class="flex items-center gap-1 text-xs text-white/70">
              <Sun class="w-3.5 h-3.5" />
              <input type="range" v-model="brightness" min="0" max="200" class="lt-slider" />
            </label>
            <label class="flex items-center gap-1 text-xs text-white/70">
              <Contrast class="w-3.5 h-3.5" />
              <input type="range" v-model="contrast" min="0" max="200" class="lt-slider" />
            </label>
            <button @click="inverted = !inverted" :class="['lt-btn', { 'lt-btn-active': inverted }]"><Eye class="w-4 h-4" /></button>
          </div>
        </div>

        <div class="toolbar-section">
          <button v-if="activeAiAnalysis" @click="showAiFindings = !showAiFindings" :class="['lt-btn', { 'lt-btn-active': showAiFindings }]">
            <Sparkles class="w-4 h-4" /><span class="text-xs ml-1">AI</span>
          </button>
          <!-- Annotation tools -->
          <button @click="annotationTool = annotationTool === 'freehand' ? null : 'freehand'" :class="['lt-btn', { 'lt-btn-active': annotationTool === 'freehand' }]"><Pen class="w-4 h-4" /></button>
          <button @click="annotationTool = annotationTool === 'circle' ? null : 'circle'" :class="['lt-btn', { 'lt-btn-active': annotationTool === 'circle' }]"><Circle class="w-4 h-4" /></button>
          <button @click="annotationTool = annotationTool === 'arrow' ? null : 'arrow'" :class="['lt-btn', { 'lt-btn-active': annotationTool === 'arrow' }]"><ArrowRight class="w-4 h-4" /></button>
          <button @click="annotationTool = annotationTool === 'text' ? null : 'text'" :class="['lt-btn', { 'lt-btn-active': annotationTool === 'text' }]"><Type class="w-4 h-4" /></button>
          <button @click="annotationTool = annotationTool === 'ruler' ? null : 'ruler'" :class="['lt-btn', { 'lt-btn-active': annotationTool === 'ruler' }]"><Ruler class="w-4 h-4" /></button>
        </div>

        <div class="toolbar-section">
          <span class="text-xs text-white/50">{{ currentIndex + 1 }}/{{ images.length || 1 }}</span>
          <button @click="saveAnnotations" class="lt-btn" title="Save annotations">
            <Download class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
@reference "tailwindcss/theme";
.lightbox-overlay { @apply fixed inset-0 z-50 bg-black/95 flex items-center justify-center; }
.lightbox-close { @apply absolute top-4 right-4 z-50 p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-xl transition-colors; }
.lightbox-nav { @apply absolute top-1/2 -translate-y-1/2 z-40 p-3 text-white/50 hover:text-white hover:bg-white/10 rounded-xl transition-colors; }
.lightbox-nav-left { @apply left-4; }
.lightbox-nav-right { @apply right-4; }
.lightbox-viewport { @apply w-full h-full flex items-center justify-center overflow-hidden cursor-grab active:cursor-grabbing relative; }
.lightbox-image { @apply max-w-[90%] max-h-[80vh] object-contain select-none; }
.lightbox-toolbar { @apply absolute bottom-0 left-0 right-0 flex items-center justify-center gap-6 px-6 py-3 bg-black/60 backdrop-blur-md; }
.toolbar-section { @apply flex items-center gap-2; }
.lt-btn { @apply p-2 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-colors flex items-center; }
.lt-btn-active { @apply bg-white/20 text-white; }
.lt-slider { @apply w-20 h-1 accent-white; }
.ai-overlay { @apply absolute inset-0 pointer-events-none; }
.ai-bounding-box { @apply absolute border-2 border-yellow-400 rounded pointer-events-auto cursor-pointer; }
.ai-label { @apply absolute -top-5 left-0 text-[10px] bg-yellow-400 text-black font-bold px-1 rounded whitespace-nowrap; }
</style>
