import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const useWorkforceStore = defineStore('workforce', {
  state: () => ({
    employees: [],
    skills: [],
    departments: [],
    designations: [],
    shifts: [],
    loading: false,
    saving: false
  }),
  actions: {
    async fetchEmployees(filters = {}) {
      this.loading = true;
      try {
        const response = await api.get('/workforce/employees', { params: filters });
        this.employees = response.data.data;
      } catch (error) {
        console.error('Failed to load employees', error);
      } finally {
        this.loading = false;
      }
    },
    async fetchLookups() {
      try {
        const [skillsRes, deptsRes, desgsRes, shiftsRes] = await Promise.all([
          api.get('/workforce/skills'),
          api.get('/workforce/departments'),
          api.get('/workforce/designations'),
          api.get('/workforce/shifts')
        ]);
        this.skills = skillsRes.data.data;
        this.departments = deptsRes.data.data;
        this.designations = desgsRes.data.data;
        this.shifts = shiftsRes.data.data;
      } catch (error) {
        console.error('Failed to load workforce lookups', error);
      }
    },
    async assignWorkforce(jobCardId, payload) {
      this.saving = true;
      const toast = useToastStore();
      try {
        const response = await api.post(`/job-cards/${jobCardId}/assign`, payload);
        toast.success('Workforce delegation updated successfully');
        return response.data.data;
      } catch (error) {
        console.error('Failed to assign workforce', error);
        throw error;
      } finally {
        this.saving = false;
      }
    }
  }
});
