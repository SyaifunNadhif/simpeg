/*
 Navicat Premium Dump SQL

 Source Server         : Localhost
 Source Server Type    : MySQL
 Source Server Version : 100138 (10.1.38-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : masq2971_simpeg

 Target Server Type    : MySQL
 Target Server Version : 100138 (10.1.38-MariaDB)
 File Encoding         : 65001

 Date: 06/09/2025 13:54:37
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for oldtb_jabatan
-- ----------------------------
DROP TABLE IF EXISTS `oldtb_jabatan`;
CREATE TABLE `oldtb_jabatan`  (
  `id_jab` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jabatan` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `unit_kerja` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `eselon` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tmt_jabatan` date NULL DEFAULT NULL,
  `sampai_tgl` date NULL DEFAULT NULL,
  `status_jab` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jk_jab` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `no_sk` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NOT NULL,
  PRIMARY KEY (`id_jab`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for q_pegawai
-- ----------------------------
DROP TABLE IF EXISTS `q_pegawai`;
CREATE TABLE `q_pegawai`  (
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jabatan` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `group_jab` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tmt_kerja` date NULL DEFAULT NULL,
  `lama_kerja` int NULL DEFAULT NULL,
  `tmt_jab` date NULL DEFAULT NULL,
  `lama_menjabat` int NULL DEFAULT NULL,
  `pendidikan` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `sertifikasi` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for talenta
-- ----------------------------
DROP TABLE IF EXISTS `talenta`;
CREATE TABLE `talenta`  (
  `Employee_ID` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Full_Name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Barcode` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Organization` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Job_Position` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Job_Level` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Join_Date` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Resign_Date` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Status_Employee` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `End_Date` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Sign_Date` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Birth_Date` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Age` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Birth_Place` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Citizen_ID_Address` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Residential_Address` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `NPWP` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `PTKP_Status` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Employee_Tax_Status` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Tax_Config` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Bank_Name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Bank_Account` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Bank_Account_Holder` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `BPJS_Ketenagakerjaan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `BPJS_Kesehatan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `NIK_(NPWP_16_Digit)` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Mobile_Phone` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Phone` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Branch_Name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Parent_Branch_Name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Religion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Gender` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Marital_Status` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Blood_Type` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Nationality_Code` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Currency` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Length_Of_Service` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Payment_Schedule` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Approval_Line` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Manager` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Grade` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Class` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Profile_Picture` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Cost_Center` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Cost_Center_Category` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `SBU` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `NPWP_16_digit_(new)` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Passport` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Passport_Expiration_Date` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Keluarga` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Jumlah_Anak` int NULL DEFAULT NULL,
  `Nilai_Asset_Cabang` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Masa_Kerja_Golongan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_anak
-- ----------------------------
DROP TABLE IF EXISTS `tb_anak`;
CREATE TABLE `tb_anak`  (
  `id_anak` int NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_peg_old` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nik` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tmp_lhr` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tgl_lhr` date NULL DEFAULT NULL,
  `pendidikan` varchar(26) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_pekerjaan` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `pekerjaan` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status_hub` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `anak_ke` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `bpjs_anak` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NOT NULL,
  PRIMARY KEY (`id_anak`) USING BTREE,
  INDEX `fk_tb_anak_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_anak_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2201 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_angkat
-- ----------------------------
DROP TABLE IF EXISTS `tb_angkat`;
CREATE TABLE `tb_angkat`  (
  `id_angkat` int NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jns_mutasi` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg_baru` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_mutasi` date NOT NULL,
  `no_mutasi` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tmt` date NOT NULL,
  `sk_mutasi` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id_angkat`, `id_peg_baru`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_bahasa
-- ----------------------------
DROP TABLE IF EXISTS `tb_bahasa`;
CREATE TABLE `tb_bahasa`  (
  `id_bhs` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jns_bhs` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `bahasa` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kemampuan` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_bhs`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_config
-- ----------------------------
DROP TABLE IF EXISTS `tb_config`;
CREATE TABLE `tb_config`  (
  `id_app` varchar(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama_app` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `desc_app` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `alias_app` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `url_app` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `anchor_app` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_app`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_cuti
-- ----------------------------
DROP TABLE IF EXISTS `tb_cuti`;
CREATE TABLE `tb_cuti`  (
  `id_cuti` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jns_cuti` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `no_suratcuti` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_suratcuti` date NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `ket` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_cuti`) USING BTREE,
  INDEX `fk_tb_cuti_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_cuti_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_diklat
-- ----------------------------
DROP TABLE IF EXISTS `tb_diklat`;
CREATE TABLE `tb_diklat`  (
  `id_diklat` int NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `diklat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `penyelenggara` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tempat` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `angkatan` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tahun` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_diklat`) USING BTREE,
  INDEX `fk_tb_diklat_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_diklat_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 5484 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_dp3
-- ----------------------------
DROP TABLE IF EXISTS `tb_dp3`;
CREATE TABLE `tb_dp3`  (
  `id_dp3` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `periode_awal` date NOT NULL,
  `periode_akhir` date NOT NULL,
  `pejabat_penilai` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `atasan_pejabat_penilai` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nilai_kesetiaan` int NOT NULL,
  `nilai_prestasi` int NOT NULL,
  `nilai_tgjwb` int NOT NULL,
  `nilai_ketaatan` int NOT NULL,
  `nilai_kejujuran` int NOT NULL,
  `nilai_kerjasama` int NOT NULL,
  `nilai_prakarsa` int NOT NULL,
  `nilai_kepemimpinan` int NOT NULL,
  `hasil_penilaian` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_dp3`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_edit_pending
-- ----------------------------
DROP TABLE IF EXISTS `tb_edit_pending`;
CREATE TABLE `tb_edit_pending`  (
  `id_edit` int NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jenis_data` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `data_lama` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `data_baru` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `id_user` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status_otorisasi` enum('pending','approved','rejected') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'pending',
  `tanggal_pengajuan` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_otorisasi` datetime NULL DEFAULT NULL,
  `otorisator` int NULL DEFAULT NULL,
  `komentar_otorisasi` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  PRIMARY KEY (`id_edit`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_formasi
-- ----------------------------
DROP TABLE IF EXISTS `tb_formasi`;
CREATE TABLE `tb_formasi`  (
  `id_formasi` int NOT NULL AUTO_INCREMENT,
  `kode_cabang` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jabatan` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `kuota` int NULL DEFAULT NULL,
  PRIMARY KEY (`id_formasi`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_hukuman
-- ----------------------------
DROP TABLE IF EXISTS `tb_hukuman`;
CREATE TABLE `tb_hukuman`  (
  `id_hukum` tinyint NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_peg_old` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `hukuman` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `pejabat_sk` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jabatan_sk` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `no_sk` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tgl_sk` date NULL DEFAULT NULL,
  `pejabat_pulih` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jabatan_pulih` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `no_pulih` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tgl_pulih` date NULL DEFAULT NULL,
  `gol` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `pangkat` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `eselon` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `dokumen` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `keterangan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NULL DEFAULT NULL,
  `date_update` date NULL DEFAULT NULL,
  PRIMARY KEY (`id_hukum`) USING BTREE,
  INDEX `fk_tb_hukuman_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_hukuman_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 80 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_jabatan
-- ----------------------------
DROP TABLE IF EXISTS `tb_jabatan`;
CREATE TABLE `tb_jabatan`  (
  `id_jab` int NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_peg_old` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `kode_jabatan` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jabatan` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `unit_kerja` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tmt_jabatan` date NULL DEFAULT NULL,
  `sampai_tgl` date NULL DEFAULT NULL,
  `status_jab` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `no_sk` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_sk` date NULL DEFAULT NULL,
  `date_reg` date NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_jab`) USING BTREE,
  INDEX `fk_tb_jabatan_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_jabatan_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1748 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_kantor
-- ----------------------------
DROP TABLE IF EXISTS `tb_kantor`;
CREATE TABLE `tb_kantor`  (
  `kode_kantor_detail` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kode_cabang` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama_kantor` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `alamat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `level` varchar(2) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jarak_kc` char(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `waktu_kc` char(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status_kantor` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `bentuk_dokumen` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `no_dokumen` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tgl_mulai` date NULL DEFAULT NULL,
  `tgl_selesai` date NULL DEFAULT NULL,
  `keterangan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NULL DEFAULT NULL,
  `date_update` date NULL DEFAULT NULL,
  PRIMARY KEY (`kode_kantor_detail`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_lat_jabatan
-- ----------------------------
DROP TABLE IF EXISTS `tb_lat_jabatan`;
CREATE TABLE `tb_lat_jabatan`  (
  `id_lat_jabatan` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama_pelatih` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tahun_lat` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jml_jam` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_lat_jabatan`) USING BTREE,
  INDEX `fk_tb_lat_jabatan_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_lat_jabatan_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_log_aktivitas
-- ----------------------------
DROP TABLE IF EXISTS `tb_log_aktivitas`;
CREATE TABLE `tb_log_aktivitas`  (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_user` int NULL DEFAULT NULL,
  `aksi` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `keterangan` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `waktu_log` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  PRIMARY KEY (`id_log`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_master_jabatan
-- ----------------------------
DROP TABLE IF EXISTS `tb_master_jabatan`;
CREATE TABLE `tb_master_jabatan`  (
  `id_jabatan` int NOT NULL AUTO_INCREMENT,
  `desc_jabatan` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `level` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `eselon` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_jabatan`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_master_pekerjaan
-- ----------------------------
DROP TABLE IF EXISTS `tb_master_pekerjaan`;
CREATE TABLE `tb_master_pekerjaan`  (
  `id_pekerjaan` int NOT NULL AUTO_INCREMENT,
  `desc_pekerjaan` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_pekerjaan`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_masteresl
-- ----------------------------
DROP TABLE IF EXISTS `tb_masteresl`;
CREATE TABLE `tb_masteresl`  (
  `id_masteresl` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama_masteresl` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_masteresl`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_mastergol
-- ----------------------------
DROP TABLE IF EXISTS `tb_mastergol`;
CREATE TABLE `tb_mastergol`  (
  `id_mastergol` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama_mastergol` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_mastergol`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_mutasi
-- ----------------------------
DROP TABLE IF EXISTS `tb_mutasi`;
CREATE TABLE `tb_mutasi`  (
  `id_mutasi` int NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_peg_old` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jns_mutasi` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_mutasi` date NOT NULL,
  `no_mutasi` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tmt` date NOT NULL,
  `sk_mutasi` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jabatan` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `pangkat` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `eselon` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_mutasi`) USING BTREE,
  INDEX `fk_tb_mutasi_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_mutasi_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 355 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_notifikasi
-- ----------------------------
DROP TABLE IF EXISTS `tb_notifikasi`;
CREATE TABLE `tb_notifikasi`  (
  `id_notif` int NOT NULL AUTO_INCREMENT,
  `id_user` varbinary(12) NOT NULL,
  `judul` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `pesan` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `link_aksi` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `status_baca` enum('unread','read') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'unread',
  `waktu_notif` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notif`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_ortu
-- ----------------------------
DROP TABLE IF EXISTS `tb_ortu`;
CREATE TABLE `tb_ortu`  (
  `id_ortu` int NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_peg_old` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nik` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tmp_lhr` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tgl_lhr` date NULL DEFAULT NULL,
  `pendidikan` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_pekerjaan` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `pekerjaan` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status_hub` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NULL DEFAULT NULL,
  PRIMARY KEY (`id_ortu`) USING BTREE,
  INDEX `fk_tb_ortu_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_ortu_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2955 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_pangkat
-- ----------------------------
DROP TABLE IF EXISTS `tb_pangkat`;
CREATE TABLE `tb_pangkat`  (
  `id_pangkat` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `pangkat` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `gol` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jns_pangkat` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `pejabat_sk` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `no_sk` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_sk` date NOT NULL,
  `tmt_pangkat` date NOT NULL,
  `status_pan` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jk_pan` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_pangkat`) USING BTREE,
  INDEX `fk_tb_pangkat_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_pangkat_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_pegawai
-- ----------------------------
DROP TABLE IF EXISTS `tb_pegawai`;
CREATE TABLE `tb_pegawai`  (
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg_old` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nip` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tempat_lhr` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_lhr` date NOT NULL,
  `agama` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jk` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `gol_darah` varchar(2) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status_nikah` varchar(18) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status_kepeg` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `alamat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `telp` varchar(13) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tmt_kerja` date NULL DEFAULT NULL,
  `tgl_pensiun` date NOT NULL,
  `bpjstk` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `bpjskes` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status_aktif` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date_reg` date NOT NULL,
  `date_modify` date NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_peg`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_pegawai_2
-- ----------------------------
DROP TABLE IF EXISTS `tb_pegawai_2`;
CREATE TABLE `tb_pegawai_2`  (
  `id_peg` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nip` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tempat_lhr` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_lhr` date NOT NULL,
  `agama` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jk` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `gol_darah` varchar(2) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status_nikah` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status_kepeg` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_mulaikerja` date NOT NULL,
  `tgl_naikpangkat` date NOT NULL,
  `tgl_naikgaji` date NOT NULL,
  `alamat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `telp` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_pensiun` date NOT NULL,
  `date_reg` date NOT NULL,
  `urut_pangkat` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_peg`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_pendidikan
-- ----------------------------
DROP TABLE IF EXISTS `tb_pendidikan`;
CREATE TABLE `tb_pendidikan`  (
  `id_sekolah` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_peg_old` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jenjang` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama_sekolah` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `lokasi` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `jurusan` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `no_ijazah` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tgl_ijazah` date NULL DEFAULT NULL,
  `kepala` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `th_masuk` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `th_lulus` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_sekolah`) USING BTREE,
  INDEX `fk_tb_sekolah_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_sekolah_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_penghargaan
-- ----------------------------
DROP TABLE IF EXISTS `tb_penghargaan`;
CREATE TABLE `tb_penghargaan`  (
  `id_penghargaan` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `penghargaan` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tahun` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `pemberi` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_penghargaan`) USING BTREE,
  INDEX `fk_tb_penghargaan_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_penghargaan_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_penugasan
-- ----------------------------
DROP TABLE IF EXISTS `tb_penugasan`;
CREATE TABLE `tb_penugasan`  (
  `id_penugasan` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tujuan` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tahun` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `lama` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `alasan` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_penugasan`) USING BTREE,
  INDEX `fk_tb_penugasan_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_penugasan_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_recycle_bin_pegawai
-- ----------------------------
DROP TABLE IF EXISTS `tb_recycle_bin_pegawai`;
CREATE TABLE `tb_recycle_bin_pegawai`  (
  `id_peg` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg_old` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nip` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tempat_lhr` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_lhr` date NOT NULL,
  `agama` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jk` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `gol_darah` varchar(2) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status_nikah` varchar(18) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status_kepeg` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `alamat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `telp` varchar(13) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tmt_kerja` date NULL DEFAULT NULL,
  `tgl_pensiun` date NOT NULL,
  `bpjstk` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `bpjskes` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status_aktif` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date_reg` datetime NOT NULL,
  PRIMARY KEY (`id_peg`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_ref_jabatan
-- ----------------------------
DROP TABLE IF EXISTS `tb_ref_jabatan`;
CREATE TABLE `tb_ref_jabatan`  (
  `kode_jabatan` int NOT NULL AUTO_INCREMENT,
  `jabatan` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `level` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `group` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `kuota` int NULL DEFAULT NULL,
  `status` int NULL DEFAULT NULL,
  `lingkup` enum('KP','KC','KANWIL') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'KP',
  PRIMARY KEY (`kode_jabatan`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 82 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_seminar
-- ----------------------------
DROP TABLE IF EXISTS `tb_seminar`;
CREATE TABLE `tb_seminar`  (
  `id_seminar` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `seminar` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tempat` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `penyelenggara` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `no_piagam` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_piagam` date NOT NULL,
  PRIMARY KEY (`id_seminar`) USING BTREE,
  INDEX `fk_tb_seminar_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_seminar_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_sertifikasi
-- ----------------------------
DROP TABLE IF EXISTS `tb_sertifikasi`;
CREATE TABLE `tb_sertifikasi`  (
  `id_sertif` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `sertifikasi` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `penyelenggara` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tgl_sertifikat` date NULL DEFAULT NULL,
  `tgl_expired` date NULL DEFAULT NULL,
  `sertifikat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` datetime NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_sertif`) USING BTREE,
  INDEX `fk_tb_sertifikasi_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_sertifikasi_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 279 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_suamiistri
-- ----------------------------
DROP TABLE IF EXISTS `tb_suamiistri`;
CREATE TABLE `tb_suamiistri`  (
  `id_si` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_peg` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_peg_old` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nik` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tmp_lhr` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_lhr` date NOT NULL,
  `pendidikan` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_pekerjaan` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `pekerjaan` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status_hub` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `hp` varchar(13) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `bpjs_pasangan` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `date_reg` date NOT NULL,
  PRIMARY KEY (`id_si`) USING BTREE,
  INDEX `fk_tb_suamiistri_pegawai`(`id_peg` ASC) USING BTREE,
  CONSTRAINT `fk_tb_suamiistri_pegawai` FOREIGN KEY (`id_peg`) REFERENCES `tb_pegawai` (`id_peg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for tb_user
-- ----------------------------
DROP TABLE IF EXISTS `tb_user`;
CREATE TABLE `tb_user`  (
  `id_user` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama_user` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jabatan` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `hak_akses` varchar(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status_aktif` enum('Y','N') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Y' COMMENT 'Status aktif/nonaktif user',
  `id_pegawai` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- View structure for v_pegawai_jabatan_aktif
-- ----------------------------
DROP VIEW IF EXISTS `v_pegawai_jabatan_aktif`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_pegawai_jabatan_aktif` AS SELECT j.*
FROM tb_jabatan j
WHERE COALESCE(j.sampai_tgl,'9999-12-31') = (
  SELECT MAX(COALESCE(j2.sampai_tgl,'9999-12-31'))
  FROM tb_jabatan j2
  WHERE j2.id_peg = j.id_peg
) ;

-- ----------------------------
-- View structure for v_pendidikan_akhir
-- ----------------------------
DROP VIEW IF EXISTS `v_pendidikan_akhir`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_pendidikan_akhir` AS SELECT p.*
FROM tb_pendidikan p
WHERE
  (
    p.status = 'Akhir' AND
    p.th_lulus = (
      SELECT MAX(p2.th_lulus)
      FROM tb_pendidikan p2
      WHERE p2.id_peg = p.id_peg AND p2.status = 'Akhir'
    )
  )
  OR
  (
    NOT EXISTS (
      SELECT 1 FROM tb_pendidikan x
      WHERE x.id_peg = p.id_peg AND x.status = 'Akhir'
    )
    AND p.th_lulus = (
      SELECT MAX(p3.th_lulus)
      FROM tb_pendidikan p3
      WHERE p3.id_peg = p.id_peg
    )
  ) ;

-- ----------------------------
-- Procedure structure for sp_pengangkatan_update_id
-- ----------------------------
DROP PROCEDURE IF EXISTS `sp_pengangkatan_update_id`;
delimiter ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_pengangkatan_update_id`(
  IN p_old_id VARCHAR(20),
  IN p_new_id VARCHAR(20),
  IN p_tmt DATE,
  IN p_alasan VARCHAR(100),
  IN p_user VARCHAR(20)
)
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
  END;

  START TRANSACTION;

  /* 1) Catat history & alias */
  INSERT INTO tb_id_peg_history(id_peg_lama, id_peg_baru, tmt, alasan, dibuat_oleh)
  VALUES(p_old_id, p_new_id, p_tmt, p_alasan, p_user);

  INSERT IGNORE INTO tb_id_peg_alias(id_peg_alias, id_peg_master, sumber)
  VALUES(p_old_id, p_new_id, NULL);

  /* 2) Update master */
  UPDATE tb_pegawai
     SET id_peg_old = p_old_id,
         id_peg     = p_new_id
   WHERE id_peg = p_old_id
   LIMIT 1;

  /* 3) Update semua tabel anak yang refer ke id_peg */
  /* Tambahkan/kurangi sesuai tabel yang ada di sistemmu */
  UPDATE tb_suamiistri  SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_anak        SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_ortu        SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_pendidikan  SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_diklat      SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_sertifikasi SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_jabatan     SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_mutasi      SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_hukuman     SET id_peg = p_new_id WHERE id_peg = p_old_id;
  UPDATE tb_kgb         SET id_peg = p_new_id WHERE id_peg = p_old_id; /* jika sudah dibuat */
  UPDATE tb_cuti        SET id_peg = p_new_id WHERE id_peg = p_old_id;
  /* Jika ada tb_notifikasi, tb_log_aktivitas menyimpan entity_id = id_peg, ikut update bila relevan */
  UPDATE tb_notifikasi  SET ref_id = p_new_id WHERE ref_modul='pegawai' AND ref_id = p_old_id;
  UPDATE tb_log_aktivitas SET entity_id = p_new_id WHERE modul='pegawai' AND entity_id = p_old_id;

  COMMIT;
END
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
