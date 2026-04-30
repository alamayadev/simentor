# Panduan Pengembangan japekv3

Dokumen ini berisi panduan lengkap untuk pengembangan aplikasi japekv3 berbasis Laravel, termasuk struktur proyek, fitur utama, dan praktik terbaik.


## Prasyarat
- PHP >= 8.1
- Composer
- Node.js & npm
- MySQL atau database yang kompatibel


## Struktur Proyek
- `app/` - Inti aplikasi (Model, Controller, Actions, Livewire, Enum, dll)
- `config/` - File konfigurasi aplikasi
- `database/` - Migrasi, seeder, factory, backup database
- `public/` - Aset publik dan entry point aplikasi
- `resources/` - View Blade, CSS, JS, Markdown
- `routes/` - Definisi rute (`web.php`, `api.php`, dll)
- `tests/` - Pengujian aplikasi


## Langkah Instalasi
1. **Clone repository:**
   ```sh
   git clone <repo-url>
   cd japekv3
   ```
2. **Instalasi dependensi PHP:**
   ```sh
   composer install
   ```
3. **Instalasi dependensi Node.js:**
   ```sh
   npm install
   ```
4. **Salin dan konfigurasi file environment:**
   ```sh
   cp .env.example .env
   # Edit file .env sesuai konfigurasi database dan aplikasi
   ```
5. **Generate application key:**
   ```sh
   php artisan key:generate
   ```
6. **Jalankan migrasi dan seeder:**
   ```sh
   php artisan migrate --seed
   ```
7. **Build aset frontend:**
   ```sh
   npm run build
   ```
8. **Jalankan server pengembangan:**
   ```sh
   php artisan serve
   ```


## Perintah Penting

## Seeder workflow dan safety (SEEDER_AUTO_TRUNCATE)

Untuk mencegah kehilangan data tak sengaja saat menjalankan seeder yang melakukan TRUNCATE, proyek ini menambahkan dua lapis pengamanan:

1. Variabel environment `SEEDER_AUTO_TRUNCATE` (default: false). Hanya ketika variabel ini di-set ke `true`, proses truncation otomatis akan dijalankan.
2. Pemeriksaan environment aplikasi: truncation akan dijalankan hanya bila aplikasi berjalan di environment `local` atau `testing`.

Dengan kata lain, truncation hanya terjadi bila kedua kondisi berikut terpenuhi:

- `SEEDER_AUTO_TRUNCATE=true` di `.env`
- `APP_ENV=local` atau `APP_ENV=testing`

Implementasi teknis:

- Central truncation: `database/seeders/DatabaseSeeder.php` menonaktifkan foreign key checks, lalu men-truncate daftar tabel master yang digunakan oleh seeders, lalu mengaktifkan kembali foreign key checks.
- Per-seeder guard: setiap seeder yang melakukan insert juga memeriksa kedua kondisi di atas sebelum men-truncate tabelnya sendiri (berguna ketika menjalankan seeder tunggal).

Contoh `.env` (lokal/test saja):

```
SEEDER_AUTO_TRUNCATE=true
APP_ENV=local
```

Cara menjalankan (recommended on local/test DB only):

```powershell
# Pastikan .env sudah disiapkan dan Anda sedang di environment lokal/test
php artisan migrate
php artisan db:seed
```

Tips dan catatan keselamatan:

- Backup database atau gunakan salinan lokal sebelum menjalankan seeder dengan truncation.
- Jangan mengatur `SEEDER_AUTO_TRUNCATE=true` di environment produksi.
- Jika Anda hanya ingin men-run seeders tanpa truncation, biarkan `SEEDER_AUTO_TRUNCATE` bernilai `false` (default) dan jalankan `php artisan db:seed`.
- Anda juga bisa menjalankan seeder tunggal tanpa truncation:

```powershell
# Run single seeder (no truncation unless env toggle + APP_ENV allow it)
php artisan db:seed --class=ProfilPegawaiTableSeeder
```

