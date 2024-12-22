-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2024 at 10:28 PM
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
-- Database: `letsgotax`
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
(1, 'SuperAdmin', 'superr@gmail.com', '$2y$10$XtzQlnmrVPK0xKvYxRncXuE3RtSaiQx6ik5DJ7/yF3eZALUnPubY.', 'profileDefault.jpg', '2024-12-20 09:53:38');

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
  `status` enum('VERIFIED','NOT VERIFIED','ON PROGRESS') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`akunId`, `adminId`, `email`, `username`, `password`, `photoProfile`, `status`) VALUES
(8, 1, 'unity@gmail.com', 'UNITY', '$2y$10$OpqnJFnbHSb5tkKe0o8aIOTGl6IGeVSjKoR8doEg6vTCVcXk8OO32', 'profileDefault.jpg', 'VERIFIED');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `contactId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `titleContact` varchar(255) NOT NULL,
  `massageContact` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `databio`
--

CREATE TABLE `databio` (
  `databioId` int(11) NOT NULL,
  `akunId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `namaLengkap` varchar(255) NOT NULL,
  `alamat` mediumtext NOT NULL,
  `alamatNow` mediumtext NOT NULL,
  `nik` char(16) NOT NULL,
  `photoKTPSelfie` varchar(255) NOT NULL,
  `photoKTP` varchar(255) NOT NULL,
  `noHP` char(12) NOT NULL,
  `kelamin` enum('LAKI-LAKI','PEREMPUAN') NOT NULL,
  `tanggalLahir` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `databio`
--

