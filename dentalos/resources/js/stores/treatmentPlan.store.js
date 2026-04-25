import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useTreatmentPlanStore = defineStore('treatmentPlan', () => {
    const plans = ref([]);
    const activePlan = ref(null);
    const cdtSearchResults = ref([]);
    const loading = ref(false);
    const saving = ref(false);

    const draftPlans = computed(() => plans.value.filter(p => p.status === 'draft'));
    const acceptedPlans = computed(() => plans.value.filter(p => p.status === 'accepted'));

    async function fetchPlans(patientId) {
        loading.value = true;
        try {
            const { data } = await axios.get(`/api/patients/${patientId}/treatment-plans`);
            plans.value = data.data;
            return data.data;
        } finally { loading.value = false; }
    }

    async function fetchPlan(planId) {
        loading.value = true;
        try {
            const { data } = await axios.get(`/api/treatment-plans/${planId}`);
            activePlan.value = data.data;
            return data.data;
        } finally { loading.value = false; }
    }

    async function createPlan(patientId, planData) {
        saving.value = true;
        try {
            const { data } = await axios.post(`/api/patients/${patientId}/treatment-plans`, planData);
            plans.value.unshift(data.data);
            return data.data;
        } finally { saving.value = false; }
    }

    async function savePlan(planId, planData) {
        saving.value = true;
        try {
            const { data } = await axios.patch(`/api/treatment-plans/${planId}`, planData);
            const idx = plans.value.findIndex(p => p.id === planId);
            if (idx !== -1) plans.value[idx] = data.data;
            activePlan.value = data.data;
            return data.data;
        } finally { saving.value = false; }
    }

    async function updateStatus(planId, status, extra = {}) {
        const { data } = await axios.patch(`/api/treatment-plans/${planId}/status`, { status, ...extra });
        const idx = plans.value.findIndex(p => p.id === planId);
        if (idx !== -1) plans.value[idx] = data.data;
        return data.data;
    }

    async function duplicatePlan(planId) {
        const { data } = await axios.post(`/api/treatment-plans/${planId}/duplicate`);
        plans.value.unshift(data.data);
        return data.data;
    }

    async function archivePlan(planId) {
        await axios.delete(`/api/treatment-plans/${planId}`);
        plans.value = plans.value.filter(p => p.id !== planId);
    }

    async function reorderProcedures(planId, procedures) {
        const { data } = await axios.post(`/api/treatment-plans/${planId}/reorder`, { procedures });
        activePlan.value = data.data;
        return data.data;
    }

    async function searchCdtCodes(query, feeScheduleId = null) {
        try {
            const params = { q: query };
            if (feeScheduleId) params.fee_schedule_id = feeScheduleId;
            const { data } = await axios.get('/api/cdt-codes/search', { params });
            cdtSearchResults.value = data.data;
            return data.data;
        } catch { cdtSearchResults.value = []; return []; }
    }

    async function fetchPresentation(planId) {
        const { data } = await axios.get(`/api/treatment-plans/${planId}/present`);
        return data.data;
    }

    async function acceptPlan(planId, payload) {
        const { data } = await axios.patch(`/api/treatment-plans/${planId}/status`, payload);
        return data.data;
    }

    function calculateInsuranceEstimate(fee, category, coveredPercentages, deductibleRemaining = 0) {
        const categoryMap = {
            diagnostic: 'preventive', preventive: 'preventive',
            restorative: 'basic', endodontics: 'major',
            periodontics: 'basic', prosthodontics: 'major',
            orthodontics: 'orthodontics', adjunctive: 'basic',
            maxillofacial: 'major', other: 'basic'
        };
        const coverageKey = categoryMap[category] || 'basic';
        const percent = (coveredPercentages?.[coverageKey] ?? 0) / 100;
        const insEst = Math.max(0, (fee * percent) - Math.max(0, deductibleRemaining));
        return { insuranceEstimate: Math.round(insEst * 100) / 100, patientPortion: Math.round((fee - insEst) * 100) / 100 };
    }

    return {
        plans, activePlan, cdtSearchResults, loading, saving,
        draftPlans, acceptedPlans,
        fetchPlans, fetchPlan, createPlan, savePlan, updateStatus,
        duplicatePlan, archivePlan, reorderProcedures, searchCdtCodes,
        fetchPresentation, acceptPlan, calculateInsuranceEstimate
    };
});
