import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useInsuranceStore = defineStore('insurance', () => {
    const insurances = ref([]);
    const carriers = ref([]);
    const feeSchedules = ref([]);
    const preAuths = ref([]);
    const loading = ref(false);
    const verifying = ref(false);

    // Patient Insurance
    async function fetchInsurances(patientId) {
        loading.value = true;
        try {
            const { data } = await axios.get(`/api/patients/${patientId}/insurance`);
            insurances.value = data.data;
            return data.data;
        } finally { loading.value = false; }
    }

    async function saveInsurance(patientId, insData, insuranceId = null) {
        if (insuranceId) {
            const { data } = await axios.patch(`/api/patients/${patientId}/insurance/${insuranceId}`, insData);
            const idx = insurances.value.findIndex(i => i.id === insuranceId);
            if (idx !== -1) insurances.value[idx] = data.data;
            return data.data;
        } else {
            const { data } = await axios.post(`/api/patients/${patientId}/insurance`, insData);
            insurances.value.push(data.data);
            return data.data;
        }
    }

    async function deleteInsurance(patientId, insuranceId) {
        await axios.delete(`/api/patients/${patientId}/insurance/${insuranceId}`);
        insurances.value = insurances.value.filter(i => i.id !== insuranceId);
    }

    async function verifyEligibility(patientId, insuranceId) {
        verifying.value = true;
        try {
            const { data } = await axios.post(`/api/patients/${patientId}/insurance/${insuranceId}/verify`);
            const idx = insurances.value.findIndex(i => i.id === insuranceId);
            if (idx !== -1) insurances.value[idx] = data.data;
            return data.data;
        } finally { verifying.value = false; }
    }

    async function fetchEligibilityHistory(patientId, insuranceId) {
        const { data } = await axios.get(`/api/patients/${patientId}/insurance/${insuranceId}/eligibility-history`);
        return data.data;
    }

    // Pre-Authorizations
    async function fetchPreAuths(patientId) {
        const { data } = await axios.get(`/api/patients/${patientId}/pre-auths`);
        preAuths.value = data.data;
        return data.data;
    }

    async function createPreAuth(patientId, authData) {
        const { data } = await axios.post(`/api/patients/${patientId}/pre-auths`, authData);
        preAuths.value.unshift(data.data);
        return data.data;
    }

    async function updatePreAuth(preAuthId, authData) {
        const { data } = await axios.patch(`/api/pre-auths/${preAuthId}`, authData);
        const idx = preAuths.value.findIndex(a => a.id === preAuthId);
        if (idx !== -1) preAuths.value[idx] = data.data;
        return data.data;
    }

    // Insurance Carriers (Settings)
    async function fetchCarriers(params = {}) {
        const { data } = await axios.get('/api/insurance-carriers', { params });
        carriers.value = data.data;
        return data.data;
    }

    async function saveCarrier(carrierData, carrierId = null) {
        if (carrierId) {
            const { data } = await axios.patch(`/api/insurance-carriers/${carrierId}`, carrierData);
            const idx = carriers.value.findIndex(c => c.id === carrierId);
            if (idx !== -1) carriers.value[idx] = data.data;
            return data.data;
        } else {
            const { data } = await axios.post('/api/insurance-carriers', carrierData);
            carriers.value.push(data.data);
            return data.data;
        }
    }

    async function toggleCarrier(carrierId) {
        const { data } = await axios.patch(`/api/insurance-carriers/${carrierId}/deactivate`);
        const idx = carriers.value.findIndex(c => c.id === carrierId);
        if (idx !== -1) carriers.value[idx] = data.data;
        return data.data;
    }

    // Fee Schedules
    async function fetchFeeSchedules() {
        const { data } = await axios.get('/api/fee-schedules');
        feeSchedules.value = data.data;
        return data.data;
    }

    async function saveFeeSchedule(schedData, schedId = null) {
        if (schedId) {
            const { data } = await axios.patch(`/api/fee-schedules/${schedId}`, schedData);
            const idx = feeSchedules.value.findIndex(s => s.id === schedId);
            if (idx !== -1) feeSchedules.value[idx] = data.data;
            return data.data;
        } else {
            const { data } = await axios.post('/api/fee-schedules', schedData);
            feeSchedules.value.push(data.data);
            return data.data;
        }
    }

    async function fetchFeeScheduleItems(schedId) {
        const { data } = await axios.get(`/api/fee-schedules/${schedId}/items`);
        return data.data;
    }

    async function addFeeScheduleItem(schedId, itemData) {
        const { data } = await axios.post(`/api/fee-schedules/${schedId}/items`, itemData);
        return data.data;
    }

    async function importCsv(schedId, items) {
        const { data } = await axios.post(`/api/fee-schedules/${schedId}/import-csv`, { items });
        return data.data;
    }

    async function bulkAdjust(schedId, percent) {
        const { data } = await axios.patch(`/api/fee-schedules/${schedId}/bulk-adjust`, { percent });
        return data.data;
    }

    return {
        insurances, carriers, feeSchedules, preAuths, loading, verifying,
        fetchInsurances, saveInsurance, deleteInsurance, verifyEligibility, fetchEligibilityHistory,
        fetchPreAuths, createPreAuth, updatePreAuth,
        fetchCarriers, saveCarrier, toggleCarrier,
        fetchFeeSchedules, saveFeeSchedule, fetchFeeScheduleItems, addFeeScheduleItem, importCsv, bulkAdjust
    };
});
