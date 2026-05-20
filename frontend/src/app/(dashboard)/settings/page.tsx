'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import api from '@/lib/api';
import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';

export default function SettingsPage() {
  const queryClient = useQueryClient();
  const [settings, setSettings] = useState<Record<string, string>>({});

  const { data, isLoading } = useQuery({
    queryKey: ['settingsData'],
    queryFn: async () => {
      const res = await api.get('/settings');
      return res.data.data;
    },
  });

  useEffect(() => {
    if (data) {
      setSettings(data);
    }
  }, [data]);

  const mutation = useMutation({
    mutationFn: async (newSettings: Record<string, string>) => {
      const res = await api.put('/settings', newSettings);
      return res.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['settingsData'] });
      alert('Settings updated successfully!');
    },
    onError: (error: any) => {
      alert(error.response?.data?.message || 'Failed to update settings');
    }
  });

  const handleChange = (key: string, value: string) => {
    setSettings((prev) => ({ ...prev, [key]: value }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    mutation.mutate(settings);
  };

  if (isLoading) {
    return <div className="text-white">Loading settings...</div>;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">System Settings</h1>
      
      <div className="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700 max-w-2xl">
        <form onSubmit={handleSubmit} className="space-y-6">
          {Object.entries(settings).map(([key, value]) => (
            <div key={key}>
              <label className="block text-sm font-medium text-slate-200 capitalize mb-1">
                {key.replace(/_/g, ' ')}
              </label>
              <input
                type="text"
                value={value || ''}
                onChange={(e) => handleChange(key, e.target.value)}
                className="block w-full rounded-md border-0 bg-slate-700 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
              />
            </div>
          ))}

          {Object.keys(settings).length === 0 && (
            <div className="text-slate-400 text-sm">
              No settings found. Add some in the database to get started.
            </div>
          )}
          
          <div>
            <Button 
              type="submit" 
              className="bg-indigo-600 hover:bg-indigo-500 text-white" 
              disabled={mutation.isPending}
            >
              {mutation.isPending ? 'Saving...' : 'Save Settings'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}
