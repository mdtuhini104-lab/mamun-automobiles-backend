import { useEcho } from './useEcho';

export function useDashboardSocket(onUpdate) {
  return useEcho('dashboard', true, {
    'DashboardStatsUpdated': (data) => {
      if (onUpdate) onUpdate(data);
    }
  });
}
