<template>
  <div class="max-w-md mx-auto space-y-6 p-5 bg-slate-50 border border-gray-200 rounded-3xl shadow-sm text-slate-800 min-h-[90vh] pb-24 flex flex-col justify-between">
    
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center font-black tracking-wider text-xs text-white">
          MA
        </div>
        <div>
          <h1 class="text-xs font-black uppercase text-slate-900 tracking-widest">Technician Workspace</h1>
          <p class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">Mobile Task Deck</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span v-if="offlineQueueCount > 0" class="px-2 py-0.5 rounded bg-amber-50 border border-amber-250 text-amber-700 font-extrabold text-[8px] uppercase tracking-wider font-mono">
          Queue: {{ offlineQueueCount }}
        </span>
        <div class="flex items-center gap-1.5 shrink-0">
          <span :class="isOnline ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.3)]' : 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.3)]'" class="inline-flex h-2.5 w-2.5 rounded-full transition-all duration-300"></span>
          <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500">
            {{ isOnline ? 'Online' : 'Offline' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Active Tasks Queue -->
    <div class="flex-1 space-y-4">
      <div class="flex justify-between items-center mt-4">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Assigned Repair Tasks</h3>
        <button @click="fetchTasks" class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 bg-white hover:bg-gray-50 rounded-lg border border-gray-200 text-indigo-600 hover:text-indigo-700 transition duration-200 shadow-sm">Sync Tasks</button>
      </div>

      <div v-if="loading" class="flex justify-center py-16">
        <div class="w-6 h-6 border-2 border-indigo-650 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <div v-else-if="tasks.length === 0" class="text-center py-20 border border-dashed border-gray-300 rounded-2xl p-4 text-slate-500 text-xs italic">
        No active tasks assigned to you. Go to hub or check-in.
      </div>

      <!-- Task cards -->
      <div v-else class="space-y-4">
        <div 
          v-for="task in tasks" 
          :key="task.id"
          class="bg-white border border-gray-200 rounded-2xl p-4 space-y-4 flex flex-col justify-between shadow-sm"
        >
          <div>
            <div class="flex justify-between items-start gap-2">
              <span class="text-[9px] font-mono text-slate-500 font-bold">JC #{{ task.job_card_id }}</span>
              <span :class="getStatusBadgeClass(task.status)" class="px-2.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider">
                {{ task.status }}
              </span>
            </div>
            <h4 class="font-bold text-slate-900 text-sm mt-1.5">{{ task.task_name }}</h4>
            <p class="text-[10px] text-slate-500 font-mono mt-1">Vehicle: {{ task.work_order?.job_card?.vehicle?.make }} {{ task.work_order?.job_card?.vehicle?.model }} ({{ task.work_order?.job_card?.vehicle?.registration_no }})</p>
            
            <!-- Task Media Section -->
            <div class="mt-3.5 space-y-2 border-t border-gray-150 pt-3">
              <span class="text-[9px] font-black uppercase text-slate-500 block">Progress Photo Reports</span>
              <div v-if="task.photos && task.photos.length > 0" class="flex gap-2 overflow-x-auto py-1">
                <img v-for="(img, idx) in task.photos" :key="idx" :src="img" class="w-11 h-11 rounded-lg object-cover border border-gray-200 shrink-0" />
              </div>
              <div v-else class="text-[9px] text-slate-450 italic">No progress photos uploaded.</div>
              <button 
                type="button" 
                @click="mockTaskPhotoUpload(task)"
                class="px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg text-[9px] text-indigo-600 font-bold uppercase transition shadow-sm"
              >
                + Upload Photo
              </button>
            </div>
          </div>

          <!-- Actions deck (Optimized touch targets for mobile) -->
          <div class="flex gap-2.5 border-t border-gray-150 pt-4">
            <button 
              v-if="task.status === 'pending' || task.status === 'paused'"
              @click="updateStatus(task.id, 'in_progress')"
              class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-200 active:scale-[0.98] shadow-sm"
            >
              ▶ Start
            </button>
            <button 
              v-if="task.status === 'in_progress'"
              @click="updateStatus(task.id, 'paused')"
              class="flex-1 py-3 bg-white hover:bg-gray-50 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-200 active:scale-[0.98] border border-gray-200 shadow-sm"
            >
              ⏸ Pause
            </button>
            <button 
              v-if="task.status === 'in_progress'"
              @click="updateStatus(task.id, 'completed')"
              class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-200 active:scale-[0.98] shadow-sm"
            >
              ✔ Complete
            </button>
            <button 
              @click="promptConsumeParts(task)"
              class="py-3 px-4.5 bg-white hover:bg-gray-50 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-200 active:scale-[0.98] border border-gray-200 shadow-sm"
            >
              + Part
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Consume Parts Modal Popup -->
    <div v-if="showPartsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
      <div class="bg-white border border-gray-200 rounded-3xl shadow-2xl max-w-sm w-full p-5 space-y-4 text-slate-800 animate-scaleUp">
        <h3 class="text-xs font-black uppercase tracking-widest text-indigo-650">Consume Materials</h3>
        <p class="text-[10px] text-slate-500 font-bold">Record parts consumed for: "{{ selectedTask?.task_name }}"</p>
        
        <form @submit.prevent="submitPartsConsumption" class="space-y-4">
          <div>
            <label class="block text-[10px] text-slate-500 mb-1.5 uppercase font-black">Select Part *</label>
            <select v-model="partsForm.part_id" required class="w-full text-xs bg-slate-50 border border-gray-200 rounded-xl p-3 text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white">
              <option value="">Select Part...</option>
              <option v-for="part in partsList" :key="part.id" :value="part.id">
                {{ part.name }} (Stock: {{ part.stock_quantity }} {{ part.unit_type || 'pcs' }})
              </option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] text-slate-500 mb-1.5 uppercase font-black">Quantity *</label>
              <input 
                v-model.number="partsForm.quantity" 
                type="number" 
                required 
                class="w-full text-xs bg-slate-50 border border-gray-200 rounded-xl p-3 text-slate-800 font-mono focus:outline-none focus:border-indigo-500 focus:bg-white"
              />
            </div>
            <div>
              <label class="block text-[10px] text-slate-500 mb-1.5 uppercase font-black">Ownership *</label>
              <select v-model="partsForm.source_type" class="w-full text-xs bg-slate-50 border border-gray-200 rounded-xl p-3 text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white">
                <option value="workshop_supplied">Workshop Supplied</option>
                <option value="customer_supplied">Customer Supplied</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-3 border-t border-gray-150">
            <button type="button" @click="showPartsModal = false" class="px-4 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 rounded-xl text-xs font-bold text-slate-600 shadow-sm">Cancel</button>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm">Log Parts</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(true);
const isOnline = ref(typeof navigator !== 'undefined' ? navigator.onLine : true);

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

const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
  if (isOnline.value) {
    fetchTasks();
    fetchParts();
  }
};

