<template>
  <div class="max-w-4xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    
    <!-- Fallback Stage Selector -->
    <WorkspaceJobSelector 
      v-if="!route.params.id" 
      stage="delivery" 
      title="Select Vehicle for Delivery Handover" 
      @selected="handleJobSelected"
    />

    <div v-else-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-800 rounded w-1/4"></div>
      <div class="h-96 bg-slate-800 rounded"></div>
    </div>

    <JobDetailsLayout v-else-if="jobCard" :jobCard="jobCard" :activeStage="9">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-850 pb-5">
        <div class="flex items-center space-x-4">
          <div v-if="jobCard">
            <h1 class="text-2xl font-black tracking-tight text-white uppercase">Vehicle Handover Delivery</h1>
            <p class="text-xs text-slate-400 mt-1">JC #{{ String(jobCard.id).padStart(5, '0') }}</p>
          </div>
        </div>
      </div>

    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-800 rounded w-1/4"></div>
      <div class="h-96 bg-slate-800 rounded"></div>
    </div>

    <div v-else-if="jobCard" class="space-y-6">
      
      <!-- Balance Check Gate (Invoice Settlement Linkage) -->
      <div 
        v-if="invoice && invoice.due_amount > 0" 
        class="bg-rose-950/20 border border-rose-900/40 p-5 rounded-2xl flex flex-col sm:flex-row justify-between sm:items-center gap-4 animate-in fade-in"
      >
        <div class="space-y-1">
          <h4 class="text-sm font-black text-rose-400 flex items-center gap-2">
            ⚠️ Outstanding Balance Pending Settlement
          </h4>
          <p class="text-xs text-slate-400">This vehicle has an active invoice with BDT {{ formatCurrency(invoice.due_amount) }} due. Settle payment before handing over keys.</p>
        </div>
        <router-link
          :to="{ name: 'workshop.settlement', params: { id: invoice.id } }"
          class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shrink-0 text-center"
        >
          Go to Settlement Workspace
        </router-link>
      </div>

      <div 
        v-else-if="invoice && invoice.payment_status === 'paid'" 
        class="bg-emerald-950/10 border border-emerald-900/30 p-4 rounded-2xl text-xs text-emerald-400 font-bold flex items-center gap-2"
      >
        ✔ Invoice Fully Settled & Cleared for Handover (Paid: ৳{{ invoice.paid_amount }})
      </div>

      <div v-else-if="!invoice" class="bg-amber-950/15 border border-amber-900/40 p-5 rounded-2xl flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div class="space-y-1">
          <h4 class="text-sm font-black text-amber-400">Invoice Not Generated</h4>
          <p class="text-xs text-slate-400">Final bill generation is required before key release.</p>
        </div>
        <button
          @click="generateInvoice"
          :disabled="generatingInvoice"
          class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shrink-0"
        >
          {{ generatingInvoice ? 'Generating...' : 'Auto-Generate Invoice' }}
        </button>
      </div>

      <!-- Handover form -->
      <form @submit.prevent="submitHandover" class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-950/40 p-5 rounded-2xl border border-slate-850">
          <!-- Collector Name -->
          <div class="space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400 font-mono">Receiver Profile</h3>
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Delivered To (Collector Name) *</label>
              <input
                v-model="form.delivered_to"
                type="text"
                required
                placeholder="e.g. Mr. Mamunur Rahman (Customer)"
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              />
            </div>
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Handover Clerk Notes</label>
              <textarea
                v-model="form.notes"
                rows="3"
                placeholder="Details of returned worn parts (brake pads/spark plugs) returned to customer, or visual check feedback..."
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              ></textarea>
            </div>
          </div>

          <!-- Digital signature pad -->
          <div class="space-y-2">
            <div class="flex justify-between items-center text-xs text-slate-400">
              <label>Customer Handover Sign-off Signature *</label>
              <button type="button" @click="clearSignature" class="text-rose-400 hover:text-rose-350 text-[10px] font-bold uppercase">Clear Pad</button>
            </div>
            <div class="border border-slate-800 rounded-xl bg-slate-900/80 overflow-hidden flex items-center justify-center">
              <canvas
                ref="sigCanvas"
                width="360"
                height="150"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart="startDrawingTouch"
                @touchmove="drawTouch"
                @touchend="stopDrawing"
                class="w-full h-[150px] cursor-crosshair"
              ></canvas>
            </div>
            <p class="text-[10px] text-slate-500 italic">Sign on the touch pad using a mouse, pointer, or touch device.</p>
          </div>
        </div>

        <!-- Handover photographs -->
        <div class="bg-slate-950/40 p-5 rounded-2xl border border-slate-850 space-y-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400">Delivery Photograph Verification</h3>
          <p class="text-[11px] text-slate-400">Optional: Record photo of the vehicle key handover or departing the service bay.</p>
          <div class="flex items-center gap-4">
            <button
              type="button"
              @click="mockPhotoUpload"
              class="w-20 h-20 border-2 border-dashed border-slate-700 hover:border-slate-500 hover:text-slate-300 rounded-2xl flex items-center justify-center text-slate-500 text-xl font-bold transition"
            >
              +
            </button>
            <div v-if="form.delivery_photos.length > 0" class="flex gap-2">
              <div
                v-for="(photo, idx) in form.delivery_photos"
                :key="idx"
                class="w-20 h-20 rounded-2xl bg-cover bg-center border border-slate-750 relative shadow"
                :style="{ backgroundImage: `url(${photo})` }"
              >
                <button
                  type="button"
                  @click="form.delivery_photos.splice(idx, 1)"
                  class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[9px] shadow border border-white"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end gap-3 border-t border-slate-850 pt-4">
          <router-link
            :to="{ name: 'workshop.hub' }"
            class="px-4 py-2 border border-slate-700 rounded-lg text-xs font-bold text-slate-450 hover:bg-slate-850 transition"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            :disabled="saving || !form.signature_path"
            class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black uppercase tracking-wider transition disabled:opacity-50"
          >
            {{ saving ? 'Logging delivery...' : 'Complete Key Handover & Close Job' }}
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
const generatingInvoice = ref(false);

