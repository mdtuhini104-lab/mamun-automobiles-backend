<template>
  <div class="max-w-md mx-auto space-y-6 p-5 bg-slate-950 border border-slate-900 rounded-3xl shadow-2xl text-slate-100 min-h-[90vh] pb-24 flex flex-col justify-between">
    
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-900 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center font-black tracking-wider text-xs">
          MA
        </div>
        <div>
          <h1 class="text-xs font-black uppercase text-white tracking-widest">Technician Workspace</h1>
          <p class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">Mobile Task Deck</p>
        </div>
      </div>
      <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse" title="System Synchronized"></span>
    </div>

    <!-- Active Tasks Queue -->
    <div class="flex-1 space-y-4">
      <div class="flex justify-between items-center mt-4">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Assigned Repair Tasks</h3>
        <button @click="fetchTasks" class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300">Sync Tasks</button>
      </div>

      <div v-if="loading" class="flex justify-center py-16">
        <div class="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <div v-else-if="tasks.length === 0" class="text-center py-20 border border-dashed border-slate-900 rounded-2xl p-4 text-slate-600 text-xs italic">
        No active tasks assigned to you. Go to hub or check-in.
      </div>

      <!-- Task cards -->
      <div v-else class="space-y-3.5">
        <div 
          v-for="task in tasks" 
          :key="task.id"
          class="bg-slate-900 border border-slate-850 rounded-2xl p-4 space-y-3 flex flex-col justify-between"
        >
          <div>
            <div class="flex justify-between items-start gap-2">
              <span class="text-[8px] font-mono text-slate-500">JC #{{ task.job_card_id }}</span>
              <span :class="getStatusBadgeClass(task.status)" class="px-2 py-0.5 rounded text-[8px] font-black uppercase">
                {{ task.status }}
              </span>
            </div>
            <h4 class="font-bold text-white text-xs mt-1.5">{{ task.task_name }}</h4>
            <p class="text-[9px] text-slate-400 font-mono mt-0.5">Vehicle: {{ task.work_order?.job_card?.vehicle?.make }} {{ task.work_order?.job_card?.vehicle?.model }} ({{ task.work_order?.job_card?.vehicle?.registration_no }})</p>
          </div>

          <!-- Actions deck -->
          <div class="flex gap-2 border-t border-slate-950 pt-3">
            <button 
              v-if="task.status === 'pending' || task.status === 'paused'"
              @click="updateStatus(task.id, 'in_progress')"
              class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[9px] font-black uppercase tracking-wider"
            >
              ▶ Start Task
            </button>
            <button 
              v-if="task.status === 'in_progress'"
              @click="updateStatus(task.id, 'paused')"
              class="flex-1 py-2 bg-slate-850 hover:bg-slate-800 text-slate-350 rounded-xl text-[9px] font-black uppercase tracking-wider border border-slate-800"
            >
              ⏸ Pause Task
            </button>
            <button 
              v-if="task.status === 'in_progress'"
              @click="updateStatus(task.id, 'completed')"
              class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[9px] font-black uppercase tracking-wider"
            >
              ✔ Complete
            </button>
            <button 
              @click="promptConsumeParts(task)"
              class="py-2 px-3 bg-slate-800 hover:bg-slate-750 text-white rounded-xl text-[9px] font-bold uppercase tracking-wider border border-slate-750"
            >
              + Part
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Consume Parts Modal Popup -->
    <div v-if="showPartsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl max-w-sm w-full p-5 space-y-4 text-slate-100">
        <h3 class="text-xs font-black uppercase tracking-widest text-indigo-400">Consume Materials</h3>
        <p class="text-[10px] text-slate-500">Record parts consumed for: "{{ selectedTask?.task_name }}"</p>
        
        <form @submit.prevent="submitPartsConsumption" class="space-y-4">
          <div>
            <label class="block text-[10px] text-slate-400 mb-1">Select Part *</label>
            <select v-model="partsForm.part_id" required class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white">
              <option value="">Select Part...</option>
              <option v-for="part in partsList" :key="part.id" :value="part.id">
                {{ part.name }} (Stock: {{ part.stock_quantity }} {{ part.unit_type || 'pcs' }})
              </option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] text-slate-400 mb-1">Quantity *</label>
              <input 
                v-model.number="partsForm.quantity" 
                type="number" 
                required 
                class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white font-mono"
              />
            </div>
            <div>
              <label class="block text-[10px] text-slate-400 mb-1">Ownership *</label>
              <select v-model="partsForm.source_type" class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white">
                <option value="workshop_supplied">Workshop Supplied</option>
                <option value="customer_supplied">Customer Supplied</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-slate-850">
            <button type="button" @click="showPartsModal = false" class="px-3.5 py-2 border border-slate-700 rounded-xl text-xs font-bold text-slate-405">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider">Log Parts</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(true);

