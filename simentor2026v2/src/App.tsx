import { RouterProvider } from '@tanstack/react-router';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { router } from './router';
import { ThemeProvider } from './components/ThemeProvider';
import { AuthProvider } from './contexts/AuthContext';

const queryClient = new QueryClient();

// Expose router for dev tools (scanner, etc.)
if (typeof window !== 'undefined') {
  (window as any).__TSR_ROUTER__ = router;
}

export default function App() {
  return (
    <ThemeProvider defaultTheme="system" storageKey="opspulse-theme">
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <RouterProvider router={router} />
        </AuthProvider>
      </QueryClientProvider>
    </ThemeProvider>
  );
}
