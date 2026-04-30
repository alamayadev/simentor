-- --------------------------------------------------------
-- Host:                         192.168.1.9
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

-- Dumping structure for table simantordb.bast_detils
DROP TABLE IF EXISTS `bast_detils`;
CREATE TABLE IF NOT EXISTS `bast_detils` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `penugasan_id` int NOT NULL,
  `item` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `satuan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `volume` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for view simantordb.cek_landmark
DROP VIEW IF EXISTS `cek_landmark`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `cek_landmark` (
	`kdkec` VARCHAR(3) NULL COLLATE 'utf8mb4_general_ci',
	`kddesa` VARCHAR(3) NULL COLLATE 'utf8mb4_general_ci',
	`idsls_2024` VARCHAR(20) NULL COLLATE 'utf8mb4_general_ci',
	`nmsls` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`deskripsi_project` VARCHAR(70) NULL COLLATE 'utf8mb4_general_ci',
	`jml_batas_sls` DECIMAL(42,0) NULL,
	`jml_titik_sls` DECIMAL(42,0) NULL,
	`jml_landmark` DECIMAL(42,0) NULL
) ENGINE=MyISAM;

-- Dumping structure for table simantordb.data_form_permintaan
DROP TABLE IF EXISTS `data_form_permintaan`;
CREATE TABLE IF NOT EXISTS `data_form_permintaan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `thn` text COLLATE utf8mb4_unicode_ci,
  `bulan` text COLLATE utf8mb4_unicode_ci,
  `nomor` text COLLATE utf8mb4_unicode_ci,
  `no_sisip` text COLLATE utf8mb4_unicode_ci,
  `tanggal` date DEFAULT NULL,
  `tanggal_indo` text COLLATE utf8mb4_unicode_ci,
  `kode_klas` text COLLATE utf8mb4_unicode_ci,
  `no_surat` text COLLATE utf8mb4_unicode_ci,
  `dari` text COLLATE utf8mb4_unicode_ci,
  `perihal` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2095 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.data_surat_keluar
DROP TABLE IF EXISTS `data_surat_keluar`;
CREATE TABLE IF NOT EXISTS `data_surat_keluar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bln` text COLLATE utf8mb4_unicode_ci,
  `thn` text COLLATE utf8mb4_unicode_ci,
  `nomor` text COLLATE utf8mb4_unicode_ci,
  `no_sisip` text COLLATE utf8mb4_unicode_ci,
  `tanggal` date DEFAULT NULL,
  `tanggal_indo` text COLLATE utf8mb4_unicode_ci,
  `no_surat` text COLLATE utf8mb4_unicode_ci,
  `dari` text COLLATE utf8mb4_unicode_ci,
  `tujuan` text COLLATE utf8mb4_unicode_ci,
  `perihal` text COLLATE utf8mb4_unicode_ci,
  `isi_surat` text COLLATE utf8mb4_unicode_ci,
  `lampiran` int DEFAULT NULL,
  `file` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2011 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.data_surat_sk_bast
DROP TABLE IF EXISTS `data_surat_sk_bast`;
CREATE TABLE IF NOT EXISTS `data_surat_sk_bast` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bln` text COLLATE utf8mb4_unicode_ci,
  `thn` text COLLATE utf8mb4_unicode_ci,
  `nomor` text COLLATE utf8mb4_unicode_ci,
  `no_sisip` text COLLATE utf8mb4_unicode_ci,
  `tanggal` date DEFAULT NULL,
  `no_surat` text COLLATE utf8mb4_unicode_ci,
  `oleh` text COLLATE utf8mb4_unicode_ci,
  `kegiatan` text COLLATE utf8mb4_unicode_ci,
  `kepada` text COLLATE utf8mb4_unicode_ci,
  `perihal` text COLLATE utf8mb4_unicode_ci,
  `type` text COLLATE utf8mb4_unicode_ci,
  `kol_lampiran` text COLLATE utf8mb4_unicode_ci,
  `create_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=722 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.data_surtug
