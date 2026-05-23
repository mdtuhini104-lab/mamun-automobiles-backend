import { defineStore } from 'pinia';
import api from '../services/api';

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    parts: [],
    categories: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 15
    },
    filters: {
      search: '',
      category: '',
      sort: 'name',
      direction: 'asc'
    },
    loading: false,
    error: null,
    isModalOpen: false,
    selectedPart: null,
    saving: false
  }),
  actions: {
    async fetchCategories() {
      try {
        const response = await api.get('/categories');
        this.categories = response.data.data;
      } catch (err) {
        console.error('Failed to fetch categories', err);
      }
    },
    async fetchParts(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const params = {
          page,
          search: this.filters.search,
          category: this.filters.category,
          sort: this.filters.sort,
          direction: this.filters.direction
        };
        const response = await api.get('/parts', { params });
        
        const data = response.data.data;
        const meta = response.data.meta;
        if (meta) {
          this.parts = data;
          this.pagination = {
            current_page: meta.current_page,
            last_page: meta.last_page,
            total: meta.total,
            per_page: meta.per_page
          };
        } else if (data && data.data) {
          this.parts = data.data;
          this.pagination = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            per_page: data.per_page
          };
        } else {
          this.parts = data || [];
        }
      } catch (err) {
        this.error = 'Failed to fetch inventory.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    setFilter(key, value) {
      this.filters[key] = value;
      this.fetchParts(1);
    },
    openModal(part = null) {
      this.selectedPart = part ? { ...part } : { 
        name: '', sku: '', barcode: '', brand: '', category_id: '', 
        cost_price: 0, sale_price: 0, stock_quantity: 0, 
        low_stock_threshold: 5, rack_location: '', unit_type: 'pcs' 
      };
      this.isModalOpen = true;
    },
    closeModal() {
      this.isModalOpen = false;
      this.selectedPart = null;
    },
    async savePart() {
      this.saving = true;
      try {
        if (this.selectedPart.id) {
          await api.put(/parts/ + this.selectedPart.id, this.selectedPart);
        } else {
          await api.post('/parts', this.selectedPart);
        }
        await this.fetchParts(this.pagination.current_page);
        this.closeModal();
      } catch (err) {
        console.error('Failed to save part', err);
        throw err;
      } finally {
        this.saving = false;
      }
    },
    async deletePart(id) {
      try {
        await api.delete(/parts/ + id);
        await this.fetchParts(this.pagination.current_page);
      } catch (err) {
        console.error('Failed to delete part', err);
        throw err;
      }
    }
  }
});
