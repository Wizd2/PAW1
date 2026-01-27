-- ================================
-- ClickClick — (Sklep cz.1)
-- Tabela categories + przykladowe dane
-- Wersja poprawiona: bez bledu #1093
-- (MySQL nie pozwala na INSERT z podzapytaniem SELECT z tej samej tabeli)
-- Importuj w phpMyAdmin: Import -> wybierz plik -> Go
-- ================================

CREATE DATABASE IF NOT EXISTS `moja_strona` CHARACTER SET utf8 COLLATE utf8_polish_ci;
USE `moja_strona`;

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
 `id` INT NOT NULL AUTO_INCREMENT,
 `parent_id` INT NOT NULL DEFAULT 0,
 `name` VARCHAR(120) NOT NULL,
 PRIMARY KEY (`id`),
 INDEX (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Wkladamy kategorie z ustalonymi ID, zeby latwo dodac podkategorie
INSERT INTO `categories` (`id`, `parent_id`, `name`) VALUES
(1, 0, 'Switche'),
(2, 0, 'Keycapy'),
(3, 0, 'Kable'),
(4, 0, 'Stabilizatory'),
(5, 0, 'Akcesoria');

-- Podkategorie: Switche (parent_id = 1)
INSERT INTO `categories` (`parent_id`, `name`) VALUES
(1, 'Linear'),
(1, 'Tactile'),
(1, 'Clicky'),
(1, 'Silent');

-- Podkategorie: Keycapy (parent_id = 2)
INSERT INTO `categories` (`parent_id`, `name`) VALUES
(2, 'PBT'),
(2, 'ABS'),
(2, 'Artisan');

-- Podkategorie: Kable (parent_id = 3)
INSERT INTO `categories` (`parent_id`, `name`) VALUES
(3, 'USB-C'),
(3, 'Coiled'),
(3, 'Aviator');

-- Podkategorie: Akcesoria (parent_id = 5)
INSERT INTO `categories` (`parent_id`, `name`) VALUES
(5, 'Switch puller'),
(5, 'Keycap puller'),
(5, 'Lube / smar');

-- Ustawiamy AUTO_INCREMENT na cos wiekszego, zeby kolejne rekordy nie kolidowaly z ID=1..5
ALTER TABLE `categories` AUTO_INCREMENT=100;
