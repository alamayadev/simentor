# Panduan Modul SKP (Kinerja Pegawai)

## 📋 Ikhtisar
Modul SKP (Sasaran Kinerja Pegawai) digunakan untuk merencanakan, melacak, dan mengelola target kinerja pegawai.

## 📊 Halaman dan Fitur

### 1. Dashboard SKP
**Path:** `/skp/dashboard`  
**Tujuan:** Melihat statistik kinerja tingkat tinggi untuk organisasi.

#### Memahami Metrik:
- **Total Pegawai:** Jumlah total pegawai yang dilacak dalam sistem.
- **SKP Bulanan:** Jumlah rencana kinerja bulanan yang aktif.
- **SKP Tetap:** Jumlah rencana kinerja tetap/tahunan.
- **Indikator Status:** 🟢 Live (aktif), ⚫ Closed (selesai).

#### Memfilter Data:
1. **Klik** tombol filter di bagian atas.
2. **Pilih** periode waktu atau departemen yang diinginkan.
3. **Lihat** statistik yang diperbarui dalam bagan dan tabel.

---

### 2. SKP Progres
**Path:** `/skp/progres`  
**Tujuan:** Memantau kemajuan penyelesaian dan status unggahan dokumen kinerja di seluruh pegawai.

Halaman ini memiliki dua tab pemantauan utama:

#### A. Tab Bulanan & Penetapan
Digunakan untuk memantau kewajiban rutin di awal periode.
- **SKP Bulanan**: Daftar pegawai yang belum mengunggah laporan kinerja bulanan.
- **Penetapan Tahunan**: Daftar pegawai yang belum mengunggah dokumen penetapan target kerja (awal tahun).

#### B. Tab Penilaian & Evaluasi
Digunakan untuk memantau dokumen hasil akhir.
- **Penilaian SKP**: Daftar pegawai yang belum mengunggah dokumen penilaian capaian akhir tahun.
- **Evaluasi Kinerja**: Daftar pegawai yang belum mengunggah dokumen evaluasi resmi dari atasan.

#### Fitur Tambahan:
- **Filter**: Anda dapat memfilter data berdasarkan **Tahun** dan **Bulan**.
- **Upload SKP**: Tombol cepat untuk mengunggah dokumen SKP Anda sendiri langsung dari halaman ini.

---

### 3. Daftar SKP
**Path:** `/skp/daftar`  
**Tujuan:** Mengelola dan membuat rencana kinerja individu.

#### Mengunggah/Edit SKP:
1. **Klik** tombol "Upload SKP" atau ikon pensil untuk edit.
2. **Isi Informasi:**
   - **Jenis SKP**: Pilih jenis dokumen (SKP Bulanan, Penetapan Tahunan, Penilaian SKP, atau Evaluasi Tahunan).
   - **Tahun**: Pilih tahun kinerja.
   - **Bulan**: Pilih bulan (hanya muncul jika memilih jenis **SKP Bulanan**).
   - **File PDF**: Unggah dokumen dalam format PDF (maksimal 7 MB).
3. **Klik "Upload SKP"** atau **"Perbarui SKP"**.

---

## 🔄 Integrasi dengan Modul Lain
- **Modul Kegiatan**: Evaluasi dalam modul Kegiatan secara langsung mempengaruhi progres penyelesaian SKP.
- **Modul Umum**: Data personel bersumber dari Data Master Pegawai.

## ⚠️ Troubleshooting (Khusus SKP)
**Masalah: Persentase progres tidak diperbarui**  
**Solusi:** Pastikan kegiatan terkait dalam modul **Kegiatan** telah dievaluasi dan dikirim. Progres dihitung berdasarkan evaluasi yang disetujui.

**Masalah: Pegawai tidak ditemukan dalam daftar SKP**  
**Solusi:** Verifikasi bahwa pegawai tersebut diatur ke status "Aktif" di **Umum → Pegawai**.
