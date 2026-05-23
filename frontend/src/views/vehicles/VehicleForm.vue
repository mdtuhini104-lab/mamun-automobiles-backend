<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-4">
      <router-link :to="{ name: 'vehicles.index' }" class="text-slate-400 hover:text-slate-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ isEdit ? 'Edit Vehicle' : 'Add New Vehicle' }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ isEdit ? 'Update existing vehicle details' : 'Register a new vehicle in the system' }}</p>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
      <div class="p-6 space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="col-span-1 md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Customer / Owner <span class="text-red-500">*</span></label>
            <select v-model="form.customer_id" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" :disabled="loadingCustomers">
              <option value="" disabled>Select a customer</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.name }} ({{ customer.phone || customer.email }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">License Plate <span class="text-red-500">*</span></label>
            <input type="text" v-model="form.license_plate" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="e.g. DHK-12-3456" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Make / Brand <span class="text-red-500">*</span></label>
            <input type="text" v-model="form.make" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="e.g. Toyota" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Model <span class="text-red-500">*</span></label>
            <input type="text" v-model="form.model" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="e.g. Corolla" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Year</label>
            <input type="number" v-model="form.year" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="e.g. 2022" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Chassis No. (VIN)</label>
            <input type="text" v-model="form.vin" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="Vehicle Identification Number" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Engine Number</label>
            <input type="text" v-model="form.engine_number" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="Engine No." />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Color</label>
            <input type="text" v-model="form.color" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="e.g. Pearl White" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Fuel Type</label>
            <select v-model="form.fuel_type" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
              <option value="" disabled>Select Fuel Type</option>
              <option value="Petrol">Petrol</option>
              <option value="Diesel">Diesel</option>
              <option value="Octane">Octane</option>
              <option value="CNG">CNG</option>
              <option value="LPG">LPG</option>
              <option value="Hybrid">Hybrid</option>
              <option value="Electric">Electric</option>
            </select>
          </div>
        </div>

        <!-- Validation Errors -->
        <div v-if="Object.keys(errors).length > 0" class="rounded-md bg-red-50 p-4 mt-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                  <li v-for="(errorArray, field) in errors" :key="field">{{ errorArray[0] }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3">
        <router-link :to="{ name: 'vehicles.index' }" class="px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Cancel
        </router-link>
        <button type="submit" :disabled="saving" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 flex items-center">
          <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isEdit ? 'Update Vehicle' : 'Save Vehicle' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const router = useRouter();
const route = useRoute();
const toast = useToastStore();

const isEdit = computed(() => !!route.params.id);
const saving = ref(false);
const errors = ref({});
const customers = ref([]);
const loadingCustomers = ref(true);

const form = ref({
  customer_id: '',
  license_plate: '',
  make: '',
  model: '',
  year: '',
  vin: '',
  engine_number: '',
  color: '',
  fuel_type: ''
});

const loadCustomers = async () => {
  loadingCustomers.value = true;
  try {
    const response = await api.get('/customers', { params: { per_page: 1000 } });
    customers.value = response.data.data;
  } catch (error) {
    toast.error('Failed to load customers');
  } finally {
    loadingCustomers.value = false;
  }
};

const loadVehicle = async () => {
  if (!isEdit.value) return;
  try {
    const response = await api.get(`/vehicles/${route.params.id}`);
    const data = response.data.data;
    Object.keys(form.value).forEach(key => {
      if (data[key] !== undefined) {
        form.value[key] = data[key];
      }
    });
  } catch (error) {
    toast.error('Failed to load vehicle details');
    router.push({ name: 'vehicles.index' });
  }
};

const submitForm = async () => {
  saving.value = true;
  errors.value = {};
  
  try {
    if (isEdit.value) {
      await api.put(`/vehicles/${route.params.id}`, form.value);
      toast.success('Vehicle updated successfully');
    } else {
      await api.post('/vehicles', form.value);
      toast.success('Vehicle added successfully');
    }
    router.push({ name: 'vehicles.index' });
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
      toast.error('Please check the form for errors');
    } else {
      toast.error(error.response?.data?.message || 'Something went wrong');
    }
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadCustomers();
  loadVehicle();
});
</script>