INSERT INTO `databio` (`databioId`, `akunId`, `adminId`, `namaLengkap`, `alamat`, `alamatNow`, `nik`, `photoKTPSelfie`, `photoKTP`, `noHP`, `kelamin`, `tanggalLahir`) VALUES
(6, 8, 1, 'Unity Lity', 'Jln. jus apel rasa pisang Blok L no.20', 'jln. jus pisang rasa apel Blok M no.20', '1234123412341234', '1734861282_TrySelfieKTP.png', '1734861282_TryKTP.png', '123456789012', 'LAKI-LAKI', 'Sleman, 26 Februari 2000');

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `No_Rangka` varchar(255) NOT NULL,
  `No_Mesin` varchar(255) NOT NULL,
  `No_Plat` varchar(255) NOT NULL,
  `akunId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `namaPemilik` varchar(255) NOT NULL,
  `statusPilih` enum('SELECTED','UNSELECTED') NOT NULL,
  `jenisKendaraan` enum('PRIBADI','UMUM','NIAGA','DINAS','KHUSUS','LISTRIK') NOT NULL
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
  `id_pembayaran` int(11) NOT NULL,
  `Bukti_Pembayaran` varchar(255) DEFAULT NULL,
  `No_Tagihan` varchar(255) NOT NULL,
  `Tanggal_Bayar` date NOT NULL,
  `Metode_Pembayaran` varchar(255) NOT NULL,
  `id_kendaraan` varchar(255) DEFAULT NULL,
  `id_Status_Pembayaran` varchar(255) DEFAULT NULL,
  `status` enum('PENDING','PROCESSED','COMPLETED','FAILED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `point`
--

CREATE TABLE `point` (
  `pointId` int(11) NOT NULL AUTO_INCREMENT,
  `akunId` int(11) NOT NULL,
  `totalPoint` int(11) NOT NULL DEFAULT 0,
  `lastUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`pointId`),
  KEY `akunId` (`akunId`),
  FOREIGN KEY (`akunId`) REFERENCES `akun`(`akunId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `point_history`
--

CREATE TABLE `point_history` (
  `historyId` int(11) NOT NULL AUTO_INCREMENT,
  `akunId` int(11) NOT NULL,
  `pointAmount` int(11) NOT NULL,
  `type` enum('earn','redeem') NOT NULL,
  `description` varchar(255) NOT NULL,
  `transactionDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_id` varchar(50) DEFAULT NULL COMMENT 'ID referensi ke pembayaran atau voucher',
  PRIMARY KEY (`historyId`),
  KEY `akunId` (`akunId`),
  FOREIGN KEY (`akunId`) REFERENCES `akun`(`akunId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
-- Table structure for table `tax`
--

CREATE TABLE `tax` (
  `taxId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `namaLengkap` varchar(255) NOT NULL,
  `platKendaraan` varchar(255) NOT NULL,
  `jenisKendaraan` enum('PRIBADI','PRIBADI LAIN','UMUM','NIAGA','DINAS','KHUSUS','LISTRIK') NOT NULL,
  `totalPajak` varchar(255) NOT NULL,
  `lastPay` varchar(255) NOT NULL,
  `status` enum('ON TIME','OVERDUE') NOT NULL,
  `dendaPajak` varchar(255) NOT NULL,
  `nextPay` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tax`
--

INSERT INTO `tax` (`taxId`, `adminId`, `namaLengkap`, `platKendaraan`, `jenisKendaraan`, `totalPajak`, `lastPay`, `status`, `dendaPajak`, `nextPay`) VALUES
(1, 1, 'Aliya Hanifa', 'AB 2103 WS', 'PRIBADI', 'Rp. 254.000,-', '24-12-2023', 'ON TIME', 'Rp. 0,-', '24-12-2024');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucherId` int(11) NOT NULL AUTO_INCREMENT,
  `voucherName` varchar(100) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `pointCost` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`voucherId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`voucherName`, `startDate`, `endDate`, `pointCost`, `stock`, `status`) VALUES
('thisisapril 20% discount voucher', '2024-12-23', '2025-11-01', 67, 10, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_redemptions`
--

CREATE TABLE `voucher_redemptions` (
  `redemptionId` int(11) NOT NULL AUTO_INCREMENT,
  `voucherId` int(11) NOT NULL,
  `akunId` int(11) NOT NULL,
  `redemptionCode` varchar(50) NOT NULL,
  `redemptionDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('ACTIVE','USED','EXPIRED') NOT NULL DEFAULT 'ACTIVE',
  `expiryDate` date NOT NULL,
  PRIMARY KEY (`redemptionId`),
  KEY `voucherId` (`voucherId`),
  KEY `akunId` (`akunId`),
  FOREIGN KEY (`voucherId`) REFERENCES `vouchers`(`voucherId`) ON DELETE CASCADE,
  FOREIGN KEY (`akunId`) REFERENCES `akun`(`akunId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`contactId`);

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
  ADD KEY `akunId` (`akunId`),
  ADD KEY `adminId` (`adminId`);

--
-- Indexes for table `notif`
--
ALTER TABLE `notif`
  ADD PRIMARY KEY (`notifId`),
  ADD UNIQUE KEY `akunId` (`akunId`),
  ADD UNIQUE KEY `adminId` (`adminId`),
  ADD KEY `tanggal_idx` (`tanggalNotif`);

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
  ADD UNIQUE KEY `akunId` (`akunId`);

--
-- Indexes for table `point_history`
--
ALTER TABLE `point_history`
  ADD PRIMARY KEY (`historyId`),
  ADD KEY `akunId` (`akunId`);

--
-- Indexes for table `status_pembayaran`
--
ALTER TABLE `status_pembayaran`
  ADD PRIMARY KEY (`id_Status_Pembayaran`);

--
-- Indexes for table `tax`
--
ALTER TABLE `tax`
  ADD PRIMARY KEY (`taxId`),
  ADD KEY `adminId` (`adminId`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucherId`),
  ADD KEY `adminId` (`adminId`);

--
-- Indexes for table `voucher_redemptions`
--
ALTER TABLE `voucher_redemptions`
  ADD PRIMARY KEY (`redemptionId`),
  ADD KEY `voucherId` (`voucherId`),
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
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `contactId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `databio`
--
ALTER TABLE `databio`
  MODIFY `databioId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- AUTO_INCREMENT for table `point_history`
--
ALTER TABLE `point_history`
  MODIFY `historyId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tax`
--
ALTER TABLE `tax`
  MODIFY `taxId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucherId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher_redemptions`
--
ALTER TABLE `voucher_redemptions`
  MODIFY `redemptionId` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `kendaraan_ibfk_1` FOREIGN KEY (`akunId`) REFERENCES `akun` (`akunId`),
  ADD CONSTRAINT `kendaraan_ibfk_2` FOREIGN KEY (`adminId`) REFERENCES `admin` (`adminId`);

--
-- Constraints for table `point_history`
--
ALTER TABLE `point_history`
  ADD CONSTRAINT `akunId` FOREIGN KEY (`akunId`) REFERENCES `akun` (`akunId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
