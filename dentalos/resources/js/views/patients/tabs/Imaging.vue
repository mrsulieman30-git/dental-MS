<script setup>
import { ref, inject, onMounted, computed, watch } from 'vue';
import { useImagingStore } from '../../../stores/imaging.store';
import ImageLightbox from '../../../components/clinical/imaging/ImageLightbox.vue';
import { formatDate } from '../../../utils/formatters.js';
import {
  Upload, Image as ImageIcon, Camera, Scan, Eye, Link2, FolderOpen,
  Grid, X, Plus, Share2
} from 'lucide-vue-next';

const patient = inject('patient');
const store = useImagingStore();

const showLightbox = ref(false);
const showUpload = ref(false);
const uploadFiles = ref([]);
const uploadSeriesType = ref('bw');
const uploadToothNum = ref(null);
const isDragging = ref(false);

const seriesTypeIcons = {
  fmx: Scan, bw: Scan, pa: Scan, pan: Scan, ceph: Scan,
  intraoral_photo: Camera, extraoral_photo: Camera, cbct: Grid, other: ImageIcon,
};

onMounted(async () => {
  if (patient.value?.id) await store.fetchSeries(patient.value.id);
});

watch(() => patient.value?.id, async (id) => {
  if (id) await store.fetchSeries(id);
});

const seriesTypeOptions = computed(() =>
  Object.entries(store.seriesTypeLabels).map(([value, label]) => ({ value, label }))
);

function selectSeries(seriesId) { store.selectSeries(seriesId); }

function openImage(image) {
  store.selectImage(image);
  showLightbox.value = true;
}

function onDragOver(e) { e.preventDefault(); isDragging.value = true; }
function onDragLeave() { isDragging.value = false; }
function onDrop(e) {
  e.preventDefault();
  isDragging.value = false;
  const files = Array.from(e.dataTransfer?.files || []);
  if (files.length) {
    uploadFiles.value = files;
    showUpload.value = true;
  }
}

function onFileSelect(e) {
  const files = Array.from(e.target.files || []);
  if (files.length) {
    uploadFiles.value = files;
    showUpload.value = true;
  }
}

async function doUpload() {
  if (!uploadFiles.value.length || !patient.value?.id) return;
  try {
    await store.uploadImages(patient.value.id, uploadFiles.value, uploadSeriesType.value, uploadToothNum.value);
    showUpload.value = false;
    uploadFiles.value = [];
  } catch (err) {
    console.error('Upload failed', err);
  }
}

async function shareImage(image) {
  try {
    const url = await store.generateShareLink(image.id);
    if (url) {
      await navigator.clipboard.writeText(url);
      alert('Share link copied to clipboard!');
    }
  } catch (err) { console.error('Share failed', err); }
}

async function saveAnnotations({ imageId, annotations }) {
  if (!imageId) {
    return;
  }

  try {
    await store.updateAnnotations(imageId, annotations);
  } catch (err) {
    console.error('Failed to save annotations', err);
  }
}
</script>

