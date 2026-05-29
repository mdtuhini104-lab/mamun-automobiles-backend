<template>
  <div class="space-y-6">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Workforce & Staff Management</h1>
        <p class="text-sm text-slate-500 mt-1">Manage enterprise employee profiles, departments, designations, skill matrices, and access roles.</p>
      </div>
      <button @click="staffStore.openModal()" v-if="authStore.hasPermission('staff.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Add Employee
      </button>
    </div>
    
    <!-- Table list -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50/75 border-b border-slate-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Employee & Code</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Contact Details</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Department & Job Title</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">System Role</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Shift & Salary</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status & Availability</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-if="staffStore.loading">
              <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">
                <div class="flex items-center justify-center gap-2">
                  <div class="w-4 h-4 rounded-full border-2 border-indigo-600 border-t-transparent animate-spin"></div>
                  <span>Loading workforce profiles...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="staffStore.staffMembers.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">No workforce profiles found.</td>
            </tr>
            <tr v-else v-for="staff in staffStore.staffMembers" :key="staff.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-700 font-bold shadow-sm">{{ staff.name.charAt(0) }}</div>
                  <div class="ml-4">
                    <div class="text-sm font-semibold text-slate-900">{{ staff.name }}</div>
                    <div class="flex items-center gap-1.5 mt-0.5">
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xxs font-mono bg-slate-100 text-slate-600 border border-slate-200">
                        {{ staff.employee?.employee_code || 'No Code' }}
                      </span>
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900 font-medium">{{ staff.phone || 'N/A' }}</div>
                <div class="text-xs text-slate-500">{{ staff.email }}</div>
                <div class="text-xxs text-slate-400 mt-0.5">Joined: {{ staff.joining_date || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900 font-semibold">{{ staff.employee?.department?.name || staff.department?.name || 'N/A' }}</div>
                <div class="text-xs text-slate-500 font-medium">{{ staff.employee?.designation?.name || staff.designation?.name || 'N/A' }}</div>
                <!-- Skills indicator -->
                <div v-if="staff.employee?.skills?.length" class="flex flex-wrap gap-1 mt-1.5 max-w-[200px]">
                  <span v-for="skill in staff.employee.skills" :key="skill.id" class="inline-flex items-center px-1.5 py-0.5 rounded text-xxs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    {{ skill.name }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">{{ staff.roles.length ? staff.roles[0] : 'None' }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900 font-semibold">৳{{ staff.salary ? Number(staff.salary).toLocaleString() : '-' }}</div>
                <span class="inline-flex items-center text-xxs text-slate-500 mt-0.5">
                  {{ getShiftName(staff.shift_id) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap space-y-1">
                <div class="flex items-center gap-1.5">
                  <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xxs font-semibold ring-1 ring-inset', staff.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20']">
                    {{ staff.is_active ? 'Active Employee' : 'Inactive' }}
                  </span>
                </div>
                <div class="flex items-center gap-1.5">
                  <span :class="['inline-flex items-center rounded px-1.5 py-0.5 text-xxs font-medium ring-1 ring-inset', getAvailabilityBadgeClass(staff.employee?.availability_status)]">
                    Avail: {{ formatStatus(staff.employee?.availability_status || 'available') }}
                  </span>
                  <span :class="['inline-flex items-center rounded px-1.5 py-0.5 text-xxs font-medium ring-1 ring-inset', getStatusBadgeClass(staff.employee?.status)]">
                    Workforce: {{ formatStatus(staff.employee?.status || 'active') }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold">
                <button v-if="authStore.hasPermission('staff.edit')" @click="staffStore.openModal(staff)" class="text-indigo-600 hover:text-indigo-900 transition-colors mr-3">Edit</button>
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
          <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200">
            <div class="bg-white px-6 pb-6 pt-6">
              <div class="flex justify-between items-start border-b border-slate-100 pb-3 mb-6">
                <div>
                  <h3 class="text-lg font-bold leading-6 text-slate-900">{{ staffStore.selectedStaff.id ? 'Edit Employee Profile' : 'New Employee Profile' }}</h3>
                  <p class="text-xs text-slate-500 mt-0.5">Ensure information is updated accurately in accordance with operational limits.</p>
                </div>
                <button @click="staffStore.closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <div class="space-y-6">
                <!-- Section 1: Basic Information -->
                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-1 mb-4 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    Basic Information
                  </h4>
                  <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Full Name</label>
                      <input v-model="staffStore.selectedStaff.name" type="text" placeholder="John Doe" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Email Address</label>
                      <input v-model="staffStore.selectedStaff.email" type="email" placeholder="john.doe@company.com" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">
                        Password 
                        <span v-if="staffStore.selectedStaff.id" class="text-xs text-slate-400 font-normal">(Leave blank to keep)</span>
                      </label>
                      <input v-model="staffStore.selectedStaff.password" type="password" placeholder="••••••••" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Phone Number</label>
                      <input v-model="staffStore.selectedStaff.phone" type="text" placeholder="017XXXXXXXX" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div class="sm:col-span-2">
                      <label class="block text-sm font-semibold text-slate-700">Residential Address</label>
                      <input v-model="staffStore.selectedStaff.address" type="text" placeholder="House 12, Road 4, Sector 7, Uttara, Dhaka" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm">
                    </div>
                  </div>
                </div>

                <!-- Section 2: Authentication & System Permissions -->
                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-1 mb-4 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    Authentication & Permissions
                  </h4>
                  <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">System Role</label>
                      <select v-model="staffStore.selectedStaff.role" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm bg-white">
                        <option value="">Select System Role</option>
                        <option v-for="roleName in SYSTEM_ROLES" :key="roleName" :value="roleName">{{ roleName }}</option>
                      </select>
                      <p class="text-xxs text-slate-400 mt-1">Spatie security roles strictly separate permissions and menu visibility.</p>
                    </div>
                  </div>
                </div>

                <!-- Section 3: Workforce Profile Information -->
                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-1 mb-4 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.453.258-.75.258H3.75c-.297 0-.556-.093-.75-.258m16.5 0v-4.25m-16.5 4.25a2.18 2.18 0 0 1-.75-1.661V8.706c0-1.081.768-2.015 1.837-2.175a48.114 48.114 0 0 1 3.413-.387m0 0V6.269c0-1.026.708-1.93 1.72-2.038a48.972 48.972 0 0 1 7.96 0c1.012.108 1.72 1.012 1.72 2.038V6.27m-9.75 0V6.27" />
                    </svg>
                    Workforce Information
                  </h4>
                  <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Department</label>
                      <select v-model="staffStore.selectedStaff.department_id" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm bg-white">
                        <option value="">Select Department</option>
                        <option v-for="dept in workforceStore.departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Designation (Job Title)</label>
                      <select v-model="staffStore.selectedStaff.designation_id" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm bg-white">
                        <option value="">Select Designation</option>
                        <option v-for="desg in workforceStore.designations" :key="desg.id" :value="desg.id">{{ desg.name }}</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Operational Shift</label>
                      <select v-model="staffStore.selectedStaff.shift_id" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm bg-white">
                        <option value="">Select Shift</option>
                        <option v-for="shift in workforceStore.shifts" :key="shift.id" :value="shift.id">
                          {{ shift.name }} ({{ shift.start_time.substring(0,5) }} - {{ shift.end_time.substring(0,5) }})
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Employment Status</label>
                      <select v-model="staffStore.selectedStaff.status" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm bg-white">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                        <option value="resigned">Resigned</option>
                        <option value="terminated">Terminated</option>
                        <option value="on_leave">On Leave</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Real-Time Availability</label>
                      <select v-model="staffStore.selectedStaff.availability_status" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm bg-white">
                        <option value="available">Available</option>
                        <option value="busy">Busy (Working on Task)</option>
                        <option value="assigned">Assigned (Pre-allocated)</option>
                        <option value="on_leave">On Leave</option>
                        <option value="offline">Offline</option>
                      </select>
                    </div>
                    
                    <!-- Skills Checkbox Matrix -->
                    <div class="col-span-full">
                      <label class="block text-sm font-semibold text-slate-700 mb-2">Skills & Operational Capabilities</label>
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 border border-slate-200 rounded-lg p-4 bg-slate-50/50">
                        <div v-for="skill in workforceStore.skills" :key="skill.id" class="relative flex items-start p-2 hover:bg-white hover:shadow-sm rounded-md transition-all border border-transparent hover:border-slate-100">
                          <div class="flex h-6 items-center">
                            <input :id="'skill-' + skill.id" v-model="staffStore.selectedStaff.skills" :value="skill.id" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                          </div>
                          <div class="ml-3 text-sm leading-6">
                            <label :for="'skill-' + skill.id" class="font-bold text-slate-900">{{ skill.name }}</label>
                            <p class="text-xs text-slate-500 font-medium">{{ skill.description }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section 4: Employment Details -->
                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-1 mb-4 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h.007m-.007 3h.007m-.007 3h.007m-1.5-6h1.5m-1.5 3h1.5m-1.5 3h1.5m-1.5 3H12m-9-3h12m-9 3h12m0 0v-3.75m0 3.75h-.007m0 0v-.007m0 0h.008v.007Zm-3.75-3.75h.008v.007h-.008v-.007Zm0 3h.008v.007h-.008v-.007Zm0 3h.008v.007h-.008v-.007Z" />
                    </svg>
                    Employment Details
                  </h4>
                  <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-4">
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Employee Code</label>
                      <input v-model="staffStore.selectedStaff.employee_code" type="text" placeholder="Auto-generated if blank" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm bg-slate-50" :disabled="!!staffStore.selectedStaff.id">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">NID / Passport Number</label>
                      <input v-model="staffStore.selectedStaff.nid" type="text" placeholder="199XXXXXXXXXX" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Base Salary (৳)</label>
                      <input v-model="staffStore.selectedStaff.salary" type="number" placeholder="25000" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700">Joining Date</label>
                      <input v-model="staffStore.selectedStaff.joining_date" type="date" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    
                    <div class="col-span-full flex items-center justify-between border border-slate-100 rounded-lg p-3 bg-slate-50/20 mt-2">
                      <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900">Active Employment Status</span>
                        <span class="text-xs text-slate-500">Deactivating an employee immediately revokes system route login permissions and removes them from active operational dispatch pools.</span>
                      </div>
                      <div class="flex items-center">
                        <input id="is_active" v-model="staffStore.selectedStaff.is_active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600 transition-all cursor-pointer">
                        <label for="is_active" class="ml-3 text-sm font-semibold leading-6 text-slate-900 cursor-pointer">Active Employee</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer actions -->
            <div class="bg-slate-50/75 border-t border-slate-100 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3">
              <button @click="handleSave" :disabled="staffStore.saving" type="button" class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                <div v-if="staffStore.saving" class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin mr-2 mt-0.5"></div>
                {{ staffStore.saving ? 'Saving Profile...' : 'Save Profile' }}
              </button>
              <button @click="staffStore.closeModal()" type="button" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-all duration-200">Cancel</button>
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
import { useToastStore } from '../../stores/toast';
import { useWorkforceStore } from '../../stores/workforce';

const staffStore = useStaffStore();
const authStore = useAuthStore();
const toastStore = useToastStore();
const workforceStore = useWorkforceStore();

// Restrict system Spatie roles to exactly these 4 clean options on frontend
const SYSTEM_ROLES = ['Super Admin', 'Admin', 'Staff', 'Viewer'];

onMounted(async () => {
  staffStore.fetchStaff();
  workforceStore.fetchLookups();
});

const handleSave = async () => {
  if (!staffStore.selectedStaff.name) {
    toastStore.error('Name field is required');
    return;
  }
  if (!staffStore.selectedStaff.email) {
    toastStore.error('Email field is required');
    return;
  }
  if (!staffStore.selectedStaff.id && !staffStore.selectedStaff.password) {
    toastStore.error('Password is required for new employee registrations');
    return;
  }
  if (!staffStore.selectedStaff.role) {
    toastStore.error('System Access Role is required');
    return;
  }

  try {
    await staffStore.saveStaff();
    toastStore.success('Employee profile successfully serialized and saved.');
  } catch (e) {
    // Handled by global Axios interceptors
  }
};

const getShiftName = (shiftId) => {
  if (!shiftId) return 'No Shift Assigned';
  const shiftObj = workforceStore.shifts.find(s => s.id === Number(shiftId));
  return shiftObj ? `${shiftObj.name} (${shiftObj.start_time.substring(0,5)}-${shiftObj.end_time.substring(0,5)})` : 'Shift ID: ' + shiftId;
};

const formatStatus = (status) => {
  if (!status) return '';
  return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getAvailabilityBadgeClass = (status) => {
  switch (status) {
    case 'available':
      return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20';
    case 'busy':
    case 'assigned':
      return 'bg-amber-50 text-amber-700 ring-amber-600/20';
    case 'on_leave':
      return 'bg-rose-50 text-rose-700 ring-rose-600/20';
    case 'offline':
    default:
      return 'bg-slate-50 text-slate-700 ring-slate-600/20';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'active':
      return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20';
    case 'inactive':
    case 'resigned':
    case 'terminated':
      return 'bg-rose-50 text-rose-700 ring-rose-600/20';
    case 'suspended':
    case 'on_leave':
      return 'bg-amber-50 text-amber-700 ring-amber-600/20';
    default:
      return 'bg-slate-50 text-slate-700 ring-slate-600/20';
  }
};
</script>

<style scoped>
.text-xxs {
  font-size: 0.7rem;
}
.text-xxxs {
  font-size: 0.62rem;
}
</style>
