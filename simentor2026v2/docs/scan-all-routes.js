/**
 * Auto-scanner script — paste this into the browser console.
 * Uses TanStack Router's navigate() for SPA-friendly navigation.
 * Scans all routes, merges results, and copies to clipboard.
 *
 * Usage: paste into DevTools console on http://localhost:5000
 */
(async () => {
  const ROUTES = [
    '/skp/dashboard',
    '/skp/progres',
    '/skp/daftar',
    '/umum/pengaturan',
    '/umum/rkk-dipa/monitoring',
    '/umum/rkk-dipa/perencanaan',
    '/umum/rkk-dipa/pencairan',
    '/umum/rkk-dipa/riwayat',
    '/umum/rkk-dipa/integritas',
    '/umum/rkk-dipa/revisi-import',
    '/umum/surat/keluar',
    '/umum/surat/tugas',
    '/umum/surat/keputusan',
    '/umum/surat/permintaan',
    '/umum/pegawai',
    '/umum/libur',
    '/kegiatan/daftar',
    '/kegiatan/kalender',
    '/kegiatan/penugasan',
    '/kegiatan/petugas',
    '/kegiatan/evaluasi',
    '/kegiatan/monitoring',
    '/kontraktual/monitoring',
    '/kontraktual/spk-bast',
    '/ipds/tiket',
    '/ipds/asset',
    '/admin/pengguna',
    '/admin/peran',
    '/admin/izin',
    '/admin/metadata',
  ];

  const wait = (ms) => new Promise((r) => setTimeout(r, ms));
  const merged = {};

  for (let i = 0; i < ROUTES.length; i++) {
    const route = ROUTES[i];

    // Use TanStack Router's navigate
    window.__TSR_ROUTER__?.navigate({ to: route });
    await wait(2500);

    const result = window.scanCurrentPage();
    Object.assign(merged, result);
    const routeKey = Object.keys(result)[0];
    const count = Object.keys(result[routeKey] || {}).length;
    console.log(`[${i + 1}/${ROUTES.length}] ${route} → ${count} elements`);
  }

  const json = JSON.stringify(merged, null, 2);
  console.log('\n──── FULL SCAN RESULT ────\n');
  console.log(json);

  try {
    await navigator.clipboard.writeText(json);
    console.log('\n✅ Result copied to clipboard! Paste into docs/positions.json');
  } catch {
    console.log('\n⚠️ Could not auto-copy. Select the JSON above and copy manually.');
  }
})();
