<script setup>
import { ref, inject, onMounted, computed, watch } from 'vue';
import NoteEditor from '../../../components/clinical/notes/NoteEditor.vue';
import { formatDateTime } from '../../../utils/formatters.js';
import axios from 'axios';
import {
  Plus, FileText, Lock, CheckCircle, Search, Phone, MessageSquare,
  Stethoscope, ClipboardList, File
} from 'lucide-vue-next';

const patient = inject('patient');
const notes = ref([]);
const loading = ref(false);
const showEditor = ref(false);
const editingNote = ref(null);
const searchQuery = ref('');
const filterType = ref(null);

const noteTypeIcons = {
  soap: Stethoscope, progress: ClipboardList, consult: MessageSquare,
  phone: Phone, general: File, referral: FileText,
};

const noteTypeColors = {
  soap: 'info', progress: 'success', consult: 'warning',
  phone: 'secondary', general: 'secondary', referral: 'info',
};

const filterOptions = [
  { label: 'All Types', value: null },
  { label: 'SOAP', value: 'soap' },
  { label: 'Progress', value: 'progress' },
  { label: 'Consult', value: 'consult' },
  { label: 'Phone Call', value: 'phone' },
  { label: 'General', value: 'general' },
];

async function fetchNotes() {
  if (!patient.value?.id) return;
  loading.value = true;
  try {
    const { data } = await axios.get(`/clinical/patients/${patient.value.id}/notes`);
    notes.value = data.data || [];
  } catch (err) {
    console.error('Failed to load notes', err);
  } finally {
    loading.value = false;
  }
}

onMounted(fetchNotes);
watch(() => patient.value?.id, fetchNotes);

const filteredNotes = computed(() => {
  let result = notes.value;
  if (filterType.value) result = result.filter(n => n.note_type === filterType.value);
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(n =>
      (n.full_note_text || '').toLowerCase().includes(q) ||
      (n.subjective || '').toLowerCase().includes(q) ||
      (n.objective || '').toLowerCase().includes(q)
    );
  }
  return result;
});

function openNewNote() {
  editingNote.value = null;
  showEditor.value = true;
}

function openNote(note) {
  editingNote.value = note;
  showEditor.value = true;
}

function onNoteSaved() {
  fetchNotes();
}

function preview(note) {
  const text = note.full_note_text || [note.subjective, note.objective, note.assessment, note.plan].filter(Boolean).join(' ');
  return text?.replace(/<[^>]*>/g, '').slice(0, 150) || 'No content';
}
</script>

<template>
  <div class="notes-tab">
    <!-- Toolbar -->
    <div class="notes-toolbar">
      <div class="flex items-center gap-3">
        <h2 class="text-lg font-black text-slate-800 dark:text-white">Clinical Notes</h2>
        <p-select v-model="filterType" :options="filterOptions" optionLabel="label" optionValue="value" class="w-36" size="small" />
      </div>
      <div class="flex items-center gap-2">
        <span class="relative">
          <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input v-model="searchQuery" placeholder="Search notes..." class="pl-9 pr-4 py-1.5 rounded-lg border border-slate-200 text-sm w-52 outline-none focus:ring-2 focus:ring-blue-300" />
        </span>
        <p-button label="New Note" icon="pi pi-plus" @click="openNewNote" size="small" />
      </div>
    </div>

    <!-- Notes List -->
    <div v-if="loading" class="space-y-3 mt-4">
      <p-skeleton height="5rem" v-for="i in 4" :key="i" />
    </div>

    <div v-else-if="filteredNotes.length === 0" class="empty-state mt-8">
      <FileText class="w-12 h-12 text-slate-300 mx-auto mb-3" />
      <p class="text-sm text-slate-400">No notes found</p>
      <p class="text-xs text-slate-300 mt-1">Click "New Note" to create one</p>
    </div>

    <div v-else class="notes-list mt-4">
      <div
        v-for="note in filteredNotes" :key="note.id"
        @click="openNote(note)"
        class="note-card"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="note-icon-wrapper" :class="`type-${note.note_type}`">
              <component :is="noteTypeIcons[note.note_type] || File" class="w-4 h-4" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-slate-700 dark:text-white">{{ formatDateTime(note.created_at) }}</span>
                <p-tag :value="note.note_type?.toUpperCase()" :severity="noteTypeColors[note.note_type]" class="text-[9px] font-black" />
              </div>
              <div class="text-xs text-slate-400 mt-0.5">{{ note.provider?.full_name || 'Unknown Provider' }}</div>
            </div>
          </div>
          <div class="flex items-center gap-1.5">
            <Lock v-if="note.is_locked" class="w-3.5 h-3.5 text-amber-500" title="Locked" />
            <CheckCircle v-if="note.is_signed" class="w-3.5 h-3.5 text-green-500" title="Signed" />
          </div>
        </div>
        <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">{{ preview(note) }}</p>
      </div>
    </div>

    <!-- Note Editor Dialog -->
    <NoteEditor
      v-if="showEditor"
      :visible="showEditor"
      @update:visible="showEditor = $event"
      :patientId="patient?.id"
      :note="editingNote"
      @saved="onNoteSaved"
      @closed="showEditor = false"
    />
  </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.notes-tab { @apply space-y-0; }
.notes-toolbar { @apply flex items-center justify-between px-4 py-2 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200/50 dark:border-slate-700; }
.notes-list { @apply space-y-2; }
.note-card {
  @apply p-4 bg-white dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700
         shadow-sm hover:shadow-md hover:border-blue-200 dark:hover:border-blue-800 transition-all cursor-pointer;
}
.note-icon-wrapper { @apply w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0; }
.type-soap { @apply bg-blue-100 text-blue-600; }
.type-progress { @apply bg-green-100 text-green-600; }
.type-consult { @apply bg-amber-100 text-amber-600; }
.type-phone { @apply bg-slate-100 text-slate-600; }
.type-general { @apply bg-slate-100 text-slate-500; }
.type-referral { @apply bg-indigo-100 text-indigo-600; }
.empty-state { @apply text-center; }
</style>