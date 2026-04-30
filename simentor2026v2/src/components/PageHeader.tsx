import React, { useState, useEffect, useRef } from 'react';
import { useLocation, Link } from '@tanstack/react-router';
import { MODULES } from '../constants';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { ChevronDown, MoreHorizontal } from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { SubMenu } from '../types';

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

interface PageHeaderProps {
  title: string;
  description?: string;
  actions?: React.ReactNode;
}

export function PageHeader({ title, description, actions }: PageHeaderProps) {
  const location = useLocation();
  const currentPath = location.pathname;
  const activeModule = MODULES.find(m => currentPath.startsWith(m.path));
  
  const subMenus = activeModule?.subMenus || [];
  // Use slightly different visible count for a cleaner full-width look if needed, 
  // but keeping 3 for logic consistency.
  const visibleMenus = subMenus.slice(0, 3);
  const dropdownMenus = subMenus.slice(3);

  return (
    <div className="relative z-30 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
      <div className="flex-1">
        <h1 
          data-scan="judul halaman"
          className="text-[32px] lg:text-[40px] font-semibold tracking-tight leading-none text-gray-900 dark:text-white"
        >
          {title}
        </h1>
        {description && (
          <p className="mt-2 text-[14px] text-gray-600 dark:text-gray-400">
            {description}
          </p>
        )}
      </div>
      
      <div className="flex flex-col lg:flex-row lg:items-center gap-4 w-full lg:w-auto">
        {/* Module Sub-Menus as primary navigation actions */}
        {activeModule && (
          <div className="w-full lg:w-auto flex flex-col lg:flex-row lg:items-center gap-1 bg-white/60 dark:bg-white/5 glass-strong rounded-[24px] lg:rounded-full p-1.5 lg:p-1 border border-white/40 dark:border-white/10 shadow-sm overflow-visible">
            {visibleMenus.map((sub) => {
              if (sub.dropdown) {
                return <SubMenuWithDropdown key={sub.name} item={sub} currentPath={currentPath} />;
              }
              const isActive = currentPath === sub.path;
              const Icon = sub.icon;
              return (
                <Link
                  key={sub.path}
                  to={sub.path}
                  className={cn(
                    "flex items-center gap-2.5 lg:gap-1.5 px-4 lg:px-3 py-2.5 lg:py-1.5 rounded-xl lg:rounded-full transition-all text-[14px] lg:text-[13px] font-semibold whitespace-nowrap",
                    isActive
                      ? "bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-md"
                      : "text-gray-600 hover:bg-black/5 dark:text-gray-400 dark:hover:bg-white/10"
                  )}
                >
                  {Icon && <Icon className="h-4 w-4 lg:h-3.5 lg:w-3.5" />}
                  {sub.name}
                </Link>
              );
            })}

            {dropdownMenus.length > 0 && (
              <HeaderDropdown items={dropdownMenus} currentPath={currentPath} />
            )}
          </div>
        )}
        
        {/* Optional explicit actions (e.g. "Tambah" buttons) */}
        {actions && (
          <div data-scan="aksi halaman" className="flex items-center gap-2.5">
            {actions}
          </div>
        )}
      </div>
    </div>
  );
}

/**
 * Handles sub-menus that have a local dropdown.
 * On Mobile: Behaves like an accordion (expands vertical list).
 * On Desktop: Behaves like a standard absolute-positioned dropdown.
 */
