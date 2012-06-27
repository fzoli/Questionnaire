-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Hoszt: localhost
-- Létrehozás ideje: 2012. febr. 24. 15:28
-- Szerver verzió: 5.1.49
-- PHP verzió: 5.3.3-7+squeeze8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Adatbázis: `kerdoiv`
--
CREATE DATABASE `kerdoiv` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `kerdoiv`;

-- --------------------------------------------------------

--
-- Tábla szerkezet: `adatlap`
--

CREATE TABLE IF NOT EXISTS `adatlap` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned NOT NULL,
  `kerdes` int(10) unsigned DEFAULT NULL,
  `valasz` int(150) unsigned DEFAULT NULL,
  `extra_valasz` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`azon`),
  KEY `kerdes` (`kerdes`,`valasz`),
  KEY `valasz` (`valasz`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- A tábla adatainak kiíratása `adatlap`
--

INSERT INTO `adatlap` (`azon`, `id`, `kerdes`, `valasz`, `extra_valasz`) VALUES
(1, 1, NULL, 1, NULL),
(2, 1, NULL, 4, NULL),
(3, 1, NULL, 8, NULL),
(4, 1, 4, NULL, 'plc,számtech műszerész'),
(5, 1, NULL, 11, NULL),
(6, 1, NULL, 13, NULL),
(7, 1, 7, NULL, 'az előbb írtam hogy nincs...xD'),
(8, 1, NULL, 24, NULL),
(9, 1, 9, NULL, 'cvonline'),
(10, 1, NULL, 27, NULL),
(11, 1, NULL, 30, NULL),
(12, 1, 12, NULL, 'korlátlan szabadnap'),
(13, 1, NULL, 33, NULL),
(14, 1, NULL, 34, NULL),
(15, 1, NULL, 35, NULL),
(16, 1, NULL, 36, NULL),
(17, 1, NULL, 37, NULL),
(18, 1, NULL, 38, NULL),
(19, 1, NULL, 40, NULL),
(20, 1, NULL, 42, NULL),
(21, 1, NULL, 44, NULL),
(22, 1, NULL, 46, NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `adatlap_info`
--

CREATE TABLE IF NOT EXISTS `adatlap_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nyelv` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `nyelv` (`nyelv`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- A tábla adatainak kiíratása `adatlap_info`
--

INSERT INTO `adatlap_info` (`id`, `nyelv`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `hibauzenet`
--

CREATE TABLE IF NOT EXISTS `hibauzenet` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `szoveg` varchar(255) NOT NULL,
  `szoveg_d` varchar(255) NOT NULL,
  PRIMARY KEY (`azon`),
  UNIQUE KEY `szoveg` (`szoveg`,`szoveg_d`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- A tábla adatainak kiíratása `hibauzenet`
--

INSERT INTO `hibauzenet` (`azon`, `szoveg`, `szoveg_d`) VALUES
(4, 'Egynél több adat lett megadva.', 'Sie haben mehr als eine Information gegeben.'),
(1, 'Kérdőív-formátum hiba.', 'Fragebogen Format Fehler.'),
(2, 'Nem létező kérdésre válaszolt.', 'Sie haben eine Frage geantwortet was war nicht im Fragebogen.'),
(6, 'Nem létező válasz a kérdésre.', 'Es ist keine Antwort auf die Frage.'),
(3, 'Nincs minden adat kitöltve.', 'Sie haben nicht alle Information gegeben.'),
(5, 'Üresen hagyott mező.', 'Leer gelassen.');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `kapocs`
--

CREATE TABLE IF NOT EXISTS `kapocs` (
  `korlatolt_kerdes` int(10) unsigned NOT NULL,
  `kerdes` int(10) unsigned NOT NULL,
  `valasz` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kerdes`,`valasz`,`korlatolt_kerdes`),
  KEY `valasz` (`valasz`),
  KEY `korlatolt_kerdes` (`korlatolt_kerdes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `kapocs`
--

INSERT INTO `kapocs` (`korlatolt_kerdes`, `kerdes`, `valasz`) VALUES
(9, 8, 1),
(10, 8, 1),
(13, 8, 1),
(6, 5, 2),
(7, 5, 2),
(11, 5, 2),
(9, 8, 23),
(10, 8, 23),
(13, 8, 23),
(9, 8, 24),
(10, 8, 24),
(13, 8, 24),
(9, 8, 25),
(10, 8, 25),
(13, 8, 25),
(9, 8, 26),
(10, 8, 26),
(13, 8, 26),
(9, 8, 27),
(10, 8, 27),
(13, 8, 27),
(9, 8, 28),
(10, 8, 28),
(13, 8, 28);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `kerdes`
--

CREATE TABLE IF NOT EXISTS `kerdes` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `szoveg` varchar(255) NOT NULL,
  `szoveg_d` varchar(255) NOT NULL,
  `extra_megnev` int(10) unsigned DEFAULT NULL,
  `tipus` enum('radio-button','checkbox','one-input') NOT NULL DEFAULT 'radio-button',
  `kotelezo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`azon`),
  UNIQUE KEY `szoveg` (`szoveg`),
  KEY `extra_megnev` (`extra_megnev`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- A tábla adatainak kiíratása `kerdes`
--

INSERT INTO `kerdes` (`azon`, `szoveg`, `szoveg_d`, `extra_megnev`, `tipus`, `kotelezo`) VALUES
(1, 'Nem:', 'Geschlecht:', NULL, 'radio-button', 1),
(2, 'Kor:', 'Alter:', NULL, 'radio-button', 1),
(3, 'Lakóhely típusa:', 'Der Typ des Wohnorts:', NULL, 'radio-button', 1),
(4, 'Kérem sorolja fel, milyen képzettségekkel rendelkezik!', 'Welche Bildungen haben Sie?', NULL, 'one-input', 1),
(5, 'Jelenleg dolgozik? Ha igen, kérem nevezze meg!', 'Arbeiten Sie zur Zeit? Wenn “Ja” , bitte nennen Sie Ihre Arbeit!', 2, 'radio-button', 1),
(6, 'Meg van elégedve a munkahelyével?', 'Wie zufrieden sind Sie mit Ihrem Arbeitsplatz?', NULL, 'radio-button', 0),
(7, 'Hogyan találta a jelenlegi állását?', 'Wie haben Sie Ihre Arbeitsstelle gefunden?', 1, 'radio-button', 0),
(8, 'Az álláskeresés ideje alatt az internetes keresés gyakorisága:', 'Wie oft haben Sie während der Suche nach einer Stelle im Internet gesucht?', 1, 'radio-button', 1),
(9, 'Kérem nevezze meg azokat az internetes oldalakat, ahol a legtöbb hasznos információt találta!', 'Auf welchen Internetseiten haben Sie die meisten nützlichen Informationen gefunden?', NULL, 'one-input', 0),
(10, 'Ajánlaná ezeket az oldalakat ismerőseinek is?', 'Würden Sie diese Internetseiten Ihren Bekannten empfehlen?', NULL, 'radio-button', 0),
(11, 'Elegendő információval rendelkezik a jelenlegi állásáról?', 'Haben Sie genug Informationen über Ihre Arbeit?', NULL, 'radio-button', 0),
(12, 'A következőkben néhány fontos információt sorolok fel, melyek fontosak lehetnek az egyének számára, mielőtt elvállalnak egy adott állást. Kérem jelölje be, hogy Ön számára mi a legfontosabb ezek közül egy új állás elfogadása előtt!', 'Unten folgen einige Punkte, die sehr wichtig sein können bevor jemand eine neue Arbeitsstelle annimmt. Welche Punkte sind für Sie wichtig?', 1, 'checkbox', 1),
(13, 'Milyen internetes oldalakon szokott állást keresni?', 'In welchen Internetseiten suchen Sie nach einer Stelle?', 1, 'checkbox', 0),
(14, 'Mennyire tartja az interneten szerzett információkat megbízhatónak?', 'Was denken Sie, sind die Informationen im Internet zuverlässig?', NULL, 'radio-button', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `megnev`
--

CREATE TABLE IF NOT EXISTS `megnev` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `szoveg` varchar(255) NOT NULL,
  `szoveg_d` varchar(255) NOT NULL,
  PRIMARY KEY (`azon`),
  UNIQUE KEY `szoveg` (`szoveg`,`szoveg_d`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

--
-- A tábla adatainak kiíratása `megnev`
--

INSERT INTO `megnev` (`azon`, `szoveg`, `szoveg_d`) VALUES
(6, '18-20', '18-20'),
(7, '21-25', '21-25'),
(8, '26-30', '26-30'),
(9, '30 felett', 'über 30'),
(42, 'Cégek saját honlapjai', 'Auf firmeneigenen Seiten'),
(48, 'Egyáltalán nem pontosak az információk', 'Gar nicht zuverlässig'),
(18, 'Egyáltalán nem vagyok vele megelégedve', 'Sehr unzufrieden'),
(1, 'Egyéb', 'Sonstiges'),
(28, 'Félévente', 'Halbjährlich'),
(4, 'Férfi', 'Männlich'),
(34, 'Fizetés', 'Das Gehalt'),
(13, 'Főváros', 'Hauptstadt'),
(43, 'Gyűjtőportálok, mint például:careerjet,jobrapido', 'Sammel-Portale, zum Beispiel:careerjet,jobrapido'),
(26, 'Havonta', 'Monatlich'),
(25, 'Hetente egyszer', 'Einmal in der Woche'),
(24, 'Hetente több alkalommal', 'Mehrmals in einer Woche'),
(2, 'Igen', 'Ja'),
(29, 'Igen, de szívesebben tudnék meg többet', 'Ja, aber ich würde gerne mehr Informationen haben'),
(15, 'Inkább meg vagyok elégedve', 'Meist zufrieden'),
(45, 'Inkább megbízható', 'Meist zuverlässig'),
(47, 'Inkább nem megbízható', 'Meist nicht zuverlässig'),
(17, 'Inkább nem vagyok megelégedve', 'Meist unzufrieden'),
(20, 'Interneten', 'habe diese Stelle im Internet gefunden'),
(41, 'Internetes speciális portálok', 'Spezielle  Internet-Portale'),
(22, 'Iskola által', 'Mit Hilfe der Schule'),
(21, 'Ismerős ajánlotta', 'Ein/e Bekannte/r hat sie mir vermittelt'),
(27, 'Kéthavonta', 'In jedem zweiten Monat'),
(10, 'Község', 'Gemeinde'),
(35, 'Lehetőség az előléptetésre', 'Die Möglichkeiten einer Beförderung'),
(12, 'Megyeszékhely', 'große Stadt'),
(31, 'Munka időtartama', 'Die Arbeitszeiten'),
(33, 'Munka jellege(fizikai, vagy szellemi munka)', 'Die Art der Arbeit ( physich oder geistig)'),
(32, 'Munkaidő kötöttsége', 'Die Flexibilität der Arbeitszeit'),
(40, 'Munkaközvetítők oldalai', 'Die Seiten der Arbeitsagenturen'),
(23, 'Naponta', 'Täglich'),
(3, 'Nem', 'Nein'),
(46, 'Nem mindig megbízható', 'Hält sich die Waage '),
(16, 'Nem vagyok elégedetlen, de nem is vagyok teljesen elégedett', 'Hält sich  die Waage'),
(30, 'Nem, de nem is szeretnék több információt', 'Nein, aber ich brauche nicht mehr Informationen'),
(5, 'Nő', 'Weiblich'),
(49, 'Soha', 'Nie'),
(36, 'Támogatások(pl.:utazás, étkezés)', 'Unterstützungen ( Reisen, Essen)'),
(37, 'Team összetétele(pl.:kor,nem,családi állapot)', 'Die Zusammensetzung des Teams ( Alter, Geschlecht, Beziehungstatus)'),
(14, 'Teljes mértékben', 'Sehr zufrieden'),
(44, 'Teljes mértékben megbízható', 'Immer zuverlässig'),
(38, 'Továbbképzések gyakorisága', 'Fortbildungen'),
(19, 'Újsághírdetésben láttam', 'Ich habe diese Stelle in einer Zeitungsanzeige gefunden'),
(11, 'Város', 'kleine Stadt');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `nyelv`
--

CREATE TABLE IF NOT EXISTS `nyelv` (
  `azon` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nev` varchar(100) NOT NULL,
  `oszlop` varchar(20) NOT NULL,
  PRIMARY KEY (`azon`),
  UNIQUE KEY `nev` (`nev`,`oszlop`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- A tábla adatainak kiíratása `nyelv`
--

INSERT INTO `nyelv` (`azon`, `nev`, `oszlop`) VALUES
(2, 'deutsch', 'szoveg_d'),
(1, 'magyar', 'szoveg');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `rendszeruzenet`
--

CREATE TABLE IF NOT EXISTS `rendszeruzenet` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `szoveg` varchar(255) NOT NULL,
  `szoveg_d` varchar(255) NOT NULL,
  PRIMARY KEY (`azon`),
  UNIQUE KEY `szoveg` (`szoveg`,`szoveg_d`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- A tábla adatainak kiíratása `rendszeruzenet`
--

INSERT INTO `rendszeruzenet` (`azon`, `szoveg`, `szoveg_d`) VALUES
(3, 'A kérdés sorszáma', 'Frage'),
(9, 'A megadott kérdőív nem létezik.', 'Der Fragebogen angegeben ist nicht vorhanden.'),
(6, 'Biztos, hogy újra akarja kezdeni?', 'Sind Sie sicher dass Sie es wieder ausfüllen wollen?'),
(1, 'Kérdőív', 'Der Fragebogen'),
(5, 'Köszönöm, hogy kitöltötte a kérdőívet!', 'Danke!'),
(2, 'Küldés', 'Schicken'),
(8, 'Nincs egy kitöltött kérdőív sem.', 'Es gibt einen Fragebogen entweder gefüllt.'),
(7, 'Üdv', 'Hallo'),
(4, 'Újra', 'Wieder');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `usr`
--

CREATE TABLE IF NOT EXISTS `usr` (
  `id` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `passwd` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `usr`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet: `valasz`
--

CREATE TABLE IF NOT EXISTS `valasz` (
  `azon` int(150) unsigned NOT NULL AUTO_INCREMENT,
  `kerdes` int(10) unsigned NOT NULL,
  `valasz` int(10) unsigned NOT NULL,
  PRIMARY KEY (`azon`),
  KEY `kerdes` (`kerdes`),
  KEY `valasz` (`valasz`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51 ;

--
-- A tábla adatainak kiíratása `valasz`
--

INSERT INTO `valasz` (`azon`, `kerdes`, `valasz`) VALUES
(1, 1, 4),
(2, 1, 5),
(3, 2, 6),
(4, 2, 7),
(5, 2, 8),
(6, 2, 9),
(7, 3, 10),
(8, 3, 11),
(9, 3, 12),
(10, 3, 13),
(11, 5, 3),
(12, 6, 14),
(13, 6, 15),
(14, 6, 16),
(15, 6, 17),
(16, 6, 18),
(17, 7, 19),
(18, 7, 20),
(19, 7, 21),
(20, 7, 22),
(21, 8, 23),
(22, 8, 24),
(23, 8, 25),
(24, 8, 26),
(25, 8, 27),
(26, 8, 28),
(27, 10, 2),
(28, 10, 3),
(29, 11, 2),
(30, 11, 29),
(31, 11, 30),
(32, 11, 3),
(33, 12, 31),
(34, 12, 32),
(35, 12, 33),
(36, 12, 34),
(37, 12, 35),
(38, 12, 36),
(39, 12, 37),
(40, 12, 38),
(41, 13, 40),
(42, 13, 41),
(43, 13, 42),
(44, 13, 43),
(45, 14, 44),
(46, 14, 45),
(47, 14, 46),
(48, 14, 47),
(49, 14, 48),
(50, 8, 49);

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `adatlap`
--
ALTER TABLE `adatlap`
  ADD CONSTRAINT `adatlap_ibfk_8` FOREIGN KEY (`valasz`) REFERENCES `valasz` (`azon`),
  ADD CONSTRAINT `adatlap_ibfk_6` FOREIGN KEY (`id`) REFERENCES `adatlap_info` (`id`),
  ADD CONSTRAINT `adatlap_ibfk_7` FOREIGN KEY (`kerdes`) REFERENCES `kerdes` (`azon`);

--
-- Megkötések a táblához `adatlap_info`
--
ALTER TABLE `adatlap_info`
  ADD CONSTRAINT `adatlap_info_ibfk_1` FOREIGN KEY (`nyelv`) REFERENCES `nyelv` (`azon`);

--
-- Megkötések a táblához `kapocs`
--
ALTER TABLE `kapocs`
  ADD CONSTRAINT `kapocs_ibfk_4` FOREIGN KEY (`korlatolt_kerdes`) REFERENCES `kerdes` (`azon`),
  ADD CONSTRAINT `kapocs_ibfk_2` FOREIGN KEY (`valasz`) REFERENCES `megnev` (`azon`),
  ADD CONSTRAINT `kapocs_ibfk_3` FOREIGN KEY (`kerdes`) REFERENCES `kerdes` (`azon`);

--
-- Megkötések a táblához `kerdes`
--
ALTER TABLE `kerdes`
  ADD CONSTRAINT `kerdes_ibfk_1` FOREIGN KEY (`extra_megnev`) REFERENCES `megnev` (`azon`);

--
-- Megkötések a táblához `valasz`
--
ALTER TABLE `valasz`
  ADD CONSTRAINT `valasz_ibfk_1` FOREIGN KEY (`kerdes`) REFERENCES `kerdes` (`azon`),
  ADD CONSTRAINT `valasz_ibfk_2` FOREIGN KEY (`valasz`) REFERENCES `megnev` (`azon`);
