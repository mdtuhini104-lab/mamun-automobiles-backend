import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const useWorkshopBaysStore = defineStore('workshopBays', {
  state: () => ({
    bays: [],
    loading: false,
    allocating: false
  }),
  actions: {
    async fetchBays() {
      this.loading = true;
      try {
        const response = await api.get('/workshop-bays');
        this.bays = response.data.data;
      } catch (error) {
        console.error('Failed to fetch workshop bays', error);
      } finally {
        this.loading = false;
      }
    },
    async allocateBay(jobCardId, bayId) {
      this.allocating = true;
      const toast = useToastStore();
      try {
        // We use the same assign endpoint which accepts workshop_bay_id
        await api.post(`/job-cards/${jobCardId}/assign`, {
          workshop_bay_id: bayId
        });
        toast.success('Workshop bay allocated successfully');
        await this.fetchBays();
      } catch (error) {
        console.error('Failed to allocate bay', error);
        throw error;
      } finally {
        this.allocating = false;
      }
    }
  }
});
