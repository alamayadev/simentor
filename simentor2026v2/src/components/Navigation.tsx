import React, { useEffect, useRef, useState } from 'react';
import { Link, useLocation, useNavigate } from '@tanstack/react-router';
import { ChevronDown, Home, LogIn, LogOut, Menu, MoreHorizontal, Search, Settings, User, X } from 'lucide-react';
import { MODULES } from '../constants';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { ThemeToggle } from './ThemeToggle';
import { useAuth } from '../hooks/useAuth';
import { canAccessPath } from '../lib/authz';
import type { SubMenu } from '../types';

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

function isPresent<T>(value: T | null | undefined): value is T {
  return value != null;
}

export function Navigation() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const userMenuRef = useRef<HTMLDivElement | null>(null);
  const navigate = useNavigate();
  const location = useLocation();
  const currentPath = location.pathname;
  const { user, isAuthenticated, logout } = useAuth();
  const visibleModules = MODULES
    .map((module) => {
      const filteredSubMenus = module.subMenus
        .map((sub) => {
          if (sub.dropdown) {
            const filteredDropdown = sub.dropdown.filter((item) => canAccessPath(user, item.path));
            return filteredDropdown.length > 0 ? { ...sub, dropdown: filteredDropdown } : null;
          }

          return canAccessPath(user, sub.path) ? sub : null;
        })
        .filter(isPresent);

      return filteredSubMenus.length > 0
        ? { ...module, subMenus: filteredSubMenus as typeof module.subMenus }
        : null;
    })
    .filter(isPresent);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (!userMenuRef.current?.contains(event.target as Node)) {
        setIsUserMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleLogout = async () => {
    setIsUserMenuOpen(false);
    await logout();
    navigate({ to: '/login' });
  };

  const displayName = user?.name || 'Pengguna';

  return (
    <header className="flex items-center justify-between gap-4">
      {/* Logo */}
      <Link to="/" className="flex items-center gap-2.5 group">
        <div className="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/20 group-hover:shadow-orange-500/40 transition-shadow overflow-hidden">
          <img src="/icon.svg" alt="Simentor" className="w-6 h-6" />
        </div>
        <span className="font-semibold text-[17px] tracking-tight text-gray-900 dark:text-white">Simentor</span>
      </Link>

      {/* Center Nav Pill — only when authenticated */}
      {isAuthenticated && (
        <div className="hidden md:flex items-center gap-1.5 bg-white/60 dark:bg-white/5 glass-strong rounded-full px-2 py-1.5 border border-white/40 dark:border-white/10 shadow-sm">
          {visibleModules.map((module) => {
            const isActive = currentPath.startsWith(module.path);
            const defaultPath = module.subMenus.find(s => !s.dropdown)?.path || module.subMenus[0]?.dropdown?.[0]?.path || module.subMenus[0]?.path;
            const FirstIcon = module.subMenus[0].icon || Menu;

            return (
              <div key={module.id} className="relative group">
                <Link
                  to={defaultPath}
                  className={cn(
                    "flex items-center gap-1.5 px-3 py-1.5 rounded-full transition-all text-sm font-medium",
                    isActive
                      ? "bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-md"
                      : "text-gray-600 hover:bg-black/5 dark:text-gray-400 dark:hover:bg-white/10"
                  )}
                >
                  {isActive ? (
                    <>
                      <FirstIcon className="h-4 w-4" />
                      {module.name}
                    </>
                  ) : (
                    <FirstIcon className="h-4.5 w-4.5" />
                  )}
                </Link>
                {!isActive && (
                  <div className="absolute top-full left-1/2 -translate-x-1/2 mt-2 px-2.5 py-1 rounded-lg bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-[11px] font-medium whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none shadow-lg z-50">
                    {module.name}
                    <div className="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-900 dark:bg-white rotate-45"></div>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {/* Right Controls */}
      <div className="flex items-center gap-2">
        <ThemeToggle />
        {isAuthenticated && (
          <button className="p-2.5 rounded-full bg-white/60 dark:bg-white/5 glass border border-white/40 dark:border-white/10 hover:bg-white/80 dark:hover:bg-white/10">
            <Search className="h-4.5 w-4.5 text-gray-600 dark:text-gray-300" />
          </button>
        )}

        {isAuthenticated ? (
          <div ref={userMenuRef} className="relative hidden sm:block">
            <button
              type="button"
              onClick={() => setIsUserMenuOpen((prev) => !prev)}
              className="flex items-center gap-1.5 rounded-full border border-white/40 bg-white/60 py-1.5 pl-2 pr-3 glass transition hover:bg-white/80 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
            >
              <div className="h-6 w-6 overflow-hidden rounded-full border border-white/40 shadow-sm">
                <img
                  src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=80&h=80&fit=crop"
                  className="h-full w-full object-cover"
                  alt="avatar"
                  referrerPolicy="no-referrer"
                />
              </div>
              <span className="text-[13px] font-medium text-gray-700 dark:text-gray-200">{displayName}</span>
              <ChevronDown className={cn("h-4 w-4 text-gray-400 transition-transform", isUserMenuOpen && "rotate-180")} />
            </button>

            {isUserMenuOpen && (
                <div className="absolute right-0 top-full z-50 mt-2 w-52 overflow-hidden rounded-[20px] border border-white/50 bg-white/95 p-1.5 shadow-xl glass-strong dark:border-white/10 dark:bg-gray-900/95">
                  <a
                    href="/profil"
                    onClick={() => setIsUserMenuOpen(false)}
                    className="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white"
                  >
                    <User className="h-4 w-4" />
                    Profil
                  </a>
                  <button
                    type="button"
                    onClick={() => { setIsUserMenuOpen(false); void navigate({ to: '/pengaturan-pengguna' as any }); }}
                    className="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white"
                  >
                    <Settings className="h-4 w-4" />
                    Pengaturan
                  </button>
                  <Link
                    to="/"
                    onClick={() => setIsUserMenuOpen(false)}
                    className="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white"
                  >
                    <Home className="h-4 w-4" />
                    Beranda
                  </Link>
                  <div className="my-1 border-t border-gray-200/70 dark:border-white/10" />
                  <button
                    type="button"
                    onClick={handleLogout}
                    className="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[13px] font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                  >
                    <LogOut className="h-4 w-4" />
                    Keluar
                  </button>
                </div>
              )}
          </div>
        ) : (
          <Link
            to="/login"
            className="flex items-center gap-2 rounded-full bg-gradient-to-r from-orange-400 to-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-orange-500/25 transition-all hover:from-orange-500 hover:to-orange-600"
          >
            <LogIn className="h-4 w-4" />
            <span className="hidden sm:inline">Masuk</span>
          </Link>
        )}

        {/* Mobile menu button — only when authenticated */}
        {isAuthenticated && (
          <button
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="rounded-lg p-2 lg:hidden text-gray-500 hover:bg-white/60 dark:hover:bg-white/10 glass"
          >
            {isMobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
          </button>
        )}
      </div>

      {/* Mobile Menu — only when authenticated */}
      {isAuthenticated && isMobileMenuOpen && (
            <div className="fixed inset-x-4 top-20 z-50 overflow-y-auto no-scrollbar max-h-[calc(100vh-120px)] rounded-[24px] border border-white/50 bg-white/90 p-4 shadow-2xl glass-strong dark:border-white/10 dark:bg-gray-900/90 lg:hidden">
              <div className="space-y-4 pb-12">
                {visibleModules.map(module => (
                  <div key={module.id}>
                    <div className="px-2 text-[10px] font-bold uppercase tracking-widest text-orange-500 mb-1">{module.name}</div>
                    <div className="grid grid-cols-2 gap-1 text-sm">
                      {module.subMenus.flatMap(sub => {
                        if (sub.dropdown) {
                          return sub.dropdown.map(d => (
                            <Link
                              key={d.path}
                              to={d.path}
                              onClick={() => setIsMobileMenuOpen(false)}
                              className={cn(
                                "flex items-center gap-2 p-2 rounded-xl transition-colors",
                                currentPath === d.path ? "bg-orange-500 text-white" : "hover:bg-gray-100 dark:hover:bg-white/5"
                              )}
                            >
                              {sub.icon && <sub.icon className="h-4 w-4" />}
                              {d.name}
                            </Link>
                          ));
                        }
                        return (
                          <Link
                            key={sub.path}
                            to={sub.path}
                            onClick={() => setIsMobileMenuOpen(false)}
                            className={cn(
                              "flex items-center gap-2 p-2 rounded-xl transition-colors",
                              currentPath === sub.path ? "bg-orange-500 text-white" : "hover:bg-gray-100 dark:hover:bg-white/5"
                            )}
                          >
                            {sub.icon && <sub.icon className="h-4 w-4" />}
                            {sub.name}
                          </Link>
                        );
                      })}
                    </div>
                  </div>
                ))}
              </div>
            </div>
      )}
    </header>
  );
}

export function SubMenuLink({ item, currentPath }: { item: SubMenu, currentPath: string }) {
  const Icon = item.icon;
  const active = currentPath === item.path;

  if (item.dropdown) {
    return <DropdownMenu title={item.name} items={item.dropdown} currentPath={currentPath} icon={Icon} />;
  }

  return (
    <Link
      to={item.path}
      className={cn(
        "flex items-center gap-1 whitespace-nowrap text-[0.85rem] font-bold transition-all",
        active
          ? "text-primary-dark"
          : "text-text-light hover:text-primary-dark"
      )}
    >
      {Icon && <Icon className="h-4 w-4" />}
      {item.name}
    </Link>
  );
}

function DropdownMenu({ title, items, currentPath, icon: Icon }: { title: string, items: SubMenu[], currentPath: string, icon?: any }) {
  const [isOpen, setIsOpen] = useState(false);
  const timeoutRef = useRef<any>(null);

  const handleMouseEnter = () => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);
    setIsOpen(true);
  };

  const handleMouseLeave = () => {
    timeoutRef.current = setTimeout(() => {
      setIsOpen(false);
    }, 150);
  };

  const isActive = items.some(item => currentPath === item.path);

  return (
    <div className="relative" onMouseEnter={handleMouseEnter} onMouseLeave={handleMouseLeave}>
      <button
        className={cn(
          "flex items-center gap-1 whitespace-nowrap text-[0.85rem] font-bold transition-all",
          isActive
             ? "text-primary-dark"
             : "text-text-light hover:text-primary-dark"
        )}
      >
        {Icon ? <Icon className="h-4 w-4" /> : <MoreHorizontal className="h-4 w-4" />}
        {title}
        <ChevronDown className={cn("h-4 w-4 transition-transform", isOpen && "rotate-180")} />
      </button>

      {isOpen && (
          <div className="absolute left-0 mt-1 min-w-[200px] overflow-hidden rounded-xl border border-border bg-surface p-2 shadow-xl lg:before:absolute lg:before:-top-2 lg:before:left-0 lg:before:right-0 lg:before:h-3 lg:before:content-['']">
            {items.map((item) => (
              <Link
                key={item.path}
                to={item.path}
                className={cn(
                  "flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors",
                  currentPath === item.path
                    ? "bg-subnav text-primary"
                    : "text-text hover:bg-[#f9f8f4] hover:text-primary"
                )}
              >
                {item.icon && <item.icon className="h-4 w-4" />}
                {item.name}
              </Link>
            ))}
          </div>
        )}
    </div>
  );
}
