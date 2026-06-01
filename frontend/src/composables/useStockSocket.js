import { useEcho } from './useEcho';

export function useStockSocket(onUpdate, fetchCallback = null) {
  return useEcho('stock', true, {
    'StockSyncEvent': (data) => {
      if (onUpdate) onUpdate(data);
    }
  }, {
    callback: fetchCallback,
    interval: 30
  });
}