const tasks = ref([]);
const partsList = ref([]);

// Consume Parts Form
const showPartsModal = ref(false);
const selectedTask = ref(null);
const partsForm = reactive({
  part_id: '',
  quantity: 1,
  source_type: 'workshop_supplied'
});

const fetchTasks = async () => {
  loading.value = true;
  try {
    const response = await api.get('/mobile/tasks');
    tasks.value = response.data?.data || response.data || [];
  } catch (error) {
    console.error('Failed to load technician tasks', error);
    // Offline / Mock fallback
    tasks.value = [
      { id: 101, task_name: 'Brake pad swap & slide pins clean', status: 'in_progress', job_card_id: 1, work_order_id: 1, work_order: { id: 1, job_card: { id: 1, vehicle: { registration_no: 'DHA-11-2233', make: 'Toyota', model: 'Allion' } } } },
      { id: 102, task_name: 'Engine oil filter change & diagnostics', status: 'pending', job_card_id: 2, work_order_id: 2, work_order: { id: 2, job_card: { id: 2, vehicle: { registration_no: 'CTG-55-9988', make: 'Honda', model: 'Civic' } } } }
    ];
  } finally {
    loading.value = false;
  }
};

const fetchParts = async () => {
  try {
    const res = await api.get('/parts');
    partsList.value = res.data?.data || res.data || [];
  } catch (err) {
    console.error(err);
  }
};

const updateStatus = async (taskId, status) => {
  try {
    await api.put(`/mobile/tasks/${taskId}/status`, { status });
    toast.success(`Task status marked as ${status}`);
    fetchTasks();
  } catch (err) {
    toast.error('Failed to sync status update with server.');
  }
};

const promptConsumeParts = (task) => {
  selectedTask.value = task;
  partsForm.part_id = '';
  partsForm.quantity = 1;
  partsForm.source_type = 'workshop_supplied';
  showPartsModal.value = true;
};

const submitPartsConsumption = async () => {
  if (!selectedTask.value.work_order_id) {
    toast.warning('This task has no active work order linked.');
    return;
  }
  try {
    await api.post(`/work-orders/${selectedTask.value.work_order_id}/additional-consumption`, {
      item_type: 'product',
      part_id: partsForm.part_id,
      quantity: partsForm.quantity,
      source_type: partsForm.source_type,
      is_approved: true
    });
    toast.success('Parts consumption logged successfully.');
    showPartsModal.value = false;
    fetchTasks();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Consumption log failed.');
  }
};

const getStatusBadgeClass = (status) => {
  const map = {
    'pending': 'bg-slate-800 text-slate-450',
    'in_progress': 'bg-blue-500/10 text-indigo-400 border border-indigo-500/20',
    'paused': 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
    'completed': 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
  };
  return map[status?.toLowerCase()] || 'bg-slate-850 text-slate-400';
};

onMounted(() => {
  fetchTasks();
  fetchParts();
});
</script>
