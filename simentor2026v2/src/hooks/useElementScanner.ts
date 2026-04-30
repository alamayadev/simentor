import { useLocation } from '@tanstack/react-router';
import { translatePosition } from '../lib/positionTranslator';

/**
 * Hook to scan elements with 'data-scan' attributes and return their positions
 * in human-readable Indonesian format, grouped by the current route.
 */
export function useElementScanner() {
  const location = useLocation();

  const scanElements = () => {
    const elements = document.querySelectorAll('[data-scan]');
    const results: Record<string, { position: string }> = {};

    elements.forEach((el) => {
      const label = el.getAttribute('data-scan');
      if (label) {
        const rect = el.getBoundingClientRect();
        results[label] = {
          position: translatePosition(rect),
        };
      }
    });

    // Extract the route name (e.g., /skp/dashboard -> skp/dashboard)
    const routeName = location.pathname.startsWith('/') 
      ? location.pathname.slice(1) 
      : location.pathname;

    return {
      [routeName || 'root']: results,
    };
  };

  return { scanElements };
}
