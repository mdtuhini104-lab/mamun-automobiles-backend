<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Service Appointments</h1>
      <p class="text-sm text-slate-500 mt-1">Manage workshop bookings, capacity, and mechanic schedules.</p>
    </div>

    <div v-if="crmStore.loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>
    <div v-else class="bg-white border rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Date & Time</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Customer / Vehicle</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Mechanic</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Service Type</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="apt in crmStore.appointments" :key="apt.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-slate-900">{{ apt.appointment_date }}</div>
                <div class="text-xs text-slate-500">{{ apt.appointment_time }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-slate-900">{{ apt.customer?.name }}</div>
                <div class="text-xs text-slate-500">{{ apt.customer?.phone }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900 font-medium">{{ apt.mechanic?.name || 'Unassigned' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900">{{ apt.service_type }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium rounded-md"
                  :class="{
                    'bg-amber-100 text-amber-800': apt.status === 'pending',
                    'bg-blue-100 text-blue-800': apt.status === 'confirmed',
                    'bg-emerald-100 text-emerald-800': apt.status === 'completed',
                    'bg-rose-100 text-rose-800': apt.status === 'cancelled'
                  }">
                  {{ apt.status.toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <button v-if="apt.status === 'pending'" @click="updateStatus(apt.id, 'confirmed')" class="text-blue-600 hover:text-blue-900">Confirm</button>
                <button v-if="apt.status === 'confirmed'" @click="updateStatus(apt.id, 'completed')" class="text-emerald-600 hover:text-emerald-900">Complete</button>
                <button v-if="apt.status !== 'completed' && apt.status !== 'cancelled'" @click="updateStatus(apt.id, 'cancelled')" class="text-rose-600 hover:text-rose-900">Cancel</button>
              </td>
            </tr>
            <tr v-if="!crmStore.appointments.length">
              <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">No appointments booked.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useCrmStore } from '../../stores/crm';

const crmStore = useCrmStore();

onMounted(() => {
  crmStore.fetchAppointments();
});

const updateStatus = async (id, status) => {
  if (confirm("Are you sure you want to mark this appointment as " + status + "?")) {
    await crmStore.updateAppointmentStatus(id, status);
  }
};
</script>

