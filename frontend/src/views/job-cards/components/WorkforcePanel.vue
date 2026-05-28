<template>
  <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
      <h3 class="text-base font-bold text-slate-900">Workforce Delegation</h3>
      <button
        v-if="canEdit"
        @click="openAssignModal"
        class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors"
      >
        Delegate Staff
      </button>
    </div>
    
    <div class="p-6 space-y-5">
      <!-- Lead Technician -->
      <div class="space-y-2">
        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Lead Technician</span>
        <div v-if="leadTechnician" class="flex items-center space-x-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
          <div class="h-9 w-9 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
            {{ leadTechnician.employee?.first_name?.charAt(0) || 'T' }}
          </div>
          <div>
            <h4 class="text-sm font-bold text-slate-800">
              {{ leadTechnician.employee?.first_name }} {{ leadTechnician.employee?.last_name }}
            </h4>
            <span class="text-xs text-slate-500 font-mono">{{ leadTechnician.employee?.employee_code }}</span>
          </div>
        </div>
        <div v-else class="text-sm text-slate-400 italic p-3 bg-slate-50 rounded-xl border border-dashed border-slate-300 text-center">
          No lead technician assigned
        </div>
      </div>

      <!-- Assistant Technicians -->
      <div class="space-y-2">
        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Assistants</span>
        <div v-if="assistants.length > 0" class="space-y-2">
          <div v-for="ast in assistants" :key="ast.id" class="flex items-center space-x-3 p-2 bg-slate-50 rounded-xl border border-slate-100">
            <div class="h-8 w-8 rounded-full bg-blue-500 text-white font-bold flex items-center justify-center text-xs">
              {{ ast.employee?.first_name?.charAt(0) || 'A' }}
            </div>
            <div>
              <h4 class="text-xs font-semibold text-slate-800">
                {{ ast.employee?.first_name }} {{ ast.employee?.last_name }}
              </h4>
              <span class="text-[10px] text-slate-500 font-mono">{{ ast.employee?.employee_code }}</span>
            </div>
          </div>
        </div>
        <div v-else class="text-xs text-slate-400 italic py-2 text-center">
          No assistants assigned
        </div>
      </div>

      <!-- Helpers -->
      <div class="space-y-2">
        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Helpers</span>
        <div v-if="helpers.length > 0" class="space-y-2">
          <div v-for="help in helpers" :key="help.id" class="flex items-center space-x-3 p-2 bg-slate-50 rounded-xl border border-slate-100">
            <div class="h-8 w-8 rounded-full bg-amber-500 text-white font-bold flex items-center justify-center text-xs">
              {{ help.employee?.first_name?.charAt(0) || 'H' }}
            </div>
            <div>
              <h4 class="text-xs font-semibold text-slate-800">
                {{ help.employee?.first_name }} {{ help.employee?.last_name }}
              </h4>
              <span class="text-[10px] text-slate-500 font-mono">{{ help.employee?.employee_code }}</span>
            </div>
          </div>
        </div>
        <div v-else class="text-xs text-slate-400 italic py-2 text-center">
          No helpers assigned
        </div>
      </div>
    </div>

    <!-- Assignment Modal Overlay -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
          <h3 class="text-base font-bold text-slate-950">Configure Job Card Workforce</h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Scrollable content -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          <!-- Selection Mode -->
          <div class="flex items-center justify-center bg-slate-100 p-1 rounded-xl">
            <button
              v-for="role in ['lead', 'assistant', 'helper']"
              :key="role"
              @click="activeRoleSelection = role"
              :class="activeRoleSelection === role ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
              class="flex-1 py-2 text-xs font-bold rounded-lg uppercase tracking-wider transition-all"
            >
              {{ role }}s
            </button>
          </div>

          <!-- Current Selections review -->
          <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 space-y-3">
            <h4 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Delegation Draft</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <span class="block text-[10px] font-semibold text-indigo-400 uppercase">Lead</span>
                <span v-if="draft.lead" class="text-xs font-bold text-slate-800 flex justify-between items-center">
                  {{ draft.lead.name }}
                  <button @click="draft.lead = null" class="text-red-500 hover:text-red-700 ml-1">×</button>
                </span>
                <span v-else class="text-xs text-slate-400 italic">None selected</span>
              </div>
              <div>
                <span class="block text-[10px] font-semibold text-indigo-400 uppercase">Assistants</span>
                <div v-if="draft.assistants.length > 0" class="flex flex-wrap gap-1 mt-0.5">
                  <span v-for="ast in draft.assistants" :key="ast.id" class="inline-flex items-center text-[10px] font-bold text-slate-800 bg-slate-100 border px-1.5 py-0.5 rounded">
                    {{ ast.first_name }}
                    <button @click="removeDraftAssistant(ast.id)" class="text-red-500 hover:text-red-700 ml-1">×</button>
                  </span>
                </div>
                <span v-else class="text-xs text-slate-400 italic">None selected</span>
              </div>
              <div>
                <span class="block text-[10px] font-semibold text-indigo-400 uppercase">Helpers</span>
                <div v-if="draft.helpers.length > 0" class="flex flex-wrap gap-1 mt-0.5">
                  <span v-for="hlp in draft.helpers" :key="hlp.id" class="inline-flex items-center text-[10px] font-bold text-slate-800 bg-slate-100 border px-1.5 py-0.5 rounded">
                    {{ hlp.first_name }}
                    <button @click="removeDraftHelper(hlp.id)" class="text-red-500 hover:text-red-700 ml-1">×</button>
                  </span>
                </div>
                <span v-else class="text-xs text-slate-400 italic">None selected</span>
              </div>
            </div>
          </div>

          <!-- Selector -->
          <TechnicianSelector @select="handleStaffSelect" />
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end space-x-3">
          <button
            @click="closeModal"
            class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            @click="saveWorkforce"
            :disabled="workforceStore.saving"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
          >
            <svg v-if="workforceStore.saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Save Workforce
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { useWorkforceStore } from '../../../stores/workforce';
import TechnicianSelector from './TechnicianSelector.vue';

