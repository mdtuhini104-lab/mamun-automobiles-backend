<template>
  <div class="max-w-6xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    
    <!-- Fallback Stage Selector -->
    <WorkspaceJobSelector 
      v-if="!route.params.id" 
      stage="inspection" 
      title="Select Vehicle for Visual & OBD Inspection" 
      @selected="handleJobSelected"
    />

    <div v-else-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-800 rounded w-1/4"></div>
      <div class="h-96 bg-slate-800 rounded"></div>
    </div>

    <JobDetailsLayout v-else-if="jobCard" :jobCard="jobCard" :activeStage="2">
      <!-- Form -->
      <form @submit.prevent="saveInspection" class="space-y-6">
        
        <!-- Checklist of findings -->
        <div class="bg-slate-950/40 p-5 rounded-2xl border border-slate-850 space-y-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400">Visual & OBD Checklist</h3>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <label 
              v-for="(checked, key) in checklist" 
              :key="key"
              class="flex items-center gap-3 p-3 bg-slate-900 border border-slate-800 rounded-xl hover:border-slate-700 cursor-pointer transition"
            >
              <input 
                type="checkbox" 
                v-model="checklist[key]"
                class="w-4 h-4 text-indigo-650 bg-slate-950 border-slate-750 rounded focus:ring-indigo-500"
              />
              <span class="text-[11px] font-bold text-slate-350 capitalize">{{ key.replace('_', ' ') }} Passed</span>
            </label>
          </div>
        </div>

        <!-- Intake Params -->
        <div class="bg-slate-950/40 p-5 rounded-2xl border border-slate-850 space-y-6">
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400">Diagnostic Parameters</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs text-slate-400 mb-1">Odometer Reading (KM) *</label>
              <input
                v-model.number="form.odometer_reading"
                type="number"
                required
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Fuel Level Gauge *</label>
              <select
                v-model="form.fuel_level"
                required
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              >
                <option value="Empty">Empty</option>
                <option value="1/4">1/4 Tank</option>
                <option value="1/2">1/2 Tank</option>
                <option value="3/4">3/4 Tank</option>
                <option value="Full">Full Tank</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Emergency Hazard Level *</label>
              <select
                v-model="form.emergency_level"
                required
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              >
                <option value="low">Low (Standard Maintenance)</option>
                <option value="medium">Medium (Repair Advised)</option>
                <option value="high">High (Major Faults Detected)</option>
                <option value="critical">Critical (Safety Hazard - Do Not Run)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Workflow priority *</label>
              <select
                v-model="form.priority_level"
                required
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              >
                <option value="normal">Normal Priority</option>
                <option value="high">High Priority</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-xs text-slate-400 mb-1">Diagnostic Findings & Code Scans *</label>
              <textarea
                v-model="form.diagnosis"
                required
                rows="4"
                placeholder="Log physical diagnostic notes, scan codes (e.g. P0300 misfire, P0171 lean limit), engine sounds, visual leaks..."
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              ></textarea>
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">General Workshop Notes</label>
              <textarea
                v-model="form.inspection_notes"
                rows="2"
                placeholder="Internal notes for service advisors, helper recommendations, cleanup state..."
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              ></textarea>
            </div>
            <div>
              <label class="block text-xs text-red-400 font-extrabold mb-1">Safety Warnings & Hazards</label>
              <textarea
                v-model="form.safety_warnings"
                rows="2"
                placeholder="BALD TIRES, BRAKE FAILURE HAZARD, FUEL LINE LEAK..."
                class="w-full text-xs bg-slate-900 border border-red-905/60 bg-red-950/10 rounded-lg p-2.5 text-red-200 focus:border-red-500"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Document Attachments Mockups -->
        <div class="bg-slate-950/40 p-5 rounded-2xl border border-slate-850 space-y-3">
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400">Media & Diagnostic Audio Notes</h3>
          <p class="text-[11px] text-slate-400">Optional: Upload scanner output logs, engine sound voice memos, or dashboard warning photos.</p>
          <div class="flex flex-wrap gap-3">
            <button type="button" @click="toast.success('Microphone active: Diagnostic sound memo logged.')" class="px-4 py-2 bg-slate-850 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition">
              🎤 Record Scanner Audio Memo
            </button>
            <button type="button" @click="toast.success('Scanner log file attached.')" class="px-4 py-2 bg-slate-850 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition">
              📎 Upload OBD Scanner Log (PDF)
            </button>
          </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end gap-3 border-t border-slate-850 pt-4">
          <router-link
            :to="{ name: 'workshop.inspection' }"
            class="px-4 py-2 border border-slate-700 rounded-lg text-xs font-bold text-slate-450 hover:bg-slate-850 transition"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            :disabled="saving"
            class="px-6 py-2 bg-indigo-650 hover:bg-indigo-700 text-white rounded-lg text-xs font-black uppercase tracking-wider transition disabled:opacity-50"
          >
            {{ saving ? 'Submitting...' : 'Save Inspection Findings' }}
          </button>
        </div>

      </form>
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
const jobCard = ref(null);

