import { defineStore } from 'pinia';
import axios from 'axios';

export const useScheduleStore = defineStore('schedule', {
    state: () => ({
        currentDate: new Date(),
        currentView: 'timeGridDay',
        selectedProviders: [],
        selectedLocation: null,
        events: [],
        blocks: [],
        isLoading: false,
    }),

    actions: {
        async fetchSchedule(range) {
            this.isLoading = true;
            try {
                const response = await axios.get('/appointments', {
                    params: {
                        date_from: range.start,
                        date_to: range.end,
                        location_id: this.selectedLocation,
                        provider_ids: this.selectedProviders,
                    }
                });
                this.events = response.data.data;
            } catch (error) {
                console.error('Failed to fetch schedule', error);
            } finally {
                this.isLoading = false;
            }
        },

        async createAppointment(data) {
            const response = await axios.post('/appointments', data);
            this.events.push(response.data.data);
            return response.data;
        },

        async updateAppointmentStatus(id, status) {
            const response = await axios.patch(`/appointments/${id}/status`, { status });
            const index = this.events.findIndex(e => e.id === id);
            if (index !== -1) {
                this.events[index].status = status;
            }
            return response.data;
        }
    }
});
