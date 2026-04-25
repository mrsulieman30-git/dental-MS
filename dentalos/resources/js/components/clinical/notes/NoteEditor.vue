<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import UnderlineExtension from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import { ToothChipExtension } from './ToothChipExtension.js';
import { quickPhraseCategories } from './quickPhrases.js';
import { isSpeechRecognitionAvailable, createSpeechRecognition } from '../../../utils/perioVoiceParser.js';
import {
  Bold, Italic, Underline, List, ListOrdered, Mic, MicOff,
  CheckCircle, FileText, X, Sparkles, PencilLine
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  visible: { type: Boolean, default: false },
  patientId: { type: [Number, String], required: true },
  appointmentId: { type: [Number, String], default: null },
  note: { type: Object, default: null },
  embedded: { type: Boolean, default: false },
});

const emit = defineEmits(['update:visible', 'saved', 'closed']);

const noteType = ref('soap');
const isSaving = ref(false);
const lastSaved = ref(null);
const isLocked = ref(false);
const isSigned = ref(false);
const noteId = ref(null);
const templates = ref([]);
const showQuickPhrases = ref(false);
const showTemplates = ref(false);
const showAmendment = ref(false);
const amendmentText = ref('');
const isVoiceActive = ref(false);
const voiceSupported = ref(false);
const activeSoapSection = ref('subjective');

let recognition = null;
let autoSaveTimer = null;

const noteTypes = [
  { label: 'SOAP', value: 'soap' },
  { label: 'Progress', value: 'progress' },
  { label: 'Consult', value: 'consult' },
  { label: 'Phone Call', value: 'phone' },
  { label: 'General', value: 'general' },
];

const soapSections = ['subjective', 'objective', 'assessment', 'plan'];
const soapLabels = {
  subjective: 'S - Subjective',
  objective: 'O - Objective',
  assessment: 'A - Assessment',
  plan: 'P - Plan',
};

const buildExtensions = (placeholder) => ([
  StarterKit,
  UnderlineExtension,
  Placeholder.configure({ placeholder }),
  ToothChipExtension,
]);

const editor = useEditor({
  extensions: buildExtensions('Start typing your note...'),
  content: '',
  editable: true,
});

const soapEditors = Object.fromEntries(
  soapSections.map((section) => [
    section,
    useEditor({
      extensions: buildExtensions(`Enter ${section}...`),
      content: '',
      editable: true,
    }),
  ]),
);

const isSoap = computed(() => noteType.value === 'soap');
const filteredTemplates = computed(() =>
  templates.value.filter((template) => !template.note_type || template.note_type === noteType.value),
);

function getEditorInstance(section = null) {
  if (!isSoap.value) {
    return editor.value;
  }

  const key = section || activeSoapSection.value;
  return soapEditors[key]?.value || soapEditors.subjective?.value || null;
}

const activeEditor = computed(() => getEditorInstance());

function setEditorContent(editorRef, content = '') {
  editorRef?.value?.commands?.setContent(content || '');
}

function setEditorsEditable(editable) {
  editor.value?.setEditable(editable);
  soapSections.forEach((section) => {
    soapEditors[section]?.value?.setEditable(editable);
  });
}

function clearEditors() {
  setEditorContent(editor, '');
  soapSections.forEach((section) => setEditorContent(soapEditors[section], ''));
}

function syncNoteState(note) {
  const soapReady = soapSections.every((section) => !!soapEditors[section]?.value);
  if (!editor.value || !soapReady) {
    nextTick(() => syncNoteState(note));
    return;
  }

  showQuickPhrases.value = false;
  showTemplates.value = false;
  showAmendment.value = false;
  amendmentText.value = '';
  activeSoapSection.value = 'subjective';

  if (!note) {
    noteId.value = null;
    noteType.value = 'soap';
    isLocked.value = false;
    isSigned.value = false;
    lastSaved.value = null;
    clearEditors();
    setEditorsEditable(true);
    return;
  }

  noteId.value = note.id;
  noteType.value = note.note_type || 'soap';
  isLocked.value = !!note.is_locked;
  isSigned.value = !!note.is_signed;

  if (noteType.value === 'soap') {
    soapSections.forEach((section) => {
      setEditorContent(soapEditors[section], note[section] || '');
    });
    setEditorContent(editor, note.full_note_text || '');
  } else {
    setEditorContent(editor, note.full_note_text || '');
    soapSections.forEach((section) => setEditorContent(soapEditors[section], ''));
  }

  setEditorsEditable(!isLocked.value);
}

