<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center print:hidden">
    <!-- Backdrop -->
    <div 
      class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity"
      @click="closeOnBackdrop ? $emit('update:modelValue', false) : null"
    ></div>
    
    <!-- Modal Dialog -->
    <div 
      class="relative bg-slate-800 rounded-lg shadow-2xl border border-slate-600 flex flex-col w-full max-h-[90vh] mx-4 transition-all"
      :class="maxWidthClass"
    >
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700">
        <h3 class="text-lg font-semibold text-white">
          <slot name="title">{{ title }}</slot>
        </h3>
        <button 
          @click="$emit('update:modelValue', false)"
          class="text-slate-400 hover:text-white transition-colors p-1 rounded hover:bg-slate-700"
        >
          ✕
        </button>
      </div>
      
      <!-- Body -->
      <div class="px-6 py-4 overflow-y-auto flex-1">
        <slot></slot>
      </div>
      
      <!-- Footer -->
      <div v-if="$slots.footer" class="px-6 py-4 border-t border-slate-700 bg-slate-800/50 flex justify-end gap-3 rounded-b-lg">
        <slot name="footer"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  title: { type: String, default: '' },
  maxWidth: { type: String, default: 'md' }, // sm, md, lg, xl, 2xl
  closeOnBackdrop: { type: Boolean, default: true }
});

const emit = defineEmits(['update:modelValue']);

const maxWidthClass = computed(() => {
  const map = {
    'sm': 'max-w-sm',
    'md': 'max-w-md',
    'lg': 'max-w-lg',
    'xl': 'max-w-xl',
    '2xl': 'max-w-2xl',
    '4xl': 'max-w-4xl'
  };
  return map[props.maxWidth] || map['md'];
});

const handleEscape = (e) => {
  if (e.key === 'Escape' && props.modelValue) {
    emit('update:modelValue', false);
  }
};

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

onMounted(() => {
  window.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape);
  document.body.style.overflow = '';
});
</script>
