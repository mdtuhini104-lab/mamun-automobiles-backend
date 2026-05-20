import { useEcho } from './useEcho';

export function usePosSocket(onUpdate) {
  return useEcho('pos', true, {
    'PosUpdateEvent': (data) => {
      if (onUpdate) onUpdate(data);
    }
  });
}
