<template>
  <div class="max-w-6xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    
    <!-- Fallback Stage Selector -->
    <WorkspaceJobSelector 
      v-if="!route.params.id" 
      stage="diagnosis" 
      title="Select Vehicle for OBD Diagnostics Center" 
      @selected="handleJobSelected"
    />

    <div v-else-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-800 rounded w-1/4"></div>
      <div class="h-96 bg-slate-800 rounded"></div>
    </div>

    <JobDetailsLayout v-else-if="jobCard" :jobCard="jobCard" :activeStage="2">
      <!-- High-Tech Diagnostic Console Wrapper -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Live Scan HUD (Left Column) -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-slate-950 border border-slate-850 rounded-2xl p-5 space-y-5 relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-36 h-36 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex items-center justify-between border-b border-slate-850 pb-3">
              <span class="text-xs font-black uppercase tracking-wider text-emerald-450 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                OBD-II CAN-Bus Live
              </span>
              <span class="text-[9px] font-mono text-slate-500">Baud: 500kbit/s</span>
            </div>

            <!-- Virtual Gauges -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-slate-900/80 border border-slate-800/80 p-3 rounded-xl text-center space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase">RPM Gauge</span>
                <div class="text-lg font-black font-mono text-white">820 <span class="text-[9px] text-slate-550 font-normal">rpm</span></div>
                <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                  <div class="bg-emerald-500 h-full w-[12%]"></div>
                </div>
              </div>
              <div class="bg-slate-900/80 border border-slate-800/80 p-3 rounded-xl text-center space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase">Coolant Temp</span>
                <div class="text-lg font-black font-mono text-white">92°C</div>
                <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                  <div class="bg-indigo-500 h-full w-[65%]"></div>
                </div>
              </div>
              <div class="bg-slate-900/80 border border-slate-800/80 p-3 rounded-xl text-center space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase">LTFT Bank 1</span>
                <div class="text-lg font-black font-mono text-amber-450">+4.2%</div>
                <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                  <div class="bg-amber-500 h-full w-[45%]"></div>
                </div>
              </div>
              <div class="bg-slate-900/80 border border-slate-800/80 p-3 rounded-xl text-center space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase">O2 Volts B1S1</span>
                <div class="text-lg font-black font-mono text-white">0.76V</div>
                <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                  <div class="bg-indigo-500 h-full w-[76%]"></div>
                </div>
              </div>
            </div>

            <!-- Predefined Scan Simulation Buttons -->
            <div class="space-y-2">
              <span class="text-[9px] font-black uppercase text-slate-500 block">Diagnostic Quick-Scans</span>
              <button 
                type="button" 
                @click="simulateDtcScan('P0300', 'Random/Multiple Cylinder Misfire Detected. Spark plug electrode deterioration, coil pack leakage on B1.')" 
                class="w-full py-2 bg-slate-900 hover:bg-slate-850 text-left px-3 text-[10px] text-slate-300 font-mono rounded-lg border border-slate-800/80 flex justify-between items-center transition"
              >
                <span>Scan DTC: P0300</span>
                <span class="text-rose-400 font-bold">Inject Code →</span>
              </button>
              <button 
                type="button" 
                @click="simulateDtcScan('P0171', 'System Too Lean Bank 1. Vacuum leak in intake manifold plenum gasket, MAF element contamination.')" 
                class="w-full py-2 bg-slate-900 hover:bg-slate-850 text-left px-3 text-[10px] text-slate-300 font-mono rounded-lg border border-slate-800/80 flex justify-between items-center transition"
              >
                <span>Scan DTC: P0171</span>
                <span class="text-amber-450 font-bold">Inject Code →</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Main Form (Middle & Right Columns) -->
        <div class="lg:col-span-2 space-y-6">
          <form @submit.prevent="submitDiagnostics" class="bg-slate-950/40 p-6 border border-slate-850 rounded-2xl space-y-6">
            <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400">Diagnosis Findings & Actionable Plan</h3>
            
            <div class="space-y-4">
              <!-- DTC Log Entry -->
              <div>
                <label class="block text-xs text-slate-400 mb-1">DTC Trouble Codes (OBD-II) *</label>
                <input 
                  v-model="obdForm.dtc_codes"
                  type="text"
                  required
                  placeholder="e.g. P0300, P0171, P0420"
                  class="w-full text-xs font-mono bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-rose-350 focus:border-indigo-500"
                />
              </div>

              <!-- Main Diagnostic Findings -->
              <div>
                <label class="block text-xs text-slate-400 mb-1">Detailed Diagnostic Inspection Notes *</label>
                <textarea 
                  v-model="obdForm.findings"
                  rows="5"
                  required
                  placeholder="Log physical diagnostic notes, scan codes findings, compression test pressure, visual component damage..."
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white focus:border-indigo-500"
                ></textarea>
              </div>

              <!-- Recommended Services -->
              <div>
                <label class="block text-xs text-slate-400 mb-1">Recommended Services / Parts Required</label>
                <textarea 
                  v-model="obdForm.recommendations"
                  rows="3"
                  placeholder="List spark plug replacement, intake manifold gasket set, MAF cleaning service..."
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white focus:border-indigo-500"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4 pt-2">
                <div>
                  <label class="block text-xs text-slate-400 mb-1">Diagnostic Priority *</label>
                  <select 
                    v-model="obdForm.priority"
                    class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
                  >
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs text-slate-400 mb-1">Estimated Workshop Days *</label>
                  <input 
                    v-model.number="obdForm.days_estimated"
                    type="number"
                    required
                    class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white font-mono"
                  />
                </div>
              </div>
            </div>

            <!-- Image Upload Trigger -->
            <div class="pt-4 border-t border-slate-850 flex items-center justify-between">
              <div class="text-[10px] text-slate-400">
                Attach Diagnostic Photos (Scope graphs, visual cracks, etc.)
              </div>
              <button 
                type="button" 
                @click="toast.success('DTC Scope image attached to job card diagnostics file.')"
                class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-750 text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition border border-slate-700/50"
              >
                + Add Scan Photo
              </button>
            </div>

            <!-- Submit buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-850">
              <router-link
                :to="{ name: 'workshop.diagnosis' }"
                class="px-4 py-2.5 border border-slate-700 rounded-xl text-xs font-bold text-slate-450 hover:bg-slate-850 transition"
              >
                Reset
              </router-link>
              <button
                type="submit"
                :disabled="saving"
                class="px-6 py-2.5 bg-indigo-650 hover:bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-wider transition disabled:opacity-50"
              >
                {{ saving ? 'Saving findings...' : 'Finalize Diagnosis & Plan' }}
              </button>
            </div>
          </form>
        </div>

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
const jobCard = ref(null);

