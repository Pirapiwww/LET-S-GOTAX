-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2024 at 08:46 AM
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
-- Table structure for table `akun`
--

CREATE TABLE `akun` (
  `akunId` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photoProfile` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`akunId`, `email`, `username`, `password`, `photoProfile`) VALUES
(1, 'unity@gmail.com', 'UNITY', '$2y$10$GLyazLrjc1JntcFx2vltB.70POA0mcHmLD1fcZJA5l63xt1e8hiay', 'profileDefault.jpg'),
(3, 'p@gmail.com', 'p', '$2y$10$tON9vsPohyF/eG/.HfnXa.pvFtKINItRanhJtGM.gyNlUNPgo5AnW', 'profileDefault.jpg'),
(4, 'rap@gmail.com', 'rap', '$2y$10$QbelrHbLrfSLNKTgXZrt6.fQ8nHgewG6Ts0BmAmz.jTfZ/Zpzh8p.', 'profileDefault.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `databio`
--

CREATE TABLE `databio` (
  `databioId` int(11) NOT NULL,
  `akunId` int(11) NOT NULL,
  `namaLengkap` varchar(255) NOT NULL,
  `nik` int(16) NOT NULL,
  `photoKTPSelfie` varchar(255) NOT NULL,
  `photoKTP` varchar(255) NOT NULL,
  `noHP` int(12) NOT NULL,
  `kelamin` enum('LAKI-LAKI','PEREMPUAN') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`akunId`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `databio`
--
ALTER TABLE `databio`
  ADD PRIMARY KEY (`databioId`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `noHP` (`noHP`),
  ADD KEY `akunId` (`akunId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `akun`
--
ALTER TABLE `akun`
  MODIFY `akunId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `databio`
--
ALTER TABLE `databio`
  MODIFY `databioId` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `databio`
--
ALTER TABLE `databio`
  ADD CONSTRAINT `databio_ibfk_1` FOREIGN KEY (`akunId`) REFERENCES `akun` (`akunId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
