<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { usePatients } from '../../composables/usePatients';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { 
    User, Phone, ShieldCheck, HeartPulse, 
    ArrowRight, ArrowLeft, Check, AlertCircle,
    Plus, X, Search
} from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import { formatAge } from '../../utils/formatters';

const router = useRouter();
const toast = useToast();
const { createPatient, fetchPatients } = usePatients();

const activeStep = ref(0);
const steps = [
    { label: 'Demographics', icon: User },
    { label: 'Contact', icon: Phone },
    { label: 'Insurance', icon: ShieldCheck },
    { label: 'Responsible', icon: User },
    { label: 'Medical', icon: HeartPulse },
    { label: 'Review', icon: Check }
];

const patientForm = reactive({
    // Step 1: Demographics
    first_name: '',
    last_name: '',
    middle_name: '',
    preferred_name: '',
    dob: null,
    gender: 'other',
    pronouns: '',
    avatar_url: null,

    // Step 2: Contact
    phones: [{ type: 'mobile', number: '', primary: true, sms: true }],
    emails: [{ type: 'personal', address: '', primary: true }],
    address: { line1: '', line2: '', city: '', state: '', zip: '' },
    comm_preference: 'email',

    // Step 3: Insurance
    has_insurance: false,
    insurance: { carrier: '', plan_name: '', subscriber_id: '', group_id: '', relationship: 'self' },

    // Step 4: Responsible Party
    is_self_responsible: true,
    guardian: { first_name: '', last_name: '', phone: '', email: '' },

    // Step 5: Medical
    conditions: [],
    allergies: [],
    medications: [],
    asa_class: '1'
});

const conditionsList = [
    'Diabetes', 'Hypertension', 'Heart Disease', 'Bleeding Disorder', 
    'Asthma', 'Epilepsy', 'Arthritis', 'HIV/AIDS', 'Hepatitis'
];

const nextStep = () => {
    if (activeStep.value < steps.length - 1) activeStep.value++;
};

const prevStep = () => {
    if (activeStep.value > 0) activeStep.value--;
};

const addPhone = () => patientForm.phones.push({ type: 'mobile', number: '', primary: false, sms: false });
const removePhone = (index) => patientForm.phones.splice(index, 1);

const addAllergy = () => patientForm.allergies.push({ allergen: '', type: 'allergy', severity: 'mild' });
const removeAllergy = (index) => patientForm.allergies.splice(index, 1);

const savePatient = async () => {
    try {
        // Mock duplicate check
        // const dupes = await api.post('/patients/check-duplicate', { name: ..., dob: ... });
        
        await createPatient(patientForm);
        toast.success('Patient registered successfully');
        router.push({ name: 'patients.index' });
    } catch (err) {
        toast.error('Failed to create patient');
    }
};

// Auto-save to sessionStorage
watch(patientForm, (newVal) => {
    sessionStorage.setItem('new_patient_draft', JSON.stringify(newVal));
}, { deep: true });

onMounted(() => {
    const draft = sessionStorage.getItem('new_patient_draft');
    if (draft) Object.assign(patientForm, JSON.parse(draft));
});
</script>

