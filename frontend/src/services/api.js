import axios from 'axios';
import { useToastStore } from '../stores/toast';
import router from '../router';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, error => {
  return Promise.reject(error);
});

api.interceptors.response.use(
  response => response,
  error => {
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

export default api;
