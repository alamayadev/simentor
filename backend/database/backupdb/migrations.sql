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

-- Dumping structure for table simantordb.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table simantordb.migrations: ~32 rows (approximately)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2024_04_24_000001_add_user_social_provider_table', 1),
	(5, '2024_04_24_000002_update_passwords_field_to_be_nullable', 1),
	(6, '2024_12_11_061526_add_two_factor_columns_to_users_table', 1),
	(7, '2024_12_11_061554_create_personal_access_tokens_table', 1),
	(8, '2024_12_12_112447_create_permission_tables', 1),
	(9, '2024_12_14_081114_create_tamus_table', 1),
	(10, '2024_12_14_081454_create_pengaduans_table', 1),
	(11, '2024_12_15_114230_create_links_table', 1),
	(12, '2024_12_18_220324_create_kegiatan_table', 1),
	(13, '2024_12_18_220324_create_mitra_kepka_table', 1),
	(14, '2024_12_18_220324_create_penugasan_table', 1),
	(15, '2024_12_18_220324_create_settings_table', 1),
	(16, '2024_12_18_220324_create_skps_table', 1),
	(17, '2024_12_18_221943_create_data_surtug_table', 1),
	(18, '2024_12_18_222351_create_data_surat_spk_bast_table', 1),
	(19, '2024_12_18_222704_create_data_surat_keluar_table', 1),
	(20, '2024_12_18_222855_create_data_form_permintaan_table', 1),
	(21, '2024_12_20_092655_create_surtug_detils_table', 1),
	(22, '2024_12_26_162552_create_uu_tambahans_table', 1),
	(23, '2024_12_27_203151_create_uus_table', 1),
	(24, '2024_12_28_174448_create_sk_detils_table', 1),
	(25, '2025_01_01_183109_create_klasifikasi_surats_table', 1),
	(26, '2025_01_01_185535_create_profil_pegawais_table', 1),
	(27, '2025_01_01_190051_create_poks_table', 1),
	(28, '2025_01_05_200231_create_bast_detils_table', 1),
	(29, '2025_01_06_113813_create_sls_kecs_table', 1),
	(30, '2025_01_06_113919_create_sls2024s_table', 1),
	(31, '2025_01_14_074833_create_hardware_table', 2),
	(32, '2025_01_14_075225_create_tikets_table', 2);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
