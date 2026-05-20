'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';

export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();

  const handleLogout = () => {
    localStorage.removeItem('token');
    router.push('/auth/login');
  };

  return (
    <div className="flex h-screen bg-slate-900 text-white">
      {/* Sidebar */}
      <div className="w-64 bg-slate-800 p-4 flex flex-col justify-between">
        <div>
          <h2 className="text-xl font-bold mb-8 text-indigo-400">Mamun ERP</h2>
          <nav className="space-y-2">
            <Link href="/dashboard" className="block p-2 hover:bg-slate-700 rounded transition-colors">Dashboard</Link>
            <Link href="/pos" className="block p-2 hover:bg-slate-700 rounded transition-colors">POS System</Link>
            <Link href="/inventory" className="block p-2 hover:bg-slate-700 rounded transition-colors">Inventory</Link>
            <Link href="/purchases" className="block p-2 hover:bg-slate-700 rounded transition-colors">Purchases</Link>
            <Link href="/invoices" className="block p-2 hover:bg-slate-700 rounded transition-colors">Invoices</Link>
            <Link href="/job-cards" className="block p-2 hover:bg-slate-700 rounded transition-colors">Job Cards</Link>
            <Link href="/reports" className="block p-2 hover:bg-slate-700 rounded transition-colors">Reports</Link>
            <Link href="/settings" className="block p-2 hover:bg-slate-700 rounded transition-colors">Settings</Link>
          </nav>
        </div>
        <button onClick={handleLogout} className="text-left p-2 hover:bg-slate-700 rounded text-red-400 transition-colors">
          Logout
        </button>
      </div>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Header */}
        <header className="bg-slate-800 p-4 border-b border-slate-700">
          <div className="flex justify-between items-center">
            <h1 className="text-xl font-semibold">Admin Panel</h1>
            <div className="flex items-center space-x-4">
              <span className="text-slate-300">Welcome, Admin</span>
            </div>
          </div>
        </header>

        {/* Content Area */}
        <main className="flex-1 overflow-auto p-6 bg-slate-900">
          {children}
        </main>
      </div>
    </div>
  );
}
