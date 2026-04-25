<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';
import { useScheduleStore } from '../../stores/schedule.store';
import { useAppStore } from '../../stores/app.store';
import { 
    Calendar, Users, Settings, Plus, 
    Printer, Target, ChevronLeft, ChevronRight,
    Search, Filter, Clock, MapPin
} from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import { formatCurrency } from '../../utils/formatters';

const scheduleStore = useScheduleStore();
const appStore = useAppStore();
const toast = useToast();

const fullCalendar = ref(null);
const opRef = ref(null); // OverlayPanel reference
const selectedEvent = ref(null);

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, resourceTimeGridPlugin],
    initialView: 'resourceTimeGridDay',
    headerToolbar: false, // Custom toolbar
    resources: [
        { id: 'op1', title: 'Operatory 1' },
        { id: 'op2', title: 'Operatory 2' },
        { id: 'op3', title: 'Operatory 3' },
        { id: 'op4', title: 'Operatory 4' }
    ],
    slotDuration: '00:15:00',
    snapDuration: '00:05:00',
    nowIndicator: true,
    allDaySlot: false,
    editable: true,
    selectable: true,
    dayMaxEvents: true,
    height: 'auto',
    businessHours: {
        daysOfWeek: [1, 2, 3, 4, 5],
        startTime: '08:00',
        endTime: '17:00',
    },
    events: computed(() => scheduleStore.events),
    
    // Interactions
    eventClick: (info) => {
        selectedEvent.value = info.event;
        opRef.value.toggle(info.el);
    },
    select: (info) => {
        // Open New Appointment Modal
        toast.info(`Scheduling new appointment for ${info.startStr}`);
    },
    eventDrop: async (info) => {
        try {
            await scheduleStore.updateAppointment(info.event.id, {
                start_time: info.event.start,
                end_time: info.event.end,
                operatory_id: info.event.getResources()[0]?.id
            });
            toast.success('Appointment rescheduled');
        } catch (err) {
            info.revert();
            toast.error('Failed to reschedule');
        }
    }
});

const changeView = (view) => {
    fullCalendar.value.getApi().changeView(view);
};

const next = () => fullCalendar.value.getApi().next();
const prev = () => fullCalendar.value.getApi().prev();
const today = () => fullCalendar.value.getApi().today();

const productionGoal = 15000;
const currentProduction = 8450;
const productionPercent = computed(() => (currentProduction / productionGoal) * 100);

onMounted(() => {
    // Listen for real-time updates (only if Echo is available)
    if (window.Echo && appStore.currentLocationId) {
        window.Echo.private(`location.${appStore.currentLocationId}`)
            .listen('.appointment.updated', (e) => {
                scheduleStore.fetchSchedule();
                toast.info(`Schedule updated by ${e.user.first_name}`);
            });
    }
});
</script>

