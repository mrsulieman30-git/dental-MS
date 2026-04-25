<script setup>
import { ref, reactive } from 'vue';
import { 
    Calendar, User, Clock, MapPin, 
    ArrowRight, CheckCircle2, Shield,
    ArrowLeft, CalendarCheck2
} from 'lucide-vue-next';
import { useToast } from 'vue-toastification';

const activeStep = ref(1);
const toast = useToast();

const bookingForm = reactive({
    location_id: null,
    appointment_type_id: null,
    provider_id: null,
    date: null,
    time_slot: null,
    patient_info: { first_name: '', last_name: '', phone: '', email: '' }
});

const nextStep = () => activeStep.value++;
const prevStep = () => activeStep.value--;

const locations = [
    { id: 1, name: 'Main Street Dental', address: '123 Main St, Anytown' },
    { id: 2, name: 'Westside Clinic', address: '456 West Blvd, Anytown' }
];

const submitBooking = () => {
    toast.success('Your appointment request has been submitted!');
    activeStep.value = 6; // Success step
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col items-center p-6 sm:p-12">
        <!-- Public Branding -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#1A3C5E] rounded-2xl shadow-xl mb-4">
                <Shield class="w-8 h-8 text-white" />
            </div>
            <h1 class="text-3xl font-black text-[#1A3C5E] tracking-tight">DentalOS</h1>
            <p class="text-slate-500 font-medium">Request your appointment online</p>
        </div>

        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
            <!-- Progress Bar -->
            <div class="h-1 bg-slate-100 flex">
                <div 
                    class="h-full bg-primary-500 transition-all duration-500" 
                    :style="{ width: `${(activeStep / 5) * 100}%` }"
                ></div>
            </div>

            <div class="p-8 sm:p-10">
                <transition name="fade-slide" mode="out-in">
                    <!-- Step 1: Location -->
                    <div v-if="activeStep === 1" key="step1" class="space-y-6">
                        <h2 class="text-2xl font-bold text-slate-900">Choose a Location</h2>
                        <div class="space-y-4">
                            <div 
                                v-for="loc in locations" :key="loc.id"
                                @click="bookingForm.location_id = loc.id; nextStep()"
                                class="p-6 border-2 rounded-2xl cursor-pointer transition-all hover:border-primary-500 hover:bg-primary-50 group"
                                :class="bookingForm.location_id === loc.id ? 'border-primary-500 bg-primary-50' : 'border-slate-100'"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-bold text-lg text-slate-900 group-hover:text-primary-700">{{ loc.name }}</h3>
                                        <p class="text-sm text-slate-500 flex items-center gap-2 mt-1">
                                            <MapPin class="w-4 h-4" />
                                            {{ loc.address }}
                                        </p>
                                    </div>
                                    <ArrowRight class="w-5 h-5 text-slate-300 group-hover:text-primary-500" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Success -->
                    <div v-else-if="activeStep === 6" key="success" class="text-center py-10 space-y-6">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                            <CheckCircle2 class="w-10 h-10 text-green-600" />
                        </div>
                        <h2 class="text-3xl font-black text-slate-900">Thank You!</h2>
                        <div class="bg-slate-50 p-6 rounded-3xl text-sm text-slate-600 max-w-sm mx-auto">
                            <p>We've received your request for <strong>Main Street Dental</strong>. Our team will review and confirm your appointment shortly.</p>
                        </div>
                        <div class="flex flex-col items-center gap-4 pt-4">
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Added to your calendar?</p>
                            <button class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold text-sm hover:bg-slate-200 transition-all">Add to iCal / Google</button>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Footer Navigation -->
            <div v-if="activeStep > 1 && activeStep < 6" class="p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <button @click="prevStep" class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-700">
                    <ArrowLeft class="w-4 h-4" />
                    Back
                </button>
                <button @click="nextStep" class="flex items-center gap-2 px-8 py-3 bg-[#1A3C5E] text-white rounded-xl font-bold hover:bg-[#15304b] transition-all">
                    Continue
                    <ArrowRight class="w-4 h-4" />
                </button>
            </div>
        </div>

        <p class="mt-8 text-sm text-slate-400 font-medium">Powered by DentalOS Enterprise</p>
    </div>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(20px);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}
</style>
