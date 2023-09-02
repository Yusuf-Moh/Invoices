-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 02. Sep 2023 um 21:34
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `software`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `deletedrechnung`
--

CREATE TABLE `deletedrechnung` (
  `Leistung` text NOT NULL,
  `Abrechnungsart` text NOT NULL,
  `NettoPreis` text NOT NULL,
  `KundenID` int(11) NOT NULL,
  `MonatlicheRechnungBool` tinyint(1) NOT NULL,
  `RechnungsDatum` text NOT NULL,
  `Monat_Jahr` text NOT NULL,
  `RechnungsNummer` int(11) NOT NULL,
  `RechnungsKürzelNummer` text NOT NULL COMMENT 'RechnungsKürzelMMJJ/RechnungsNR',
  `RechnungsID` int(11) NOT NULL,
  `MwSt` text DEFAULT NULL,
  `GesamtBetrag` text DEFAULT NULL,
  `Zeitpunkt_Loeschung` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Zeitpunkt der Löschung',
  `Pfad` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunden`
--

CREATE TABLE `kunden` (
  `FirmenName` text DEFAULT NULL,
  `Adresse` text NOT NULL,
  `RechnungsKürzel` text NOT NULL,
  `PLZ` text NOT NULL,
  `Ort` text NOT NULL,
  `VertragsDatum` text DEFAULT NULL,
  `Name_Ansprechpartner` text DEFAULT NULL,
  `Gender` varchar(6) DEFAULT NULL,
  `KundenID` int(11) NOT NULL,
  `person` tinyint(1) DEFAULT 0,
  `organization` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `monatliche_rechnung`
--

CREATE TABLE `monatliche_rechnung` (
  `MonatlicheRechnungsID` int(11) NOT NULL,
  `RechnungsID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rechnung`
--

CREATE TABLE `rechnung` (
  `Leistung` text NOT NULL,
  `Abrechnungsart` text NOT NULL,
  `NettoPreis` text NOT NULL,
  `KundenID` int(11) NOT NULL,
  `MonatlicheRechnungBool` tinyint(1) NOT NULL,
  `RechnungsDatum` text NOT NULL,
  `Monat_Jahr` text NOT NULL,
  `RechnungsNummer` int(11) NOT NULL,
  `RechnungsKürzelNummer` text NOT NULL COMMENT 'RechnungsKürzelMMJJ/RechnungsNR',
  `RechnungsID` int(11) NOT NULL,
  `MwSt` text DEFAULT NULL,
  `GesamtBetrag` text DEFAULT NULL,
  `Pfad` text DEFAULT NULL,
  `Bezahlt` tinyint(1) NOT NULL DEFAULT 0,
  `UeberweisungsDatum` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `deletedrechnung`
--
ALTER TABLE `deletedrechnung`
  ADD PRIMARY KEY (`RechnungsID`);

--
-- Indizes für die Tabelle `kunden`
--
ALTER TABLE `kunden`
  ADD PRIMARY KEY (`KundenID`);

--
-- Indizes für die Tabelle `monatliche_rechnung`
--
ALTER TABLE `monatliche_rechnung`
  ADD PRIMARY KEY (`MonatlicheRechnungsID`);

--
-- Indizes für die Tabelle `rechnung`
--
ALTER TABLE `rechnung`
  ADD PRIMARY KEY (`RechnungsID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `kunden`
--
ALTER TABLE `kunden`
  MODIFY `KundenID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `monatliche_rechnung`
--
ALTER TABLE `monatliche_rechnung`
  MODIFY `MonatlicheRechnungsID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `rechnung`
--
ALTER TABLE `rechnung`
  MODIFY `RechnungsID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
