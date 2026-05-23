import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const initEcho = () => {
  return new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || process.env.VITE_REVERB_APP_KEY || 'app-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: (import.meta.env.VITE_API_URL || 'http://localhost:8000/api') + '/broadcasting/auth',
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
