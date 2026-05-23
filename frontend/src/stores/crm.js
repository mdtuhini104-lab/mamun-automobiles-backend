import { defineStore } from 'pinia';
import api from '../services/api';

export const useCrmStore = defineStore('crm', {
  state: () => ({
    appointments: [],
    timeline: null,
    loading: false
  }),
  actions: {
    async fetchAppointments() {
      this.loading = true;
      try {
        const res = await api.get('/crm/appointments');
        this.appointments = res.data.data;
      } finally {
        this.loading = false;
      }
    },
    async updateAppointmentStatus(id, status) {
      await api.patch('/crm/appointments/' + id, { status });
      await this.fetchAppointments();
    },
    async fetchCustomerTimeline(customerId) {
      this.loading = true;
      try {
        const res = await api.get('/crm/customers/' + customerId + '/timeline');
        this.timeline = res.data.data;
      } finally {
        this.loading = false;
      }
    }
  }
});

