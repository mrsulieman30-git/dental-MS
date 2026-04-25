<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useClinicalStore } from '../../stores/clinical.store';
import OdontogramChart from '../../components/clinical/odontogram/OdontogramChart.vue';
import NoteEditor from '../../components/clinical/notes/NoteEditor.vue';
import { formatDate, formatCurrency } from '../../utils/formatters.js';
import axios from 'axios';
import {
  User, Calendar, AlertTriangle, Activity, Heart,
  CheckCircle, Maximize, Minimize, HelpCircle, Search,
  Plus, Image as ImageIcon, ClipboardList, X
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const clinicalStore = useClinicalStore();

const appointment = ref(null);
const patient = ref(null);
const procedures = ref([]);
const recentImages = ref([]);
const perioSummary = ref(null);
const isFullscreen = ref(false);
const showShortcuts = ref(false);
const loading = ref(true);

// Quick charge
const cdtSearch = ref('');
const quickChargeTooth = ref(null);
const quickChargeFee = ref(0);

onMounted(async () => {
  await loadData();
  document.addEventListener('keydown', onKeyDown);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKeyDown);
});

async function loadData() {
  loading.value = true;
  try {
    const apptId = route.params.appointmentId;
    // Load appointment
    const { data: apptData } = await axios.get(`/appointments/${apptId}`);
    appointment.value = apptData.data;
    patient.value = apptData.data?.patient;

    if (patient.value?.id) {
      // Load chart
      await clinicalStore.fetchChart(patient.value.id);
      // Load recent images
      try {
        const { data: imgData } = await axios.get(`/imaging/patients/${patient.value.id}/series`);
        recentImages.value = (imgData.data || []).slice(0, 6);
      } catch {}
      // Load perio summary
      try {
        const { data: perioData } = await axios.get(`/clinical/patients/${patient.value.id}/perio`);
        perioSummary.value = (perioData.data || [])[0] || null;
      } catch {}
    }
  } catch (err) {
    console.error('Failed to load chairside data', err);
  } finally {
    loading.value = false;
  }
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
    isFullscreen.value = true;
  } else {
    document.exitFullscreen();
    isFullscreen.value = false;
  }
}

function onKeyDown(e) {
  if (e.key === '?' && !e.ctrlKey) showShortcuts.value = !showShortcuts.value;
  if (e.key === 'F11') { e.preventDefault(); toggleFullscreen(); }
  if (e.key === 'Escape' && showShortcuts.value) showShortcuts.value = false;
}

async function completeAppointment() {
  if (!appointment.value?.id) return;
  if (!confirm('Complete this appointment? This will finalize all posted charges.')) return;
  try {
    await axios.patch(`/appointments/${appointment.value.id}/status`, { status: 'completed' });
    router.push('/schedule');
  } catch (err) {
    console.error('Failed to complete appointment', err);
  }
}

async function postCharge() {
  if (!cdtSearch.value) return;
  procedures.value.push({
    id: Date.now(),
    cdt_code: cdtSearch.value,
    tooth: quickChargeTooth.value,
    fee: quickChargeFee.value,
    posted: true,
  });
  cdtSearch.value = '';
  quickChargeTooth.value = null;
  quickChargeFee.value = 0;
}

const shortcuts = [
  { key: '?', desc: 'Toggle this help panel' },
  { key: 'F11', desc: 'Toggle fullscreen' },
  { key: 'Ctrl+S', desc: 'Save current note' },
  { key: 'Esc', desc: 'Close dialogs' },
];
</script>

