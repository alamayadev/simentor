# Panduan Troubleshooting Umum - Simentor v1.5

## 🔍 Perbaikan Cepat Masalah Umum

### Masalah Login ❌
**Masalah:** Tidak bisa login  
**Perbaikan Cepat:** Periksa tombol Caps Lock, gunakan tautan "Lupa password?". Pastikan akun Anda aktif dan tidak terkunci oleh administrator.

### Masalah Formulir ❌
**Masalah:** Tombol simpan dinonaktifkan  
**Perbaikan Cepat:** Cari bidang yang bergaris tepi merah atau pesan kesalahan di bawah input. Isi semua bidang wajib (ditandai dengan *).

### Masalah Pemuatan (Loading) ❌
**Masalah:** Halaman terus berputar atau data tidak muncul  
**Perbaikan Cepat:** Refresh halaman (F5), periksa koneksi internet Anda, atau tunggu 30 detik hingga sistem merespons.

### Masalah Izin ❌
**Masalah:** "Anda tidak memiliki akses" atau "Permission Denied"  
**Perbaikan Cepat:** Hubungi pengawas atau administrator Anda untuk memverifikasi peran dan hak akses Anda.

---

## 🔧 Pemulihan Kesalahan Sistematis

### Gagal Mengirim Dokumen
**Gejala:** Tombol simpan tetap dinonaktifkan, pesan kesalahan merah muncul, bidang formulir ditandai dengan garis tepi merah.

**Proses Pemulihan:**
1. **Identifikasi Kesalahan:** Periksa pesan seperti "Harap isi field yang wajib", "Format email tidak valid", atau "File terlalu besar".
2. **Kesalahan Jaringan/Koneksi:** Tunggu 30 detik, lalu refresh (F5). Jika masih gagal, salin teks Anda ke lokasi yang aman (seperti Notepad) sebelum menutup browser.
3. **Validasi Data:** Pastikan NIP berjumlah 18 digit, email mengikuti format standar, dan nomor telepon dimulai dengan +62 atau 08.

### Masalah Korupsi Data atau Perhitungan
**Gejala:** Halaman menampilkan data yang salah, perhitungan salah, atau catatan hilang.

**Proses Pemulihan:**
1. **Verifikasi Integritas Data:** Periksa apakah catatan tersebut muncul dengan benar di modul terkait lainnya.
2. **Koreksi Data:** Jika data dapat diedit, gunakan ikon pensil untuk memperbaikinya.
3. **Laporkan Bug:** Jika ini adalah kesalahan perhitungan, laporkan ke tim TI dengan menyertakan screenshot serta nilai yang diharapkan vs nilai aktual.

---

## 🔍 Troubleshooting Tingkat Lanjut

### Masalah Kinerja
**Gejala:** Sistem lambat atau tidak responsif.
**Solusi:**
- **Refresh browser (Ctrl+F5)** untuk memaksa memuat ulang semua aset.
- **Hapus cache browser** jika masalah berlanjut setelah refresh.
- **Tutup tab/program lain** untuk membebaskan memori sistem.
- **Gunakan filter** pada kumpulan data besar (seperti daftar pegawai) untuk mempercepat pemuatan.

### Masalah Sinkronisasi Data
**Gejala:** Data berbeda antar modul (misalnya, status pegawai di 'Umum' tidak sesuai dengan 'Kegiatan').
**Solusi:**
- Hapus cache dan refresh.
- Periksa "Aktivitas Terbaru" di dashboard untuk melihat apakah orang lain melakukan perubahan.
- Hubungi tim TI jika diperlukan sinkronisasi paksa.

### Kompatibilitas Browser
**Didukung:** Chrome 90+, Firefox 88+, Edge 90+, Safari 14+.
**Tidak Didukung:** Internet Explorer, versi Chrome yang sangat lama.
**Tips:** Coba mode "Incognito" atau "Private" untuk menyingkirkan konflik ekstensi browser.

---

## 📞 Mendapatkan Bantuan

- **IT Helpdesk:** Buat tiket di **IPDS → Tiket**.
- **Admin Sistem:** Hubungi melalui saluran kantor internal untuk bantuan segera.
- **Dokumentasi:** Lihat panduan modul spesifik (misalnya, `umum.md`, `admin.md`).
