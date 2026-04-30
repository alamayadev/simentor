# Panduan Modul Kontraktual (SPK & BAST)

## 📋 Ikhtisar
Modul Kontraktual mengelola kontrak eksternal (Surat Perintah Kerja - SPK) dan berita acara serah terima (BAST).

## 📊 Halaman dan Fitur

### 1. Manajemen SPK & BAST
**Path:** `/kontraktual/spk-bast`  
**Tujuan:** Membuat dan mengelola dokumen kontrak resmi.

#### Mengelola SPK & BAST (Bulk Update):
1. **Filter Bulan Bayar**: Pilih bulan bayar di bagian kanan atas tabel.
2. **Pilih Baris**: Centang satu atau lebih mitra yang akan diproses.
3. **Klik "Bulk SPK" atau "Bulk BAST"**:
   - **Tanggal SPK/BAST**: Pilih tanggal melalui kalender.
4. **Klik "Terapkan"**: Sistem akan otomatis menghasilkan nomor dan tanggal untuk semua baris yang dipilih.

---

### 2. Monitoring
**Path:** `/kontraktual/monitoring`  
**Tujuan:** Melacak kemajuan dokumen dan nilai kontrak aktif.

Halaman ini terbagi menjadi 3 tab filter utama:
- **Tab Tanpa SPK**: Menampilkan daftar penugasan atau kegiatan yang sudah berjalan tetapi belum memiliki nomor SPK resmi.
- **Tab Tanpa BAST**: Menampilkan daftar kontrak yang sudah selesai atau sedang berjalan tetapi belum mengunggah Berita Acara Serah Terima (BAST).
- **Tab Diatas 4JT**: Filter khusus untuk memantau kontrak dengan nilai nominal di atas Rp 4.000.000 yang memerlukan perhatian administratif lebih detail.

#### Fitur Tambahan:
- **Filter Bulan**: Mempersempit daftar berdasarkan bulan bayar.
- **Kartu Statistik**: Ringkasan cepat jumlah data, data tanpa dokumen, dan kontrak bernilai besar.

---

## 🔄 Titik Integrasi
- **RKK DIPA**: Nilai kontrak harus berada dalam jalur anggaran yang dialokasikan yang didefinisikan dalam modul RKK DIPA.
- **Bank Data**: Kontrak dan tanda terima yang telah difinalisasi sering kali diarsipkan dalam modul Bank Data.

## ⚠️ Troubleshooting (Khusus Kontraktual)
**Masalah: Nilai kontrak melebihi anggaran**  
**Solusi:** Verifikasi saldo yang tersisa di **Umum → RKK DIPA → Monitoring**. Anda mungkin memerlukan revisi anggaran jika alokasi tidak mencukupi.

**Masalah: Tidak dapat mengunggah lampiran**  
**Solusi:** Pastikan file dalam format PDF atau JPG dan tidak melebihi batas ukuran (biasanya 5MB).
