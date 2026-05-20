'use client';

import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

export default function DashboardPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['dashboardData'],
    queryFn: async () => {
      const res = await api.get('/dashboard');
      return res.data.data;
    },
  });

  if (isLoading) {
    return <div className="text-white">Loading dashboard data...</div>;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Dashboard Overview</h1>
      
      {/* Stats Cards */}
      <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <p className="text-sm font-medium text-slate-400 truncate">Total Revenue</p>
          <p className="mt-1 text-3xl font-semibold text-white">${data?.total_revenue || 0}</p>
        </div>
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <p className="text-sm font-medium text-slate-400 truncate">Total Invoices</p>
          <p className="mt-1 text-3xl font-semibold text-white">{data?.total_invoices || 0}</p>
        </div>
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <p className="text-sm font-medium text-slate-400 truncate">Total Job Cards</p>
          <p className="mt-1 text-3xl font-semibold text-white">{data?.total_job_cards || 0}</p>
        </div>
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <p className="text-sm font-medium text-slate-400 truncate">Low Stock Items</p>
          <p className="mt-1 text-3xl font-semibold text-white">{data?.low_stock_items || 0}</p>
        </div>
      </div>

      {/* Charts Placeholder */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700 h-80 flex items-center justify-center">
          <span className="text-slate-400">Monthly Revenue Chart Placeholder</span>
        </div>
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700 h-80 flex items-center justify-center">
          <span className="text-slate-400">Job Card Status Chart Placeholder</span>
        </div>
      </div>
    </div>
  );
}
