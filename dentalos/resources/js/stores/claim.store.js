import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useClaimStore = defineStore('claim', () => {
    const claims = ref([]);
    const activeClaim = ref(null);
    const loading = ref(false);
    const saving = ref(false);

    async function fetchClaims(params = {}) {
        loading.value = true;
        try {
            const { data } = await axios.get('/api/claims', { params });
            claims.value = data.data.data;
            return data.data;
        } finally { loading.value = false; }
    }

    async function fetchClaim(claimId) {
        loading.value = true;
        try {
            const { data } = await axios.get(`/api/claims/${claimId}`);
            activeClaim.value = data.data;
            return data.data;
        } finally { loading.value = false; }
    }

    async function generateClaim(appointmentId) {
        saving.value = true;
        try {
            const { data } = await axios.post(`/api/appointments/${appointmentId}/generate-claim`);
            return data.data;
        } finally { saving.value = false; }
    }

    async function scrubClaim(claimId) {
        const { data } = await axios.post(`/api/claims/${claimId}/scrub`);
        if (activeClaim.value && activeClaim.value.id === claimId) {
            activeClaim.value.is_scrubbed = true;
            activeClaim.value.scrubbing_errors = data.data.errors;
        }
        return data.data;
    }

    async function submitClaim(claimId) {
        saving.value = true;
        try {
            const { data } = await axios.post(`/api/claims/${claimId}/submit`);
            const idx = claims.value.findIndex(c => c.id === claimId);
            if (idx !== -1) claims.value[idx] = data.data;
            if (activeClaim.value && activeClaim.value.id === claimId) activeClaim.value = data.data;
            return data.data;
        } finally { saving.value = false; }
    }

    async function addAttachment(claimId, payload) {
        const formData = new FormData();
        Object.keys(payload).forEach(key => {
            formData.append(key, payload[key]);
        });
        const { data } = await axios.post(`/api/claims/${claimId}/attachments`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        if (activeClaim.value && activeClaim.value.id === claimId) {
            activeClaim.value.attachments.push(data.data);
        }
        return data.data;
    }

    async function generateSecondaryClaim(primaryClaimId) {
        const { data } = await axios.post(`/api/claims/${primaryClaimId}/generate-secondary`);
        return data.data;
    }

    return {
        claims, activeClaim, loading, saving,
        fetchClaims, fetchClaim, generateClaim, scrubClaim, submitClaim,
        addAttachment, generateSecondaryClaim
    };
});
