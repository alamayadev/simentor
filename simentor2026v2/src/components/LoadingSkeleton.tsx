import React from 'react';
import { Loader2 } from 'lucide-react';

interface LoadingSkeletonProps {
  message?: string;
  rows?: number;
  className?: string;
  variant?: 'table' | 'cards' | 'spinner' | 'button';
}

/**
 * Reusable loading skeleton for table/card views.
 * Supports table rows, card grid, and simple spinner variants.
 */
export function LoadingSkeleton({ 
  message = 'Memuat data...', 
  rows = 5,
  className = '',
  variant = 'table'
}: LoadingSkeletonProps) {
  if (variant === 'spinner') {
    return (
      <div className={`flex items-center justify-center py-20 ${className}`}>
        <Loader2 className="h-8 w-8 animate-spin text-orange-500" />
        <span className="ml-3 text-gray-500 dark:text-gray-400">{message}</span>
      </div>
    );
  }

  if (variant === 'button') {
    return (
      <div className={`flex items-center justify-center gap-2 ${className}`}>
        <Loader2 className="h-4 w-4 animate-spin text-current" />
        <span>{message}</span>
      </div>
    );
  }

  if (variant === 'cards') {
    return (
      <div className={`grid grid-cols-2 lg:grid-cols-4 gap-4 ${className}`}>
        {[1, 2, 3, 4].map((i) => (
          <div key={i} className="bg-white/65 dark:bg-white/[0.04] rounded-[20px] border border-white/50 dark:border-white/10 p-5 space-y-3 animate-pulse">
            <div className="h-3 w-20 bg-gray-200 dark:bg-white/10 rounded" />
            <div className="h-7 w-16 bg-gray-200 dark:bg-white/10 rounded" />
          </div>
        ))}
      </div>
    );
  }

  // Table variant
  return (
    <div className={className}>
      {/* Header skeleton */}
      <div className="flex gap-6 px-6 py-5 border-b border-gray-200/60 dark:border-white/10">
        {[1, 2, 3, 4, 5].map((i) => (
          <div key={i} className="h-3 w-20 bg-gray-200 dark:bg-white/10 rounded animate-pulse" />
        ))}
      </div>
      {/* Row skeletons */}
      {Array.from({ length: rows }, (_, i) => (
        <div key={i} className="flex gap-6 px-6 py-4 border-b border-gray-100/70 dark:border-white/5 animate-pulse">
          <div className="h-4 w-32 bg-gray-100 dark:bg-white/5 rounded" />
          <div className="h-4 w-24 bg-gray-100 dark:bg-white/5 rounded" />
          <div className="h-4 w-20 bg-gray-100 dark:bg-white/5 rounded" />
          <div className="h-4 w-16 bg-gray-100 dark:bg-white/5 rounded" />
          <div className="h-4 w-12 bg-gray-100 dark:bg-white/5 rounded" />
        </div>
      ))}
      {/* Footer skeleton */}
      <div className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10">
        <div className="h-3 w-40 bg-gray-100 dark:bg-white/5 rounded animate-pulse" />
        <div className="flex gap-2">
          <div className="h-8 w-24 bg-gray-100 dark:bg-white/5 rounded-full animate-pulse" />
          <div className="h-8 w-24 bg-gray-100 dark:bg-white/5 rounded-full animate-pulse" />
        </div>
      </div>
    </div>
  );
}
