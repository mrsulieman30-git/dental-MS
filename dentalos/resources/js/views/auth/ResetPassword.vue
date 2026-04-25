<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Shield, Lock, Eye, EyeOff, CheckCircle2 } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

const password = ref('');
const confirmPassword = ref('');
const showPassword = ref(false);
const submitted = ref(false);

const onSubmit = () => {
    submitted.value = true;
    setTimeout(() => router.push('/login'), 2000);
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-950 p-6 relative overflow-hidden">
        <div class="w-full max-w-md z-10">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-[#1A3C5E] rounded-2xl shadow-xl mb-4">
                    <Shield class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Set New Password</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Create a strong password for your account</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/50 dark:border-slate-800 p-8 sm:p-10">
                <div v-if="!submitted" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300">New Password</label>
                        <div class="relative group">
                            <Lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-primary-500 transition-colors" />
                            <input 
                                v-model="password"
                                :type="showPassword ? 'text' : 'password'" 
                                class="w-full pl-12 pr-12 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 transition-all text-slate-900 dark:text-white"
                                placeholder="••••••••"
                            />
                            <button @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <Eye v-if="!showPassword" class="w-5 h-5" />
                                <EyeOff v-else class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Confirm Password</label>
                        <input 
                            v-model="confirmPassword"
                            type="password" 
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 transition-all text-slate-900 dark:text-white"
                            placeholder="••••••••"
                        />
                    </div>

                    <button 
                        @click="onSubmit"
                        class="w-full py-4 bg-[#1A3C5E] hover:bg-[#15304b] text-white rounded-xl font-bold shadow-lg shadow-primary-500/20 transition-all"
                    >
                        Reset Password
                    </button>
                </div>

                <div v-else class="text-center py-4">
                    <CheckCircle2 class="w-16 h-16 text-green-500 mx-auto mb-4" />
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Password Updated</h3>
                    <p class="text-sm text-slate-500">Redirecting you to login...</p>
                </div>
            </div>
        </div>
    </div>
</template>
