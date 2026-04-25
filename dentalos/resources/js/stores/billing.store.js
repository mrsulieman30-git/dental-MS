import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useBillingStore = defineStore('billing', () => {
    const ledgerEntries = ref([]);
    const currentBalance = ref(0);
    const kpis = ref({});
    const aging = ref({});
    const loading = ref(false);
    const posting = ref(false);

    async function fetchLedger(patientId) {
        loading.value = true;
        try {
            const { data } = await axios.get(`/api/patients/${patientId}/ledger`);
            ledgerEntries.value = data.data.entries;
            currentBalance.value = data.data.current_balance;
            return data.data;
        } finally { loading.value = false; }
    }

    async function postCharge(patientId, chargeData) {
        posting.value = true;
        try {
            const { data } = await axios.post(`/api/patients/${patientId}/ledger/charge`, chargeData);
            await fetchLedger(patientId);
            return data.data;
        } finally { posting.value = false; }
    }

    async function postAdjustment(patientId, adjData) {
        posting.value = true;
        try {
            const { data } = await axios.post(`/api/patients/${patientId}/ledger/adjustment`, adjData);
            await fetchLedger(patientId);
            return data.data;
        } finally { posting.value = false; }
    }

    async function voidEntry(entryId, patientId, reason) {
        await axios.post(`/api/ledger/${entryId}/void`, { void_reason: reason });
        await fetchLedger(patientId);
    }

    async function postPayment(patientId, paymentData) {
        posting.value = true;
        try {
            const { data } = await axios.post(`/api/patients/${patientId}/payments`, paymentData);
            await fetchLedger(patientId);
            return data.data;
        } finally { posting.value = false; }
    }

    async function createPaymentIntent(patientId, amount) {
        const { data } = await axios.post(`/api/patients/${patientId}/payments/intent`, { amount });
        return data.data;
    }

    async function refundPayment(paymentId, amount, patientId) {
        await axios.post(`/api/payments/${paymentId}/refund`, { amount });
        await fetchLedger(patientId);
    }

    async function fetchDashboardKpis() {
        const { data } = await axios.get('/api/billing/dashboard/kpis');
        kpis.value = data.data;
        return data.data;
    }

    async function fetchAgingReport() {
        const { data } = await axios.get('/api/billing/dashboard/aging');
        aging.value = data.data;
        return data.data;
    }

    return {
        ledgerEntries, currentBalance, kpis, aging, loading, posting,
        fetchLedger, postCharge, postAdjustment, voidEntry, postPayment,
        createPaymentIntent, refundPayment, fetchDashboardKpis, fetchAgingReport
    };
});
