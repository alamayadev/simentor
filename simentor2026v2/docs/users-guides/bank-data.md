# Panduan Modul Bank Data

## 📋 Ikhtisar
Modul Bank Data berfungsi sebagai repositori pusat untuk data survei mentah dan arsip historis.

## 📊 Halaman dan Fitur

### 1. Data Mentah (Raw Data)
**Path:** `/bank-data/raw`  
**Tujuan:** Penyimpanan untuk hasil survei yang belum diproses dan file data.

#### Bekerja dengan Data Mentah:
1. **Unggah:** Klik "Upload Data" untuk menambah file baru.
2. **Kategorisasi:** Tetapkan kategori (misalnya, Sensus Ekonomi, Survei Pertanian) untuk memudahkan pencarian.
3. **Berbagi:** File yang diunggah di sini dapat diakses oleh anggota tim yang berwenang.

---

### 2. Arsip Data
**Path:** `/bank-data/arsip`  
**Tujuan:** Penyimpanan jangka panjang untuk data yang telah difinalisasi dan historis.

#### Proses Pengarsipan:
- **Pindah ke Arsip:** Setelah proyek selesai, pilih file di "Raw Data" dan klik "Arsipkan".
- **Cari Arsip:** Gunakan filter untuk menemukan data dari tahun-tahun sebelumnya atau materi subjek tertentu.

---

## 🔄 Titik Integrasi
- **Modul Kegiatan**: Output akhir dari kegiatan sering kali diunggah ke modul Bank Data.
- **RKK DIPA**: Dokumentasi untuk pencairan keuangan diarsipkan di sini untuk keperluan audit.

## ⚠️ Troubleshooting (Khusus Bank Data)
**Masalah: Unggah file gagal**  
**Solusi:** Periksa koneksi internet Anda dan pastikan ukuran file berada dalam batas yang ditentukan. Kumpulan data besar harus di-zip/dikompres sebelum diunggah.

**Masalah: Data tidak terlihat oleh anggota tim**  
**Solusi:** Verifikasi bahwa "Kategori" dan "Izin" untuk file tersebut mengizinkan akses untuk peran lain di modul **Admin**.
