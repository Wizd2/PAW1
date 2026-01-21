-- ==========================================
-- ClickClick — Lab11
-- Produkty sklepu (tabela products) + przykladowe dane
--
-- Co robimy:
-- 1) tworzymy tabele products
-- 2) dodajemy kilka przykladowych produktow
--
-- Wazne: cena brutto NIE jest w bazie.
-- Brutto liczymy w PHP na podstawie netto + VAT.
-- ==========================================

USE `moja_strona`;

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  `expires_at` DATETIME NULL DEFAULT NULL,
  `price_netto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `vat` INT NOT NULL DEFAULT 23,
  `stock_qty` INT NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 1,
  `category_id` INT NOT NULL,
  `size` VARCHAR(50) NULL DEFAULT NULL,
  `image_url` VARCHAR(255) NULL DEFAULT NULL,
  `sound_file` VARCHAR(255) NULL DEFAULT NULL,
  `spec_1` VARCHAR(255) NULL DEFAULT NULL,
  `spec_2` VARCHAR(255) NULL DEFAULT NULL,
  `spec_3` VARCHAR(255) NULL DEFAULT NULL,
  `spec_4` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pobieramy ID glownych kategorii po nazwie (z Lab10)
SET @cat_switche := (SELECT id FROM categories WHERE name='Switche' LIMIT 1);
SET @cat_keycapy := (SELECT id FROM categories WHERE name='Keycapy' LIMIT 1);
SET @cat_kable   := (SELECT id FROM categories WHERE name='Kable'   LIMIT 1);
SET @cat_acc     := (SELECT id FROM categories WHERE name='Akcesoria' LIMIT 1);

-- ------------------------------
-- Switche (4 duze karty)
-- ------------------------------
INSERT INTO `products`
(`title`,`description`,`price_netto`,`vat`,`stock_qty`,`status`,`category_id`,`size`,`image_url`,`sound_file`,`spec_1`,`spec_2`,`spec_3`,`spec_4`)
VALUES
('Holy Panda', 'Tactile switch — głęboki dźwięk i wyraźny bump.', 3.25, 23, 120, 1, @cat_switche, '1 szt.', 'img/switch1.jpg', 'audio/holy_panda.wav',
 'Typ: <b>Tactile</b>', 'Actuation: <b>2.0 mm</b>', 'Travel: <b>4.0 mm</b>', 'Force: <b>67g</b>'),

('Gateron Red', 'Linear switch — lekki i gładki, dobry do szybkiego pisania.', 2.10, 23, 200, 1, @cat_switche, '1 szt.', 'img/switch2.jpg', 'audio/linear_red.wav',
 'Typ: <b>Linear</b>', 'Actuation: <b>2.0 mm</b>', 'Travel: <b>4.0 mm</b>', 'Force: <b>45g</b>'),

('Kailh Box White', 'Clicky switch — wyraźny klik, retro feeling.', 2.60, 23, 80, 1, @cat_switche, '1 szt.', 'img/switch1.jpg', 'audio/clicky_blue.wav',
 'Typ: <b>Clicky</b>', 'Actuation: <b>1.8 mm</b>', 'Travel: <b>3.6 mm</b>', 'Force: <b>55g</b>'),

('Silent Alpaca', 'Silent linear — ciszej, ale nadal płynnie.', 3.05, 23, 0, 1, @cat_switche, '1 szt.', 'img/switch2.jpg', 'audio/silent.wav',
 'Typ: <b>Silent linear</b>', 'Actuation: <b>2.0 mm</b>', 'Travel: <b>4.0 mm</b>', 'Force: <b>62g</b>');

-- ------------------------------
-- Keycapy
-- ------------------------------
INSERT INTO `products`
(`title`,`description`,`price_netto`,`vat`,`stock_qty`,`status`,`category_id`,`size`,`image_url`,`sound_file`,`spec_1`,`spec_2`,`spec_3`,`spec_4`)
VALUES
('PBT Keycaps — Sakura', 'Zestaw keycapów PBT, profil Cherry, świetny do codziennego użytku.', 169.00, 23, 15, 1, @cat_keycapy, '1 zestaw', 'img/keycaps1.jpg', NULL,
 'Materiał: <b>PBT</b>', 'Profil: <b>Cherry</b>', 'Legenda: <b>Dye-sub</b>', 'Układ: <b>ANSI</b>'),

('ABS Keycaps — Neon', 'ABS z mocnym kolorem, lekko „clacky” brzmienie.', 129.00, 23, 6, 1, @cat_keycapy, '1 zestaw', 'img/keycaps2.jpg', NULL,
 'Materiał: <b>ABS</b>', 'Profil: <b>OEM</b>', 'Legenda: <b>Double-shot</b>', 'Układ: <b>ANSI</b>');

-- ------------------------------
-- Kable
-- ------------------------------
INSERT INTO `products`
(`title`,`description`,`price_netto`,`vat`,`stock_qty`,`status`,`category_id`,`size`,`image_url`,`sound_file`,`spec_1`,`spec_2`,`spec_3`,`spec_4`)
VALUES
('Coiled Cable USB-C — Purple', 'Zwijany kabel USB-C, pasuje do stylu „dark neon”.', 79.00, 23, 20, 1, @cat_kable, '1 szt.', 'img/cable1.jpg', NULL,
 'Złącze: <b>USB-C</b>', 'Długość: <b>1.8m</b>', 'Styl: <b>Coiled</b>', 'Kolor: <b>Purple</b>'),

('Aviator Cable — Green', 'Kabel z aviator, łatwe odpinanie, fajny wygląd.', 99.00, 23, 10, 1, @cat_kable, '1 szt.', 'img/cable2.jpg', NULL,
 'Złącze: <b>USB-C</b>', 'Adapter: <b>Aviator</b>', 'Oplot: <b>Paracord</b>', 'Kolor: <b>Green</b>');

-- ------------------------------
-- Akcesoria (przyklad)
-- ------------------------------
INSERT INTO `products`
(`title`,`description`,`price_netto`,`vat`,`stock_qty`,`status`,`category_id`,`size`,`image_url`,`sound_file`,`spec_1`,`spec_2`,`spec_3`,`spec_4`)
VALUES
('Switch Lube Kit', 'Zestaw do smarowania switchy: pędzelek + lube (demo).', 39.00, 23, 25, 1, @cat_acc, '1 zestaw', 'img/tool1.jpg', NULL,
 'Zastosowanie: <b>Switche</b>', 'Typ: <b>Kit</b>', 'Poziom: <b>DIY</b>', 'Info: <b>Demo</b>');
