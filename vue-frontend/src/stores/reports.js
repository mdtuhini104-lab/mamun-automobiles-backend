import { defineStore } from 'pinia';
import api from '../services/api';

export const useReportStore = defineStore('reports', {
  state: () => ({
    analytics: {
      revenue: 0,
      expenses: 0,
      profit: 0,
      salesCount: 0
    },
    chartData: [],
    filters: {
      dateRange: 'month',
      type: 'sales'
    },
    loading: false,
    exporting: false
  }),
  actions: {
    async fetchAnalytics() {
      this.loading = true;
      try {
        const res = await api.get('/reports', { params: this.filters });
        if (res.data && res.data.data) {
          // Prevent reactive chart loops by shallow copying or extracting raw primitives
          this.analytics = {
            revenue: res.data.data.revenue || 0,
            expenses: res.data.data.expenses || 0,
            profit: res.data.data.profit || 0,
            salesCount: res.data.data.salesCount || 0
          };
          // Hardcode or map chart data to prevent reactive arrays exploding
          this.chartData = (res.data.data.chart || []).map(item => ({ label: item.label, value: item.value }));
        }
      } catch (err) {
        console.error('Failed to fetch analytics', err);
      } finally {
        this.loading = false;
      }
    },
    setFilter(key, value) {
      this.filters[key] = value;
      this.fetchAnalytics();
    },
    async exportReport(format = 'csv') {
      this.exporting = true;
      try {
        const res = await api.get(`/reports/export?format=${format}`, { responseType: 'blob' });
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `report_${this.filters.type}_${Date.now()}.${format}`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } catch (err) {
        console.error('Export failed', err);
      } finally {
        this.exporting = false;
      }
    },
    patchAnalytics(data) {
      // Safely patch analytics without a full API reload to prevent stale dataset duplication
      if (!data) return;
      if (data.revenue !== undefined) this.analytics.revenue = data.revenue;
      if (data.expenses !== undefined) this.analytics.expenses = data.expenses;
      if (data.profit !== undefined) this.analytics.profit = data.profit;
      if (data.salesCount !== undefined) this.analytics.salesCount = data.salesCount;
    }
  }
});
