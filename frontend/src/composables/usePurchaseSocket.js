import { useEcho } from './useEcho';

export function usePurchaseSocket(onUpdate, fetchCallback = null) {
  return useEcho('purchases', true, {
    'PurchaseStatusUpdated': (data) => {
      if (onUpdate) onUpdate(data);
    }
  }, {
    callback: fetchCallback,
    interval: 30
  });
}
