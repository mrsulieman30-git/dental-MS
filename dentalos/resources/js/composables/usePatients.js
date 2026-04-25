import { useApi } from './useApi';

export function usePatients() {
    const api = useApi();

    const fetchPatients = (params) => api.get('/patients', params);
    const fetchPatient = (id) => api.get(`/patients/${id}`);
    const createPatient = (data) => api.post('/patients', data);
    const updatePatient = (id, data) => api.put(`/patients/${id}`, data);

    return { ...api, fetchPatients, fetchPatient, createPatient, updatePatient };
}
