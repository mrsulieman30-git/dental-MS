<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { usePatients } from '../../composables/usePatients';
import { useAppStore } from '../../stores/app.store';
import { 
    formatDate, formatPhone, formatPatientNumber, 
    formatAge, formatCurrency 
} from '../../utils/formatters';
import { 
    Search, Plus, Filter, Download, 
    MoreHorizontal, CalendarPlus, FileText, User 
} from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';

const router = useRouter();
const app = useAppStore();
const { loading, patients, fetchPatients } = usePatients();

const dt = ref();
const searchQuery = ref('');
const rows = ref(15);
const first = ref(0);
const totalRecords = ref(0);
const lazyParams = ref({
    first: 0,
    rows: 15,
    page: 1,
    sortField: 'created_at',
    sortOrder: -1,
    filters: {}
});

const loadLazyData = async () => {
    const params = {
        page: lazyParams.value.page,
        per_page: lazyParams.value.rows,
        sort: lazyParams.value.sortField,
        order: lazyParams.value.sortOrder === 1 ? 'asc' : 'desc',
        search: searchQuery.value,
        ...lazyParams.value.filters
    };
    
    const response = await fetchPatients(params);
    // Note: UsePatients returns data directly, but we might need meta for pagination
    // Assuming API returns { data, meta }
    if (response) {
        totalRecords.value = response.meta?.total || 0;
    }
};

const onPage = (event) => {
    lazyParams.value = { ...lazyParams.value, ...event, page: event.page + 1 };
    loadLazyData();
};

const onSort = (event) => {
    lazyParams.value = { ...lazyParams.value, ...event };
    loadLazyData();
};

const onFilter = (event) => {
    lazyParams.value.filters = event.filters;
    loadLazyData();
};

const debouncedSearch = useDebounceFn(() => {
    lazyParams.value.page = 1;
    first.value = 0;
    loadLazyData();
}, 300);

watch(searchQuery, () => {
    debouncedSearch();
});

const exportCSV = () => {
    dt.value.exportCSV();
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'danger';
        case 'prospective': return 'info';
        case 'archived': return 'secondary';
        default: return null;
    }
};

