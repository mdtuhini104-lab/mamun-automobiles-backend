import { useEcho } from './useEcho';

export function useDashboardSocket(onUpdate, fetchDashboardData = null) {
  return useEcho('dashboard', true, {
    'DashboardStatsUpdated': (data) => {
      if (onUpdate) onUpdate(data);
    }
  }, {
    callback: fetchDashboardData,
    interval: 30
  });
}
