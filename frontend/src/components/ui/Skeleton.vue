<template>
  <div 
    class="animate-pulse bg-slate-200 rounded" 
    :class="[width, height, circle ? 'rounded-full' : '']"
  ></div>
</template>

<script setup>
defineProps({
  width: { type: String, default: 'w-full' },
  height: { type: String, default: 'h-4' },
  circle: { type: Boolean, default: false }
});
</script>
