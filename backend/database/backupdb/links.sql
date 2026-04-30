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

-- Dumping structure for table simantordb.links
DROP TABLE IF EXISTS `links`;
CREATE TABLE IF NOT EXISTS `links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table simantordb.links: ~50 rows (approximately)
DELETE FROM `links`;
INSERT INTO `links` (`id`, `parent_id`, `nama`, `link`, `created_at`, `updated_at`) VALUES
	(2, NULL, 'Matriks Absensi & Translok', 'https://docs.google.com/spreadsheets/d/15T3holZGaewLCsbIOvNWEvLTpFLXQWm23oykEWjVqd8', NULL, NULL),
	(3, 43, 'Laporan Kegiatan 2024', 'https://drive.google.com/drive/folders/1Voy63xYnwgZq6bD0F_bVJD3qrV5Ke2Js', NULL, NULL),
	(4, NULL, 'Prestasi dan Penghargaan', 'https://drive.google.com/drive/folders/1RhbHKlnOSBoi8e_MIH_CGUPQ_3J7QkgT', NULL, NULL),
	(5, NULL, 'Kumpulan SK', 'https://drive.google.com/drive/folders/1-CMpEKDoJ3gWwds73EgvPuY5VwW3zK-H', NULL, NULL),
	(6, NULL, 'Subbag Umum', 'https://drive.google.com/drive/folders/1SrN_zPhGdXTo2S9wz5QUAxHghjfOxhyn', NULL, NULL),
	(7, NULL, 'CPR/Tim 10', 'https://drive.google.com/drive/folders/10ERBpY9fTagnFgR3Tkyr6wYka84phBak', NULL, NULL),
	(8, NULL, 'ST2023', 'https://drive.google.com/drive/folders/1I4tBVw9IIeMTSKv3yZu7Q2Ybv6hklcfj', NULL, NULL),
	(9, NULL, 'SAKIP', NULL, NULL, NULL),
	(10, 9, 'Dokumen Evaluasi AKIP 2023', 'https://drive.bps.go.id/apps/files/?dir=/%5B02.%20Share%20-%20BPS%20Kabupaten%20Karawang%5D/Dokumen%20Evaluasi%20AKIP%202023&fileid=4477308', NULL, NULL),
	(11, 9, 'SAKIP 2023', 'https://drive.bps.go.id/apps/files/?dir=/%5B02.%20Share%20-%20BPS%20Kabupaten%20Karawang%5D/SAKIP%202023&fileid=2586756', NULL, NULL),
	(12, 9, 'SAKIP 2024', 'https://drive.bps.go.id/apps/files/?dir=/%5B02.%20Share%20-%20BPS%20Kabupaten%20Karawang%5D/SAKIP%202024&fileid=23057084', NULL, NULL),
	(13, NULL, 'Monitoring', NULL, NULL, NULL),
	(14, 13, 'Distribusi & Harga', 'https://docs.google.com/spreadsheets/d/1I1imYPJ3rjcQnJdwqT7BwIO8MICaoIbeAvMHS8ItSf8/edit?gid=1603680808#gid=1603680808', NULL, NULL),
	(15, 13, 'Sosial', 'https://docs.google.com/spreadsheets/d/1aNZVzurjLc6vNMynp84BETw0l69rRdz6vJEA5Clrz-w/edit?gid=1109619672#gid=1109619672', NULL, NULL),
	(16, 13, 'Industri', 'https://docs.google.com/spreadsheets/d/1tTJCNPv20fBnUzqdMalcqhDhTypGK9xv/edit?gid=1764494931#gid=1764494931', NULL, NULL),
	(17, 13, 'Nerwilis', 'https://docs.google.com/spreadsheets/d/1XiSamp3e2SCF9qg4oMx2spUByNWfejR-0MG-Rga38Q4', NULL, NULL),
	(18, 13, 'DLS', 'https://drive.google.com/drive/folders/1YuMWLf_WfCPcEykQLNuznJNPFbfVQq6Y', NULL, NULL),
	(19, 13, 'Pertanian', 'https://docs.google.com/spreadsheets/d/1kyyjNNz-lmYQG56peJs__biAo7JtsFfLokI1zC2n58Q', NULL, NULL),
	(20, 13, 'KTIP', 'https://docs.google.com/spreadsheets/d/15JaWb-CWrD8-7dsLS-lEyKUR-iMPIFrz8aE8jrj-zhU', NULL, NULL),
	(21, NULL, 'Reformasi Birokrasi', NULL, NULL, NULL),
	(22, 21, 'LKE', 'https://docs.google.com/spreadsheets/d/1rJRV445DJ7Xk4kO6xQ94S1aJXyMcNI89', NULL, NULL),
	(23, 21, 'Pendukung', 'https://drive.google.com/drive/folders/1yMOTDexxg-YfG9RMUz4TPbXlw47h6G9K', NULL, NULL),
	(24, NULL, 'Templates', NULL, NULL, NULL),
	(25, 24, 'Surat Tugas Mitra', 'https://docs.google.com/uc?export=download&id=1hSOuZVQCwCCmZiGuFGCNag4ujOUvOZR1', NULL, NULL),
	(26, 24, 'Surat Tugas Organik', 'https://docs.google.com/uc?export=download&id=1RcKLdbxp2F4EmlWzBQFHy0eHrNrJNaiu', NULL, NULL),
	(27, 24, 'Surat Keputusan', 'https://docs.google.com/uc?export=download&id=1wfvd6rH3eOTchLfelR4Kb8LOj2uUYie4', NULL, NULL),
	(28, 24, 'Form Permintaan', 'https://docs.google.com/uc?export=download&id=1QLPumUXBVrT-1qkCKFusjgKldN6MIkni', NULL, NULL),
	(29, 24, 'Laporan harian Perjalanan', 'https://docs.google.com/uc?export=download&id=1nApBBxkj6tLG4WGSa1OSHPmXATV0AAdV', NULL, NULL),
	(30, 24, 'KAK', 'https://docs.google.com/uc?export=download&id=1ukjsYhQhH8ZVxU2TCwC1v7A-REcpZEam', NULL, NULL),
	(31, 24, 'Notulen Rapat', 'https://docs.google.com/uc?export=download&id=1mjwWoQWrUXboeEMWfUtN4gzhQ7YwdY-z', NULL, NULL),
	(32, 24, 'Daftar Hadir', 'https://docs.google.com/uc?export=download&id=1InBh7wAnJTOGUkEkYxuuRLCCx5NkRBcH', NULL, NULL),
	(33, NULL, 'Evaluasi Kinerja', NULL, NULL, NULL),
	(34, 33, 'Upload SKP Bulanan', 'https://drive.google.com/drive/folders/1Jt02IZ3BQQShw4gBYbzSCrwfUHuHVL6m', NULL, NULL),
	(35, 33, 'Aplikasi Catatan Harian', 'https://s.id/catatan-harian', NULL, NULL),
	(36, NULL, 'Kumpulan Materi', NULL, NULL, NULL),
	(37, 36, 'Umum', 'https://drive.google.com/drive/folders/1R16qM0DQ3nu30yKfvkZN620IoWRm4O-v', NULL, NULL),
	(38, 36, 'IPDS', 'https://drive.google.com/drive/folders/13yKWx9cgUOCb0sFiB0lyteTB05b8gWV3', NULL, NULL),
	(39, 36, 'Distribusi', 'https://drive.google.com/drive/folders/1drGUT1wsZ_upbpU4H6HsvpBJbz8GaVwq', NULL, NULL),
	(40, 36, 'Sosial', 'https://drive.google.com/drive/folders/1qlbuFGeDFYpitA_7xcnKRw8mhc4inLLj', NULL, NULL),
	(41, 36, 'Produksi', 'https://drive.google.com/drive/folders/1htaNtDIK7I1oCy6FKeRybqNIYNEHuNEW', NULL, NULL),
	(42, 36, 'Nerwilis', 'https://drive.google.com/drive/folders/16pURicBehhrsA5Q3dLc_didMAOg5wMhz', NULL, NULL),
	(43, NULL, 'Ketua Tim', NULL, NULL, NULL),
	(44, 43, 'Rencana Penyerapan LDS', 'https://docs.google.com/spreadsheets/d/11E8U-x48DnwHJ5DV7oKL94CbfawK7UkWFS1TuGjmdzY/edit?usp=share_link', NULL, NULL),
	(45, 43, 'Matriks Alokasi Petugas', 'https://drive.google.com/drive/folders/1-5h6fvvxytpnZY0Z0G0VVo-UZrbmujec', NULL, NULL),
	(46, 43, 'Laporan Kegiatan 2023', 'https://drive.google.com/drive/folders/1tkkWXlwkrJZvmfKtUKTBIwJuR59NLXt9', NULL, NULL),
	(47, 43, 'Timeline Kegiatan', 'https://drive.google.com/drive/folders/1klVyA3Twsl5bCWzPPcHbOBS7Z5b0zu0L?usp=share_link', NULL, NULL),
	(48, 43, 'Penilaian CKP', 'https://www.google.com/url?q=https%3A%2F%2Fsites.google.com%2Fview%2Fbps-3215%2Fpenilaian-ckp%3Fauthuser%3D0&sa=D&sntz=1&usg=AOvVaw2VJ0T-_zMEvRTjWnwc6VKP', NULL, NULL),
	(49, NULL, 'Catatan Harian Pekerjaan', 'https://sites.google.com/view/bps-3215/catatan-harian-pekerjaan-new', NULL, NULL),
	(50, 9, 'SAKIP 2025', 'https://drive.bps.go.id/apps/files/?dir=/%5B02.%20Share%20-%20BPS%20Kabupaten%20Karawang%5D/SAKIP%202025&fileid=67598789', NULL, NULL),
	(51, 9, 'Dokumen Evaliasi AKIP 2024', 'https://drive.bps.go.id/apps/files/?dir=/%5B02.%20Share%20-%20BPS%20Kabupaten%20Karawang%5D/Dokumen%20Evaluasi%20AKIP%202024&fileid=41570178', NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