const obdForm = reactive({
  dtc_codes: '',
  findings: '',
  recommendations: '',
  priority: 'normal',
  days_estimated: 1
});

const handleJobSelected = (id) => {
  router.push({ name: 'workshop.diagnosis', params: { id } });
};

const fetchJobDetails = async () => {
  loading.value = true;
  try {
    const response = await api.get(`/job-cards/${route.params.id}`);
    jobCard.value = response.data.data;
    
    // Auto fill if some fields exist
    obdForm.findings = jobCard.value.diagnosis || '';
    obdForm.priority = jobCard.value.priority_level || 'normal';
  } catch (err) {
    toast.error('Failed to load Job Card details.');
    router.push({ name: 'workshop.diagnosis' });
  } finally {
    loading.value = false;
  }
};

const simulateDtcScan = (code, desc) => {
  obdForm.dtc_codes = obdForm.dtc_codes ? `${obdForm.dtc_codes}, ${code}` : code;
  obdForm.findings = obdForm.findings 
    ? `${obdForm.findings}\n\n[OBD Scanner Auto-inject Code: ${code}]\nDescription: ${desc}`
    : `[OBD Scanner Auto-inject Code: ${code}]\nDescription: ${desc}`;
  toast.success(`DTC Code ${code} injected to scanner findings.`);
};

const submitDiagnostics = async () => {
  saving.value = true;
  try {
    const diagnosticNotes = `[DTC Codes Detected: ${obdForm.dtc_codes}]\n\n${obdForm.findings}\n\n[Recommendations]\n${obdForm.recommendations}`;
    
    await api.put(`/job-cards/${jobCard.value.id}`, {
      ...jobCard.value,
      diagnosis: diagnosticNotes,
      priority_level: obdForm.priority,
      service_status: 'pending' // Moves directly to pending quotation draft stage
    });
    
    toast.success('Diagnosis completed and logged. Redirecting to Quotation Workspace.');
    router.push({ name: 'workshop.quotation', params: { id: jobCard.value.id } });
  } catch (err) {
    console.error('Diagnosis submission failed', err);
    toast.error(err.response?.data?.message || 'Failed to submit diagnostics details.');
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

<style scoped>
.text-emerald-450 {
  color: #34d399;
}
.text-amber-450 {
  color: #fbbf24;
}
</style>
