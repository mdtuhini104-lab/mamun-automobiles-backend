<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-4">
    <div 
      class="bg-gray-900 border border-gray-800 rounded-t-3xl sm:rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate-slide-up flex flex-col max-h-[85vh] sm:max-h-[90vh]"
    >
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-gray-800 bg-gray-950 px-5 py-4">
        <div>
          <h3 class="text-base font-bold text-white">Log Parts Consumption</h3>
          <p class="text-[10px] text-gray-500 mt-0.5">Logging parts consumed on work card #{{ task.work_order_id }}</p>
        </div>
        <button @click="close" class="p-2 text-gray-400 hover:text-gray-200 focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form Body -->
      <div class="p-5 overflow-y-auto flex-1 space-y-4">
        <!-- Search parts autocomplete -->
        <div>
          <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2">Search Catalog Part</label>
          <div class="relative">
            <input 
              v-model="searchQuery" 
              @input="searchParts"
              type="text" 
              placeholder="Type part name or SKU..." 
              class="w-full bg-gray-950 border border-gray-800 focus:border-rose-500/50 focus:ring-1 focus:ring-rose-500/50 rounded-2xl px-4 py-3 text-sm text-gray-200 placeholder-gray-600 focus:outline-none transition duration-200"
            />
            <div 
              v-if="partsResults.length > 0" 
              class="absolute left-0 right-0 mt-2 bg-gray-950 border border-gray-800 rounded-2xl shadow-2xl z-50 divide-y divide-gray-900 max-h-48 overflow-y-auto custom-scrollbar"
            >
              <div 
                v-for="part in partsResults" 
                :key="part.id"
                @click="selectPart(part)"
                class="px-4 py-3 hover:bg-gray-900 cursor-pointer flex justify-between items-center text-xs"
              >
                <div>
                  <p class="font-bold text-white">{{ part.name }}</p>
                  <p class="text-[10px] text-gray-500 mt-0.5">SKU: {{ part.sku }}</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-gray-800 text-gray-400">
                  {{ part.stock_quantity }} avail
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Selected Part details -->
        <div v-if="selectedPart" class="bg-gray-950 border border-gray-850 p-4 rounded-2xl flex items-center justify-between">
          <div>
            <h4 class="text-xs font-bold text-rose-400">Selected Part</h4>
            <p class="text-sm font-extrabold text-white mt-1">{{ selectedPart.name }}</p>
          </div>
          <button @click="selectedPart = null" class="text-xs text-rose-500 font-bold hover:text-rose-400 transition">
            Remove
          </button>
        </div>

        <!-- Quantities inputs -->
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1.5">Qty Consumed</label>
            <input 
              v-model.number="form.quantity" 
              type="number" 
              min="1" 
              class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2.5 text-sm text-center text-white focus:outline-none focus:border-rose-500/50"
            />
          </div>
          <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1.5">Qty Wasted</label>
            <input 
              v-model.number="form.wasted" 
              type="number" 
              min="0" 
              class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2.5 text-sm text-center text-white focus:outline-none focus:border-rose-500/50"
            />
          </div>
          <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1.5">Qty Returned</label>
            <input 
              v-model.number="form.returned" 
              type="number" 
              min="0" 
              class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2.5 text-sm text-center text-white focus:outline-none focus:border-rose-500/50"
            />
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1.5">Usage Notes</label>
          <textarea 
            v-model="form.notes" 
            rows="2" 
            placeholder="e.g. Engine filter replacement..." 
            class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-gray-200 placeholder-gray-600 focus:outline-none focus:border-rose-500/50"
          ></textarea>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="border-t border-gray-800 bg-gray-950/80 px-5 py-4 flex gap-3">
        <button 
          @click="close" 
          class="flex-1 py-3 bg-gray-800 hover:bg-gray-750 text-gray-400 rounded-2xl font-extrabold text-xs transition focus:outline-none"
        >
          Cancel
        </button>
        <button 
          @click="submit" 
          :disabled="submitting || !selectedPart"
          class="flex-1 py-3 bg-rose-600 hover:bg-rose-500 disabled:bg-gray-800 disabled:text-gray-600 disabled:border-none text-white rounded-2xl font-extrabold text-xs transition shadow-lg focus:outline-none"
        >
          {{ submitting ? 'Logging...' : 'Confirm Log' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
  isOpen: Boolean,
  task: Object
});

const emit = defineEmits(['close', 'logged']);

const toast = useToastStore();
const searchQuery = ref('');
const partsResults = ref([]);
const selectedPart = ref(null);
const submitting = ref(false);

const form = reactive({
  quantity: 1,
  wasted: 0,
  returned: 0,
  notes: ''
});

const close = () => {
  searchQuery.value = '';
  partsResults.value = [];
  selectedPart.value = null;
  form.quantity = 1;
  form.wasted = 0;
  form.returned = 0;
  form.notes = '';
  emit('close');
};

const searchParts = async () => {
  if (searchQuery.value.length < 2) {
    partsResults.value = [];
    return;
  }

  try {
    const response = await api.get('/parts', {
      params: { q: searchQuery.value }
    });
    // Assume response format response.data.data
    partsResults.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to search parts', error);
  }
};

const selectPart = (part) => {
  selectedPart.value = part;
  partsResults.value = [];
  searchQuery.value = '';
};

const submit = async () => {
  if (!selectedPart.value) return;

  submitting.value = true;
  try {
    const payload = {
      part_id: selectedPart.value.id,
      quantity: form.quantity,
      actual_consumed_quantity: form.quantity,
      wasted_quantity: form.wasted,
      returned_quantity: form.returned,
      notes: form.notes
    };

    // Call additional consumption endpoint
    await api.post(`/work-orders/${props.task.work_order_id}/additional-consumption`, payload);
    toast.success('Parts consumption logged successfully');
    emit('logged');
    close();
  } catch (error) {
    console.error('Failed to log consumption', error);
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.animate-slide-up {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #374151;
  border-radius: 9999px;
}
</style>
