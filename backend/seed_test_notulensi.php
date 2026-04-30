<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Notulensi;

// Clear existing test data if any
Notulensi::where('judul', 'TEST RAPAT PLENO')->delete();

$notulensi = Notulensi::create([
    'judul' => 'TEST RAPAT PLENO',
    'instansi' => 'BPS Kabupaten Karawang',
    'kegiatan' => 'Pembangunan Dashboard Simentor',
    'topik' => 'Pembahasan Progres Simentor v1.5',
    'tanggal_rapat' => '2026-01-20',
    'waktu_mulai' => '08:30',
    'waktu_selesai' => '11:15',
    'tempat' => 'Ruang Rapat Utama',
    'jabatan_pimpinan_rapat' => 'Kepala BPS Kabupaten Karawang',
    'nip_pimpinan' => '198001012005011001',
    'nip_notulis' => '199505052020012002',
    'peserta' => array_map(fn($i) => "Peserta ke-$i", range(1, 30)),
    'agenda' => '<ul>' . str_repeat('<li>Pembahasan poin ke-' . uniqid() . ' yang sangat panjang untuk memastikan dokumen melebihi satu halaman dan memicu page break agar kita bisa melihat apakah header muncul di halaman kedua.</li>', 50) . '</ul>',
    'resume' => '<strong>Hasil:</strong> Semua fitur berjalan lancar. Perlu tambahan NIP sudah diakomodasi.' . str_repeat(' Ini adalah teks tambahan untuk memanjangkan resume.', 20),
    'tanya_jawab' => 'Q: Apakah PDF sudah oke? A: Sedang diuji.' . str_repeat(' Ini adalah teks tambahan untuk memanjangkan tanya jawab.', 20),
    'kategori' => 4, // Penataan Tata Laksana
]);

echo "Created Notulensi ID: " . $notulensi->id . "\n";
