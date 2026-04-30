import React, { useCallback, useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import {
  addMonths,
  eachDayOfInterval,
  endOfMonth,
  endOfWeek,
  format,
  isSameDay,
  isSameMonth,
  parseISO,
  startOfMonth,
  startOfWeek,
  subMonths,
} from 'date-fns';
import { id } from 'date-fns/locale';
import { Calendar, ChevronLeft, ChevronRight } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';

type DatePickerProps = {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  className?: string;
  mode?: 'date' | 'month';
};

export function DatePicker({
  value,
  onChange,
  placeholder = 'Pilih tanggal',
  className = '',
  mode = 'date',
}: DatePickerProps) {
  const rootRef = useRef<HTMLDivElement | null>(null);
  const buttonRef = useRef<HTMLButtonElement | null>(null);
  const popupRef = useRef<HTMLDivElement | null>(null);
  const [popupStyle, setPopupStyle] = useState<React.CSSProperties>({});
  const selectedDate = useMemo(() => (value ? parseISO(value) : new Date()), [value]);
  const isValid = useMemo(() => value && !isNaN(selectedDate.getTime()), [value, selectedDate]);
  const [open, setOpen] = useState(false);
  const [currentMonth, setCurrentMonth] = useState<Date>(isValid ? selectedDate : new Date());

  const updatePosition = useCallback(() => {
    if (!buttonRef.current) return;
    const rect = buttonRef.current.getBoundingClientRect();
    setPopupStyle({
      position: 'fixed',
      top: rect.bottom + 8,
      left: rect.left,
      zIndex: 9999,
    });
  }, []);

  useEffect(() => {
    setCurrentMonth(isValid ? selectedDate : new Date());
  }, [isValid, selectedDate]);

  useLayoutEffect(() => {
    if (open) {
      updatePosition();
      window.addEventListener('resize', updatePosition);
      window.addEventListener('scroll', updatePosition, true);
      return () => {
        window.removeEventListener('resize', updatePosition);
        window.removeEventListener('scroll', updatePosition, true);
      };
    }
  }, [open, updatePosition]);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      const target = event.target as Node;
      if (
        !rootRef.current?.contains(target) &&
        !popupRef.current?.contains(target)
      ) {
        setOpen(false);
      }
    };

    const handleEscape = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setOpen(false);
    };

    document.addEventListener('mousedown', handleClickOutside);
    document.addEventListener('keydown', handleEscape);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleEscape);
    };
  }, []);

  const calendarDays = useMemo(() => {
    const start = startOfWeek(startOfMonth(currentMonth), { weekStartsOn: 1 });
    const end = endOfWeek(endOfMonth(currentMonth), { weekStartsOn: 1 });
    return eachDayOfInterval({ start, end });
  }, [currentMonth]);

  return (
    <div ref={rootRef} className={`relative ${className}`}>
      <button
        ref={buttonRef}
        type="button"
        onClick={() => setOpen((prev) => !prev)}
        className="flex h-11 w-full items-center justify-between rounded-xl border border-white/50 bg-white/70 px-4 text-left text-sm text-gray-900 outline-none transition hover:bg-white focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:hover:bg-white/[0.08]"
      >
        <span>{isValid ? format(selectedDate, mode === 'month' ? 'MMMM yyyy' : 'dd MMMM yyyy', { locale: id }) : placeholder}</span>
        <Calendar className="h-4 w-4 text-gray-400" />
      </button>

      {createPortal(
        <AnimatePresence>
          {open && (
            <motion.div
              ref={popupRef}
              initial={{ opacity: 0, y: 8, scale: 0.98 }}
              animate={{ opacity: 1, y: 0, scale: 1 }}
              exit={{ opacity: 0, y: 8, scale: 0.98 }}
              style={popupStyle}
              className="w-[288px] rounded-[20px] border border-white/50 bg-white/95 p-3 shadow-2xl backdrop-blur dark:border-white/10 dark:bg-gray-950/95"
            >
            <div className="mb-3 flex items-center justify-between">
              <button
                type="button"
                onClick={() => setCurrentMonth((prev) => subMonths(prev, 1))}
                className="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.06] dark:hover:text-white"
              >
                <ChevronLeft className="h-4 w-4" />
              </button>
              <div className="text-sm font-semibold text-gray-900 dark:text-white">
                {format(currentMonth, 'MMMM yyyy', { locale: id })}
              </div>
              <button
                type="button"
                onClick={() => setCurrentMonth((prev) => addMonths(prev, 1))}
                className="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.06] dark:hover:text-white"
              >
                <ChevronRight className="h-4 w-4" />
              </button>
            </div>

            {mode === 'month' ? (
              <div className="grid grid-cols-3 gap-2">
                {Array.from({ length: 12 }, (_, index) => {
                  const monthDate = new Date(currentMonth.getFullYear(), index, 1);
                  const selected =
                    selectedDate.getFullYear() === monthDate.getFullYear() &&
                    selectedDate.getMonth() === monthDate.getMonth();

                  return (
                    <button
                      key={monthDate.toISOString()}
                      type="button"
                      onClick={() => {
                        onChange(format(monthDate, 'yyyy-MM-01'));
                        setOpen(false);
                      }}
                      className={`flex h-10 items-center justify-center rounded-lg text-sm transition ${
                        selected
                          ? 'bg-sky-600 font-semibold text-white hover:bg-sky-700'
                          : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/[0.06]'
                      }`}
                    >
                      {format(monthDate, 'MMM', { locale: id })}
                    </button>
                  );
                })}
              </div>
            ) : (
              <>
                <div className="mb-2 grid grid-cols-7 gap-1 text-center text-[11px] font-medium uppercase tracking-wide text-gray-400">
                  {['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'].map((day) => (
                    <div key={day} className="py-1">
                      {day}
                    </div>
                  ))}
                </div>

                <div className="grid grid-cols-7 gap-1">
                  {calendarDays.map((day) => {
                    const selected = isSameDay(day, selectedDate);
                    const inMonth = isSameMonth(day, currentMonth);

                    return (
                      <button
                        key={day.toISOString()}
                        type="button"
                        onClick={() => {
                          onChange(format(day, 'yyyy-MM-dd'));
                          setOpen(false);
                        }}
                        className={`flex h-9 items-center justify-center rounded-lg text-sm transition ${
                          selected
                            ? 'bg-sky-600 font-semibold text-white hover:bg-sky-700'
                            : inMonth
                              ? 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/[0.06]'
                              : 'text-gray-300 hover:bg-gray-50 dark:text-gray-600 dark:hover:bg-white/[0.03]'
                        }`}
                      >
                        {format(day, 'd')}
                      </button>
                    );
                  })}
                </div>
              </>
            )}
          </motion.div>
          )}
        </AnimatePresence>,
        document.body,
      )}
    </div>
  );
}
