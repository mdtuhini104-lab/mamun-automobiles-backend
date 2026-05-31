<template>
  <div class="max-w-7xl mx-auto space-y-6 p-6 bg-slate-50 border border-gray-200 rounded-3xl shadow-sm text-slate-800 min-h-screen">
    <JobDetailsLayout :jobCard="selectedQuotation?.job_card || null" :activeStage="4">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-gray-200 pb-5">
        <div class="flex items-center space-x-4">
          <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Customer Approval Tracker</h1>
            <p class="text-xs text-slate-500 mt-1">Supervise pending quotations, track feedback loop, and record customer decisions with secure digital signatures.</p>
          </div>
        </div>
        <span class="bg-indigo-50 text-indigo-700 border border-indigo-200 px-3.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
          {{ pendingApprovals.length }} Pending approvals
        </span>
      </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- List Panel (Left) -->
      <div class="lg:col-span-1 bg-white border border-gray-200 rounded-2xl p-4 flex flex-col gap-4 shadow-sm">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Quotations Waiting Action</h3>
        
        <div v-if="loading" class="flex justify-center py-12">
          <div class="w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div v-else-if="pendingApprovals.length === 0" class="text-center py-16 text-slate-400 text-xs italic">
          No quotations currently awaiting customer approval.
        </div>

        <div v-else class="space-y-3 overflow-y-auto max-h-[60vh] pr-1">
          <button
            v-for="q in pendingApprovals"
            :key="q.id"
            @click="selectQuotation(q)"
            :class="selectedQuotation?.id === q.id ? 'border-indigo-650 bg-indigo-50/50' : 'border-gray-250 bg-white'"
            class="w-full text-left p-3.5 border rounded-xl hover:border-indigo-300 transition flex flex-col justify-between gap-3 shadow-sm"
          >
            <div class="flex justify-between items-start gap-2">
              <span class="text-[10px] font-black text-slate-500 font-mono">{{ q.quotation_number }}</span>
              <span class="px-2 py-0.5 rounded text-[8px] font-black bg-amber-50 text-amber-700 border border-amber-250 uppercase">
                V{{ q.version }}
              </span>
            </div>
            <div>
              <h4 class="font-bold text-slate-850 text-xs">{{ q.job_card?.vehicle?.make }} {{ q.job_card?.vehicle?.model }}</h4>
              <p class="text-[9px] text-slate-500 font-mono mt-0.5">Plate: {{ q.job_card?.vehicle?.license_plate || q.job_card?.vehicle?.registration_no }}</p>
            </div>
            <div class="flex justify-between items-center text-[10px] text-slate-500 border-t border-gray-100 pt-2">
              <span>Customer: {{ q.job_card?.customer?.name }}</span>
              <span class="font-bold text-slate-800 font-mono">৳{{ q.grand_total }}</span>
            </div>
          </button>
        </div>
      </div>

      <!-- Action Panel (Middle & Right) -->
      <div class="lg:col-span-2 space-y-6">
        <div v-if="!selectedQuotation" class="bg-white border border-gray-200 rounded-2xl p-16 text-center text-slate-400 text-xs italic shadow-sm">
          Select a quotation from the queue panel to record customer action.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white border border-gray-200 rounded-3xl p-6 shadow-sm">
          <!-- Quotation Sheet Details -->
          <div class="space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-indigo-650">Sheet details: V{{ selectedQuotation.version }}</h3>
            
            <dl class="space-y-2.5 text-xs text-slate-505">
              <div class="flex justify-between">
                <dt>Plate No:</dt>
                <dd class="font-bold text-slate-800 font-mono">{{ selectedQuotation.job_card?.vehicle?.license_plate || selectedQuotation.job_card?.vehicle?.registration_no }}</dd>
              </div>
              <div class="flex justify-between">
                <dt>Customer Name:</dt>
                <dd class="font-bold text-slate-800">{{ selectedQuotation.job_card?.customer?.name }}</dd>
              </div>
              <div class="flex justify-between">
                <dt>Estimated Products:</dt>
                <dd class="font-semibold text-slate-800 font-mono">৳{{ selectedQuotation.total_product_cost }}</dd>
              </div>
              <div class="flex justify-between">
                <dt>Estimated Labor:</dt>
                <dd class="font-semibold text-slate-800 font-mono">৳{{ selectedQuotation.total_labor_cost }}</dd>
              </div>
              <div class="flex justify-between border-t border-gray-200 pt-2 text-sm">
                <dt class="font-bold text-slate-900">Grand total:</dt>
                <dd class="font-black text-indigo-600 font-mono">৳{{ selectedQuotation.grand_total }}</dd>
              </div>
            </dl>

            <!-- Line Items Preview -->
            <div class="border border-gray-200 rounded-xl p-3 bg-slate-50 text-[10px] space-y-2.5 max-h-[250px] overflow-y-auto">
              <span class="text-slate-500 font-black uppercase tracking-wider block">Estimated Line Items</span>
              <div 
                v-for="item in selectedQuotation.items" 
                :key="item.id"
                class="flex justify-between border-b border-gray-150 pb-1.5 text-slate-700"
              >
                <span>
                  {{ item.item_type === 'product' ? (item.part?.name || 'Product Line') : item.service_name }}
                  <span class="text-[8px] bg-gray-200 text-slate-600 px-1 rounded uppercase">x{{ item.quantity }}</span>
                </span>
                <span class="font-mono text-slate-805 text-slate-800">
                  ৳{{ item.item_type === 'product' ? item.quantity * item.unit_price : item.labor_cost }}
                </span>
              </div>
            </div>
          </div>

          <!-- Customer Action Form -->
          <form @submit.prevent="submitApproval" class="space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-indigo-650 font-mono">Customer Decision</h3>
            
            <div class="space-y-3">
              <div>
                <label class="block text-[10px] text-slate-500 mb-1">Approved/Received By *</label>
                <input
                  v-model="approvalForm.approved_by"
                  type="text"
                  required
                  placeholder="e.g. Client Name or Family Member"
                  class="w-full text-xs bg-slate-50 border border-gray-200 rounded-lg p-2.5 text-slate-800 focus:bg-white"
                />
              </div>

              <div>
                <label class="block text-[10px] text-slate-500 mb-1">Approval Decision Status *</label>
                <select
                  v-model="approvalForm.status"
                  required
                  class="w-full text-xs bg-slate-50 border border-gray-200 rounded-lg p-2.5 text-slate-800 focus:bg-white"
                >
                  <option value="approved">Fully Approved (Activate Work Order)</option>
                  <option value="partially_approved">Partially Approved (Select Specific Lines)</option>
                  <option value="rejected">Rejected (Cancel Job Card)</option>
                </select>
              </div>

              <!-- Partial list -->
              <div v-if="approvalForm.status === 'partially_approved'" class="bg-slate-50 border border-gray-200 p-3 rounded-xl space-y-2 text-[10px] max-h-36 overflow-y-auto">
                <span class="text-slate-500 font-bold block uppercase tracking-wider mb-1">Check Approved Items Only:</span>
                <label 
                  v-for="item in selectedQuotation.items" 
                  :key="item.id" 
                  class="flex items-center gap-2 text-slate-700 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="item.id"
                    v-model="approvalForm.approved_items"
                    class="rounded text-indigo-600 bg-white border-gray-300 focus:ring-indigo-500"
                  />
                  <span>
                    {{ item.item_type === 'product' ? item.part?.name : item.service_name }}
                    (৳{{ item.item_type === 'product' ? item.quantity * item.unit_price : item.labor_cost }})
                  </span>
                </label>
              </div>

              <!-- Digital Signature drawing canvas -->
              <div class="space-y-1">
                <div class="flex justify-between items-center text-[10px] text-slate-505">
                  <label>Secure Representative Digital Signature *</label>
                  <button type="button" @click="clearSignature" class="text-rose-600 hover:text-rose-700 uppercase font-bold text-[9px]">Clear Pad</button>
                </div>
                <div class="border border-gray-200 rounded-xl bg-slate-50 overflow-hidden flex items-center justify-center">
                  <canvas
                    ref="sigCanvas"
                    width="320"
                    height="120"
                    @mousedown="startDrawing"
                    @mousemove="draw"
                    @mouseup="stopDrawing"
                    @mouseleave="stopDrawing"
                    @touchstart="startDrawingTouch"
                    @touchmove="drawTouch"
                    @touchend="stopDrawing"
                    class="w-full h-[120px] cursor-crosshair bg-white"
                  ></canvas>
                </div>
              </div>

              <div>
                <label class="block text-[10px] text-slate-500 mb-1">Interaction Notes / Feedback</label>
                <textarea
                  v-model="approvalForm.notes"
                  rows="2"
                  placeholder="e.g. Logged over phone call. Customer requested standard warranty details..."
                  class="w-full text-xs bg-slate-50 border border-gray-200 rounded-lg p-2.5 text-slate-800 focus:bg-white"
                ></textarea>
              </div>
            </div>

            <!-- Submit buttons -->
            <div class="flex justify-end gap-3 pt-2">
              <button
                type="submit"
                :disabled="submitting || !approvalForm.approved_by"
                class="w-full py-2.5 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-sm"
              >
                {{ submitting ? 'Recording Decision...' : 'Confirm Decision & Activate WO' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
    </JobDetailsLayout>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import JobDetailsLayout from '../../components/workshop/JobDetailsLayout.vue';

const toast = useToastStore();
const router = useRouter();
const loading = ref(true);
const submitting = ref(false);

const pendingApprovals = ref([]);
const selectedQuotation = ref(null);

const approvalForm = reactive({
  status: 'approved',
  approved_by: '',
  notes: '',
  approved_items: [],
  signature_path: ''
});

// Canvas sign states
const sigCanvas = ref(null);
let isDrawing = false;
let ctx = null;

const fetchPendingQuotations = async () => {
  loading.value = true;
  try {
    const response = await api.get('/quotations', { params: { status: 'draft' } });
    pendingApprovals.value = response.data?.data || response.data || [];
    if (pendingApprovals.value.length > 0) {
      selectQuotation(pendingApprovals.value[0]);
    } else {
      selectedQuotation.value = null;
    }
  } catch (err) {
    console.error('Failed to fetch draft quotations', err);
    toast.error('Failed to load pending approvals queue.');
  } finally {
    loading.value = false;
  }
};

const selectQuotation = async (q) => {
  try {
    const details = await api.get(`/quotations/${q.id}`);
    selectedQuotation.value = details.data?.data || details.data;
    
    // Reset forms
    approvalForm.status = 'approved';
    approvalForm.approved_by = selectedQuotation.value.job_card?.customer?.name || '';
    approvalForm.notes = '';
    approvalForm.approved_items = selectedQuotation.value.items.map(i => i.id);
    approvalForm.signature_path = '';
    
    setTimeout(clearSignature, 100);
  } catch (err) {
    console.error('Failed to fetch quotation details', err);
  }
};

// Canvas drawing functions
const initCtx = () => {
  if (!ctx && sigCanvas.value) {
    ctx = sigCanvas.value.getContext('2d');
    ctx.strokeStyle = '#6366f1'; // Indigo signature color
    ctx.lineWidth = 2.5;
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
    approvalForm.signature_path = '';
  }
};

const saveSignature = () => {
  if (sigCanvas.value) {
    approvalForm.signature_path = sigCanvas.value.toDataURL('image/png');
  }
};

const submitApproval = async () => {
  if (!approvalForm.signature_path) {
    toast.warning('Please obtain a customer signature verification sign-off.');
    return;
  }
  
  submitting.value = true;
  try {
    const payload = {
      status: approvalForm.status,
      approved_by: approvalForm.approved_by,
      notes: approvalForm.notes,
      signature_path: approvalForm.signature_path,
      approved_items: approvalForm.approved_items
    };
    
    await api.post(`/quotations/${selectedQuotation.value.id}/approve`, payload);
    toast.success('Approval decision recorded. Active Work Order dispatched.');
    router.push({ name: 'workshop.work-orders' });
  } catch (err) {
    console.error('Failed to submit customer approval', err);
    toast.error(err.response?.data?.message || 'Approval submission failed.');
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchPendingQuotations();
});
</script>

<style scoped>
canvas {
  touch-action: none;
}
</style>
