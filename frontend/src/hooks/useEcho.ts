"use client";

import { useEffect, useRef } from 'react';
import { useEchoContext } from '@/components/providers/echo-provider';
import { toast } from 'sonner';

interface UseEchoProps {
  channel: string;
  isPrivate?: boolean;
  events: {
    [eventName: string]: (data: any) => void;
  };
}

export const useEcho = ({ channel, isPrivate = true, events }: UseEchoProps) => {
  const { echo, isConnected } = useEchoContext();
  const subscribedRef = useRef(false);

  useEffect(() => {
    if (!echo || !isConnected) return;

    const channelInstance = isPrivate 
      ? echo.private(channel) 
      : echo.channel(channel);

    Object.entries(events).forEach(([eventName, callback]) => {
      channelInstance.listen(eventName, (data: any) => {
        // Automatically deduplicate or log if needed
        callback(data);
      });
    });

    subscribedRef.current = true;

    return () => {
      if (subscribedRef.current) {
        // Only stop listening to specific events or leave channel if it's the last listener
        Object.keys(events).forEach((eventName) => {
          channelInstance.stopListening(eventName);
        });
        echo.leave(channel);
        subscribedRef.current = false;
      }
    };
  }, [echo, isConnected, channel, isPrivate, events]);

  return { isConnected };
};