<template>
  <div class="chairside-view">
    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center h-full">
      <div class="text-center">
        <div class="w-12 h-12 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin mx-auto mb-4"></div>
        <p class="text-sm text-slate-400">Loading chairside view...</p>
      </div>
    </div>

    <template v-else>
      <!-- Top Bar -->
      <div class="chairside-header">
        <div class="flex items-center gap-3">
          <button @click="router.back()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
            <X class="w-5 h-5 text-white/70" />
          </button>
          <div>
            <h1 class="text-lg font-black text-white">{{ patient?.full_name || 'Patient' }}</h1>
            <div class="text-xs text-white/50">{{ appointment?.appointment_type?.name || 'Appointment' }} · {{ formatDate(appointment?.start_time) }}</div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button @click="showShortcuts = !showShortcuts" class="header-btn"><HelpCircle class="w-4 h-4" /></button>
          <button @click="toggleFullscreen" class="header-btn">
            <Maximize v-if="!isFullscreen" class="w-4 h-4" /><Minimize v-else class="w-4 h-4" />
          </button>
          <button @click="completeAppointment" class="complete-btn">
            <CheckCircle class="w-4 h-4" /> Complete Appointment
          </button>
        </div>
      </div>

      <!-- 4 Panel Grid -->
      <div class="chairside-grid">
        <!-- Top Left: Patient + Odontogram -->
        <div class="panel panel-tl">
          <div class="panel-header">
            <User class="w-4 h-4" /> <span>Chart</span>
          </div>
          <div class="panel-body">
            <!-- Alerts -->
            <div v-if="patient?.alerts?.length" class="mb-3 space-y-1">
              <div v-for="alert in (patient.alerts || []).slice(0,2)" :key="alert.id" class="flex items-center gap-2 px-2 py-1 bg-red-50 rounded-lg text-xs text-red-600 font-bold">
                <AlertTriangle class="w-3 h-3" /> {{ alert.message }}
              </div>
            </div>
            <OdontogramChart
              :modelValue="clinicalStore.chartData"
              mode="chart" :readonly="false"
              :notation="clinicalStore.notation"
              :selectedTooth="clinicalStore.selectedTooth"
              compact
              @tooth-selected="clinicalStore.selectTooth($event)"
            />
          </div>
        </div>

        <!-- Top Right: Note Editor -->
        <div class="panel panel-tr">
          <div class="panel-header">
            <ClipboardList class="w-4 h-4" /> <span>Clinical Note</span>
          </div>
          <div class="panel-body overflow-hidden">
            <NoteEditor
              :patientId="patient?.id"
              :appointmentId="appointment?.id"
              :visible="true"
              embedded
            />
          </div>
        </div>

        <!-- Bottom Left: Procedures -->
        <div class="panel panel-bl">
          <div class="panel-header">
            <Activity class="w-4 h-4" /> <span>Today's Procedures</span>
          </div>
          <div class="panel-body">
            <div v-if="procedures.length" class="space-y-2 mb-3">
              <div v-for="proc in procedures" :key="proc.id" class="flex items-center justify-between p-2 bg-slate-50 dark:bg-slate-800 rounded-lg">
                <div>
                  <span class="text-xs font-bold text-slate-700">{{ proc.cdt_code }}</span>
                  <span v-if="proc.tooth" class="text-xs text-slate-400 ml-2">#{{ proc.tooth }}</span>
                </div>
                <span class="text-xs font-bold text-green-600">{{ formatCurrency(proc.fee) }}</span>
              </div>
            </div>
            <div v-else class="text-center py-4 text-xs text-slate-400">No procedures posted yet</div>

            <!-- Quick Charge -->
            <div class="quick-charge">
              <h4 class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Quick Charge</h4>
              <div class="flex gap-2">
                <input v-model="cdtSearch" placeholder="CDT Code" class="quick-input flex-1" />
                <input v-model.number="quickChargeTooth" type="number" placeholder="#" class="quick-input w-12" min="1" max="32" />
                <input v-model.number="quickChargeFee" type="number" placeholder="Fee" class="quick-input w-20" step="0.01" />
                <button @click="postCharge" class="px-3 py-1 bg-blue-500 text-white rounded-lg text-xs font-bold hover:bg-blue-600 transition-colors">Post</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Right: Images + Perio -->
        <div class="panel panel-br">
          <div class="panel-header">
            <ImageIcon class="w-4 h-4" /> <span>Images & Perio</span>
          </div>
          <div class="panel-body">
            <!-- Recent Images -->
            <h4 class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Recent Images</h4>
            <div v-if="recentImages.length" class="grid grid-cols-3 gap-2 mb-4">
              <div v-for="s in recentImages" :key="s.id" class="aspect-square rounded-lg bg-slate-200 overflow-hidden">
                <img v-if="s.thumbnail_path" :src="s.thumbnail_path" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <ImageIcon class="w-6 h-6 text-slate-400" />
                </div>
              </div>
            </div>
            <div v-else class="text-xs text-slate-400 mb-4">No recent images</div>

            <!-- Perio Summary -->
            <h4 class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Last Perio Chart</h4>
            <div v-if="perioSummary" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
              <div class="text-center">
                <div class="text-lg font-black text-blue-600">{{ perioSummary.aap_stage || '-' }}</div>
                <div class="text-[9px] text-slate-400">Stage</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-black text-purple-600">{{ perioSummary.aap_grade || '-' }}</div>
                <div class="text-[9px] text-slate-400">Grade</div>
              </div>
              <div class="text-xs text-slate-400">{{ formatDate(perioSummary.chart_date) }}</div>
            </div>
            <div v-else class="text-xs text-slate-400">No perio chart on file</div>
          </div>
        </div>
      </div>

      <!-- Keyboard Shortcuts Dialog -->
      <div v-if="showShortcuts" class="shortcuts-overlay" @click="showShortcuts = false">
        <div class="shortcuts-panel" @click.stop>
          <h3 class="text-sm font-black text-slate-700 mb-3">Keyboard Shortcuts</h3>
          <div v-for="s in shortcuts" :key="s.key" class="flex items-center justify-between py-1.5">
            <span class="text-xs text-slate-500">{{ s.desc }}</span>
            <kbd class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-mono font-bold text-slate-600">{{ s.key }}</kbd>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
@reference "tailwindcss/theme";
.chairside-view { @apply h-screen flex flex-col bg-slate-900 overflow-hidden; }
.chairside-header { @apply flex items-center justify-between px-4 py-2 bg-slate-800 border-b border-slate-700 flex-shrink-0; }
.header-btn { @apply p-2 text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition-colors; }
.complete-btn { @apply flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded-xl text-xs font-bold hover:bg-green-600 transition-colors; }

.chairside-grid { @apply flex-1 grid grid-cols-2 grid-rows-2 gap-1 p-1 min-h-0; }
.panel { @apply bg-white dark:bg-slate-800 rounded-xl overflow-hidden flex flex-col; }
.panel-header { @apply flex items-center gap-2 px-3 py-2 text-xs font-black text-slate-500 uppercase tracking-wider bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 flex-shrink-0; }
.panel-body { @apply flex-1 overflow-y-auto p-3; }

.quick-charge { @apply p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700; }
.quick-input { @apply px-2 py-1.5 rounded-lg border border-slate-200 text-xs outline-none focus:ring-2 focus:ring-blue-300; }
.quick-input::-webkit-outer-spin-button, .quick-input::-webkit-inner-spin-button { -webkit-appearance: none; }

.shortcuts-overlay { @apply fixed inset-0 z-50 bg-black/50 flex items-center justify-center backdrop-blur-sm; }
.shortcuts-panel { @apply bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 w-80; }
</style>