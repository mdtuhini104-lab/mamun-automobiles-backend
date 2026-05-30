<template>
  <div class="max-w-7xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    <JobDetailsLayout :jobCard="workOrdersList[0]?.job_card || null" :activeStage="5">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-850 pb-5">
        <div class="flex items-center space-x-4">
          <div>
            <h1 class="text-2xl font-black tracking-tight text-white uppercase">Work Order Command Center</h1>
            <p class="text-xs text-slate-400 mt-1">Real-time coordinator dashboard. Delegate technician tasks, oversee repair statuses, and alert on stalled or delayed work.</p>
          </div>
        </div>
      </div>

    <!-- Alert / Live Diagnostics Deck -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Stalled/Delayed Alerts -->
      <div class="bg-red-950/20 border border-red-900/40 p-4 rounded-2xl space-y-3">
        <h3 class="text-[10px] font-black uppercase tracking-wider text-rose-400 flex items-center gap-2">
          <span class="w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
          Operational Warnings (Delayed Tasks)
        </h3>
        <div v-if="delayedTasks.length === 0" class="text-xs text-slate-500 italic">No tasks currently exceeding estimations.</div>
        <div v-else class="space-y-2 max-h-24 overflow-y-auto pr-1">
          <div v-for="t in delayedTasks" :key="t.id" class="text-[11px] bg-red-950/40 p-2 rounded border border-red-900/20 flex justify-between">
            <span>{{ t.task_name }} (JC #{{ t.jcId }})</span>
            <span class="font-bold text-rose-400 font-mono">+{{ t.delayedBy }}m</span>
          </div>
        </div>
      </div>

      <!-- Overloaded Bays Alert -->
      <div class="bg-amber-950/20 border border-amber-900/40 p-4 rounded-2xl space-y-3">
        <h3 class="text-[10px] font-black uppercase tracking-wider text-amber-400 flex items-center gap-2">
          <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
          Congestion Warnings (Overloaded Bays)
        </h3>
        <div v-if="overloadedBays.length === 0" class="text-xs text-slate-500 italic">All workshop bays running within nominal limits.</div>
        <div v-else class="space-y-2 max-h-24 overflow-y-auto pr-1">
          <div v-for="b in overloadedBays" :key="b.id" class="text-[11px] bg-amber-955/40 p-2 rounded border border-amber-900/20 flex justify-between">
            <span>{{ b.name }} (Capacity Limit)</span>
            <span class="font-bold text-amber-400 font-mono">{{ b.current_load }}/{{ b.max_vehicle_capacity }} vh</span>
          </div>
        </div>
      </div>

      <!-- Technician workload summary -->
      <div class="bg-indigo-950/20 border border-indigo-900/40 p-4 rounded-2xl space-y-3">
        <h3 class="text-[10px] font-black uppercase tracking-wider text-indigo-400 flex items-center gap-2">
          <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
          Technician Allocation Load
        </h3>
        <div class="flex justify-between items-center text-xs text-slate-400">
          <span>Active Staff Checked-In:</span>
          <span class="font-bold text-white font-mono">{{ technicians.length }}</span>
        </div>
        <div class="text-[10px] text-slate-500 max-h-20 overflow-y-auto">
          <div v-for="t in technicians.slice(0, 3)" :key="t.id" class="flex justify-between py-0.5 border-b border-slate-800">
            <span>{{ t.name }}</span>
            <span class="font-bold text-indigo-300 font-mono">{{ t.active_jobs_count || 0 }} Active jobs</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Active Work Orders Table / Deck -->
    <div class="grid grid-cols-1 gap-6">
      <div class="bg-slate-950/20 border border-slate-850 rounded-3xl p-5 shadow-xl">
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Active Workshop Repairs</h3>
        
        <div v-if="loading" class="flex justify-center py-12">
          <div class="w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div v-else-if="workOrdersList.length === 0" class="text-center py-20 text-slate-500 text-xs italic">
          No active work orders. Approve quotations on the approvals tab to dispatch repair orders.
        </div>

        <div v-else class="space-y-6">
          <div 
            v-for="wo in workOrdersList" 
            :key="wo.id"
            class="border border-slate-800/80 rounded-2xl p-5 bg-slate-900/60 space-y-4 hover:border-slate-700 transition"
          >
            <!-- Top bar -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-slate-850 pb-3">
              <div>
                <div class="flex items-center gap-3">
                  <h4 class="text-sm font-black text-white font-mono">{{ wo.work_order_number }}</h4>
                  <span class="text-[10px] font-bold text-slate-500 font-mono">Job ID: #{{ wo.job_card_id }}</span>
                  <span class="px-2 py-0.5 rounded text-[8px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono">
                    {{ wo.department_allocations?.join(', ') || 'Diagnostics' }}
                  </span>
                </div>
                <p class="text-[11px] text-slate-400 font-bold mt-1">Vehicle: {{ wo.job_card?.vehicle?.make }} {{ wo.job_card?.vehicle?.model }} ({{ wo.job_card?.vehicle?.license_plate || wo.job_card?.vehicle?.registration_no }})</p>
              </div>

              <!-- Status controllers -->
              <div class="flex items-center gap-2">
                <select 
                  v-model="wo.status" 
                  @change="updateWorkOrderStatus(wo)"
                  class="text-[10px] font-bold uppercase tracking-wider bg-slate-850 border border-slate-700 rounded-lg p-1.5 text-slate-200"
                >
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="paused">Paused / Shortage</option>
                  <option value="completed">Completed (Triggers QC)</option>
                  <option value="cancelled">Cancelled</option>
                </select>
                <router-link
                  :to="{ name: 'workshop.parts-consumption', params: { id: wo.id } }"
                  class="px-3 py-1.5 bg-slate-800 hover:bg-slate-750 border border-slate-700 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider"
                >
                  Log Mid-Repair Parts
                </router-link>
              </div>
            </div>

            <!-- Workforce & Decomposed Tasks Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              
              <!-- Assigned Mechanics list -->
              <div class="bg-slate-950/30 p-4 border border-slate-850 rounded-xl space-y-3">
                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider text-slate-450">
                  <span>Assigned Workforce</span>
                  <button @click="openDelegationDrawer(wo)" class="text-indigo-400 hover:text-indigo-300">Assign Staff</button>
                </div>
                <div class="space-y-2 text-xs">
                  <div v-if="wo.job_card?.assignments?.length === 0" class="text-slate-500 italic p-1 text-[11px]">No technicians delegated yet.</div>
                  <div 
                    v-else
                    v-for="asm in wo.job_card?.assignments" 
                    :key="asm.id"
                    class="flex justify-between bg-slate-900 border border-slate-850 p-2 rounded text-[11px] items-center text-slate-350"
                  >
                    <div>
                      <span class="font-bold text-white block">{{ asm.employee?.first_name }} {{ asm.employee?.last_name }}</span>
                      <span class="text-[9px] text-slate-500 uppercase tracking-widest">{{ asm.assignment_type }}</span>
                    </div>
                    <button 
                      v-if="asm.status === 'active'"
                      @click="completeTechnicianAssignment(asm.id)" 
                      class="text-emerald-450 hover:text-emerald-400 text-[9px] uppercase font-black"
                    >
                      Complete
                    </button>
                    <span v-else class="text-slate-500 text-[9px] uppercase">Completed</span>
                  </div>
                </div>
              </div>

              <!-- Tasks tracking -->
              <div class="md:col-span-2 bg-slate-950/30 p-4 border border-slate-850 rounded-xl space-y-3">
                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider text-slate-450">
                  <span>Task checklist items</span>
                  <button @click="promptCreateTask(wo)" class="text-indigo-400 hover:text-indigo-300">+ Add Task</button>
                </div>
                <div class="space-y-2 text-xs max-h-40 overflow-y-auto pr-1">
                  <div v-if="wo.job_card?.tasks?.length === 0" class="text-slate-500 italic p-1 text-[11px]">No tasks decomposed. Assign lines.</div>
                  <div 
                    v-else
                    v-for="task in wo.job_card?.tasks" 
                    :key="task.id"
                    class="bg-slate-900 border border-slate-850 p-2.5 rounded-lg flex flex-col sm:flex-row justify-between sm:items-center gap-3 text-slate-350"
                  >
                    <div>
                      <span class="font-bold text-white block">{{ task.name }}</span>
                      <span class="text-[9px] text-slate-550 font-mono">Est: {{ task.estimated_minutes }} mins | Actual: {{ task.actual_minutes }} mins</span>
                    </div>

                    <div class="flex items-center gap-2">
                      <!-- Assign button for task -->
                      <button 
                        v-if="task.status === 'pending'"
                        @click="promptAssignTask(task)"
                        class="px-2 py-1 bg-slate-800 hover:bg-slate-750 text-white border border-slate-750 rounded text-[9px] uppercase font-bold"
                      >
                        Assign Task
                      </button>
                      <span :class="getTaskStatusClass(task.status)" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest">
                        {{ task.status }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Assign Workforce Delegation Drawer / Modal -->
    <div v-if="showDelegationDrawer" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4 text-slate-100">
        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-400">Assign Technician / helpers</h3>
        
        <form @submit.prevent="submitDelegation" class="space-y-4">
          <div>
            <label class="block text-xs text-slate-400 mb-1">Select Lead Technician *</label>
            <select v-model="delegationForm.lead_technician_id" required class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white">
              <option value="">Select Technician...</option>
              <option v-for="t in activeTechnicians" :key="t.id" :value="t.id">
                {{ t.name }} (Active Jobs: {{ t.active_jobs_count }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs text-slate-400 mb-1">Select Assistant Technicians</label>
            <div class="border border-slate-800 rounded-xl p-3 bg-slate-950/40 text-[10px] space-y-2 max-h-32 overflow-y-auto">
              <label v-for="t in activeTechnicians" :key="t.id" class="flex items-center gap-2 cursor-pointer text-slate-300">
                <input 
                  type="checkbox" 
                  :value="t.id" 
                  v-model="delegationForm.assistant_technician_ids" 
                  class="rounded text-indigo-600 bg-slate-900 border-slate-750"
                />
                <span>{{ t.name }}</span>
              </label>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-2 border-t border-slate-850">
            <button type="button" @click="showDelegationDrawer = false" class="px-4 py-2 border border-slate-700 rounded-xl text-xs font-bold text-slate-400 hover:bg-slate-800">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider">Assign</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Custom Task Modal -->
    <div v-if="showTaskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4 text-slate-100">
        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-400">Add Custom Repair Task</h3>
        
        <form @submit.prevent="submitCreateTask" class="space-y-4">
          <div>
            <label class="block text-xs text-slate-400 mb-1">Task Name *</label>
            <input 
              v-model="taskForm.name" 
              type="text" 
              required 
              placeholder="e.g. Clean brake sensors"
              class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white"
            />
          </div>
          <div>
            <label class="block text-xs text-slate-400 mb-1">Estimated minutes</label>
            <input 
              v-model.number="taskForm.estimated_minutes" 
              type="number" 
              class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white font-mono"
            />
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-slate-850">
            <button type="button" @click="showTaskModal = false" class="px-4 py-2 border border-slate-700 rounded-xl text-xs font-bold text-slate-405">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider">Create</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Assign Specific Task to Employee Modal -->
    <div v-if="showAssignTaskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4 text-slate-100">
        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-400">Assign Technician to Task</h3>
        <p class="text-xs text-slate-450">Select staff member for task: "{{ selectedTask?.name }}"</p>
        
        <form @submit.prevent="submitAssignTask" class="space-y-4">
          <div>
            <label class="block text-xs text-slate-400 mb-1">Select staff *</label>
            <select v-model="taskAssignForm.employee_id" required class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white">
              <option value="">Select Staff...</option>
              <option v-for="t in activeTechnicians" :key="t.id" :value="t.id">
                {{ t.name }}
              </option>
            </select>
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-slate-850">
            <button type="button" @click="showAssignTaskModal = false" class="px-4 py-2 border border-slate-700 rounded-xl text-xs font-bold text-slate-450">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider">Assign</button>
          </div>
        </form>
      </div>
    </div>

    </JobDetailsLayout>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import JobDetailsLayout from '../../components/workshop/JobDetailsLayout.vue';

const toast = useToastStore();
const loading = ref(true);

const workOrdersList = ref([]);
const technicians = ref([]);
const activeTechnicians = ref([]);

// Alert analytics states
const delayedTasks = ref([]);
const overloadedBays = ref([]);

// Delegation Form
const showDelegationDrawer = ref(false);
const selectedWo = ref(null);
const delegationForm = reactive({
  lead_technician_id: '',
  assistant_technician_ids: [],
});

// Custom Task Form
const showTaskModal = ref(false);
const taskForm = reactive({
  name: '',
  estimated_minutes: 30,
});

// Assign Task Modal
const showAssignTaskModal = ref(false);
const selectedTask = ref(null);
const taskAssignForm = reactive({
  employee_id: '',
});

const fetchData = async () => {
  loading.value = true;
  try {
    const [woRes, techRes, bayRes] = await Promise.all([
      api.get('/work-orders', { params: { per_page: 150 } }),
      api.get('/users', { params: { role: 'Technician' } }),
      api.get('/workshop-bays')
    ]);

    workOrdersList.value = woRes.data?.data || woRes.data || [];
    technicians.value = techRes.data?.data || techRes.data || [];

    // Filter overloaded bays
    const bays = bayRes.data?.data || bayRes.data || [];
    overloadedBays.value = bays.filter(b => b.current_load >= b.max_vehicle_capacity);

    // Fetch employee workload list explicitly to get active workload
    const emplRes = await api.get('/workforce/employees'); // Fetch list of employees
    const emplList = emplRes.data?.data || [];
    activeTechnicians.value = emplList.filter(e => e.department?.name?.toLowerCase().includes('technic') || e.department?.name?.toLowerCase().includes('worksh') || e.designation?.name?.toLowerCase().includes('mechan'));

    // Filter delayed active tasks
    delayedTasks.value = [];
    workOrdersList.value.forEach(wo => {
      if (wo.job_card && Array.isArray(wo.job_card.tasks)) {
        wo.job_card.tasks.forEach(t => {
          if (t.status === 'in_progress' && t.actual_minutes > t.estimated_minutes) {
            delayedTasks.value.push({
              id: t.id,
              task_name: t.name,
              jcId: wo.job_card_id,
              delayedBy: t.actual_minutes - t.estimated_minutes
            });
          }
        });
      }
    });

  } catch (err) {
    console.error('Failed to sync Command center data', err);
    toast.error('Command center synchronization failed.');
  } finally {
    loading.value = false;
  }
};

const updateWorkOrderStatus = async (wo) => {
  try {
    await api.put(`/work-orders/${wo.id}/status`, { status: wo.status });
    toast.success(`Work Order status updated to ${wo.status}.`);
    fetchData();
  } catch (err) {
    toast.error('Failed to update Work Order status.');
  }
};

const openDelegationDrawer = (wo) => {
  selectedWo.value = wo;
  delegationForm.lead_technician_id = '';
  delegationForm.assistant_technician_ids = [];
  showDelegationDrawer.value = true;
};

const submitDelegation = async () => {
  try {
    await api.post(`/job-cards/${selectedWo.value.job_card_id}/assign`, {
      lead_technician_id: delegationForm.lead_technician_id,
      assistant_technician_ids: delegationForm.assistant_technician_ids
    });
    toast.success('Technician workforce delegated successfully.');
    showDelegationDrawer.value = false;
    fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Delegation failed.');
  }
};

const completeTechnicianAssignment = async (assignmentId) => {
  try {
    await api.post(`/job-cards/assignments/${assignmentId}/complete`, { labor_hours: 2.5 });
    toast.success('Technician assignment marked completed.');
    fetchData();
  } catch (err) {
    toast.error('Failed to complete technician assignment.');
  }
};

// Decomposed Custom Task creation
const promptCreateTask = (wo) => {
  selectedWo.value = wo;
  taskForm.name = '';
  taskForm.estimated_minutes = 30;
  showTaskModal.value = true;
};

const submitCreateTask = async () => {
  try {
    await api.post(`/job-cards/${selectedWo.value.job_card_id}/tasks`, { ...taskForm });
    toast.success('Custom task registered on work order.');
    showTaskModal.value = false;
    fetchData();
  } catch (err) {
    toast.error('Failed to register custom task.');
  }
};

// Specific task assignment
const promptAssignTask = (task) => {
  selectedTask.value = task;
  taskAssignForm.employee_id = '';
  showAssignTaskModal.value = true;
};

const submitAssignTask = async () => {
  try {
    await api.post(`/job-cards/${selectedTask.value.job_card_id}/tasks/${selectedTask.value.id}/assign`, {
      employee_id: taskAssignForm.employee_id
    });
    toast.success('Staff allocated to task successfully.');
    showAssignTaskModal.value = false;
    fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Task assignment failed.');
  }
};

const getTaskStatusClass = (status) => {
  const map = {
    'pending': 'bg-slate-800 text-slate-400',
    'in_progress': 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
    'completed': 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
  };
  return map[status] || 'bg-slate-800 text-slate-400';
};

onMounted(() => {
  fetchData();
});
</script>
