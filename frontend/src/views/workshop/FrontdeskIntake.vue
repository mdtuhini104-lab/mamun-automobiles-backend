<template>
  <div class="max-w-5xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-850 pb-5">
      <div class="flex items-center space-x-4">
        <router-link :to="{ name: 'workshop.hub' }" class="text-slate-400 hover:text-slate-200 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
        </router-link>
        <div>
          <h1 class="text-2xl font-black tracking-tight text-white uppercase">Frontdesk Reception Intake</h1>
          <p class="text-xs text-slate-400 mt-1">High-speed workshop vehicle check-in console with instant customer lookup & repeat client detection.</p>
        </div>
      </div>
      <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
        Active Intake Session
      </span>
    </div>

    <!-- Live Search & Repeat Detection panel -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-950/40 p-5 rounded-2xl border border-slate-850">
      <!-- 1. License Plate Search -->
      <div class="space-y-2">
        <label class="block text-xs font-black uppercase text-indigo-400 tracking-wider">Instant Vehicle Plate Lookup</label>
        <div class="relative">
          <input
            v-model="searchPlate"
            @input="lookupVehicleByPlate"
            type="text"
            placeholder="e.g. DHA-55-9988 or CTG-11-2233"
            class="w-full text-sm bg-slate-900 rounded-xl border border-slate-750 p-3 pl-10 focus:ring-2 focus:ring-indigo-500 text-white font-mono uppercase"
          />
          <span class="absolute left-3.5 top-3.5 text-slate-500 font-bold">🔍</span>
          <!-- Dropdown list -->
          <div v-if="suggestedVehicles.length > 0" class="absolute z-50 w-full mt-2 bg-slate-850 border border-slate-700 rounded-xl shadow-2xl max-h-48 overflow-y-auto">
            <button
              v-for="v in suggestedVehicles"
              :key="v.id"
              @click="selectVehicle(v)"
              class="w-full text-left px-4 py-2.5 hover:bg-slate-750 text-xs font-mono border-b border-slate-800 flex justify-between items-center text-slate-200"
            >
              <span>{{ v.license_plate }} - {{ v.make }} {{ v.model }}</span>
              <span class="text-[9px] bg-slate-700 text-slate-400 px-2 py-0.5 rounded uppercase">Owner: {{ v.customer?.name || 'Walk-in' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- 2. Customer Phone/Name Search -->
      <div class="space-y-2">
        <label class="block text-xs font-black uppercase text-indigo-400 tracking-wider">Customer Phone / Name Quick-Lookup</label>
        <div class="relative">
          <input
            v-model="searchCustomer"
            @input="lookupCustomer"
            type="text"
            placeholder="Search by phone number or name..."
            class="w-full text-sm bg-slate-900 rounded-xl border border-slate-750 p-3 pl-10 focus:ring-2 focus:ring-indigo-500 text-white"
          />
          <span class="absolute left-3.5 top-3.5 text-slate-500 font-bold">👤</span>
          <!-- Dropdown list -->
          <div v-if="suggestedCustomers.length > 0" class="absolute z-50 w-full mt-2 bg-slate-850 border border-slate-700 rounded-xl shadow-2xl max-h-48 overflow-y-auto">
            <button
              v-for="c in suggestedCustomers"
              :key="c.id"
              @click="selectCustomer(c)"
              class="w-full text-left px-4 py-2.5 hover:bg-slate-750 text-xs border-b border-slate-800 flex justify-between items-center text-slate-200"
            >
              <span>{{ c.name }} ({{ c.phone || c.email }})</span>
              <span class="text-[9px] bg-emerald-950 text-emerald-450 px-2 py-0.5 rounded uppercase">Registered</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Form (New / Repeat Mode) -->
    <form @submit.prevent="registerJob" class="space-y-6">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Customer Section -->
        <div class="space-y-4 bg-slate-950/20 border border-slate-850 p-5 rounded-2xl">
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
            <span>Customer Profile</span>
            <span v-if="customerMode === 'found'" class="text-[9px] font-black uppercase bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded">Repeat Client Match</span>
            <span v-else class="text-[9px] font-black uppercase bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded">New Client Profile</span>
          </h3>

          <div class="space-y-3">
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Customer Full Name *</label>
              <input
                v-model="form.customer_name"
                type="text"
                required
                :disabled="customerMode === 'found'"
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white disabled:opacity-60"
              />
            </div>
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Contact Phone *</label>
              <input
                v-model="form.customer_phone"
                type="text"
                required
                :disabled="customerMode === 'found'"
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white disabled:opacity-60"
              />
            </div>
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Email Address</label>
              <input
                v-model="form.customer_email"
                type="email"
                :disabled="customerMode === 'found'"
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white disabled:opacity-60"
              />
            </div>
            <button
              v-if="customerMode === 'found'"
              type="button"
              @click="resetCustomerProfile"
              class="text-[10px] font-bold text-rose-400 hover:text-rose-300 uppercase"
            >
              Clear / Switch Client
            </button>
          </div>
        </div>

        <!-- Vehicle Section -->
        <div class="space-y-4 bg-slate-950/20 border border-slate-850 p-5 rounded-2xl">
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
            <span>Vehicle Details</span>
            <span v-if="vehicleMode === 'found'" class="text-[9px] font-black uppercase bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded">Repeat Vehicle Match</span>
            <span v-else class="text-[9px] font-black uppercase bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded">New Vehicle</span>
          </h3>

          <div class="space-y-3">
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">License Plate Number *</label>
              <input
                v-model="form.license_plate"
                type="text"
                required
                :disabled="vehicleMode === 'found'"
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white font-mono uppercase disabled:opacity-60"
              />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] text-slate-400 mb-1">Vehicle Make *</label>
                <input
                  v-model="form.make"
                  type="text"
                  required
                  :disabled="vehicleMode === 'found'"
                  placeholder="e.g. Toyota"
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white disabled:opacity-60"
                />
              </div>
              <div>
                <label class="block text-[11px] text-slate-400 mb-1">Vehicle Model *</label>
                <input
                  v-model="form.model"
                  type="text"
                  required
                  :disabled="vehicleMode === 'found'"
                  placeholder="e.g. Allion"
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white disabled:opacity-60"
                />
              </div>
            </div>
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Chassis / VIN Number</label>
              <input
                v-model="form.vin"
                type="text"
                :disabled="vehicleMode === 'found'"
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white disabled:opacity-60 font-mono"
              />
            </div>
            <button
              v-if="vehicleMode === 'found'"
              type="button"
              @click="resetVehicleProfile"
              class="text-[10px] font-bold text-rose-400 hover:text-rose-300 uppercase"
            >
              Clear / Switch Vehicle
            </button>
          </div>
        </div>

      </div>

      <!-- Workshop Intake parameters -->
      <div class="space-y-4 bg-slate-950/20 border border-slate-850 p-5 rounded-2xl">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Intake Complaint & Schedule</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 space-y-4">
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Customer Complaint / Issue Description *</label>
              <textarea
                v-model="form.complaint"
                required
                rows="3"
                placeholder="Describe the issues reported by customer (e.g. Engine oil leakage, brake squeal noise, AC cooling issue)"
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              ></textarea>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-[11px] text-slate-400 mb-1">Odometer Reading (KM) *</label>
                <input
                  v-model.number="form.odometer_reading"
                  type="number"
                  required
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
                />
              </div>
              <div>
                <label class="block text-[11px] text-slate-400 mb-1">Fuel Level *</label>
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
                <label class="block text-[11px] text-slate-400 mb-1">Emergency Hazard Level *</label>
                <select
                  v-model="form.emergency_level"
                  required
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
                >
                  <option value="low">Low (Standard)</option>
                  <option value="medium">Medium (Repair Advised)</option>
                  <option value="high">High (Major Faults)</option>
                  <option value="critical">Critical (Safety Hazard)</option>
                </select>
              </div>
            </div>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Service Priority *</label>
              <select
                v-model="form.priority_level"
                required
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              >
                <option value="normal">Normal Priority</option>
                <option value="high">High Priority</option>
                <option value="urgent">Urgent Priority</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Service Date</label>
              <input
                v-model="form.service_date"
                type="date"
                required
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Media attachments (Mock Upload) -->
      <div class="space-y-4 bg-slate-950/20 border border-slate-850 p-5 rounded-2xl">
        <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400">Media Attachments (Photographs / Walkaround Video)</h3>
        <p class="text-[11px] text-slate-400">Click to attach photographs or diagnostic check-in walkaround videos.</p>
        <div class="flex flex-wrap gap-4 items-center">
          <button 
            type="button" 
            @click="mockImageUpload" 
            class="px-4 py-2.5 bg-slate-800 hover:bg-slate-750 border border-slate-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2"
          >
            📸 Capture Photo / Walkaround Video
          </button>
          <div v-if="form.images_paths.length > 0" class="flex gap-2">
            <div 
              v-for="(img, i) in form.images_paths" 
              :key="i"
              class="w-16 h-16 rounded-xl bg-cover bg-center border border-slate-700 relative shadow-md"
              :style="{ backgroundImage: `url(${img})` }"
            >
              <button 
                type="button" 
                @click="form.images_paths.splice(i, 1)" 
                class="absolute -top-1.5 -right-1.5 bg-rose-505 text-white rounded-full w-5 h-5 flex items-center justify-center text-[9px] font-black border border-white"
              >
                ✕
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit action -->
      <div class="flex justify-end gap-3 border-t border-slate-850 pt-4">
        <router-link
          :to="{ name: 'workshop.hub' }"
          class="px-4 py-2 border border-slate-700 rounded-lg text-xs font-bold text-slate-450 hover:bg-slate-850 transition"
        >
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="saving"
          class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-black uppercase tracking-wider transition disabled:opacity-50"
        >
          {{ saving ? 'Registering...' : '1-Click Create Job Card' }}
        </button>
      </div>

    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const router = useRouter();
const toast = useToastStore();

const searchPlate = ref('');
const searchCustomer = ref('');
const suggestedVehicles = ref([]);
const suggestedCustomers = ref([]);
const saving = ref(false);

const customerMode = ref('new'); // new, found
const vehicleMode = ref('new'); // new, found
const activeCustomerId = ref(null);
const activeVehicleId = ref(null);

const form = reactive({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  license_plate: '',
  make: '',
  model: '',
  vin: '',
  complaint: '',
  priority_level: 'normal',
  service_date: new Date().toISOString().split('T')[0],
  odometer_reading: 35000,
  fuel_level: '1/2',
  emergency_level: 'medium',
  images_paths: []
});

const mockImageUpload = () => {
  const list = [
    'https://images.unsplash.com/photo-1517524206127-48bbd363f3d7?w=400',
    'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=400'
  ];
  form.images_paths.push(list[Math.floor(Math.random() * list.length)]);
  toast.success('Mock photo attached to job card.');
};

// Instant customer lookup
const lookupCustomer = async () => {
  if (searchCustomer.value.length < 2) {
    suggestedCustomers.value = [];
    return;
  }
  try {
    const response = await api.get('/customers', { params: { search: searchCustomer.value } });
    suggestedCustomers.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Customer lookup failed', err);
  }
};

const selectCustomer = async (customer) => {
  customerMode.value = 'found';
  activeCustomerId.value = customer.id;
  form.customer_name = customer.name;
  form.customer_phone = customer.phone || '';
  form.customer_email = customer.email || '';
  suggestedCustomers.value = [];
  searchCustomer.value = '';

  // Proactively pull this customer's vehicles to accelerate selection
  try {
    const response = await api.get(`/customers/${customer.id}/vehicles`);
    const vehiclesList = response.data?.data || response.data || [];
    if (vehiclesList.length > 0) {
      // Pre-select the first vehicle if only one exists
      selectVehicle(vehiclesList[0]);
    }
  } catch (err) {
    console.error('Failed to pre-fetch customer vehicles', err);
  }
};

const resetCustomerProfile = () => {
  customerMode.value = 'new';
  activeCustomerId.value = null;
  form.customer_name = '';
  form.customer_phone = '';
  form.customer_email = '';
  resetVehicleProfile();
};

// Instant vehicle lookup by plate
const lookupVehicleByPlate = async () => {
  if (searchPlate.value.length < 2) {
    suggestedVehicles.value = [];
    return;
  }
  try {
    const response = await api.get('/vehicles', { params: { search: searchPlate.value } });
    suggestedVehicles.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Vehicle lookup failed', err);
  }
};

const selectVehicle = (vehicle) => {
  vehicleMode.value = 'found';
  activeVehicleId.value = vehicle.id;
  form.license_plate = vehicle.license_plate || vehicle.registration_no || '';
  form.make = vehicle.make;
  form.model = vehicle.model;
  form.vin = vehicle.vin || '';
  suggestedVehicles.value = [];
  searchPlate.value = '';

  // If vehicle has customer owner, load owner immediately (Repeat Customer Detection!)
  if (vehicle.customer) {
    customerMode.value = 'found';
    activeCustomerId.value = vehicle.customer.id;
    form.customer_name = vehicle.customer.name;
    form.customer_phone = vehicle.customer.phone || '';
    form.customer_email = vehicle.customer.email || '';
  }
};

const resetVehicleProfile = () => {
  vehicleMode.value = 'new';
  activeVehicleId.value = null;
  form.license_plate = '';
  form.make = '';
  form.model = '';
  form.vin = '';
};

// 1-Click Job Card registration
const registerJob = async () => {
  saving.value = true;
  try {
    let customerId = activeCustomerId.value;
    let vehicleId = activeVehicleId.value;

    // 1. Create customer if new
    if (customerMode.value === 'new') {
      const custRes = await api.post('/customers', {
        name: form.customer_name,
        phone: form.customer_phone,
        email: form.customer_email
      });
      customerId = custRes.data.data.id;
    }

    // 2. Create vehicle if new
    if (vehicleMode.value === 'new') {
      const vehRes = await api.post('/vehicles', {
        customer_id: customerId,
        license_plate: form.license_plate,
        make: form.make,
        model: form.model,
        vin: form.vin
      });
      vehicleId = vehRes.data.data.id;
    }

    // 3. Create Job Card
    await api.post('/job-cards', {
      customer_id: customerId,
      vehicle_id: vehicleId,
      complaint: form.complaint,
      priority_level: form.priority_level,
      service_status: 'pending',
      service_date: form.service_date,
      odometer_reading: form.odometer_reading,
      fuel_level: form.fuel_level,
      emergency_level: form.emergency_level,
      images_paths: form.images_paths
    });

    toast.success('Frontdesk check-in completed. Job card generated successfully!');
    router.push({ name: 'workshop.hub' });
  } catch (err) {
    console.error('Intake submission failed', err);
    toast.error(err.response?.data?.message || 'Check-in failed. Verify form errors.');
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
/* Custom local modifications */
input:focus, textarea:focus, select:focus {
  border-color: #4f46e5;
  outline: none;
}
</style>
