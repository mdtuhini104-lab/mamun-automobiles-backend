<template>
  <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
      <div>
        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Technician Efficiency Heatmap</h3>
        <p class="text-[10px] text-slate-400 mt-0.5">Realtime completion speeds vs QC pass ratios</p>
      </div>
      <span class="text-[10px] font-bold text-slate-500 uppercase">12-Month aggregates</span>
    </div>

    <!-- Heatmap Matrix -->
    <div class="space-y-4">
      <div 
        v-for="tech in roster" 
        :key="tech.id"
        class="flex items-center justify-between p-3.5 bg-slate-50 border border-slate-100 rounded-2xl gap-4 hover:border-slate-200 transition"
      >
        <!-- Tech info -->
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 font-extrabold flex items-center justify-center text-xs uppercase tracking-wider">
            {{ tech.initials }}
          </div>
          <div class="min-w-0">
            <h4 class="text-xs font-black text-slate-800 truncate">{{ tech.name }}</h4>
            <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">{{ tech.role }}</p>
          </div>
        </div>

        <!-- Metric Grid -->
        <div class="flex items-center gap-6 text-right shrink-0">
          <!-- Quality Pass Rate -->
          <div>
            <span class="block text-[9px] font-bold text-slate-400 uppercase">QC Pass Rate</span>
            <span 
              class="text-xs font-black"
              :class="getPassRateClass(tech.qc_pass_rate)"
            >
              {{ tech.qc_pass_rate }}%
            </span>
          </div>

          <!-- Repair Completion Speed -->
          <div>
            <span class="block text-[9px] font-bold text-slate-400 uppercase">Speed Ratio</span>
            <span 
              class="text-xs font-black"
              :class="getSpeedClass(tech.speed_ratio)"
            >
              {{ tech.speed_ratio }}% of Est.
            </span>
          </div>

          <!-- Status Indicator Block -->
          <div 
            class="w-12 py-1 rounded text-center text-[9px] font-extrabold uppercase border"
            :class="getHeatmapBadgeClass(tech.qc_pass_rate, tech.speed_ratio)"
          >
            {{ getHeatmapLabel(tech.qc_pass_rate, tech.speed_ratio) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const roster = [
  { id: 1, name: 'Anowar Hossain', initials: 'AH', role: 'Lead Tech', qc_pass_rate: 96, speed_ratio: 82 }, // fast and high quality
  { id: 2, name: 'Kabir Uddin', initials: 'KU', role: 'Senior Tech', qc_pass_rate: 91, speed_ratio: 95 },  // standard
  { id: 3, name: 'Mamun Mia', initials: 'MM', role: 'Technician', qc_pass_rate: 78, speed_ratio: 120 },   // slow, needs training
  { id: 4, name: 'Sajidul Islam', initials: 'SI', role: 'Assistant Tech', qc_pass_rate: 88, speed_ratio: 105 },
];

const getPassRateClass = (rate) => {
  if (rate >= 90) return 'text-emerald-600';
  if (rate >= 80) return 'text-indigo-600';
  return 'text-amber-600';
};

const getSpeedClass = (speed) => {
  if (speed < 90) return 'text-emerald-600'; // faster than estimated
  if (speed <= 110) return 'text-indigo-600';
  return 'text-rose-500 font-bold animate-pulse'; // slower than estimated
};

const getHeatmapBadgeClass = (qc, speed) => {
  if (qc >= 90 && speed < 100) return 'bg-emerald-50 border-emerald-200 text-emerald-700'; // Elite
  if (qc < 80 || speed > 110) return 'bg-rose-50 border-rose-200 text-rose-700'; // Training required
  return 'bg-indigo-50 border-indigo-200 text-indigo-700'; // Active / Stable
};

const getHeatmapLabel = (qc, speed) => {
  if (qc >= 90 && speed < 100) return 'Elite';
  if (qc < 80 || speed > 110) return 'Train';
  return 'Stable';
};
</script>
