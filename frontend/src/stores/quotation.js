import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const useQuotationStore = defineStore('quotation', {
  state: () => ({
    quotations: [],
    currentQuotation: null,
    loading: false,
    saving: false,
    error: null,
  }),
  actions: {
    async fetchQuotations(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/quotations', { params: filters });
        this.quotations = response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch quotations';
        useToastStore().error(this.error);
      } finally {
        this.loading = false;
      }
    },

    async fetchQuotationDetails(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/quotations/${id}`);
        this.currentQuotation = response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch quotation details';
        useToastStore().error(this.error);
      } finally {
        this.loading = false;
      }
    },

    async createQuotation(data) {
      this.saving = true;
      this.error = null;
      try {
        const response = await api.post('/quotations', data);
        useToastStore().success('Quotation draft generated successfully!');
        return response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to generate quotation draft';
        useToastStore().error(this.error);
        throw err;
      } finally {
        this.saving = false;
      }
    },

    async reviseQuotation(id, data) {
      this.saving = true;
      this.error = null;
      try {
        const response = await api.put(`/quotations/${id}/revise`, data);
        useToastStore().success(`Quotation successfully revised to version ${response.data.data.version}!`);
        this.currentQuotation = response.data.data;
        return response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to revise quotation';
        useToastStore().error(this.error);
        throw err;
      } finally {
        this.saving = false;
      }
    },

    async approveQuotation(id, approvalData) {
      this.saving = true;
      this.error = null;
      try {
        const response = await api.post(`/quotations/${id}/approve`, approvalData);
        useToastStore().success('Customer approval recorded and active Work Order generated!');
        return response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to record customer approval';
        useToastStore().error(this.error);
        throw err;
      } finally {
        this.saving = false;
      }
    },

    async removeQuotationItem(itemId, reason) {
      this.saving = true;
      this.error = null;
      try {
        await api.delete(`/quotation-items/${itemId}`, { data: { reason } });
        useToastStore().success('Line item removed and deleted audibly.');
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to remove line item';
        useToastStore().error(this.error);
        throw err;
      } finally {
        this.saving = false;
      }
    }
  }
});
