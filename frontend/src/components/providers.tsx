'use client';

import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useState } from 'react';
import { EchoProvider } from './providers/echo-provider';
import { Toaster } from 'sonner';

export function Providers({ children }: { children: React.ReactNode }) {
  const [queryClient] = useState(() => new QueryClient({
    defaultOptions: {
      queries: {
        staleTime: 1000 * 60 * 5, // 5 minutes
        gcTime: 1000 * 60 * 15, // 15 minutes (was cacheTime)
        refetchOnWindowFocus: false, // Prevents aggressive background refreshing while sockets are active
        retry: 1, // Only retry once by default (our axios interceptor also helps)
      },
    },
  }));
  return (
    <QueryClientProvider client={queryClient}>
      <EchoProvider>
        {children}
        <Toaster position="top-right" richColors />
      </EchoProvider>
    </QueryClientProvider>
  );
}

