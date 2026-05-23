<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Roles & Permissions 🔒</h1>
        <p class="mt-1 text-sm text-slate-500">Manage user roles and granular access control matrix.</p>
      </div>
      <div class="mt-4 sm:mt-0 flex gap-2">
        <button @click="openModal()" class="btn bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 font-medium flex items-center gap-2 transition-colors">
          <svg class="w-4 h-4" viewBox="0 0 16 16" fill="currentColor"><path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" /></svg>
          Create Role
        </button>
      </div>
    </div>

    <!-- Roles Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
      <div class="overflow-x-auto">
        <table class="table-auto w-full">
          <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="px-6 py-4 text-left">Role Name</th>
              <th class="px-6 py-4 text-left">Assigned Users (Est.)</th>
              <th class="px-6 py-4 text-left">Permissions Count</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="text-sm divide-y divide-slate-200">
            <tr v-for="role in roles" :key="role.id" class="hover:bg-slate-50">
              <td class="px-6 py-4">
                <div class="font-medium text-slate-900">{{ role.name }}</div>
                <div v-if="role.name === 'Super Admin'" class="text-xs text-rose-500 mt-1 font-semibold">Bypasses all rules</div>
              </td>
              <td class="px-6 py-4 text-slate-500">-</td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                  {{ role.name === 'Super Admin' ? 'All Permissions' : role.permissions?.length || 0 }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button v-if="role.name !== 'Super Admin'" @click="openModal(role)" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm mr-4">Edit Matrix</button>
                <button v-if="role.name !== 'Super Admin'" @click="deleteRole(role.id)" class="text-rose-600 hover:text-rose-900 font-medium text-sm">Delete</button>
              </td>
            </tr>
            <tr v-if="!roles.length">
              <td colspan="4" class="px-6 py-12 text-center text-slate-500">No roles found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Role Matrix Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-50 transition-opacity" @click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
          <form @submit.prevent="saveRole">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-semibold text-slate-900" id="modal-title">
                    {{ editingRole ? 'Edit Role & Permissions' : 'Create New Role' }}
                  </h3>
                  
                  <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Role Name</label>
                    <input type="text" v-model="form.name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" placeholder="e.g. Senior Technician">
                  </div>

                  <div class="mt-6">
                    <h4 class="text-md font-semibold text-slate-800 mb-3">Permission Matrix</h4>
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                      <table class="min-w-full divide-y divide-slate-200 table-fixed">
                        <thead class="bg-slate-50">
                          <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-1/4">Module</th>
                            <th scope="col" class="px-2 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-12" v-for="action in availableActions" :key="action">
                              {{ action }}
                            </th>
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                          <tr v-for="(modulePerms, moduleName) in groupedPermissions" :key="moduleName" class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 capitalize">
                              {{ moduleName.replace('_', ' ') }}
                            </td>
                            <td class="px-2 py-4 whitespace-nowrap text-center" v-for="action in availableActions" :key="action">
                              <input 
                                v-if="hasActionForModule(moduleName, action)"
                                type="checkbox" 
                                :value="getPermissionName(moduleName, action)"
                                v-model="form.permissions"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded cursor-pointer"
                              >
                              <span v-else class="text-slate-300">-</span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200">
              <button type="submit" :disabled="isSaving" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                {{ isSaving ? 'Saving...' : 'Save Role Matrix' }}
              </button>
              <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../services/api';

const roles = ref([]);
const groupedPermissions = ref({});
const isModalOpen = ref(false);
const editingRole = ref(null);
const isSaving = ref(false);

const availableActions = ['view', 'create', 'edit', 'delete', 'approve', 'print', 'export', 'execute'];

const form = ref({
  name: '',
  permissions: []
});

const loadData = async () => {
  try {
    const [rolesRes, permsRes] = await Promise.all([
      api.get('/roles'),
      api.get('/permissions')
    ]);
    roles.value = rolesRes.data.data;
    groupedPermissions.value = permsRes.data.data;
  } catch (error) {
    console.error('Failed to load roles and permissions', error);
  }
};

const hasActionForModule = (module, action) => {
  const perms = groupedPermissions.value[module] || [];
  return perms.some(p => p.name.endsWith(`.${action}`));
};

const getPermissionName = (module, action) => {
  return `${module}.${action}`;
};

const openModal = (role = null) => {
  editingRole.value = role;
  if (role) {
    form.value.name = role.name;
    form.value.permissions = role.permissions ? role.permissions.map(p => p.name) : [];
  } else {
    form.value.name = '';
    form.value.permissions = [];
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingRole.value = null;
};

const saveRole = async () => {
  isSaving.value = true;
  try {
    const payload = {
      name: form.value.name,
      permissions: form.value.permissions
    };

    if (editingRole.value) {
      await api.put(`/roles/${editingRole.value.id}`, payload);
    } else {
      await api.post('/roles', payload);
    }
    
    await loadData();
    closeModal();
  } catch (error) {
    console.error('Failed to save role', error);
    alert(error.response?.data?.message || 'Failed to save role');
  } finally {
    isSaving.value = false;
  }
};

const deleteRole = async (id) => {
  if (confirm('Are you sure you want to delete this role?')) {
    try {
      await api.delete(`/roles/${id}`);
      await loadData();
    } catch (error) {
      console.error('Failed to delete role', error);
      alert(error.response?.data?.message || 'Failed to delete role');
    }
  }
};

onMounted(() => {
  loadData();
});
</script>
