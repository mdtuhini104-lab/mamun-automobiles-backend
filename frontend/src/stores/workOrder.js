import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const useWorkOrderStore = defineStore('workOrder', {
  state: () => ({
    workOrders: [],
    currentWorkOrder: null,
    loading: false,
    saving: false,
    error: null,
  }),
  actions: {
    async fetchWorkOrders(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/work-orders', { params: filters });
        this.workOrders = response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch work orders';
        useToastStore().error(this.error);
      } finally {
        this.loading = false;
      }
    },

    async fetchWorkOrderDetails(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/work-orders/${id}`);
        this.currentWorkOrder = response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch work order details';
        useToastStore().error(this.error);
      } finally {
        this.loading = false;
      }
    },

    async updateStatus(id, status) {
      this.saving = true;
      this.error = null;
      try {
        await api.put(`/work-orders/${id}/status`, { status });
        useToastStore().success(`Work Order status updated to ${status}!`);
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update work order status';
        useToastStore().error(this.error);
        throw err;
      } finally {
        this.saving = false;
      }
    },

    async addConsumption(id, consumptionData) {
      this.saving = true;
      this.error = null;
      try {
        await api.post(`/work-orders/${id}/additional-consumption`, consumptionData);
        useToastStore().success('Additional consumption logged successfully!');
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to log additional consumption';
        useToastStore().error(this.error);
        throw err;
      } finally {
        this.saving = false;
      }
    }
  }
});