function SubMenuWithDropdown({ item, currentPath }: { item: SubMenu; currentPath: string }) {
  const [isOpen, setIsOpen] = useState(false);
  const timeoutRef = useRef<any>(null);

  const handleMouseEnter = () => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);
    setIsOpen(true);
  };

  const handleMouseLeave = () => {
    if (window.innerWidth >= 1024) {
      timeoutRef.current = setTimeout(() => {
        setIsOpen(false);
      }, 150);
    } else {
      setIsOpen(false);
    }
  };

  const isActive = item.dropdown!.some(d => currentPath === d.path);
  const Icon = item.icon;

  return (
    <div 
      className="relative w-full lg:w-auto" 
      onMouseEnter={handleMouseEnter} 
      onMouseLeave={handleMouseLeave}
      onClick={() => {
        // Toggle on mobile click
        if (window.innerWidth < 1024) setIsOpen(!isOpen);
      }}
    >
      <button
        type="button"
        className={cn(
          "w-full lg:w-auto flex items-center justify-between lg:justify-start gap-2.5 lg:gap-1.5 px-4 lg:px-3 py-2.5 lg:py-1.5 rounded-xl lg:rounded-full transition-all text-[14px] lg:text-[13px] font-semibold whitespace-nowrap",
          isActive
            ? "bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-md"
            : "text-gray-600 hover:bg-black/5 dark:text-gray-400 dark:hover:bg-white/10"
        )}
      >
        <span className="flex items-center gap-2.5 lg:gap-1.5">
          {Icon && <Icon className="h-4 w-4 lg:h-3.5 lg:w-3.5" />}
          {item.name}
        </span>
        <ChevronDown className={cn("h-4 w-4 lg:h-3.5 lg:w-3.5 transition-transform", isOpen && "rotate-180")} />
      </button>

      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0, scale: 0.95 }}
            animate={{ opacity: 1, height: 'auto', scale: 1 }}
            exit={{ opacity: 0, height: 0, scale: 0.95 }}
            className={cn(
              "overflow-hidden transition-all duration-200 z-50",
              // Desktop: Absolute Popover
              "lg:absolute lg:top-full lg:left-0 lg:mt-2 lg:min-w-[200px] lg:rounded-2xl lg:border lg:border-white/40 lg:dark:border-white/10 lg:bg-white dark:lg:bg-gray-900 lg:p-1.5 lg:shadow-xl lg:glass-strong",
              // Bridge to prevent closing on hover gap
              "lg:before:absolute lg:before:-top-3 lg:before:left-0 lg:before:right-0 lg:before:h-4 lg:before:content-['']",
              // Mobile: Inline Accordion
              "lg:h-auto"
            )}
          >
            <div className="flex flex-col gap-0.5 p-1 lg:p-0">
              {item.dropdown!.map((sub) => (
                <Link
                  key={sub.path}
                  to={sub.path}
                  className={cn(
                    "flex items-center gap-3 rounded-lg px-3 py-2.5 lg:py-2 text-[13px] font-semibold transition-all",
                    currentPath === sub.path
                      ? "bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white"
                      : "text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5"
                  )}
                >
                  {sub.icon && <sub.icon className="h-4.5 w-4.5 lg:h-4 lg:w-4" />}
                  {sub.name}
                </Link>
              ))}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

/**
 * Handles the "Lainnya" overflow dropdown.
 * On Mobile: Behaves like an accordion (expands vertical list).
 * On Desktop: Behaves like a standard absolute-positioned dropdown.
 */
