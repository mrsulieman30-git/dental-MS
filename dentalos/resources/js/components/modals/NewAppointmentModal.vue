<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { usePatients } from '../../composables/usePatients';
import { useAppointments } from '../../composables/useAppointments';
import { 
    Calendar, User, Clock, MapPin, 
    AlertTriangle, ShieldCheck, Plus, X, Search 
} from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import { formatPatientNumber, formatDate, formatCurrency } from '../../utils/formatters';

const props = defineProps({
    visible: Boolean,
    initialData: Object // e.g. { start, operatoryId }
});

const emit = defineEmits(['update:visible', 'saved']);

const toast = useToast();
const { fetchPatients } = usePatients();
const { createAppointment } = useAppointments();

const form = reactive({
    patient: null,
    appointment_type_id: null,
    provider_id: null,
    operatory_id: null,
    start_time: null,
    duration: 30,
    notes: '',
    internal_notes: '',
    send_confirmation: true,
    linked_procedures: []
});

const patientResults = ref([]);
const searchPatients = async (event) => {
    const response = await fetchPatients({ search: event.query });
    patientResults.value = response.data;
};

const providers = ref([
    { id: 1, name: 'Dr. Sarah Wilson', status: 'available' },
    { id: 2, name: 'Dr. Mike Ross', status: 'limited' }
]);

const apptTypes = ref([
    { id: 1, name: 'Periodic Exam', duration: 40, color: '#10b981' },
    { id: 2, name: 'Prophy Adult', duration: 60, color: '#3b82f6' },
    { id: 3, name: 'Emergency', duration: 30, color: '#ef4444' }
]);

const doubleBookingWarning = ref(false);

const onSave = async () => {
    try {
        const payload = {
            ...form,
            patient_id: form.patient?.id,
            start_time: form.start_time
        };
        await createAppointment(payload);
        toast.success('Appointment scheduled');
        emit('saved');
        emit('update:visible', false);
    } catch (err) {
        toast.error('Failed to schedule appointment');
    }
};

watch(() => props.initialData, (newVal) => {
    if (newVal) {
        if (newVal.start) form.start_time = newVal.start;
        if (newVal.operatoryId) form.operatory_id = newVal.operatoryId;
    }
}, { immediate: true });
</script>

<template>
    <p-dialog 
        :visible="visible" 
        @update:visible="emit('update:visible', $event)"
        header="Schedule New Appointment" 
        modal 
        :style="{ width: '700px' }" 
        class="p-0 overflow-hidden rounded-3xl"
    >
        <div class="space-y-6 py-4">
            <!-- Double Booking Warning -->
            <div v-if="doubleBookingWarning" class="bg-amber-50 border border-amber-200 p-4 rounded-2xl flex items-start gap-3">
                <AlertTriangle class="w-5 h-5 text-amber-500 mt-0.5" />
                <div>
                    <h4 class="text-sm font-bold text-amber-900">Double Booking Warning</h4>
                    <p class="text-xs text-amber-700">The selected provider already has an appointment at this time. Admin override required.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Patient Search -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Patient Search</label>
                    <p-auto-complete 
                        v-model="form.patient" 
                        :suggestions="patientResults" 
                        @complete="searchPatients" 
                        field="full_name" 
                        class="w-full"
                        placeholder="Search by name, ID, or phone..."
                        forceSelection
                    >
                        <template #item="{ item }">
                            <div class="flex items-center gap-3 p-1">
                                <p-avatar :label="item.first_name[0] + item.last_name[0]" shape="circle" class="bg-primary-100 text-primary-600 font-bold" />
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ item.full_name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ formatDate(item.dob) }} • {{ item.phone }}</p>
                                </div>
                            </div>
                        </template>
                        <template #footer>
                            <div class="p-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                                <span class="text-xs text-slate-500">Not found?</span>
                                <button class="text-primary-600 text-xs font-bold hover:underline">+ Create New Patient</button>
                            </div>
                        </template>
                    </p-auto-complete>
                </div>

                <!-- Appointment Type -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Appointment Type</label>
                    <p-select v-model="form.appointment_type_id" :options="apptTypes" optionLabel="name" optionValue="id" class="w-full">
                        <template #option="{ option }">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: option.color }"></span>
                                <span class="text-sm font-medium">{{ option.name }}</span>
                            </div>
                        </template>
                    </p-select>
                </div>

                <!-- Provider -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Provider</label>
                    <p-select v-model="form.provider_id" :options="providers" optionLabel="name" optionValue="id" class="w-full">
                        <template #option="{ option }">
                            <div class="flex items-center gap-2">
                                <div :class="[
                                    'w-2 h-2 rounded-full',
                                    option.status === 'available' ? 'bg-green-500' : 
                                    option.status === 'limited' ? 'bg-amber-500' : 'bg-slate-300'
                                ]"></div>
                                <span class="text-sm font-medium">{{ option.name }}</span>
                            </div>
                        </template>
                    </p-select>
                </div>

                <!-- Date & Time -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Date & Time</label>
                    <p-calendar v-model="form.start_time" showTime hourFormat="12" class="w-full" />
                </div>

                <!-- Duration -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Duration (Minutes)</label>
                    <p-input-number v-model="form.duration" class="w-full" suffix=" mins" showButtons :min="5" :step="5" />
                </div>
            </div>

            <!-- Notes -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Patient Notes</label>
                    <p-textarea v-model="form.notes" rows="2" class="w-full" placeholder="Seen on patient portal/reminder..." />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Internal Office Notes</label>
                    <p-textarea v-model="form.internal_notes" rows="2" class="w-full bg-amber-50/30 border-amber-100" placeholder="Needs medical consult before procedure..." />
                </div>
            </div>

            <!-- Insurance / Treatment Plan Summary (Mock) -->
            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 flex items-start gap-4">
                <ShieldCheck class="w-6 h-6 text-blue-500 mt-1" />
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="text-sm font-bold text-blue-900">Insurance Estimation</h4>
                        <span class="text-xs text-blue-700 font-bold">Aetna PPO Active</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] uppercase text-blue-400 font-bold">Estimated Patient Portion</p>
                            <p class="text-lg font-black text-blue-900">{{ formatCurrency(45.00) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase text-blue-400 font-bold">Benefits Remaining</p>
                            <p class="text-sm font-bold text-blue-900">{{ formatCurrency(1250.00) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4">
                <div class="flex items-center gap-2">
                    <p-checkbox v-model="form.send_confirmation" binary />
                    <span class="text-sm text-slate-500 font-medium">Send SMS/Email Confirmation</span>
                </div>
                <div class="flex items-center gap-3">
                    <p-button label="Cancel" class="p-button-text p-button-secondary" @click="emit('update:visible', false)" />
                    <button 
                        @click="onSave"
                        class="px-8 py-3 bg-[#1A3C5E] text-white rounded-xl font-bold shadow-lg shadow-primary-500/20 hover:bg-[#15304b] transition-all active:scale-95"
                    >
                        Schedule Appointment
                    </button>
                </div>
            </div>
        </div>
    </p-dialog>
</template>

<style scoped>
@reference "../../../css/app.css";
:deep(.p-autocomplete-input), :deep(.p-select), :deep(.p-inputnumber-input), :deep(.p-calendar .p-inputtext), :deep(.p-textarea) {
    @apply bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 p-3;
}

:deep(.p-dialog-content) {
    @apply p-6;
}
</style>
