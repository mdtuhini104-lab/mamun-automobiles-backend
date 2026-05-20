"use client";

import { useEffect, useRef, useState } from 'react';
import { useEchoContext } from '@/components/providers/echo-provider';
import { toast } from 'sonner';

export const useLiveSync = () => {
  const { echo, isConnected } = useEchoContext();
  const [activeUsers, setActiveUsers] = useState<any[]>([]);
  const subscribedRef = useRef(false);

  useEffect(() => {
    if (!echo || !isConnected) return;

    const presenceChannel = echo.join('live-sync');

    presenceChannel.here((users: any[]) => {
      setActiveUsers(users);
    })
    .joining((user: any) => {
      setActiveUsers((prev) => [...prev, user]);
      toast.success(`${user.name} has joined the workspace.`);
    })
    .leaving((user: any) => {
      setActiveUsers((prev) => prev.filter((u) => u.id !== user.id));
      toast.info(`${user.name} left the workspace.`);
    })
    .listen('UserActivityBroadcast', (e: any) => {
      // Optional: Handle custom activity events like 'user started typing', etc.
    });

    subscribedRef.current = true;

    return () => {
      if (subscribedRef.current) {
        echo.leave('live-sync');
        subscribedRef.current = false;
      }
    };
  }, [echo, isConnected]);

  return { activeUsers, isConnected };
};
