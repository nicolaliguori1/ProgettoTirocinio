
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

-- Simulazione realistica
INSERT INTO fari (nome, lat, lon) VALUES
('Faro di Capo d’Orso', 40.6333, 14.6833),
('Faro di Punta Licosa', 40.2500, 14.9000),
('Faro del Porto di Salerno (Molo Manfredi)', 40.6745, 14.7519);

-- Percorso dal Faro di Capo d’Orso (Costiera Amalfitana)
-- Capo d'Orso: 40.6333, 14.6833
(40.6333, 14.6833),
(40.6280, 14.7000),
(40.6200, 14.7150),
(40.6100, 14.7200), -- Verso Amalfi
(40.6150, 14.7050),
(40.6220, 14.6900),
(40.6300, 14.6800) -- Ritorno

-- Percorso dal Faro di Punta Licosa (Castellabate)
-- Punta Licosa: 40.2500, 14.9000
(40.2500, 14.9000),
(40.2480, 14.9100),
(40.2450, 14.9200),
(40.2400, 14.9250), -- al largo
(40.2420, 14.9150),
(40.2460, 14.9050),
(40.2500, 14.9000) -- Ritorno

--  Percorso dal Faro del Porto di Salerno (Molo Manfredi)
-- Molo Manfredi: 40.6745, 14.7519
(40.6745, 14.7519),
(40.6720, 14.7600),
(40.6650, 14.7700),
(40.6600, 14.7600), -- Verso Vietri
(40.6630, 14.7450),
(40.6680, 14.7400),
(40.6745, 14.7519) -- Ritorno


-- barche
INSERT INTO boats (targa, lunghezza, nome, id_user, id_faro) VALUES
('SA1234', 12, 'Aurora', NULL, 1),  -- Faro di Capo d’Orso
('SA5678', 9, 'Stella Marina', NULL, 2),  -- Faro di Punta Licosa
('SA9012', 15, 'Onda Libera', NULL, 3);  -- Faro del Porto di Salerno
