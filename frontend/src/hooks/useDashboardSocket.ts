"use client";

import { useEcho } from './useEcho';

export const useDashboardSocket = (onUpdate?: (data: any) => void) => {
  return useEcho({
    channel: 'dashboard',
    isPrivate: true,
    events: {
      'DashboardStatsUpdated': (e: any) => {
        // We typically don't show a toast for every dashboard tick,
        // just silently update the state using the callback
        if (onUpdate) onUpdate(e);
      }
    }
  });
};
