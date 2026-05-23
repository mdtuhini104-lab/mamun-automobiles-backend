import { onMounted, onUnmounted, ref } from 'vue';
import { getEcho } from '../services/echo';

export function useEcho(channelName, isPrivate = true, events = {}) {
  const isConnected = ref(false);
  let channelInstance = null;
  const echo = getEcho();

  onMounted(() => {
    if (!echo) return;

    channelInstance = isPrivate 
      ? echo.private(channelName) 
      : echo.channel(channelName);

    Object.entries(events).forEach(([eventName, callback]) => {
      channelInstance.listen(eventName, (data) => {
        callback(data);
      });
    });

    isConnected.value = true;
  });

  onUnmounted(() => {
    if (channelInstance) {
      Object.keys(events).forEach((eventName) => {
        channelInstance.stopListening(eventName);
      });
      echo.leave(channelName);
      isConnected.value = false;
    }
  });

  return { isConnected };
}
