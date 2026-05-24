<script setup>
import { ref } from 'vue';

const payslip = ref({
  employeeName: 'Rahim Uddin',
  role: 'Mechanic',
  month: 'October 2023',
  basic: 15000,
  houseRent: 2000,
  medical: 500,
  transport: 500,
  tax: 0,
  providentFund: 500,
  absentDeduction: 0,
});

const print = () => window.print();
</script>

<template>
  <div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex justify-between items-center print:hidden">
      <h1 class="text-2xl font-bold text-gray-900">Payslip Preview</h1>
      <button @click="print" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Print Payslip
      </button>
    </div>

    <div class="bg-white p-10 border border-gray-200 shadow-lg rounded-xl">
      <!-- Header -->
      <div class="border-b-2 border-gray-800 pb-6 mb-6 flex justify-between items-start">
        <div>
          <h2 class="text-3xl font-black tracking-tight text-gray-900">MAMUN AUTOMOBILES</h2>
          <p class="text-gray-600 mt-1">123 Auto Care Street, Dhaka, Bangladesh</p>
        </div>
        <div class="text-right">
          <h3 class="text-2xl font-bold text-blue-800 uppercase tracking-widest">Payslip</h3>
          <p class="text-gray-600 mt-1 font-medium">{{ payslip.month }}</p>
        </div>
      </div>

      <!-- Employee Info -->
      <div class="flex justify-between mb-8">
        <div>
          <p class="text-sm text-gray-500 uppercase font-semibold">Employee Name</p>
          <p class="text-lg font-bold text-gray-900">{{ payslip.employeeName }}</p>
          <p class="text-sm text-gray-700">{{ payslip.role }}</p>
        </div>
        <div class="w-24 h-24 border-2 border-gray-200 p-1 flex items-center justify-center bg-gray-50">
          <!-- Placeholder for QR Code -->
          <svg class="w-16 h-16 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v3h-3v-3zm-3 3h3v3h-3v-3zm3 3h3v2h-3v-2zm-3-6h3v3h-3v-3z"/></svg>
        </div>
      </div>

      <!-- Salary Details -->
      <div class="grid grid-cols-2 gap-12">
        <!-- Earnings -->
        <div>
          <h4 class="text-lg font-bold border-b border-gray-300 pb-2 mb-4 text-green-700">Earnings</h4>
          <div class="space-y-3">
            <div class="flex justify-between text-gray-800"><span class="font-medium">Basic Salary</span><span>৳ {{ payslip.basic }}</span></div>
            <div class="flex justify-between text-gray-800"><span class="font-medium">House Rent</span><span>৳ {{ payslip.houseRent }}</span></div>
            <div class="flex justify-between text-gray-800"><span class="font-medium">Medical</span><span>৳ {{ payslip.medical }}</span></div>
            <div class="flex justify-between text-gray-800"><span class="font-medium">Transport</span><span>৳ {{ payslip.transport }}</span></div>
          </div>
          <div class="flex justify-between font-bold text-lg mt-6 pt-3 border-t border-gray-300">
            <span>Total Earnings</span>
            <span>৳ {{ payslip.basic + payslip.houseRent + payslip.medical + payslip.transport }}</span>
          </div>
        </div>

        <!-- Deductions -->
        <div>
          <h4 class="text-lg font-bold border-b border-gray-300 pb-2 mb-4 text-red-700">Deductions</h4>
          <div class="space-y-3">
            <div class="flex justify-between text-gray-800"><span class="font-medium">Tax</span><span>৳ {{ payslip.tax }}</span></div>
            <div class="flex justify-between text-gray-800"><span class="font-medium">Provident Fund</span><span>৳ {{ payslip.providentFund }}</span></div>
            <div class="flex justify-between text-gray-800"><span class="font-medium">Absent Deduction</span><span>৳ {{ payslip.absentDeduction }}</span></div>
          </div>
          <div class="flex justify-between font-bold text-lg mt-6 pt-3 border-t border-gray-300">
            <span>Total Deductions</span>
            <span>৳ {{ payslip.tax + payslip.providentFund + payslip.absentDeduction }}</span>
          </div>
        </div>
      </div>

      <!-- Net Pay -->
      <div class="mt-10 bg-gray-50 p-6 rounded-lg border border-gray-200 flex justify-between items-center">
        <div>
          <p class="text-sm text-gray-500 uppercase font-semibold">Net Salary</p>
          <p class="text-xs text-gray-400 mt-1">Total Earnings - Total Deductions</p>
        </div>
        <p class="text-4xl font-black text-gray-900 tracking-tight">৳ {{ (payslip.basic + payslip.houseRent + payslip.medical + payslip.transport) - (payslip.tax + payslip.providentFund + payslip.absentDeduction) }}</p>
      </div>

      <!-- Signatures -->
      <div class="mt-16 flex justify-between pt-10 px-4">
        <div class="text-center">
          <div class="w-40 border-t border-gray-400"></div>
          <p class="mt-2 text-sm text-gray-600 font-medium">Employer Signature</p>
        </div>
        <div class="text-center">
          <div class="w-40 border-t border-gray-400"></div>
          <p class="mt-2 text-sm text-gray-600 font-medium">Employee Signature</p>
        </div>
      </div>
    </div>
  </div>
</template>
