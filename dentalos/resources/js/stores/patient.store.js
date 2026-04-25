import { defineStore } from 'pinia';
import axios from 'axios';

export const usePatientStore = defineStore('patient', {
    state: () => ({
        patients: [],
        currentPatient: null,
        pagination: {
            total: 0,
            per_page: 15,
            current_page: 1,
            last_page: 1,
        },
        filters: {
            status: 'active',
            location_id: null,
        },
        searchQuery: '',
        isLoading: false,
    }),

    actions: {
        async fetchPatients(page = 1) {
            this.isLoading = true;
            try {
                const response = await axios.get('/patients', {
                    params: {
                        page,
                        search: this.searchQuery,
                        ...this.filters
                    }
                });
                this.patients = response.data.data;
                this.pagination = response.data.meta;
            } finally {
                this.isLoading = false;
            }
        },

        async fetchPatient(id) {
            this.isLoading = true;
            try {
                const response = await axios.get(`/patients/${id}`);
                this.currentPatient = response.data.data;
            } finally {
                this.isLoading = false;
            }
        },

        async createPatient(data) {
            const response = await axios.post('/patients', data);
            return response.data;
        },

        async updatePatient(id, data) {
            const response = await axios.put(`/patients/${id}`, data);
            this.currentPatient = response.data.data;
            return response.data;
        }
    }
});
