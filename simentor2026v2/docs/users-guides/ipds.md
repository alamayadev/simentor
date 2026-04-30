# Panduan Modul IPDS (Dukungan TI)

## 📋 Ikhtisar
Modul IPDS menyediakan dukungan helpdesk TI dan mengelola aset teknologi organisasi.

## 📊 Halaman dan Fitur

### 1. Tiket (IT Helpdesk)
**Path:** `/ipds/tiket`  
**Tujuan:** Melaporkan dan melacak masalah teknis.

#### Membuat Tiket:
1. **Klik** "Tiket Baru".
2. **Pilih Jenis Keluhan**: Pilih kategori masalah (Sistem, Software, Printer, Jaringan, Akun BPS, Hardware, atau Lainnya).
3. **Deskripsi**: Jelaskan masalah atau keluhan secara detail.
4. **Klik "Simpan"**: Staf TI akan menerima laporan Anda.

#### Update Tiket (Staf TI):
1. **Klik** ikon pensil pada tiket.
2. **Status**: Ubah status penanganan (Pending, Perlu Perbaikan, Selesai).
3. **Keterangan**: Tambahkan catatan teknis atau solusi.

#### Melacak Status:
- **Terbuka (Open):** Menunggu penugasan.
- **Dalam Proses (In Progress):** Sedang dikerjakan oleh tim TI.
- **Ditutup (Closed):** Masalah telah selesai.

---

### 2. Aset TI
**Path:** `/ipds/asset`  
**Tujuan:** Inventaris pusat untuk laptop, komputer, printer, dan peralatan TI lainnya.

#### Menambah/Edit Aset:
1. **Klik** "Tambah Asset" atau ikon pensil pada baris aset.
2. **Isi Informasi:**
   - **Kode & Tipe**: Masukkan kode unik dan pilih tipe (Hardware/Software/Network).
   - **Kategori & Spesifikasi**: Pilih kategori (Laptop, Server, dll.) serta masukkan Brand dan Model.
   - **Identitas**: Serial Number, IP Address, atau License Key (untuk software).
   - **Lokasi & Status**: Tempat aset berada dan status operasionalnya.
   - **Pengguna**: Nama pegawai yang memegang aset.
   - **Tanggal Penting**: Tanggal pembelian, pengiriman, dan berakhirnya garansi.
3. **Klik "Simpan"**.

#### Penjadwalan Pemeliharaan (Maintenance):
1. **Klik ikon panah** pada baris aset untuk melihat detail.
2. **Klik "Tambah"** di bagian Jadwal Maintenance.
3. **Isi Jadwal**: Tentukan tanggal pemeliharaan berikutnya dan tim penanggung jawab.

---

## 🔄 Titik Integrasi
- **Modul Admin**: Kategori aset dan metadata dikelola oleh administrator.
- **Kontraktual**: Pengadaan aset TI baru ditangani melalui kontrak SPK.

## ⚠️ Troubleshooting (Khusus IPDS)
**Masalah: Status tiket tidak berubah**  
**Solusi:** Tiket diperbarui secara manual oleh staf TI. Jika masalah mendesak, pastikan prioritas diatur ke "Tinggi" atau hubungi administrator TI secara langsung.

**Masalah: Aset tidak muncul dalam daftar**  
**Solusi:** Gunakan bilah pencarian untuk menemukan aset berdasarkan nomor seri atau NIP pengguna yang ditugaskan.
