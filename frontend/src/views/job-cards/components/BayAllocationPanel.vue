<template>
  <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
      <h3 class="text-base font-bold text-slate-900">Workshop Bay Allocation</h3>
      <button
        v-if="canEdit"
        @click="openBayModal"
        class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors"
      >
        {{ workshopBay ? 'Reassign Bay' : 'Allocate Bay' }}
      </button>
    </div>

    <div class="p-6 space-y-4">
      <div v-if="workshopBay" class="space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2.5">
            <div class="h-8 w-8 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
              B
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-800">{{ workshopBay.name }}</h4>
              <span class="text-xs text-slate-500 font-mono">{{ workshopBay.code }}</span>
            </div>
          </div>
          <span :class="getUtilizationClass(workshopBay)" class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase">
            {{ workshopBay.current_load }} / {{ workshopBay.max_vehicle_capacity }} vehicles
          </span>
        </div>

        <!-- Progress Gauge -->
        <div class="w-full bg-slate-100 rounded-full h-2">
          <div
            :class="getProgressBarClass(workshopBay)"
            class="h-2 rounded-full transition-all duration-300"
            :style="{ width: getUtilizationPercentage(workshopBay) + '%' }"
          ></div>
        </div>
      </div>

      <div v-else class="text-sm text-slate-400 italic p-6 bg-slate-50 rounded-xl border border-dashed border-slate-300 text-center flex flex-col items-center justify-center space-y-2">
        <span>No workshop bay allocated yet.</span>
        <button
          v-if="canEdit"
          @click="openBayModal"
          class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-50 transition-all shadow-sm"
        >
          Select Bay
        </button>
      </div>
    </div>

    <!-- Bay Selection Modal Overlay -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full max-h-[80vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
          <h3 class="text-base font-bold text-slate-950">Allocate Workshop Bay</h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Bays list -->
        <div class="p-6 overflow-y-auto space-y-3">
          <div v-if="baysStore.loading" class="flex justify-center items-center py-8">
            <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          <div v-else-if="baysStore.bays.length === 0" class="text-center py-6 text-slate-400 text-sm">
            No active workshop bays found in the system.
          </div>
          <div
            v-else
            v-for="bay in baysStore.bays"
            :key="bay.id"
            class="flex items-center justify-between p-4 border border-slate-200 rounded-xl hover:border-indigo-500 hover:shadow-sm transition-all"
          >
            <div class="space-y-1">
              <h4 class="text-sm font-bold text-slate-900">{{ bay.name }}</h4>
              <span class="text-xs text-slate-400 font-mono">{{ bay.code }}</span>
              <div class="flex items-center space-x-2 mt-1">
                <div class="w-24 bg-slate-100 rounded-full h-1.5">
                  <div
                    :class="getProgressBarClass(bay)"
                    class="h-1.5 rounded-full"
                    :style="{ width: getUtilizationPercentage(bay) + '%' }"
                  ></div>
                </div>
                <span class="text-[10px] font-semibold text-slate-500">
                  {{ bay.current_load }} / {{ bay.max_vehicle_capacity }} vehicles
                </span>
              </div>
            </div>

            <!-- Allocate Action -->
            <button
              @click="allocate(bay.id, bay)"
              :disabled="bay.current_load >= bay.max_vehicle_capacity || baysStore.allocating"
              :class="bay.current_load >= bay.max_vehicle_capacity ? 'bg-slate-100 text-slate-400 border border-slate-200' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm'"
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all"
            >
              {{ bay.current_load >= bay.max_vehicle_capacity ? 'Full' : 'Select' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useWorkshopBaysStore } from '../../../stores/workshopBays';
import { useToastStore } from '../../../stores/toast';

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
const baysStore = useWorkshopBaysStore();
const isModalOpen = ref(false);

const workshopBay = computed(() => props.jobCard?.workshop_bay);

const openBayModal = async () => {
  isModalOpen.value = true;
  await baysStore.fetchBays();
};

const closeModal = () => {
  isModalOpen.value = false;
};

const allocate = async (bayId, bay) => {
  if (bay.current_load >= bay.max_vehicle_capacity) {
    const toast = useToastStore();
    toast.warning('This workshop bay is at maximum capacity!');
    return;
  }
  try {
    await baysStore.allocateBay(props.jobCard.id, bayId);
    closeModal();
    emit('updated');
  } catch (error) {
    // Toast error is intercepted
  }
};

const getUtilizationPercentage = (bay) => {
  if (!bay || !bay.max_vehicle_capacity) return 0;
  return Math.min(100, Math.round((bay.current_load / bay.max_vehicle_capacity) * 100));
};

const getUtilizationClass = (bay) => {
  if (!bay) return 'bg-slate-50 text-slate-500';
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20';
  if (pct >= 75) return 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20';
  return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20';
};

const getProgressBarClass = (bay) => {
  if (!bay) return 'bg-slate-400';
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'bg-rose-600';
  if (pct >= 75) return 'bg-amber-500';
  return 'bg-indigo-600';
};
</script>