onMounted(() => {
    loadLazyData();
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Patients</h1>
                <p class="text-sm text-slate-500">Manage and search your practice's patient records</p>
            </div>
            <div class="flex items-center gap-3">
                <button 
                    @click="exportCSV"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition-colors shadow-sm"
                >
                    <Download class="w-4 h-4 mr-2" />
                    Export CSV
                </button>
                <router-link 
                    to="/patients/new"
                    class="inline-flex items-center px-4 py-2 bg-[#1A3C5E] hover:bg-[#15304b] text-white rounded-xl text-sm font-bold shadow-lg shadow-primary-500/20 transition-all active:scale-95"
                >
                    <Plus class="w-4 h-4 mr-2" />
                    New Patient
                </router-link>
            </div>
        </div>

        <!-- Controls Card -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/50 dark:border-slate-800 shadow-sm flex flex-col md:flex-row items-center gap-4">
            <div class="relative flex-1 w-full">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="Search by name, ID, phone, or DOB (MM/DD/YYYY)..."
                    class="w-full pl-12 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 transition-all text-sm"
                />
            </div>
            <div class="flex items-center gap-2">
                <p-button icon="pi pi-filter" class="p-button-outlined p-button-rounded p-button-sm" label="Filters" />
                <p-button icon="pi pi-cog" class="p-button-text p-button-rounded p-button-sm" />
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800 shadow-xl overflow-hidden relative">
            <p-data-table 
                ref="dt"
                :value="patients" 
                lazy 
                paginator 
                :rows="rows" 
                v-model:first="first"
                :totalRecords="totalRecords" 
                :loading="loading"
                @page="onPage($event)"
                @sort="onSort($event)"
                @filter="onFilter($event)"
                filterDisplay="menu"
                responsiveLayout="scroll"
                class="p-datatable-sm"
                stripedRows
                removableSort
            >
                <template #empty>
                    <div class="py-20 text-center">
                        <User class="w-12 h-12 text-slate-200 mx-auto mb-4" />
                        <p class="text-slate-500 font-medium">No patients found matching your search</p>
                    </div>
                </template>

                <p-column field="patient_number" header="Patient #" sortable style="width: 10%">
                    <template #body="{ data }">
                        <span class="font-mono text-xs font-bold text-slate-500">{{ formatPatientNumber(data.patient_number) }}</span>
                    </template>
                </p-column>

                <p-column field="last_name" header="Name" sortable style="width: 20%">
                    <template #body="{ data }">
                        <router-link :to="`/patients/${data.id}`" class="flex items-center group">
                            <p-avatar 
                                :label="data.first_name[0] + data.last_name[0]" 
                                shape="circle" 
                                class="bg-primary-100 text-primary-600 font-bold"
                            />
                            <div class="ml-3">
                                <p class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition-colors">{{ data.full_name }}</p>
                                <p class="text-[10px] text-slate-500 font-medium">{{ data.preferred_name ? `"${data.preferred_name}"` : '' }}</p>
                            </div>
                        </router-link>
                    </template>
                </p-column>

                <p-column field="dob" header="DOB / Age" sortable style="width: 12%">
                    <template #body="{ data }">
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ formatDate(data.dob) }}</p>
                        <p class="text-xs text-slate-500">{{ formatAge(data.dob) }} years</p>
                    </template>
                </p-column>

                <p-column field="phone" header="Contact" style="width: 15%">
                    <template #body="{ data }">
                        <div class="space-y-0.5">
                            <p class="text-sm text-slate-700 dark:text-slate-300 flex items-center">
                                {{ formatPhone(data.phone) }}
                            </p>
                            <p class="text-xs text-slate-500 truncate max-w-[150px]">{{ data.email }}</p>
                        </div>
                    </template>
                </p-column>

                <p-column field="last_visit_at" header="Last Visit" sortable style="width: 12%">
                    <template #body="{ data }">
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ formatDate(data.last_visit_at) }}</p>
                    </template>
                </p-column>

                <p-column field="balance" header="Balance" sortable style="width: 10%">
                    <template #body="{ data }">
                        <span :class="[
                            'text-sm font-bold',
                            data.balance > 0 ? 'text-red-500' : 'text-green-500'
                        ]">
                            {{ formatCurrency(data.balance) }}
                        </span>
                    </template>
                </p-column>

                <p-column field="status" header="Status" sortable style="width: 10%">
                    <template #body="{ data }">
                        <p-tag :value="data.status" :severity="getStatusSeverity(data.status)" class="uppercase text-[10px] font-bold" />
                    </template>
                </p-column>

                <p-column headerStyle="width: 4rem; text-align: center" bodyStyle="text-align: center; overflow: visible">
                    <template #body="{ data }">
                        <div class="flex items-center gap-1 justify-end">
                            <button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-primary-500 transition-colors" title="Schedule Appointment">
                                <CalendarPlus class="w-4 h-4" />
                            </button>
                            <button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-600 transition-colors" title="Quick Note">
                                <FileText class="w-4 h-4" />
                            </button>
                            <p-button icon="pi pi-ellipsis-v" class="p-button-text p-button-rounded p-button-secondary p-button-sm" />
                        </div>
                    </template>
                </p-column>
            </p-data-table>

            <!-- Global Search Indicator -->
            <div v-if="loading" class="absolute top-0 left-0 w-full h-1">
                <p-progress-bar mode="indeterminate" style="height: 2px" />
            </div>
        </div>
    </div>
</template>

<style scoped>
@reference "../../../css/app.css";
:deep(.p-datatable-wrapper) {
    scrollbar-width: thin;
    scrollbar-color: #e2e8f0 transparent;
}

:deep(.p-datatable-wrapper::-webkit-scrollbar) {
    width: 4px;
}

:deep(.p-datatable-wrapper::-webkit-scrollbar-track) {
    background: transparent;
}

:deep(.p-datatable-wrapper::-webkit-scrollbar-thumb) {
    background: #e2e8f0;
    border-radius: 9999px;
}

:global(.dark) :deep(.p-datatable-wrapper::-webkit-scrollbar-thumb) {
    background: #1e293b;
}

:deep(.p-paginator) {
    @apply bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-6 py-3;
}

:deep(.p-paginator-page), :deep(.p-paginator-next), :deep(.p-paginator-last), :deep(.p-paginator-first), :deep(.p-paginator-prev) {
    @apply rounded-lg font-bold text-sm;
}

:deep(.p-paginator-page.p-highlight) {
    @apply bg-primary-500 text-white;
}
</style>
