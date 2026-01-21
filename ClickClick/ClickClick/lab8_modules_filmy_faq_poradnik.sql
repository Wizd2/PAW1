-- ClickClick — dodatkowe moduły CMS: Filmy / FAQ / Poradnik
-- Importuj do tej samej bazy, co reszta tabel (page_list, products, categories)

CREATE TABLE IF NOT EXISTS videos (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  youtube_url VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faq (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT(11) NOT NULL DEFAULT 0,
  status TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS guides (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- przykładowe wpisy (opcjonalnie)
INSERT INTO videos (title, youtube_url, status) VALUES
('Przykładowy film — switch sound test', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 0);

INSERT INTO faq (question, answer, sort_order, status) VALUES
('Czy wysyłacie za granicę?', 'Tak, po wcześniejszym kontakcie mailowym.', 1, 0);

INSERT INTO guides (title, body, status) VALUES
('Jak dobrać switche?', 'Krótka ściąga: Linear = płynnie, Tactile = wyczuwalny bump, Clicky = głośny klik.', 0);
