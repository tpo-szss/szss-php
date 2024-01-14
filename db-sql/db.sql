-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2024 at 08:46 PM
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
-- Database: `szss`
--

-- --------------------------------------------------------

--
-- Table structure for table `opravila`
--

CREATE TABLE `opravila` (
  `ID_OPRAVILA` int(11) NOT NULL,
  `PONOVITEV` enum('daily','monthly','yearly') NOT NULL,
  `START_DATUM` date NOT NULL,
  `END_DATUM` date NOT NULL,
  `ZADNJI_CAS_TEKA` date NOT NULL,
  `AKTIVNO` tinyint(4) NOT NULL DEFAULT 1,
  `USTVARJENI_CAS` timestamp NOT NULL DEFAULT current_timestamp(),
  `ID_TRANSAKCIJE` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sandbox`
--

CREATE TABLE `sandbox` (
  `ID_SANDBOX` int(11) NOT NULL,
  `ID_UPORABNIKA` int(11) NOT NULL,
  `IME` varchar(50) DEFAULT NULL,
  `TIP` enum('main','side') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transakcije`
--

CREATE TABLE `transakcije` (
  `ID_TRANSAKCIJE` int(11) NOT NULL,
  `ID_SANDBOX` int(11) NOT NULL,
  `TIP` enum('priliv','odliv') NOT NULL,
  `OPIS` varchar(50) DEFAULT NULL,
  `ZNESEK` decimal(65,2) NOT NULL,
  `CAS_DATUM_TRANSAKCIJE` timestamp NOT NULL DEFAULT current_timestamp(),
  `DATOTEKA` varchar(250) NOT NULL,
  `DOLG_OPIS` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uporabniki`
--

CREATE TABLE `uporabniki` (
  `ID_UPORABNIKA` int(11) NOT NULL,
  `UPORABNISKO_IME` varchar(30) NOT NULL,
  `EMAIL` varchar(35) NOT NULL CHECK (`EMAIL` like '%@%'),
  `GESLO` varchar(1024) NOT NULL,
  `AKTIVEN` tinyint(1) NOT NULL,
  `EMAIL_TOKEN` text NOT NULL,
  `EMAIL_TOKEN_CAS` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `opravila`
--
ALTER TABLE `opravila`
  ADD PRIMARY KEY (`ID_OPRAVILA`),
  ADD KEY `ID_TRANSAKCIJE` (`ID_TRANSAKCIJE`);

--
-- Indexes for table `sandbox`
--
ALTER TABLE `sandbox`
  ADD PRIMARY KEY (`ID_SANDBOX`),
  ADD KEY `ID_UPORABNIKA` (`ID_UPORABNIKA`);

--
-- Indexes for table `transakcije`
--
ALTER TABLE `transakcije`
  ADD PRIMARY KEY (`ID_TRANSAKCIJE`),
  ADD KEY `ID_SANDBOX` (`ID_SANDBOX`);

--
-- Indexes for table `uporabniki`
--
ALTER TABLE `uporabniki`
  ADD PRIMARY KEY (`ID_UPORABNIKA`),
  ADD UNIQUE KEY `UPORABNISKO_IME` (`UPORABNISKO_IME`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `opravila`
--
ALTER TABLE `opravila`
  MODIFY `ID_OPRAVILA` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sandbox`
--
ALTER TABLE `sandbox`
  MODIFY `ID_SANDBOX` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transakcije`
--
ALTER TABLE `transakcije`
  MODIFY `ID_TRANSAKCIJE` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uporabniki`
--
ALTER TABLE `uporabniki`
  MODIFY `ID_UPORABNIKA` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `opravila`
--
ALTER TABLE `opravila`
  ADD CONSTRAINT `opravila_ibfk_1` FOREIGN KEY (`ID_TRANSAKCIJE`) REFERENCES `transakcije` (`ID_TRANSAKCIJE`);

--
-- Constraints for table `sandbox`
--
ALTER TABLE `sandbox`
  ADD CONSTRAINT `sandbox_ibfk_1` FOREIGN KEY (`ID_UPORABNIKA`) REFERENCES `uporabniki` (`ID_UPORABNIKA`);

--
-- Constraints for table `transakcije`
--
ALTER TABLE `transakcije`
  ADD CONSTRAINT `transakcije_ibfk_1` FOREIGN KEY (`ID_SANDBOX`) REFERENCES `sandbox` (`ID_SANDBOX`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
