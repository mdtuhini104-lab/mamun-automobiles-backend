import { defineStore } from 'pinia';
import api from '../services/api';

export const useAccountStore = defineStore('account', {
  state: () => ({
    accounts: [],
    loading: false,
    selectedAccount: { name: '', type: 'cash', account_no: '', status: 'active' },
    isModalOpen: false,
    saving: false
  }),
  actions: {
    async fetchAccounts() {
      this.loading = true;
      try {
        const response = await api.get('/accounts');
        this.accounts = response.data.data;
      } finally {
        this.loading = false;
      }
    },
    openModal(account = null) {
      if (account) {
        this.selectedAccount = { ...account };
      } else {
        this.selectedAccount = { name: '', type: 'cash', account_no: '', status: 'active' };
      }
      this.isModalOpen = true;
    },
    closeModal() {
      this.isModalOpen = false;
      this.selectedAccount = { name: '', type: 'cash', account_no: '', status: 'active' };
    },
    async saveAccount() {
      this.saving = true;
      try {
        if (this.selectedAccount.id) {
          await api.put(/accounts/ + this.selectedAccount.id, this.selectedAccount);
        } else {
          await api.post('/accounts', this.selectedAccount);
        }
        await this.fetchAccounts();
        this.closeModal();
      } finally {
        this.saving = false;
      }
    },
    async deleteAccount(id) {
      await api.delete(/accounts/ + id);
      await this.fetchAccounts();
    }
  }
});