Jika Anda mau, kami bisa menambahkan konfirmasi interaktif (y/n) sebelum truncation berjalan — ini menambah lapisan perlindungan ketika seeder dijalankan secara interaktif.

## Standar Kode
- Ikuti standar PSR-12 untuk kode PHP
- Gunakan best practice Laravel untuk controller, model, dan route
- Gunakan Livewire untuk komponen interaktif
- Selalu tambahkan pengujian untuk fitur baru


## Kontribusi
- Fork repository dan buat branch fitur
- Kirim pull request dengan deskripsi yang jelas
- Pastikan semua pengujian lulus sebelum submit


## Pemecahan Masalah
- Periksa konfigurasi di `.env` jika terjadi error
- Jalankan `composer install` dan `npm install` setelah update kode
- Bersihkan cache dengan `php artisan config:cache` dan `php artisan cache:clear`

## Deskripsi Fitur Utama

Aplikasi japekv3 adalah sistem manajemen administrasi dan kegiatan yang mendukung berbagai fitur utama berikut:

- **Dashboard:**
   Menampilkan ringkasan statistik aplikasi, grafik aktivitas terbaru, dan shortcut menuju fitur-fitur penting. Dashboard memudahkan pengguna untuk memantau status aplikasi secara real-time, seperti jumlah kegiatan, surat, penugasan, dan notifikasi penting.

- **Manajemen Pengguna:**
   Fitur ini memungkinkan admin untuk menambah, mengedit, menghapus, dan mengatur peran pengguna. Terdapat validasi data, pencarian pengguna, serta pengelolaan hak akses sesuai kebutuhan organisasi.

- **Kegiatan:**
   Modul untuk pencatatan, pelacakan, dan pengelolaan berbagai jenis kegiatan. Pengguna dapat membuat kegiatan baru, mengedit detail, menambahkan lampiran, serta memonitor progres dan status kegiatan secara terpusat.

- **Surat:**
   Mendukung manajemen surat masuk dan keluar, termasuk pembuatan surat tugas, surat permintaan, dan surat keluar. Fitur ini dilengkapi dengan pencatatan detail surat, upload file, tracking status, serta pencarian dan filter surat berdasarkan kategori atau tanggal.

- **Penugasan:**
   Fitur penugasan memungkinkan admin atau supervisor untuk mendistribusikan tugas kepada pegawai, memantau progres penyelesaian, memberikan deadline, dan mengelola feedback atau laporan hasil tugas.

- **Laporan:**
   Pengguna dapat membuat, melihat, dan mengunduh laporan terkait kegiatan, penugasan, surat, dan statistik aplikasi. Laporan dapat difilter berdasarkan periode, jenis kegiatan, atau pengguna tertentu, serta tersedia dalam format PDF atau Excel.

- **Backup Database:**
   Fitur ini menyediakan mekanisme backup database secara manual maupun terjadwal untuk menjaga keamanan dan integritas data. File backup dapat diunduh dan disimpan sebagai cadangan.

## Arsitektur & Teknologi
- Backend: Laravel, Livewire
- Frontend: Blade, Tailwind CSS, Vite
- Database: MySQL
- Otentikasi: Laravel Fortify & Jetstream

## Penjelasan Folder Penting