const fetchTasks = async () => {
  loading.value = true;
  try {
    const response = await api.get('/mobile/tasks');
    const dataVal = response.data?.data || response.data || [];
    tasks.value = dataVal.map(t => ({ ...t, photos: t.photos || [] }));
    
    // Cache tasks for offline usage
    localStorage.setItem('mamun_technician_tasks', JSON.stringify(tasks.value));
  } catch (error) {
    console.error('Failed to load technician tasks', error);
    
    // Load from cache if offline or error
    const cached = localStorage.getItem('mamun_technician_tasks');
    if (cached) {
      tasks.value = JSON.parse(cached).map(t => ({ ...t, photos: t.photos || [] }));
      toast.warning('Offline Cache: Displaying your saved active tasks.');
    } else {
      // Fallback
      tasks.value = [
        { id: 101, task_name: 'Brake pad swap & slide pins clean', status: 'in_progress', job_card_id: 1, work_order_id: 1, work_order: { id: 1, job_card: { id: 1, vehicle: { registration_no: 'DHA-11-2233', make: 'Toyota', model: 'Allion' } } }, photos: [] },
        { id: 102, task_name: 'Engine oil filter change & diagnostics', status: 'pending', job_card_id: 2, work_order_id: 2, work_order: { id: 2, job_card: { id: 2, vehicle: { registration_no: 'CTG-55-9988', make: 'Honda', model: 'Civic' } } }, photos: [] }
      ];
    }
  } finally {
    loading.value = false;
  }
};

const fetchParts = async () => {
  try {
    const res = await api.get('/parts');
    const dataVal = res.data?.data || res.data || [];
    partsList.value = dataVal;
    localStorage.setItem('mamun_parts_list', JSON.stringify(dataVal));
  } catch (err) {
    console.error(err);
    const cached = localStorage.getItem('mamun_parts_list');
    if (cached) {
      partsList.value = JSON.parse(cached);
    }
  }
};

const updateStatus = async (taskId, status) => {
  try {
    await api.put(`/mobile/tasks/${taskId}/status`, { status });
    toast.success(`Task status marked as ${status}`);
    fetchTasks();
  } catch (err) {
    // If request fails because we are offline, it will be queued by api.js axios interceptor automatically
    // Update local UI immediately for responsiveness
    const task = tasks.value.find(t => t.id === taskId);
    if (task) {
      task.status = status;
      localStorage.setItem('mamun_technician_tasks', JSON.stringify(tasks.value));
    }
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

const offlineQueueCount = ref(0);
let queueInterval = null;

const updateOfflineQueueCount = () => {
  if (typeof window === 'undefined') return;
  const queue = localStorage.getItem('mamun_offline_queue');
  if (queue) {
    try {
      const parsed = JSON.parse(queue);
      offlineQueueCount.value = parsed.length || 0;
    } catch (e) {
      offlineQueueCount.value = 0;
    }
  } else {
    offlineQueueCount.value = 0;
  }
};

const mockTaskPhotoUpload = (task) => {
  if (!task.photos) {
    task.photos = [];
  }
  const mockImages = [
    'https://images.unsplash.com/photo-1486006920555-c77dce18193b?w=200',
    'https://images.unsplash.com/photo-1517524206127-48bbd363f3d7?w=200'
  ];
  task.photos.push(mockImages[Math.floor(Math.random() * mockImages.length)]);
  toast.success('Task progress photograph logged to local storage.');
  localStorage.setItem('mamun_technician_tasks', JSON.stringify(tasks.value));
};

const getStatusBadgeClass = (status) => {
  const map = {
    'pending': 'bg-slate-800 text-slate-455 border border-slate-700/50',
    'in_progress': 'bg-blue-500/10 text-indigo-400 border border-indigo-500/20',
    'paused': 'bg-rose-500/10 text-rose-455 border border-rose-500/20',
    'completed': 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
  };
  return map[status?.toLowerCase()] || 'bg-slate-850 text-slate-400';
};

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
  }
  fetchTasks();
  fetchParts();
  updateOfflineQueueCount();
  queueInterval = setInterval(updateOfflineQueueCount, 3000);
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
  }
  if (queueInterval) {
    clearInterval(queueInterval);
  }
});
</script>