const props = defineProps({
  jobCard: {
    type: Object,
    required: true
  },
  canEdit: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['updated']);
const workforceStore = useWorkforceStore();

const isModalOpen = ref(false);
const activeRoleSelection = ref('lead');

const draft = reactive({
  lead: null,
  assistants: [],
  helpers: []
});

const leadTechnician = computed(() => {
  if (!props.jobCard?.assignments) return null;
  return props.jobCard.assignments.find(a => a.assignment_type === 'lead_technician' && a.status === 'active');
});

const assistants = computed(() => {
  if (!props.jobCard?.assignments) return [];
  return props.jobCard.assignments.filter(a => a.assignment_type === 'assistant_technician' && a.status === 'active');
});

const helpers = computed(() => {
  if (!props.jobCard?.assignments) return [];
  return props.jobCard.assignments.filter(a => a.assignment_type === 'helper' && a.status === 'active');
});

const openAssignModal = () => {
  // Populate draft with current values
  draft.lead = leadTechnician.value ? {
    id: leadTechnician.value.employee_id,
    name: `${leadTechnician.value.employee?.first_name} ${leadTechnician.value.employee?.last_name}`
  } : null;

  draft.assistants = assistants.value.map(a => ({
    id: a.employee_id,
    first_name: a.employee?.first_name,
    last_name: a.employee?.last_name
  }));

  draft.helpers = helpers.value.map(h => ({
    id: h.employee_id,
    first_name: h.employee?.first_name,
    last_name: h.employee?.last_name
  }));

  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const removeDraftAssistant = (id) => {
  draft.assistants = draft.assistants.filter(a => a.id !== id);
};

const removeDraftHelper = (id) => {
  draft.helpers = draft.helpers.filter(h => h.id !== id);
};

const handleStaffSelect = (tech) => {
  if (activeRoleSelection.value === 'lead') {
    // Lead technician is singular
    draft.lead = { id: tech.id, name: tech.name };
  } else if (activeRoleSelection.value === 'assistant') {
    // Check duplication
    if (!draft.assistants.find(a => a.id === tech.id)) {
      draft.assistants.push({ id: tech.id, first_name: tech.first_name, last_name: tech.last_name });
    }
  } else if (activeRoleSelection.value === 'helper') {
    // Check duplication
    if (!draft.helpers.find(h => h.id === tech.id)) {
      draft.helpers.push({ id: tech.id, first_name: tech.first_name, last_name: tech.last_name });
    }
  }
};

const saveWorkforce = async () => {
  const payload = {
    lead_technician_id: draft.lead ? draft.lead.id : null,
    assistant_technician_ids: draft.assistants.map(a => a.id),
    helper_ids: draft.helpers.map(h => h.id)
  };

  try {
    await workforceStore.assignWorkforce(props.jobCard.id, payload);
    closeModal();
    emit('updated');
  } catch (error) {
    // Toast handles validation errors
  }
};
</script>
