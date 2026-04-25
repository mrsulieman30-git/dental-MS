import { useApi } from './useApi';

export function useAppointments() {
    const api = useApi();

    const fetchSchedule = (params) => api.get('/appointments', params);
    const createAppointment = (data) => api.post('/appointments', data);
    const updateStatus = (id, status) => api.patch(`/appointments/${id}/status`, { status });

    return { ...api, fetchSchedule, createAppointment, updateStatus };
}
