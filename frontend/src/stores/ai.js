import { defineStore } from 'pinia';
import api from '../services/api';

export const useAiStore = defineStore('ai', {
  state: () => ({
    insights: [],
    automations: [],
    workshopActivities: [],
    loading: false
  }),
  actions: {
    async fetchDashboardData() {
      this.loading = true;
      try {
        const [resInsights, resAutomations, resActivity] = await Promise.all([
          api.get('/ai/insights'),
          api.get('/ai/automations'),
          api.get('/ai/workshop-activity')
        ]);
        
        this.insights = resInsights.data.data;
        this.automations = resAutomations.data.data;
        this.workshopActivities = resActivity.data.data;
      } finally {
        this.loading = false;
      }
    },
    async triggerAutomation(eventName) {
      await api.post('/ai/trigger', { event: eventName });
      await this.fetchDashboardData();
    }
  }
});

