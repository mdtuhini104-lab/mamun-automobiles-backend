<template>
  <div class="min-h-screen bg-gray-50 text-slate-800 flex overflow-hidden">
    <!-- Mobile Sidebar Overlay -->
    <div 
      v-if="isMobileMenuOpen" 
      class="fixed inset-0 bg-black/40 z-40 md:hidden"
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside 
      class="w-64 bg-white border-r border-gray-200 flex-shrink-0 flex flex-col fixed md:relative z-50 h-full transition-transform duration-300"
      :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <img src="/logo-dark.png" alt="ERP System" class="h-8 w-auto" />
        <button class="md:hidden text-gray-500 hover:text-slate-900" @click="isMobileMenuOpen = false">✕</button>
      </div>
      <nav class="p-4 space-y-1 flex-1 overflow-y-auto">
        <!-- GENERAL -->
        <div class="px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider mt-2">Overview</div>
        <router-link :to="{ name: 'dashboard-home' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </router-link>
        <router-link v-if="authStore.hasPermission('analytics.view')" :to="{ name: 'analytics.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Analytics & BI
        </router-link>
        <router-link v-if="authStore.hasPermission('analytics.view')" :to="{ name: 'dashboard.executive' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Executive Cockpit
        </router-link>

        <!-- WORKSHOP -->
        <div class="px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider mt-4">Workshop Operations</div>
        <router-link :to="{ name: 'workshop.hub' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-bold text-slate-700 bg-slate-100/50 hover:bg-slate-100 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 8.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"></path></svg>
          Workshop Operations Hub
        </router-link>
        <router-link v-if="authStore.hasPermission('customers.view')" :to="{ name: 'customers.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          Customers
        </router-link>
        <router-link v-if="authStore.hasPermission('vehicles.view')" :to="{ name: 'vehicles.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
          Vehicles
        </router-link>
        <router-link v-if="authStore.hasPermission('job_cards.view')" :to="{ name: 'job-cards.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          Service Jobs
        </router-link>
        <router-link v-if="authStore.hasPermission('quality_controls.manage')" :to="{ name: 'workshop.qc-delivery' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
          Delivery & QC Hub
        </router-link>
        <router-link v-if="authStore.hasPermission('appointments.view')" :to="{ name: 'appointments.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
          CRM & Appointments
        </router-link>

        <!-- FINANCE & INVENTORY -->
        <div class="px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider mt-4">Finance & Supply</div>
        <router-link v-if="authStore.hasPermission('invoices.view')" :to="{ name: 'invoices.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          Invoices
        </router-link>
        <router-link v-if="authStore.hasPermission('accounts.view')" :to="{ name: 'accounts.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
          Accounts & Transactions
        </router-link>
        <router-link v-if="authStore.hasPermission('inventory.view')" :to="{ name: 'inventory-list' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
          Inventory & Parts
        </router-link>
        <router-link v-if="authStore.hasPermission('purchases.view')" :to="{ name: 'purchases-list' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
          Suppliers & Purchases
        </router-link>

        <!-- HUMAN RESOURCES -->
        <div class="px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider mt-4">Human Resources</div>
        <router-link v-if="authStore.hasPermission('payrolls.view')" :to="{ name: 'payrolls.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          HR & Payroll
        </router-link>
        <router-link v-if="authStore.hasPermission('attendances.view')" :to="{ name: 'attendances.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
          Attendance
        </router-link>
        <router-link v-if="authStore.hasPermission('staff.view')" :to="{ name: 'staff.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          Staff & HR
        </router-link>
        <router-link v-if="authStore.hasPermission('staff.view')" :to="{ name: 'roles.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Roles & Permissions
        </router-link>

        <!-- SYSTEM & ADMIN -->
        <div class="px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider mt-4">System & Admin</div>
        <div class="px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider mt-4">System Settings</div>
        <router-link v-if="authStore.hasPermission('settings.view')" :to="{ name: 'settings.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100 transition-all border-l-3 border-transparent" active-class="!text-indigo-700 !border-indigo-600 bg-slate-50 shadow-sm font-semibold">
          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          General Settings
        </router-link>
        <router-link v-if="authStore.hasPermission('activity_logs.view')" :to="{ name: 'activity-logs.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100 transition-all border-l-3 border-transparent" active-class="!text-indigo-700 !border-indigo-600 bg-slate-50 shadow-sm font-semibold">
          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          Activity Logs
        </router-link>
        <router-link v-if="authStore.hasPermission('saas_admin.view')" :to="{ name: 'saas.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-all border-l-3 border-transparent" active-class="!text-rose-700 !border-rose-600 bg-rose-50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
          SaaS Admin
        </router-link>
        <router-link v-if="authStore.hasPermission('ai_operations.view')" :to="{ name: 'ai.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-purple-600 hover:bg-purple-50 transition-all border-l-3 border-transparent" active-class="!text-purple-700 !border-purple-600 bg-purple-50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
          AI Automation
        </router-link>
        <router-link v-if="authStore.hasPermission('ai_operations.view')" :to="{ name: 'dashboard.ai-inbox' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-purple-600 hover:bg-purple-50 transition-all border-l-3 border-transparent" active-class="!text-purple-700 !border-purple-600 bg-purple-50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2zM9 11h.01M12 11h.01M15 11h.01"></path></svg>
          AI Governance Inbox
        </router-link>
        <router-link v-if="authStore.hasPermission('settings.view')" :to="{ name: 'settings.index' }" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-3 border-transparent" active-class="!text-indigo-600 !border-indigo-600 bg-indigo-50/50 shadow-sm">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          Settings
        </router-link>
      </nav>
      <div class="p-4 border-t border-gray-200">
        <button @click="handleLogout" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-sm font-semibold hover:bg-red-100 hover:text-red-700 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
      <!-- Mobile Header -->
      <header class="bg-white border-b border-gray-200 p-4 flex items-center md:hidden shrink-0">
        <button class="text-gray-500 hover:text-slate-900 mr-4" @click="isMobileMenuOpen = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <img src="/logo-dark.png" alt="Mamun Automobiles" class="h-6 w-auto" />
      </header>
      
      <div class="flex-1 overflow-y-auto p-4 md:p-8 relative">
        <div v-if="hasError" class="flex flex-col items-center justify-center h-full min-h-[50vh] text-center px-4">
          <div class="bg-rose-50 rounded-full p-4 mb-4">
            <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <h2 class="text-xl font-bold text-slate-900 mb-2">Module Loading Failed</h2>
          <p class="text-slate-600 mb-6 max-w-md">{{ errorMessage }}</p>
          <button @click="router.go(0)" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
            Reload Page
          </button>
        </div>
        
        <router-view v-else v-slot="{ Component }">
          <template v-if="Component">
            <Suspense>
              <template #default>
                <component :is="Component" />
              </template>
              <template #fallback>
                <div class="flex flex-col items-center justify-center h-full min-h-[50vh] text-slate-400">
                  <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-4"></div>
                  <p>Loading module...</p>
                </div>
              </template>
            </Suspense>
          </template>
        </router-view>
      </div>
    </main>

    <!-- Global Components -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, onErrorCaptured } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import ToastContainer from '../../components/ui/ToastContainer.vue';

const router = useRouter();
const authStore = useAuthStore();
const isMobileMenuOpen = ref(false);
const hasError = ref(false);
const errorMessage = ref('');

onErrorCaptured((err, instance, info) => {
  console.error('Captured in DashboardLayout:', err, info);
  hasError.value = true;
  errorMessage.value = err.message || 'An unexpected error occurred while loading this module.';
  return false; // prevent propagation
});

router.afterEach(() => {
  // Reset error state on navigation
  hasError.value = false;
  errorMessage.value = '';
});

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'login' });
};
</script>

<style scoped>
/* Modern active link styling */
.router-link-active {
  background-color: #eef2ff; /* slate-100 / indigo-50 */
  color: #4f46e5; /* indigo-600 */
  border-left-color: #4f46e5 !important;
}
.router-link-active svg {
  opacity: 1 !important;
  color: #4f46e5 !important;
}
</style>


