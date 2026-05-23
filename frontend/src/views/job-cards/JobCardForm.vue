<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-4">
      <router-link :to="{ name: 'job-cards.index' }" class="text-slate-400 hover:text-slate-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ isEdit ? 'Edit Job Card' : 'Create Job Card' }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ isEdit ? 'Update existing service job details' : 'Register a new service job' }}</p>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
      <div class="p-6 space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Customer Selection -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Customer <span class="text-red-500">*</span></label>
            <select v-model="form.customer_id" @change="onCustomerChange" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" :disabled="loadingCustomers">
              <option value="" disabled>Select a customer</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.name }} ({{ customer.phone || customer.email }})
              </option>
            </select>
          </div>

          <!-- Vehicle Selection -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Vehicle <span class="text-red-500">*</span></label>
            <select v-model="form.vehicle_id" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" :disabled="!form.customer_id || loadingVehicles">
              <option value="" disabled>Select a vehicle</option>
              <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                {{ vehicle.license_plate }} - {{ vehicle.make }} {{ vehicle.model }}
              </option>
            </select>
          </div>

          <!-- Mechanic Selection -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Assigned Mechanic</label>
            <select v-model="form.assigned_mechanic_id" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
              <option value="">Unassigned</option>
              <option v-for="mechanic in mechanics" :key="mechanic.id" :value="mechanic.id">
                {{ mechanic.name }}
              </option>
            </select>
          </div>

          <!-- Service Status -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select v-model="form.service_status" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Service Date -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Service Date</label>
            <input type="date" v-model="form.service_date" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" />
          </div>

          <!-- Delivery Date -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Expected Delivery Date</label>
            <input type="date" v-model="form.delivery_date" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" />
          </div>

          <!-- Complaint / Issue -->
          <div class="col-span-1 md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Customer Complaint / Issue Description <span class="text-red-500">*</span></label>
            <textarea v-model="form.complaint" required rows="3" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="Describe the issues reported by customer"></textarea>
          </div>

          <!-- Diagnosis -->
          <div class="col-span-1 md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Mechanic Diagnosis / Findings</label>
            <textarea v-model="form.diagnosis" rows="3" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="Describe the actual findings"></textarea>
          </div>

          <!-- Notes -->
          <div class="col-span-1 md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Internal Notes</label>
            <textarea v-model="form.notes" rows="2" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"></textarea>
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
        <router-link :to="{ name: 'job-cards.index' }" class="px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Cancel
        </router-link>
        <button type="submit" :disabled="saving" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 flex items-center">
          <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isEdit ? 'Update Job Card' : 'Save Job Card' }}
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
const vehicles = ref([]);
const mechanics = ref([]);

const loadingCustomers = ref(true);
const loadingVehicles = ref(false);

const form = ref({
  customer_id: '',
  vehicle_id: '',
  assigned_mechanic_id: '',
  complaint: '',
  diagnosis: '',
  service_status: 'pending',
  service_date: new Date().toISOString().split('T')[0],
  delivery_date: '',
  notes: ''
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

const loadMechanics = async () => {
  try {
    // Ideally an endpoint to fetch users with role 'Mechanic'
    const response = await api.get('/users', { params: { role: 'Mechanic' } }).catch(() => null);
    if (response) {
      mechanics.value = response.data.data;
    }
  } catch (error) {
    // Ignore, fallback to unassigned
  }
};

const onCustomerChange = async () => {
  form.value.vehicle_id = '';
  if (!form.value.customer_id) {
    vehicles.value = [];
    return;
  }
  loadVehiclesForCustomer(form.value.customer_id);
};

const loadVehiclesForCustomer = async (customerId) => {
  loadingVehicles.value = true;
  try {
    const response = await api.get(`/customers/${customerId}/vehicles`);
    vehicles.value = response.data.data;
  } catch (error) {
    toast.error('Failed to load vehicles');
  } finally {
    loadingVehicles.value = false;
  }
};

const loadJobCard = async () => {
  if (!isEdit.value) return;
  try {
    const response = await api.get(`/job-cards/${route.params.id}`);
    const data = response.data.data;
    Object.keys(form.value).forEach(key => {
      if (data[key] !== undefined && data[key] !== null) {
        if (key === 'service_date' || key === 'delivery_date') {
          form.value[key] = data[key] ? data[key].split('T')[0] : '';
        } else {
          form.value[key] = data[key];
        }
      }
    });
    if (data.customer_id) {
      await loadVehiclesForCustomer(data.customer_id);
    }
  } catch (error) {
    toast.error('Failed to load job card details');
    router.push({ name: 'job-cards.index' });
  }
};

const submitForm = async () => {
  saving.value = true;
  errors.value = {};
  
  // Format dates for backend if necessary
  const payload = { ...form.value };
  if (!payload.assigned_mechanic_id) {
    delete payload.assigned_mechanic_id;
  }
  
  try {
    if (isEdit.value) {
      await api.put(`/job-cards/${route.params.id}`, payload);
      toast.success('Job Card updated successfully');
    } else {
      await api.post('/job-cards', payload);
      toast.success('Job Card created successfully');
    }
    router.push({ name: 'job-cards.index' });
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
  loadMechanics();
  loadJobCard();
});
</script>
