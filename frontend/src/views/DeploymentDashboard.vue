<template>
  <div class="deployment-dashboard p-6 bg-gray-900 text-white min-h-screen">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center">
          <svg class="w-8 h-8 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          Deployment & CI/CD
        </h1>
        <p class="text-gray-400 mt-1">Manage zero-downtime deployments, docker containers, and SSL</p>
      </div>
      <div class="flex space-x-3">
        <button class="bg-gray-800 border border-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow transition">
          Rollback Release
        </button>
        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
          Deploy to Production
        </button>
      </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-medium mb-1 uppercase">Docker Status</h3>
        <p class="text-3xl font-bold text-green-400">All Healthy</p>
        <p class="text-xs text-gray-500 mt-2">5/5 containers running</p>
      </div>
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-medium mb-1 uppercase">Current Release</h3>
        <p class="text-3xl font-bold text-blue-400">v2.4.1</p>
        <p class="text-xs text-gray-500 mt-2">Deployed 2 hours ago</p>
      </div>
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-medium mb-1 uppercase">SSL Status</h3>
        <p class="text-3xl font-bold text-green-400">Secured</p>
        <p class="text-xs text-gray-500 mt-2">Auto-renews in 45 days</p>
      </div>
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-medium mb-1 uppercase">Queue Workers</h3>
        <p class="text-3xl font-bold text-green-400">Active</p>
        <p class="text-xs text-gray-500 mt-2">Horizon monitoring enabled</p>
      </div>
    </div>

    <!-- Deployment History -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-lg">
      <div class="p-5 border-b border-gray-700 bg-gray-850">
        <h2 class="text-lg font-bold text-white">Pipeline History</h2>
      </div>
      <div class="p-0">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-900/50 text-gray-400 uppercase text-xs">
            <tr>
              <th class="p-4 font-semibold">Release</th>
              <th class="p-4 font-semibold">Commit</th>
              <th class="p-4 font-semibold">Triggered By</th>
              <th class="p-4 font-semibold">Status</th>
              <th class="p-4 font-semibold text-right">Time</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700 text-gray-300">
            <tr class="hover:bg-gray-750 transition">
              <td class="p-4 font-medium text-white">v2.4.1</td>
              <td class="p-4 text-gray-400">Fix invoice PDF layout (#a1b2c3)</td>
              <td class="p-4">GitHub Actions</td>
              <td class="p-4"><span class="bg-green-900 text-green-300 px-2 py-0.5 rounded text-xs font-medium border border-green-700">Success</span></td>
              <td class="p-4 text-right text-gray-500">2 hrs ago</td>
            </tr>
            <tr class="hover:bg-gray-750 transition">
              <td class="p-4 font-medium text-white">v2.4.0</td>
              <td class="p-4 text-gray-400">SaaS Multi-tenant implementation (#d4e5f6)</td>
              <td class="p-4">GitHub Actions</td>
              <td class="p-4"><span class="bg-green-900 text-green-300 px-2 py-0.5 rounded text-xs font-medium border border-green-700">Success</span></td>
              <td class="p-4 text-right text-gray-500">Yesterday</td>
            </tr>
            <tr class="hover:bg-gray-750 transition">
              <td class="p-4 font-medium text-white">v2.3.9</td>
              <td class="p-4 text-gray-400">Mobile API update (#f1g2h3)</td>
              <td class="p-4">Manual</td>
              <td class="p-4"><span class="bg-red-900 text-red-300 px-2 py-0.5 rounded text-xs font-medium border border-red-700">Failed</span></td>
              <td class="p-4 text-right text-gray-500">3 days ago</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
