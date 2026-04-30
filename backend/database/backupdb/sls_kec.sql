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

-- Dumping structure for table simantordb.sls_kec
DROP TABLE IF EXISTS `sls_kec`;
CREATE TABLE IF NOT EXISTS `sls_kec` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kdkec` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nmkec` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jml_sls` smallint NOT NULL,
  `pemeta` smallint DEFAULT NULL,
  `pengawas` smallint DEFAULT NULL,
  `pengawas_organik` smallint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table simantordb.sls_kec: ~30 rows (approximately)
DELETE FROM `sls_kec`;
INSERT INTO `sls_kec` (`id`, `kdkec`, `nmkec`, `jml_sls`, `pemeta`, `pengawas`, `pengawas_organik`) VALUES
	(1, '111', 'MAJALAYA', 235, 17, 3, 1),
	(2, '190', 'BATUJAYA', 178, 12, 1, 1),
	(3, '020', 'CIAMPEL', 136, 9, 1, 1),
	(4, '040', 'KLARI', 624, 39, 6, 1),
	(5, '050', 'CIKAMPEK', 463, 30, 5, 1),
	(6, '112', 'KARAWANG TIMUR', 515, 34, 6, NULL),
	(7, '051', 'PURWASARI', 259, 17, 3, 1),
	(8, '070', 'JATISARI', 293, 17, 3, 1),
	(9, '072', 'KOTABARU', 419, 27, 4, 1),
	(10, '010', 'PANGKALAN', 142, 9, 1, 1),
	(11, '011', 'TEGALWARU', 179, 13, 2, 1),
	(12, '031', 'TELUKJAMBE TIMUR', 452, 31, 6, NULL),
	(13, '032', 'TELUKJAMBE BARAT', 113, 9, 2, NULL),
	(14, '071', 'BANYUSARI', 257, 17, 3, 1),
	(15, '100', 'TELAGASARI', 266, 17, 3, 1),
	(16, '113', 'KARAWANG BARAT', 560, 38, 6, NULL),
	(17, '060', 'TIRTAMULYA', 152, 10, 1, 1),
	(18, '120', 'RAWAMERTA', 230, 16, 2, 1),
	(19, '150', 'RENGASDENGKLOK', 291, 19, 3, 1),
	(20, '081', 'CILAMAYA WETAN', 291, 20, 3, 1),
	(21, '160', 'PEDES', 215, 16, 2, 1),
	(22, '130', 'TEMPURAN', 231, 15, 2, 1),
	(23, '151', 'JAYAKERTA', 164, 11, 1, 1),
	(24, '082', 'CILAMAYA KULON', 207, 14, 2, 1),
	(25, '090', 'LEMAHABANG', 187, 11, 1, 1),
	(26, '140', 'KUTAWALUYA', 157, 13, 2, 1),
	(27, '180', 'TIRTAJAYA', 138, 10, 1, 1),
	(28, '161', 'CILEBAR', 135, 9, 1, 1),
	(29, '170', 'CIBUAYA', 157, 10, 1, 1),
	(30, '200', 'PAKISJAYA', 100, 8, 1, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
