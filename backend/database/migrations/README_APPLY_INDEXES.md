This file explains how to apply the wilkerstat index migration added to this repository.

1) Review and run EXPLAIN on your staging DB for the heavy queries used by WilkerstatDashboardService::compute().
   - The queries include DISTINCT counts on `cek_scans.kode`, `cek_georefs.kode`, whereDoesntHave on relationships to `alokasis`, and ordering by `updated_at` on cek_scans/cek_georefs.
   - Adjust index columns/order according to EXPLAIN output.

2) To apply the migration in staging/prod:

   cd /path/to/your/app
   php artisan migrate --path=database/migrations/2025_09_22_000000_add_wilkerstat_indexes.php

   or run all migrations normally:
   php artisan migrate

3) After running migrate, warm cache by hitting the endpoint once:
   curl -sS "https://<host>/api/wilkerstat-dashboard?cache=0"

4) Re-run your benchmarks.

Notes:
- Index creation can take time and may lock tables depending on your DB and engine. Run during maintenance window if needed.
- If using MySQL and large tables, prefer `pt-online-schema-change` or `gh-ost` for non-blocking index creation.