watch(() => props.note, syncNoteState, { immediate: true });

watch(
  () => props.visible,
  (visible) => {
    if (visible || props.embedded) {
      fetchTemplates();
    }

    if (!visible && !props.embedded) {
      recognition?.stop();
      isVoiceActive.value = false;
    }
  },
  { immediate: true },
);

watch(noteType, () => {
  fetchTemplates();
});

async function fetchTemplates() {
  try {
    const { data } = await axios.get('/clinical/note-templates', {
      params: { note_type: noteType.value },
    });
    templates.value = data.data || [];
  } catch (error) {
    templates.value = [];
  }
}

function buildPayload() {
  const payload = {
    note_type: noteType.value,
    appointment_id: props.appointmentId,
  };

  if (isSoap.value) {
    soapSections.forEach((section) => {
      payload[section] = soapEditors[section]?.value?.getHTML() || '';
    });
    payload.full_note_text = soapSections
      .map((section) => {
        const text = soapEditors[section]?.value?.getText() || '';
        return `${soapLabels[section]}\n${text}`.trim();
      })
      .filter(Boolean)
      .join('\n\n');
  } else {
    payload.full_note_text = editor.value?.getHTML() || '';
    payload.subjective = null;
    payload.objective = null;
    payload.assessment = null;
    payload.plan = null;
  }

  return payload;
}

function hasMeaningfulContent(payload) {
  return [payload.full_note_text, payload.subjective, payload.objective, payload.assessment, payload.plan]
    .some((value) => typeof value === 'string' && value.replace(/<[^>]+>/g, '').trim().length > 0);
}

async function saveNote(isAutoSave = false) {
  if (isLocked.value) {
    return;
  }

  const payload = buildPayload();
  if (!hasMeaningfulContent(payload)) {
    return;
  }

  isSaving.value = true;
  try {
    if (noteId.value) {
      await axios.patch(`/clinical/notes/${noteId.value}`, payload);
    } else {
      const { data } = await axios.post(`/clinical/patients/${props.patientId}/notes`, payload);
      noteId.value = data.data?.id || null;
    }

    lastSaved.value = new Date();

    if (!isAutoSave) {
      emit('saved', noteId.value);
    }
  } catch (error) {
    console.error('Failed to save note', error);
  } finally {
    isSaving.value = false;
  }
}

async function signNote() {
  if (!noteId.value) {
    await saveNote();
  }

  if (!noteId.value) {
    return;
  }

  try {
    await axios.patch(`/clinical/notes/${noteId.value}/sign`);
    isSigned.value = true;
    lastSaved.value = new Date();
    emit('saved', noteId.value);
  } catch (error) {
    console.error('Failed to sign note', error);
  }
}

async function lockNote() {
  if (!noteId.value) {
    await saveNote();
  }

  if (!noteId.value) {
    return;
  }

  try {
    await axios.patch(`/clinical/notes/${noteId.value}/lock`);
    isLocked.value = true;
    setEditorsEditable(false);
    emit('saved', noteId.value);
  } catch (error) {
    console.error('Failed to lock note', error);
  }
}

async function saveAmendment() {
  if (!noteId.value || !amendmentText.value.trim()) {
    return;
  }

  try {
    await axios.post(`/clinical/notes/${noteId.value}/amend`, {
      amendment_notes: amendmentText.value.trim(),
    });
    amendmentText.value = '';
    showAmendment.value = false;
    emit('saved', noteId.value);
  } catch (error) {
    console.error('Failed to add amendment', error);
  }
}

