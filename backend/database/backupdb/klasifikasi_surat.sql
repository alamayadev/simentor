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

-- Dumping structure for table simantordb.klasifikasi_surat
DROP TABLE IF EXISTS `klasifikasi_surat`;
CREATE TABLE IF NOT EXISTS `klasifikasi_surat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint DEFAULT NULL,
  `kode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table simantordb.klasifikasi_surat: ~40 rows (approximately)
DELETE FROM `klasifikasi_surat`;
INSERT INTO `klasifikasi_surat` (`id`, `parent_id`, `kode`, `keterangan`, `created_at`, `updated_at`) VALUES
	(1, 31, 'SS.210', 'Pelatihan Instruktur SENSUS', NULL, NULL),
	(2, 31, 'SS.220', 'Pelatihan petugas SENSUS', NULL, NULL),
	(3, 31, 'SS.230', 'Pelatihan petugas pengolahan SENSUS', NULL, NULL),
	(4, 31, 'SS.300', 'Pelaksanaan Lapangan SENSUS', NULL, NULL),
	(5, 31, 'SS.330', 'Pengumpulan data SENSUS', NULL, NULL),
	(6, 31, 'SS.340', 'Pemeriksaan data SENSUS', NULL, NULL),
	(7, 31, 'SS.350', 'Pengawasan lapangan SENSUS', NULL, NULL),
	(8, 31, 'SS.400', 'PENGOLAHAN SENSUS', NULL, NULL),
	(9, 31, 'VS.210', 'Pelatihan Instruktur SURVEI', NULL, NULL),
	(10, 31, 'VS.220', 'Pelatihan petugas SURVEI', NULL, NULL),
	(11, 31, 'VS.230', 'Pelatihan petugas pengolahan SURVEI', NULL, NULL),
	(12, 31, 'VS.300', 'Pelaksanaan Lapangan SURVEI', NULL, NULL),
	(13, 31, 'VS.310', 'Listing SURVEI', NULL, NULL),
	(14, 31, 'VS.330', 'Pengumpulan data SURVEI', NULL, NULL),
	(15, 31, 'VS.340', 'Pemeriksaan data SURVEI', NULL, NULL),
	(16, 31, 'VS.350', 'Pengawasan lapangan SURVEI', NULL, NULL),
	(17, 31, 'VS.400', 'PENGOLAHAN SURVEI', NULL, NULL),
	(18, 31, 'VS.430', 'Entri data SURVEI', NULL, NULL),
	(19, 31, 'KU.000', 'Pelaksanaan Anggaran', NULL, NULL),
	(20, 31, 'KU.300', 'Pengeluaran Anggaran', NULL, NULL),
	(21, 31, 'KP.300', 'PEMBINAAN KARIR PEGAWAI', NULL, NULL),
	(22, 31, 'KP.310', 'Diklat kursus/ TB/ Ujian dinas/ Izin Belajar Pegawai', NULL, NULL),
	(23, 31, 'KP.311', 'Surat Perintah/ Surat Tugas/ SK/ Surat Izin untuk Diklat/Pelatihan', NULL, NULL),
	(24, 31, 'KP.320', 'Ujian Kompetensi', NULL, NULL),
	(25, 31, 'KP.360', 'Daftar usul penetapan angka kredit fungsional', NULL, NULL),
	(26, 31, 'KP.550', 'Usul pengangkatan dan pemberhentian dalam jabatan struktural/Fungsional', NULL, NULL),
	(27, 31, 'KP.600', 'ADMINISTRASI PEGAWAI', NULL, NULL),
	(28, 31, 'KP.630', 'Berkas perorangan PNS', NULL, NULL),
	(29, 31, 'KP.650', 'Surat perintah dinas/ surat tugas', NULL, NULL),
	(30, 31, 'KP.900', 'Usul pemberhentian dan penetapan pensiun pegawai/ janda/ duda & PNS yang tewas', NULL, NULL),
	(31, NULL, NULL, 'Surat Tugas', NULL, NULL),
	(32, NULL, NULL, 'Form Permintaan', NULL, NULL),
	(33, 32, 'KU.300', 'Belanja Honor', NULL, NULL),
	(34, 32, 'KU.310', 'Belanja Bahan', NULL, NULL),
	(35, 32, 'KU.320', 'Belanja Barang', NULL, NULL),
	(36, 32, 'KU.330', 'Belanja Jasa (Konsultan, Profesi)', NULL, NULL),
	(37, 32, 'KU.340', 'Belanja Perjalanan', NULL, NULL),
	(38, 32, 'KU.350', 'Belanja Pegawai', NULL, NULL),
	(39, 32, 'KU.360', 'Belanja Paket Meeting Dalam Kota', NULL, NULL),
	(40, 32, 'KU.370', 'Belanja Paket Meeting Luar Kota', NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
