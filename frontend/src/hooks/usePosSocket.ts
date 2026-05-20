"use client";

import { useEcho } from './useEcho';
import { toast } from 'sonner';

export const usePosSocket = (onUpdate?: (data: any) => void) => {
  return useEcho({
    channel: 'pos',
    isPrivate: true,
    events: {
      'PosUpdateEvent': (e: any) => {
        toast.success(`New POS transaction: #${e.transaction?.id ?? 'N/A'}`);
        if (onUpdate) onUpdate(e);
      }
    }
  });
};