function parseSoapTemplate(content) {
  const sections = {
    subjective: '',
    objective: '',
    assessment: '',
    plan: '',
  };

  const patterns = {
    subjective: /\[(?:S|SUBJECTIVE)\]([\s\S]*?)(?=\[(?:O|OBJECTIVE|A|ASSESSMENT|P|PLAN)\]|$)/i,
    objective: /\[(?:O|OBJECTIVE)\]([\s\S]*?)(?=\[(?:A|ASSESSMENT|P|PLAN)\]|$)/i,
    assessment: /\[(?:A|ASSESSMENT)\]([\s\S]*?)(?=\[(?:P|PLAN)\]|$)/i,
    plan: /\[(?:P|PLAN)\]([\s\S]*)$/i,
  };

  let matched = false;
  Object.entries(patterns).forEach(([section, pattern]) => {
    const match = content.match(pattern);
    if (match?.[1]) {
      sections[section] = match[1].trim();
      matched = true;
    }
  });

  if (!matched) {
    sections.subjective = content;
  }

  return sections;
}

function renderTemplate(content) {
  return (content || '').replace(/\{\{\s*date\s*\}\}/gi, new Date().toLocaleDateString());
}

function insertQuickPhrase(phrase) {
  getEditorInstance()?.commands?.insertContent(`${phrase} `);
  showQuickPhrases.value = false;
}

function applyTemplate(template) {
  const rendered = renderTemplate(template.template_content || '');

  if (isSoap.value) {
    const parsed = parseSoapTemplate(rendered);
    soapSections.forEach((section) => {
      setEditorContent(soapEditors[section], parsed[section] || '');
    });
  } else {
    setEditorContent(editor, rendered);
  }

  showTemplates.value = false;
}

function insertToothChip() {
  const toothNumber = prompt('Enter tooth number (1-32):');

  if (!toothNumber) {
    return;
  }

  const value = Number(toothNumber);
  if (Number.isNaN(value) || value < 1 || value > 32) {
    return;
  }

  getEditorInstance()?.commands?.insertToothChip(value);
}

function toggleQuickPhrases() {
  showQuickPhrases.value = !showQuickPhrases.value;
  showTemplates.value = false;
}

function toggleTemplates() {
  showTemplates.value = !showTemplates.value;
  showQuickPhrases.value = false;
}

function toggleVoice() {
  if (!voiceSupported.value) {
    return;
  }

  if (isVoiceActive.value) {
    recognition?.stop();
    isVoiceActive.value = false;
    return;
  }

  recognition = createSpeechRecognition(
    (text, isFinal) => {
      if (isFinal) {
        getEditorInstance()?.commands?.insertContent(`${text} `);
      }
    },
    () => {
      isVoiceActive.value = false;
    },
    (error) => {
      console.error('Speech error:', error);
      isVoiceActive.value = false;
    },
  );

  recognition?.start();
  isVoiceActive.value = true;
}

function startAutoSave() {
  autoSaveTimer = setInterval(async () => {
    if (noteId.value && !isLocked.value) {
      await saveNote(true);
    }
  }, 30000);
}

function close() {
  if (props.embedded) {
    return;
  }

  emit('update:visible', false);
  emit('closed');
}

function formatTime(date) {
  if (!date) {
    return '';
  }

  return new Date(date).toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  });
}

onMounted(() => {
  voiceSupported.value = isSpeechRecognitionAvailable();
  fetchTemplates();
  startAutoSave();
});

onBeforeUnmount(() => {
  if (autoSaveTimer) {
    clearInterval(autoSaveTimer);
  }
  recognition?.stop();
});
</script>

