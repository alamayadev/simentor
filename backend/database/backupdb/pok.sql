-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table simantordb.pok
DROP TABLE IF EXISTS `pok`;
CREATE TABLE IF NOT EXISTS `pok` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `komponen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `v` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table simantordb.pok: ~105 rows (approximately)
DELETE FROM `pok`;
INSERT INTO `pok` (`id`, `kode`, `komponen`, `v`, `nama`, `created_at`, `updated_at`) VALUES
	(1, '001', 'Gaji dan Tunjangan', '3', '(001) Gaji dan Tunjangan', NULL, NULL),
	(2, '002', 'Operasional dan Pemeliharaan Kantor', '3', '(002) Operasional dan Pemeliharaan Kantor', NULL, NULL),
	(3, '005', 'Dukungan Penyelenggaraan Tugas dan Fungsi Unit', '3', '(005) Dukungan Penyelenggaraan Tugas dan Fungsi Unit', NULL, NULL),
	(4, '051', 'PERSIAPAN', '3', '(051) PERSIAPAN', NULL, NULL),
	(5, '051', 'Tanpa Komponen', '3', '(051) Tanpa Komponen', NULL, NULL),
	(6, '052', 'PENGUMPULAN DATA', '3', '(052) PENGUMPULAN DATA', NULL, NULL),
	(7, '053', 'PENGOLAHAN DAN ANALISIS', '3', '(053) PENGOLAHAN DAN ANALISIS', NULL, NULL),
	(8, '054', 'DISEMINASI DAN EVALUASI', '3', '(054) DISEMINASI DAN EVALUASI', NULL, NULL),
	(9, '054.01.GG', 'Program Penyediaan dan Pelayanan Informasi Statistik', '9', '(054.01.GG) Program Penyediaan dan Pelayanan Informasi Statistik', NULL, NULL),
	(10, '054.01.WA', 'Program Dukungan Manajemen', '9', '(054.01.WA) Program Dukungan Manajemen', NULL, NULL),
	(11, '056', 'Pengembangan Infrastruktur dan Layanan Teknologi Informasi dan Komunikasi', '3', '(056)  Pengembangan Infrastruktur dan Layanan Teknologi Informasi dan Komunikasi', NULL, NULL),
	(12, '2886', 'Dukungan Manajemen dan Pelaksanaan Tugas Teknis Lainnya BPS Provinsi', '4', '(2886) Dukungan Manajemen dan Pelaksanaan Tugas Teknis Lainnya BPS Provinsi', NULL, NULL),
	(13, '2886.EBA', 'Layanan Dukungan Manajemen Internal[Base Line]', '8', '(EBA) Layanan Dukungan Manajemen Internal[Base Line]', NULL, NULL),
	(14, '2886.EBA.956', 'Layanan BMN', '12', '(2886.EBA.956) Layanan BMN', NULL, NULL),
	(15, '2886.EBA.962', 'Layanan Umum', '12', '(2886.EBA.962) Layanan Umum', NULL, NULL),
	(16, '2886.EBA.994', 'Layanan Perkantoran', '12', '(2886.EBA.994) Layanan Perkantoran', NULL, NULL),
	(17, '2886.EBD', 'Layanan Manajemen Kinerja Internal[Base Line]', '8', '(EBD) Layanan Manajemen Kinerja Internal[Base Line]', NULL, NULL),
	(18, '2886.EBD.955', 'Layanan Manajemen Keuangan', '12', '(2886.EBD.955) Layanan Manajemen Keuangan', NULL, NULL),
	(19, '2896', 'Pengembangan dan Analisis Statistik', '4', '(2896) Pengembangan dan Analisis Statistik', NULL, NULL),
	(20, '2896.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(21, '2896.BMA.004', 'PUBLIKASI/LAPORAN ANALISIS DAN PENGEMBANGAN STATISTIK', '12', '(2896.BMA.004) PUBLIKASI/LAPORAN ANALISIS DAN PENGEMBANGAN STATISTIK', NULL, NULL),
	(22, '2897', 'Pelayanan dan Pengembangan Diseminasi Informasi Statistik', '4', '(2897) Pelayanan dan Pengembangan Diseminasi Informasi Statistik', NULL, NULL),
	(23, '2897.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(24, '2897.BMA.004', 'LAPORAN DISEMINASI DAN METADATA STATISTIK', '12', '(2897.BMA.004) LAPORAN DISEMINASI DAN METADATA STATISTIK', NULL, NULL),
	(25, '2897.QDB', 'Fasilitasi dan Pembinaan Lembaga[Base Line]', '8', '(QDB) Fasilitasi dan Pembinaan Lembaga[Base Line]', NULL, NULL),
	(26, '2897.QDB.003', 'PENGUATAN PENYELENGGARAAN PEMBINAAN STATISTIK SEKTORAL', '12', '(2897.QDB.003) PENGUATAN PENYELENGGARAAN PEMBINAAN STATISTIK SEKTORAL', NULL, NULL),
	(27, '2898', 'Penyediaan dan Pengembangan Statistik Neraca Pengeluaran', '4', '(2898) Penyediaan dan Pengembangan Statistik Neraca Pengeluaran', NULL, NULL),
	(28, '2898.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(29, '2898.BMA.007', 'PUBLIKASI/LAPORAN STATISTIK NERACA PENGELUARAN', '12', '(2898.BMA.007) PUBLIKASI/LAPORAN STATISTIK NERACA PENGELUARAN', NULL, NULL),
	(30, '2899', 'Penyediaan dan Pengembangan Statistik Neraca Produksi', '4', '(2899) Penyediaan dan Pengembangan Statistik Neraca Produksi', NULL, NULL),
	(31, '2899.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(32, '2899.BMA.006', 'PUBLIKASI/LAPORAN NERACA PRODUKSI', '12', '(2899.BMA.006) PUBLIKASI/LAPORAN NERACA PRODUKSI', NULL, NULL),
	(33, '2900', 'Pengembangan Metodologi Sensus dan Survei', '4', '(2900) Pengembangan Metodologi Sensus dan Survei', NULL, NULL),
	(34, '2900.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(35, '2900.BMA.005', 'DOKUMEN/LAPORAN PENGEMBANGAN METODOLOGI KEGIATAN STATISTIK', '12', '(2900.BMA.005) DOKUMEN/LAPORAN PENGEMBANGAN METODOLOGI KEGIATAN STATISTIK', NULL, NULL),
	(36, '2901', 'Pengembangan Sistem Informasi Statistik', '4', '(2901) Pengembangan Sistem Informasi Statistik', NULL, NULL),
	(37, '2901.CAN', 'Sarana Bidang Teknologi Informasi dan Komunikasi[Base Line]', '8', '(CAN) Sarana Bidang Teknologi Informasi dan Komunikasi[Base Line]', NULL, NULL),
	(38, '2901.CAN.004', 'Pengembangan Infrastruktur dan Layanan Teknologi Informasi dan Komunikasi', '12', '(2901.CAN.004) Pengembangan Infrastruktur dan Layanan Teknologi Informasi dan Komunikasi', NULL, NULL),
	(39, '2902', 'Penyediaan dan Pengembangan Statistik Distribusi', '4', '(2902) Penyediaan dan Pengembangan Statistik Distribusi', NULL, NULL),
	(40, '2902.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(41, '2902.BMA.004', 'PUBLIKASI/LAPORAN STATISTIK DISTRIBUSI', '12', '(2902.BMA.004) PUBLIKASI/LAPORAN STATISTIK DISTRIBUSI', NULL, NULL),
	(42, '2902.BMA.006', 'PUBLIKASI/LAPORAN SENSUS EKONOMI', '12', '(2902.BMA.006) PUBLIKASI/LAPORAN SENSUS EKONOMI', NULL, NULL),
	(43, '2903', 'Penyediaan dan Pengembangan Statistik Harga', '4', '(2903) Penyediaan dan Pengembangan Statistik Harga', NULL, NULL),
	(44, '2903.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(45, '2903.BMA.009', 'PUBLIKASI/LAPORAN STATISTIK HARGA', '12', '(2903.BMA.009) PUBLIKASI/LAPORAN STATISTIK HARGA', NULL, NULL),
	(46, '2904', 'Penyediaan dan Pengembangan Statistik Industri, Pertambangan dan Penggalian, Energi, dan Konstruksi', '4', '(2904) Penyediaan dan Pengembangan Statistik Industri, Pertambangan dan Penggalian, Energi, dan Konstruksi', NULL, NULL),
	(47, '2904.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(48, '2904.BMA.006', 'PUBLIKASI/LAPORAN STATISTIK INDUSTRI, PERTAMBANGAN DAN PENGGALIAN, ENERGI, DAN KONSTRUKSI', '12', '(2904.BMA.006) PUBLIKASI/LAPORAN STATISTIK INDUSTRI, PERTAMBANGAN DAN PENGGALIAN, ENERGI, DAN KONSTRUKSI', NULL, NULL),
	(49, '2905', 'Penyediaan dan Pengembangan Statistik Kependudukan dan Ketenagakerjaan', '4', '(2905) Penyediaan dan Pengembangan Statistik Kependudukan dan Ketenagakerjaan', NULL, NULL),
	(50, '2905.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(51, '2905.BMA.004', 'PUBLIKASI/LAPORAN SAKERNAS', '12', '(2905.BMA.004) PUBLIKASI/LAPORAN SAKERNAS', NULL, NULL),
	(52, '2905.BMA.005', 'PUBLIKASI/LAPORAN STATISTIK KEPENDUDUKAN DAN KETENAGAKERJAAN', '12', '(2905.BMA.005) PUBLIKASI/LAPORAN STATISTIK KEPENDUDUKAN DAN KETENAGAKERJAAN', NULL, NULL),
	(53, '2906', 'Penyediaan dan Pengembangan Statistik Kesejahteraan Rakyat', '4', '(2906) Penyediaan dan Pengembangan Statistik Kesejahteraan Rakyat', NULL, NULL),
	(54, '2906.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(55, '2906.BMA.003', 'PUBLIKASI/LAPORAN STATISTIK KESEJAHTERAAN RAKYAT', '12', '(2906.BMA.003) PUBLIKASI/LAPORAN STATISTIK KESEJAHTERAAN RAKYAT', NULL, NULL),
	(56, '2906.BMA.006', 'PUBLIKASI/LAPORAN SUSENAS', '12', '(2906.BMA.006) PUBLIKASI/LAPORAN SUSENAS', NULL, NULL),
	(57, '2907', 'Penyediaan dan Pengembangan Statistik Ketahanan Sosial', '4', '(2907) Penyediaan dan Pengembangan Statistik Ketahanan Sosial', NULL, NULL),
	(58, '2907.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(59, '2907.BMA.006', 'PUBLIKASI/LAPORAN STATISTIK KETAHANAN SOSIAL', '12', '(2907.BMA.006) PUBLIKASI/LAPORAN STATISTIK KETAHANAN SOSIAL', NULL, NULL),
	(60, '2907.BMA.008', 'PUBLIKASI/LAPORAN PENDATAAN PODES', '12', '(2907.BMA.008) PUBLIKASI/LAPORAN PENDATAAN PODES', NULL, NULL),
	(61, '2908', 'Penyediaan dan Pengembangan Statistik Keuangan, Teknologi Informasi, dan Pariwisata', '4', '(2908) Penyediaan dan Pengembangan Statistik Keuangan, Teknologi Informasi, dan Pariwisata', NULL, NULL),
	(62, '2908.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(63, '2908.BMA.004', 'PUBLIKASI/LAPORAN STATISTIK KEUANGAN, TEKNOLOGI INFORMASI, DAN PARIWISATA', '12', '(2908.BMA.004) PUBLIKASI/LAPORAN STATISTIK KEUANGAN, TEKNOLOGI INFORMASI, DAN PARIWISATA', NULL, NULL),
	(64, '2908.BMA.009', 'PUBLIKASI/LAPORAN STATISTIK E-COMMERCE', '12', '(2908.BMA.009) PUBLIKASI/LAPORAN STATISTIK E-COMMERCE', NULL, NULL),
	(65, '2909', 'Penyediaan dan Pengembangan Statistik Peternakan, Perikanan, dan Kehutanan', '4', '(2909) Penyediaan dan Pengembangan Statistik Peternakan, Perikanan, dan Kehutanan', NULL, NULL),
	(66, '2909.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(67, '2909.BMA.005', 'PUBLIKASI/LAPORAN STATISTIK PETERNAKAN, PERIKANAN, DAN KEHUTANAN', '12', '(2909.BMA.005) PUBLIKASI/LAPORAN STATISTIK PETERNAKAN, PERIKANAN, DAN KEHUTANAN', NULL, NULL),
	(68, '2910', 'Penyediaan dan Pengembangan Statistik Tanaman Pangan, Hortikultura, dan Perkebunan', '4', '(2910) Penyediaan dan Pengembangan Statistik Tanaman Pangan, Hortikultura, dan Perkebunan', NULL, NULL),
	(69, '2910.BMA', 'Data dan Informasi Publik[Base Line]', '8', '(BMA) Data dan Informasi Publik[Base Line]', NULL, NULL),
	(70, '2910.BMA.007', 'PUBLIKASI/ LAPORAN STATISTIK TANAMAN PANGAN', '12', '(2910.BMA.007) PUBLIKASI/ LAPORAN STATISTIK TANAMAN PANGAN', NULL, NULL),
	(71, '2910.BMA.008', 'PUBLIKASI/LAPORAN STATISTIK HORTIKULTURA DAN PERKEBUNAN', '12', '(2910.BMA.008) PUBLIKASI/LAPORAN STATISTIK HORTIKULTURA DAN PERKEBUNAN', NULL, NULL),
	(72, '506', 'Pemutakhiran Kerangka Geospasial dan Muatan Wilkerstat', '3', '(506) Pemutakhiran Kerangka Geospasial dan Muatan Wilkerstat', NULL, NULL),
	(73, '511111', 'Belanja Gaji Pokok PNS', '6', '(511111) Belanja Gaji Pokok PNS', NULL, NULL),
	(74, '511119', 'Belanja Pembulatan Gaji PNS', '6', '(511119) Belanja Pembulatan Gaji PNS', NULL, NULL),
	(75, '511121', 'Belanja Tunj. Suami/Istri PNS', '6', '(511121) Belanja Tunj. Suami/Istri PNS', NULL, NULL),
	(76, '511122', 'Belanja Tunj. Anak PNS', '6', '(511122) Belanja Tunj. Anak PNS', NULL, NULL),
	(77, '511123', 'Belanja Tunj. Struktural PNS', '6', '(511123) Belanja Tunj. Struktural PNS', NULL, NULL),
	(78, '511124', 'Belanja Tunj. Fungsional PNS', '6', '(511124) Belanja Tunj. Fungsional PNS', NULL, NULL),
	(79, '511125', 'Belanja Tunj. PPh PNS', '6', '(511125) Belanja Tunj. PPh PNS', NULL, NULL),
	(80, '511126', 'Belanja Tunj. Beras PNS', '6', '(511126) Belanja Tunj. Beras PNS', NULL, NULL),
	(81, '511129', 'Belanja Uang Makan PNS', '6', '(511129) Belanja Uang Makan PNS', NULL, NULL),
	(82, '511151', 'Belanja Tunjangan Umum PNS', '6', '(511151) Belanja Tunjangan Umum PNS', NULL, NULL),
	(83, '512211', 'Belanja Uang Lembur', '6', '(512211) Belanja Uang Lembur', NULL, NULL),
	(84, '512411', 'Belanja Pegawai (Tunjangan Khusus/Kegiatan/Kinerja)', '6', '(512411) Belanja Pegawai (Tunjangan Khusus/Kegiatan/Kinerja)', NULL, NULL),
	(85, '516', 'Updating Direktori Usaha/Perusahaan Ekonomi Lanjutan', '3', '(516) Updating Direktori Usaha/Perusahaan Ekonomi Lanjutan', NULL, NULL),
	(86, '519', 'Penyusunan Bahan Publisitas', '3', '(519) Penyusunan Bahan Publisitas', NULL, NULL),
	(87, '521111', 'Belanja Keperluan Perkantoran', '6', '(521111) Belanja Keperluan Perkantoran', NULL, NULL),
	(88, '521115', 'Belanja Honor Operasional Satuan Kerja', '6', '(521115) Belanja Honor Operasional Satuan Kerja', NULL, NULL),
	(89, '521119', 'Belanja Barang Operasional Lainnya', '6', '(521119) Belanja Barang Operasional Lainnya', NULL, NULL),
	(90, '521211', 'Belanja Bahan', '6', '(521211) Belanja Bahan', NULL, NULL),
	(91, '521213', 'Belanja Honor Output Kegiatan', '6', '(521213) Belanja Honor Output Kegiatan', NULL, NULL),
	(92, '521219', 'Belanja Barang Non Operasional Lainnya', '6', '(521219) Belanja Barang Non Operasional Lainnya', NULL, NULL),
	(93, '521811', 'Belanja Barang Persediaan Barang Konsumsi', '6', '(521811) Belanja Barang Persediaan Barang Konsumsi', NULL, NULL),
	(94, '522111', 'Belanja Langganan Listrik', '6', '(522111) Belanja Langganan Listrik', NULL, NULL),
	(95, '522112', 'Belanja Langganan Telepon', '6', '(522112) Belanja Langganan Telepon', NULL, NULL),
	(96, '522113', 'Belanja Langganan Air', '6', '(522113) Belanja Langganan Air', NULL, NULL),
	(97, '522119', 'Belanja Langganan Daya dan Jasa Lainnya', '6', '(522119) Belanja Langganan Daya dan Jasa Lainnya', NULL, NULL),
	(98, '522151', 'Belanja Jasa Profesi', '6', '(522151) Belanja Jasa Profesi', NULL, NULL),
	(99, '522191', 'Belanja Jasa Lainnya', '6', '(522191) Belanja Jasa Lainnya', NULL, NULL),
	(100, '523111', 'Belanja Pemeliharaan Gedung dan Bangunan', '6', '(523111) Belanja Pemeliharaan Gedung dan Bangunan', NULL, NULL),
	(101, '523121', 'Belanja Pemeliharaan Peralatan dan Mesin', '6', '(523121) Belanja Pemeliharaan Peralatan dan Mesin', NULL, NULL),
	(102, '524111', 'Belanja Perjalanan Dinas Biasa', '6', '(524111) Belanja Perjalanan Dinas Biasa', NULL, NULL),
	(103, '524113', 'Belanja Perjalanan Dinas Dalam Kota', '6', '(524113) Belanja Perjalanan Dinas Dalam Kota', NULL, NULL),
	(104, '524114', 'Belanja Perjalanan Dinas Paket Meeting Dalam Kota', '6', '(524114) Belanja Perjalanan Dinas Paket Meeting Dalam Kota', NULL, NULL),
	(105, 'A', 'TANPA SUB KOMPONEN', '1', '(A) TANPA SUB KOMPONEN', NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
