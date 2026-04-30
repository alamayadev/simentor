# Panduan Alur Kerja End-to-End (Skenario Utama)

## 📋 Ikhtisar
Halaman ini menjelaskan bagaimana berbagai modul di Simentor bekerja sama untuk menyelesaikan proses bisnis yang kompleks. Ini membantu Anda memahami urutan langkah dari awal hingga akhir.

---

## 🔄 Skenario A: Siklus Pelaksanaan Kegiatan
**Tujuan:** Dari membuat surat tugas hingga mendapatkan nilai kinerja.

1. **Langkah 1: Inisialisasi Dokumen (Modul Umum)**
   - Buat **Surat Tugas** di `/umum/surat/tugas`.
   - Masukkan perihal kegiatan (misalnya: "Sensus Ekonomi 2026").
   - Tambahkan daftar pegawai yang terlibat.
   - Kirim untuk persetujuan atasan.

2. **Langkah 2: Penugasan Teknis (Modul Kegiatan)**
   - Setelah surat disetujui, buka modul **Kegiatan → Penugasan**.
   - Pilih kegiatan yang sesuai dengan Surat Tugas tadi.
   - Atur jadwal spesifik dan tanggung jawab untuk setiap petugas.

3. **Langkah 3: Pelaksanaan & Monitoring (Modul Kegiatan)**
   - Pegawai melakukan pekerjaan di lapangan/kantor.
   - Pantau progres di halaman **Monitoring**. Pegawai atau admin memperbarui persentase penyelesaian (0% - 100%).

4. **Langkah 4: Evaluasi Hasil (Modul Kegiatan)**
   - Setelah pekerjaan selesai, atasan mengisi formulir **Evaluasi**.
   - Masukkan nilai angka (skor) dan komentar mengenai kualitas kerja.

5. **Langkah 5: Update Kinerja (Modul SKP)**
   - Sistem secara otomatis mengirim nilai evaluasi ke modul **SKP**.
   - Pegawai dapat melihat pencapaian target kinerja mereka di **SKP Progres**.

---

## 💰 Skenario B: Manajemen Anggaran & Kontrak
**Tujuan:** Dari perencanaan dana hingga pembayaran pihak ketiga.

1. **Langkah 1: Perencanaan (Modul Umum - RKK DIPA)**
   - Atur alokasi dana tahunan di halaman **Perencanaan**.
   - Pastikan setiap kategori kegiatan memiliki pagu anggaran yang cukup.

2. **Langkah 2: Pembuatan Kontrak (Modul Kontraktual)**
   - Jika kegiatan menggunakan pihak ketiga, buat **SPK** di modul Kontraktual.
   - Masukkan nilai kontrak. Sistem akan memvalidasi apakah dana tersedia di RKK DIPA.

3. **Langkah 3: Pengajuan Dana (Modul Umum - RKK DIPA)**
   - Saat pekerjaan mencapai termin tertentu, ajukan **Pencairan**.
   - Lampirkan bukti pendukung (kuitansi, invoice, laporan progres).

4. **Langkah 4: Realisasi & Arsip (Modul Bank Data)**
   - Setelah dana cair, catat riwayatnya di **RKK DIPA Riwayat**.
   - Unggah dokumen final (BAST) ke **Bank Data → Arsip** untuk keperluan audit di masa depan.

---

## 👥 Skenario C: Pengelolaan Pegawai Baru
**Tujuan:** Menyiapkan akun dan data master untuk personil baru.

1. **Langkah 1: Pembuatan Akun (Modul Admin)**
   - Buat user di **Admin → Pengguna**.
   - Tentukan **Peran** (misalnya: Pegawai Aktif).
   - Berikan username dan password awal kepada pegawai.

2. **Langkah 2: Kelengkapan Data Master (Modul Umum)**
   - Masukkan data rinci (NIP, Pangkat, Jabatan) di **Umum → Pegawai**.
   - Sinkronkan NIP dengan username yang dibuat di Modul Admin.

3. **Langkah 3: Persiapan Kerja**
   - Pegawai kini dapat login dan mulai menerima penugasan surat atau kegiatan.
