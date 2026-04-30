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

-- Dumping structure for view simantordb.002_rekap_landmark
DROP VIEW IF EXISTS `002_rekap_landmark`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `002_rekap_landmark`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `002_rekap_landmark` AS select min(`landmark_tagging_wilkerstat`.`id`) AS `id`,substr(`landmark_tagging_wilkerstat`.`idsls`,5,3) AS `kdkec`,substr(`landmark_tagging_wilkerstat`.`idsls`,8,3) AS `kddesa`,right(`landmark_tagging_wilkerstat`.`idsls`,4) AS `kdsls`,`landmark_tagging_wilkerstat`.`idsls` AS `idsls`,upper(`landmark_tagging_wilkerstat`.`user_creator_nama`) AS `pemeta`,`landmark_tagging_wilkerstat`.`nm_project` AS `nm_project`,upper(trim(min(`landmark_tagging_wilkerstat`.`deskripsi_project`))) AS `deskripsi_project`,`landmark_tagging_wilkerstat`.`kode_landmark_tipe` AS `kode_landmark_tipe`,`landmark_tagging_wilkerstat`.`tipe_landmark` AS `tipe_landmark`,count(0) AS `Jml` from `landmark_tagging_wilkerstat` group by `landmark_tagging_wilkerstat`.`idsls`,`landmark_tagging_wilkerstat`.`user_creator_nama`,`landmark_tagging_wilkerstat`.`nm_project`,`landmark_tagging_wilkerstat`.`kode_landmark_tipe`,`landmark_tagging_wilkerstat`.`tipe_landmark`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
