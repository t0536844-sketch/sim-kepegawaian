-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 07:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rsud_mimika_kepegawaian`
--

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-10 04:52:12'),
(2, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-10 04:52:22'),
(3, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-10 05:09:42'),
(4, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-10 05:09:52'),
(5, 1, 'CREATE', 'users', 2, 'Menambah user baru: user', NULL, '2026-01-10 05:26:43'),
(6, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-10 05:27:28'),
(7, 2, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-10 05:27:33'),
(8, 2, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-10 05:30:57'),
(9, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-10 05:31:01'),
(10, 1, 'CREATE', 'users', 3, 'Menambah user baru: view', NULL, '2026-01-10 05:31:50'),
(11, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-10 05:32:07'),
(12, 3, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-10 05:32:11'),
(13, 3, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-10 05:32:37'),
(14, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-10 05:32:40'),
(15, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-10 05:34:02'),
(16, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-12 00:36:24'),
(17, 1, 'CREATE', 'pegawai', 2, 'Menambah data pegawai baru: JOKO', NULL, '2026-01-12 00:44:20'),
(18, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-12 02:19:40'),
(19, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-12 02:19:54'),
(20, 1, 'CREATE', 'pegawai', 3, 'Menambah data pegawai baru: KODIR', NULL, '2026-01-12 23:53:51'),
(21, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-13 02:42:12'),
(22, 1, 'CREATE', 'pegawai', 4, 'Menambah data pegawai baru: RANI', NULL, '2026-01-13 03:12:23'),
(23, 1, 'UPDATE', 'pegawai', 4, 'Mengupdate data pegawai: RANI', NULL, '2026-01-13 04:49:34'),
(24, 1, 'UPDATE', 'pegawai', 4, 'Mengupdate data pegawai: RANI', NULL, '2026-01-13 04:54:31'),
(25, 1, 'UPDATE', 'pegawai', 2, 'Mengupdate data pegawai: JOKO', NULL, '2026-01-13 04:59:29'),
(26, 1, 'UPDATE', 'pegawai', 2, 'Mengupdate data pegawai: JOKO', NULL, '2026-01-13 05:09:55'),
(27, 1, 'UPDATE', 'pegawai', 4, 'Mengupdate data pegawai: RANI', NULL, '2026-01-13 05:14:36'),
(28, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-13 06:03:41'),
(29, 3, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-13 06:03:48'),
(30, 3, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-13 06:08:16'),
(31, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-13 06:08:25'),
(32, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-13 06:09:55'),
(33, 3, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-13 06:10:00'),
(34, 3, 'CREATE', 'pegawai', 5, 'Menambah data pegawai baru: LINDA', NULL, '2026-01-13 06:22:27'),
(35, 3, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-13 06:48:43'),
(36, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-13 06:48:51'),
(37, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-13 07:08:16'),
(38, 3, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-13 22:59:32'),
(39, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-13 22:59:41'),
(40, 1, 'LOGOUT', NULL, NULL, 'User logout', '::1', '2026-01-13 23:29:07'),
(41, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-14 00:03:37'),
(42, 1, 'LOGIN', NULL, NULL, 'User login', '::1', '2026-01-14 05:01:19');

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `nama_lengkap` varchar(255) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `jenis_kelamin` enum('Pria','Wanita') DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `pangkat_golongan` varchar(50) DEFAULT NULL,
  `pendidikan` varchar(255) DEFAULT NULL,
  `status_pernikahan` varchar(50) DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `status_kepegawaian` varchar(50) DEFAULT NULL,
  `link_sk` text DEFAULT NULL,
  `jumlah_keluarga` int(11) DEFAULT 0,
  `alamat_rumah` text DEFAULT NULL,
  `link_ktp` text DEFAULT NULL,
  `link_kartu_keluarga` text DEFAULT NULL,
  `link_ijazah` text DEFAULT NULL,
  `link_str` text DEFAULT NULL,
  `masa_berlaku_str` date DEFAULT NULL,
  `link_sip` text DEFAULT NULL,
  `masa_berlaku_sip` date DEFAULT NULL,
  `nomor_kartu_pegawai` varchar(100) DEFAULT NULL,
  `link_npwp` text DEFAULT NULL,
  `link_foto` text DEFAULT NULL,
  `link_akta_lahir` text DEFAULT NULL,
  `link_akta_nikah` text DEFAULT NULL,
  `link_skp` text DEFAULT NULL,
  `link_sk_kenaikan_pangkat` text DEFAULT NULL,
  `link_sk_jabatan` text DEFAULT NULL,
  `link_sk_mutasi` text DEFAULT NULL,
  `link_sk_pensiun` text DEFAULT NULL,
  `link_sertifikat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id`, `timestamp`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `agama`, `jenis_kelamin`, `nip`, `pangkat_golongan`, `pendidikan`, `status_pernikahan`, `jabatan`, `status_kepegawaian`, `link_sk`, `jumlah_keluarga`, `alamat_rumah`, `link_ktp`, `link_kartu_keluarga`, `link_ijazah`, `link_str`, `masa_berlaku_str`, `link_sip`, `masa_berlaku_sip`, `nomor_kartu_pegawai`, `link_npwp`, `link_foto`, `link_akta_lahir`, `link_akta_nikah`, `link_skp`, `link_sk_kenaikan_pangkat`, `link_sk_jabatan`, `link_sk_mutasi`, `link_sk_pensiun`, `link_sertifikat`, `created_at`, `updated_at`) VALUES
(1, '2026-01-10 04:22:28', 'Uji coba data', 'Nabire', '1990-01-31', 'Konghucu', 'Pria', '123123123', 'IV/a', 'S1 ilmu kesehatan masyarakat', 'Menikah', 'Staf', 'PNS', 'https://drive.google.com/open?id=1aYrA86pYxZ9fkAOWtfqCoy6QSGXUGiw-', 2, 'DINAS', 'https://drive.google.com/open?id=1TPoRqoVz030dfZYj8MmSvl3SkBvvp99C', 'https://drive.google.com/open?id=1mVRR9FYy07Hl4CdWZ8Fw10K-naEU4ViP', 'https://drive.google.com/open?id=1fo2_zcH1Y62RLEKteIzTIplHplbyvftN', 'https://drive.google.com/open?id=1O3RaQ9J7OsFQP0AOih3bLC48cf2uiRu5', '2026-03-04', 'https://drive.google.com/open?id=147PjKg7uMco3jdTBc1ZgWJwZB7M3i7D6', '2026-03-13', '123123123', 'https://drive.google.com/open?id=1G6xGTQch8jON-m8l9TJNaiTXCNwv_JIW', 'https://drive.google.com/open?id=1KtuNgnxGpTm1XELZ4Q85NSrerKQ8o47G', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 04:22:28', '2026-01-10 04:22:28'),
(2, '2026-01-12 00:44:20', 'JOKO', 'MADURA', '1977-01-01', 'Islam', 'Pria', '197701012015100001', 'III/a', 'S-1 TEKNIK', 'Menikah', 'PELAKSANA', 'PNS', 'uploads/JOKO_2/SK/6965898e8fc19_BAHV_28_20260113_060955.pdf', 3, 'Kwamki Narama', 'uploads/JOKO_2/KTP/6965898e94ae5_BAHV_366_20260113_060955.pdf', 'uploads/JOKO_2/KARTU_KELUARGA/6965898e94cc9_BAHV_760_20260113_060955.pdf', 'uploads/JOKO_2/IJAZAH/6965898e94eec_BAHV_761_20260113_060955.pdf', 'uploads/JOKO_2/STR/6965898e95a5a_BAHV_2960_20260113_060955.pdf', '2027-01-01', 'uploads/JOKO_2/SIP/6965898e941aa_BAHV_29_20260113_060955.pdf', '2027-01-01', '1010101010', 'uploads/JOKO_2/NPWP/6965898e941aa_BAHV_29_20260113_060955.pdf', 'uploads/JOKO_2/FOTO/rsutimika-logo_20260113_060955.png', 'uploads/JOKO_2/AKTA_LAHIR/6965898e950d0_BAHV_761_20260113_060955.pdf', 'uploads/JOKO_2/AKTA_NIKAH/6965898e952b0_BAHV_996_20260113_060955.pdf', 'uploads/JOKO_2/SKP/6965898e952b0_BAHV_996_20260113_060955.pdf', 'uploads/JOKO_2/KENAIKAN_PANGKAT/6965898e94703_BAHV_246_20260113_060955.pdf', 'uploads/JOKO_2/SK_JABATAN/6965898e94703_BAHV_246_20260113_060955.pdf', 'uploads/JOKO_2/SK_MUTASI/6965898e94703_BAHV_246_20260113_060955.pdf', 'uploads/JOKO_2/SK_PENSIUN/6965898e95695_BAHV_3650_20260113_060955.pdf', 'uploads/JOKO_2/SERTIFIKAT/6965898e95877_BAHV_760_20260113_060955.pdf', '2026-01-12 00:44:20', '2026-01-13 05:09:55'),
(3, '2026-01-12 23:53:50', 'KODIR', 'MADURA', '1977-01-01', 'Islam', 'Pria', '00000000003', 'II/d', 'SMU', 'Menikah', 'PELAKSANA', 'PNS', 'uploads/6965898e8fc19_BAHV_28.pdf', 4, 'SP2', 'uploads/6965898e941aa_BAHV_29.pdf', 'uploads/6965898e943a6_BAHV_52.pdf', 'uploads/6965898e94703_BAHV_246.pdf', 'uploads/6965898e94901_BAHV_356.pdf', '2026-01-12', 'uploads/6965898e94ae5_BAHV_366.pdf', '2026-01-12', '1010101011', 'uploads/6965898e94cc9_BAHV_760.pdf', '', 'uploads/6965898e94eec_BAHV_761.pdf', 'uploads/6965898e950d0_BAHV_761.pdf', 'uploads/6965898e952b0_BAHV_996.pdf', 'uploads/6965898e95492_BAHV_1092.pdf', 'uploads/6965898e95695_BAHV_3650.pdf', 'uploads/6965898e95877_BAHV_760.pdf', 'uploads/6965898e95a5a_BAHV_2960.pdf', 'uploads/6965898e95c3c_BAHV_3537.pdf', '2026-01-12 23:53:50', '2026-01-12 23:53:50'),
(4, '2026-01-13 03:12:18', 'RANI', 'TORAJA', '1988-12-02', 'Kristen', 'Wanita', '00000000004', 'III/a', 'S-1 EKONOMI', 'Belum Menikah', 'PELAKSANA', 'CPNS', 'uploads/RANI_4/SK/6965898e94ae5_BAHV_366_20260113_054933.pdf', 0, 'NAWARIPI', 'uploads/RANI_4/KTP/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/KARTU_KELUARGA/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/IJAZAH/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/STR/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', '2026-01-03', 'uploads/RANI_4/SIP/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', '2026-01-05', '1010101012', 'uploads/RANI_4/NPWP/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/FOTO/LOGO_SI-MONIKA_TIMBUL-NON_BG_20260113_061436.jpg', 'uploads/RANI_4/AKTA_LAHIR/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/AKTA_NIKAH/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/SKP/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/KENAIKAN_PANGKAT/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/SK_JABATAN/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/SK_MUTASI/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/SK_PENSIUN/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', 'uploads/RANI_4/SERTIFIKAT/SURAT_PINDAH_DOMISILI-oke_20260113_041218.pdf', '2026-01-13 03:12:18', '2026-01-13 05:14:36'),
(5, '2026-01-13 06:22:22', 'LINDA', 'JAYAPURA', '1989-02-19', 'Kristen', 'Wanita', '123456789', 'II/c', 'SMU', 'Menikah', 'PELAKSANA', 'PNS', 'uploads/LINDA_5/SK/6965898e941aa_BAHV_29_20260113_072221.pdf', 2, 'TIMIKA INDAH', 'uploads/LINDA_5/KTP/6965898e94cc9_BAHV_760_20260113_072221.pdf', 'uploads/LINDA_5/KARTU_KELUARGA/6965898e94eec_BAHV_761_20260113_072221.pdf', 'uploads/LINDA_5/IJAZAH/6965898e95c3c_BAHV_3537_20260113_072222.pdf', 'uploads/LINDA_5/STR/6965898e950d0_BAHV_761_20260113_072222.pdf', '2026-01-20', 'uploads/LINDA_5/SIP/6965898e95c3c_BAHV_3537_20260113_072222.pdf', '2026-05-02', '987456123', 'uploads/LINDA_5/NPWP/6965898e950d0_BAHV_761_20260113_072222.pdf', 'uploads/LINDA_5/FOTO/rsutimika-logo_20260113_060955_20260113_072222.png', 'uploads/LINDA_5/AKTA_LAHIR/6965898e94703_BAHV_246_20260113_072222.pdf', 'uploads/LINDA_5/AKTA_NIKAH/6965898e952b0_BAHV_996_20260113_072222.pdf', 'uploads/LINDA_5/SKP/6965898e94703_BAHV_246_20260113_072222.pdf', 'uploads/LINDA_5/KENAIKAN_PANGKAT/6965898e94703_BAHV_246_20260113_072222.pdf', 'uploads/LINDA_5/SK_JABATAN/6965898e94703_BAHV_246_20260113_072222.pdf', 'uploads/LINDA_5/SK_MUTASI/6965898e95c3c_BAHV_3537_20260113_072222.pdf', 'uploads/LINDA_5/SK_PENSIUN/6965898e94901_BAHV_356_20260113_072222.pdf', 'uploads/LINDA_5/SERTIFIKAT/6965898e943a6_BAHV_52_20260113_072222.pdf', '2026-01-13 06:22:22', '2026-01-13 06:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) DEFAULT NULL,
  `role` enum('admin','operator','viewer') DEFAULT 'operator',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Administrator', 'admin', '2026-01-10 04:22:28'),
(2, 'user', 'e606e38b0d8c19b24cf0ee3808183162ea7cd63ff7912dbb22b5e803286b4446', 'user', 'operator', '2026-01-10 05:26:42'),
(3, 'view', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'viewer', 'viewer', '2026-01-10 05:31:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
