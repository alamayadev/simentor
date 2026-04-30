# Strategi Mengukur Halaman Paling Sering Diakses

Strategi paling efisien untuk app seperti OpsPulse adalah mengukur di level `route change` pada frontend, lalu mengirim event ringan ke backend atau analytics store.

## Pendekatan yang Disarankan

1. Definisikan metrik yang benar

`page_view` saja sering bias. Minimal simpan:

- `path`
- `timestamp`
- `user_id` atau `session_id`
- `referrer`
- `entered_at` dan `left_at`, atau minimal durasi

2. Track setiap perpindahan halaman

Karena app ini berbentuk SPA, log server saja biasanya tidak cukup akurat. Saat route berubah, kirim event `page_view`.

3. Simpan agregat harian

Jangan selalu query raw event. Buat agregasi harian seperti:

- `date`
- `path`
- `views`
- `unique_users`
- `avg_duration`

4. Bedakan `views` dan `unique_users`

Halaman paling sering diakses bisa berarti beberapa hal:

- paling banyak dibuka
- paling banyak user unik
- paling lama dipakai

Tiga metrik ini bisa menghasilkan ranking yang berbeda, jadi sebaiknya disimpan terpisah.

5. Filter noise

Abaikan data yang bisa merusak akurasi:

- bot
- health check
- auto refresh
- redirect page
- duplicate hit dalam window beberapa detik

## Opsi Implementasi

### Opsi 1: Paling cepat

Gunakan analytics tool seperti:

- PostHog
- Plausible
- Google Analytics

Cocok jika ingin insight cepat tanpa membangun pipeline sendiri.

### Opsi 2: Paling cocok untuk app internal

Buat event tracking sederhana di app, lalu simpan ke database sendiri.

Kelebihannya:

- lebih ringan
- lebih murah
- lebih relevan untuk kebutuhan internal
- mudah diolah jadi dashboard sendiri

## Rekomendasi untuk OpsPulse

Gunakan pendekatan berikut:

- track `page_view` saat route berubah
- simpan agregat harian
- hitung `unique_users`
- tambahkan `duration` jika ingin kualitas insight lebih baik

Alasan:

- implementasinya ringan
- akurat untuk SPA
- mudah dipakai untuk dashboard “halaman paling sering diakses”
- tidak overkill

## Desain Implementasi

### 1. Payload Event

Payload yang dikirim dari client sebaiknya sederhana, stabil, dan cukup untuk agregasi.

Contoh:

```json
{
  "event_type": "page_view",
  "path": "/umum/rkk-dipa/monitoring",
  "title": "Monitoring RKK DIPA",
  "referrer": "/",
  "session_id": "sess_01JXYZ...",
  "user_id": "USR-001",
  "occurred_at": "2026-04-18T09:12:33.120Z",
  "meta": {
    "module": "umum",
    "feature": "rkk-dipa"
  }
}
```

Minimal field yang direkomendasikan:

- `event_type`
- `path`
- `session_id`
- `user_id` jika tersedia
- `occurred_at`

Field opsional yang berguna:

- `title`
- `referrer`
- `meta`

Catatan:

- `session_id` bisa dibuat di client dan disimpan di `sessionStorage` atau `localStorage`
- `occurred_at` boleh dikirim dari client, tapi server tetap bisa menambahkan `received_at`
- `meta` berguna untuk pengelompokan tanpa harus parsing path terus-menerus

### 2. Endpoint API

Model paling sederhana:

- `POST /api/analytics/events`

Request body:

```json
{
  "event_type": "page_view",
  "path": "/skp/dashboard",
  "title": "Dashboard Kinerja",
  "referrer": "/",
  "session_id": "sess_123",
  "user_id": "USR-001",
  "occurred_at": "2026-04-18T09:12:33.120Z",
  "meta": {
    "module": "skp"
  }
}
```

Response:

```json
{
  "success": true
}
```

Tambahan endpoint yang nanti berguna:

- `GET /api/analytics/pages/top?range=30d`
- `GET /api/analytics/pages/daily?path=/umum/rkk-dipa/monitoring`

