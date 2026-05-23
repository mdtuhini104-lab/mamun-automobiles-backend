import { defineStore } from 'pinia';
import api from '../services/api';

export const useSaasStore = defineStore('saas', {
  state: () => ({
    tenants: [],
    stats: null,
    loading: false
  }),
  actions: {
    async fetchTenants() {
      this.loading = true;
      try {
        const res = await api.get('/saas/tenants');
        this.tenants = res.data.data;
      } finally {
        this.loading = false;
      }
    },
    async fetchStats() {
      const res = await api.get('/saas/stats');
      this.stats = res.data.data;
    }
  }
});