DROP TABLE IF EXISTS `data_surtug`;
CREATE TABLE IF NOT EXISTS `data_surtug` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bln` text COLLATE utf8mb4_unicode_ci,
  `tahun` text COLLATE utf8mb4_unicode_ci,
  `no_mix` text COLLATE utf8mb4_unicode_ci,
  `nomor` text COLLATE utf8mb4_unicode_ci,
  `no_sisip` text COLLATE utf8mb4_unicode_ci,
  `tanggal` date DEFAULT NULL,
  `tanggal_indo` text COLLATE utf8mb4_unicode_ci,
  `kode_klas` text COLLATE utf8mb4_unicode_ci,
  `no_surat` text COLLATE utf8mb4_unicode_ci,
  `kepada` text COLLATE utf8mb4_unicode_ci,
  `menimbang` text COLLATE utf8mb4_unicode_ci,
  `uraian` text COLLATE utf8mb4_unicode_ci,
  `file` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4740 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.data_surtug_detil
DROP TABLE IF EXISTS `data_surtug_detil`;
CREATE TABLE IF NOT EXISTS `data_surtug_detil` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surtug_id` int NOT NULL,
  `pegawai_id` int DEFAULT NULL,
  `mitra_id` int DEFAULT NULL,
  `penugasan_id` int DEFAULT NULL,
  `dasar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_kegiatan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tugas_sebagai` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hari` int NOT NULL,
  `wilayah_kerja` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_mulai` date NOT NULL,
  `jenis_kendaraan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_dipa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isOrganik` tinyint(1) NOT NULL,
  `sppd` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.kegiatan
DROP TABLE IF EXISTS `kegiatan`;
CREATE TABLE IF NOT EXISTS `kegiatan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tahun` text COLLATE utf8mb4_unicode_ci,
  `fungsi` text COLLATE utf8mb4_unicode_ci,
  `kode_kelompok_kegiatan` text COLLATE utf8mb4_unicode_ci,
  `kode_kegiatan` text COLLATE utf8mb4_unicode_ci,
  `nama` text COLLATE utf8mb4_unicode_ci,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `jenis_kegiatan` text COLLATE utf8mb4_unicode_ci,
  `jml_ptgs` int DEFAULT NULL,
  `volume` int DEFAULT NULL,
  `satuan` text COLLATE utf8mb4_unicode_ci,
  `rate_pcl` int DEFAULT NULL,
  `rate_pml` int DEFAULT NULL,
  `rate_entri` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.klasifikasi_surat
