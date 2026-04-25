<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Practice Overview</h1>
            <div class="flex items-center gap-3">
                <p-button label="Quick Add" icon="pi pi-plus" class="p-button-rounded" />
                <p-button label="Export" icon="pi pi-download" class="p-button-outlined p-button-rounded" />
            </div>
        </div>

        <!-- Dashboard Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="i in 4" :key="i" class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm">
                <p-skeleton width="10rem" class="mb-2"></p-skeleton>
                <p-skeleton width="5rem" height="2rem"></p-skeleton>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm min-h-[400px]">
                <p-skeleton height="100%"></p-skeleton>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-sm min-h-[400px]">
                <p-skeleton height="100%"></p-skeleton>
            </div>
        </div>
    </div>
</template>
