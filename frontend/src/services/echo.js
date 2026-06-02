import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const initEcho = () => {
  if (import.meta.env.VITE_ENABLE_REALTIME === 'false') {
    console.warn('Realtime is disabled via environment variables.');
    return null;
  }

  let apiURL = import.meta.env.VITE_API_URL;
  if (!apiURL) {
    const isProd = typeof window !== 'undefined' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
    apiURL = isProd ? 'https://mamun-automobiles-backend-production.up.railway.app/api' : 'http://localhost:8000/api';
  }
  const driver = import.meta.env.VITE_BROADCAST_DRIVER || 'reverb';

  if (driver === 'none' || driver === 'log') {
    console.warn(`Realtime is disabled via VITE_BROADCAST_DRIVER=${driver}.`);
    return null;
  }

  let config = {};

  if (driver === 'pusher') {
    config = {
      broadcaster: 'pusher',
      key: import.meta.env.VITE_PUSHER_APP_KEY,
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
      wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
      wsPort: import.meta.env.VITE_PUSHER_PORT || 80,
      wssPort: import.meta.env.VITE_PUSHER_PORT || 443,
      forceTLS: (import.meta.env.VITE_PUSHER_SCHEME || 'https') === 'https',
      enabledTransports: ['ws', 'wss'],
    };
  } else {
    // Default to Reverb
    const wsHost = import.meta.env.VITE_REVERB_HOST || 'localhost';
    const forceTLS = (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https';
    
    config = {
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY || 'mamun_erp_key',
      wsHost: wsHost,
      wsPort: import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8080,
      wssPort: import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8080,
      forceTLS: forceTLS,
      enabledTransports: ['ws', 'wss'],
    };
  }

  const echo = new Echo({
    ...config,
    authEndpoint: apiURL + '/broadcasting/auth',
    auth: {
      headers: {
        Authorization: 'Bearer ' + localStorage.getItem('token'),
      },
    },
    // Heartbeat configuration for fast stale connection detection
    activityTimeout: 20000,
    pongTimeout: 8000,
  });

  // Setup connection monitoring for fallback
  echo.connector.pusher.connection.bind('state_change', (states) => {
    if (states.current === 'unavailable' || states.current === 'failed') {
      console.warn('WebSocket connection failed. Emitting fallback event.');
      window.dispatchEvent(new CustomEvent('websocket:fallback', { detail: { state: states.current } }));
    }
  });

  return echo;
};

let echoInstance = null;

export const getEcho = () => {
  if (!echoInstance) {
    echoInstance = initEcho();
  }
  return echoInstance;
};
