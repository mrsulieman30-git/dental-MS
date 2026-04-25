import { defineStore } from 'pinia';
import axios from 'axios';

export const useImagingStore = defineStore('imaging', {
  state: () => ({
    series: [],
    selectedSeries: null,
    images: [],
    selectedImage: null,
    isLoading: false,
    uploadProgress: {},
    error: null,
  }),

  getters: {
    seriesByType: (state) => {
      const grouped = {};
      state.series.forEach(s => {
        if (!grouped[s.series_type]) grouped[s.series_type] = [];
        grouped[s.series_type].push(s);
      });
      return grouped;
    },
    seriesTypeLabels: () => ({
      fmx: 'Full Mouth X-rays', bw: 'Bitewings', pa: 'Periapical',
      pan: 'Panoramic', ceph: 'Cephalometric', cbct: 'CBCT',
      intraoral_photo: 'Intraoral Photos', extraoral_photo: 'Extraoral Photos', other: 'Other',
    }),
    mountLayoutCounts: () => ({
      fmx: 18, bw: 4, pa: 1, pan: 1, ceph: 1,
      intraoral_photo: 6, extraoral_photo: 4, other: 4, cbct: 1,
    }),
  },

  actions: {
    async fetchSeries(patientId) {
      this.isLoading = true;
      try {
        const { data } = await axios.get(`/imaging/patients/${patientId}/series`);
        this.series = data.data || [];
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load imaging';
      } finally {
        this.isLoading = false;
      }
    },

    async selectSeries(seriesId) {
      const s = this.series.find(s => s.id === seriesId);
      this.selectedSeries = s;
      this.isLoading = true;
      try {
        const { data } = await axios.get(`/imaging/series/${seriesId}`);
        this.images = data.data?.images || [];
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load images';
      } finally {
        this.isLoading = false;
      }
    },

    async uploadImages(patientId, files, seriesType, toothNumber = null) {
      const formData = new FormData();
      formData.append('patient_id', patientId);
      formData.append('series_type', seriesType);
      if (toothNumber) formData.append('tooth_number', toothNumber);
      files.forEach((file, i) => formData.append(`files[${i}]`, file));

      try {
        const { data } = await axios.post('/imaging/upload', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: (e) => {
            this.uploadProgress = { loaded: e.loaded, total: e.total, percent: Math.round((e.loaded / e.total) * 100) };
          },
        });
        this.uploadProgress = {};
        await this.fetchSeries(patientId);
        return data.data;
      } catch (err) {
        this.uploadProgress = {};
        this.error = err.response?.data?.message || 'Upload failed';
        throw err;
      }
    },

    async updateAnnotations(imageId, annotations) {
      const { data } = await axios.patch(`/imaging/images/${imageId}/annotations`, { annotations });
      const idx = this.images.findIndex(i => i.id === imageId);
      if (idx !== -1) this.images[idx] = { ...this.images[idx], annotations };
      return data.data;
    },

    async generateShareLink(imageId) {
      const { data } = await axios.post(`/imaging/images/${imageId}/share`);
      return data.data?.url;
    },

    selectImage(image) { this.selectedImage = image; },
    clearSelection() { this.selectedImage = null; },
  },
});
