
-- Abilita l'estensione per l'hashing delle password
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Creazione delle tabelle
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  nome_utente VARCHAR(100) NOT NULL,
  pw VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL
);

CREATE TABLE fari (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  lat DOUBLE PRECISION NOT NULL,
  lon DOUBLE PRECISION NOT NULL
);

CREATE TABLE fari_position (
  id_faro INT NOT NULL,
  lat DOUBLE PRECISION NOT NULL,
  lon DOUBLE PRECISION NOT NULL,
  ts TIMESTAMP NOT NULL DEFAULT NOW(),
  FOREIGN KEY (id_faro) REFERENCES fari(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  PRIMARY KEY (id_faro, ts)
);


CREATE TABLE boats (
  targa VARCHAR(100) PRIMARY KEY,
  lunghezza INT NOT NULL,
  nome VARCHAR(100) NOT NULL,
  id_user INT,
  id_faro INT,
  FOREIGN KEY (id_user) REFERENCES users(id)
    ON DELETE SET NULL 
    ON UPDATE CASCADE,
  FOREIGN KEY (id_faro) REFERENCES fari(id)
    ON DELETE SET NULL 
    ON UPDATE CASCADE
);

CREATE TABLE boats_position (
  ts TIMESTAMP DEFAULT NOW(),
  targa_barca VARCHAR(100) NOT NULL,
  lat DOUBLE PRECISION NOT NULL,
  lon DOUBLE PRECISION NOT NULL,
  PRIMARY KEY (ts, targa_barca),
  FOREIGN KEY (targa_barca) REFERENCES boats(targa)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

CREATE TABLE boats_current_position (
  targa_barca VARCHAR(100) PRIMARY KEY,
  lat DOUBLE PRECISION NOT NULL,
  lon DOUBLE PRECISION NOT NULL,
  ts TIMESTAMP NOT NULL,
  FOREIGN KEY (targa_barca) REFERENCES boats(targa)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);


-- Simulazione realistica
INSERT INTO fari (nome, lat, lon) VALUES
('Faro di Capo d’Orso', 40.6333, 14.6833),
('Faro di Punta Licosa', 40.2500, 14.9000),
('Faro del Porto di Salerno (Molo Manfredi)', 40.6745, 14.7519);


-- barche
INSERT INTO boats (targa, lunghezza, nome, id_user, id_faro) VALUES
('SA1234', 12, 'Aurora', NULL, 1),  -- Faro di Capo d’Orso
('SA5678', 9, 'Stella Marina', NULL, 2),  -- Faro di Punta Licosa
('SA9012', 15, 'Onda Libera', NULL, 3);  -- Faro del Porto di Salerno

-- prova