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
            <tr v-for="role in safeRoles" :key="role.id" class="hover:bg-slate-50">
              <td class="px-6 py-4">
                <div class="font-medium text-slate-900">{{ role.name }}</div>
                <div v-if="role.name === 'Super Admin'" class="text-xs text-rose-500 mt-1 font-semibold">Bypasses all rules</div>
              </td>
              <td class="px-6 py-4 text-slate-500">-</td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                  {{ role.name === 'Super Admin' ? 'All Permissions' : (role.permissions?.length || 0) }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button v-if="role.name !== 'Super Admin'" @click="openModal(role)" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm mr-4">Edit Matrix</button>
                <button v-if="role.name !== 'Super Admin'" @click="deleteRole(role.id)" class="text-rose-600 hover:text-rose-900 font-medium text-sm">Delete</button>
              </td>
            </tr>
            <tr v-if="isLoading && !safeRoles.length">
              <td colspan="4" class="px-6 py-12 text-center text-slate-500">Loading roles...</td>
            </tr>
            <tr v-else-if="!safeRoles.length">
              <td colspan="4" class="px-6 py-12 text-center text-slate-500">No roles found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Role Matrix Modal -->
    <Teleport to="body">
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 print:hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Glassmorphism Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300" @click="closeModal"></div>
        
        <!-- Modal Content Container -->
        <div class="relative bg-white rounded-xl text-left shadow-2xl border border-slate-200 flex flex-col w-full max-w-5xl max-h-[90vh] z-10 overflow-hidden transform transition-all duration-300">
          <form @submit.prevent="saveRole" class="flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between bg-white">
              <h3 class="text-lg font-bold text-slate-900" id="modal-title">
                {{ editingRole ? 'Edit Role & Permissions' : 'Create New Role' }}
              </h3>
              <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 rounded hover:bg-slate-100">
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>
            
            <!-- Body -->
            <div class="px-6 py-5 overflow-y-auto flex-1">
              <!-- Fallback UI if role data is missing while editing -->
              <div v-if="editingRole && (!editingRole.id || !form.name)" class="mb-4 p-4 bg-yellow-50 text-yellow-800 text-sm rounded-lg border border-yellow-200">
                <strong>Warning:</strong> Selected role details are not fully loaded or are invalid. Please close the modal and try again.
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Role Name</label>
                <input type="text" v-model="form.name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow text-slate-900" placeholder="e.g. Senior Technician">
              </div>

              <!-- Loader if permissions list is loading -->
              <div v-if="isLoading" class="mt-6 p-8 text-center text-slate-500 border border-dashed border-slate-200 rounded-lg">
                <svg class="animate-spin h-6 w-6 text-indigo-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading permissions matrix...
              </div>

              <!-- Fallback if permissions list is empty -->
              <div v-else-if="!hasPermissions" class="mt-6 p-8 text-center text-slate-500 border border-dashed border-slate-200 rounded-lg">
                No permissions or modules available in the database.
              </div>

              <!-- Permissions Matrix Table -->
              <div v-else class="mt-6">
                <h4 class="text-md font-semibold text-slate-800 mb-3">Permission Matrix</h4>
                <div class="border border-slate-200 rounded-lg overflow-hidden overflow-x-auto">
                  <table class="min-w-full divide-y divide-slate-200 table-fixed">
                    <thead class="bg-slate-50">
                      <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/4">Module</th>
                        <th scope="col" class="px-2 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-12" v-for="action in availableActions" :key="action">
                          {{ action }}
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                      <tr v-for="(modulePerms, moduleName) in safeGroupedPermissions" :key="moduleName" class="hover:bg-slate-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 capitalize">
                          {{ typeof moduleName === 'string' ? moduleName.replace(/_/g, ' ') : (moduleName || '') }}
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
            
            <!-- Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3 rounded-b-xl">
              <button type="button" @click="closeModal" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 bg-white hover:bg-slate-50 font-medium transition-colors text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                Cancel
              </button>
              <button type="submit" :disabled="isSaving" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors text-sm disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center gap-2">
                <svg v-if="isSaving" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSaving ? 'Saving...' : 'Save Role Matrix' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const roles = ref([]);
const groupedPermissions = ref({});
const isModalOpen = ref(false);
const editingRole = ref(null);
const isSaving = ref(false);
const isLoading = ref(false);
const isInitialized = ref(false);
const toastStore = useToastStore();

const availableActions = ['view', 'create', 'edit', 'delete', 'approve', 'print', 'export', 'execute'];

const form = ref({
  name: '',
  permissions: []
});

const safeRoles = computed(() => {
  if (!Array.isArray(roles.value)) return [];
  return roles.value.filter(role => role && typeof role === 'object' && role.id && typeof role.name === 'string');
});

const safeGroupedPermissions = computed(() => {
  const gp = groupedPermissions.value;
  if (!gp || typeof gp !== 'object' || Array.isArray(gp)) {
    return {};
  }
  const cleaned = {};
  for (const [moduleName, perms] of Object.entries(gp)) {
    if (!moduleName || typeof moduleName !== 'string') continue;
    if (Array.isArray(perms)) {
      cleaned[moduleName] = perms.filter(p => p && typeof p === 'object' && typeof p.name === 'string');
    } else {
      cleaned[moduleName] = [];
    }
  }
  return cleaned;
});

const hasPermissions = computed(() => {
  const keys = Object.keys(safeGroupedPermissions.value);
  return keys.length > 0;
});

const loadData = async () => {
  isLoading.value = true;
  try {
    const [rolesRes, permsRes] = await Promise.all([
      api.get('/roles'),
      api.get('/permissions')
    ]);
    roles.value = Array.isArray(rolesRes.data?.data) ? rolesRes.data.data : [];
    groupedPermissions.value = (permsRes.data?.data && typeof permsRes.data.data === 'object' && !Array.isArray(permsRes.data.data))
      ? permsRes.data.data
      : {};
    isInitialized.value = true;
  } catch (error) {
    console.error('Failed to load roles and permissions', error);
    toastStore.error('Failed to retrieve role/permission matrix data from the server.');
  } finally {
    isLoading.value = false;
  }
};

const hasActionForModule = (module, action) => {
  if (!module || typeof module !== 'string' || !action || typeof action !== 'string') return false;
  const perms = safeGroupedPermissions.value[module];
  if (!Array.isArray(perms)) return false;
  return perms.some(p => p && typeof p.name === 'string' && p.name.endsWith(`.${action}`));
};

const getPermissionName = (module, action) => {
  if (!module || typeof module !== 'string' || !action || typeof action !== 'string') return '';
  return `${module}.${action}`;
};

const openModal = (role = null) => {
  try {
    if (isLoading.value && !isInitialized.value) {
      toastStore.warning('Please wait, loading permissions matrix...');
      return;
    }
    
    // Add try/catch and log the payload structure
    console.log('RolesList: openModal called with role payload:', role ? JSON.parse(JSON.stringify(role)) : null);
    
    editingRole.value = role;
    if (role) {
      form.value.name = typeof role.name === 'string' ? role.name : '';
      
      // Defensively parse and map permissions
      form.value.permissions = (role && Array.isArray(role.permissions))
        ? role.permissions
            .map(p => {
              if (!p) return null;
              if (typeof p === 'string') return p;
              if (typeof p === 'object') {
                if (typeof p.name === 'string') return p.name;
                if (typeof p.id !== 'undefined') return String(p.id);
              }
              return null;
            })
            .filter(name => typeof name === 'string' && name.length > 0)
        : [];
    } else {
      form.value.name = '';
      form.value.permissions = [];
    }
    isModalOpen.value = true;
  } catch (err) {
    console.error('RolesList: Error during openModal initialization', err);
    toastStore.error('Error opening matrix editor. See console for details.');
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  editingRole.value = null;
  form.value.name = '';
  form.value.permissions = [];
};

const saveRole = async () => {
  if (!form.value.name || typeof form.value.name !== 'string' || !form.value.name.trim()) {
    toastStore.warning('Role name is required.');
    return;
  }
  isSaving.value = true;
  try {
    const payload = {
      name: form.value.name.trim(),
      permissions: Array.isArray(form.value.permissions) ? form.value.permissions : []
    };

    console.log('RolesList: Saving role with payload:', JSON.parse(JSON.stringify(payload)));

    if (editingRole.value && editingRole.value.id) {
      await api.put(`/roles/${editingRole.value.id}`, payload);
    } else {
      await api.post('/roles', payload);
    }
    
    await loadData();
    closeModal();
    toastStore.success('Role saved successfully.');
  } catch (error) {
    console.error('Failed to save role', error);
    toastStore.error(error.response?.data?.message || 'Failed to save role matrix.');
  } finally {
    isSaving.value = false;
  }
};

const deleteRole = async (id) => {
  if (!id) return;
  if (confirm('Are you sure you want to delete this role?')) {
    try {
      await api.delete(`/roles/${id}`);
      await loadData();
      toastStore.success('Role deleted successfully.');
    } catch (error) {
      console.error('Failed to delete role', error);
      toastStore.error(error.response?.data?.message || 'Failed to delete role.');
    }
  }
};

onMounted(() => {
  loadData();
});
</script>

