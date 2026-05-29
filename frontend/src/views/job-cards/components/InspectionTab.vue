<template>
  <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
      <h3 class="text-lg leading-6 font-medium text-slate-900">Technician Intake & Inspection</h3>
      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
        AI-Ready Input
      </span>
    </div>
    <form @submit.prevent="saveInspection" class="p-6 space-y-6">
      <!-- 2 Column Intake Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Odometer Reading (KM) *</label>
          <input
            v-model.number="form.odometer_reading"
            type="number"
            required
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="e.g. 45000"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Fuel Level *</label>
          <select
            v-model="form.fuel_level"
            required
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="Empty">Empty</option>
            <option value="1/4">1/4 tank</option>
            <option value="1/2">1/2 tank</option>
            <option value="3/4">3/4 tank</option>
            <option value="Full">Full tank</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Emergency Level *</label>
          <select
            v-model="form.emergency_level"
            required
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="low">Low (Standard Maintenance)</option>
            <option value="medium">Medium (Repair Required)</option>
            <option value="high">High (Major Faults)</option>
            <option value="critical">Critical (Safety Hazard)</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Priority Level *</label>
          <select
            v-model="form.priority_level"
            required
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="normal">Normal Priority</option>
            <option value="high">High Priority</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Expected Delivery Date *</label>
          <input
            v-model="form.expected_delivery_date"
            type="datetime-local"
            required
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <!-- Text Inputs -->
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Diagnosis / Findings *</label>
          <textarea
            v-model="form.diagnosis"
            rows="3"
            required
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="Record all fault findings, diagnostic scan results, and engine/electrical codes..."
          ></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Inspection Notes</label>
          <textarea
            v-model="form.inspection_notes"
            rows="2"
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="General observations, workshop notes..."
          ></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2 text-red-600 font-bold">Safety Warnings / Critical Alerts</label>
          <textarea
            v-model="form.safety_warnings"
            rows="2"
            class="block w-full rounded-lg border-red-300 bg-red-50 text-red-900 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
            placeholder="Alert service advisor or customer about major hazards e.g. bald tires, leaking fuel, total brake wear..."
          ></textarea>
        </div>
      </div>

      <!-- Attachment Upload Mockup -->
      <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
        <h4 class="text-sm font-medium text-slate-800 mb-3 flex items-center">
          <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
          </svg>
          Documents, Image Uploads, & Voice Notes
        </h4>
        <p class="text-xs text-slate-500 mb-4">Upload mechanical diagrams, visual pictures of damages, and voice recording notes for the files index.</p>
        <div class="flex flex-wrap gap-4">
          <button type="button" class="inline-flex items-center px-3 py-1.5 border border-slate-300 rounded text-xs font-medium text-slate-700 bg-white hover:bg-slate-50">
            Record Voice Note
          </button>
          <button type="button" class="inline-flex items-center px-3 py-1.5 border border-slate-300 rounded text-xs font-medium text-slate-700 bg-white hover:bg-slate-50">
            Upload Images
          </button>
          <button type="button" class="inline-flex items-center px-3 py-1.5 border border-slate-300 rounded text-xs font-medium text-slate-700 bg-white hover:bg-slate-50">
            Attach PDF
          </button>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end pt-4 border-t border-slate-100">
        <button
          type="submit"
          :disabled="saving"
          class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
          {{ saving ? 'Saving Inspection Records...' : 'Save Inspection Findings' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';
import { useToastStore } from '../../../stores/toast';

const props = defineProps({
  jobCard: {
    type: Object,
    required: true,
  }
});

const emit = defineEmits(['updated']);

const toast = useToastStore();
const saving = ref(false);

const form = ref({
  odometer_reading: '',
  fuel_level: '1/2',
  emergency_level: 'medium',
  priority_level: 'normal',
  expected_delivery_date: '',
  diagnosis: '',
  inspection_notes: '',
  safety_warnings: '',
});

onMounted(() => {
  if (props.jobCard) {
    form.value.odometer_reading = props.jobCard.odometer_reading || '';
    form.value.fuel_level = props.jobCard.fuel_level || '1/2';
    form.value.emergency_level = props.jobCard.emergency_level || 'medium';
    form.value.priority_level = props.jobCard.priority_level || 'normal';
    form.value.diagnosis = props.jobCard.diagnosis || '';
    form.value.inspection_notes = props.jobCard.inspection_notes || '';
    form.value.safety_warnings = props.jobCard.safety_warnings || '';
    
    if (props.jobCard.expected_delivery_date) {
      const date = new Date(props.jobCard.expected_delivery_date);
      // Format to YYYY-MM-DDThh:mm for datetime-local input
      form.value.expected_delivery_date = date.toISOString().slice(0, 16);
    }
  }
});

const saveInspection = async () => {
  saving.value = ref(true);
  try {
    await api.put(`/job-cards/${props.jobCard.id}`, {
      ...props.jobCard,
      ...form.value,
      service_status: props.jobCard.service_status // preserve legacy status
    });
    toast.success('Inspection findings saved and dynamic history logged successfully!');
    emit('updated');
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save inspection findings');
  } finally {
    saving.value = false;
  }
};
</script>