DROP TABLE IF EXISTS `klasifikasi_surat`;
CREATE TABLE IF NOT EXISTS `klasifikasi_surat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint DEFAULT NULL,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for view simantordb.landmark_2022_sls_2024
DROP VIEW IF EXISTS `landmark_2022_sls_2024`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `landmark_2022_sls_2024` (
	`id` BIGINT(20) UNSIGNED NOT NULL,
	`wid` VARCHAR(10) NOT NULL COLLATE 'utf8mb4_general_ci',
	`nama` VARCHAR(60) NOT NULL COLLATE 'utf8mb4_general_ci',
	`nm_project` VARCHAR(10) NOT NULL COLLATE 'utf8mb4_general_ci',
	`deskripsi` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`deskripsi_project` VARCHAR(70) NOT NULL COLLATE 'utf8mb4_general_ci',
	`latitude` VARCHAR(20) NOT NULL COLLATE 'utf8mb4_general_ci',
	`longitude` DECIMAL(20,6) NOT NULL,
	`accuracy` DECIMAL(20,6) NOT NULL,
	`alamat` VARCHAR(110) NULL COLLATE 'utf8mb4_general_ci',
	`user_created_at` DATETIME NULL,
	`kode_landmark_tipe` VARCHAR(5) NOT NULL COLLATE 'utf8mb4_general_ci',
	`tipe_landmark` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_general_ci',
	`iddesa` VARCHAR(10) NOT NULL COLLATE 'utf8mb4_general_ci',
	`user_creator_nama` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
	`photo_url` VARCHAR(70) NOT NULL COLLATE 'utf8mb4_general_ci',
	`idsls` VARCHAR(15) NOT NULL COLLATE 'utf8mb4_general_ci',
	`idsls_2024` VARCHAR(20) NULL COLLATE 'utf8mb4_general_ci',
	`nmsls` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for table simantordb.landmark_tagging_wilkerstat
DROP TABLE IF EXISTS `landmark_tagging_wilkerstat`;
CREATE TABLE IF NOT EXISTS `landmark_tagging_wilkerstat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `wid` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `nm_project` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi_project` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `latitude` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `longitude` decimal(20,6) NOT NULL,
  `accuracy` decimal(20,6) NOT NULL,
  `alamat` varchar(110) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_created_at` datetime DEFAULT NULL,
  `kode_landmark_tipe` varchar(5) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `tipe_landmark` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `iddesa` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `user_creator_nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `photo_url` varchar(70) COLLATE utf8mb4_general_ci NOT NULL,
  `idsls` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11399 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.links
DROP TABLE IF EXISTS `links`;
CREATE TABLE IF NOT EXISTS `links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.mitra_kepka
DROP TABLE IF EXISTS `mitra_kepka`;
CREATE TABLE IF NOT EXISTS `mitra_kepka` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` text COLLATE utf8mb4_unicode_ci,
  `sobat_id` text COLLATE utf8mb4_unicode_ci,
  `posisi` text COLLATE utf8mb4_unicode_ci,
  `status_seleksi` text COLLATE utf8mb4_unicode_ci,
  `posisi_daftar` text COLLATE utf8mb4_unicode_ci,
  `nama_lengkap` text COLLATE utf8mb4_unicode_ci,
  `alamat_detail` text COLLATE utf8mb4_unicode_ci,
  `alamat_prov` text COLLATE utf8mb4_unicode_ci,
  `alamat_kab` text COLLATE utf8mb4_unicode_ci,
  `alamat_kec` text COLLATE utf8mb4_unicode_ci,
  `alamat_desa` text COLLATE utf8mb4_unicode_ci,
  `tgl_lahir` date DEFAULT NULL,
  `jenis_kelamin` text COLLATE utf8mb4_unicode_ci,
  `agama` text COLLATE utf8mb4_unicode_ci,
  `status_kawin` text COLLATE utf8mb4_unicode_ci,
  `pendidikan` text COLLATE utf8mb4_unicode_ci,
  `pekerjaan` text COLLATE utf8mb4_unicode_ci,
  `deskripsi_pekerjaan_lain` text COLLATE utf8mb4_unicode_ci,
  `no_telp` text COLLATE utf8mb4_unicode_ci,
  `npwp` text COLLATE utf8mb4_unicode_ci,
  `kepemilikan_motor` text COLLATE utf8mb4_unicode_ci,
  `kemampuan_berkendara_motor` text COLLATE utf8mb4_unicode_ci,
  `pernah_capi` text COLLATE utf8mb4_unicode_ci,
  `kepemilikan_hp_android` text COLLATE utf8mb4_unicode_ci,
  `merk_hp` text COLLATE utf8mb4_unicode_ci,
  `tipe_hp` text COLLATE utf8mb4_unicode_ci,
  `ram_hp` int DEFAULT NULL,
  `kepemilikan_laptop` text COLLATE utf8mb4_unicode_ci,
  `kemampuan_komputer` text COLLATE utf8mb4_unicode_ci,
  `mitra_eksternal` text COLLATE utf8mb4_unicode_ci,
  `nama_k_l_lain` text COLLATE utf8mb4_unicode_ci,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `nilai_ujian` decimal(8,2) DEFAULT NULL,
  `waktu_mulai` text COLLATE utf8mb4_unicode_ci,
  `waktu_submit` text COLLATE utf8mb4_unicode_ci,
  `durasi_menit` text COLLATE utf8mb4_unicode_ci,
  `remedial` int DEFAULT NULL,
  `kabid` text COLLATE utf8mb4_unicode_ci,
  `kab` text COLLATE utf8mb4_unicode_ci,
  `kecid` text COLLATE utf8mb4_unicode_ci,
  `keca` text COLLATE utf8mb4_unicode_ci,
  `desaid` text COLLATE utf8mb4_unicode_ci,
  `desa` text COLLATE utf8mb4_unicode_ci,
  `nik` text COLLATE utf8mb4_unicode_ci,
  `foto` text COLLATE utf8mb4_unicode_ci,
  `foto_ktp` text COLLATE utf8mb4_unicode_ci,
  `ijazah` text COLLATE utf8mb4_unicode_ci,
  `cek_kepka` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table simantordb.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.pengaduan
DROP TABLE IF EXISTS `pengaduan`;
CREATE TABLE IF NOT EXISTS `pengaduan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis_pelangaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lainnya` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pelaku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_kejadian` date NOT NULL,
  `kronologi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `bukti` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.penugasan
DROP TABLE IF EXISTS `penugasan`;
CREATE TABLE IF NOT EXISTS `penugasan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kegiatan_id` int NOT NULL,
  `jabatan_tugas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pegawai_id` int DEFAULT NULL,
  `mitra_id` int DEFAULT NULL,
  `volume` int NOT NULL,
  `nilai` int NOT NULL,
  `bln_bayar` date DEFAULT NULL,
  `created_by` int NOT NULL,
  `no_bast` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_bast` date DEFAULT NULL,
  `no_sk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_sk` date DEFAULT NULL,
  `jangka_waktu_mulai` date DEFAULT NULL,
  `jangka_waktu_selesai` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.permissions
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.personal_access_tokens
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.pok
DROP TABLE IF EXISTS `pok`;
CREATE TABLE IF NOT EXISTS `pok` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `komponen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `v` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.profil_pegawai
DROP TABLE IF EXISTS `profil_pegawai`;
CREATE TABLE IF NOT EXISTS `profil_pegawai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pangkat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for view simantordb.rekap_landmark
DROP VIEW IF EXISTS `rekap_landmark`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `rekap_landmark` (
	`id` BIGINT(20) UNSIGNED NULL,
	`kdkec` VARCHAR(3) NULL COLLATE 'utf8mb4_general_ci',
	`kddesa` VARCHAR(3) NULL COLLATE 'utf8mb4_general_ci',
	`kdsls` VARCHAR(4) NULL COLLATE 'utf8mb4_general_ci',
	`idsls` VARCHAR(15) NOT NULL COLLATE 'utf8mb4_general_ci',
	`deskripsi_project` VARCHAR(70) NULL COLLATE 'utf8mb4_general_ci',
	`kode_landmark_tipe` VARCHAR(5) NOT NULL COLLATE 'utf8mb4_general_ci',
	`tipe_landmark` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_general_ci',
	`Jml` BIGINT(19) NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for view simantordb.rekap_landmark_2022_sls_2024
DROP VIEW IF EXISTS `rekap_landmark_2022_sls_2024`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `rekap_landmark_2022_sls_2024` (
	`id` BIGINT(20) UNSIGNED NULL,
	`kdkec` VARCHAR(3) NOT NULL COLLATE 'utf8mb4_general_ci',
	`kddesa` VARCHAR(3) NOT NULL COLLATE 'utf8mb4_general_ci',
	`kdsls` VARCHAR(4) NOT NULL COLLATE 'utf8mb4_general_ci',
	`idsls` VARCHAR(15) NOT NULL COLLATE 'utf8mb4_general_ci',
	`deskripsi_project` VARCHAR(70) NULL COLLATE 'utf8mb4_general_ci',
	`kode_landmark_tipe` VARCHAR(5) NOT NULL COLLATE 'utf8mb4_general_ci',
	`tipe_landmark` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_general_ci',
	`Jml` BIGINT(19) NOT NULL,
	`idsls_2024` VARCHAR(20) NULL COLLATE 'utf8mb4_general_ci',
	`nmsls` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tahun` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grup` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.skps
DROP TABLE IF EXISTS `skps`;
CREATE TABLE IF NOT EXISTS `skps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `jenis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bulan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `konten` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.sk_detils
DROP TABLE IF EXISTS `sk_detils`;
CREATE TABLE IF NOT EXISTS `sk_detils` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sk_id` int NOT NULL,
  `pegawai_id` int DEFAULT NULL,
  `mitra_id` int DEFAULT NULL,
  `penugasan_id` int DEFAULT NULL,
  `detil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isOrganik` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.sls_2024_2
DROP TABLE IF EXISTS `sls_2024_2`;
CREATE TABLE IF NOT EXISTS `sls_2024_2` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kdprov` varchar(2) COLLATE utf8mb4_general_ci NOT NULL,
  `kdkab` varchar(2) COLLATE utf8mb4_general_ci NOT NULL,
  `kdkec` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `kddesa` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `kdsls` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `idsls` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nmprov` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nmkab` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nmkec` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nmdesa` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `nmsls` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `periode` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `pcl_id` smallint DEFAULT NULL,
  `pml_id` smallint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7747 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.sls_kec
DROP TABLE IF EXISTS `sls_kec`;
CREATE TABLE IF NOT EXISTS `sls_kec` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kdkec` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nmkec` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `jml_sls` smallint NOT NULL,
  `pemeta` smallint DEFAULT NULL,
  `pengawas` smallint DEFAULT NULL,
  `pengawas_organik` smallint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.social_provider_user
DROP TABLE IF EXISTS `social_provider_user`;
CREATE TABLE IF NOT EXISTS `social_provider_user` (
  `user_id` bigint unsigned NOT NULL,
  `provider_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_data` text COLLATE utf8mb4_unicode_ci,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`provider_slug`),
  CONSTRAINT `social_provider_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.tamu
DROP TABLE IF EXISTS `tamu`;
CREATE TABLE IF NOT EXISTS `tamu` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asal_instansi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_kunjungan` date NOT NULL,
  `tujuan_kunjungan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_layanan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detil_layanan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.uu
DROP TABLE IF EXISTS `uu`;
CREATE TABLE IF NOT EXISTS `uu` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detil` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.uu_tambahan
DROP TABLE IF EXISTS `uu_tambahan`;
CREATE TABLE IF NOT EXISTS `uu_tambahan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis_surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table simantordb.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.role_has_permissions
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping structure for table simantordb.model_has_permissions
DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table simantordb.model_has_roles
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
