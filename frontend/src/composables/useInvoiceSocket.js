import { useEcho } from './useEcho';

export function useInvoiceSocket(onUpdate) {
  return useEcho('invoices', true, {
    'InvoicePaymentUpdated': (data) => {
      if (onUpdate) onUpdate(data);
    }
  });
}
