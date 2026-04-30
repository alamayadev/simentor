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

-- Dumping structure for view simantordb.004_cek_landmark
DROP VIEW IF EXISTS `004_cek_landmark`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `004_cek_landmark`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `004_cek_landmark` AS select `003_rekap_landmark_2022_sls_2024`.`kdkec` AS `kdkec`,`003_rekap_landmark_2022_sls_2024`.`kddesa` AS `kddesa`,`003_rekap_landmark_2022_sls_2024`.`idsls_2024` AS `idsls_2024`,`003_rekap_landmark_2022_sls_2024`.`pcl_id` AS `pcl_id`,`003_rekap_landmark_2022_sls_2024`.`pml_id` AS `pml_id`,`003_rekap_landmark_2022_sls_2024`.`pemeta` AS `pemeta`,`003_rekap_landmark_2022_sls_2024`.`nm_project` AS `nm_project`,`003_rekap_landmark_2022_sls_2024`.`nmsls` AS `nmsls`,`003_rekap_landmark_2022_sls_2024`.`deskripsi_project` AS `deskripsi_project`,sum(if((`003_rekap_landmark_2022_sls_2024`.`kode_landmark_tipe` = '15040'),`003_rekap_landmark_2022_sls_2024`.`Jml`,0)) AS `jml_batas_sls`,sum(if((`003_rekap_landmark_2022_sls_2024`.`kode_landmark_tipe` = '15060'),`003_rekap_landmark_2022_sls_2024`.`Jml`,0)) AS `jml_titik_sls`,sum(if((`003_rekap_landmark_2022_sls_2024`.`kode_landmark_tipe` like '16%'),`003_rekap_landmark_2022_sls_2024`.`Jml`,0)) AS `jml_landmark` from `003_rekap_landmark_2022_sls_2024` group by `003_rekap_landmark_2022_sls_2024`.`kdkec`,`003_rekap_landmark_2022_sls_2024`.`kddesa`,`003_rekap_landmark_2022_sls_2024`.`idsls_2024`,`003_rekap_landmark_2022_sls_2024`.`pcl_id`,`003_rekap_landmark_2022_sls_2024`.`pml_id`,`003_rekap_landmark_2022_sls_2024`.`pemeta`,`003_rekap_landmark_2022_sls_2024`.`nm_project`,`003_rekap_landmark_2022_sls_2024`.`nmsls`,`003_rekap_landmark_2022_sls_2024`.`deskripsi_project`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
