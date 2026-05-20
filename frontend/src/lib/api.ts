import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add a request interceptor to add the token
api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  }
  return config;
});

// Add a response interceptor for retries
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const config = error.config;
    if (!config || !config.retry) {
      config.retry = 0;
    }
    
    // Only retry on 5xx errors or network errors
    const shouldRetry = (!error.response || error.response.status >= 500) && config.retry < 3;
    
    if (shouldRetry) {
      config.retry += 1;
      const backoff = new Promise((resolve) => setTimeout(resolve, config.retry * 1000));
      await backoff;
      return api(config);
    }
    return Promise.reject(error);
  }
);

export default api;
