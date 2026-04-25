<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth.store';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { Shield, Mail, Lock, Eye, EyeOff, Loader2, ArrowRight, Smartphone } from 'lucide-vue-next';
import { useToast } from 'vue-toastification';

const router = useRouter();
const auth = useAuthStore();
const toast = useToast();

const showPassword = ref(false);
const mfaRequired = ref(false);
const mfaCode = ref('');

const schema = yup.object({
    email: yup.string().required().email(),
    password: yup.string().required().min(6),
});

const { handleSubmit, errors, isSubmitting, defineField } = useForm({
    validationSchema: schema,
});

const [email, emailAttrs] = defineField('email');
const [password, passwordAttrs] = defineField('password');

const onLogin = handleSubmit(async (values) => {
    try {
        const response = await auth.login(values);
        
        if (response.data.mfa_required) {
            mfaRequired.value = true;
            return;
        }

        toast.success('Welcome back, ' + auth.user.first_name);
        router.push({ name: 'dashboard' });
    } catch (err) {
        toast.error(err.response?.data?.message || 'Invalid credentials');
    }
});

const onVerifyMfa = async () => {
    try {
        // Mock MFA verify call
        // await auth.verifyMfa({ code: mfaCode.value });
        router.push({ name: 'dashboard' });
    } catch (err) {
        toast.error('Invalid MFA code');
    }
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-950 p-6 relative overflow-hidden">
        <!-- Abstract Background Shapes -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-primary-200 dark:bg-primary-900/30 rounded-full blur-3xl opacity-50"></div>

        <div class="w-full max-w-md z-10">
            <!-- Logo & Title -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-[#1A3C5E] rounded-2xl shadow-xl shadow-primary-500/20 mb-4 rotate-3 hover:rotate-0 transition-transform duration-300">
                    <Shield class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">DentalOS</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Enterprise Practice Management</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/50 dark:border-slate-800 p-8 sm:p-10 transition-all duration-500 hover:shadow-primary-500/5">
                <transition name="fade-slide" mode="out-in">
                    <!-- Credentials Step -->
                    <div v-if="!mfaRequired" key="login">
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Sign In</h2>
                            <p class="text-sm text-slate-500 mt-1">Access your practice dashboard</p>
                        </div>

                        <form @submit="onLogin" class="space-y-6">
                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 ml-1">Email Address</label>
                                <div class="relative group">
                                    <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-primary-500 transition-colors" />
                                    <input 
                                        v-model="email"
                                        v-bind="emailAttrs"
                                        type="email" 
                                        placeholder="admin@practice.com"
                                        class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 transition-all text-slate-900 dark:text-white"
                                        required
                                    />
                                </div>
                                <p v-if="errors.email" class="text-xs text-red-500 font-medium ml-1">{{ errors.email }}</p>
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between ml-1">
                                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Password</label>
                                    <router-link to="/forgot-password" class="text-xs text-primary-600 hover:text-primary-700 font-bold">Forgot?</router-link>
                                </div>
                                <div class="relative group">
                                    <Lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-primary-500 transition-colors" />
                                    <input 
                                        v-model="password"
                                        v-bind="passwordAttrs"
                                        :type="showPassword ? 'text' : 'password'" 
                                        placeholder="••••••••"
                                        class="w-full pl-12 pr-12 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 transition-all text-slate-900 dark:text-white"
                                        required
                                    />
                                    <button 
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                                    >
                                        <Eye v-if="!showPassword" class="w-5 h-5" />
                                        <EyeOff v-else class="w-5 h-5" />
                                    </button>
                                </div>
                                <p v-if="errors.password" class="text-xs text-red-500 font-medium ml-1">{{ errors.password }}</p>
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center gap-2 ml-1">
                                <input type="checkbox" id="remember" class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
                                <label for="remember" class="text-sm text-slate-600 dark:text-slate-400 font-medium cursor-pointer">Stay signed in for 30 days</label>
                            </div>

                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="w-full py-4 bg-[#1A3C5E] hover:bg-[#15304b] text-white rounded-xl font-bold shadow-lg shadow-primary-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-70"
                            >
                                <Loader2 v-if="isSubmitting" class="w-5 h-5 animate-spin" />
                                <span v-else>Continue to Dashboard</span>
                                <ArrowRight v-if="!isSubmitting" class="w-5 h-5" />
                            </button>
                        </form>
                    </div>

                    <!-- MFA Step -->
                    <div v-else key="mfa">
                        <div class="mb-8">
                            <div class="w-12 h-12 bg-primary-50 dark:bg-primary-900/30 rounded-xl flex items-center justify-center mb-4">
                                <Smartphone class="w-6 h-6 text-primary-600" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Verify Your Identity</h2>
                            <p class="text-sm text-slate-500 mt-1">Enter the 6-digit code from your authenticator app</p>
                        </div>

                        <div class="space-y-6">
                            <div class="flex justify-between gap-2">
                                <input 
                                    v-model="mfaCode"
                                    type="text" 
                                    maxlength="6"
                                    placeholder="000000"
                                    class="w-full text-center text-3xl font-bold tracking-[1em] py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 transition-all text-slate-900 dark:text-white"
                                />
                            </div>

                            <button 
                                @click="onVerifyMfa"
                                class="w-full py-4 bg-[#1A3C5E] hover:bg-[#15304b] text-white rounded-xl font-bold shadow-lg shadow-primary-500/20 active:scale-[0.98] transition-all"
                            >
                                Verify & Sign In
                            </button>

                            <button @click="mfaRequired = false" class="w-full text-sm text-slate-500 hover:text-slate-700 font-bold transition-colors">
                                Back to Login
                            </button>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Footer Links -->
            <p class="text-center text-sm text-slate-500 dark:text-slate-400 mt-8">
                Need help? <a href="#" class="text-primary-600 font-bold hover:underline">Contact Support</a> or 
                <a href="#" class="text-primary-600 font-bold hover:underline">View Status</a>
            </p>
        </div>
    </div>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>
