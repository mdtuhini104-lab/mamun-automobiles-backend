<template>
  <div class="space-y-6 p-6 bg-slate-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
          Delivery & Quality Control Hub
          <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
            Realtime Operations
          </span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Supervise vehicle road tests, checklist verifications, and secure customer handover sign-offs.</p>
      </div>

      <!-- Live Counters -->
      <div class="flex gap-4">
        <div class="bg-white px-4 py-3 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-lg">
            {{ pendingQcList.length }}
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pending QC</p>
            <p class="text-xs font-black text-slate-800">Inspections</p>
          </div>
        </div>
        <div class="bg-white px-4 py-3 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-lg">
            {{ readyDeliveryList.length }}
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ready Vehicles</p>
            <p class="text-xs font-black text-slate-800">Deliveries</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Mode Selector Tabs -->
    <div class="flex border-b border-slate-200 gap-6">
      <button 
        @click="activeTab = 'qc'"
        class="pb-4 text-sm font-bold uppercase tracking-wider transition-all focus:outline-none relative"
        :class="activeTab === 'qc' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-slate-400 hover:text-slate-600'"
      >
        Quality Control Queue ({{ pendingQcList.length }})
      </button>
      <button 
        @click="activeTab = 'delivery'"
        class="pb-4 text-sm font-bold uppercase tracking-wider transition-all focus:outline-none relative"
        :class="activeTab === 'delivery' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-slate-400 hover:text-slate-600'"
      >
        Delivery Handover Queue ({{ readyDeliveryList.length }})
      </button>
    </div>

    <!-- Active Tab Content -->
    <div class="grid grid-cols-1 gap-6">
      <!-- 1. QC Queue Grid -->
      <div v-if="activeTab === 'qc'" class="space-y-4">
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
          <div class="w-10 h-10 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-sm text-slate-500 font-bold uppercase tracking-wider">Loading QC Queue...</p>
        </div>

        <div v-else-if="pendingQcList.length === 0" class="bg-white border border-slate-200 rounded-3xl py-16 text-center shadow-sm">
          <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-4 text-sm font-bold text-slate-900">All Vehicles Passed Inspection</h3>
          <p class="mt-2 text-xs text-slate-500 max-w-sm mx-auto">There are no work orders currently waiting for supervisor quality checks.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="wo in pendingQcList" 
            :key="wo.id"
            class="bg-white border border-slate-200 hover:border-slate-300 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between gap-5"
          >
            <div>
              <div class="flex justify-between items-start">
                <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-800 ring-1 ring-inset ring-amber-600/20 uppercase tracking-wider">
                  {{ wo.status }}
                </span>
                <span class="text-xs font-mono font-bold text-slate-400">#WO-{{ wo.id }}</span>
              </div>

              <div class="mt-4">
                <h3 class="text-base font-black text-slate-900">
                  {{ wo.job_card?.vehicle?.make || 'Toyota' }} {{ wo.job_card?.vehicle?.model || 'Allion' }}
                </h3>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Plate: {{ wo.job_card?.vehicle?.registration_no || wo.job_card?.vehicle?.plate_number || 'DHA-55-9988' }}</p>
              </div>

              <div class="mt-4 border-t border-slate-100 pt-3 text-xs space-y-2">
                <div class="flex justify-between">
                  <span class="text-slate-400">Customer:</span>
                  <span class="font-bold text-slate-700">{{ wo.job_card?.customer?.name || 'Walk-in Client' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400">Estimated Cost:</span>
                  <span class="font-bold text-slate-700">৳{{ wo.quotation?.grand_total || '0.00' }}</span>
                </div>
              </div>
            </div>

            <button 
              @click="openQcModal(wo)"
              class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-sm hover:shadow transition-colors"
            >
              Perform QC Inspection
            </button>
          </div>
        </div>
      </div>

      <!-- 2. Delivery Queue Grid -->
      <div v-if="activeTab === 'delivery'" class="space-y-4">
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
          <div class="w-10 h-10 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-sm text-slate-500 font-bold uppercase tracking-wider">Loading Deliveries...</p>
        </div>

        <div v-else-if="readyDeliveryList.length === 0" class="bg-white border border-slate-200 rounded-3xl py-16 text-center shadow-sm">
          <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <h3 class="mt-4 text-sm font-bold text-slate-900">No Vehicles Ready for Handover</h3>
          <p class="mt-2 text-xs text-slate-500 max-w-sm mx-auto">Vehicles will automatically appear here once their supervisor quality inspection passes.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="jc in readyDeliveryList" 
            :key="jc.id"
            class="bg-white border border-slate-200 hover:border-slate-300 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between gap-5"
          >
            <div>
              <div class="flex justify-between items-start">
                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-800 ring-1 ring-inset ring-emerald-600/20 uppercase tracking-wider">
                  Ready to Deliver
                </span>
                <span class="text-xs font-mono font-bold text-slate-400">#JC-{{ jc.id }}</span>
              </div>

              <div class="mt-4">
                <h3 class="text-base font-black text-slate-900">
                  {{ jc.vehicle?.make || 'Honda' }} {{ jc.vehicle?.model || 'Civic' }}
                </h3>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Plate: {{ jc.vehicle?.registration_no || jc.vehicle?.plate_number || 'CTG-11-2233' }}</p>
              </div>

              <div class="mt-4 border-t border-slate-100 pt-3 text-xs space-y-2">
                <div class="flex justify-between">
                  <span class="text-slate-400">Owner:</span>
                  <span class="font-bold text-slate-700">{{ jc.customer?.name || 'Walk-in Client' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400">Phone:</span>
                  <span class="font-bold text-slate-700">{{ jc.customer?.phone || '-' }}</span>
                </div>
              </div>
            </div>

            <button 
              @click="openDeliveryModal(jc)"
              class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-sm hover:shadow transition-colors"
            >
              Process Handover & Sign-off
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Quality Control (QC) Modal -->
    <div v-if="showQcModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm animate-in fade-in duration-200">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-black text-slate-950">QC Inspection Report</h3>
            <p class="text-xs text-slate-500 mt-0.5">WO #{{ selectedWorkOrder?.id }} - {{ selectedWorkOrder?.job_card?.vehicle?.make }} {{ selectedWorkOrder?.job_card?.vehicle?.model }}</p>
          </div>
          <button @click="showQcModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
            ✕
          </button>
        </div>

        <!-- Body -->
        <form @submit.prevent="submitQcReport" class="flex-1 overflow-y-auto p-6 space-y-6">
          <!-- Road Test Checklist -->
          <div class="space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Road Test Checklist</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label 
                v-for="(checked, key) in qcForm.checklist" 
                :key="key"
                class="flex items-center gap-3 p-3 border border-slate-200 rounded-2xl hover:bg-slate-50 cursor-pointer transition"
              >
                <input 
                  type="checkbox" 
                  v-model="qcForm.checklist[key]"
                  class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"
                />
                <span class="text-xs font-bold text-slate-700 capitalize">{{ key.replace('_', ' ') }}</span>
              </label>
            </div>
          </div>

          <!-- Road Test Performed -->
          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Road Test Performed</label>
            <div class="flex gap-4">
              <button 
                type="button"
                @click="qcForm.road_test_performed = true"
                class="flex-1 py-3 rounded-2xl font-bold text-xs border transition"
                :class="qcForm.road_test_performed ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
              >
                Yes, Performed
              </button>
              <button 
                type="button"
                @click="qcForm.road_test_performed = false"
                class="flex-1 py-3 rounded-2xl font-bold text-xs border transition"
                :class="!qcForm.road_test_performed ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
              >
                No
              </button>
            </div>
          </div>

          <!-- Road Test Notes -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Inspection & Road Test Notes</label>
            <textarea 
              v-model="qcForm.road_test_notes" 
              rows="3" 
              placeholder="Detail visual observations, brake performance, alignment status..."
              class="w-full text-xs rounded-2xl border-slate-200 p-3 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            ></textarea>
          </div>

          <!-- Final QC Verdict -->
          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">QC Verdict Decision</label>
            <div class="flex gap-4">
              <button 
                type="button"
                @click="qcForm.status = 'passed'"
                class="flex-1 py-4 rounded-2xl font-bold text-xs border flex items-center justify-center gap-2 transition"
                :class="qcForm.status === 'passed' ? 'bg-emerald-50 border-emerald-600 text-emerald-700 ring-2 ring-emerald-600/10' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
              >
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Inspection Passed
              </button>
              <button 
                type="button"
                @click="qcForm.status = 'failed'"
                class="flex-1 py-4 rounded-2xl font-bold text-xs border flex items-center justify-center gap-2 transition"
                :class="qcForm.status === 'failed' ? 'bg-rose-50 border-rose-600 text-rose-700 ring-2 ring-rose-600/10' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
              >
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                Inspection Failed
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-4 border-t border-slate-100 flex gap-3">
            <button 
              type="button" 
              @click="showQcModal = false"
              class="flex-1 py-3 border border-slate-300 rounded-2xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="saving"
              class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-2xl font-bold text-xs shadow-md"
            >
              Submit QC Report
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- 4. Delivery Handover Modal -->
    <div v-if="showDeliveryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm animate-in fade-in duration-200">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-black text-slate-950">Vehicle Delivery Handover</h3>
            <p class="text-xs text-slate-500 mt-0.5">JC #{{ selectedJobCard?.id }} - {{ selectedJobCard?.vehicle?.make }} {{ selectedJobCard?.vehicle?.model }}</p>
          </div>
          <button @click="showDeliveryModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
            ✕
          </button>
        </div>

        <!-- Body -->
        <form @submit.prevent="submitDeliveryHandover" class="flex-1 overflow-y-auto p-6 space-y-6">
          <!-- Delivered To -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Delivered To (Collector Name) *</label>
            <input 
              v-model="deliveryForm.delivered_to" 
              type="text" 
              required 
              placeholder="e.g. Customer Name or Authorised Agent" 
              class="w-full text-xs rounded-2xl border-slate-200 p-3 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>

          <!-- Interactive Signature Canvas -->
          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Customer Signature Sign-off *</label>
              <button 
                type="button" 
                @click="clearSignature"
                class="text-[10px] font-bold text-rose-500 hover:text-rose-600 uppercase"
              >
                Clear Pad
              </button>
            </div>
            <div class="border border-slate-200 rounded-2xl bg-slate-50 overflow-hidden shadow-inner flex items-center justify-center">
              <canvas 
                ref="sigCanvas" 
                width="400" 
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
            <p class="text-[10px] text-slate-400 italic">Sign on the touch-sensitive pad above using your mouse or touch device.</p>
          </div>

          <!-- Handover Notes -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Handover & Delivery Notes</label>
            <textarea 
              v-model="deliveryForm.notes" 
              rows="3" 
              placeholder="Document physical details, returned stock parts handover, or final customer feedback..."
              class="w-full text-xs rounded-2xl border-slate-200 p-3 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            ></textarea>
          </div>

          <!-- Mock Delivery Photos -->
          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Delivery Photograph</label>
            <div class="flex items-center gap-3">
              <div 
                class="w-20 h-20 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center text-slate-300 hover:text-slate-500 hover:border-slate-300 cursor-pointer transition"
                @click="mockPhotoUpload"
              >
                <span class="text-xl font-bold">+</span>
              </div>
              <div v-if="deliveryForm.delivery_photos.length > 0" class="flex gap-2">
                <div 
                  v-for="(photo, idx) in deliveryForm.delivery_photos" 
                  :key="idx"
                  class="w-20 h-20 rounded-2xl bg-cover bg-center border border-slate-200 shadow-sm relative group"
                  :style="{ backgroundImage: `url(${photo})` }"
                >
                  <button 
                    type="button"
                    @click="deliveryForm.delivery_photos.splice(idx, 1)"
                    class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] shadow border border-white"
                  >
                    ✕
                  </button>
                </div>
              </div>
            </div>
            <p class="text-[10px] text-slate-400">Optional: Log photo evidence of the vehicle leaving the service bay.</p>
          </div>

          <!-- Submit Button -->
          <div class="pt-4 border-t border-slate-100 flex gap-3">
            <button 
              type="button" 
              @click="showDeliveryModal = false"
              class="flex-1 py-3 border border-slate-300 rounded-2xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="saving"
              class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-2xl font-bold text-xs shadow-md"
            >
              Complete Delivery
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();

const activeTab = ref('qc');
const loading = ref(false);
const saving = ref(false);

const pendingQcList = ref([]);
const readyDeliveryList = ref([]);

// Modals toggles
const showQcModal = ref(false);
const showDeliveryModal = ref(false);

// Active items
const selectedWorkOrder = ref(null);
const selectedJobCard = ref(null);

// Forms
const qcForm = reactive({
  work_order_id: null,
  status: 'passed',
  checklist: {
    brakes: true,
    steering: true,
    lights: true,
    suspension: true,
    cleanliness: true
  },
  road_test_performed: true,
  road_test_notes: ''
});

const deliveryForm = reactive({
  job_card_id: null,
  delivered_to: '',
  signature_path: '',
  delivery_photos: [],
  notes: ''
});

// Signature canvas drawing states
const sigCanvas = ref(null);
let isDrawing = false;
let ctx = null;

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
  saveSignatureBase64();
};

// Touch events for signature
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

const initCtx = () => {
  if (!ctx && sigCanvas.value) {
    ctx = sigCanvas.value.getContext('2d');
    ctx.strokeStyle = '#1e1b4b'; // Deep Navy color for signature ink
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
  }
};

const clearSignature = () => {
  if (sigCanvas.value) {
    const canvas = sigCanvas.value;
    ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    deliveryForm.signature_path = '';
  }
};

const saveSignatureBase64 = () => {
  if (sigCanvas.value) {
    deliveryForm.signature_path = sigCanvas.value.toDataURL('image/png');
  }
};

const mockPhotoUpload = () => {
  // Add a placeholder/mock delivery photo to show upload safety
  const mockPhotos = [
    'https://images.unsplash.com/photo-1542282088-fe8426682b8f?w=400',
    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=400'
  ];
  deliveryForm.delivery_photos.push(mockPhotos[Math.floor(Math.random() * mockPhotos.length)]);
  toast.success('Photograph uploaded successfully');
};

// Fetch data queues
const fetchQueues = async () => {
  loading.value = true;
  try {
    // 1. Fetch active/in_progress work orders for QC Queue
    const qcResponse = await api.get('/work-orders');
    // Filter work orders that are 'in_progress' or 'completed' waiting for final verification
    pendingQcList.value = (qcResponse.data?.data || qcResponse.data || []).filter(
      wo => wo.status === 'in_progress' || wo.status === 'completed'
    );

    // 2. Fetch job cards that are in ready_for_delivery state
    const jcResponse = await api.get('/job-cards');
    readyDeliveryList.value = (jcResponse.data?.data || jcResponse.data || []).filter(
      jc => jc.service_status === 'completed'
    );
  } catch (error) {
    console.error('Failed to query QC/Delivery queues', error);
  } finally {
    loading.value = false;
  }
};

// Open Modals
const openQcModal = (wo) => {
  selectedWorkOrder.value = wo;
  qcForm.work_order_id = wo.id;
  qcForm.status = 'passed';
  qcForm.road_test_performed = true;
  qcForm.road_test_notes = '';
  showQcModal.value = true;
};

const openDeliveryModal = (jc) => {
  selectedJobCard.value = jc;
  deliveryForm.job_card_id = jc.id;
  deliveryForm.delivered_to = jc.customer?.name || '';
  deliveryForm.signature_path = '';
  deliveryForm.delivery_photos = [];
  deliveryForm.notes = '';
  showDeliveryModal.value = true;
  setTimeout(clearSignature, 100);
};

// Submissions
const submitQcReport = async () => {
  saving.value = true;
  try {
    await api.post('/quality-control', { ...qcForm });
    toast.success('Quality Control report logged successfully');
    showQcModal.value = false;
    fetchQueues();
  } catch (error) {
    console.error('Failed to submit QC', error);
  } finally {
    saving.value = false;
  }
};

const submitDeliveryHandover = async () => {
  if (!deliveryForm.signature_path) {
    toast.warning('Please obtain a customer signature before finalizing handover.');
    return;
  }
  saving.value = true;
  try {
    await api.post('/vehicle-delivery', { ...deliveryForm });
    toast.success('Vehicle delivered. Job Card closed.');
    showDeliveryModal.value = false;
    fetchQueues();
  } catch (error) {
    console.error('Failed to submit delivery', error);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchQueues();
});
</script>

<style scoped>
/* Scoped styles */
canvas {
  touch-action: none;
}
</style>
