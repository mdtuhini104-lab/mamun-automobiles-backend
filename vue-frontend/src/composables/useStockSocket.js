import { useEcho } from './useEcho';

export function useStockSocket(onUpdate) {
  return useEcho('stock', true, {
    'StockSyncEvent': (data) => {
      if (onUpdate) onUpdate(data);
    }
  });
}
