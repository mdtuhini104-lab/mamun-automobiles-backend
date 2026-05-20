"use client";

import { useEcho } from './useEcho';
import { toast } from 'sonner';

export const useJobCardSocket = (jobCardId: number | string, onUpdate?: (data: any) => void) => {
  return useEcho({
    channel: `jobcard.${jobCardId}`,
    isPrivate: true,
    events: {
      'JobCardStatusUpdated': (e: any) => {
        toast.success(`Job Card #${jobCardId} status updated to: ${e.jobCard?.status}`);
        if (onUpdate) onUpdate(e);
      }
    }
  });
};
