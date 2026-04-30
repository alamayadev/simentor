import { translatePosition } from './positionTranslator';

const ROUTES = [
  '/skp/dashboard', '/skp/progres', '/skp/daftar',
  '/umum/pengaturan',
  '/umum/rkk-dipa/monitoring', '/umum/rkk-dipa/perencanaan',
  '/umum/rkk-dipa/pencairan', '/umum/rkk-dipa/riwayat',
  '/umum/rkk-dipa/integritas', '/umum/rkk-dipa/revisi-import',
  '/umum/surat/keluar', '/umum/surat/tugas',
  '/umum/surat/keputusan', '/umum/surat/permintaan',
  '/umum/pegawai', '/umum/libur',
  '/kegiatan/daftar', '/kegiatan/kalender',
  '/kegiatan/penugasan', '/kegiatan/petugas',
  '/kegiatan/evaluasi', '/kegiatan/monitoring',
  '/kontraktual/monitoring', '/kontraktual/spk-bast',
  '/ipds/tiket', '/ipds/asset',
  '/admin/pengguna', '/admin/peran', '/admin/izin', '/admin/metadata',
];

/**
 * Scans the current page for elements with 'data-scan' attributes.
 * Returns a JSON object grouped by the current route name.
 */
export function scanCurrentPage() {
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

  const pathname = window.location.pathname;
  // Group by route name like 'skp/dashboard'
  const routeName = pathname === '/' ? 'root' : pathname.startsWith('/') ? pathname.slice(1) : pathname;
  
  const output = {
    [routeName]: results
  };

  console.log('EXTRACTED_POSITIONS:', JSON.stringify(output, null, 2));
  return output;
}

/**
 * Auto-scan: navigates all routes using the TanStack Router, scans each,
 * and logs the merged JSON result.
 * 
 * Triggered via: window.scanAllRoutes()
 * Or auto-triggered on page load when URL contains ?scan=all
 */
async function scanAllRoutes() {
  const wait = (ms: number) => new Promise((r) => setTimeout(r, ms));
  const merged: Record<string, any> = {};
  const router = (window as any).__TSR_ROUTER__;

  if (!router) {
    console.error('❌ TanStack Router not found on window.__TSR_ROUTER__');
    return;
  }

  console.log(`🔍 Starting scan of ${ROUTES.length} routes...`);

  for (let i = 0; i < ROUTES.length; i++) {
    const route = ROUTES[i];
    try {
      await router.navigate({ to: route });
    } catch {
      // Some routes might throw, try direct
      window.history.pushState({}, '', route);
      window.dispatchEvent(new PopStateEvent('popstate'));
    }
    await wait(2500);

    const result = scanCurrentPage();
    Object.assign(merged, result);
    const key = Object.keys(result)[0];
    const count = Object.keys(result[key] || {}).length;
    console.log(`[${i + 1}/${ROUTES.length}] ${route} → ${count} elements`);
  }

  const json = JSON.stringify(merged, null, 2);
  console.log('\n──── FULL SCAN RESULT ────\n');
  console.log(json);

  try {
    await navigator.clipboard.writeText(json);
    console.log('\n✅ Copied to clipboard! Paste into docs/positions.json');
  } catch {
    console.log('\n⚠️ Could not auto-copy. See __SCAN_RESULT__ on window.');
  }

  (window as any).__SCAN_RESULT__ = merged;
  return merged;
}

// Expose to window for manual trigger via console
if (typeof window !== 'undefined') {
  (window as any).scanCurrentPage = scanCurrentPage;
  (window as any).scanAllRoutes = scanAllRoutes;

  // Auto-trigger on ?scan=all
  const params = new URLSearchParams(window.location.search);
  if (params.get('scan') === 'all') {
    // Wait for app to fully mount
    setTimeout(() => scanAllRoutes(), 3000);
  }
}