<template>
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">New Patient Registration</h1>
                <p class="text-sm text-slate-500">Step {{ activeStep + 1 }}: {{ steps[activeStep].label }}</p>
            </div>
            <p-button icon="pi pi-times" class="p-button-text p-button-rounded p-button-secondary" @click="router.back()" />
        </div>

        <!-- Steps Progress -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm overflow-x-auto">
            <div class="flex items-center justify-between min-w-[600px]">
                <div 
                    v-for="(step, index) in steps" 
                    :key="index"
                    class="flex flex-col items-center gap-2 relative z-10 group cursor-pointer"
                    @click="activeStep = index"
                >
                    <div 
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300',
                            activeStep === index ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30 scale-110' : 
                            activeStep > index ? 'bg-green-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'
                        ]"
                    >
                        <component :is="step.icon" class="w-5 h-5" />
                    </div>
                    <span 
                        :class="[
                            'text-xs font-bold uppercase tracking-wider',
                            activeStep === index ? 'text-primary-600' : 'text-slate-400'
                        ]"
                    >
                        {{ step.label }}
                    </span>
                    
                    <!-- Connector line -->
                    <div v-if="index < steps.length - 1" class="absolute left-full top-5 w-[calc(100%-40px)] h-0.5 bg-slate-100 dark:bg-slate-800 -z-10 mx-5"></div>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-2xl p-8 sm:p-10 min-h-[500px]">
            <transition name="fade-slide" mode="out-in">
                <!-- Step 1: Demographics -->
                <div v-if="activeStep === 0" key="step0" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">First Name</label>
                            <input v-model="patientForm.first_name" type="text" class="w-full p-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-primary-500" placeholder="John" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Middle Name</label>
                            <input v-model="patientForm.middle_name" type="text" class="w-full p-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-primary-500" placeholder="Quincy" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Last Name</label>
                            <input v-model="patientForm.last_name" type="text" class="w-full p-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-primary-500" placeholder="Doe" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Date of Birth</label>
                            <div class="flex items-center gap-4">
                                <p-calendar v-model="patientForm.dob" class="flex-1" dateFormat="mm/dd/yy" placeholder="Select DOB" />
                                <div v-if="patientForm.dob" class="px-4 py-2 bg-primary-50 text-primary-600 rounded-xl font-bold text-sm">
                                    {{ formatAge(patientForm.dob) }} yrs
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Preferred Pronouns</label>
                            <p-select v-model="patientForm.pronouns" :options="['He/Him', 'She/Her', 'They/Them', 'Other']" class="w-full" />
                        </div>
                    </div>
                </div>

                <!-- Step 2: Contact Info -->
                <div v-else-if="activeStep === 1" key="step1" class="space-y-8">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900">Phone Numbers</h3>
                            <button @click="addPhone" class="text-primary-600 text-sm font-bold flex items-center"><Plus class="w-4 h-4 mr-1" /> Add</button>
                        </div>
                        <div v-for="(phone, index) in patientForm.phones" :key="index" class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-2xl relative">
                            <p-select v-model="phone.type" :options="['mobile', 'home', 'work']" class="sm:col-span-1" />
                            <input v-model="phone.number" type="tel" placeholder="(000) 000-0000" class="sm:col-span-2 p-2 bg-transparent border-none focus:ring-0 text-sm" />
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <p-checkbox v-model="phone.sms" binary />
                                    <span class="text-xs text-slate-500">SMS</span>
                                </div>
                                <button v-if="index > 0" @click="removePhone(index)" class="text-red-400 hover:text-red-600"><X class="w-4 h-4" /></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Medical History -->
                <div v-else-if="activeStep === 4" key="step4" class="space-y-8">
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-slate-900">Past Medical Conditions</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div 
                                v-for="cond in conditionsList" 
                                :key="cond"
                                @click="patientForm.conditions.includes(cond) ? patientForm.conditions.splice(patientForm.conditions.indexOf(cond), 1) : patientForm.conditions.push(cond)"
                                :class="[
                                    'p-4 rounded-2xl border-2 transition-all cursor-pointer flex items-center justify-between',
                                    patientForm.conditions.includes(cond) ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-100 text-slate-600'
                                ]"
                            >
                                <span class="text-sm font-bold">{{ cond }}</span>
                                <Check v-if="patientForm.conditions.includes(cond)" class="w-4 h-4" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900">Allergies</h3>
                            <button @click="addAllergy" class="text-primary-600 text-sm font-bold flex items-center"><Plus class="w-4 h-4 mr-1" /> Add</button>
                        </div>
                        <div v-for="(allergy, index) in patientForm.allergies" :key="index" class="flex items-center gap-4 bg-red-50/50 p-4 rounded-2xl">
                            <input v-model="allergy.allergen" placeholder="e.g. Penicillin" class="flex-1 bg-transparent border-none focus:ring-0 text-sm font-bold" />
                            <p-select v-model="allergy.severity" :options="['mild', 'moderate', 'severe']" class="w-32" />
                            <button @click="removeAllergy(index)" class="text-red-400"><X class="w-4 h-4" /></button>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Review -->
                <div v-else-if="activeStep === 5" key="step5" class="space-y-8">
                    <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100 flex items-start gap-4">
                        <AlertCircle class="w-6 h-6 text-blue-500 shrink-0 mt-1" />
                        <div>
                            <h4 class="font-bold text-blue-900">Ready to finalize?</h4>
                            <p class="text-sm text-blue-700">Please review all patient information below before confirming. New records are subject to immediate clinical availability.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest">Personal Details</h3>
                            <div class="bg-slate-50 p-6 rounded-3xl space-y-3">
                                <div class="flex justify-between"><span class="text-slate-500">Name</span><span class="font-bold">{{ patientForm.first_name }} {{ patientForm.last_name }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">DOB</span><span class="font-bold">{{ patientForm.dob?.toDateString() }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">Conditions</span><span class="font-bold text-right">{{ patientForm.conditions.join(', ') || 'None' }}</span></div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest">Contact Info</h3>
                            <div class="bg-slate-50 p-6 rounded-3xl space-y-3">
                                <div v-for="phone in patientForm.phones" :key="phone.number" class="flex justify-between">
                                    <span class="text-slate-500 capitalize">{{ phone.type }}</span>
                                    <span class="font-bold">{{ phone.number }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between pt-4">
            <button 
                v-if="activeStep > 0" 
                @click="prevStep"
                class="inline-flex items-center px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Previous Step
            </button>
            <div v-else></div>

            <button 
                v-if="activeStep < steps.length - 1"
                @click="nextStep"
                class="inline-flex items-center px-8 py-4 bg-[#1A3C5E] hover:bg-[#15304b] text-white rounded-xl font-bold shadow-lg shadow-primary-500/20 transition-all active:scale-95 ml-auto"
            >
                Continue
                <ArrowRight class="w-5 h-5 ml-2" />
            </button>
            
            <button 
                v-else
                @click="savePatient"
                class="inline-flex items-center px-10 py-4 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-lg shadow-green-500/20 transition-all active:scale-95 ml-auto"
            >
                Complete Registration
                <Check class="w-5 h-5 ml-2" />
            </button>
        </div>
    </div>
</template>

<style scoped>
@reference "../../../css/app.css";
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

:deep(.p-calendar .p-inputtext) {
    @apply w-full p-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-primary-500;
}

:deep(.p-select) {
    @apply bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary-500;
}
</style>
