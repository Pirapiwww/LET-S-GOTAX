-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2024 at 08:49 AM
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
  `profileAdmin` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminId`, `usernameAdmin`, `emailAdmin`, `passwordAdmin`, `profileAdmin`) VALUES
(1, 'SuperAdmin', 'superr@gmail.com', 'AdminSuper', 'profileDefault.jpg');

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
-- Table structure for table `point`
--

CREATE TABLE `point` (
  `pointId` int(11) NOT NULL,
  `akunId` int(11) NOT NULL,
  `adminId` int(11) NOT NULL,
  `totalPoint` mediumint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD UNIQUE KEY `adminId` (`adminId`);

--
-- Indexes for table `databio`
--
ALTER TABLE `databio`
  ADD PRIMARY KEY (`databioId`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `noHP` (`noHP`),
  ADD UNIQUE KEY `adminId` (`adminId`),
  ADD KEY `akunId` (`akunId`);

--
-- Indexes for table `notif`
--
ALTER TABLE `notif`
  ADD PRIMARY KEY (`notifId`),
  ADD UNIQUE KEY `akunId` (`akunId`),
  ADD UNIQUE KEY `adminId` (`adminId`);

--
-- Indexes for table `point`
--
ALTER TABLE `point`
  ADD PRIMARY KEY (`pointId`),
  ADD UNIQUE KEY `akunId` (`akunId`),
  ADD UNIQUE KEY `adminId` (`adminId`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
