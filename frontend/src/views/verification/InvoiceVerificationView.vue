<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-xl">
      <div v-if="loading" class="text-center space-y-4">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-indigo-600 mx-auto"></div>
        <h2 class="text-xl font-semibold text-gray-700">Verifying Invoice...</h2>
        <p class="text-sm text-gray-500">Please wait while we check the blockchain/secure hash.</p>
      </div>

      <div v-else-if="verificationResult && verificationResult.success" class="space-y-6">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-4">
            <svg class="h-16 w-16 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Valid Invoice</h2>
          <p class="text-sm text-green-600 font-semibold mt-1">✓ Secure QR Matched Successfully</p>
        </div>

        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm space-y-3">
          <div class="flex justify-between border-b pb-2">
            <span class="text-gray-500 font-medium">Invoice Number:</span>
            <span class="font-bold text-gray-900">{{ verificationResult.data.invoice_number }}</span>
          </div>
          <div class="flex justify-between border-b pb-2">
            <span class="text-gray-500 font-medium">Customer:</span>
            <span class="font-bold text-gray-900">{{ verificationResult.data.customer_name }}</span>
          </div>
          <div class="flex justify-between border-b pb-2">
            <span class="text-gray-500 font-medium">Vehicle No:</span>
            <span class="font-bold text-gray-900">{{ verificationResult.data.vehicle_number }}</span>
          </div>
          <div class="flex justify-between border-b pb-2">
            <span class="text-gray-500 font-medium">Date:</span>
            <span class="font-bold text-gray-900">{{ verificationResult.data.date }}</span>
          </div>
          <div class="flex justify-between pt-2">
            <span class="text-gray-500 font-medium">Amount:</span>
            <span class="font-bold text-xl text-indigo-600">৳{{ verificationResult.data.grand_total }}</span>
          </div>
          <div class="flex justify-between pt-2 mt-2 bg-gray-100 p-2 rounded">
            <span class="text-gray-600 font-medium">Status:</span>
            <span 
              class="font-bold uppercase"
              :class="{
                'text-green-600': verificationResult.data.payment_status === 'paid',
                'text-red-600': verificationResult.data.payment_status === 'unpaid',
                'text-yellow-600': verificationResult.data.payment_status === 'partial'
              }"
            >{{ verificationResult.data.payment_status }}</span>
          </div>
        </div>

        <div class="text-center">
           <img src="/logo-dark.png" alt="Company Logo" class="h-10 mx-auto opacity-70 grayscale">
           <p class="text-xs text-gray-400 mt-2">Mamun Automobiles ERP Secured Verification</p>
        </div>
      </div>

      <div v-else class="space-y-6">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100 mb-4">
            <svg class="h-16 w-16 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <h2 class="text-3xl font-extrabold text-red-600 tracking-tight">Invoice Not Valid</h2>
          <p class="text-md text-red-500 mt-2 font-medium bg-red-50 p-2 rounded">⚠ Possible tampering detected or invoice not found.</p>
        </div>
        
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-sm text-red-700">
          <p>The scanned QR code does not match any valid records in our system. Please ensure the document is authentic and contact support if you believe this is an error.</p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const loading = ref(true);
const verificationResult = ref(null);

onMounted(async () => {
  const invoiceNo = route.params.invoice_no;
  const token = route.query.token;

  if (!token) {
    loading.value = false;
    verificationResult.value = { success: false };
    return;
  }

  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/verify/invoice/${invoiceNo}`, {
      params: { token }
    });
    verificationResult.value = response.data;
  } catch (error) {
    console.error('Verification failed', error);
    verificationResult.value = { success: false, message: error.response?.data?.message || 'Verification Error' };
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
</style>
