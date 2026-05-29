<template>
  <div class="space-y-6">
    <div v-if="!workOrder" class="bg-white shadow-sm rounded-xl border border-slate-200 p-8 text-center">
      <div class="max-w-md mx-auto space-y-4">
        <div class="inline-flex p-3 bg-indigo-50 rounded-full text-indigo-600">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 114 0v2m-4 0h4m-4 0h8m-1 2v.01M19 19h.01M6 12h.01M11 16h.01"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-900">Work Order Awaiting Generation</h3>
        <p class="text-sm text-slate-500">The operational execution layer will become active automatically once the customer approves the quotation.</p>
      </div>
    </div>

    <!-- Active Execution Panel -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Decomposed Task checklist & Execution -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
          <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-slate-900">Decomposed Repair Tasks</h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
              WO Status: {{ workOrder.status }}
            </span>
          </div>

          <!-- Tasks List -->
          <div class="p-6">
            <div v-if="!jobCard.tasks || jobCard.tasks.length === 0" class="text-center py-6">
              <p class="text-sm text-slate-500">No repair tasks decomposed yet.</p>
            </div>
            <div v-else class="space-y-4">
              <div v-for="task in jobCard.tasks" :key="task.id" class="border border-slate-200 rounded-lg p-4 bg-slate-50 hover:bg-slate-100 transition-colors">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                  <div>
                    <h4 class="font-bold text-slate-900 text-base">{{ task.name }}</h4>
                    <p class="text-xs text-slate-500 mt-0.5">{{ task.description }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                      <span class="inline-flex items-center text-xs text-slate-500 font-medium">
                        ⏱ Est: {{ task.estimated_minutes }} mins
                      </span>
                      <span :class="getTaskStatusClass(task.status)" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        {{ task.status }}
                      </span>
                    </div>
                  </div>

                  <!-- Task Operations -->
                  <div class="flex items-center space-x-2">
                    <button
                      v-if="task.status === 'pending'"
                      @click="startTaskExecution(task)"
                      class="px-3 py-1 border border-transparent text-xs font-semibold rounded bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm"
                    >
                      Start Task
                    </button>
                    <button
                      v-if="task.status === 'in_progress'"
                      @click="promptCompleteTask(task)"
                      class="px-3 py-1 border border-transparent text-xs font-semibold rounded bg-green-600 hover:bg-green-700 text-white shadow-sm"
                    >
                      Complete
                    </button>
                    <span v-if="task.status === 'completed'" class="text-xs font-bold text-green-600 flex items-center">
                      ✔ Task Completed
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Consumption Drawer Panel -->
        <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-6 space-y-4">
          <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Technician Additional material Consumption Drawer
          </h4>
          <p class="text-xs text-slate-500">Log any additional parts consumed or service hours required mid-repair. Workshop supplied items dynamically request admin approval.</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs text-slate-500 mb-1">Item Type</label>
              <select v-model="consumptionForm.item_type" class="block w-full rounded border-slate-300 shadow-sm sm:text-sm">
                <option value="product">Product (Parts)</option>
                <option value="service">Service (Labor)</option>
              </select>
            </div>
            <div v-if="consumptionForm.item_type === 'product'">
              <label class="block text-xs text-slate-500 mb-1">Select Part</label>
              <select v-model="consumptionForm.part_id" class="block w-full rounded border-slate-300 shadow-sm sm:text-sm">
                <option value="">Choose Part...</option>
                <option v-for="part in partsList" :key="part.id" :value="part.id">
                  {{ part.name }} (Stock: {{ part.stock_quantity }})
                </option>
              </select>
            </div>
            <div v-else>
              <label class="block text-xs text-slate-500 mb-1">Labor Service Name</label>
              <input v-model="consumptionForm.service_name" type="text" placeholder="e.g. AC Filter Clean" class="block w-full rounded border-slate-300 shadow-sm sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Quantity / Hours</label>
              <input v-model.number="consumptionForm.quantity" type="number" class="block w-full rounded border-slate-300 shadow-sm sm:text-sm" />
            </div>
          </div>
          <div class="flex justify-end pt-2">
            <button
              @click="submitAdditionalConsumption"
              :disabled="savingConsumption || (consumptionForm.item_type === 'product' && !consumptionForm.part_id) || (consumptionForm.item_type === 'service' && !consumptionForm.service_name)"
              class="inline-flex justify-center px-4 py-2 border border-transparent text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm"
            >
              {{ savingConsumption ? 'Logging Material...' : 'Log & Deduct Stock' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Workshop Bay & Workforce shift tracking -->
      <div class="lg:col-span-1 space-y-6">
        <!-- Allocations Summary -->
        <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Execution Allocations</h3>
          </div>
          <div class="p-6 space-y-4">
            <dl class="space-y-4">
              <div>
                <dt class="text-xs font-bold text-slate-500 uppercase">Allocated Workshop Bay</dt>
                <dd class="mt-1 text-sm text-slate-900 font-bold bg-slate-100 p-2 rounded">
                  {{ jobCard.workshop_bay?.name || 'Unassigned Bay' }}
                </dd>
              </div>

              <div>
                <dt class="text-xs font-bold text-slate-500 uppercase">Lead Technician</dt>
                <dd class="mt-1 text-sm text-slate-900 font-bold">
                  {{ jobCard.mechanic?.name || 'Unassigned' }}
                </dd>
              </div>

              <div>
                <dt class="text-xs font-bold text-slate-500 uppercase">Assigned Departments</dt>
                <dd class="mt-1 flex flex-wrap gap-2">
                  <span v-for="dept in workOrder.department_allocations" :key="dept" class="px-2 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-800">
                    {{ dept }}
                  </span>
                  <span v-if="!workOrder.department_allocations || workOrder.department_allocations.length === 0" class="text-slate-400 text-xs">None</span>
                </dd>
              </div>
            </dl>

            <!-- Complete Work Order Button -->
            <div class="pt-4 border-t border-slate-100">
              <button
                v-if="workOrder.status !== 'completed'"
                @click="completeWorkOrderConfirmed"
                :disabled="completingWO"
                class="w-full inline-flex justify-center py-2.5 px-4 border border-transparent shadow-sm text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 transition-all"
              >
                {{ completingWO ? 'Finalizing Repair Execution...' : 'Finalize & Generate Invoice' }}
              </button>
              <div v-else class="bg-green-50 text-green-800 text-center font-bold p-3 rounded-lg border border-green-200">
                ✔ Work Order Executed & Closed
              </div>
            </div>
          </div>
        </div>

        <!-- Immutable Labor Progress Logs -->
        <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Immutable Labor Hours Logs</h3>
          </div>
          <div class="p-6">
            <div v-if="!jobCard.assignments || jobCard.assignments.length === 0" class="text-center py-4">
              <p class="text-xs text-slate-500">No workforce shifts or labor hours logged yet.</p>
            </div>
            <div v-else class="space-y-4">
              <div v-for="assign in jobCard.assignments" :key="assign.id" class="border-l-2 border-green-500 pl-3 py-1 space-y-1">
                <div class="text-xs font-bold text-slate-900">{{ assign.employee?.first_name }} {{ assign.employee?.last_name }}</div>
                <div class="text-[10px] text-slate-400 capitalize">{{ assign.assignment_type?.replace('_', ' ') }} | Status: {{ assign.status }}</div>
                <div class="text-xs text-slate-600 font-semibold" v-if="assign.labor_hours !== null">
                  Logged: {{ assign.labor_hours }} billable labor hrs (Immutable)
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Complete Task Modal -->
    <div v-if="showCompleteTaskModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full border border-slate-200 p-6 space-y-4 mx-4">
        <h3 class="text-lg font-bold text-slate-900">Complete Repair Task</h3>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Labor Hours Spent *</label>
            <input
              v-model.number="taskCompleteForm.labor_hours"
              type="number"
              required
              step="0.5"
              placeholder="e.g. 2.5"
              class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Completion notes</label>
            <textarea
              v-model="taskCompleteForm.notes"
              rows="3"
              placeholder="Record any details about parts installed, diagnostic codes cleared..."
              class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
          </div>
        </div>
        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
          <button
            type="button"
            @click="showCompleteTaskModal = false"
            class="px-4 py-2 border border-slate-300 text-sm font-semibold rounded-lg bg-white hover:bg-slate-50 text-slate-700"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="completeTaskConfirmed"
            :disabled="!taskCompleteForm.labor_hours"
            class="px-4 py-2 border border-transparent text-sm font-semibold rounded-lg bg-green-600 hover:bg-green-700 text-white"
          >
            Save & Log Immutable Labor
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';
import { useToastStore } from '../../../stores/toast';

const props = defineProps({
  jobCard: {
    type: Object,
    required: true,
  }
});

const emit = defineEmits(['updated']);

const toast = useToastStore();
const workOrder = ref(null);
const loading = ref(true);
const partsList = ref([]);

const savingConsumption = ref(false);
const consumptionForm = ref({
  item_type: 'product',
  part_id: '',
  service_name: '',
  quantity: 1,
  source_type: 'workshop_supplied',
});

const showCompleteTaskModal = ref(false);
const activeTask = ref(null);
const taskCompleteForm = ref({
  labor_hours: '',
  notes: '',
});

const completingWO = ref(false);

const fetchWorkOrder = async () => {
  try {
    const response = await api.get('/work-orders', { params: { job_card_id: props.jobCard.id } });
    if (response.data.data && response.data.data.length > 0) {
      const details = await api.get(`/work-orders/${response.data.data[0].id}`);
      workOrder.value = details.data.data;
    } else {
      workOrder.value = null;
    }
  } catch (err) {
    console.error('Failed to fetch work order', err);
  } finally {
    loading.value = false;
  }
};

const fetchPartsList = async () => {
  try {
    const response = await api.get('/parts');
    partsList.value = response.data.data;
  } catch (err) {
    console.error('Failed to load parts', err);
  }
};

onMounted(() => {
  fetchWorkOrder();
  fetchPartsList();
});

const startTaskExecution = async (task) => {
  try {
    // Standard workforce assignment task allocation
    await api.post(`/job-cards/${props.jobCard.id}/tasks/${task.id}/assign`, {
      employee_id: props.jobCard.assignments[0]?.employee_id || 1,
    });
    toast.success('Task active! shift timers running.');
    emit('updated');
    await fetchWorkOrder();
  } catch (err) {
    toast.error('Failed to start task');
  }
};

const promptCompleteTask = (task) => {
  activeTask.value = task;
  taskCompleteForm.value.labor_hours = '';
  taskCompleteForm.value.notes = '';
  showCompleteTaskModal.value = true;
};

const completeTaskConfirmed = async () => {
  try {
    // Log labor hours to the active technician assignment
    const activeAssign = props.jobCard.assignments.find(a => a.status === 'active');
    if (activeAssign) {
      await api.post(`/job-cards/assignments/${activeAssign.id}/complete`, {
        labor_hours: taskCompleteForm.value.labor_hours,
      });
    }

    // Complete task assignment
    const activeTaskAssign = activeTask.value.task_assignments?.find(ta => ta.status === 'active');
    if (activeTaskAssign) {
      await api.post(`/job-cards/task-assignments/${activeTaskAssign.id}/complete`);
    } else {
      // Direct completion API fallback if no assignments
      await api.post(`/job-cards/task-assignments/${activeTask.value.id}/complete`);
    }

    toast.success('Labor logged! Labor hours are locked as immutable.');
    showCompleteTaskModal.value = false;
    emit('updated');
    await fetchWorkOrder();
  } catch (err) {
    toast.error('Failed to complete task');
  }
};

const submitAdditionalConsumption = async () => {
  savingConsumption.value = true;
  try {
    await api.post(`/work-orders/${workOrder.value.id}/additional-consumption`, {
      item_type: consumptionForm.value.item_type,
      part_id: consumptionForm.value.part_id || undefined,
      service_name: consumptionForm.value.service_name || undefined,
      quantity: consumptionForm.value.quantity,
      source_type: consumptionForm.value.source_type,
      is_approved: true,
    });
    
    toast.success('Mid-repair parts consumed and deducted successfully!');
    emit('updated');
    await fetchWorkOrder();
    
    // Reset
    consumptionForm.value.part_id = '';
    consumptionForm.value.service_name = '';
    consumptionForm.value.quantity = 1;
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to consume material');
  } finally {
    savingConsumption.value = false;
  }
};

const completeWorkOrderConfirmed = async () => {
  if (!confirm('Are you sure all repair tasks are complete? This will close execution and generate the invoice.')) return;
  completingWO.value = true;
  try {
    await api.put(`/work-orders/${workOrder.value.id}/status`, { status: 'completed' });
    toast.success('Repairs completed! Invoice generated.');
    emit('updated');
    await fetchWorkOrder();
  } catch (err) {
    toast.error('Failed to close work order');
  } finally {
    completingWO.value = false;
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const getTaskStatusClass = (status) => {
  const map = {
    'pending': 'bg-slate-100 text-slate-800 border border-slate-200',
    'in_progress': 'bg-blue-100 text-blue-800 border border-blue-200',
    'completed': 'bg-green-100 text-green-800 border border-green-200',
    'cancelled': 'bg-red-100 text-red-800 border border-red-200'
  };
  return map[status] || 'bg-slate-100 text-slate-800';
};
</script>
