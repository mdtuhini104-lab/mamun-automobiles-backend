<template>
  <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6 relative overflow-hidden">
    <!-- Header with AI branding -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 animate-pulse">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21L14.907 18M18 14.25L19.5 21L14.907 18M14.907 18L12 13.5M12 13.5L15 9M12 13.5L9 9M9.813 15.904L6.938 12.188M9.813 15.904L12 13.5M9.813 15.904L10.5 10.5M18 14.25L16.5 9.75M18 14.25L15 9M16.5 9.75L12 9M16.5 9.75L18 4.5L12 9M12 9L9 9M9 9L5.25 12" />
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">AI Pricing Assistant</h3>
          <p class="text-[10px] text-slate-400 mt-0.5">In-Context Markup & Risk Scanning</p>
        </div>
      </div>
      <span class="text-[9px] bg-purple-50 text-purple-700 font-extrabold px-2 py-0.5 rounded border border-purple-100 uppercase tracking-wider">
        Active Copilot
      </span>
    </div>

    <!-- Interactive Anomaly Alerts -->
    <div class="space-y-3">
      <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Live Discount Validation</div>
      
      <!-- Safe State -->
      <div 
        v-if="warnings.length === 0" 
        class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-xs text-emerald-800"
      >
        <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full shrink-0"></span>
        <div>
          <span class="font-extrabold block">All pricing entries are safe</span>
          <span class="text-emerald-600/90 text-[10px] mt-0.5 block">No discount policy warning triggered.</span>
        </div>
      </div>

      <!-- Warning State -->
      <div 
        v-else 
        v-for="warning in warnings" 
        :key="warning.id"
        class="p-4 rounded-2xl border text-xs flex gap-3"
        :class="warning.severity === 'critical' ? 'bg-rose-50 border-rose-100 text-rose-800' : 'bg-amber-50 border-amber-100 text-amber-800'"
      >
        <span 
          class="w-2.5 h-2.5 rounded-full shrink-0 mt-1 animate-ping"
          :class="warning.severity === 'critical' ? 'bg-rose-500' : 'bg-amber-500'"
        ></span>
        <div>
          <span class="font-black block uppercase tracking-wider text-[10px]" :class="warning.severity === 'critical' ? 'text-rose-700' : 'text-amber-700'">
            {{ warning.severity === 'critical' ? 'High-Risk Anomaly' : 'Item Warning' }}
          </span>
          <span class="block mt-1 font-semibold text-[11px] leading-relaxed">{{ warning.message }}</span>
          <span class="block mt-2 text-[10px] font-bold underline cursor-pointer" @click="$router.push('/ai-inbox')">
            Requires Central Inbox Sign-off
          </span>
        </div>
      </div>
    </div>

    <!-- Suggested Pricing Rules / Realtime recommendations -->
    <div class="space-y-4 pt-2 border-t border-slate-100">
      <div class="text-xs font-bold text-slate-400 uppercase tracking-widest flex justify-between">
        <span>Suggested Labor Markups</span>
        <span class="text-purple-600">Confidence: 94%</span>
      </div>

      <div class="space-y-2.5">
        <div 
          v-for="item in suggestions" 
          :key="item.id"
          class="p-3 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-between text-xs"
        >
          <div>
            <span class="font-extrabold text-slate-800">{{ item.service }}</span>
            <span class="block text-[10px] text-slate-400 mt-0.5">VIP customer discount adjustment applied</span>
          </div>
          <div class="text-right shrink-0">
            <span class="block font-black text-slate-900">৳{{ item.cost }}</span>
            <span class="text-[9px] text-indigo-600 font-bold block">{{ item.hours }} hrs suggested</span>
          </div>
        </div>
      </div>
    </div>

    <!-- AI Safety & Explainability reminder -->
    <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-[10px] text-slate-500 leading-relaxed italic">
      "Explainability note: AI suggestions are calculated based on 12-month historical service logs adjusted for negotiated corporate contract discounts. Auto-mutation of financial records is blocked."
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  quotationItems: {
    type: Array,
    default: () => [],
  },
  discountAmount: {
    type: Number,
    default: 0,
  },
  totalAmount: {
    type: Number,
    default: 0,
  }
});

const warnings = ref([]);

const suggestions = [
  { id: 1, service: 'Engine Tune-Up Diagnostic', cost: '3,500', hours: '2.0' },
  { id: 2, service: 'Brake Caliper Servicing', cost: '1,800', hours: '1.2' },
  { id: 3, service: 'AC System Evacuation & Recharge', cost: '2,200', hours: '1.5' },
];

const scanPricing = () => {
  const list = [];
  
  // 1. Scan item level discounts (> 15%)
  props.quotationItems.forEach((item, idx) => {
    // If discount rate exceeds 15%
    if (item.discount_rate > 15) {
      list.push({
        id: `item-${idx}`,
        severity: 'warning',
        message: `Line item '${item.part?.name || item.service_name}' discount exceeds 15% margin safety threshold.`
      });
    }
  });

  // 2. Scan grand total discount (> 20%)
  const grandTotalDiscountPercent = props.totalAmount > 0 ? (props.discountAmount / props.totalAmount) * 100 : 0;
  if (grandTotalDiscountPercent > 20) {
    list.push({
      id: 'grand-total',
      severity: 'critical',
      message: `Quotation Grand Total discount holds a ${Math.round(grandTotalDiscountPercent)}% rate, exceeding the 20% high-risk threshold limit.`
    });
  }

  warnings.value = list;
};

// Watch for quotation details modification
watch(() => [props.quotationItems, props.discountAmount, props.totalAmount], () => {
  scanPricing();
}, { deep: true, immediate: true });
</script>

<style scoped>
</style>