Tanggung jawab API:

- validasi payload
- normalisasi field penting
- simpan raw event
- sediakan agregasi untuk reporting

### 3. Skema Tabel

#### Tabel raw events

Nama tabel:

- `page_events`

Contoh kolom:

```sql
CREATE TABLE page_events (
  id BIGSERIAL PRIMARY KEY,
  event_type VARCHAR(50) NOT NULL,
  path VARCHAR(255) NOT NULL,
  title VARCHAR(255),
  referrer VARCHAR(255),
  session_id VARCHAR(100) NOT NULL,
  user_id VARCHAR(100),
  module_name VARCHAR(100),
  feature_name VARCHAR(100),
  occurred_at TIMESTAMP NOT NULL,
  received_at TIMESTAMP NOT NULL DEFAULT NOW(),
  ip_address VARCHAR(64),
  user_agent TEXT
);
```

Index yang disarankan:

```sql
CREATE INDEX idx_page_events_path ON page_events(path);
CREATE INDEX idx_page_events_occurred_at ON page_events(occurred_at);
CREATE INDEX idx_page_events_user_id ON page_events(user_id);
CREATE INDEX idx_page_events_session_id ON page_events(session_id);
```

#### Tabel agregat harian

Nama tabel:

- `page_metrics_daily`

Contoh kolom:

```sql
CREATE TABLE page_metrics_daily (
  id BIGSERIAL PRIMARY KEY,
  metric_date DATE NOT NULL,
  path VARCHAR(255) NOT NULL,
  views INT NOT NULL DEFAULT 0,
  unique_users INT NOT NULL DEFAULT 0,
  unique_sessions INT NOT NULL DEFAULT 0,
  avg_duration_seconds NUMERIC(10,2),
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
  UNIQUE(metric_date, path)
);
```

Catatan:

- jika belum menghitung duration, `avg_duration_seconds` bisa `NULL`
- agregasi bisa dibuat via cron, background job, atau query periodik

### 4. Hook Tracking di Router React App Ini

Karena OpsPulse adalah SPA React, tracking paling tepat dilakukan saat route berubah.

#### Ide implementasi

Buat hook seperti:

- `usePageTracking()`

Hook ini:

- membaca path aktif dari router
- membandingkan dengan path sebelumnya
- mengirim event hanya saat benar-benar berubah

#### Contoh struktur hook

```tsx
import { useEffect, useRef } from 'react';
import { useLocation } from '@tanstack/react-router';

function getSessionId() {
  const existing = sessionStorage.getItem('ops_session_id');
  if (existing) return existing;

  const value = `sess_${crypto.randomUUID()}`;
  sessionStorage.setItem('ops_session_id', value);
  return value;
}

export function usePageTracking() {
  const location = useLocation();
  const prevPathRef = useRef<string | null>(null);

  useEffect(() => {
    const currentPath = location.pathname;
    if (prevPathRef.current === currentPath) return;

    prevPathRef.current = currentPath;

    fetch('/api/analytics/events', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        event_type: 'page_view',
        path: currentPath,
        referrer: document.referrer || null,
        session_id: getSessionId(),
        occurred_at: new Date().toISOString()
      })
    }).catch(() => {
      // tracking failure should not break UI
    });
  }, [location.pathname]);
}
```

#### Lokasi pemasangan hook

Pasang sekali di level root app, misalnya:

- `App.tsx`
- layout utama yang selalu aktif

Jangan pasang di setiap halaman, karena:

- mudah duplikat event
- lebih sulit dirawat

#### Pengembangan berikutnya

Setelah `page_view` stabil, bisa ditambah:

- `page_leave`
- `duration_seconds`
- debounce/dedup dalam beberapa detik
- queue + batch sending jika traffic tinggi

## Ringkasan Arsitektur

- client mendeteksi route change
- client mengirim `page_view` ke API
- API menyimpan raw event
- server membuat agregasi harian
- dashboard membaca tabel agregat, bukan raw event langsung