<template>
    <div class="h-full flex flex-col gap-6 animate-in fade-in duration-500">
        
        <!-- Top Toolbar -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-6">
                <!-- Navigation -->
                <div class="flex items-center gap-2">
                    <button @click="prev" class="p-2 hover:bg-slate-100 rounded-xl transition-colors"><ChevronLeft class="w-5 h-5 text-slate-500" /></button>
                    <button @click="today" class="px-4 py-2 text-sm font-bold text-primary-600 hover:bg-primary-50 rounded-xl transition-colors">Today</button>
                    <button @click="next" class="p-2 hover:bg-slate-100 rounded-xl transition-colors"><ChevronRight class="w-5 h-5 text-slate-500" /></button>
                </div>
                
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight min-w-[200px]">
                    {{ new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric', day: 'numeric' }) }}
                </h2>

                <!-- View Switcher -->
                <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                    <button @click="changeView('resourceTimeGridDay')" class="view-btn active">Day</button>
                    <button @click="changeView('timeGridWeek')" class="view-btn">Week</button>
                    <button @click="changeView('dayGridMonth')" class="view-btn">Month</button>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Production Tracker -->
                <div class="hidden sm:flex items-center gap-4 bg-slate-50 dark:bg-slate-800/50 px-6 py-2.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="text-right">
                        <p class="text-[9px] uppercase tracking-widest text-slate-400 font-black">Daily Goal</p>
                        <p class="text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(currentProduction) }} / {{ formatCurrency(productionGoal) }}</p>
                    </div>
                    <div class="w-32">
                        <p-progress-bar :value="productionPercent" :showValue="false" class="h-2 rounded-full" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <p-button icon="pi pi-print" class="p-button-outlined p-button-rounded p-button-secondary" v-tooltip="'Print Day Sheet'" />
                    <button class="flex items-center gap-2 px-6 py-2.5 bg-[#1A3C5E] text-white rounded-xl font-bold shadow-lg shadow-primary-500/20 hover:bg-[#15304b] transition-all active:scale-95">
                        <Plus class="w-4 h-4" />
                        New Appointment
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 flex gap-6 min-h-0">
            <!-- Sidebar Filters -->
            <aside class="w-72 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm p-6 overflow-y-auto custom-scrollbar">
                <div class="space-y-8">
                    <!-- Provider Filter -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <Users class="w-4 h-4" />
                            Providers
                        </h3>
                        <div class="space-y-2">
                            <div v-for="i in 4" :key="i" class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-xl cursor-pointer group">
                                <div class="flex items-center gap-3">
                                    <p-avatar label="DW" shape="circle" class="bg-primary-100 text-primary-600 text-xs font-bold" />
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Dr. Sarah Wilson</span>
                                </div>
                                <p-checkbox binary :modelValue="true" />
                            </div>
                        </div>
                    </div>

                    <!-- Operatory Filter -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <MapPin class="w-4 h-4" />
                            Operatories
                        </h3>
                        <div class="space-y-2">
                            <div v-for="i in 5" :key="i" class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-xl">
                                <span class="text-sm font-bold text-slate-700">Operatory {{ i }}</span>
                                <p-checkbox binary :modelValue="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Calendar Area -->
            <div class="flex-1 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-2xl overflow-hidden relative">
                <FullCalendar 
                    ref="fullCalendar"
                    :options="calendarOptions"
                    class="h-full dental-calendar"
                />

                <!-- Quick Legend -->
                <div class="absolute bottom-6 right-6 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md px-4 py-2 rounded-xl border border-slate-200 shadow-xl flex items-center gap-4 z-10">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500"></span><span class="text-[10px] font-bold uppercase">Confirmed</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span><span class="text-[10px] font-bold uppercase">Checked In</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-500"></span><span class="text-[10px] font-bold uppercase">In Chair</span></div>
                </div>
            </div>
        </div>

        <!-- Appointment Popover -->
        <p-popover ref="opRef" class="p-0">
            <div v-if="selectedEvent" class="w-72 overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200">
                <div class="bg-primary-500 p-4 text-white">
                    <p class="text-xs font-bold uppercase tracking-widest opacity-80">Patient Name</p>
                    <h3 class="text-lg font-black leading-tight">{{ selectedEvent.title }}</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <Clock class="w-4 h-4 text-slate-400" />
                        <span class="text-sm font-bold text-slate-700">{{ selectedEvent.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }} - {{ selectedEvent.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="pop-btn bg-blue-50 text-blue-600">Check In</button>
                        <button class="pop-btn bg-green-50 text-green-600">Complete</button>
                        <button class="pop-btn bg-red-50 text-red-600">No Show</button>
                        <button class="pop-btn bg-slate-50 text-slate-600">View Hub</button>
                    </div>
                </div>
            </div>
        </p-popover>
    </div>
</template>

<style scoped>
@reference "../../../css/app.css";
.view-btn {
    @apply px-4 py-1.5 text-xs font-black uppercase tracking-wider text-slate-500 rounded-lg transition-all;
}

.view-btn.active {
    @apply bg-white dark:bg-slate-700 text-primary-600 shadow-sm;
}

.pop-btn {
    @apply py-2 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all hover:scale-[1.02] active:scale-[0.98];
}

:deep(.dental-calendar) {
    --fc-border-color: rgba(226, 232, 240, 0.5);
    --fc-today-bg-color: transparent;
    --fc-now-indicator-color: #ef4444;
}

:deep(.fc-resource-timeline-divider) {
    display: none;
}

:deep(.fc-timegrid-slot) {
    @apply h-12;
}

:deep(.fc-event) {
    @apply rounded-lg border-none shadow-sm cursor-pointer transition-transform active:scale-[0.98];
}

:deep(.fc-event-main) {
    @apply p-1.5;
}

:deep(.fc-v-event) {
    @apply bg-primary-500;
}
</style>
