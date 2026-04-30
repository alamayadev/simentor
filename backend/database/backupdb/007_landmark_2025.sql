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

-- Dumping structure for view simantordb.007_landmark_2025
DROP VIEW IF EXISTS `007_landmark_2025`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `007_landmark_2025`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `007_landmark_2025` AS select `006_landmark_2025`.`idsls` AS `idsls`,`006_landmark_2025`.`kddesa` AS `kddesa`,`006_landmark_2025`.`nmdesa` AS `nmdesa`,`006_landmark_2025`.`kecid` AS `kecid`,`006_landmark_2025`.`kdkec` AS `kdkec`,`006_landmark_2025`.`nmkec` AS `nmkec`,`006_landmark_2025`.`nama` AS `nama`,`006_landmark_2025`.`nm_project` AS `nm_project`,upper(`006_landmark_2025`.`deskripsi_project`) AS `deskripsi_project`,max(`006_landmark_2025`.`status`) AS `status`,`006_landmark_2025`.`kode_landmark_tipe` AS `kode_landmark_tipe`,`006_landmark_2025`.`tipe_landmark` AS `tipe_landmark`,`006_landmark_2025`.`iddesa` AS `iddesa`,upper(`006_landmark_2025`.`user_creator_nama`) AS `user_creator_nama`,`006_landmark_2025`.`idsls_2024` AS `idsls_2024`,`006_landmark_2025`.`nmsls` AS `nmsls`,`006_landmark_2025`.`pcl_id` AS `pcl_id`,`006_landmark_2025`.`pml_id` AS `pml_id`,count(0) AS `jml`,max(`006_landmark_2025`.`user_upload_at`) AS `user_last_upload` from `006_landmark_2025` group by `006_landmark_2025`.`idsls`,`006_landmark_2025`.`kddesa`,`006_landmark_2025`.`nmdesa`,`006_landmark_2025`.`kecid`,`006_landmark_2025`.`kdkec`,`006_landmark_2025`.`nmkec`,`006_landmark_2025`.`nama`,`006_landmark_2025`.`nm_project`,upper(`006_landmark_2025`.`deskripsi_project`),`006_landmark_2025`.`kode_landmark_tipe`,`006_landmark_2025`.`tipe_landmark`,`006_landmark_2025`.`iddesa`,upper(`006_landmark_2025`.`user_creator_nama`),`006_landmark_2025`.`idsls_2024`,`006_landmark_2025`.`nmsls`,`006_landmark_2025`.`pcl_id`,`006_landmark_2025`.`pml_id`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