const jobCard = ref(null);
const invoice = ref(null);

const form = reactive({
  job_card_id: null,
  delivered_to: '',
  signature_path: '',
  delivery_photos: [],
  notes: ''
});

// Canvas signature
const sigCanvas = ref(null);
let isDrawing = false;
let ctx = null;

const handleJobSelected = (id) => {
  router.push({ name: 'workshop.delivery', params: { id } });
};

const fetchJobAndInvoice = async () => {
  if (!route.params.id) {
    jobCard.value = null;
    invoice.value = null;
    loading.value = false;
    return;
  }
  loading.value = true;
  try {
    const jcRes = await api.get(`/job-cards/${route.params.id}`);
    jobCard.value = jcRes.data.data;
    form.job_card_id = jobCard.value.id;
    form.delivered_to = jobCard.value.customer?.name || '';

    // Load invoice
    const invRes = await api.get('/invoices', { params: { job_card_id: jobCard.value.id } });
    const invoices = invRes.data?.data || invRes.data || [];
    if (invoices.length > 0) {
      const details = await api.get(`/invoices/${invoices[0].id}`);
      invoice.value = details.data?.data || details.data;
    }
  } catch (err) {
    toast.error('Failed to load vehicle details');
    router.push({ name: 'workshop.hub' });
  } finally {
    loading.value = false;
  }
};

const generateInvoice = async () => {
  generatingInvoice.value = true;
  try {
    const res = await api.post(`/invoices/generate/${jobCard.value.id}`);
    toast.success('Invoice compiled and generated successfully.');
    await fetchJobAndInvoice();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Invoice generation failed.');
  } finally {
    generatingInvoice.value = false;
  }
};

// Canvas drawing functions
const initCtx = () => {
  if (!ctx && sigCanvas.value) {
    ctx = sigCanvas.value.getContext('2d');
    ctx.strokeStyle = '#34d399'; // Emerald signature line ink
    ctx.lineWidth = 3.0;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
  }
};

const startDrawing = (e) => {
  isDrawing = true;
  initCtx();
  ctx.beginPath();
  const rect = sigCanvas.value.getBoundingClientRect();
  ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
};

const draw = (e) => {
  if (!isDrawing) return;
  const rect = sigCanvas.value.getBoundingClientRect();
  ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
  ctx.stroke();
};

const stopDrawing = () => {
  isDrawing = false;
  saveSignature();
};

const startDrawingTouch = (e) => {
  isDrawing = true;
  initCtx();
  ctx.beginPath();
  const rect = sigCanvas.value.getBoundingClientRect();
  const touch = e.touches[0];
  ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
};

const drawTouch = (e) => {
  if (!isDrawing) return;
  e.preventDefault();
  const rect = sigCanvas.value.getBoundingClientRect();
  const touch = e.touches[0];
  ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
  ctx.stroke();
};

const clearSignature = () => {
  if (sigCanvas.value) {
    const canvas = sigCanvas.value;
    ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    form.signature_path = '';
  }
};

const saveSignature = () => {
  if (sigCanvas.value) {
    form.signature_path = sigCanvas.value.toDataURL('image/png');
  }
};

const mockPhotoUpload = () => {
  const list = [
    'https://images.unsplash.com/photo-1542282088-fe8426682b8f?w=400',
    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=400'
  ];
  form.delivery_photos.push(list[Math.floor(Math.random() * list.length)]);
  toast.success('Photograph registered.');
};

const submitHandover = async () => {
  if (invoice && invoice.value && invoice.value.due_amount > 0) {
    toast.warning('Cannot close job card: settle remaining invoice balance.');
    return;
  }
  saving.value = true;
  try {
    await api.post('/vehicle-delivery', { ...form });
    toast.success('Vehicle handed over. Job card closed and archived.');
    router.push({ name: 'workshop.hub' });
  } catch (err) {
    toast.error(err.response?.data?.message || 'Handover failed.');
  } finally {
    saving.value = false;
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value || 0);
};

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchJobAndInvoice();
  } else {
    jobCard.value = null;
    invoice.value = null;
    loading.value = false;
  }
});

onMounted(() => {
  if (route.params.id) {
    fetchJobAndInvoice();
  } else {
    loading.value = false;
  }
});
</script>

<style scoped>
canvas {
  touch-action: none;
}
</style>
