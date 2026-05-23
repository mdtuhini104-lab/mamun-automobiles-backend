import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    hasRole: (state) => (role) => {
      if (!state.user) return false;
      const roles = state.user.roles || [];
      return roles.includes(role) || roles.includes('Super Admin') || roles.includes('admin');
    },
    hasPermission: (state) => (permission) => {
      if (!state.user) return false;
      const roles = state.user.roles || [];
      if (roles.includes('Super Admin') || roles.includes('admin')) return true; // Admins have all permissions
      const perms = state.user.permissions || [];
      return perms.includes(permission);
    }
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
    async fetchUser() {
      if (!this.token) return;
      try {
        const response = await api.get('/auth/user');
        this.user = response.data.data;
      } catch (error) {
        console.error('Failed to fetch user', error);
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
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
