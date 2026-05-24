<template>
  <div class="saas-dashboard p-6 bg-gray-900 text-white min-h-screen">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center">
          <svg class="w-8 h-8 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          SaaS Admin Center
        </h1>
        <p class="text-gray-400 mt-1">Manage tenants, subscriptions, and platform revenue</p>
      </div>
      <div class="flex space-x-3">
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
          Provision New Tenant
        </button>
      </div>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-medium mb-1 uppercase">Total Tenants</h3>
        <p class="text-3xl font-bold text-white">124</p>
        <p class="text-xs text-green-400 mt-2">+12 this month</p>
      </div>
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-medium mb-1 uppercase">Active Subscriptions</h3>
        <p class="text-3xl font-bold text-blue-400">98</p>
        <p class="text-xs text-blue-400 mt-2">79% conversion rate</p>
      </div>
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-medium mb-1 uppercase">MRR (Monthly Revenue)</h3>
        <p class="text-3xl font-bold text-green-400">$12,450</p>
        <p class="text-xs text-green-400 mt-2">+5.2% growth</p>
      </div>
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg relative overflow-hidden">
        <h3 class="text-gray-400 text-sm font-medium mb-1 uppercase">Pending Renewals</h3>
        <p class="text-3xl font-bold text-yellow-400">15</p>
        <p class="text-xs text-yellow-500 mt-2">Expiring next 7 days</p>
        <div class="absolute bottom-0 right-0 w-16 h-16 bg-yellow-500 opacity-10 rounded-tl-full"></div>
      </div>
    </div>

    <!-- Tenant List -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-lg">
      <div class="p-5 border-b border-gray-700 flex justify-between items-center bg-gray-850">
        <h2 class="text-lg font-bold text-white">Recent Tenants</h2>
        <input type="text" placeholder="Search domain or name..." class="bg-gray-900 border border-gray-600 text-sm rounded px-3 py-1.5 focus:outline-none focus:border-blue-500 text-white placeholder-gray-500">
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-900/50 text-gray-400 uppercase text-xs">
            <tr>
              <th class="p-4 font-semibold">Tenant</th>
              <th class="p-4 font-semibold">Domain</th>
              <th class="p-4 font-semibold">Plan</th>
              <th class="p-4 font-semibold">Status</th>
              <th class="p-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700 text-gray-300">
            <tr class="hover:bg-gray-750 transition">
              <td class="p-4 font-medium text-white">Auto Fixers Ltd</td>
              <td class="p-4 text-blue-400">autofix.mamunerp.com</td>
              <td class="p-4"><span class="bg-indigo-900 text-indigo-300 px-2 py-0.5 rounded text-xs font-medium border border-indigo-700">Enterprise</span></td>
              <td class="p-4"><span class="flex items-center text-green-400 text-xs font-bold"><span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> Active</span></td>
              <td class="p-4 text-right space-x-3">
                <button class="text-gray-400 hover:text-white transition">Manage</button>
                <button class="text-red-400 hover:text-red-300 transition">Suspend</button>
              </td>
            </tr>
            <tr class="hover:bg-gray-750 transition">
              <td class="p-4 font-medium text-white">Speedy Garage</td>
              <td class="p-4 text-blue-400">speedy.mamunerp.com</td>
              <td class="p-4"><span class="bg-gray-700 text-gray-300 px-2 py-0.5 rounded text-xs font-medium border border-gray-600">Starter</span></td>
              <td class="p-4"><span class="flex items-center text-yellow-400 text-xs font-bold"><span class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></span> Trial (3 days left)</span></td>
              <td class="p-4 text-right space-x-3">
                <button class="text-gray-400 hover:text-white transition">Manage</button>
                <button class="text-red-400 hover:text-red-300 transition">Suspend</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
// Dashboard logic
</script>
