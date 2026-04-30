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

-- Dumping structure for view simantordb.005_landmark_2025
DROP VIEW IF EXISTS `005_landmark_2025`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `005_landmark_2025`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `005_landmark_2025` AS select `16_landmark_3215`.`id` AS `id`,`16_landmark_3215`.`wid` AS `wid`,`16_landmark_3215`.`nama` AS `nama`,`16_landmark_3215`.`nm_project` AS `nm_project`,`16_landmark_3215`.`deskripsi_project` AS `deskripsi_project`,`16_landmark_3215`.`iddesa` AS `iddesa`,`16_landmark_3215`.`latitude` AS `latitude`,`16_landmark_3215`.`longitude` AS `longitude`,`16_landmark_3215`.`accuracy` AS `accuracy`,`16_landmark_3215`.`status` AS `status`,`16_landmark_3215`.`kode_kategori` AS `kode_kategori`,`16_landmark_3215`.`kategori_landmark` AS `kategori_landmark`,`16_landmark_3215`.`kode_landmark_tipe` AS `kode_landmark_tipe`,`16_landmark_3215`.`tipe_landmark` AS `tipe_landmark`,`16_landmark_3215`.`user_created_at` AS `user_created_at`,`16_landmark_3215`.`user_upload_at` AS `user_upload_at`,`16_landmark_3215`.`user_creator_nama` AS `user_creator_nama`,`16_landmark_3215`.`photo_url` AS `photo_url`,concat(`16_landmark_3215`.`iddesa`,`16_landmark_3215`.`nm_project`) AS `idsls` from `16_landmark_3215`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
