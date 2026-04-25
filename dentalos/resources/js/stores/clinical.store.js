import { defineStore } from 'pinia';
import { shallowRef, shallowReactive } from 'vue';
import axios from 'axios';

export const useClinicalStore = defineStore('clinical', {
  state: () => ({
    chart: null,
    conditions: shallowRef([]),
    restorations: shallowRef([]),
    implants: shallowRef([]),
    selectedTooth: null,
    selectedSurface: null,
    notation: 'universal',
    isLoading: false,
    error: null,
    chartHistory: [],
  }),

  getters: {
    conditionsForTooth: (state) => (toothNum) =>
      state.conditions.filter(c => c.tooth_number === toothNum),

    restorationsForTooth: (state) => (toothNum) =>
      state.restorations.filter(r => r.tooth_number === toothNum),

    implantsForTooth: (state) => (toothNum) =>
      state.implants.filter(i => i.tooth_number === toothNum),

    chartData: (state) => ({
      conditions: state.conditions,
      restorations: state.restorations,
      implants: state.implants,
    }),

    selectedToothData() {
      if (!this.selectedTooth) return null;
      return {
        tooth: this.selectedTooth,
        conditions: this.conditionsForTooth(this.selectedTooth),
        restorations: this.restorationsForTooth(this.selectedTooth),
        implants: this.implantsForTooth(this.selectedTooth),
      };
    },
  },

  actions: {
    async fetchChart(patientId) {
      this.isLoading = true;
      this.error = null;
      try {
        const { data } = await axios.get(`/clinical/patients/${patientId}/chart`);
        const chart = data.data;
        this.chart = chart;
        this.conditions = chart.tooth_conditions || [];
        this.restorations = chart.restorations || [];
        this.implants = chart.implants || [];
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load chart';
        throw err;
      } finally {
        this.isLoading = false;
      }
    },

    async addCondition(patientId, conditionData) {
      const { data } = await axios.post(`/clinical/patients/${patientId}/condition`, conditionData);
      this.conditions = [...this.conditions, data.data];
      return data.data;
    },

    async addRestoration(patientId, restorationData) {
      const { data } = await axios.post(`/clinical/patients/${patientId}/restoration`, restorationData);
      this.restorations = [...this.restorations, data.data];
      return data.data;
    },

    selectTooth(toothNum) {
      this.selectedTooth = toothNum;
      this.selectedSurface = null;
    },

    selectSurface(toothNum, surface) {
      this.selectedTooth = toothNum;
      this.selectedSurface = surface;
    },

    clearSelection() {
      this.selectedTooth = null;
      this.selectedSurface = null;
    },

    setNotation(notation) {
      this.notation = notation;
    },
  },
});
