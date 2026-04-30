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

-- Dumping structure for table simantordb.profil_pegawai
DROP TABLE IF EXISTS `profil_pegawai`;
CREATE TABLE IF NOT EXISTS `profil_pegawai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pangkat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table simantordb.profil_pegawai: ~37 rows (approximately)
DELETE FROM `profil_pegawai`;
INSERT INTO `profil_pegawai` (`id`, `nama`, `pangkat`, `gol`, `nip`, `jabatan`, `kelas`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Robert Ronytua Pardosi, S.Si, MAB', 'Pembina Tingkat I', 'IVb', '19710426 199211 1 001', 'Kepala', '12', 4, NULL, NULL, NULL),
	(2, 'Ir. Mina Nur Aini M.M.', 'Pembina', 'IVa', '19680802 199302 2 001', 'Statistisi Ahli Madya', '11', 6, NULL, NULL, NULL),
	(3, 'Asep Surya, S.ST', 'Penata Tk. I', 'IIId', '19690930 198903 1 001', 'Statistisi Ahli Muda', '9', 2, NULL, NULL, NULL),
	(4, 'Harni Dwi Prikasih, S.ST', 'Penata Tk. I', 'IIId', '19700911 199003 2 001', 'Statistisi Ahli Muda', '9', 3, NULL, NULL, NULL),
	(5, 'Iskandar Zulkarnain, SE, MM', 'Penata Tk. I', 'IIId', '19720317 199312 1 001', 'Pranata Komputer Ahli Muda', '9', 7, NULL, NULL, NULL),
	(6, 'Eko Sucahyono S.Si.', 'Penata Tk. I', 'IIId', '19761012 199901 1 001', 'Kasubbag Umum', '9', 37, NULL, NULL, NULL),
	(7, 'Novi Rinawati, S.ST', 'Penata Tk. I', 'IIId', '19791113 200012 2 001', 'Statistisi Ahli Muda', '9', 14, NULL, NULL, NULL),
	(8, 'Budi Yunior, S.ST', 'Penata Tk. I', 'IIId', '19740609 199302 1 001', 'Pranata Komputer Ahli Muda', '9', 5, NULL, NULL, NULL),
	(9, 'Titi Kurniati, S.ST', 'Penata Tk. I', 'IIId', '19820908 200602 2 001', 'Statistisi Ahli Muda', '9', 18, NULL, NULL, NULL),
	(10, 'Triyono, SE', 'Penata', 'IIIc', '19780314 201101 1 005', 'Statistisi Ahli Muda', '9', 30, 'meninggal', NULL, NULL),
	(11, 'Prima Rudiansah, S.Si', 'Penata', 'IIIc', '19851013 2011011 014', 'Statistisi Ahli Muda', '9', 29, NULL, NULL, NULL),
	(12, 'Nurul Nubuwwati M., S.ST', 'Penata', 'IIIc', '19880725 201012 2 006', 'Statistisi Ahli Muda', '9', 27, NULL, NULL, NULL),
	(13, 'Asep Suryadi', 'Penata', 'IIIc', '19720514 199403 1 004', 'Statistisi Penyelia', '8', 10, NULL, NULL, NULL),
	(14, 'Vinalia Arief Cahyawati, A.Md', 'Penata Muda Tk. I', 'IIIb', '19821126 200502 2 001', 'PK APBN Mahir', '8', 17, NULL, NULL, NULL),
	(15, 'Pramadya Yuyu Ananda, SST', 'Penata Muda Tk. I', 'IIIb', '19940316 201701 1 001', 'Statistisi Ahli Pertama', '8', 33, NULL, NULL, NULL),
	(16, 'Aa Munawar Kholil, S.Kom', 'Penata Muda Tk. I', 'IIIb', '19850605 201101 1 015', 'Statistisi Ahli Pertama', '8', 28, NULL, NULL, NULL),
	(17, 'Friski Ramadhani, S.ST', 'Penata Muda Tk. I', 'IIIb', '19930309 201412 2 001', 'Statistisi Ahli Pertama', '8', 32, NULL, NULL, NULL),
	(18, 'Yedih Wahyudin, SE', 'Penata Muda Tk. I', 'IIIb', '19790714 200112 1 002', 'Statistisi Ahli Pertama', '8', 15, NULL, NULL, NULL),
	(19, 'Agus Syaripudin', 'Penata Muda Tk. I', 'IIIb', '19720723 199403 1 002', 'Statistisi Pelaksana Lanjutan', '7', 11, NULL, NULL, NULL),
	(20, 'Wawan Kurniawan', 'Penata Muda Tk. I', 'IIIb', '19740407 199403 1 002', 'Statistisi Pelaksana Lanjutan', '7', 12, NULL, NULL, NULL),
	(21, 'Arief Kurnia Irawan, A.Md.Kom', 'Penata Muda Tk. I', 'IIIb', '19870408 201003 1 001', 'Statistisi Pelaksana Lanjutan', '7', 25, NULL, NULL, NULL),
	(22, 'Pratiwi Sasti Wahyuni, A.Md', 'Penata Muda Tk. I', 'IIIb', '19871006 201003 2 002', 'Statistisi Pelaksana Lanjutan', '7', 26, NULL, NULL, NULL),
	(23, 'Andika Yunawan Pratomo, A.Md.', 'Penata Muda', 'IIIa', '19890606 201101 1 003', 'Statistisi Pelaksana Lanjutan', '7', 31, NULL, NULL, NULL),
	(24, 'H. Ali Anwar, SP', 'Penata Tk. I', 'IIId', '19710805 199403 1 002', 'Statistisi Ahli Pertama', '8', 9, NULL, NULL, NULL),
	(25, 'Dody Syafrudin', 'Penata Muda Tk. I', 'IIIb', '19730825 199403 1 003', 'Pengolah Data', '6', 8, NULL, NULL, NULL),
	(26, 'Rulis Setya Wardani, A.Md', 'Penata Muda Tk. I', 'IIIb', '19870309 200902 2 012', 'Statistisi Mahir', '7', 23, NULL, NULL, NULL),
	(27, 'Asti Sundariningsih, S.Tr.Stat', 'Penata Muda Tk. I', 'IIIb', '19951107 201901 2 002', 'Statistisi Ahli Pertama', '8', 34, NULL, NULL, NULL),
	(28, 'Inna Viktorina', 'Penata Muda', 'IIIa', '19761229 200604 2 002', 'Pranata Komputer Ahli Pertama', '8', 19, NULL, NULL, NULL),
	(29, 'Suwirno Atma Atmaja', 'Penata Muda', 'IIIa', '19680407 200701 1 007', 'Pengolah Data', '6', 21, NULL, NULL, NULL),
	(30, 'Jaja Sutarja', 'Penata Muda', 'IIIa', '19690714 200701 1 006', 'Pengolah Data', '6', 20, NULL, NULL, NULL),
	(31, 'Agus Rosidi, S.P.', 'Penata Muda', 'IIIa', '19810817 200212 1 003', 'Statistisi Pelaksana', '6', 16, NULL, NULL, NULL),
	(32, 'Aep Saefulloh, S.P', 'Penata Muda', 'IIIa', '19830525 200701 1 009', 'Pengolah Data', '6', 22, NULL, NULL, NULL),
	(33, 'Della Marsita, A.Md', 'Pengatur', 'IIc', '19970305 202203 2 016', 'Statistisi Pelaksana', '6', 35, NULL, NULL, NULL),
	(34, 'Muhammad Hilmy Zaini, A.Md.Stat', 'Pengatur', 'IIc', '19980925 202203 1 008', 'Statistisi Pelaksana', '6', 36, NULL, NULL, NULL),
	(35, 'Didin Hermawan', 'Pengatur Muda Tk. I', 'IIb', '19680617 200901 1 001', 'Pengolah Data', '6', 24, NULL, NULL, NULL),
	(36, 'Evy Djuwita', 'Penata Muda', 'IIIa', '19740308 199503 2 002', 'Pengolah Data', '6', 13, NULL, NULL, NULL),
	(37, 'Ayu Faridah S.Tr.Stat.', 'Penata Muda Tk. I', 'IIIa', '19950516 201901 2 002', 'Statistisi Ahli Pertama\n', '8', 38, NULL, NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
