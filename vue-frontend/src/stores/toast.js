import { defineStore } from 'pinia';

export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: []
  }),
  actions: {
    addToast({ message, type = 'info', duration = 3000 }) {
      const id = Date.now() + Math.random().toString();
      this.toasts.push({ id, message, type });
      
      if (duration > 0) {
        setTimeout(() => {
          this.removeToast(id);
        }, duration);
      }
    },
    success(message) {
      this.addToast({ message, type: 'success' });
    },
    error(message) {
      this.addToast({ message, type: 'error' });
    },
    warning(message) {
      this.addToast({ message, type: 'warning' });
    },
    info(message) {
      this.addToast({ message, type: 'info' });
    },
    removeToast(id) {
      this.toasts = this.toasts.filter(t => t.id !== id);
    }
  }
});
