import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const useCustomerStore = defineStore('customer', {
  state: () => ({
    customers: [],
    currentCustomer: null,
    loading: false,
    saving: false,
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
    },
    filters: {
      search: '',
      sort_by: 'created_at',
      sort_order: 'desc',
    }
  }),

  actions: {
    async fetchCustomers(page = 1) {
      this.loading = true;
      try {
        const { data } = await api.get('/customers', {
          params: {
            page,
            search: this.filters.search,
            sort_by: this.filters.sort_by,
            sort_order: this.filters.sort_order
          }
        });
        this.customers = data.data;
        this.pagination = data.meta;
      } catch (error) {
        console.error('Failed to fetch customers', error);
      } finally {
        this.loading = false;
      }
    },

    setFilter(key, value) {
      this.filters[key] = value;
      this.fetchCustomers(1);
    },

    async fetchCustomer(id) {
      this.loading = true;
      try {
        const { data } = await api.get(`/customers/${id}`);
        this.currentCustomer = data.data;
        return data.data;
      } catch (error) {
        console.error('Failed to fetch customer', error);
        return null;
      } finally {
        this.loading = false;
      }
    },

    async createCustomer(payload) {
      this.saving = true;
      try {
        const { data } = await api.post('/customers', payload);
        const toast = useToastStore();
        toast.success('Customer created successfully');
        return data.data;
      } catch (error) {
        throw error;
      } finally {
        this.saving = false;
      }
    },

    async updateCustomer(id, payload) {
      this.saving = true;
      try {
        const { data } = await api.put(`/customers/${id}`, payload);
        const toast = useToastStore();
        toast.success('Customer updated successfully');
        if (this.currentCustomer && this.currentCustomer.id === id) {
          this.currentCustomer = data.data;
        }
        return data.data;
      } catch (error) {
        throw error;
      } finally {
        this.saving = false;
      }
    },

    async deleteCustomer(id) {
      try {
        await api.delete(`/customers/${id}`);
        const toast = useToastStore();
        toast.success('Customer deleted successfully');
        await this.fetchCustomers(this.pagination.current_page);
        return true;
      } catch (error) {
        return false;
      }
    }
  }
});
