import { defineStore } from 'pinia';
import axios from 'axios';

export const usePerioStore = defineStore('perio', {
  state: () => ({
    charts: [],
    currentChart: null,
    measurements: {},
    isLoading: false,
    isSaving: false,
    error: null,
    aapResult: null,
    isCharting: false,
    compareChart: null,
  }),

  getters: {
    latestChart: (state) => state.charts[0] || null,

    getMeasurement: (state) => (toothNum, surface) => {
      const key = `${toothNum}_${surface}`;
      return state.measurements[key] || createEmptyMeasurement(toothNum, surface);
    },

    totalBOP(state) {
      let bleeding = 0, total = 0;
      Object.values(state.measurements).forEach(m => {
        for (let i = 1; i <= 3; i++) {
          if (m[`pos${i}_probe`] != null) {
            total++;
            if (m[`pos${i}_bleeding`]) bleeding++;
          }
        }
      });
      return total > 0 ? Math.round((bleeding / total) * 100) : 0;
    },

    maxProbeDepth(state) {
      let max = 0;
      Object.values(state.measurements).forEach(m => {
        for (let i = 1; i <= 3; i++) {
          const v = m[`pos${i}_probe`];
          if (v != null && v > max) max = v;
        }
      });
      return max;
    },
  },

  actions: {
    async fetchCharts(patientId) {
      this.isLoading = true;
      try {
        const { data } = await axios.get(`/clinical/patients/${patientId}/perio`);
        this.charts = data.data || [];
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load perio charts';
      } finally {
        this.isLoading = false;
      }
    },

    async loadChart(chartId) {
      this.isLoading = true;
      try {
        const chart = await this.fetchChartDetail(chartId);
        this.currentChart = chart;
        this.measurements = chart.measurementsMap;
        this.isCharting = true;
      } finally {
        this.isLoading = false;
      }
    },

    async fetchChartDetail(chartId) {
      const { data } = await axios.get(`/clinical/perio/${chartId}`);
      return {
        ...data.data,
        measurementsMap: normalizeMeasurements(data.data.measurements || []),
      };
    },

    startNewChart() {
      this.isCharting = true;
      this.currentChart = null;
      this.measurements = {};
      // Initialize empty measurements for all teeth
      for (let t = 1; t <= 32; t++) {
        for (const surface of ['buccal', 'lingual']) {
          this.measurements[`${t}_${surface}`] = createEmptyMeasurement(t, surface);
        }
      }
      this.aapResult = null;
    },

    updateMeasurement(toothNum, surface, field, value) {
      const key = `${toothNum}_${surface}`;
      if (!this.measurements[key]) {
        this.measurements[key] = createEmptyMeasurement(toothNum, surface);
      }
      this.measurements[key][field] = value;
    },

    async saveChart(patientId, appointmentId = null) {
      this.isSaving = true;
      try {
        const measurementsArray = Object.values(this.measurements).filter(m =>
          m.pos1_probe != null || m.pos2_probe != null || m.pos3_probe != null
        );
        const { data } = await axios.post(`/clinical/patients/${patientId}/perio`, {
          chart_date: new Date().toISOString().slice(0, 10),
          appointment_id: appointmentId,
          measurements: measurementsArray,
        });
        this.currentChart = {
          ...data.data,
          measurementsMap: normalizeMeasurements(data.data.measurements || []),
        };
        this.measurements = this.currentChart.measurementsMap;
        this.isCharting = false;
        await this.fetchCharts(patientId);
        return data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to save perio chart';
        throw err;
      } finally {
        this.isSaving = false;
      }
    },

    calculateAAP() {
      const maxPD = this.maxProbeDepth;
      const bop = this.totalBOP;
      let stage, grade;

      if (maxPD <= 3) stage = 'I';
      else if (maxPD <= 4) stage = 'II';
      else if (maxPD <= 5) stage = 'III';
      else stage = 'IV';

      // Simplified grade calculation
      if (bop < 20) grade = 'A';
      else if (bop < 50) grade = 'B';
      else grade = 'C';

      const riskLevel = stage === 'I' ? 'low' : stage === 'II' ? 'moderate' : stage === 'III' ? 'high' : 'very_high';

      this.aapResult = { stage, grade, riskLevel, maxPD, bop };
      return this.aapResult;
    },

    setCompareChart(chart) {
      this.compareChart = chart;
    },
  },
});

function normalizeMeasurements(measurements = []) {
  return measurements.reduce((acc, measurement) => {
    acc[`${measurement.tooth_number}_${measurement.surface}`] = { ...measurement };
    return acc;
  }, {});
}

function createEmptyMeasurement(toothNum, surface) {
  return {
    tooth_number: toothNum,
    surface,
    pos1_probe: null, pos2_probe: null, pos3_probe: null,
    pos1_recession: 0, pos2_recession: 0, pos3_recession: 0,
    pos1_bleeding: false, pos2_bleeding: false, pos3_bleeding: false,
    pos1_suppuration: false, pos2_suppuration: false, pos3_suppuration: false,
    furcation_class: 'none',
    mobility_grade: 0,
    plaque_present: false,
    calculus_present: false,
  };
}
