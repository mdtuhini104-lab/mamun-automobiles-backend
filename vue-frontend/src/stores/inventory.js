import { defineStore } from 'pinia';
import api from '../services/api';

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    parts: [],
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
    error: null
  }),
  actions: {
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
        
        // Handle both paginated and unpaginated response formats seamlessly
        const data = response.data.data;
        if (data.data && data.current_page) {
          this.parts = data.data;
          this.pagination = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            per_page: data.per_page
          };
        } else {
          this.parts = data;
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
      this.fetchParts(1); // Reset to page 1 on filter change
    },
    patchStock(eventData) {
      // Patch existing state to prevent re-render loops and pagination resets
      if (!eventData || !eventData.part) return;
      
      const partId = eventData.part.id || eventData.part_id;
      const newStock = eventData.part.stock || eventData.stock;
      
      const index = this.parts.findIndex(p => p.id === partId);
      if (index !== -1) {
        this.parts[index].stock = newStock;
      }
    }
  }
});
