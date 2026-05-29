<template>
  <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Revenue Forecast trends</h3>
        <p class="text-[10px] text-slate-400 mt-0.5">Weighted 12-month historical projection vs actuals</p>
      </div>
      <div class="flex items-center gap-4 text-xs font-bold">
        <span class="flex items-center gap-1.5 text-slate-500">
          <span class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></span>
          Actual
        </span>
        <span class="flex items-center gap-1.5 text-slate-500">
          <span class="w-2.5 h-2.5 bg-purple-400 rounded-full border-2 border-dashed border-white"></span>
          AI Forecast
        </span>
      </div>
    </div>

    <!-- Chart Grid -->
    <div class="h-48 flex items-end gap-3.5 pt-4 border-b border-slate-100 pb-2">
      <div 
        v-for="bar in chartData" 
        :key="bar.month"
        class="flex-1 flex flex-col justify-end items-center h-full group relative cursor-pointer"
      >
        <!-- Tooltip -->
        <div class="absolute -top-10 scale-0 group-hover:scale-100 bg-slate-900 text-white font-mono text-[9px] px-2.5 py-1.5 rounded-lg shadow-xl z-20 transition duration-150 flex flex-col gap-0.5 min-w-[90px] text-center">
          <span class="text-slate-400">Actual: ৳{{ bar.actual }}k</span>
          <span class="text-purple-300 font-bold">Forecast: ৳{{ bar.forecast }}k</span>
        </div>

        <!-- AI Forecast Bar (dashed overlay or ghost bar behind) -->
        <div 
          class="w-full bg-purple-100 rounded-t-lg transition-all duration-500 relative"
          :style="{ height: `${bar.forecast * 0.8}%` }"
        >
          <!-- Actual Revenue Bar overlaying -->
          <div 
            class="w-full bg-indigo-500 hover:bg-indigo-600 rounded-t-lg absolute bottom-0 transition-all duration-500 shadow-sm"
            :style="{ height: `${(bar.actual / bar.forecast) * 100}%` }"
          ></div>
        </div>

        <span class="text-[9px] font-mono font-bold text-slate-400 mt-2 uppercase">
          {{ bar.month }}
        </span>
      </div>
    </div>

    <div class="flex justify-between items-center text-xs bg-slate-50 border border-slate-100 p-3.5 rounded-2xl">
      <div class="flex items-center gap-2">
        <span class="text-[10px] bg-indigo-50 border border-indigo-200 text-indigo-700 font-black px-2 py-0.5 rounded">
          90d weight
        </span>
        <span class="text-slate-500">Predicted Q3 Surge:</span>
      </div>
      <span class="font-black text-slate-800">৳1,580,000 (+18.5% YoY)</span>
    </div>
  </div>
</template>

<script setup>
const chartData = [
  { month: 'Jan', actual: 45, forecast: 42 },
  { month: 'Feb', actual: 52, forecast: 50 },
  { month: 'Mar', actual: 60, forecast: 58 },
  { month: 'Apr', actual: 75, forecast: 70 }, // Eid surge
  { month: 'May', actual: 80, forecast: 82 }, // Eid surge
  { month: 'Jun', actual: 95, forecast: 90 }, // AC summer peak
  { month: 'Jul', actual: 0, forecast: 85 },  // Projected
  { month: 'Aug', actual: 0, forecast: 78 },  // Projected
  { month: 'Sep', actual: 0, forecast: 72 },  // Projected
  { month: 'Oct', actual: 0, forecast: 65 },  // Projected
];
</script>