<template>
  <component
    :is="embedded ? 'div' : 'p-dialog'"
    v-bind="embedded ? {} : { visible, modal: true, maximizable: true, style: { width: '90vw', height: '85vh' }, header: 'Clinical Note', closable: true }"
    @update:visible="close"
    :class="{ 'note-editor-embedded': embedded }"
  >
    <div class="note-editor-layout" :class="{ 'h-full': embedded }">
      <div class="note-toolbar">
        <div class="flex items-center gap-3">
          <p-select
            v-model="noteType"
            :options="noteTypes"
            optionLabel="label"
            optionValue="value"
            class="w-36"
            :disabled="isLocked"
          />

          <div class="toolbar-group">
            <button
              @click="activeEditor?.chain().focus().toggleBold().run()"
              :class="{ active: activeEditor?.isActive('bold') }"
              class="tb-btn"
              title="Bold"
            >
              <Bold class="w-4 h-4" />
            </button>
            <button
              @click="activeEditor?.chain().focus().toggleItalic().run()"
              :class="{ active: activeEditor?.isActive('italic') }"
              class="tb-btn"
              title="Italic"
            >
              <Italic class="w-4 h-4" />
            </button>
            <button
              @click="activeEditor?.chain().focus().toggleUnderline().run()"
              :class="{ active: activeEditor?.isActive('underline') }"
              class="tb-btn"
              title="Underline"
            >
              <Underline class="w-4 h-4" />
            </button>
            <button
              @click="activeEditor?.chain().focus().toggleBulletList().run()"
              :class="{ active: activeEditor?.isActive('bulletList') }"
              class="tb-btn"
              title="Bullet List"
            >
              <List class="w-4 h-4" />
            </button>
            <button
              @click="activeEditor?.chain().focus().toggleOrderedList().run()"
              :class="{ active: activeEditor?.isActive('orderedList') }"
              class="tb-btn"
              title="Numbered List"
            >
              <ListOrdered class="w-4 h-4" />
            </button>
          </div>

          <div class="toolbar-group">
            <button @click="insertToothChip" class="tb-btn" title="Insert Tooth Number">
              <span class="text-xs font-bold">#T</span>
            </button>
            <button @click="toggleQuickPhrases" class="tb-btn" :class="{ active: showQuickPhrases }" title="Quick Phrases">
              <Sparkles class="w-4 h-4" />
            </button>
            <button @click="toggleTemplates" class="tb-btn" :class="{ active: showTemplates }" title="Templates">
              <FileText class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            v-if="voiceSupported"
            @click="toggleVoice"
            :class="['tb-btn', { 'voice-active': isVoiceActive }]"
            title="Voice Dictation"
          >
            <Mic v-if="!isVoiceActive" class="w-4 h-4" />
            <MicOff v-else class="w-4 h-4 text-red-500" />
          </button>

          <span v-if="lastSaved" class="text-xs text-green-500 flex items-center gap-1">
            <CheckCircle class="w-3 h-3" />
            Saved {{ formatTime(lastSaved) }}
          </span>
          <span v-if="isSaving" class="text-xs text-slate-400">Saving...</span>
        </div>
      </div>

      <div class="note-content-area">
        <div v-if="showQuickPhrases" class="quick-phrases-panel">
          <div class="panel-head">
            <h4 class="text-sm font-black text-slate-700">Quick Phrases</h4>
            <button @click="showQuickPhrases = false">
              <X class="w-4 h-4 text-slate-400" />
            </button>
          </div>
          <div class="overflow-y-auto max-h-[400px]">
            <div v-for="category in quickPhraseCategories" :key="category.name" class="border-b border-slate-100 last:border-0">
              <div class="panel-section-title">{{ category.name }}</div>
              <button
                v-for="(phrase, index) in category.phrases"
                :key="index"
                @click="insertQuickPhrase(phrase)"
                class="panel-item"
              >
                {{ phrase }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="showTemplates" class="templates-panel">
          <div class="panel-head">
            <h4 class="text-sm font-black text-slate-700">Templates</h4>
            <button @click="showTemplates = false">
              <X class="w-4 h-4 text-slate-400" />
            </button>
          </div>
          <div v-if="filteredTemplates.length" class="overflow-y-auto max-h-[400px]">
            <button
              v-for="template in filteredTemplates"
              :key="template.id"
              @click="applyTemplate(template)"
              class="template-item"
            >
              <div class="font-bold text-sm text-slate-700">{{ template.name }}</div>
              <div class="text-xs text-slate-400 line-clamp-2 mt-1">{{ template.template_content }}</div>
            </button>
          </div>
          <div v-else class="empty-side-panel">
            <p class="text-sm text-slate-400">No templates available for this note type.</p>
          </div>
        </div>

        <div v-if="isSoap" class="soap-sections">
          <div
            v-for="section in soapSections"
            :key="section"
            class="soap-section"
            @click="activeSoapSection = section"
          >
            <label class="soap-label">{{ soapLabels[section] }}</label>
            <div class="soap-editor-wrapper">
              <editor-content :editor="soapEditors[section]" class="tiptap-editor" />
            </div>
          </div>
        </div>

        <div v-else class="single-editor">
          <editor-content :editor="editor" class="tiptap-editor" />
        </div>
      </div>

      <div v-if="showAmendment" class="amendment-panel">
        <label class="field-label">Amendment</label>
        <p-textarea v-model="amendmentText" rows="3" class="w-full" placeholder="Document the amendment..." />
        <div class="flex justify-end gap-2 mt-3">
          <p-button label="Cancel" severity="secondary" text @click="showAmendment = false" />
          <p-button label="Save Amendment" icon="pi pi-check" @click="saveAmendment" />
        </div>
      </div>

      <div class="note-footer">
        <div class="flex items-center gap-2">
          <p-button v-if="!isLocked" label="Save" icon="pi pi-save" :loading="isSaving" @click="saveNote(false)" size="small" />
          <p-button v-if="!isSigned && noteId" label="Sign" severity="success" icon="pi pi-check" @click="signNote" size="small" outlined />
          <p-button v-if="!isLocked && noteId" label="Lock" severity="warning" icon="pi pi-lock" @click="lockNote" size="small" outlined />
          <p-button v-if="isLocked && noteId" label="Amend" severity="secondary" outlined size="small" @click="showAmendment = !showAmendment">
            <template #icon>
              <PencilLine class="w-4 h-4" />
            </template>
          </p-button>
        </div>

        <div class="flex items-center gap-2">
          <span v-if="note?.version" class="text-xs text-slate-400">Version {{ note.version }}</span>
          <p-tag v-if="isLocked" value="LOCKED" severity="warning" />
          <p-tag v-if="isSigned" value="SIGNED" severity="success" />
        </div>
      </div>
    </div>
  </component>
</template>

<style scoped>
@reference "tailwindcss/theme";
.note-editor-layout { @apply flex flex-col h-full; }
.note-toolbar { @apply flex items-center justify-between px-4 py-2 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex-shrink-0; }
.toolbar-group { @apply flex items-center gap-0.5 px-2 border-l border-slate-200 dark:border-slate-700; }
.tb-btn { @apply p-2 rounded-lg text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors; }
.tb-btn.active { @apply bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400; }
.tb-btn.voice-active { @apply bg-red-100 text-red-600 animate-pulse; }

.note-content-area { @apply flex-1 overflow-y-auto relative; }

.quick-phrases-panel,
.templates-panel {
  @apply absolute right-0 top-0 w-80 bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-700 shadow-xl z-20 h-full;
}

.panel-head { @apply flex items-center justify-between p-3 border-b border-slate-200; }
.panel-section-title { @apply px-3 py-2 text-xs font-black uppercase tracking-wider text-slate-400 bg-slate-50; }
.panel-item { @apply w-full text-left px-3 py-2 text-xs text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-colors; }
.template-item { @apply w-full text-left px-3 py-3 border-b border-slate-100 hover:bg-blue-50 transition-colors; }
.empty-side-panel { @apply flex items-center justify-center h-full px-6 text-center; }

.soap-sections { @apply p-4 space-y-4; }
.soap-section { @apply border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden; }
.soap-label { @apply block px-4 py-2 text-xs font-black uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700; }
.soap-editor-wrapper { @apply min-h-[80px]; }

.single-editor { @apply p-4 min-h-[300px]; }
.amendment-panel { @apply border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3; }
.field-label { @apply text-xs font-bold uppercase tracking-wider text-slate-500; }
.note-footer { @apply flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex-shrink-0; }

:deep(.tiptap-editor .tiptap) {
  @apply p-3 min-h-[80px] outline-none text-sm text-slate-700 dark:text-slate-200;
}

:deep(.tiptap-editor .tiptap p.is-editor-empty:first-child::before) {
  @apply text-slate-400 float-left h-0 pointer-events-none;
  content: attr(data-placeholder);
}

:deep(.tooth-chip) {
  @apply inline-flex items-center px-1.5 py-0.5 rounded-md bg-indigo-100 text-indigo-700 text-xs font-bold mx-0.5 cursor-pointer;
}

.note-editor-embedded { @apply h-full; }
</style>
