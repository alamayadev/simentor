import React, { useState, useEffect } from 'react';
import { motion } from 'motion/react';
import { PageHeader } from '../components/PageHeader';
import { useTheme } from '../components/ThemeProvider';

type AppWidth = 'narrow' | 'wide';
type ThemeColor = 'orange' | 'indigo' | 'green';

const colorThemes: Record<ThemeColor, {
  primary: string;
  primaryDark: string;
  accent: string;
  label: string;
  preview: string;
  ring: string;
}> = {
  orange: { primary: '#f97316', primaryDark: '#ea580c', accent: '#f59e0b', label: 'Oranye', preview: 'bg-orange-500', ring: 'bg-orange-500' },
  indigo: { primary: '#6366f1', primaryDark: '#4f46e5', accent: '#818cf8', label: 'Indigo', preview: 'bg-indigo-500', ring: 'bg-indigo-500' },
  green: { primary: '#22c55e', primaryDark: '#16a34a', accent: '#4ade80', label: 'Hijau', preview: 'bg-green-500', ring: 'bg-green-500' },
};

function RadioOption({
  checked,
  onClick,
  children,
}: {
  checked: boolean;
  onClick: () => void;
  children: React.ReactNode;
}) {
  return (
    <button
      type="button"
      role="radio"
      aria-checked={checked}
      onClick={onClick}
      className="flex items-center gap-2.5 w-full text-left group"
    >
      <span className="relative aspect-square size-4 shrink-0 rounded-full border border-gray-300 dark:border-white/20 shadow-sm transition-all">
        {checked && (
          <span className="absolute inset-0 flex items-center justify-center">
            <span className="size-2 rounded-full bg-orange-500 dark:bg-orange-400 theme-color-dot" />
          </span>
        )}
      </span>
      <span className="text-sm leading-none font-medium select-none text-gray-700 dark:text-gray-300 flex items-center gap-2">
        {children}
      </span>
    </button>
  );
}

function applyThemeColor(color: ThemeColor) {
  const colors = colorThemes[color];
  const root = document.documentElement;
  root.style.setProperty('--color-primary', colors.primary);
  root.style.setProperty('--color-primary-dark', colors.primaryDark);
  root.style.setProperty('--color-accent', colors.accent);

  document.querySelectorAll('.theme-color-dot').forEach((dot) => {
    (dot as HTMLElement).style.backgroundColor = colors.primary;
  });
}

export function PengaturanPengguna() {
  const { theme, setTheme } = useTheme();
  const [appWidth, setAppWidthState] = useState<AppWidth>(
    () => (localStorage.getItem('app-width') as AppWidth) || 'narrow'
  );
  const [themeColor, setThemeColorState] = useState<ThemeColor>(
    () => (localStorage.getItem('theme-color') as ThemeColor) || 'orange'
  );

  const setAppWidth = (w: AppWidth) => {
    setAppWidthState(w);
    localStorage.setItem('app-width', w);
    window.dispatchEvent(new Event('app-width-change'));
  };

  const setThemeColor = (c: ThemeColor) => {
    setThemeColorState(c);
    localStorage.setItem('theme-color', c);
    applyThemeColor(c);
  };

  useEffect(() => {
    applyThemeColor(themeColor);
  }, []);

  const cards = [
    {
      title: 'Lebar Aplikasi',
      desc: 'Pilih lebar tampilan aplikasi di layar besar.',
      cols: 'grid-cols-2' as const,
      content: (
        <>
          <RadioOption checked={appWidth === 'narrow'} onClick={() => setAppWidth('narrow')}>
            <svg className="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2" />
              <line x1="4.5" y1="3" x2="4.5" y2="21" strokeDasharray="0" />
              <line x1="19.5" y1="3" x2="19.5" y2="21" strokeDasharray="0" />
            </svg>
            4/5 Layar (Sempit)
          </RadioOption>
          <RadioOption checked={appWidth === 'wide'} onClick={() => setAppWidth('wide')}>
            <svg className="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="2" y="3" width="20" height="18" rx="2" />
            </svg>
            Lebar Penuh
          </RadioOption>
        </>
      ),
      span: '',
    },
    {
      title: 'Mode Terang/Gelap',
      desc: 'Pilih mode tampilan yang sesuai dengan preferensi Anda.',
      cols: 'grid-cols-2' as const,
      content: (
        <>
          <RadioOption checked={theme === 'light'} onClick={() => setTheme('light')}>
            <svg className="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <circle cx="12" cy="12" r="4" /><path d="M12 2v2" /><path d="M12 20v2" /><path d="m4.93 4.93 1.41 1.41" /><path d="m17.66 17.66 1.41 1.41" /><path d="M2 12h2" /><path d="M20 12h2" /><path d="m6.34 17.66-1.41 1.41" /><path d="m19.07 4.93-1.41 1.41" />
            </svg>
            Terang
          </RadioOption>
          <RadioOption checked={theme === 'dark'} onClick={() => setTheme('dark')}>
            <svg className="w-4 h-4 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            </svg>
            Gelap
          </RadioOption>
        </>
      ),
      span: '',
    },
    {
      title: 'Warna Utama Tema',
      desc: 'Pilih skema warna utama untuk aplikasi.',
      cols: 'grid-cols-3' as const,
      content: (
        <>
          {(Object.keys(colorThemes) as ThemeColor[]).map((color) => (
            <RadioOption key={color} checked={themeColor === color} onClick={() => setThemeColor(color)}>
              <div className={`w-4 h-4 rounded-full ${colorThemes[color].preview}`} />
              <span>{colorThemes[color].label}</span>
            </RadioOption>
          ))}
        </>
      ),
      span: 'md:col-span-2',
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Pengaturan"
        description="Sesuaikan tampilan dan preferensi aplikasi Anda."
      />

      <div className="grid gap-6 md:grid-cols-2">
        {cards.map((card, i) => (
          <motion.div
            key={card.title}
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.06 }}
            className={`bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-6 space-y-5 ${card.span}`}
          >
            <div>
              <h3 className="font-semibold text-[15px] text-gray-900 dark:text-white">{card.title}</h3>
              <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">{card.desc}</p>
            </div>
            <div className={`grid ${card.cols} gap-4`}>
              {card.content}
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  );
}
