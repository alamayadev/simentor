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

-- Dumping structure for view simantordb.003_rekap_landmark_2022_sls_2024
DROP VIEW IF EXISTS `003_rekap_landmark_2022_sls_2024`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `003_rekap_landmark_2022_sls_2024`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `003_rekap_landmark_2022_sls_2024` AS select `002_rekap_landmark`.`id` AS `id`,`002_rekap_landmark`.`kdkec` AS `kdkec`,`002_rekap_landmark`.`kddesa` AS `kddesa`,`002_rekap_landmark`.`kdsls` AS `kdsls`,`002_rekap_landmark`.`idsls` AS `idsls`,`002_rekap_landmark`.`pemeta` AS `pemeta`,`002_rekap_landmark`.`nm_project` AS `nm_project`,`002_rekap_landmark`.`deskripsi_project` AS `deskripsi_project`,`002_rekap_landmark`.`kode_landmark_tipe` AS `kode_landmark_tipe`,`002_rekap_landmark`.`tipe_landmark` AS `tipe_landmark`,`002_rekap_landmark`.`Jml` AS `Jml`,`sls_2024_2`.`idsls` AS `idsls_2024`,`sls_2024_2`.`nmsls` AS `nmsls`,`sls_2024_2`.`pcl_id` AS `pcl_id`,`sls_2024_2`.`pml_id` AS `pml_id` from (`002_rekap_landmark` left join `sls_2024_2` on((`002_rekap_landmark`.`idsls` = `sls_2024_2`.`idsls`)));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
