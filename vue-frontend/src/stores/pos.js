import { defineStore } from 'pinia';
import api from '../services/api';

export const usePosStore = defineStore('pos', {
  state: () => ({
    products: [],
    customers: [],
    cart: [],
    search: '',
    discount: 0,
    customerId: '',
    paymentMethod: 'cash',
    splitCash: 0,
    splitCard: 0,
    loadingProducts: false,
    loadingCustomers: false,
    checkoutLoading: false,
    checkoutError: null,
    stockWarnings: []
  }),
  getters: {
    cartSubtotal: (state) => state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0),
    cartTotal: (state) => Math.max(0, state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) - state.discount)
  },
  actions: {
    async fetchProducts(searchQuery = '') {
      this.loadingProducts = true;
      try {
        const res = await api.get(`/parts?search=${searchQuery}`);
        // Support paginated or flat
        this.products = res.data.data.data ? res.data.data.data : res.data.data;
      } catch (err) {
        console.error('Failed to fetch POS products', err);
      } finally {
        this.loadingProducts = false;
      }
    },
    async fetchCustomers() {
      this.loadingCustomers = true;
      try {
        const res = await api.get('/customers');
        this.customers = res.data.data;
      } catch (err) {
        console.error('Failed to fetch customers', err);
      } finally {
        this.loadingCustomers = false;
      }
    },
    addToCart(product) {
      if (product.stock <= 0) {
        // Prevent adding out of stock
        return false;
      }
      
      const existing = this.cart.find(item => item.id === product.id);
      if (existing) {
        if (existing.quantity >= product.stock) return false; // low stock prevention
        existing.quantity += 1;
      } else {
        this.cart.push({ ...product, quantity: 1 });
      }
      return true;
    },
    removeFromCart(id) {
      this.cart = this.cart.filter(item => item.id !== id);
      this.stockWarnings = this.stockWarnings.filter(w => w.id !== id);
    },
    updateQuantity(id, quantity) {
      if (quantity <= 0) {
        this.removeFromCart(id);
        return;
      }
      const cartItem = this.cart.find(item => item.id === id);
      const product = this.products.find(p => p.id === id);
      
      if (cartItem) {
        const maxStock = product ? product.stock : cartItem.stock;
        cartItem.quantity = Math.min(quantity, maxStock);
      }
    },
    clearCart() {
      this.cart = [];
      this.discount = 0;
      this.customerId = '';
      this.paymentMethod = 'cash';
      this.splitCash = 0;
      this.splitCard = 0;
      this.stockWarnings = [];
    },
    patchStock(eventData) {
      if (!eventData || !eventData.part) return;
      const partId = eventData.part.id || eventData.part_id;
      const newStock = eventData.part.stock || eventData.stock;

      // Update product list
      const productIndex = this.products.findIndex(p => p.id === partId);
      if (productIndex !== -1) {
        this.products[productIndex].stock = newStock;
      }

      // Update cart safely without resetting checkout flow
      const cartItem = this.cart.find(item => item.id === partId);
      if (cartItem) {
        cartItem.stock = newStock;
        if (cartItem.quantity > newStock) {
          // Visually warn user
          if (!this.stockWarnings.find(w => w.id === partId)) {
            this.stockWarnings.push({ id: partId, message: `Stock for ${cartItem.name} reduced to ${newStock} externally.` });
          }
          cartItem.quantity = newStock;
          if (cartItem.quantity === 0) {
            this.removeFromCart(partId);
          }
        } else if (this.stockWarnings.find(w => w.id === partId) && newStock >= cartItem.quantity) {
          this.stockWarnings = this.stockWarnings.filter(w => w.id !== partId);
        }
      }
    }
  }
});
