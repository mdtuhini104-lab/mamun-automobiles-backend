<template>
  <div class="space-y-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <router-link to="/vehicles/history" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
          <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </router-link>
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Vehicle Timeline</h1>
          <p class="text-sm text-slate-500 mt-1">Detailed service & repair history</p>
        </div>
      </div>
      <button @click="printTimeline" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-semibold shadow-sm hover:bg-indigo-700 transition-colors self-start md:self-auto">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Export PDF
      </button>
    </div>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else class="space-y-6" id="printable-timeline">
      <!-- Vehicle Info Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-5 pointer-events-none">
          <svg class="w-64 h-64 -mt-10 -mr-10" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"></path></svg>
        </div>
        
        <div class="flex items-center gap-4 relative z-10">
          <div class="h-16 w-16 bg-slate-100 border border-slate-200 text-slate-700 rounded-2xl flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
          </div>
          <div>
            <div class="flex items-center gap-3">
              <h2 class="text-2xl font-extrabold text-slate-900">{{ vehicle?.make }} {{ vehicle?.model }}</h2>
              <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 font-mono tracking-wider">{{ vehicle?.registration_number }}</span>
            </div>
            <div class="text-sm text-slate-500 mt-1 flex flex-col sm:flex-row gap-2 sm:gap-4 font-medium">
              <span>Year: {{ vehicle?.year || 'N/A' }}</span>
              <span class="hidden sm:inline text-slate-300">|</span>
              <span>Mileage: <span class="font-bold text-slate-700">{{ vehicle?.mileage || '0' }} km</span></span>
              <span class="hidden sm:inline text-slate-300">|</span>
              <span>Owner: <router-link :to="`/customers/${vehicle?.customer_id}`" class="text-indigo-600 hover:underline">{{ vehicle?.customer_name }}</router-link></span>
            </div>
          </div>
        </div>
        
        <!-- Alerts / Reminders -->
        <div v-if="vehicle?.next_maintenance" class="relative z-10 p-3 bg-amber-50 border border-amber-200 rounded-xl max-w-xs w-full">
          <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
              <p class="text-xs font-bold text-amber-800 uppercase tracking-wider">Next Maintenance</p>
              <p class="text-sm font-semibold text-amber-900 mt-0.5">{{ formatDate(vehicle.next_maintenance) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Timeline -->
      <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 mb-8 flex items-center gap-2">
          <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Service Timeline
        </h3>
        
        <div class="relative pl-4 sm:pl-8">
          <!-- Vertical Line -->
          <div class="absolute top-0 bottom-0 left-4 sm:left-8 w-px bg-slate-200 -ml-px"></div>

          <!-- Timeline Items -->
          <div v-for="(job, index) in history" :key="job.id" class="relative mb-12 last:mb-0">
            <!-- Node -->
            <div class="absolute -left-2 sm:-left-2 w-4 h-4 rounded-full border-2 border-white" :class="job.status === 'completed' ? 'bg-emerald-500' : 'bg-indigo-500'"></div>
            
            <div class="pl-6 sm:pl-8">
              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 mb-2">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900">{{ formatDate(job.date) }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">Job: {{ job.job_card_no }}</span>
                  </div>
                  <h4 class="text-base font-bold text-indigo-600 mt-1">{{ job.service_type }}</h4>
                </div>
                <div class="text-sm font-bold text-slate-700 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">
                  Mileage: {{ job.mileage_at_service }} km
                </div>
              </div>

              <!-- Problem & Notes -->
              <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 mt-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Customer Complaint</p>
                    <p class="text-sm text-slate-700 font-medium">{{ job.complaint }}</p>
                    
                    <div v-if="job.repeat_issue" class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-rose-50 text-rose-700 border border-rose-100 rounded text-xs font-bold">
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                      Repeat Problem Detected
                    </div>
                  </div>
                  <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Mechanic Notes</p>
                    <p class="text-sm text-slate-700 italic border-l-2 border-indigo-200 pl-2 py-0.5">{{ job.mechanic_notes }}</p>
                  </div>
                </div>

                <!-- Parts Replaced -->
                <div v-if="job.parts_replaced?.length" class="mt-4 pt-4 border-t border-slate-200">
                  <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Parts Replaced</p>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="(part, idx) in job.parts_replaced" :key="idx" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white border border-slate-200 text-slate-700 shadow-sm">
                      <svg class="w-3 h-3 text-emerald-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                      {{ part }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="mt-3">
                <router-link :to="`/job-cards/${job.id}`" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">View Full Job Card &rarr;</router-link>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const loading = ref(true);
const vehicle = ref(null);
const history = ref([]);

const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });

const fetchTimeline = async () => {
  loading.value = true;
  try {
    const id = route.params.id;
    // Mock Data
    await new Promise(resolve => setTimeout(resolve, 600));
    vehicle.value = {
      id,
      registration_number: 'XYZ-1234',
      make: 'Toyota',
      model: 'Corolla',
      year: 2019,
      mileage: 45000,
      customer_id: 1,
      customer_name: 'John Doe',
      next_maintenance: '2024-04-15'
    };
    
    history.value = [
      {
        id: 102,
        date: '2023-10-15T10:00:00Z',
        job_card_no: 'JC-1002',
        service_type: 'General Maintenance & Oil Change',
        mileage_at_service: 45000,
        status: 'completed',
        complaint: 'Regular service, engine sounds a bit noisy on startup.',
        mechanic_notes: 'Replaced engine oil and filter. Checked belts, slight wear on alternator belt, recommended replacement next visit.',
        parts_replaced: ['Engine Oil (Synthetic 5W-30)', 'Oil Filter', 'Air Filter'],
        repeat_issue: false
      },
      {
        id: 101,
        date: '2023-04-10T14:30:00Z',
        job_card_no: 'JC-0945',
        service_type: 'Brake Pad Replacement',
        mileage_at_service: 40000,
        status: 'completed',
        complaint: 'Squeaking noise when braking.',
        mechanic_notes: 'Front brake pads were completely worn out. Rotors surfaced. Rear drums cleaned.',
        parts_replaced: ['Front Brake Pads (Ceramic)', 'Brake Fluid'],
        repeat_issue: false
      },
      {
        id: 100,
        date: '2022-10-05T09:15:00Z',
        job_card_no: 'JC-0812',
        service_type: 'Engine Diagnostics',
        mileage_at_service: 35000,
        status: 'completed',
        complaint: 'Check engine light is on.',
        mechanic_notes: 'Scanned OBD2. Found misfire on cylinder 2. Spark plug was faulty. Replaced all spark plugs to be safe.',
        parts_replaced: ['Spark Plugs x4'],
        repeat_issue: true
      }
    ];
  } catch (error) {
    console.error('Error fetching timeline:', error);
  } finally {
    loading.value = false;
  }
};

const printTimeline = () => {
  window.print();
};

onMounted(() => fetchTimeline());
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #printable-timeline, #printable-timeline * {
    visibility: visible;
  }
  #printable-timeline {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
