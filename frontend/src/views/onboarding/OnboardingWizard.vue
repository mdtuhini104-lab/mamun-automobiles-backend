<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">Guided Setup & Onboarding Wizard</h1>
        <p class="text-xs text-slate-400 mt-1">Configure your workspace defaults, import corporate ledgers, and trigger test sandboxes.</p>
      </div>
    </div>

    <!-- Wizard Step indicator -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-8">
      <div class="flex items-center justify-between max-w-xl mx-auto">
        <div 
          v-for="step in steps" 
          :key="step.number"
          class="flex items-center gap-2"
        >
          <div 
            class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs transition border"
            :class="getStepBadgeClasses(step.number)"
          >
            {{ step.number }}
          </div>
          <span class="text-xs font-bold hidden sm:inline" :class="currentStep === step.number ? 'text-white' : 'text-slate-500'">
            {{ step.label }}
          </span>
        </div>
      </div>

      <!-- Step Content -->
      <div class="max-w-xl mx-auto border-t border-slate-800 pt-6">
        <!-- Step 1: Branch Config -->
        <div v-if="currentStep === 1" class="space-y-4">
          <h3 class="text-base font-black text-white">Configure Workspace Context</h3>
          <p class="text-xs text-slate-400 leading-relaxed">
            Ensure you have registered your main branch locations and mapped staff designations. Standard Shift configurations and default tax brackets (15% VAT) have been seeded automatically on workspace activation.
          </p>
          <div class="p-4 bg-slate-950 rounded-2xl border border-slate-850 space-y-2">
            <p class="text-xs text-white">✓ Default Shift: <span class="text-slate-400">Regular Day Shift (09:00 - 18:00)</span></p>
            <p class="text-xs text-white">✓ Currency: <span class="text-slate-400">USD (default)</span></p>
          </div>
        </div>

        <!-- Step 2: CSV Importers -->
        <div v-if="currentStep === 2" class="space-y-6">
          <div class="space-y-1">
            <h3 class="text-base font-black text-white">Bulk Data CSV Importers</h3>
            <p class="text-xs text-slate-400">Upload existing client lists or parts catalogs directly into the database.</p>
          </div>

          <!-- Customer CSV -->
          <div class="p-4 bg-slate-950 rounded-2xl border border-slate-850 space-y-3">
            <h4 class="text-xs font-black text-white uppercase tracking-wider">Import Customer CSV</h4>
            <p class="text-[10px] text-slate-400 leading-normal">Requires headers: <span class="font-mono text-indigo-400">name, phone, email, address</span></p>
            <div class="flex items-center gap-3">
              <input 
                type="file" 
                ref="customerFile"
                @change="handleCustomerFileChange"
                class="hidden" 
                accept=".csv"
              />
              <button 
                type="button" 
                @click="$refs.customerFile.click()"
                class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold rounded-xl transition"
              >
                {{ selectedCustomerFileName || 'Choose CSV File' }}
              </button>
              <button 
                @click="uploadCustomers"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
                :disabled="!selectedCustomerFile || importingCustomers"
              >
                {{ importingCustomers ? 'Importing...' : 'Upload' }}
              </button>
            </div>
          </div>

          <!-- Parts CSV -->
          <div class="p-4 bg-slate-950 rounded-2xl border border-slate-850 space-y-3">
            <h4 class="text-xs font-black text-white uppercase tracking-wider">Import Parts Catalog CSV</h4>
            <p class="text-[10px] text-slate-400 leading-normal">Requires headers: <span class="font-mono text-indigo-400">name, sku, price, quantity</span></p>
            <div class="flex items-center gap-3">
              <input 
                type="file" 
                ref="partsFile"
                @change="handlePartsFileChange"
                class="hidden" 
                accept=".csv"
              />
              <button 
                type="button" 
                @click="$refs.partsFile.click()"
                class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold rounded-xl transition"
              >
                {{ selectedPartsFileName || 'Choose CSV File' }}
              </button>
              <button 
                @click="uploadParts"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
                :disabled="!selectedPartsFile || importingParts"
              >
                {{ importingParts ? 'Importing...' : 'Upload' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Step 3: Seed Sandbox -->
        <div v-if="currentStep === 3" class="space-y-4 text-center py-6">
          <h3 class="text-base font-black text-white">Seed Sandbox Demo Workload</h3>
          <p class="text-xs text-slate-400 leading-relaxed max-w-sm mx-auto">
            Populate the database with test customer accounts, mock vehicles, dummy parts, and quotations to run quick workflow simulations.
          </p>
          <div class="pt-4 flex flex-col gap-3 max-w-xs mx-auto">
            <button 
              @click="generateDemoData"
              class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition border border-slate-750"
              :disabled="generatingDemo"
            >
              {{ generatingDemo ? 'Populating database...' : 'Generate Demo Data' }}
            </button>
            <button 
              @click="finishOnboarding"
              class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
            >
              Finish guided setup
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="flex justify-between items-center max-w-xl mx-auto border-t border-slate-800 pt-6">
        <button 
          @click="prevStep" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-750 text-xs font-bold rounded-xl transition"
          :disabled="currentStep === 1"
        >
          Back
        </button>
        <button 
          @click="nextStep" 
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
          v-if="currentStep < 3"
        >
          Continue
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const router = useRouter();

const currentStep = ref(1);

const steps = [
  { number: 1, label: 'Configuration' },
  { number: 2, label: 'Data Importers' },
  { number: 3, label: 'Test Sandbox' }
];

const customerFile = ref(null);
const partsFile = ref(null);
const selectedCustomerFile = ref(null);
const selectedPartsFile = ref(null);
const selectedCustomerFileName = ref('');
const selectedPartsFileName = ref('');

const importingCustomers = ref(false);
const importingParts = ref(false);
const generatingDemo = ref(false);

const getStepBadgeClasses = (stepNum) => {
  if (currentStep.value === stepNum) {
    return 'bg-indigo-600 border-indigo-500 text-white';
  }
  if (currentStep.value > stepNum) {
    return 'bg-emerald-600/10 border-emerald-500/20 text-emerald-400';
  }
  return 'bg-slate-900 border-slate-800 text-slate-500';
};

const handleCustomerFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedCustomerFile.value = file;
    selectedCustomerFileName.value = file.name;
  }
};

const handlePartsFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedPartsFile.value = file;
    selectedPartsFileName.value = file.name;
  }
};

const uploadCustomers = async () => {
  importingCustomers.value = true;
  const formData = new FormData();
  formData.append('file', selectedCustomerFile.value);
  
  try {
    const response = await api.post('/onboarding/import-customers', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    toast.success(response.data.message);
    selectedCustomerFile.value = null;
    selectedCustomerFileName.value = '';
  } catch (error) {
    console.error('Customer CSV import failed', error);
    toast.error('Import failed. Ensure columns are structured correctly.');
  } finally {
    importingCustomers.value = false;
  }
};

const uploadParts = async () => {
  importingParts.value = true;
  const formData = new FormData();
  formData.append('file', selectedPartsFile.value);
  
  try {
    const response = await api.post('/onboarding/import-parts', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    toast.success(response.data.message);
    selectedPartsFile.value = null;
    selectedPartsFileName.value = '';
  } catch (error) {
    console.error('Parts CSV import failed', error);
    toast.error('Import failed. Check CSV column mappings.');
  } finally {
    importingParts.value = false;
  }
};

const generateDemoData = async () => {
  generatingDemo.value = true;
  try {
    const response = await api.post('/onboarding/generate-demo');
    toast.success(response.data.message);
  } catch (error) {
    console.error('Failed to generate demo data', error);
    toast.error('Simulation seeding failed. Verify database context.');
  } finally {
    generatingDemo.value = false;
  }
};

const nextStep = () => {
  if (currentStep.value < 3) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const finishOnboarding = () => {
  toast.success('Onboarding complete! Redirecting to live board.');
  router.push({ name: 'workshop.live-board' });
};
</script>

<style scoped>
</style>
