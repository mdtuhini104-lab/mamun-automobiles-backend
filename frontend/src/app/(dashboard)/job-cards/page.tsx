'use client';

import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

export default function JobCardsPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['jobCardsData'],
    queryFn: async () => {
      const res = await api.get('/job-cards');
      return res.data.data;
    },
  });

  if (isLoading) {
    return <div className="text-white">Loading job cards...</div>;
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold">Job Card Management</h1>
        <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
          New Job Card
        </button>
      </div>
      
      {/* Job Cards Table */}
      <div className="bg-slate-800 rounded-lg shadow-lg border border-slate-700 overflow-hidden">
        <table className="min-w-full divide-y divide-slate-700">
          <thead className="bg-slate-700">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Job No</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Vehicle</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Customer</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-700">
            {data?.map((jobCard: any) => (
              <tr key={jobCard.id} className="hover:bg-slate-750 transition-colors">
                <td className="px-6 py-4 whitespace-nowrap text-sm text-white">{jobCard.job_number}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{jobCard.vehicle?.plate_number}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{jobCard.customer?.name}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm">
                  <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                    jobCard.status === 'completed' ? 'bg-green-500/20 text-green-400' :
                    jobCard.status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' :
                    'bg-blue-500/20 text-blue-400'
                  }`}>
                    {jobCard.status}
                  </span>
                </td>
              </tr>
            ))}
            {(!data || data.length === 0) && (
              <tr>
                <td colSpan={4} className="px-6 py-4 text-center text-sm text-slate-400">
                  No job cards found.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
