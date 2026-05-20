'use client';

import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

export default function NotificationsPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['notificationsData'],
    queryFn: async () => {
      const res = await api.get('/notifications');
      return res.data.data;
    },
  });

  if (isLoading) {
    return <div className="text-white">Loading notifications...</div>;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Notifications</h1>
      
      <div className="space-y-4">
        {data?.map((notification: any) => (
          <div key={notification.id} className={`p-4 rounded-lg shadow-lg border transition-colors ${
            notification.read_at ? 'bg-slate-800 border-slate-700' : 'bg-slate-700 border-indigo-500'
          }`}>
            <div className="flex justify-between items-start">
              <div>
                <p className="text-white">{notification.data?.message || 'New notification'}</p>
                <p className="text-xs text-slate-400 mt-1">
                  {new Date(notification.created_at).toLocaleString()}
                </p>
              </div>
              {!notification.read_at && (
                <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-500 text-white">
                  New
                </span>
              )}
            </div>
          </div>
        ))}
        {(!data || data.length === 0) && (
          <div className="bg-slate-800 p-6 rounded-lg text-center text-slate-400 border border-slate-700">
            No notifications found.
          </div>
        )}
      </div>
    </div>
  );
}
