'use client';

import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

export default function InventoryPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['inventoryData'],
    queryFn: async () => {
      const res = await api.get('/parts');
      return res.data.data;
    },
  });

  if (isLoading) {
    return <div className="text-white">Loading inventory...</div>;
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold">Inventory Management</h1>
        <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
          Add Part
        </button>
      </div>
      
      {/* Parts Table */}
      <div className="bg-slate-800 rounded-lg shadow-lg border border-slate-700 overflow-hidden">
        <table className="min-w-full divide-y divide-slate-700">
          <thead className="bg-slate-700">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Name</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">SKU</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Stock</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Price</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-700">
            {data?.map((part: any) => (
              <tr key={part.id} className="hover:bg-slate-750 transition-colors">
                <td className="px-6 py-4 whitespace-nowrap text-sm text-white">{part.name}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{part.sku}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{part.stock}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-400">${part.price}</td>
              </tr>
            ))}
            {(!data || data.length === 0) && (
              <tr>
                <td colSpan={4} className="px-6 py-4 text-center text-sm text-slate-400">
                  No parts found.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