- `app/Models/`: Berisi seluruh model Eloquent yang merepresentasikan tabel-tabel utama di database. Setiap model biasanya berisi relasi antar tabel, query scope, dan logika bisnis terkait data.
- `app/Http/Controllers/`: Menampung seluruh controller yang bertugas menangani request dari user, memproses data, dan mengembalikan response ke view atau API. Controller dibagi berdasarkan domain fitur aplikasi.
- `app/Livewire/`: Berisi komponen Livewire yang digunakan untuk membangun UI interaktif tanpa reload halaman. Setiap komponen biasanya terdiri dari class PHP dan view Blade terkait.
- Struktur di dalamnya dibagi berdasarkan domain fitur, seperti Admin, Dashboard, Forms, dan Kantor. Berikut penjelasan detailnya:

   - **Admin/**: Berisi komponen untuk manajemen user, role, dan permission. Setiap subfolder (User, Role, Permission) memiliki file Create.php, Edit.php, Delete.php, dan Table.php yang masing-masing bertugas untuk membuat, mengedit, menghapus, dan menampilkan data dalam bentuk tabel.

   - **Dashboard/**: Komponen untuk menampilkan berbagai chart dan statistik di halaman dashboard, seperti MainChart.php, SalesCategoryChart.php, dan lain-lain. Komponen ini mengambil data dari database dan menampilkannya dalam bentuk grafik interaktif.

   - **Forms/**: Berisi berbagai form Livewire yang digunakan untuk input data, seperti FormUser.php, KegiatanForm.php, FormSurKeluar.php, dll. Setiap form biasanya berisi validasi, logika penyimpanan, dan update data ke model terkait.

   - **Kantor/**: Subfolder terbesar yang berisi komponen untuk fitur administrasi kantor, seperti Bast, Ipds, Kegiatan, Layanan, Mitra, NomorSurat, Pemetaan, Penugasan, Polink, Setting, Skp, Spk, dan SuratGenerator. Setiap subfolder biasanya memiliki file Create.php, Edit.php, Delete.php, Table.php, dan Index.php yang bertugas untuk CRUD dan tampilan data.

   - **Dashboard.php**: Komponen utama dashboard yang merender view dashboard aplikasi.

Setiap komponen Livewire dibangun dengan prinsip berikut:
1. **Class Komponen**: File PHP yang mewarisi `Livewire\Component` atau `Livewire\Form`, berisi properti data, method untuk logika bisnis (seperti save, update, delete), dan event untuk komunikasi antar komponen.
2. **View Blade**: Setiap komponen memiliki file Blade di `resources/views/livewire/...` yang menampilkan UI dan mengikat data dari class komponen.
3. **Tujuan Komponen**: Memisahkan logika UI dan backend agar aplikasi responsif, mudah dikembangkan, dan terstruktur. Komponen Livewire memudahkan pembuatan form dinamis, tabel interaktif, chart, dan proses CRUD tanpa reload halaman.
- `app/Enums/`: Folder ini berisi class enum yang digunakan untuk mendefinisikan tipe data tetap, seperti jenis kegiatan, satuan, jabatan, dan lain-lain. Enum membantu menjaga konsistensi data di seluruh aplikasi.
- `resources/views/`: Menyimpan seluruh file template Blade yang digunakan untuk menampilkan halaman web kepada user. Folder ini dapat berisi subfolder sesuai modul atau fitur aplikasi.
- `routes/web.php`: File utama untuk mendefinisikan rute aplikasi web (akses via browser). Di sini didefinisikan URL, controller, dan middleware yang digunakan.
- `routes/api.php`: File untuk mendefinisikan rute API (akses via endpoint HTTP). Biasanya digunakan untuk integrasi dengan aplikasi lain atau mobile.
- `config/`: Berisi file konfigurasi aplikasi, seperti database, mail, cache, dan service lain yang digunakan oleh Laravel.
- `database/migrations/`: Folder ini berisi file migrasi yang digunakan untuk membuat, mengubah, atau menghapus struktur tabel di database secara versioning.
- `database/seeders/`: Menyimpan file seeder untuk mengisi data awal ke database, seperti data master atau data dummy untuk pengujian.
- `public/`: Folder publik yang berisi aset statis seperti gambar, CSS, JS, dan file entry point aplikasi (`index.php`). Semua file di sini dapat diakses langsung oleh user melalui browser.

## Tips Pengembangan
- Gunakan branch terpisah untuk fitur/bugfix
- Selalu lakukan testing sebelum merge
- Dokumentasikan perubahan di pull request

Referensi lebih lanjut: [Dokumentasi Laravel](https://laravel.com/docs) (Bahasa Inggris)

## Library Utama yang Digunakan

### Library Backend (PHP/Laravel)
- **laravel/framework**: Framework utama aplikasi.
- **livewire/livewire**: Library untuk membangun komponen UI interaktif tanpa reload halaman.
- **livewire/volt**: Sintaks lebih ringkas untuk komponen Livewire.
- **laravel/jetstream**: Otentikasi dan manajemen tim/user.
- **laravel/sanctum**: API token untuk otentikasi aplikasi.
- **spatie/laravel-permission**: Manajemen role dan permission user.
- **barryvdh/laravel-dompdf**: Generate file PDF dari view.
- **phpoffice/phpword**: Generate dokumen Word.
- **smalot/pdfparser**: Parsing file PDF.
- **iio/libmergepdf**: Menggabungkan beberapa file PDF.
- **mantix/livewire-jodit-text-editor**: Editor teks berbasis Jodit untuk Livewire.
- **themesberg/flowbite-blade-icons**: Ikon SVG untuk Blade.
- **calebporzio/sushi**: Model Eloquent berbasis array.

### Library Dev & Testing
- **barryvdh/laravel-debugbar**: Debugging aplikasi.
- **enlightn/enlightn**: Audit keamanan dan performa.
- **fakerphp/faker**: Generate data dummy.
- **kitloong/laravel-migrations-generator**: Generate migrasi dari database.
- **laravel-lang/lang**: Dukungan multi bahasa.
- **laravel/pint**: Formatter kode PHP.
- **pestphp/pest**: Framework testing.
- **wire-elements/wire-spy**: Debug komponen Livewire.

### Library Frontend (JavaScript)
- **vite**: Build tool modern untuk frontend.
- **laravel-vite-plugin**: Integrasi Vite dengan Laravel.
- **tailwindcss**: Framework CSS utility-first.
- **@tailwindcss/forms/typography**: Plugin Tailwind untuk form dan tipografi.
- **flowbite**: Komponen UI berbasis Tailwind.
- **apexcharts**: Library chart interaktif.
- **axios**: HTTP client untuk AJAX.
- **@popperjs/core**: Library popper untuk tooltip/dropdown.
- **autoprefixer/postcss/postcss-nesting**: Tooling CSS modern.

Library di atas mendukung pengembangan aplikasi japekv3 agar modern, interaktif, aman, dan mudah dikembangkan.

## Perbaikan Terbaru dan Persiapan Upgrade ke Laravel 12

### Perbaikan CORS dan Autentikasi (2025-09-14)

#### Masalah: Error CORS dan Status 419
- **Masalah**: Frontend di `http://localhost:5173` tidak dapat mengakses API backend di `http://127.0.0.1:8000` karena kebijakan CORS dan error 419 (ketidakcocokan token CSRF) pada endpoint login/logout.
- **Penyebab**: Middleware Sanctum diterapkan secara global ke semua rute API, menyebabkan validasi CSRF pada endpoint publik.
- **Solusi**:
  1. Merestrukturisasi rute API untuk memisahkan endpoint publik (login) dari endpoint yang dilindungi
  2. Menghapus middleware Sanctum global dari rute API di `bootstrap/app.php`
  3. Memperbarui konfigurasi CORS untuk mengizinkan origin frontend
  4. Memperbarui konfigurasi domain stateful Sanctum

#### File yang Diubah:
1. `bootstrap/app.php` - Menghapus middleware Sanctum global dari rute API
2. `routes/api.php` - Merestrukturisasi grup rute untuk memisahkan endpoint publik dari yang dilindungi
3. `config/cors.php` - Memverifikasi konfigurasi CORS untuk akses frontend
4. `config/sanctum.php` - Memperbarui domain stateful untuk menyertakan origin frontend
5. `app/Http/Controllers/Api/AuthApiController.php` - Menyederhanakan metode logout

#### Pengujian:
- Endpoint login sekarang dapat diakses tanpa validasi CSRF
- Endpoint logout tetap dilindungi dan berfungsi dengan baik
- Frontend dapat berhasil melakukan autentikasi dan mengakses endpoint API yang dilindungi

### Persiapan Upgrade ke Laravel 12

#### Persyaratan Versi PHP
- **Persyaratan**: PHP 8.3 atau lebih tinggi (wajib untuk Laravel 12)
- **Status Saat Ini**: Verifikasi versi PHP saat ini dan rencanakan upgrade jika diperlukan
- **Tindakan**: Periksa `composer.json` dan persyaratan server

#### Kompatibilitas Package
Berdasarkan dependensi proyek, package berikut mungkin memerlukan pembaruan:

##### Package Ekosistem Laravel
1. **Laravel Jetstream** (`laravel/jetstream:^5.3`)
   - Mungkin perlu pembaruan untuk kompatibilitas Laravel 12
   - Periksa perubahan breaking pada scaffolding autentikasi

2. **Laravel Sanctum** (`laravel/sanctum`)
   - Versi saat ini mungkin perlu diperbarui
   - Tinjau perubahan penanganan token API

3. **Livewire** (`livewire/livewire:^3.0` dan `livewire/volt:^1.6`)
   - Mungkin memerlukan pembaruan untuk kompatibilitas Laravel 12
   - Periksa perubahan breaking pada penanganan komponen

##### Package Pihak Ketiga
1. **Spatie Laravel Permission** (`spatie/laravel-permission`)
   - Verifikasi kompatibilitas dengan Laravel 12
   - Periksa perubahan pada implementasi RBAC

2. **Package Spatie Lainnya**
   - Tinjau semua versi package Spatie untuk kompatibilitas

#### Masalah yang Diketahui dari Analisis Statis
Analisis PHPStan mengungkapkan 446 error setelah simulasi upgrade Laravel 12:
- Metode statis yang tidak terdefinisi pada model Eloquent
- Referensi kelas dengan huruf besar/kecil yang salah
- Properti yang tidak terdefinisi

#### Perubahan Breaking yang Perlu Diperhatikan
1. **Sistem Autentikasi**
   - Perubahan pada penanganan token API Sanctum
   - Pembaruan pada scaffolding autentikasi Jetstream

2. **Routing**
   - Perubahan sintaks definisi rute
   - Pembaruan konfigurasi grup middleware

3. **Eloquent ORM**
   - Definisi relasi model
   - Perubahan metode query builder

4. **Template Blade**
   - Pembaruan sintaks template
   - Perubahan rendering komponen

5. **File Konfigurasi**
   - Opsi konfigurasi baru
   - Penghapusan konfigurasi yang sudah usang

#### Jalur Upgrade yang Direkomendasikan
1. **Fase Persiapan**:
   - Backup seluruh proyek dan database
   - Periksa kompatibilitas versi PHP (8.3+)
   - Tinjau kompatibilitas semua package pihak ketiga

2. **Fase Pengujian**:
   - Buat branch development untuk pengujian upgrade
   - Perbarui versi framework Laravel
   - Perbarui package yang kompatibel secara bertahap
   - Jalankan suite pengujian secara menyeluruh

3. **Fase Migrasi**:
   - Terapkan migrasi database jika ada
   - Perbarui file konfigurasi
   - Perbaiki masalah kompatibilitas kode
   - Verifikasi semua fungsionalitas

#### Tugas Pasca-Upgrade
1. **Kualitas Kode**:
   - Jalankan PHPStan untuk menyelesaikan error analisis statis
   - Perbarui pengujian PHPUnit untuk perubahan breaking
   - Tinjau dan perbarui dokumentasi API

2. **Optimasi Performa**:
   - Tinjau strategi caching
   - Optimasi query database
   - Periksa performa komponen Livewire

3. **Tinjauan Keamanan**:
   - Verifikasi autentikasi dan otorisasi
   - Periksa praktik keamanan yang sudah usang
   - Perbarui dependensi keamanan
