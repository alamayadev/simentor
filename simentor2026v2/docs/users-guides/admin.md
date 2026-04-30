# Panduan Modul Admin (Administrasi Sistem)

## 📋 Ikhtisar
Modul Admin disediakan khusus untuk Super Administrator dan staf TI untuk mengelola akun pengguna, izin, dan konfigurasi sistem secara luas.

## 📊 Halaman dan Fitur

### 1. Pengguna (User Management) 🔐
**Path:** `/admin/pengguna`  
**Tujuan:** Mengelola akun pengguna sistem.

Halaman ini terbagi menjadi 2 tab kategori pengguna:
- **Tab Organik**: Daftar akun untuk pegawai internal dan staf kantor.
- **Tab Mitra**: Daftar akun untuk mitra lapangan dan pengguna eksternal.

#### Membuat Pengguna Baru:
1. **Klik** "Tambah Pengguna".
2. **Isi Informasi:**
   - **Nama Lengkap**: Nama lengkap pengguna.
   - **Email**: Alamat email aktif untuk login.
   - **Password**: Masukkan kata sandi (untuk edit, kosongkan jika tidak diubah).
   - **Peran**: Pilih satu atau lebih peran (misal: super-admin, kepala, katim, staf, mitra).
   - **Izin Akses**: Pilih izin granular (hanya untuk Super Admin).
3. **Klik "Simpan"**.

---

### 2. Peran (Role Management)
**Path:** `/admin/peran`  
**Tujuan:** Mendefinisikan peran dan kemampuan terkaitnya.

#### Mengelola Peran:
- **Tambah/Edit Peran:**
  - **Nama Peran**: Masukkan nama peran (contoh: admin, katim).
- **Edit Izin:** Sesuaikan apa yang dapat dilihat dan dilakukan oleh setiap peran melalui menu aksi.

---

### 3. Izin Akses (Access Permissions)
**Path:** `/admin/izin`  
**Tujuan:** Kontrol granular atas izin sistem.

#### Mengelola Izin:
- **Bulk Create (Tambah Izin):** Masukkan **Prefix** (contoh: `raw-data`) untuk membuat grup izin sekaligus.
- **Edit Izin:** Perbarui **Nama Izin** spesifik (contoh: `raw-data-view`).

---

### 4. Meta Data ⚙️
**Path:** `/admin/metadata`  
**Tujuan:** Mengonfigurasi definisi inti sistem dan tabel referensi.

#### Mengelola Metadata:
- **Tambah/Edit Metadata:**
  - **Nama Metadata**: Label utama untuk data (contoh: Nama Kategori).
  - **Nama Lain**: Label alternatif atau deskripsi singkat.
- **Hierarchical Trees:** Mengelola hubungan induk-anak untuk kategori metadata.
- **Lookup Tables:** Memperbarui daftar standar yang digunakan dalam dropdown di seluruh aplikasi.

---

## 👥 Referensi Hierarki Peran

- **Super Administrator**: Kontrol penuh atas semua modul dan pengguna.
- **Admin Departemen**: Otoritas manajemen dalam departemen tertentu.
- **Supervisor**: Dapat meninjau dan menyetujui dokumen untuk tim mereka.
- **User Biasa (Pegawai Aktif)**: Dapat melakukan tugas harian dan mengelola data pribadi.

---

## ⚠️ Troubleshooting (Khusus Admin)
**Masalah: Pengguna tidak dapat melihat modul tertentu**  
**Solusi:** Periksa peran pengguna di **Admin → Pengguna** dan verifikasi bahwa peran tersebut memiliki izin yang benar di **Admin → Peran**.

**Masalah: Percobaan login gagal**  
**Solusi:** Jika pengguna terkunci, Anda dapat mengatur ulang kata sandi mereka atau membuka kunci akun mereka dari halaman Manajemen Pengguna.
