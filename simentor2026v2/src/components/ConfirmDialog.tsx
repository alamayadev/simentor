import React from 'react';
import { AlertTriangle, X } from 'lucide-react';

interface ConfirmDialogProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title?: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  variant?: 'danger' | 'warning' | 'info';
  isLoading?: boolean;
}

const variantStyles = {
  danger: 'bg-red-500 hover:bg-red-600 focus:ring-red-500',
  warning: 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-500',
  info: 'bg-blue-500 hover:bg-blue-600 focus:ring-blue-500',
};

const variantIconStyles = {
  danger: 'bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400',
  warning: 'bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400',
  info: 'bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400',
};

/**
 * Reusable confirmation dialog for delete/action confirmations.
 * Renders as a modal overlay with customizable variant.
 */
export function ConfirmDialog({
  open,
  onClose,
  onConfirm,
  title = 'Konfirmasi',
  message,
  confirmLabel = 'Hapus',
  cancelLabel = 'Batal',
  variant = 'danger',
  isLoading = false,
}: ConfirmDialogProps) {
  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      {/* Backdrop */}
      <div 
        className="absolute inset-0 bg-black/40 backdrop-blur-sm" 
        onClick={onClose}
      />
      
      {/* Dialog */}
      <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl p-6 max-w-md w-full mx-4">
        <button
          onClick={onClose}
          className="absolute top-4 right-4 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
        >
          <X className="h-4 w-4" />
        </button>

        <div className="flex items-start gap-4">
          <div className={`h-10 w-10 rounded-xl flex items-center justify-center shrink-0 ${variantIconStyles[variant]}`}>
            <AlertTriangle className="h-5 w-5" />
          </div>
          <div className="flex-1">
            <h3 className="text-base font-bold text-gray-900 dark:text-white mb-1">
              {title}
            </h3>
            <p className="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
              {message}
            </p>
          </div>
        </div>

        <div className="flex items-center justify-end gap-3 mt-6">
          <button
            onClick={onClose}
            disabled={isLoading}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm disabled:opacity-50"
          >
            {cancelLabel}
          </button>
          <button
            onClick={onConfirm}
            disabled={isLoading}
            className={`px-4 py-2 text-[12px] font-bold rounded-full text-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 ${variantStyles[variant]}`}
          >
            {isLoading ? 'Memproses...' : confirmLabel}
          </button>
        </div>
      </div>
    </div>
  );
}
