<template>
  <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
      <div>
        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Operational BI metrics</h3>
        <p class="text-[10px] text-slate-400 mt-0.5">consolidated executive metrics and turnovers</p>
      </div>
      <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-2.5 py-0.5 rounded-full">Realtime</span>
    </div>

    <!-- KPIs List -->
    <div class="grid grid-cols-2 gap-4">
      <!-- 1. Comeback Rate -->
      <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-between h-24">
        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Comeback Rate</span>
        <div class="flex items-baseline gap-2 mt-2">
          <span class="text-xl font-black text-slate-800">2.1%</span>
          <span class="text-[9px] font-bold text-emerald-600">-0.4% MoM</span>
        </div>
      </div>

      <!-- 2. Bay Utilization -->
      <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-between h-24">
        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Bay Saturation</span>
        <div class="flex items-baseline gap-2 mt-2">
          <span class="text-xl font-black text-slate-800">80.0%</span>
          <span class="text-[9px] font-bold text-indigo-600">Optimal</span>
        </div>
      </div>

      <!-- 3. Inventory Turnover -->
      <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-between h-24">
        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Stock Turnover</span>
        <div class="flex items-baseline gap-2 mt-2">
          <span class="text-xl font-black text-slate-800">4.8x</span>
          <span class="text-[9px] font-bold text-emerald-600">Healthy</span>
        </div>
      </div>

      <!-- 4. Customer Retention -->
      <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-between h-24">
        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Retention Rate</span>
        <div class="flex items-baseline gap-2 mt-2">
          <span class="text-xl font-black text-slate-800">72.5%</span>
          <span class="text-[9px] font-bold text-emerald-600">+1.2% MoM</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Props or API metrics binding as needed
</script>
