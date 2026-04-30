import { Moon, Sun } from 'lucide-react';
import { useTheme } from './ThemeProvider';

export function ThemeToggle() {
  const { theme, setTheme } = useTheme();

  return (
    <button
      onClick={() => setTheme(theme === 'dark' ? 'light' : 'dark')}
      className="relative p-2.5 rounded-full bg-white/60 dark:bg-white/5 glass border border-white/40 dark:border-white/10 hover:bg-white/80 dark:hover:bg-white/10 overflow-hidden text-gray-600 dark:text-gray-300 transition-colors"
      aria-label="Toggle theme"
    >
      <div className="relative h-4.5 w-4.5 flex items-center justify-center">
        {theme === 'dark' ? <Moon className="h-4.5 w-4.5" /> : <Sun className="h-4.5 w-4.5" />}
      </div>
    </button>
  );
}
