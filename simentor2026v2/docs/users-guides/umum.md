# Panduan Modul Umum (Operasional Umum)

## 📋 Ikhtisar
Modul Umum adalah pusat administrasi Simentor. Modul ini menangani data master pegawai, dokumen resmi, dan manajemen anggaran (RKK DIPA).

---

## 👥 Manajemen Pegawai 🔴
**Path:** `/umum/pegawai`  
**Tujuan:** Mengelola database pusat pegawai.

### Menambah/Edit Pegawai:
1. **Buka** Umum → Pegawai.
2. **Klik** "Tambah" (ikon +) atau ikon pensil pada baris pegawai.
3. **Isi Informasi:**
   - **Nama**: Nama lengkap dengan gelar.
   - **NIP**: 18 digit ID unik.
   - **Pangkat/Golongan**: Pangkat dan golongan pegawai.
   - **Jabatan**: Nama jabatan saat ini.
   - **Kelas Jabatan**: Angka kelas jabatan.
   - **Unit Kerja**: Bagian/fungsi tempat bertugas.
   - **Email**: Alamat email resmi.
   - **No. HP**: Nomor telepon aktif.
   - **Organik?**: Centang jika pegawai organik (internal).
   - **Akun Pengguna**: Pilih untuk menghubungkan pegawai dengan akun di modul **Admin Pengguna**.
4. **Klik "Simpan"**.

#### Kesalahan Umum:
- **"NIP sudah terdaftar"**: Pegawai sudah ada di sistem.
- **"Harap isi field yang wajib"**: Satu atau lebih bidang yang ditandai dengan * masih kosong.

---

## 📋 Alur Kerja Dokumen (Surat) 🔴

### 1. Surat Tugas
**Path:** `/umum/surat/tugas`  
**Tujuan:** Membuat penugasan resmi untuk personel.

#### Alur Kerja:
1. **Inisialisasi:** Atur nomor dokumen, tanggal, dan perihal.
2. **Penugasan Personel:** Tambah satu atau lebih pegawai. Atur peran mereka (Ketua, Anggota) dan tanggal spesifik.
3. **Pemilihan Kegiatan:** Hubungkan penugasan dengan kegiatan tertentu dari katalog.
4. **Persetujuan:** Kirim untuk persetujuan digital.
5. **Cetak:** Setelah disetujui, hasilkan PDF resmi untuk didistribusikan.

### 2. Surat Keputusan
**Path:** `/umum/surat/keputusan`  
**Tujuan:** Keputusan hukum resmi (misalnya, promosi, pengangkatan).

#### Alur Kerja:
1. **Kategori:** Pilih jenis keputusan (Personalia, Keuangan, dll.).
2. **Konten:** Isi bagian hukum standar: *Mempertimbangkan*, *Mengingat*, dan *Memutuskan*.
3. **Pihak Terkait:** Tambah individu atau unit yang terpengaruh oleh keputusan tersebut.
4. **Finalisasi:** Kirim untuk persetujuan tingkat tinggi.

### 3. Surat Keluar (Penomoran)
**Path:** `/umum/surat/keluar`  
**Tujuan:** Mengelola penomoran, isi, pratinjau, dan cetak surat keluar kantor.

#### Membuat/Edit Surat Keluar:
1. **Isi Informasi Utama:**
   - **Tahun**: Tahun berjalan.
   - **Tanggal**: Tanggal surat.
   - **Nomor Urut**: Dihasilkan otomatis atau diisi manual.
   - **Dari**: Pengirim surat (Unit Kerja).
   - **Tujuan**: Nama/Instansi penerima.
   - **Perihal**: Ringkasan isi surat.
   - **Lampiran**: Jumlah lampiran.
   - **Sifat**: Pilihan (Biasa, Penting, Segera, Rahasia).
2. **Isi Surat**: Gunakan editor teks untuk menyusun konten surat.
3. **Simpan & Cetak**: Simpan data untuk mendapatkan nomor resmi dan gunakan fitur pratinjau untuk mencetak.

### 4. Surat Permintaan (Penomoran)
**Path:** `/umum/surat/permintaan`  
**Tujuan:** Mengelola penomoran dan arsip surat permintaan data serta layanan.

#### Membuat Nomor Surat Permintaan:
1. **Isi Informasi:**
   - **Tanggal**: Tanggal surat permintaan.
   - **Tahun**: Tahun berjalan.
   - **Nomor Urut**: Dihasilkan otomatis.
   - **Klasifikasi**: Pilih kode klasifikasi surat.
   - **Pengirim / Asal Surat**: Instansi/Orang yang meminta.
   - **Perihal**: Deskripsi singkat permintaan.
2. **Klik "Simpan"**.

---

## 💰 Manajemen Anggaran (RKK DIPA) 🔴
**Path:** `/umum/rkk-dipa/*`

### 1. Perencanaan
**Path:** `/umum/rkk-dipa/perencanaan`
Merencanakan alokasi anggaran tahunan berdasarkan kategori.

Halaman ini terbagi menjadi 2 tab utama:
- **Tab Input Rencana**: Digunakan untuk mencari sumber budget dari DIPA (menggunakan kode atau uraian) dan memasukkan rencana jumlah pencairan serta bulan target.
- **Tab Daftar Rencana**: Menampilkan semua rencana yang telah dibuat. Mendukung tampilan **Bulanan** (daftar urut waktu) dan **Tahunan** (matriks ringkasan per akun).

### 2. Monitoring
**Path:** `/umum/rkk-dipa/monitoring`
Melacak realisasi anggaran secara real-time. Membandingkan perencanaan vs. pengeluaran aktual.

Halaman ini memiliki 2 tampilan tab:
- **Tab Ringkasan & Chart**: Menampilkan kartu statistik (Pagu, Realisasi, Sisa) dan grafik batang perbandingan rencana vs realisasi setiap bulan.
- **Tab Tabel Detail**: Menampilkan tabel rinci serapan anggaran untuk setiap item kegiatan/belanja.

### 3. Pencairan
**Path:** `/umum/rkk-dipa/pencairan`
Mengajukan pencairan dana untuk kegiatan atau kontrak tertentu. Memerlukan dokumen pendukung (invoice, kuitansi).

---

## ⚙️ Fitur Umum Lainnya

### Polink
**Path:** `/umum/polink`  
Tautan akses cepat ke sistem eksternal BPS.

### Hari Libur
**Path:** `/umum/libur`  
Mengelola kalender organisasi.

#### Menambah Hari Libur:
- **Tanggal**: Pilih tanggal libur.
- **Keterangan**: Nama hari libur (contoh: Idul Fitri).
- **Libur Nasional?**: Centang jika merupakan libur resmi nasional.

### Pengaturan
**Path:** `/umum/pengaturan`  
Konfigurasi sistem untuk modul Umum.

#### Mengelola Pengaturan:
- **Key**: Kode unik pengaturan (contoh: `ALAMAT_KANTOR`).
- **Value**: Nilai pengaturan.
- **Grup**: Pengelompokan (contoh: `Surat Keluar`).
- **Deskripsi**: Penjelasan fungsi pengaturan.

---

## ⚠️ Troubleshooting (Khusus Umum)
**Masalah: Tidak dapat menghapus pegawai**  
**Solusi:** Anda tidak dapat menghapus pegawai yang memiliki riwayat transaksi atau penugasan aktif. Gunakan status "Nonaktif" sebagai gantinya.

**Masalah: Bentrok nomor dokumen**  
**Solusi:** Sistem menghasilkan nomor secara otomatis, tetapi jika Anda memasukkannya secara manual, pastikan urutannya belum digunakan pada tahun fiskal berjalan.
