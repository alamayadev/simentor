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

-- Dumping structure for view simantordb.001_landmark_2022_sls_2024
DROP VIEW IF EXISTS `001_landmark_2022_sls_2024`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `001_landmark_2022_sls_2024`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `001_landmark_2022_sls_2024` AS select `landmark_tagging_wilkerstat`.`id` AS `id`,`landmark_tagging_wilkerstat`.`wid` AS `wid`,`landmark_tagging_wilkerstat`.`nama` AS `nama`,`landmark_tagging_wilkerstat`.`nm_project` AS `nm_project`,`landmark_tagging_wilkerstat`.`deskripsi` AS `deskripsi`,`landmark_tagging_wilkerstat`.`deskripsi_project` AS `deskripsi_project`,`landmark_tagging_wilkerstat`.`latitude` AS `latitude`,`landmark_tagging_wilkerstat`.`longitude` AS `longitude`,`landmark_tagging_wilkerstat`.`accuracy` AS `accuracy`,`landmark_tagging_wilkerstat`.`alamat` AS `alamat`,`landmark_tagging_wilkerstat`.`user_created_at` AS `user_created_at`,`landmark_tagging_wilkerstat`.`kode_landmark_tipe` AS `kode_landmark_tipe`,`landmark_tagging_wilkerstat`.`tipe_landmark` AS `tipe_landmark`,`landmark_tagging_wilkerstat`.`iddesa` AS `iddesa`,`landmark_tagging_wilkerstat`.`user_creator_nama` AS `user_creator_nama`,`landmark_tagging_wilkerstat`.`photo_url` AS `photo_url`,`landmark_tagging_wilkerstat`.`idsls` AS `idsls`,`sls_2024_2`.`idsls` AS `idsls_2024`,`sls_2024_2`.`nmsls` AS `nmsls`,concat(`sls_2024_2`.`kdprov`,`sls_2024_2`.`kdkab`,`sls_2024_2`.`kdkec`,`sls_2024_2`.`kddesa`) AS `desaid`,`sls_2024_2`.`pcl_id` AS `pcl_id`,`sls_2024_2`.`pml_id` AS `pml_id`,`sls_2024_2`.`kddesa` AS `kddesa`,`sls_2024_2`.`nmdesa` AS `nmdesa`,concat(`sls_2024_2`.`kdprov`,`sls_2024_2`.`kdkab`,`sls_2024_2`.`kdkec`) AS `kecid`,`sls_2024_2`.`kdkec` AS `kdkec`,`sls_2024_2`.`nmkec` AS `nmkec` from (`landmark_tagging_wilkerstat` left join `sls_2024_2` on((`landmark_tagging_wilkerstat`.`idsls` = `sls_2024_2`.`idsls`)));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
