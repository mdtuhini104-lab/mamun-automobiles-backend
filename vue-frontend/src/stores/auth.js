import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  actions: {
    async login(credentials) {
      try {
        const response = await api.post('/auth/login', credentials);
        this.token = response.data.data.token;
        this.user = response.data.data.user || null;
        localStorage.setItem('token', this.token);
        return true;
      } catch (error) {
        console.error('Login failed', error);
        throw error;
      }
    },
    async logout() {
      try {
        if (this.token) {
          await api.post('/auth/logout');
        }
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
      }
    }
  }
});
