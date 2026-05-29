import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const useCustomerPricingStore = defineStore('customerPricing', {
  state: () => ({
    rates: [],
    loading: false,
    saving: false,
    error: null,
  }),
  actions: {
    async fetchRates(customerId) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/customers/${customerId}/pricing`);
        this.rates = response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch contract rates';
        useToastStore().error(this.error);
      } finally {
        this.loading = false;
      }
    },

    async calculatePrice(customerId, partId = null, serviceName = null) {
      this.loading = true;
      try {
        const response = await api.get(`/customers/${customerId}/pricing/calculate`, {
          params: { part_id: partId, labor_service_name: serviceName }
        });
        return response.data.data.price;
      } catch (err) {
        console.error('Failed to calculate customized price', err);
        return 0.00;
      } finally {
        this.loading = false;
      }
    },

    async storeRate(data) {
      this.saving = true;
      this.error = null;
      try {
        const response = await api.post('/customers/pricing', data);
        useToastStore().success('Corporate contract price rate recorded successfully!');
        // Refresh local list if same customer
        if (response.data.data.customer_id === data.customer_id) {
          await this.fetchRates(data.customer_id);
        }
        return response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to save contract rate';
        useToastStore().error(this.error);
        throw err;
      } finally {
        this.saving = false;
      }
    }
  }
});
