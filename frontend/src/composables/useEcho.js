import { onMounted, onUnmounted, ref } from 'vue';
import { getEcho } from '../services/echo';

export function useEcho(channelName, isPrivate = true, events = {}, pollingConfig = null) {
  const isConnected = ref(false);
  const connectionState = ref('Disconnected'); // Connected, Reconnecting, Disconnected, Degraded
  let channelInstance = null;
  let pollingIntervalId = null;
  const echo = getEcho();

  const startPolling = () => {
    if (!pollingConfig || !pollingConfig.callback) return;
    
    // Check global polling feature flag
    if (import.meta.env.VITE_ENABLE_POLLING_FALLBACK === 'false') return;

    if (!pollingIntervalId) {
      console.warn(`[${channelName}] WebSocket degraded, starting polling fallback (Interval: ${pollingConfig.interval || 30}s)`);
      // Initial call
      pollingConfig.callback();
      // Setup interval
      pollingIntervalId = setInterval(() => {
        pollingConfig.callback();
      }, (pollingConfig.interval || 30) * 1000);
    }
  };

  const stopPolling = () => {
    if (pollingIntervalId) {
      clearInterval(pollingIntervalId);
      pollingIntervalId = null;
      console.info(`[${channelName}] WebSocket recovered, stopped polling.`);
    }
  };

  const handleStateChange = (state) => {
    if (state === 'connected') {
      stopPolling();
      if (connectionState.value === 'Reconnecting' || connectionState.value === 'Degraded') {
        connectionState.value = 'Recovering';
        isConnected.value = true;
        setTimeout(() => {
          if (connectionState.value === 'Recovering') {
            connectionState.value = 'Connected';
          }
        }, 3000);
      } else {
        connectionState.value = 'Connected';
        isConnected.value = true;
      }
    } else if (state === 'connecting') {
      connectionState.value = 'Reconnecting';
    } else if (state === 'unavailable' || state === 'failed') {
      connectionState.value = 'Degraded';
      isConnected.value = false;
      startPolling();
    } else {
      connectionState.value = 'Disconnected';
      isConnected.value = false;
    }
  };

  onMounted(() => {
    if (!echo) {
      // Realtime disabled or unsupported entirely, immediately fall back to polling
      connectionState.value = 'Degraded';
      isConnected.value = false;
      startPolling();
      return;
    }

    // Listen to connection state events if using Pusher/Reverb
    if (echo.connector && echo.connector.pusher && echo.connector.pusher.connection) {
      const connection = echo.connector.pusher.connection;
      handleStateChange(connection.state);
      connection.bind('state_change', (states) => {
        handleStateChange(states.current);
      });
    } else {
      // Default fallback
      connectionState.value = 'Connected';
      isConnected.value = true;
    }

    channelInstance = isPrivate 
      ? echo.private(channelName) 
      : echo.channel(channelName);

    Object.entries(events).forEach(([eventName, callback]) => {
      channelInstance.listen(eventName, (data) => {
        callback(data);
      });
    });
  });

  onUnmounted(() => {
    stopPolling();
    if (channelInstance && echo) {
      Object.keys(events).forEach((eventName) => {
        channelInstance.stopListening(eventName);
      });
      echo.leave(channelName);
      isConnected.value = false;
      connectionState.value = 'Disconnected';
    }
  });

  return { isConnected, connectionState };
}
