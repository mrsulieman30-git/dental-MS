<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth.store';

const auth = useAuthStore();

onMounted(async () => {
    // Attempt to fetch current user if token exists
    if (localStorage.getItem('auth_token')) {
        await auth.fetchUser();
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
        <router-view></router-view>
        <p-toast />
        <p-confirm-dialog />
    </div>
</template>

<style>
/* Global PrimeVue Overrides */
:root {
    --p-primary-color: #1A3C5E;
    --p-primary-contrast-color: #ffffff;
    --p-primary-50: #f1f5f9;
    --p-primary-100: #e2e8f0;
    --p-primary-200: #cbd5e1;
    --p-primary-300: #94a3b8;
    --p-primary-400: #64748b;
    --p-primary-500: #1A3C5E;
    --p-primary-600: #0f172a;
    --p-primary-700: #334155;
    --p-primary-800: #1e293b;
    --p-primary-900: #0f172a;
    --p-primary-950: #020617;
}

body {
    margin: 0;
    padding: 0;
    font-family: 'Inter', sans-serif;
}
</style>
