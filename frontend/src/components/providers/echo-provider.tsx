"use client";

import React, { createContext, useContext, useEffect, useState } from 'react';
import Echo from 'laravel-echo';
import { initEcho } from '@/lib/echo';

interface EchoContextType {
  echo: Echo<any> | null;
  isConnected: boolean;
}

const EchoContext = createContext<EchoContextType>({ echo: null, isConnected: false });

export const EchoProvider = ({ children }: { children: React.ReactNode }) => {
  const [echoInstance, setEchoInstance] = useState<Echo<any> | null>(null);
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    const echo = initEcho();
    if (echo) {
      setEchoInstance(echo);
      
      echo.connector.pusher.connection.bind('connected', () => {
        setIsConnected(true);
      });
      
      echo.connector.pusher.connection.bind('disconnected', () => {
        setIsConnected(false);
      });
    }

    return () => {
      if (echo) {
        echo.disconnect();
      }
    };
  }, []);

  return (
    <EchoContext.Provider value={{ echo: echoInstance, isConnected }}>
      {children}
    </EchoContext.Provider>
  );
};

export const useEchoContext = () => useContext(EchoContext);
