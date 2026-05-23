import { defineStore } from 'pinia';
import api from '../services/api';

export const useHrStore = defineStore('hr', {
  state: () => ({
    attendances: [],
    payrolls: [],
    leaves: [],
    loading: false
  }),
  actions: {
    async fetchAttendances() {
      this.loading = true;
      try {
        const response = await api.get('/hr/attendances');
        this.attendances = response.data.data;
      } finally {
        this.loading = false;
      }
    },
    async fetchPayrolls() {
      this.loading = true;
      try {
        const response = await api.get('/hr/payrolls');
        this.payrolls = response.data.data;
      } finally {
        this.loading = false;
      }
    }
  }
});

