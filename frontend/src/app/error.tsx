'use client'; // Error components must be Client Components

import { useEffect } from 'react';
import { Button } from '@/components/ui/button';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Log the error to an error reporting service in production
    console.error('Global Application Error:', error);
  }, [error]);

  return (
    <div className="flex h-screen w-full flex-col items-center justify-center bg-gray-50 p-4 text-center">
      <div className="max-w-md space-y-4 rounded-xl border bg-white p-8 shadow-sm">
        <h2 className="text-2xl font-bold tracking-tight text-red-600">Something went wrong!</h2>
        <p className="text-sm text-gray-500">
          An unexpected error occurred. Our team has been notified.
        </p>
        <Button
          onClick={
            // Attempt to recover by trying to re-render the segment
            () => reset()
          }
          className="mt-4"
        >
          Try again
        </Button>
      </div>
    </div>
  );
}
