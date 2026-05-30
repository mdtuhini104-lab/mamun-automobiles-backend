<template>
  <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
      <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-950">Dashboard</h1>
        <p class="mt-1 text-sm text-slate-500 font-medium">Real-time overview of Mamun Automobiles</p>
      </div>
      <div class="flex items-center space-x-3 self-start sm:self-center">
        <!-- Live status badge -->
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/50">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          Live Sync
        </span>
        <button 
          @click="fetchData" 
          class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 transition-colors shadow-sm cursor-pointer"
          :disabled="loading"
        >
          <svg class="w-3.5 h-3.5 mr-1" :class="{'animate-spin': loading}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 flex items-start space-x-3">
      <svg class="w-5 h-5 text-rose-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <div>
        <h3 class="font-bold text-sm">System Error</h3>
        <p class="text-xs text-rose-700 mt-0.5">{{ error }}</p>
      </div>
    </div>

    <!-- Statistics Grid (Skeleton / Content) -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <!-- 1. Today Sales Card -->
      <template v-if="authStore.hasPermission('invoices.view')">
        <div v-if="loading" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm animate-pulse flex flex-col justify-between h-36">
          <div class="flex justify-between items-start">
            <div class="space-y-2">
              <div class="h-3 bg-slate-200 rounded w-20"></div>
              <div class="h-7 bg-slate-200 rounded w-28"></div>
            </div>
            <div class="w-10 h-10 bg-slate-200 rounded-xl"></div>
          </div>
          <div class="h-6 bg-slate-150 rounded w-full"></div>
        </div>
        <div v-else class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-36 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Today Sales</p>
              <p class="mt-1 text-2xl font-extrabold text-slate-900">${{ todaySales.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
            </div>
            <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <!-- SVG Sparkline Trend -->
          <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50">
            <span class="text-xs font-semibold" :class="todaySales > 0 ? 'text-emerald-600' : 'text-slate-400'">
              {{ todaySales > 0 ? 'Active momentum' : 'No sales today yet' }}
            </span>
            <svg class="w-20 h-6 text-emerald-500" stroke-width="2" fill="none" viewBox="0 0 120 30">
              <polyline stroke="currentColor" :points="invoicesSparklinePoints" stroke-linecap="round" stroke-linejoin="round"></polyline>
            </svg>
          </div>
        </div>
      </template>

      <!-- 2. Monthly Revenue Card -->
      <template v-if="authStore.hasPermission('invoices.view')">
        <div v-if="loading" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm animate-pulse flex flex-col justify-between h-36">
          <div class="flex justify-between items-start">
            <div class="space-y-2">
              <div class="h-3 bg-slate-200 rounded w-24"></div>
              <div class="h-7 bg-slate-200 rounded w-24"></div>
            </div>
            <div class="w-10 h-10 bg-slate-200 rounded-xl"></div>
          </div>
          <div class="h-6 bg-slate-150 rounded w-full"></div>
        </div>
        <div v-else class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-36 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Monthly Revenue</p>
              <p class="mt-1 text-2xl font-extrabold text-slate-900">${{ monthlyRevenue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
            </div>
            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          <!-- SVG Sparkline Trend -->
          <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50">
            <span class="text-xs font-semibold text-blue-600">Current Month</span>
            <svg class="w-20 h-6 text-blue-500" stroke-width="2" fill="none" viewBox="0 0 120 30">
              <polyline stroke="currentColor" :points="monthlySalesSparklinePoints" stroke-linecap="round" stroke-linejoin="round"></polyline>
            </svg>
          </div>
        </div>
      </template>

      <!-- 3. Pending Invoices Card -->
      <template v-if="authStore.hasPermission('invoices.view')">
        <div v-if="loading" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm animate-pulse flex flex-col justify-between h-36">
          <div class="flex justify-between items-start">
            <div class="space-y-2">
              <div class="h-3 bg-slate-200 rounded w-28"></div>
              <div class="h-7 bg-slate-200 rounded w-16"></div>
            </div>
            <div class="w-10 h-10 bg-slate-200 rounded-xl"></div>
          </div>
          <div class="h-6 bg-slate-150 rounded w-full"></div>
        </div>
        <div v-else class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-36 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Invoices</p>
              <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ pendingInvoicesCount }} Invoices</p>
            </div>
            <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50">
            <span class="text-xs font-bold text-slate-500">
              Due Total: <span class="text-amber-600 font-extrabold">${{ totalDueAmount.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
            </span>
            <!-- Dynamic pure CSS dot warning indicator -->
            <span v-if="pendingInvoicesCount > 0" class="flex h-2 w-2 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
            </span>
          </div>
        </div>
      </template>

      <!-- 4. Low Stock Items Card -->
      <template v-if="authStore.hasPermission('inventory.view')">
        <div v-if="loading" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm animate-pulse flex flex-col justify-between h-36">
          <div class="flex justify-between items-start">
            <div class="space-y-2">
              <div class="h-3 bg-slate-200 rounded w-24"></div>
              <div class="h-7 bg-slate-200 rounded w-16"></div>
            </div>
            <div class="w-10 h-10 bg-slate-200 rounded-xl"></div>
          </div>
          <div class="h-6 bg-slate-150 rounded w-full"></div>
        </div>
        <div v-else class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-36 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Low Stock Items</p>
              <p class="mt-1 text-2xl font-extrabold text-slate-900" :class="lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900'">{{ lowStockCount }} Items</p>
            </div>
            <div class="p-2.5 rounded-xl transition-colors" :class="lowStockCount > 0 ? 'bg-rose-50 text-rose-600 group-hover:bg-rose-100' : 'bg-slate-50 text-slate-400 group-hover:bg-slate-100'">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
          <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50">
            <span class="text-xs font-semibold" :class="lowStockCount > 0 ? 'text-rose-600' : 'text-slate-400'">
              {{ lowStockCount > 0 ? 'Reorder immediately' : 'Inventory stable' }}
            </span>
            <span v-if="lowStockCount > 0" class="flex h-2 w-2 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
            </span>
          </div>
        </div>
      </template>

      <!-- 5. Total Expenses Card -->
      <template v-if="authStore.hasPermission('accounts.view')">
        <div v-if="loading" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm animate-pulse flex flex-col justify-between h-36">
          <div class="flex justify-between items-start">
            <div class="space-y-2">
              <div class="h-3 bg-slate-200 rounded w-24"></div>
              <div class="h-7 bg-slate-200 rounded w-16"></div>
            </div>
            <div class="w-10 h-10 bg-slate-200 rounded-xl"></div>
          </div>
          <div class="h-6 bg-slate-150 rounded w-full"></div>
        </div>
        <div v-else class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-36 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Expenses</p>
              <p class="mt-1 text-2xl font-extrabold text-slate-900">${{ totalExpenses.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
            </div>
            <div class="p-2.5 bg-red-50 text-red-600 rounded-xl group-hover:bg-red-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50">
            <span class="text-xs font-semibold text-slate-400">All Time Expenses</span>
          </div>
        </div>
      </template>

      <!-- 6. Net Profit Card -->
      <template v-if="authStore.hasPermission('accounts.view')">
        <div v-if="loading" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm animate-pulse flex flex-col justify-between h-36">
          <div class="flex justify-between items-start">
            <div class="space-y-2">
              <div class="h-3 bg-slate-200 rounded w-24"></div>
              <div class="h-7 bg-slate-200 rounded w-16"></div>
            </div>
            <div class="w-10 h-10 bg-slate-200 rounded-xl"></div>
          </div>
          <div class="h-6 bg-slate-150 rounded w-full"></div>
        </div>
        <div v-else class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-36 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Net Profit</p>
              <p class="mt-1 text-2xl font-extrabold" :class="netProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">${{ netProfit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
            </div>
            <div class="p-2.5 rounded-xl transition-colors" :class="netProfit >= 0 ? 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100' : 'bg-red-50 text-red-600 group-hover:bg-red-100'">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50">
            <span class="text-xs font-semibold" :class="netProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">
              {{ netProfit >= 0 ? 'Profitable' : 'Loss' }}
            </span>
          </div>
        </div>
      </template>
    </div>

    <!-- Main Content Tables (Skeletons / Real Data) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Invoices Table Card -->
      <div v-if="authStore.hasPermission('invoices.view')" class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </span>
            <h2 class="text-lg font-bold text-slate-900">Recent Invoices</h2>
          </div>
          <router-link to="/invoices" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">View All</router-link>
        </div>

        <div class="flex-1 overflow-x-auto">
          <!-- Skeleton Loading -->
          <div v-if="loading" class="divide-y divide-slate-100 p-6 space-y-4">
            <div v-for="i in 5" :key="i" class="flex justify-between items-center animate-pulse">
              <div class="space-y-2">
                <div class="h-4 bg-slate-200 rounded w-28"></div>
                <div class="h-3 bg-slate-200 rounded w-16"></div>
              </div>
              <div class="h-6 bg-slate-200 rounded w-16"></div>
            </div>
          </div>
          <!-- Table Data -->
          <table v-else class="min-w-full divide-y divide-slate-100 text-left">
            <thead class="bg-slate-50/50">
              <tr>
                <th class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider">Invoice No</th>
                <th class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr v-if="recentInvoices.length === 0">
                <td colspan="4" class="px-6 py-8 text-center text-sm font-semibold text-slate-400">No invoices recorded yet.</td>
              </tr>
              <tr 
                v-else
                v-for="inv in recentInvoices" 
                :key="inv.id" 
                class="hover:bg-slate-50/50 transition-colors cursor-pointer"
                @click="$router.push(`/invoices/${inv.id}`)"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900">{{ inv.invoice_number }}</div>
                  <div class="text-xs text-slate-400 mt-0.5">{{ formatDate(inv.created_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-slate-800">{{ inv.customer?.name || 'Walk-in Customer' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900">${{ parseFloat(inv.grand_total).toLocaleString(undefined, {minimumFractionDigits: 2}) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span 
                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold capitalize"
                    :class="{
                      'bg-emerald-50 text-emerald-700': inv.payment_status === 'paid',
                      'bg-amber-50 text-amber-700': inv.payment_status === 'partial',
                      'bg-rose-50 text-rose-700': inv.payment_status === 'unpaid'
                    }"
                  >
                    {{ inv.payment_status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Purchases Table Card -->
      <div v-if="authStore.hasPermission('purchases.view')" class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </span>
            <h2 class="text-lg font-bold text-slate-900">Recent Purchases</h2>
          </div>
          <router-link to="/purchases" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">View All</router-link>
        </div>

        <div class="flex-1 overflow-x-auto">
          <!-- Skeleton Loading -->
          <div v-if="loading" class="divide-y divide-slate-100 p-6 space-y-4">
            <div v-for="i in 5" :key="i" class="flex justify-between items-center animate-pulse">
              <div class="space-y-2">
                <div class="h-4 bg-slate-200 rounded w-28"></div>
                <div class="h-3 bg-slate-200 rounded w-16"></div>
              </div>
              <div class="h-6 bg-slate-200 rounded w-16"></div>
            </div>
          </div>
          <!-- Table Data -->
          <table v-else class="min-w-full divide-y divide-slate-100 text-left">
            <thead class="bg-slate-50/50">
              <tr>
                <th class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider">Purchase No</th>
                <th class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider">Supplier</th>
                <th class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr v-if="recentPurchases.length === 0">
                <td colspan="4" class="px-6 py-8 text-center text-sm font-semibold text-slate-400">No purchases recorded yet.</td>
              </tr>
              <tr 
                v-else
                v-for="pur in recentPurchases" 
                :key="pur.id" 
                class="hover:bg-slate-50/50 transition-colors cursor-pointer"
                @click="$router.push(`/purchases/${pur.id}`)"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900">{{ pur.purchase_no }}</div>
                  <div class="text-xs text-slate-400 mt-0.5">{{ formatDate(pur.purchase_date || pur.created_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-slate-800">{{ pur.supplier?.name || 'Direct Supplier' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900">${{ parseFloat(pur.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2}) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span 
                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold capitalize"
                    :class="{
                      'bg-emerald-50 text-emerald-700': pur.status === 'received' || pur.status === 'approved',
                      'bg-amber-50 text-amber-700': pur.status === 'pending',
                      'bg-rose-50 text-rose-700': pur.status === 'cancelled' || pur.status === 'rejected'
                    }"
                  >
                    {{ pur.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';
import { useDashboardSocket } from '../../composables/useDashboardSocket';
import { useInvoiceSocket } from '../../composables/useInvoiceSocket';
import { usePurchaseSocket } from '../../composables/usePurchaseSocket';
import { useStockSocket } from '../../composables/useStockSocket';

// States
const authStore = useAuthStore();
const dashboardData = ref(null);
const invoices = ref([]);
const purchases = ref([]);
const loading = ref(true);
const error = ref(null);

// Main Parallel Fetch Data
const fetchData = async () => {
  loading.value = true;
  error.value = null;
  try {
    const promises = [api.get('/dashboard')];

    const canViewInvoices = authStore.hasPermission('invoices.view');
    const canViewPurchases = authStore.hasPermission('purchases.view');

    if (canViewInvoices) {
      promises.push(api.get('/invoices', { params: { per_page: 50 } }));
    } else {
      promises.push(Promise.resolve({ data: { data: [] } }));
    }

    if (canViewPurchases) {
      promises.push(api.get('/purchases', { params: { per_page: 5 } }));
    } else {
      promises.push(Promise.resolve({ data: { data: [] } }));
    }

    const [dashboardRes, invoicesRes, purchasesRes] = await Promise.all(promises);
    
    dashboardData.value = dashboardRes.data.data;
    invoices.value = invoicesRes.data.data;
    purchases.value = purchasesRes.data.data;
  } catch (err) {
    console.error('Failed to fetch dashboard data', err);
    error.value = 'Failed to load dashboard data. Please try again.';
  } finally {
    loading.value = false;
  }
};

// Silent fetch for real-time websocket updates
const refreshDataSilently = async () => {
  try {
    const promises = [api.get('/dashboard')];

    const canViewInvoices = authStore.hasPermission('invoices.view');
    const canViewPurchases = authStore.hasPermission('purchases.view');

    if (canViewInvoices) {
      promises.push(api.get('/invoices', { params: { per_page: 50 } }));
    } else {
      promises.push(Promise.resolve({ data: { data: [] } }));
    }

    if (canViewPurchases) {
      promises.push(api.get('/purchases', { params: { per_page: 5 } }));
    } else {
      promises.push(Promise.resolve({ data: { data: [] } }));
    }

    const [dashboardRes, invoicesRes, purchasesRes] = await Promise.all(promises);
    
    dashboardData.value = dashboardRes.data.data;
    invoices.value = invoicesRes.data.data;
    purchases.value = purchasesRes.data.data;
  } catch (err) {
    console.error('Silent refresh failed', err);
  }
};

onMounted(() => {
  fetchData();
});

// Setup websocket connection listeners for all key events to update dashboard statistics in real-time
useDashboardSocket((newData) => {
  if (dashboardData.value) {
    dashboardData.value = { ...dashboardData.value, ...newData };
  }
  refreshDataSilently();
});

useInvoiceSocket(() => {
  refreshDataSilently();
});

usePurchaseSocket(() => {
  refreshDataSilently();
});

useStockSocket(() => {
  refreshDataSilently();
});

// Computed Statistics
const todaySales = computed(() => {
  if (!invoices.value || invoices.value.length === 0) return 0;
  
  // Format local current date (YYYY-MM-DD)
  const todayStr = new Date().toLocaleDateString('sv'); // 'YYYY-MM-DD'
  
  return invoices.value
    .filter(inv => {
      if (!inv.created_at) return false;
      const invDate = inv.created_at.split('T')[0];
      return invDate === todayStr;
    })
    .reduce((sum, inv) => sum + parseFloat(inv.grand_total || 0), 0);
});

const monthlyRevenue = computed(() => {
  if (!dashboardData.value || !dashboardData.value.monthly_sales || dashboardData.value.monthly_sales.length === 0) {
    return 0;
  }
  const currentMonth = new Date().getMonth() + 1; // 1-12
  const match = dashboardData.value.monthly_sales.find(s => parseInt(s.month) === currentMonth);
  return match ? parseFloat(match.total) : 0;
});

const pendingInvoicesCount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value.filter(inv => inv.payment_status !== 'paid').length;
});

const totalDueAmount = computed(() => {
  if (!dashboardData.value || !dashboardData.value.summary) return 0;
  return parseFloat(dashboardData.value.summary.total_due || 0);
});

const lowStockCount = computed(() => {
  if (!dashboardData.value || !dashboardData.value.summary) return 0;
  return parseInt(dashboardData.value.summary.low_stock_parts || 0);
});

const totalExpenses = computed(() => {
  if (!dashboardData.value || !dashboardData.value.summary) return 0;
  return parseFloat(dashboardData.value.summary.total_expenses || 0);
});

const netProfit = computed(() => {
  if (!dashboardData.value || !dashboardData.value.summary) return 0;
  return parseFloat(dashboardData.value.summary.net_profit || 0);
});

const recentInvoices = computed(() => {
  if (!invoices.value) return [];
  return [...invoices.value].slice(0, 5);
});

const recentPurchases = computed(() => {
  if (!purchases.value) return [];
  return [...purchases.value].slice(0, 5);
});

// SVG Sparkline Polyline Builders
const monthlySalesSparklinePoints = computed(() => {
  if (!dashboardData.value || !dashboardData.value.monthly_sales || dashboardData.value.monthly_sales.length === 0) {
    return '0,15 20,15 40,15 60,15 80,15 100,15';
  }
  const sales = dashboardData.value.monthly_sales;
  const sorted = [...sales].sort((a, b) => parseInt(a.month) - parseInt(b.month));
  const values = sorted.map(s => parseFloat(s.total));
  const maxVal = Math.max(...values, 1);
  const minVal = Math.min(...values, 0);
  const range = maxVal - minVal;
  
  const width = 120;
  const height = 30;
  const padding = 3;
  
  return sorted.map((s, idx) => {
    const x = (idx / (sorted.length - 1 || 1)) * (width - 2 * padding) + padding;
    const y = height - padding - ((parseFloat(s.total) - minVal) / range) * (height - 2 * padding);
    return `${x.toFixed(1)},${y.toFixed(1)}`;
  }).join(' ');
});

const invoicesSparklinePoints = computed(() => {
  if (!invoices.value || invoices.value.length === 0) {
    return '0,15 20,15 40,15 60,15 80,15 100,15';
  }
  const list = [...invoices.value].slice(0, 7).reverse();
  const values = list.map(i => parseFloat(i.grand_total));
  const maxVal = Math.max(...values, 1);
  const minVal = Math.min(...values, 0);
  const range = maxVal - minVal || 1;
  
  const width = 120;
  const height = 30;
  const padding = 3;
  
  return list.map((inv, idx) => {
    const x = (idx / (list.length - 1 || 1)) * (width - 2 * padding) + padding;
    const y = height - padding - ((parseFloat(inv.grand_total) - minVal) / range) * (height - 2 * padding);
    return `${x.toFixed(1)},${y.toFixed(1)}`;
  }).join(' ');
});

// Helper
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

