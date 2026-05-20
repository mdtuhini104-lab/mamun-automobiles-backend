'use client';

export default function ReportsPage() {
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Reports & Analytics</h1>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <h2 className="text-lg font-semibold mb-2">Financial Reports</h2>
          <p className="text-slate-400 text-sm mb-4">View revenue, expenses, and profit reports.</p>
          <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded text-sm transition-colors">
            Generate
          </button>
        </div>
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <h2 className="text-lg font-semibold mb-2">Sales Reports</h2>
          <p className="text-slate-400 text-sm mb-4">View sales by date, customer, or part.</p>
          <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded text-sm transition-colors">
            Generate
          </button>
        </div>
        <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <h2 className="text-lg font-semibold mb-2">Inventory Reports</h2>
          <p className="text-slate-400 text-sm mb-4">View stock levels, valuation, and movement.</p>
          <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded text-sm transition-colors">
            Generate
          </button>
        </div>
      </div>
    </div>
  );
}