function HeaderDropdown({ items, currentPath }: { items: SubMenu[], currentPath: string }) {
  const [isOpen, setIsOpen] = useState(false);
  const timeoutRef = useRef<any>(null);

  const handleMouseEnter = () => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);
    setIsOpen(true);
  };

  const handleMouseLeave = () => {
    if (window.innerWidth >= 1024) {
      timeoutRef.current = setTimeout(() => {
        setIsOpen(false);
      }, 150);
    } else {
      setIsOpen(false);
    }
  };

  const isActive = items.some(item =>
    currentPath === item.path || (item.dropdown && item.dropdown.some(d => currentPath === d.path))
  );

  return (
    <div 
      className="relative w-full lg:w-auto" 
      onMouseEnter={handleMouseEnter} 
      onMouseLeave={handleMouseLeave}
      onClick={() => {
        if (window.innerWidth < 1024) setIsOpen(!isOpen);
      }}
    >
      <button
        type="button"
        className={cn(
          "w-full lg:w-auto flex items-center justify-between lg:justify-start gap-2.5 lg:gap-1.5 px-4 lg:px-3 py-2.5 lg:py-1.5 rounded-xl lg:rounded-full transition-all text-[14px] lg:text-[13px] font-semibold",
          isActive
            ? "bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-md"
            : "text-gray-600 hover:bg-black/5 dark:text-gray-400 dark:hover:bg-white/10"
        )}
      >
        <span className="flex items-center gap-2.5 lg:gap-1.5">
          <MoreHorizontal className="h-4 w-4 lg:h-4 lg:w-4" />
          Lainnya
        </span>
        <ChevronDown className={cn("h-4 w-4 lg:h-3.5 lg:w-3.5 transition-transform", isOpen && "rotate-180")} />
      </button>

      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0, scale: 0.95 }}
            animate={{ opacity: 1, height: 'auto', scale: 1 }}
            exit={{ opacity: 0, height: 0, scale: 0.95 }}
            className={cn(
              "overflow-hidden transition-all duration-200 z-50",
               // Desktop: Absolute Popover
              "lg:absolute lg:top-full lg:right-0 lg:mt-2 lg:min-w-[200px] lg:rounded-2xl lg:border lg:border-white/40 lg:dark:border-white/10 lg:bg-white dark:lg:bg-gray-900 lg:p-1.5 lg:shadow-xl lg:glass-strong shadow-gray-200/50 dark:shadow-black/50",
              // Bridge to prevent closing on hover gap
              "lg:before:absolute lg:before:-top-3 lg:before:left-0 lg:before:right-0 lg:before:h-4 lg:before:content-['']",
              // Mobile: Inline Accordion
              "lg:h-auto"
            )}
          >
            <div className="flex flex-col gap-0.5 p-1 lg:p-0">
              {items.map((item) =>
                item.dropdown ? (
                  <NestedAccordionItem key={item.name} item={item} currentPath={currentPath} />
                ) : (
                  <Link
                    key={item.path}
                    to={item.path}
                    className={cn(
                      "flex items-center gap-3 rounded-lg px-3 py-2.5 lg:py-2 text-[13px] font-semibold transition-all",
                      currentPath === item.path
                        ? "bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white"
                        : "text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5"
                    )}
                  >
                    {item.icon && <item.icon className="h-4.5 w-4.5 lg:h-4 lg:w-4" />}
                    {item.name}
                  </Link>
                )
              )}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

/**
 * Handles nested sub-menus within the "Lainnya" dropdown.
 */
function NestedAccordionItem({ item, currentPath }: { item: SubMenu; currentPath: string }) {
  const [isExpanded, setIsExpanded] = useState(false);
  const isActive = item.dropdown!.some(d => currentPath === d.path);
  const Icon = item.icon;

  return (
    <div 
      className="w-full"
      onMouseEnter={() => setIsExpanded(true)} 
      onMouseLeave={() => setIsExpanded(false)}
      onClick={(e) => {
        if (window.innerWidth < 1024) {
          e.stopPropagation(); // Prevent parent toggle
          setIsExpanded(!isExpanded);
        }
      }}
    >
      <button
        type="button"
        className={cn(
          "w-full flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 lg:py-2 text-[13px] font-semibold transition-all",
          isActive
            ? "bg-gray-800 text-white dark:bg-white/10 dark:text-white"
            : "text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5"
        )}
      >
        <span className="flex items-center gap-3">
          {Icon && <Icon className="h-4.5 w-4.5 lg:h-4 lg:w-4" />}
          {item.name}
        </span>
        <ChevronDown className={cn("h-4 w-4 lg:h-3.5 lg:w-3.5 transition-transform", isExpanded && "rotate-180")} />
      </button>

      <AnimatePresence>
        {isExpanded && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            className="overflow-hidden"
          >
            <div className="pl-6 lg:pl-10 py-1 space-y-0.5">
              {item.dropdown!.map((sub) => (
                <Link
                  key={sub.path}
                  to={sub.path}
                  className={cn(
                    "flex items-center gap-2.5 rounded-lg px-3 py-2 text-[12px] font-semibold transition-all",
                    currentPath === sub.path
                      ? "text-orange-600 dark:text-orange-400 font-bold"
                      : "text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5"
                  )}
                >
                  {sub.icon && <sub.icon className="h-4 w-4 lg:h-3.5 lg:w-3.5" />}
                  {sub.name}
                </Link>
              ))}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
