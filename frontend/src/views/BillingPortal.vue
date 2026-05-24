<template>
  <div class="billing-portal p-6 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Subscription & Billing</h1>

      <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-blue-50 to-indigo-50">
          <div>
            <h2 class="text-xl font-bold text-gray-800">Enterprise Plan</h2>
            <p class="text-sm text-gray-500 mt-1">Billed annually ($999/yr)</p>
          </div>
          <div class="text-right">
            <span class="inline-flex items-center bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded-full mb-1">
              <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full"></span> Active
            </span>
            <p class="text-sm font-medium text-gray-600">Renews on Oct 15, 2024</p>
          </div>
        </div>
        <div class="p-6 flex space-x-4">
          <button class="bg-blue-600 text-white px-5 py-2 rounded font-medium hover:bg-blue-700 transition">Upgrade Plan</button>
          <button class="bg-white border border-gray-300 text-gray-700 px-5 py-2 rounded font-medium hover:bg-gray-50 transition">Cancel Subscription</button>
        </div>
      </div>

      <h2 class="text-lg font-bold text-gray-800 mb-4">Billing History</h2>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="p-4 font-semibold">Date</th>
              <th class="p-4 font-semibold">Description</th>
              <th class="p-4 font-semibold">Amount</th>
              <th class="p-4 font-semibold">Status</th>
              <th class="p-4 font-semibold text-right">Invoice</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr class="hover:bg-gray-50">
              <td class="p-4 text-gray-600">Oct 15, 2023</td>
              <td class="p-4 font-medium text-gray-800">Enterprise Plan - Annual</td>
              <td class="p-4 text-gray-800">$999.00</td>
              <td class="p-4"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">Paid</span></td>
              <td class="p-4 text-right">
                <button class="text-blue-600 hover:text-blue-800 flex items-center justify-end ml-auto">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> PDF
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
// Billing logic
</script>
