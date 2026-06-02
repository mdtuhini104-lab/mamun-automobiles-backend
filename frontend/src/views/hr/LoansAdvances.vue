<template>
  <div class="h-full w-full flex flex-col p-6 animate-in fade-in duration-300">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Loans Advances</h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Enterprise Module Placeholder</p>
      </div>
      <div class="flex items-center gap-3">
        <button class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 transition">
          Documentation
        </button>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-sm hover:bg-indigo-700 transition">
          Initialize Module
        </button>
      </div>
    </div>

    <!-- Empty State Content -->
    <div class="flex-1 flex flex-col items-center justify-center bg-white border border-slate-200 border-dashed rounded-3xl p-12 text-center">
      <div class="w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center mb-6">
        <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
      </div>
      <h3 class="text-lg font-bold text-slate-800 mb-2">Loans Advances Module is currently under development</h3>
      <p class="text-slate-500 max-w-md text-sm leading-relaxed mb-8">
        This enterprise module is part of the upcoming ERP release cycle. Core database schemas and API endpoints are being provisioned.
      </p>
      
      <!-- Skeleton UI preview -->
      <div class="w-full max-w-2xl bg-slate-50 rounded-2xl border border-slate-100 p-6 flex flex-col gap-4">
        <div class="h-4 bg-slate-200 rounded w-1/4 animate-pulse"></div>
        <div class="h-10 bg-slate-200 rounded w-full animate-pulse"></div>
        <div class="grid grid-cols-3 gap-4">
          <div class="h-24 bg-slate-200 rounded-xl animate-pulse"></div>
          <div class="h-24 bg-slate-200 rounded-xl animate-pulse"></div>
          <div class="h-24 bg-slate-200 rounded-xl animate-pulse delay-75"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
  console.log('LoansAdvances module initialized in placeholder mode.');
});
</script>
