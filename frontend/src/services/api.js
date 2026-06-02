import axios from 'axios';
import { useToastStore } from '../stores/toast';
import router from '../router';

const getBaseURL = () => {
  return import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1';
};

const api = axios.create({
  baseURL: getBaseURL(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  // Check if offline and request is modifying data
  if (typeof window !== 'undefined' && !navigator.onLine && config.method !== 'get') {
    const queue = JSON.parse(localStorage.getItem('mamun_offline_queue') || '[]');
    // Prevent duplicate entries for exact same payload
    const isDuplicate = queue.some(q => q.url === config.url && q.method === config.method && JSON.stringify(q.data || {}) === JSON.stringify(config.data || {}));
    if (!isDuplicate) {
      queue.push({
        url: config.url,
        method: config.method,
        data: config.data,
        timestamp: Date.now()
      });
      localStorage.setItem('mamun_offline_queue', JSON.stringify(queue));

      try {
        const toast = useToastStore();
        toast.warning('Offline Mode: Your action has been queued and will sync once online.');
      } catch (e) {}
    }

    // Cancel actual request and handle as resolved mock response in error block
    const cancelToken = axios.CancelToken.source();
    config.cancelToken = cancelToken.token;
    cancelToken.cancel('offline_queued');
  }

  return config;
}, (error) => {
  return Promise.reject(error);
});

api.interceptors.response.use(
  response => response,
  error => {
    // If request was offline-cancelled, return a resolved promise with mock success structure
    if (axios.isCancel(error) && error.message === 'offline_queued') {
      return Promise.resolve({
        data: { success: true, message: 'Offline queued', data: {} }
      });
    }

    try {
      const toast = useToastStore();
      
      // Auth Expiration / 401 Handling
      if (error.response && error.response.status === 401) {
        localStorage.removeItem('token');
        router.push({ name: 'login' });
        toast.error('Session expired. Please log in again.');
        return Promise.reject(error);
      }

      // Network / Offline Error
      if (!error.response) {
        toast.error('Network Error: Please check your internet connection.');
        return Promise.reject(error);
      }

      // Global API Error Normalization
      const status = error.response.status;
      const data = error.response.data;
      
      if (status === 403) {
        toast.error('Forbidden: You do not have permission to perform this action.');
      } else if (status === 422 && data.errors) {
        const firstError = Object.values(data.errors)[0][0];
        toast.warning(`Validation Error: ${firstError}`);
      } else if (status >= 500) {
        toast.error('Server Error: Something went wrong on the backend.');
      } else {
        toast.error(data?.message || 'An unexpected error occurred.');
      }
    } catch (toastErr) {
      console.error('Toast error in interceptor:', toastErr);
    }

    return Promise.reject(error);
  }
);

// Offline Queue Replay Synchronization
if (typeof window !== 'undefined') {
  window.addEventListener('online', async () => {
    const queue = JSON.parse(localStorage.getItem('mamun_offline_queue') || '[]');
    if (queue.length === 0) return;

    try {
      const toast = useToastStore();
      toast.info(`Device back online. Synchronizing ${queue.length} offline actions...`);
    } catch (e) {}

    const remainingQueue = [];
    let successCount = 0;
    let failedCount = 0;
    let serverDisconnected = false;

    for (let i = 0; i < queue.length; i++) {
      const req = queue[i];
      if (serverDisconnected) {
        remainingQueue.push(req);
        continue;
      }

      try {
        await axios({
          url: getBaseURL() + req.url,
          method: req.method,
          data: req.data,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        });
        successCount++;
      } catch (err) {
        console.error('Failed to sync offline action:', req, err);
        const status = err.response?.status;
        const errorMsgText = (err.response?.data?.message || err.message || '').toLowerCase();
        // Treat 409 Conflict, 429 Too Many Requests, and direct database locks/deadlocks as temporary, retrying them later
        const isLockError = errorMsgText.includes('lock') || errorMsgText.includes('deadlock') || errorMsgText.includes('conflict') || status === 409 || status === 429;
        const isNetworkOrServerError = !err.response || status >= 500 || status === 408 || isLockError;

        if (isNetworkOrServerError) {
          remainingQueue.push(req);
          serverDisconnected = true;
          try {
            const toast = useToastStore();
            toast.warning('Network sync paused. Temporary connection disruption or database busy.');
          } catch (e) {}
        } else {
          failedCount++;
          try {
            const toast = useToastStore();
            const errorMsg = err.response?.data?.message || 'Invalid request format';
            toast.error(`Sync discarded: ${errorMsg}`);
          } catch (e) {}
        }
      }
    }

    if (remainingQueue.length > 0) {
      localStorage.setItem('mamun_offline_queue', JSON.stringify(remainingQueue));
    } else {
      localStorage.removeItem('mamun_offline_queue');
    }

    try {
      const toast = useToastStore();
      if (successCount > 0 && remainingQueue.length === 0 && failedCount === 0) {
        toast.success('All offline actions synced successfully.');
      } else if (successCount > 0 || failedCount > 0) {
        toast.info(`Offline sync report: ${successCount} succeeded, ${failedCount} discarded, ${remainingQueue.length} pending.`);
      }
    } catch (e) {}

    window.dispatchEvent(new CustomEvent('offline-sync-completed'));
  });
}

export default api;
