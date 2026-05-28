import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const useJobCardTasksStore = defineStore('jobCardTasks', {
  state: () => ({
    loading: false,
    saving: false
  }),
  actions: {
    async createTask(jobCardId, payload) {
      this.saving = true;
      const toast = useToastStore();
      try {
        const response = await api.post(`/job-cards/${jobCardId}/tasks`, payload);
        toast.success('Task created successfully');
        return response.data.data;
      } catch (error) {
        console.error('Failed to create task', error);
        throw error;
      } finally {
        this.saving = false;
      }
    },
    async assignTask(jobCardId, taskId, employeeId) {
      this.saving = true;
      const toast = useToastStore();
      try {
        const response = await api.post(`/job-cards/${jobCardId}/tasks/${taskId}/assign`, {
          employee_id: employeeId
        });
        toast.success('Technician assigned to task successfully');
        return response.data.data;
      } catch (error) {
        console.error('Failed to assign task', error);
        throw error;
      } finally {
        this.saving = false;
      }
    },
    async completeTaskAssignment(assignmentId) {
      this.saving = true;
      const toast = useToastStore();
      try {
        await api.post(`/job-cards/task-assignments/${assignmentId}/complete`);
        toast.success('Task assignment marked as completed');
      } catch (error) {
        console.error('Failed to complete task assignment', error);
        throw error;
      } finally {
        this.saving = false;
      }
    },
    async completeJobAssignment(assignmentId, laborHours) {
      this.saving = true;
      const toast = useToastStore();
      try {
        await api.post(`/job-cards/assignments/${assignmentId}/complete`, {
          labor_hours: laborHours
        });
        toast.success('Technician assignment completed and labor hours logged');
      } catch (error) {
        console.error('Failed to complete job assignment', error);
        throw error;
      } finally {
        this.saving = false;
      }
    }
  }
});
