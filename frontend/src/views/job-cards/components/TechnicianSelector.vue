<template>
  <div class="space-y-4">
    <!-- Filter Bars -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
      <!-- Department Filter -->
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Department</label>
        <select v-model="filters.department_id" class="w-full text-sm rounded-lg border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2 border">
          <option value="">All Departments</option>
          <option v-for="dept in workforceStore.departments" :key="dept.id" :value="dept.id">
            {{ dept.name }}
          </option>
        </select>
      </div>

      <!-- Designation Filter -->
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Designation</label>
        <select v-model="filters.designation_id" class="w-full text-sm rounded-lg border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2 border">
          <option value="">All Designations</option>
          <option v-for="desg in workforceStore.designations" :key="desg.id" :value="desg.id">
            {{ desg.name }}
          </option>
        </select>
      </div>

      <!-- Skill Filter -->
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Required Skill</label>
        <select v-model="filters.skill_id" class="w-full text-sm rounded-lg border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2 border">
          <option value="">Any Skill</option>
          <option v-for="skill in workforceStore.skills" :key="skill.id" :value="skill.id">
            {{ skill.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Active Loading Spinner -->
    <div v-if="workforceStore.loading" class="flex justify-center items-center py-10">
      <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <!-- Technicians List -->
    <div v-else class="space-y-2 max-h-[350px] overflow-y-auto pr-1">
      <div v-if="workforceStore.employees.length === 0" class="text-center py-8 text-slate-400 text-sm">
        No technicians match the selected filters.
      </div>
      <div
        v-else
        v-for="tech in workforceStore.employees"
        :key="tech.id"
        class="flex flex-col sm:flex-row justify-between sm:items-center p-4 bg-white border border-slate-200 rounded-xl hover:border-indigo-500 hover:shadow-sm transition-all space-y-3 sm:space-y-0"
      >
        <div class="space-y-1.5">
          <div class="flex items-center space-x-2">
            <h4 class="text-sm font-bold text-slate-900">{{ tech.name }}</h4>
            <span class="text-xs text-slate-400 font-mono">({{ tech.employee_code }})</span>
            <span :class="getAvailabilityBadge(tech.availability_status)" class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider">
              {{ tech.availability_status }}
            </span>
          </div>
          <p class="text-xs text-slate-500">
            {{ tech.designation?.name || 'Staff' }} • {{ tech.department?.name || 'Department' }}
          </p>
          <div class="flex flex-wrap gap-1 mt-1">
            <span
              v-for="skill in tech.skills"
              :key="skill.id"
              class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10 capitalize"
            >
              {{ skill.name }} ({{ skill.proficiency_level }})
            </span>
          </div>
        </div>

        <!-- Workload & Selection -->
        <div class="flex items-center justify-between sm:justify-end sm:space-x-4">
          <div class="text-left sm:text-right">
            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Jobs</span>
            <span :class="tech.active_jobs_count > 3 ? 'text-amber-600 font-bold' : 'text-slate-700 font-medium'" class="text-sm">
              {{ tech.active_jobs_count }} active
            </span>
          </div>
          <button
            @click="$emit('select', tech)"
            :disabled="tech.availability_status === 'on_leave' || tech.availability_status === 'offline'"
            class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 transition-colors shadow-sm"
          >
            Choose
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, onMounted } from 'vue';
import { useWorkforceStore } from '../../../stores/workforce';

const emit = defineEmits(['select']);
const workforceStore = useWorkforceStore();

const filters = reactive({
  department_id: '',
  designation_id: '',
  skill_id: '',
  availability: ''
});

// Eager load lookups
onMounted(async () => {
  await workforceStore.fetchLookups();
  await fetchFilteredEmployees();
});

// Debounced / Simple filter watch
let timeoutId = null;
watch(
  () => ({ ...filters }),
  () => {
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      fetchFilteredEmployees();
    }, 300);
  }
);

const fetchFilteredEmployees = async () => {
  const cleanFilters = {};
  if (filters.department_id) cleanFilters.department_id = filters.department_id;
  if (filters.designation_id) cleanFilters.designation_id = filters.designation_id;
  if (filters.skill_id) cleanFilters.skill_id = filters.skill_id;
  await workforceStore.fetchEmployees(cleanFilters);
};

const getAvailabilityBadge = (status) => {
  switch (status) {
    case 'available':
      return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20';
    case 'busy':
    case 'assigned':
      return 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20';
    case 'on_leave':
      return 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20';
    default:
      return 'bg-slate-50 text-slate-600 ring-1 ring-slate-600/10';
  }
};
</script>
