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

-- Dumping structure for table simantordb.sk_detils
DROP TABLE IF EXISTS `sk_detils`;
CREATE TABLE IF NOT EXISTS `sk_detils` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sk_id` int NOT NULL,
  `pegawai_id` int DEFAULT NULL,
  `mitra_id` int DEFAULT NULL,
  `penugasan_id` int DEFAULT NULL,
  `detil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isOrganik` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table simantordb.sk_detils: ~55 rows (approximately)
DELETE FROM `sk_detils`;
INSERT INTO `sk_detils` (`id`, `sk_id`, `pegawai_id`, `mitra_id`, `penugasan_id`, `detil`, `isOrganik`, `created_at`, `updated_at`) VALUES
	(2, 207, 3, NULL, NULL, NULL, 1, '2024-12-28 13:58:49', '2024-12-28 13:58:49'),
	(3, 207, 32, NULL, NULL, NULL, 1, '2024-12-28 14:05:05', '2024-12-28 14:05:05'),
	(4, 207, NULL, 113, NULL, NULL, 0, '2024-12-28 14:07:17', '2024-12-28 14:07:17'),
	(5, 207, NULL, 1062, NULL, NULL, 0, '2024-12-28 14:08:49', '2024-12-28 14:08:49'),
	(6, 723, NULL, 1020, 4, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(7, 723, NULL, 1076, 6, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(8, 723, NULL, 1348, 8, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(9, 723, NULL, 1139, 10, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(10, 723, NULL, 692, 12, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(11, 723, NULL, 1035, 15, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(12, 723, NULL, 1384, 16, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(13, 723, NULL, 1015, 19, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(14, 723, NULL, 1024, 24, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(15, 723, NULL, 1300, 26, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-08 07:36:26', '2025-01-08 07:36:26'),
	(16, 724, NULL, 1158, 30, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(17, 724, NULL, 1048, 31, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(18, 724, NULL, 1228, 32, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(19, 724, NULL, 82, 33, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(20, 724, NULL, 1129, 34, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(21, 724, NULL, 1204, 35, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(22, 724, NULL, 1145, 36, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(23, 724, NULL, 1117, 37, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(24, 724, NULL, 1115, 38, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(25, 724, NULL, 76, 39, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(26, 724, NULL, 1175, 40, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-01-13 07:31:50', '2025-01-13 07:31:50'),
	(49, 726, NULL, 1048, 31, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(50, 726, NULL, 1228, 32, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(51, 726, NULL, 82, 33, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(52, 726, NULL, 1129, 34, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(53, 726, NULL, 1204, 35, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(54, 726, NULL, 1145, 36, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(55, 726, NULL, 1117, 37, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(56, 726, NULL, 1115, 38, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(57, 726, NULL, 76, 39, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(58, 726, NULL, 1175, 40, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(64, 726, NULL, 1158, 360, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 07:43:39', '2025-02-04 07:43:39'),
	(70, 726, 31, NULL, NULL, NULL, 1, '2025-02-04 07:51:16', '2025-02-04 07:51:16'),
	(71, 726, 20, NULL, NULL, NULL, 1, '2025-02-04 07:51:52', '2025-02-04 07:51:52'),
	(72, 726, 13, NULL, NULL, NULL, 1, '2025-02-04 07:52:14', '2025-02-04 07:52:14'),
	(73, 728, NULL, 1175, 41, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:00:10', '2025-02-04 08:00:10'),
	(74, 728, NULL, 1228, 42, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:00:10', '2025-02-04 08:00:10'),
	(75, 728, NULL, 1102, 43, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:00:10', '2025-02-04 08:00:10'),
	(76, 728, NULL, 1145, 44, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:00:10', '2025-02-04 08:00:10'),
	(77, 730, NULL, 1166, 45, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:01:56', '2025-02-04 08:01:56'),
	(78, 730, NULL, 1383, 46, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:01:56', '2025-02-04 08:01:56'),
	(79, 730, NULL, 76, 47, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:01:56', '2025-02-04 08:01:56'),
	(80, 732, NULL, 1368, 349, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:12:24', '2025-02-04 08:12:24'),
	(81, 732, NULL, 1166, 350, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:12:24', '2025-02-04 08:12:24'),
	(82, 732, NULL, 47, 351, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-04 08:12:24', '2025-02-04 08:12:24'),
	(83, 733, NULL, 59, 340, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-05 08:23:41', '2025-02-05 08:23:41'),
	(84, 733, NULL, 1004, 347, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-05 08:23:41', '2025-02-05 08:23:41'),
	(85, 733, NULL, 47, 348, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-05 08:23:41', '2025-02-05 08:23:41'),
	(86, 734, NULL, 59, 344, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-05 08:28:46', '2025-02-05 08:28:46'),
	(87, 734, NULL, 22, 345, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-05 08:28:46', '2025-02-05 08:28:46'),
	(88, 734, NULL, 1327, 346, '"jabatan_tugas,beban_kerja,rate_satuan"', 0, '2025-02-05 08:28:46', '2025-02-05 08:28:46');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
