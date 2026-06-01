import { useEcho } from './useEcho';

export function useInvoiceSocket(onUpdate, fetchCallback = null) {
  return useEcho('invoices', true, {
    'InvoicePaymentUpdated': (data) => { if (onUpdate) onUpdate(data); }
  }, {
    callback: fetchCallback,
    interval: 30
  });
}
