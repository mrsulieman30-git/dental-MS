import { useApi } from './useApi';

export function useClinical() {
    const api = useApi();

    // Chart
    const fetchChart = (patientId) => api.get(`/clinical/patients/${patientId}/chart`);
    const storeCondition = (patientId, data) => api.post(`/clinical/patients/${patientId}/condition`, data);
    const storeRestoration = (patientId, data) => api.post(`/clinical/patients/${patientId}/restoration`, data);

    // Perio
    const fetchPerio = (patientId) => api.get(`/clinical/patients/${patientId}/perio`);
    const storePerio = (patientId, data) => api.post(`/clinical/patients/${patientId}/perio`, data);
    const fetchPerioChart = (chartId) => api.get(`/clinical/perio/${chartId}`);

    // Notes
    const fetchNotes = (patientId) => api.get(`/clinical/patients/${patientId}/notes`);
    const storeNote = (patientId, data) => api.post(`/clinical/patients/${patientId}/notes`, data);
    const updateNote = (noteId, data) => api.patch(`/clinical/notes/${noteId}`, data);
    const lockNote = (id) => api.patch(`/clinical/notes/${id}/lock`);
    const signNote = (id) => api.patch(`/clinical/notes/${id}/sign`);
    const amendNote = (id, data) => api.post(`/clinical/notes/${id}/amend`, data);
    const fetchNoteTemplates = () => api.get('/clinical/note-templates');

    // Imaging
    const fetchImaging = (patientId) => api.get(`/imaging/patients/${patientId}/series`);
    const fetchSeriesImages = (seriesId) => api.get(`/imaging/series/${seriesId}`);
    const uploadImage = (formData) => api.post('/imaging/upload', formData);
    const updateAnnotations = (imageId, data) => api.patch(`/imaging/images/${imageId}/annotations`, data);
    const shareImage = (imageId) => api.post(`/imaging/images/${imageId}/share`);

    return {
        ...api,
        fetchChart, storeCondition, storeRestoration,
        fetchPerio, storePerio, fetchPerioChart,
        fetchNotes, storeNote, updateNote, lockNote, signNote, amendNote, fetchNoteTemplates,
        fetchImaging, fetchSeriesImages, uploadImage, updateAnnotations, shareImage,
    };
}
