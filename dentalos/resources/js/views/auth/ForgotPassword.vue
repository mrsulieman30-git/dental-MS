<script setup>
import { ref } from 'vue';
import { Shield, Mail, ArrowLeft, CheckCircle2 } from 'lucide-vue-next';

const email = ref('');
const submitted = ref(false);

const onSubmit = () => {
    // Mock submit
    submitted.value = true;
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-950 p-6 relative overflow-hidden">
        <div class="w-full max-w-md z-10">
            <div class="text-center mb-10">
                <router-link to="/login" class="inline-flex items-center justify-center w-12 h-12 bg-white dark:bg-slate-900 rounded-xl shadow-md mb-6 text-slate-500 hover:text-primary-500 transition-colors">
                    <ArrowLeft class="w-6 h-6" />
                </router-link>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Recover Password</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2">We'll send you a secure reset link</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/50 dark:border-slate-800 p-8 sm:p-10">
                <div v-if="!submitted">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 ml-1">Practice Email</label>
                            <div class="relative group">
                                <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-primary-500 transition-colors" />
                                <input 
                                    v-model="email"
                                    type="email" 
                                    placeholder="admin@practice.com"
                                    class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 transition-all text-slate-900 dark:text-white"
                                />
                            </div>
                        </div>

                        <button 
                            @click="onSubmit"
                            class="w-full py-4 bg-[#1A3C5E] hover:bg-[#15304b] text-white rounded-xl font-bold shadow-lg shadow-primary-500/20 transition-all"
                        >
                            Send Reset Link
                        </button>
                    </div>
                </div>

                <div v-else class="text-center py-4">
                    <CheckCircle2 class="w-16 h-16 text-green-500 mx-auto mb-4" />
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Check your inbox</h3>
                    <p class="text-sm text-slate-500 mb-6">We've sent a recovery link to <strong>{{ email }}</strong>. Please check your spam folder if you don't see it.</p>
                    <button @click="submitted = false" class="text-primary-600 font-bold hover:underline">Resend email</button>
                </div>
            </div>
        </div>
    </div>
</template>
