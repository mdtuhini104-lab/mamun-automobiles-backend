<template>
  <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
    <transition-group name="toast">
      <div 
        v-for="toast in toastStore.toasts" 
        :key="toast.id"
        class="min-w-[300px] p-4 rounded shadow-lg flex justify-between items-start pointer-events-auto transition-all duration-300"
        :class="{
          'bg-green-600 text-white': toast.type === 'success',
          'bg-red-600 text-white': toast.type === 'error',
          'bg-yellow-500 text-black': toast.type === 'warning',
          'bg-blue-600 text-white': toast.type === 'info'
        }"
      >
        <span class="text-sm font-medium">{{ toast.message }}</span>
        <button @click="toastStore.removeToast(toast.id)" class="ml-4 opacity-70 hover:opacity-100">
          ✕
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToastStore } from '../../stores/toast';
const toastStore = useToastStore();
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
