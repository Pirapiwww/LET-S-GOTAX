-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2024 at 11:08 AM
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
-- Database: `lestgotax`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminId` int(11) NOT NULL,
  `usernameAdmin` varchar(255) NOT NULL,
  `emailAdmin` varchar(255) NOT NULL,
  `passwordAdmin` varchar(255) NOT NULL,
  `profileAdmin` varchar(255) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminId`, `usernameAdmin`, `emailAdmin`, `passwordAdmin`, `profileAdmin`, `last_login`) VALUES
(1, 'SuperAdmin', 'superr@gmail.com', 'AdminSuper', 'profileDefault.jpg', '2024-12-20 09:53:38');

-- --------------------------------------------------------

--
-- Table structure for table `akun`
--

CREATE TABLE `akun` (
  `akunId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photoProfile` varchar(255) NOT NULL,
  `status` enum('VERIFIED','NOT VERIFIED') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`akunId`, `adminId`, `email`, `username`, `password`, `photoProfile`, `status`) VALUES
(8, 1, 'unity@gmail.com', 'UNITY', '$2y$10$OpqnJFnbHSb5tkKe0o8aIOTGl6IGeVSjKoR8doEg6vTCVcXk8OO32', 'profileDefault.jpg', 'NOT VERIFIED');

-- --------------------------------------------------------

--
-- Table structure for table `databio`
--

CREATE TABLE `databio` (
  `databioId` int(11) NOT NULL,
  `akunId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `namaLengkap` varchar(255) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `nik` char(16) NOT NULL,
  `photoKTPSelfie` varchar(255) NOT NULL,
  `photoKTP` varchar(255) NOT NULL,
  `noHP` char(12) NOT NULL,
  `kelamin` enum('LAKI-LAKI','PEREMPUAN') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id_kendaraan` varchar(50) NOT NULL,
  `No_Rangka` varchar(50) NOT NULL,
  `No_Mesin` varchar(50) NOT NULL,
  `No_Plat` varchar(20) NOT NULL,
  `Jumlah_Biaya` decimal(10,2) NOT NULL,
  `tgl_Jatuh_Tempo` date NOT NULL,
  `akunId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notif`
--

CREATE TABLE `notif` (
  `notifId` int(11) NOT NULL,
  `akunId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `tanggalNotif` date NOT NULL,
  `jenisNotif` enum('TAX','POINT','SYSTEM') NOT NULL,
  `descNotif` varchar(255) NOT NULL,
  `descTambahan` varchar(255) NOT NULL,
  `statusNotif` enum('READ','UNREAD') NOT NULL,
  `descStatus` enum('SUCCEED','NOT SUCCESS') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` varchar(50) NOT NULL,
  `Bukti_Pembayaran` varchar(255) DEFAULT NULL,
  `No_Tagihan` varchar(50) NOT NULL,
  `Tanggal_Bayar` date NOT NULL,
  `Metode_Pembayaran` varchar(50) NOT NULL,
  `id_kendaraan` varchar(50) DEFAULT NULL,
  `id_Status_Pembayaran` varchar(50) DEFAULT NULL,
  `status` enum('PENDING','PROCESSED','COMPLETED','FAILED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `point`
--

CREATE TABLE `point` (
  `pointId` int(11) NOT NULL,
  `akunId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `totalPoint` mediumint(11) NOT NULL,
  `tgl_perolehan` date DEFAULT NULL,
  `keterangan_poin` text DEFAULT NULL,
  `status_poin` varchar(20) DEFAULT NULL,
  `tgl_kadaluarsa` date DEFAULT NULL,
  `minimum_transaksi` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status_pembayaran`
--

CREATE TABLE `status_pembayaran` (
  `id_Status_Pembayaran` varchar(50) NOT NULL,
  `Nama_Status_Pembayaran` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status_pembayaran`
--

INSERT INTO `status_pembayaran` (`id_Status_Pembayaran`, `Nama_Status_Pembayaran`) VALUES
('STP1', 'Pending'),
('STP2', 'Processed'),
('STP3', 'Completed'),
('STP4', 'Failed');

-- --------------------------------------------------------

--
-- Table structure for table `user_messages`
--

CREATE TABLE `user_messages` (
  `id` int(11) NOT NULL,
  `akunId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminId`),
  ADD UNIQUE KEY `emailAdmin` (`emailAdmin`);

--
-- Indexes for table `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`akunId`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `adminId` (`adminId`);

--
-- Indexes for table `databio`
--
ALTER TABLE `databio`
  ADD PRIMARY KEY (`databioId`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `noHP` (`noHP`),
  ADD KEY `adminId` (`adminId`),
  ADD KEY `akunId` (`akunId`);

--
-- Indexes for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD UNIQUE KEY `No_Rangka` (`No_Rangka`),
  ADD UNIQUE KEY `No_Mesin` (`No_Mesin`),
  ADD UNIQUE KEY `No_Plat` (`No_Plat`),
  ADD KEY `akunId` (`akunId`);

--
-- Indexes for table `notif`
--
ALTER TABLE `notif`
  ADD PRIMARY KEY (`notifId`),
  ADD UNIQUE KEY `akunId` (`akunId`),
  ADD UNIQUE KEY `adminId` (`adminId`),
  ADD INDEX `tanggal_idx` (`tanggalNotif`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `No_Tagihan` (`No_Tagihan`),
  ADD KEY `id_kendaraan` (`id_kendaraan`),
  ADD KEY `id_Status_Pembayaran` (`id_Status_Pembayaran`);

--
-- Indexes for table `point`
--
ALTER TABLE `point`
  ADD PRIMARY KEY (`pointId`),
  ADD UNIQUE KEY `akunId` (`akunId`),
  ADD UNIQUE KEY `adminId` (`adminId`);

--
-- Indexes for table `status_pembayaran`
--
ALTER TABLE `status_pembayaran`
  ADD PRIMARY KEY (`id_Status_Pembayaran`);

--
-- Indexes for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `akunId` (`akunId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `akun`
--
ALTER TABLE `akun`
  MODIFY `akunId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `databio`
--
ALTER TABLE `databio`
  MODIFY `databioId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notif`
--
ALTER TABLE `notif`
  MODIFY `notifId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `point`
--
ALTER TABLE `point`
  MODIFY `pointId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_messages`
--
ALTER TABLE `user_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status_pembayaran`
--
ALTER TABLE `status_pembayaran`
  MODIFY `id_Status_Pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `akun`
--
ALTER TABLE `akun`
  ADD CONSTRAINT `akun_ibfk_1` FOREIGN KEY (`adminId`) REFERENCES `admin` (`adminId`);

--
-- Constraints for table `databio`
--
ALTER TABLE `databio`
  ADD CONSTRAINT `databio_ibfk_1` FOREIGN KEY (`akunId`) REFERENCES `akun` (`akunId`),
  ADD CONSTRAINT `databio_ibfk_2` FOREIGN KEY (`adminId`) REFERENCES `admin` (`adminId`);

--
-- Constraints for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD CONSTRAINT `kendaraan_ibfk_1` FOREIGN KEY (`akunId`) REFERENCES `akun` (`akunId`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`),
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`id_Status_Pembayaran`) REFERENCES `status_pembayaran` (`id_Status_Pembayaran`);

--
-- Constraints for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD CONSTRAINT `user_messages_ibfk_1` FOREIGN KEY (`akunId`) REFERENCES `akun` (`akunId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
