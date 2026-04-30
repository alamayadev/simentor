# Panduan Profil Pengguna dan Pengaturan

## 📋 Ikhtisar
Pengguna dapat mengelola informasi pribadi, pengaturan keamanan, dan preferensi antarmuka melalui halaman Profil dan Pengaturan.

---

## 👤 Profil Pengguna
**Path:** `/profil`  
**Tujuan:** Melihat dan mengedit informasi pribadi dan data kepegawaian.

Halaman ini terbagi menjadi 3 tab utama:

### 1. Tab Pengguna
Fokus pada identitas akun digital Anda.
- **Data**: Nama Lengkap, Alamat Email, dan Foto Profil.
- **Kewenangan**: Daftar izin (*permissions*) yang diberikan kepada akun Anda.
- **Aksi**: Mengubah nama, email, dan mengunggah foto profil baru.

### 2. Tab Kepegawaian
Berisi data resmi Anda sebagai pegawai di database kantor.
- **Data**: NIP, Nama Lengkap (dengan gelar), Jabatan, Pangkat/Golongan, Tempat/Tanggal Lahir, Nomor HP, dan Alamat.
- **Aksi**: Memperbarui data kepegawaian mandiri. 
  *Catatan: Beberapa field mungkin hanya dapat diubah oleh Admin jika dikunci.*

### 3. Tab Ubah Kata Sandi
Pengaturan keamanan akun.
- **Aksi**: Mengubah password lama ke password baru.
- **Persyaratan**: Memerlukan input password saat ini sebagai validasi keamanan.

---

## 🔐 Keamanan dan Kata Sandi
**Path:** `/profil` (Tab Keamanan)

### Mengubah Kata Sandi Anda:
1. Buka halaman Profil.
2. Pilih tab **"Ganti Password"**.
3. Masukkan **Kata Sandi Saat Ini**.
4. Masukkan **Kata Sandi Baru** Anda dua kali untuk konfirmasi.
5. Klik **"Simpan"**.
   *Catatan: Kata sandi harus minimal 8 karakter dan menyertakan angka serta karakter khusus.*

---

## ⚙️ Pengaturan Pengguna
**Path:** `/pengaturan-pengguna`

### Personalisasi Antarmuka Anda:
- **Bahasa**: Beralih antara Bahasa Indonesia dan Bahasa Inggris.
- **Tema**: Beralih antara Mode Terang (Light) dan Mode Gelap (Dark).
- **Notifikasi**: Konfigurasikan preferensi notifikasi email dan push untuk persetujuan dokumen dan pembaruan tugas.

---

## ⚠️ Troubleshooting (Khusus Profil)
**Masalah: Tombol "Simpan" tidak berfungsi**  
**Solusi:** Pastikan semua bidang wajib diisi dengan benar dan kata sandi baru Anda memenuhi persyaratan keamanan.

**Masalah: Tidak bisa mengubah NIP atau Pangkat**  
**Solusi:** Bidang-bidang ini adalah bagian dari catatan master. Jika ada kesalahan pada NIP atau pangkat Anda, hubungi bagian **Admin** untuk memperbaikinya di database pegawai.
