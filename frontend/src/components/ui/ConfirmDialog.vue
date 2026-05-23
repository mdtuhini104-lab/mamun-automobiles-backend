<template>
  <div v-if="modelValue" class="fixed inset-0 z-[60] flex items-center justify-center print:hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="cancel"></div>
    
    <!-- Dialog -->
    <div class="relative bg-white rounded-lg shadow-2xl border border-slate-200 flex flex-col w-full max-w-sm mx-4 transform transition-all">
      <div class="p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-2">{{ title }}</h3>
        <p class="text-slate-600 text-sm">{{ message }}</p>
      </div>
      <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 rounded-b-lg">
        <button 
          ref="cancelBtn"
          @click="cancel"
          class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded focus:ring-2 focus:ring-slate-400 focus:outline-none transition-colors"
        >
          {{ cancelText }}
        </button>
        <button 
          @click="confirm"
          class="px-4 py-2 text-sm font-semibold text-white rounded focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:outline-none transition-colors"
          :class="isDanger ? 'bg-red-600 hover:bg-red-500 focus:ring-red-500' : 'bg-indigo-600 hover:bg-indigo-500 focus:ring-indigo-500'"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  title: { type: String, default: 'Confirm Action' },
  message: { type: String, default: 'Are you sure you want to proceed?' },
  confirmText: { type: String, default: 'Confirm' },
  cancelText: { type: String, default: 'Cancel' },
  isDanger: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);
const cancelBtn = ref(null);

watch(() => props.modelValue, async (isOpen) => {
  if (isOpen) {
    await nextTick();
    if (cancelBtn.value) cancelBtn.value.focus();
  }
});

const confirm = () => {
  emit('confirm');
  emit('update:modelValue', false);
};

const cancel = () => {
  emit('cancel');
  emit('update:modelValue', false);
};

const handleEscape = (e) => {
  if (e.key === 'Escape' && props.modelValue) cancel();
};

onMounted(() => window.addEventListener('keydown', handleEscape));
onUnmounted(() => window.removeEventListener('keydown', handleEscape));
</script>
