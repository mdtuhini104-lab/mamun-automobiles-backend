import { defineStore } from 'pinia';
import api from '../services/api';

export const useStaffStore = defineStore('staff', {
  state: () => ({
    staffMembers: [],
    roles: [],
    loading: false,
    selectedStaff: {
      name: '', email: '', password: '', role: '', phone: '', address: '', nid: '', salary: 0, joining_date: new Date().toISOString().split('T')[0], is_active: true
    },
    isModalOpen: false,
    saving: false
  }),
  actions: {
    async fetchStaff() {
      this.loading = true;
      try {
        const response = await api.get('/users');
        this.staffMembers = response.data.data;
      } finally {
        this.loading = false;
      }
    },
    async fetchRoles() {
      const response = await api.get('/roles');
      this.roles = response.data.data;
    },
    openModal(staff = null) {
      if (staff) {
        this.selectedStaff = { ...staff, role: staff.roles.length > 0 ? staff.roles[0] : '' };
      } else {
        this.selectedStaff = { name: '', email: '', password: '', role: '', phone: '', address: '', nid: '', salary: 0, joining_date: new Date().toISOString().split('T')[0], is_active: true };
      }
      if (this.roles.length === 0) this.fetchRoles();
      this.isModalOpen = true;
    },
    closeModal() {
      this.isModalOpen = false;
    },
    async saveStaff() {
      this.saving = true;
      try {
        if (this.selectedStaff.id) {
          await api.put('/users/' + this.selectedStaff.id, this.selectedStaff);
        } else {
          await api.post('/users', this.selectedStaff);
        }
        await this.fetchStaff();
        this.closeModal();
      } finally {
        this.saving = false;
      }
    },
    async deleteStaff(id) {
      await api.delete('/users/' + id);
      await this.fetchStaff();
    }
  }
});

