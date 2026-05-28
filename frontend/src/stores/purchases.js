import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const usePurchaseStore = defineStore('purchases', {
  state: () => ({
    purchases: [],
    suppliers: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 15
    },
    filters: {
      search: '',
      status: '',
      sort: 'created_at',
      direction: 'desc'
    },
    loading: false,
    error: null,
    
    // Isolated form state for creating a new purchase
    form: {
      supplier_id: '',
      items: [],
      notes: ''
    },
    approvingIds: [], // Track which purchases are currently being approved to prevent duplicates
    isModalOpen: false,
    saving: false
  }),
  getters: {
    formTotal: (state) => state.form.items.reduce((sum, item) => sum + (item.cost * item.quantity), 0)
  },
  actions: {
    async fetchPurchases(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const params = {
          page,
          search: this.filters.search,
          status: this.filters.status,
          sort: this.filters.sort,
          direction: this.filters.direction
        };
        const response = await api.get('/purchases', { params });
        
        const data = response.data.data;
        if (data.data && data.current_page) {
          this.purchases = data.data;
          this.pagination = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            per_page: data.per_page
          };
        } else {
          this.purchases = data;
        }
      } catch (err) {
        this.error = 'Failed to fetch purchases.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    async fetchSuppliers() {
      try {
        const res = await api.get('/suppliers');
        this.suppliers = res.data.data;
      } catch (err) {
        console.error('Failed to fetch suppliers', err);
      }
    },
    setFilter(key, value) {
      this.filters[key] = value;
      this.fetchPurchases(1);
    },
    
    // Form management
    openModal() {
      this.clearForm();
      this.isModalOpen = true;
    },
    closeModal() {
      this.isModalOpen = false;
      this.clearForm();
    },
    addItemToForm(product) {
      const existing = this.form.items.find(item => item.product_id === product.id);
      if (existing) {
        existing.quantity += 1;
      } else {
        this.form.items.push({
          product_id: product.id,
          name: product.name,
          cost: product.cost_price || product.price,
          quantity: 1
        });
      }
    },
    removeItemFromForm(index) {
      this.form.items.splice(index, 1);
    },
    clearForm() {
      this.form = { supplier_id: '', items: [], notes: '' };
    },
    
    async savePurchase() {
      if (!this.form.supplier_id || this.form.items.length === 0) return;
      this.saving = true;
      try {
        await api.post('/purchases', {
          supplier_id: this.form.supplier_id,
          items: this.form.items,
          notes: this.form.notes
        });
        await this.fetchPurchases(1);
        this.closeModal();
      } catch (err) {
        console.error('Failed to create purchase', err);
        throw err;
      } finally {
        this.saving = false;
      }
    },

    // Approval Safety Workflow
    async approvePurchase(id) {
      if (this.approvingIds.includes(id)) return;
      
      const purchase = this.purchases.find(p => p.id === id);
      if (purchase && purchase.status === 'approved') {
        const toast = useToastStore();
        toast.warning('This purchase was already approved externally.');
        return false;
      }

      this.approvingIds.push(id);
      try {
        const res = await api.post(`/purchases/${id}/approve`);
        // Update local state
        const index = this.purchases.findIndex(p => p.id === id);
        if (index !== -1) {
          this.purchases[index].status = 'approved';
        }
        return true;
      } catch (err) {
        console.error('Failed to approve purchase', err);
        throw err;
      } finally {
        this.approvingIds = this.approvingIds.filter(aId => aId !== id);
      }
    },
    
    // Websocket state patch for approval
    patchPurchaseStatus(eventData) {
      if (!eventData || !eventData.purchase) return;
      
      const purchaseId = eventData.purchase.id || eventData.purchase_id;
      const status = eventData.purchase.status || eventData.status;
      
      const index = this.purchases.findIndex(p => p.id === purchaseId);
      if (index !== -1) {
        this.purchases[index].status = status;
      }
    }
  }
});
