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

-- Dumping structure for view simantordb.006_landmark_2025
DROP VIEW IF EXISTS `006_landmark_2025`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `006_landmark_2025`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `006_landmark_2025` AS select `005_landmark_2025`.`id` AS `id`,`005_landmark_2025`.`wid` AS `wid`,`005_landmark_2025`.`nama` AS `nama`,`005_landmark_2025`.`nm_project` AS `nm_project`,`005_landmark_2025`.`deskripsi_project` AS `deskripsi_project`,`005_landmark_2025`.`latitude` AS `latitude`,`005_landmark_2025`.`longitude` AS `longitude`,`005_landmark_2025`.`accuracy` AS `accuracy`,`005_landmark_2025`.`user_upload_at` AS `user_upload_at`,`005_landmark_2025`.`status` AS `status`,`005_landmark_2025`.`kode_landmark_tipe` AS `kode_landmark_tipe`,`005_landmark_2025`.`tipe_landmark` AS `tipe_landmark`,`005_landmark_2025`.`iddesa` AS `iddesa`,`005_landmark_2025`.`user_creator_nama` AS `user_creator_nama`,`005_landmark_2025`.`photo_url` AS `photo_url`,`005_landmark_2025`.`idsls` AS `idsls`,`sls_2024_2`.`idsls` AS `idsls_2024`,`sls_2024_2`.`nmsls` AS `nmsls`,concat(`sls_2024_2`.`kdprov`,`sls_2024_2`.`kdkab`,`sls_2024_2`.`kdkec`,`sls_2024_2`.`kddesa`) AS `desaid`,`sls_2024_2`.`pcl_id` AS `pcl_id`,`sls_2024_2`.`pml_id` AS `pml_id`,substr(`005_landmark_2025`.`idsls`,8,3) AS `kddesa`,`sls_2024_2`.`nmdesa` AS `nmdesa`,substr(`005_landmark_2025`.`idsls`,1,7) AS `kecid`,substr(`005_landmark_2025`.`idsls`,5,3) AS `kdkec`,`sls_2024_2`.`nmkec` AS `nmkec` from (`005_landmark_2025` left join `sls_2024_2` on((`005_landmark_2025`.`idsls` = `sls_2024_2`.`idsls`)));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
