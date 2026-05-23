<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Staff Management</h1>
        <p class="text-sm text-slate-500 mt-1">Manage employees, roles and access permissions.</p>
      </div>
      <button @click="staffStore.openModal()" v-if="authStore.hasPermission('staff.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
        New Staff
      </button>
    </div>
    
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Employee</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Salary</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-if="staffStore.loading">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">Loading...</td>
            </tr>
            <tr v-else-if="staffStore.staffMembers.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No staff found.</td>
            </tr>
            <tr v-else v-for="staff in staffStore.staffMembers" :key="staff.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 flex-shrink-0 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold">{{ staff.name.charAt(0) }}</div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-slate-900">{{ staff.name }}</div>
                    <div class="text-xs text-slate-500">{{ staff.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900">{{ staff.phone || 'N/A' }}</div>
                <div class="text-xs text-slate-500">Joined: {{ staff.joining_date || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">{{ staff.roles.length ? staff.roles[0] : 'None' }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">{{ staff.salary ? Number(staff.salary).toLocaleString() : '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset', staff.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20']">{{ staff.is_active ? 'Active' : 'Inactive' }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button v-if="authStore.hasPermission('staff.edit')" @click="staffStore.openModal(staff)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="staffStore.isModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <h3 class="text-xl font-semibold leading-6 text-slate-900">{{ staffStore.selectedStaff.id ? 'Edit Staff' : 'New Staff' }}</h3>
              <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Name</label>
                  <input v-model="staffStore.selectedStaff.name" type="text" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Email</label>
                  <input v-model="staffStore.selectedStaff.email" type="email" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Password <span v-if="staffStore.selectedStaff.id" class="text-xs text-slate-500 font-normal">(Leave blank to keep current)</span></label>
                  <input v-model="staffStore.selectedStaff.password" type="password" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Role</label>
                  <select v-model="staffStore.selectedStaff.role" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <option v-for="role in staffStore.roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Phone</label>
                  <input v-model="staffStore.selectedStaff.phone" type="text" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Salary</label>
                  <input v-model="staffStore.selectedStaff.salary" type="number" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Joining Date</label>
                  <input v-model="staffStore.selectedStaff.joining_date" type="date" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div class="flex items-center mt-8">
                  <input id="is_active" v-model="staffStore.selectedStaff.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                  <label for="is_active" class="ml-3 block text-sm font-medium leading-6 text-slate-900">Active Employee</label>
                </div>
                <div class="col-span-full">
                  <label class="block text-sm font-medium leading-6 text-slate-900">Address</label>
                  <textarea v-model="staffStore.selectedStaff.address" rows="2" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                </div>
              </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button @click="handleSave" type="button" class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Save</button>
              <button @click="staffStore.closeModal()" type="button" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useStaffStore } from '../../stores/staff';
import { useAuthStore } from '../../stores/auth';

const staffStore = useStaffStore();
const authStore = useAuthStore();

onMounted(() => {
  staffStore.fetchStaff();
});

const handleSave = async () => {
  try {
    await staffStore.saveStaff();
  } catch (e) {
    alert('Failed to save staff');
  }
};
</script>