const checklist = reactive({
  obd_scan: true,
  engine_oil: true,
  coolant_level: true,
  brake_pads: true,
  electricals: true,
  tires_treads: true,
  wipers_washers: true
});

const form = reactive({
  odometer_reading: '',
  fuel_level: '1/2',
  emergency_level: 'medium',
  priority_level: 'normal',
  diagnosis: '',
  inspection_notes: '',
  safety_warnings: '',
});

const handleJobSelected = (id) => {
  router.push({ name: 'workshop.inspection', params: { id } });
};

const fetchJobDetails = async () => {
  loading.value = true;
  try {
    const response = await api.get(`/job-cards/${route.params.id}`);
    jobCard.value = response.data.data;
    
    // Autofill form
    form.odometer_reading = jobCard.value.odometer_reading || '';
    form.fuel_level = jobCard.value.fuel_level || '1/2';
    form.emergency_level = jobCard.value.emergency_level || 'medium';
    form.priority_level = jobCard.value.priority_level || 'normal';
    form.diagnosis = jobCard.value.diagnosis || '';
    form.inspection_notes = jobCard.value.inspection_notes || '';
    form.safety_warnings = jobCard.value.safety_warnings || '';
  } catch (err) {
    toast.error('Failed to load Job Card details');
    router.push({ name: 'workshop.inspection' });
  } finally {
    loading.value = false;
  }
};

const saveInspection = async () => {
  saving.value = true;
  try {
    // Serialize visual checklist state directly to inspection_notes to persist it safely
    const checklistStr = Object.entries(checklist)
      .map(([k, v]) => `${k.replace('_', ' ')}: ${v ? 'Passed' : 'Failed'}`)
      .join(', ');

    const combinedNotes = form.inspection_notes 
      ? `${form.inspection_notes}\n\n[Visual Checklist]\n${checklistStr}`
      : `[Visual Checklist]\n${checklistStr}`;

    // Save inspection findings and update job card diagnosis
    await api.put(`/job-cards/${jobCard.value.id}`, {
      ...jobCard.value,
      ...form,
      inspection_notes: combinedNotes,
      service_status: 'pending' // Moves the card to "Waiting Quotation"
    });
    
    toast.success('Vehicle inspection findings completed and logged.');
    router.push({ name: 'workshop.quotation', params: { id: jobCard.value.id } }); // Chains directly to next stage
  } catch (err) {
    console.error('Inspection submit failed', err);
    toast.error(err.response?.data?.message || 'Failed to submit inspection reports.');
  } finally {
    saving.value = false;
  }
};

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchJobDetails();
  } else {
    jobCard.value = null;
    loading.value = false;
  }
});

onMounted(() => {
  if (route.params.id) {
    fetchJobDetails();
  } else {
    loading.value = false;
  }
});
</script>
