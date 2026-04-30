# Panduan Modul Kegiatan

## 📋 Ikhtisar
Modul Kegiatan mengelola katalog kegiatan, penugasan tugas kepada personel, dan evaluasi pekerjaan yang telah diselesaikan.

## 📊 Halaman dan Fitur

### 1. Daftar Kegiatan
**Path:** `/kegiatan/daftar`  
**Tujuan:** Mengelola katalog master dari semua kegiatan yang tersedia.

#### Menambah/Edit Kegiatan:
1. **Klik** "Tambah Kegiatan" atau ikon pensil pada baris kegiatan.
2. **Isi Detail**:
   - **Tahun**: Tahun anggaran kegiatan.
   - **Fungsi**: Pilih fungsi penanggung jawab.
   - **Kode Kegiatan**: Kode anggaran (misalnya, `2904.521213`).
   - **Nama Kegiatan**: Nama lengkap kegiatan.
   - **Tgl Mulai & Tgl Selesai**: Periode pelaksanaan.
   - **Jenis**: Jenis kegiatan (Sensus, Survei, dll.).
   - **Jml Petugas**: Target jumlah petugas yang dibutuhkan.
   - **Volume & Satuan**: Target output (contoh: 100 Dok).
   - **Rate (PCL/PML/Entri)**: Nilai honor per satuan untuk masing-masing peran (opsional).
   - **Status**: Aktif, Tidak Dicairkan, atau Dibatalkan.
3. **Klik "Simpan"**.

---

### 2. Kegiatan Kalender
**Path:** `/kegiatan/kalender`  
**Tujuan:** Memvisualisasikan kegiatan pada kalender kronologis.

#### Menggunakan Kalender:
- **Navigasi:** Gunakan panah kiri/kanan untuk berpindah antar bulan.
- **Tab Status Kegiatan**: Memfilter tampilan kegiatan berdasarkan tahap pelaksanaan:
  - **Tab Berjalan**: Daftar kegiatan yang sedang berlangsung hari ini.
  - **Tab Akan Datang**: Daftar kegiatan yang sudah dijadwalkan di masa depan.
  - **Tab Selesai**: Arsip kegiatan yang sudah melewati tanggal berakhir.
- **Kartu Detail**: Klik pada kartu kegiatan untuk melihat volume target vs realisasi penugasan.

---

### 3. Penugasan (Task Assignment) 🔴
**Path:** `/kegiatan/penugasan`  
**Tujuan:** Menugaskan tugas dan tanggung jawab spesifik kepada personel.

#### Membuat Penugasan:
1. **Pilih** kegiatan dari daftar.
2. **Klik** "Tambah Penugasan".
3. **Pilih Personel:** Pilih dari daftar staf yang tersedia.
4. **Atur Periode:** Tentukan tanggal mulai dan berakhir untuk tugas tersebut.
5. **Klik "Simpan"**.

---

### 4. Petugas (Personnel)
**Path:** `/kegiatan/petugas`  
**Tujuan:** Memantau dan mengelola beban personel di berbagai kegiatan.

---

### 5. Evaluasi 🔴
**Path:** `/kegiatan/evaluasi`  
**Tujuan:** Melakukan evaluasi kinerja untuk tugas yang telah diselesaikan.

#### Melakukan Evaluasi:
1. **Pilih** kegiatan/penugasan yang akan dievaluasi.
2. **Isi Formulir:** Masukkan skor kinerja dan komentar kualitatif.
3. **Kirim:** Setelah dikirim, skor akan secara otomatis memperbarui progres **SKP** pegawai.

---

### 6. Monitoring
**Path:** `/kegiatan/monitoring`  
**Tujuan:** Melacak kemajuan real-time dari semua kegiatan yang sedang berlangsung.

#### Memperbarui Status:
1. **Pilih** kegiatan.
2. **Sesuaikan Progres:** Gerakkan slider atau masukkan persentase.
3. **Tambah Catatan:** Dokumentasikan hambatan atau pencapaian apa pun.
4. **Klik "Simpan"**.

---

## 🔄 Titik Integrasi
- **Modul Umum**: Personel bersumber dari daftar Pegawai.
- **Modul Surat**: Surat penugasan (Surat Tugas) sering kali memicu penugasan kegiatan ini.
- **Modul SKP**: Hasil evaluasi di sini adalah sumber data utama untuk metrik SKP.

## ⚠️ Troubleshooting (Khusus Kegiatan)
**Masalah: Tidak dapat menugaskan personel pada tanggal tertentu**  
**Solusi:** Periksa apakah personel tersebut memiliki penugasan yang bentrok atau jika tanggal tersebut ditandai sebagai hari libur di **Umum → Hari Libur**.

**Masalah: Formulir evaluasi tidak muncul**  
**Solusi:** Pastikan "Tanggal Berakhir" kegiatan telah lewat atau status telah diatur ke "Selesai".
