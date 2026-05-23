import { defineStore } from 'pinia';
import api from '../services/api';

export const useAnalyticsStore = defineStore('analytics', {
  state: () => ({
    dashboard: null,
    loading: false
  }),
  actions: {
    async fetchDashboard() {
      this.loading = true;
      try {
        const response = await api.get('/analytics/dashboard');
        this.dashboard = response.data.data;
      } finally {
        this.loading = false;
      }
    }
  }
});

