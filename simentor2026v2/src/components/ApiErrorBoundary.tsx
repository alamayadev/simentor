import React from 'react';
import { AlertCircle, RefreshCw } from 'lucide-react';

interface ApiErrorBoundaryProps {
  error: Error | unknown;
  onRetry?: () => void;
  title?: string;
  className?: string;
}

/**
 * Reusable error display component for failed API calls.
 * Shows error message with optional retry button.
 */
export function ApiErrorBoundary({ error, onRetry, title, className = '' }: ApiErrorBoundaryProps) {
  const message = error instanceof Error ? error.message : 'Terjadi kesalahan saat memuat data.';

  return (
    <div className={`flex flex-col items-center justify-center py-16 ${className}`}>
      <div className="h-14 w-14 rounded-2xl bg-red-100 dark:bg-red-500/10 flex items-center justify-center mb-4">
        <AlertCircle className="h-7 w-7 text-red-500" />
      </div>
      <p className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        {title || 'Gagal Memuat Data'}
      </p>
      <p className="text-xs text-gray-500 dark:text-gray-400 mb-4 max-w-md text-center">
        {message}
      </p>
      {onRetry && (
        <button
          onClick={onRetry}
          className="flex items-center gap-2 px-4 py-2 rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm"
        >
          <RefreshCw className="h-3.5 w-3.5" />
          Coba Lagi
        </button>
      )}
    </div>
  );
}
