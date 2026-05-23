import { defineStore } from 'pinia';
import api from '../services/api';

export const useTransactionStore = defineStore('transaction', {
  state: () => ({
    transactions: [],
    categories: [],
    accounts: [],
    loading: false,
    filters: { type: '', search: '' },
    selectedTransaction: { account_id: '', category_id: '', type: 'expense', amount: 0, payment_method: 'cash', date: new Date().toISOString().split('T')[0], description: '' },
    isModalOpen: false,
    saving: false
  }),
  actions: {
    async fetchTransactions() {
      this.loading = true;
      try {
        const params = new URLSearchParams(this.filters).toString();
        const response = await api.get('/transactions?' + params);
        this.transactions = response.data.data;
      } finally {
        this.loading = false;
      }
    },
    async fetchCategories() {
      const response = await api.get('/categories');
      this.categories = response.data.data;
    },
    async fetchAccounts() {
      const response = await api.get('/accounts');
      this.accounts = response.data.data;
    },
    setFilter(key, value) {
      this.filters[key] = value;
      this.fetchTransactions();
    },
    openModal(tx = null, type = 'expense') {
      if (tx) {
        this.selectedTransaction = { ...tx };
      } else {
        this.selectedTransaction = { account_id: '', category_id: '', type: type, amount: 0, payment_method: 'cash', date: new Date().toISOString().split('T')[0], description: '' };
      }
      if (this.categories.length === 0) this.fetchCategories();
      if (this.accounts.length === 0) this.fetchAccounts();
      this.isModalOpen = true;
    },
    closeModal() {
      this.isModalOpen = false;
    },
    async saveTransaction() {
      this.saving = true;
      try {
        if (this.selectedTransaction.id) {
          await api.put('/transactions/' + this.selectedTransaction.id, this.selectedTransaction);
        } else {
          await api.post('/transactions', this.selectedTransaction);
        }
        await this.fetchTransactions();
        this.closeModal();
      } finally {
        this.saving = false;
      }
    },
    async deleteTransaction(id) {
      await api.delete('/transactions/' + id);
      await this.fetchTransactions();
    }
  }
});

