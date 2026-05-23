<template>
  <div class="flex flex-col items-center justify-center p-8 text-center bg-red-50 rounded-lg border border-red-200 shadow-sm">
    <div class="text-red-500 mb-3">
      <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
    </div>
    <h3 class="text-lg font-bold text-red-700 mb-1">{{ title }}</h3>
    <p class="text-sm text-red-600 max-w-sm mx-auto mb-4">{{ description }}</p>
    <button v-if="retry" @click="$emit('retry')" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded text-sm transition-colors font-semibold shadow-sm">
      Try Again
    </button>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'Something went wrong' },
  description: { type: String, default: 'An error occurred while loading this data.' },
  retry: { type: Boolean, default: true }
});
defineEmits(['retry']);
</script>
