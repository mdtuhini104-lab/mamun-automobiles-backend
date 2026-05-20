import { useEcho } from './useEcho';

export function usePurchaseSocket(onUpdate) {
  return useEcho('purchases', true, {
    'PurchaseStatusUpdated': (data) => {
      if (onUpdate) onUpdate(data);
    }
  });
}
