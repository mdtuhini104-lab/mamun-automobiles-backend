import { defineStore } from 'pinia';
import api from '../services/api';

export const useStaffStore = defineStore('staff', {
  state: () => ({
    staffMembers: [],
    roles: [],
    loading: false,
    selectedStaff: {
      name: '',
      email: '',
      password: '',
      role: '',
      phone: '',
      address: '',
      nid: '',
      salary: 0,
      joining_date: new Date().toISOString().split('T')[0],
      is_active: true,
      department_id: '',
      designation_id: '',
      shift_id: '',
      employee_code: '',
      status: 'active',
      availability_status: 'available',
      skills: []
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
        this.selectedStaff = {
          id: staff.id,
          name: staff.name,
          email: staff.email,
          password: '',
          role: staff.roles && staff.roles.length > 0 ? staff.roles[0] : '',
          phone: staff.phone || '',
          address: staff.address || '',
          nid: staff.nid || '',
          salary: staff.salary || 0,
          joining_date: staff.joining_date ? staff.joining_date.split('T')[0] : new Date().toISOString().split('T')[0],
          is_active: staff.is_active !== undefined ? !!staff.is_active : true,
          department_id: staff.department_id || staff.employee?.department?.id || '',
          designation_id: staff.designation_id || staff.employee?.designation?.id || '',
          shift_id: staff.shift_id || '',
          employee_code: staff.employee?.employee_code || '',
          status: staff.employee?.status || 'active',
          availability_status: staff.employee?.availability_status || 'available',
          skills: staff.employee?.skills ? staff.employee.skills.map(s => s.id) : []
        };
      } else {
        this.selectedStaff = {
          name: '',
          email: '',
          password: '',
          role: '',
          phone: '',
          address: '',
          nid: '',
          salary: 0,
          joining_date: new Date().toISOString().split('T')[0],
          is_active: true,
          department_id: '',
          designation_id: '',
          shift_id: '',
          employee_code: '',
          status: 'active',
          availability_status: 'available',
          skills: []
        };
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
