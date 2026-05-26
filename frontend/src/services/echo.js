import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const initEcho = () => {
  const isProd = typeof window !== 'undefined' && window.location.hostname.includes('vercel.app');
  const wsHost = isProd ? 'mamun-automobiles-backend.vercel.app' : (import.meta.env.VITE_REVERB_HOST || 'localhost');
  const forceTLS = isProd ? true : ((import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https');
  const apiURL = isProd ? 'https://mamun-automobiles-backend.vercel.app/api' : (import.meta.env.VITE_API_URL || 'http://localhost:8000/api');

  return new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'mamun_erp_key',
    wsHost: wsHost,
    wsPort: isProd ? 443 : (import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8080),
    wssPort: isProd ? 443 : (import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8080),
    forceTLS: forceTLS,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: apiURL + '/broadcasting/auth',
    auth: {
      headers: {
        Authorization: 'Bearer ' + localStorage.getItem('token'),
      },
    },
  });
};

let echoInstance = null;

export const getEcho = () => {
  if (!echoInstance) {
    echoInstance = initEcho();
  }
  return echoInstance;
};
