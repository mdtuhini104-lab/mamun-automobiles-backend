<template>
  <div class="max-w-4xl mx-auto space-y-6 p-6 bg-slate-50 border border-slate-200 rounded-3xl shadow-sm text-slate-800 min-h-screen">
    
    <!-- Fallback Stage Selector -->
    <WorkspaceJobSelector 
      v-if="!route.params.id" 
      stage="qc" 
      title="Select Work Order for QC Verification" 
      @selected="handleJobSelected"
    />

    <div v-else-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-200 rounded w-1/4"></div>
      <div class="h-96 bg-slate-200 rounded"></div>
    </div>

    <JobDetailsLayout v-else-if="workOrder" :jobCard="workOrder?.job_card || null" :activeStage="7">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-200 pb-5">
        <div class="flex items-center space-x-4">
          <div v-if="workOrder">
            <h1 class="text-2xl font-black tracking-tight text-slate-800 uppercase">Quality Control Inspection</h1>
            <p class="text-xs text-slate-500 mt-1">WO #{{ workOrder.id }}</p>
          </div>
        </div>
      </div>

    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-200 rounded w-1/4"></div>
      <div class="h-96 bg-slate-200 rounded"></div>
    </div>

    <div v-else-if="workOrder">
      <form @submit.prevent="submitQcReport" class="space-y-6">
        
        <!-- QC Checklist -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 space-y-4 shadow-sm">
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-600">Mechanical Checklist Verification</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label 
              v-for="(checked, key) in form.checklist" 
              :key="key"
              class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-100 cursor-pointer transition"
            >
              <input 
                type="checkbox" 
                v-model="form.checklist[key]"
                class="w-4 h-4 text-indigo-600 bg-white border-slate-300 rounded"
              />
              <span class="text-xs font-bold text-slate-700 capitalize">{{ key.replace('_', ' ') }} verified</span>
            </label>
          </div>
        </div>

        <!-- Road Test details -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 space-y-5 shadow-sm">
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-600">Road Test Performance Validation</h3>
          
          <div class="space-y-2">
            <label class="block text-xs text-slate-500">Was a dynamic road test performed? *</label>
            <div class="flex gap-4">
              <button 
                type="button"
                @click="form.road_test_performed = true"
                class="flex-1 py-3 rounded-xl font-bold text-xs border transition"
                :class="form.road_test_performed ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-slate-50 border-slate-200 text-slate-500'"
              >
                Yes, Road Test Executed
              </button>
              <button 
                type="button"
                @click="form.road_test_performed = false"
                class="flex-1 py-3 rounded-xl font-bold text-xs border transition"
                :class="!form.road_test_performed ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-slate-50 border-slate-200 text-slate-500'"
              >
                No, Road Test Skipped
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs text-slate-500 mb-1">Road Test & Visual Inspection Notes *</label>
            <textarea
              v-model="form.road_test_notes"
              required
              rows="3"
              placeholder="Record steering responsiveness, alignment, brake response, and engine telemetry observations during the test..."
              class="w-full text-xs bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-slate-800 focus:bg-white animate-in"
            ></textarea>
          </div>
        </div>

        <!-- Verdict selection -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 space-y-4 shadow-sm">
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-600">Final Supervisor Verdict</h3>
          
          <div class="flex gap-4">
            <button 
              type="button"
              @click="form.status = 'passed'"
              class="flex-1 py-4 rounded-2xl font-bold text-xs border flex items-center justify-center gap-2 transition"
              :class="form.status === 'passed' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 ring-2 ring-emerald-500/20' : 'bg-slate-50 border-slate-200 text-slate-500'"
            >
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
              Inspection Passed (Mark Ready for Delivery)
            </button>
            <button 
              type="button"
              @click="form.status = 'failed'"
              class="flex-1 py-4 rounded-2xl font-bold text-xs border flex items-center justify-center gap-2 transition"
              :class="form.status === 'failed' ? 'bg-rose-50 border-rose-500 text-rose-700 ring-2 ring-rose-500/20' : 'bg-slate-50 border-slate-200 text-slate-500'"
            >
              <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
              Inspection Failed (Revert to Workshop)
            </button>
          </div>
        </div>

        <!-- Submit actions -->
        <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
          <router-link
            :to="{ name: 'workshop.hub' }"
            class="px-4 py-2 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 transition"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            :disabled="saving"
            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-black uppercase tracking-wider transition disabled:opacity-50"
          >
            {{ saving ? 'Logging report...' : 'Submit QC Verification Report' }}
          </button>
        </div>

      </form>
    </div>
    </JobDetailsLayout>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import JobDetailsLayout from '../../components/workshop/JobDetailsLayout.vue';
import WorkspaceJobSelector from '../../components/workshop/WorkspaceJobSelector.vue';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const loading = ref(true);
const saving = ref(false);
const workOrder = ref(null);

const form = reactive({
  work_order_id: null,
  status: 'passed',
  checklist: {
    brakes_responsive: true,
    steering_alignment: true,
    lights_signals: true,
    suspension_bushings: true,
    engine_idle_sound: true,
    fluid_leak_check: true,
    cabin_cleanliness: true
  },
  road_test_performed: true,
  road_test_notes: ''
});

const handleJobSelected = (id) => {
  router.push({ name: 'workshop.qc', params: { id } });
};

const fetchWorkOrderDetails = async () => {
  if (!route.params.id) {
    workOrder.value = null;
    loading.value = false;
    return;
  }
  loading.value = true;
  try {
    const res = await api.get(`/work-orders/${route.params.id}`);
    workOrder.value = res.data?.data || res.data;
    form.work_order_id = workOrder.value.id;
  } catch (err) {
    toast.error('Failed to load Work Order details');
    router.push({ name: 'workshop.qc' });
  } finally {
    loading.value = false;
  }
};

const submitQcReport = async () => {
  saving.value = true;
  try {
    await api.post('/quality-control', { ...form });
    toast.success('QC Inspection Report submitted successfully.');
    if (form.status === 'passed') {
      router.push({ name: 'workshop.settlement', params: { id: workOrder.value.job_card_id } });
    } else {
      router.push({ name: 'workshop.qc' });
    }
  } catch (err) {
    console.error('QC submission error', err);
    toast.error(err.response?.data?.message || 'QC verification report logging failed.');
  } finally {
    saving.value = false;
  }
};

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchWorkOrderDetails();
  } else {
    workOrder.value = null;
    loading.value = false;
  }
});

onMounted(() => {
  if (route.params.id) {
    fetchWorkOrderDetails();
  } else {
    loading.value = false;
  }
});
</script>