<template>
  <div class="imaging-tab">
    <div class="imaging-layout">
      <!-- Left Sidebar: Series List -->
      <div class="series-sidebar">
        <div class="sidebar-header">
          <h3 class="text-sm font-black text-slate-700 dark:text-white">Image Series</h3>
          <button @click="showUpload = true" class="upload-btn"><Plus class="w-4 h-4" /></button>
        </div>

        <div v-if="store.isLoading && !store.series.length" class="p-3 space-y-2">
          <p-skeleton height="3rem" v-for="i in 5" :key="i" />
        </div>

        <div v-else-if="store.series.length === 0" class="p-4 text-center">
          <Camera class="w-8 h-8 text-slate-300 mx-auto mb-2" />
          <p class="text-xs text-slate-400">No images</p>
        </div>

        <div v-else class="series-list">
          <div v-for="(seriesList, type) in store.seriesByType" :key="type" class="series-type-group">
            <div class="type-label">{{ store.seriesTypeLabels[type] || type }}</div>
            <button
              v-for="s in seriesList" :key="s.id"
              @click="selectSeries(s.id)"
              :class="['series-item', { active: store.selectedSeries?.id === s.id }]"
            >
              <component :is="seriesTypeIcons[type] || ImageIcon" class="w-4 h-4 flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold truncate">{{ s.name || store.seriesTypeLabels[type] }}</div>
                <div class="text-[10px] text-slate-400">{{ formatDate(s.taken_at) }} · {{ s.images_count || 0 }} images</div>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Image Viewer -->
      <div
        class="image-viewer"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
      >
        <div v-if="isDragging" class="drag-overlay">
          <Upload class="w-12 h-12 text-blue-400 mb-2" />
          <p class="text-sm font-bold text-blue-500">Drop files to upload</p>
        </div>

        <div v-if="store.images.length" class="image-grid">
          <div
            v-for="img in store.images" :key="img.id"
            class="image-thumb group"
            @click="openImage(img)"
          >
            <img
              :src="img.thumbnail_path || img.file_path || '/images/placeholder-xray.jpg'"
              :alt="`Image ${img.image_number}`"
              class="thumb-img"
            />
            <div class="thumb-overlay">
              <Eye class="w-4 h-4" />
            </div>
            <div class="thumb-info">
              <span class="text-[9px] font-bold text-white">{{ img.tooth_number ? `#${img.tooth_number}` : `#${img.image_number}` }}</span>
            </div>
            <div class="thumb-actions">
              <button @click.stop="shareImage(img)" class="thumb-action-btn" title="Share">
                <Share2 class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>

        <div v-else-if="store.selectedSeries" class="empty-viewer">
          <p class="text-sm text-slate-400">No images in this series</p>
        </div>

        <div v-else class="empty-viewer">
          <FolderOpen class="w-16 h-16 text-slate-200 mb-4" />
          <p class="text-sm font-bold text-slate-400">Select a series to view images</p>
          <p class="text-xs text-slate-300 mt-1">Or drag and drop files here to upload</p>
          <label class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl text-sm font-bold cursor-pointer hover:bg-blue-100 transition-colors">
            <Upload class="w-4 h-4" />
            Upload Images
            <input type="file" multiple accept="image/*" @change="onFileSelect" class="hidden" />
          </label>
        </div>
      </div>
    </div>

    <!-- Upload Dialog -->
    <p-dialog v-model:visible="showUpload" header="Upload Images" :modal="true" :style="{ width: '480px' }">
      <div class="space-y-4">
        <div class="form-field">
          <label class="text-xs font-bold uppercase text-slate-500">Series Type</label>
          <p-select v-model="uploadSeriesType" :options="seriesTypeOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>
        <div class="form-field">
          <label class="text-xs font-bold uppercase text-slate-500">Tooth Number (optional)</label>
          <p-input-number v-model="uploadToothNum" :min="1" :max="32" class="w-full" />
        </div>
        <div class="p-3 bg-slate-50 rounded-xl">
          <p class="text-sm font-bold text-slate-600">{{ uploadFiles.length }} file(s) selected</p>
          <div v-for="(f, i) in uploadFiles" :key="i" class="text-xs text-slate-400 mt-1">{{ f.name }}</div>
        </div>
        <div v-if="store.uploadProgress?.percent" class="mt-2">
          <p-progress-bar :value="store.uploadProgress.percent" />
        </div>
      </div>
      <template #footer>
        <p-button label="Cancel" severity="secondary" text @click="showUpload = false" />
        <p-button label="Upload" icon="pi pi-upload" @click="doUpload" :loading="!!store.uploadProgress?.percent" />
      </template>
    </p-dialog>

    <!-- Lightbox -->
    <ImageLightbox
      :visible="showLightbox"
      @update:visible="showLightbox = $event"
      :image="store.selectedImage"
      :images="store.images"
      @save-annotations="saveAnnotations"
    />
  </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.imaging-tab { @apply h-full; }
.imaging-layout { @apply flex h-full gap-0 min-h-[500px]; }
.series-sidebar { @apply w-64 flex-shrink-0 border-r border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-y-auto; }
.sidebar-header { @apply flex items-center justify-between p-3 border-b border-slate-200 dark:border-slate-700; }
.upload-btn { @apply p-1.5 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 transition-colors; }
.series-list { @apply py-1; }
.series-type-group { @apply mb-1; }
.type-label { @apply px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/30; }
.series-item { @apply w-full flex items-center gap-2 px-3 py-2 text-left text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors; }
.series-item.active { @apply bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400; }

.image-viewer { @apply flex-1 relative bg-slate-50 dark:bg-slate-800/20 overflow-y-auto p-4; }
.drag-overlay { @apply absolute inset-0 z-10 flex flex-col items-center justify-center bg-blue-50/80 border-2 border-dashed border-blue-400 rounded-xl; }
.image-grid { @apply grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3; }
.image-thumb { @apply relative rounded-xl overflow-hidden aspect-square bg-black cursor-pointer; }
.thumb-img { @apply w-full h-full object-cover; }
.thumb-overlay { @apply absolute inset-0 bg-black/0 flex items-center justify-center text-white opacity-0 transition-all; }
.thumb-info { @apply absolute bottom-0 left-0 right-0 px-2 py-1 bg-gradient-to-t from-black/60; }
.thumb-actions { @apply absolute top-1 right-1 opacity-0 transition-opacity; }
.thumb-action-btn { @apply p-1.5 rounded-lg bg-white/20 text-white hover:bg-white/40 transition-colors backdrop-blur-sm; }
.empty-viewer { @apply flex flex-col items-center justify-center h-full text-center; }
.form-field { @apply space-y-1; }
.image-thumb:hover .thumb-overlay { @apply bg-black/40 opacity-100; }
.image-thumb:hover .thumb-actions { @apply opacity-100; }
</style>
