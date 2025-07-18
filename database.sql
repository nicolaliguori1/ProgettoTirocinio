
-- Abilita l'estensione per l'hashing delle password
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Creazione delle tabelle
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  nome_utente VARCHAR(20) NOT NULL,
  pw VARCHAR(100) NOT NULL,
  email VARCHAR(50) NOT NULL
);

CREATE TABLE boats_position (
  ts TIMESTAMP DEFAULT NOW(),
  targa_barca VARCHAR(20),
  lat DOUBLE PRECISION NOT NULL,
  lon DOUBLE PRECISION NOT NULL,
  PRIMARY KEY (ts, targa_barca),
  FOREIGN KEY (targa_barca) REFERENCES boats(targa)
    ON DELETE SET NULL 
    ON UPDATE CASCADE
);


CREATE TABLE fari (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(20) NOT NULL,
  lat DOUBLE PRECISION NOT NULL,
  lon DOUBLE PRECISION NOT NULL
);

CREATE TABLE boats (
  targa VARCHAR(20) PRIMARY KEY,
  lunghezza INT NOT NULL,
  nome VARCHAR(20) NOT NULL,
  id_user INT,
  id_faro INT,
  FOREIGN KEY (id_user) REFERENCES users(id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE
  FOREIGN KEY (id_faro) REFERENCES fari(id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE
);