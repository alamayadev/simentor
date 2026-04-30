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

-- Dumping structure for table simantordb.settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tahun` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `grup` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table simantordb.settings: ~19 rows (approximately)
DELETE FROM `settings`;
INSERT INTO `settings` (`id`, `tahun`, `key`, `value`, `grup`, `created_at`, `updated_at`) VALUES
	(1, '2025', 'PPK', 'Asep Surya, S.ST', 2, '2023-12-31 17:00:00', NULL),
	(2, '2025', 'FORMAT_NO_SPK', '/3215/PPK/SPK/', 4, '2023-12-31 17:00:00', NULL),
	(3, '2025', 'NAMA_KANTOR', 'Badan Pusat Statistik Kabupaten Karawang', 1, '2023-12-31 17:00:00', NULL),
	(4, '2025', 'ALAMAT_KANTOR', 'Jl. Cakradireja No 36 Nagasari Karawang', 1, '2023-12-31 17:00:00', NULL),
	(5, '2025', 'KEPALA_KANTOR', 'Robert Ronytua Pardosi, S.Si, MAB', 2, '2023-12-31 17:00:00', NULL),
	(6, '2025', 'KODE_RING', '054.01.GG.', 1, '2023-12-31 17:00:00', NULL),
	(8, '2025', 'TAHUN_KEGIATAN', '2025', 3, '2023-12-31 17:00:00', NULL),
	(9, '2025', 'TAHUN_SPK', '2025', 3, '2023-12-31 17:00:00', NULL),
	(10, '2025', 'TAHUN_BAST', '2025', 3, '2023-12-31 17:00:00', NULL),
	(11, '2025', 'NILAI_MAX_SPK', '4000000', 3, '2023-12-31 17:00:00', NULL),
	(12, '2025', 'FORMAT_NO_BAST', '/3215/PPK/BAST/', 4, '2023-12-31 17:00:00', NULL),
	(13, '2025', 'NIP_PPK', '19690930 198903 1 001', 2, '2023-12-31 17:00:00', NULL),
	(14, '2025', 'FORMAT_SURTUG', 'B-{nomor}/32150/{klasifikasi}/{tahun}', 4, '2023-12-31 17:00:00', NULL),
	(15, '2025', 'FORMAT_SURAT_KELUAR', 'B-{nomor}/32150/KA.220/{tahun}', 4, '2023-12-31 17:00:00', NULL),
	(16, '2025', 'NIP_KEPALA', '19710426 1992 1 11001', 2, '2023-12-31 17:00:00', NULL),
	(17, '2025', 'FORMAT_FORM_PERMINTAAN', 'B-{nomor}/32150/{klas}/{tahun}', 4, '2023-12-31 17:00:00', NULL),
	(18, '2025', 'FORMAT_SK', '32150.{nomor}/{bln}/{tahun}', 4, '2023-12-31 17:00:00', NULL),
	(19, '2025', 'NOMOR_DIPA', 'SP DIPA- 054.01.2.018686/2025', 3, '2024-12-26 17:00:00', NULL),
	(20, '2025', 'TANGGAL_DIPA', '02 Desember 2024', 3, '2024-12-26 17:00:00', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
