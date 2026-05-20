"use client";

import { useEcho } from './useEcho';
import { toast } from 'sonner';

export const useStockSocket = (onUpdate?: (data: any) => void) => {
  return useEcho({
    channel: 'stock',
    isPrivate: true,
    events: {
      'StockSyncEvent': (e: any) => {
        toast.info(`Stock updated for part: ${e.part?.name ?? 'Unknown'}`);
        if (onUpdate) onUpdate(e);
      }
    }
  });
};
