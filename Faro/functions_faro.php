<?php
/**
 * Recupera l'ultima posizione del faro.
 * 1. Cerca in fari_position
 * 2. Se non trova nulla, prende lat/lon da fari
 */
function getFaroData($conn, int $id): ?array {
    // Ultima posizione disponibile
    $sql = "SELECT lat, lon, ts
            FROM fari_position
            WHERE id_faro = $1
            ORDER BY ts DESC
            LIMIT 1";
    $res = pg_query_params($conn, $sql, [$id]);

    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        return [
            'lat'   => $row['lat'],
            'lon'   => $row['lon'],
            'ts'    => $row['ts'],
            'stato' => null // lo calcoliamo con calcolaStatoFaro()
        ];
    }

    // Recupero coordinate in fari
    $sql = "SELECT lat, lon FROM fari WHERE id = $1";
    $res = pg_query_params($conn, $sql, [$id]);

    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        return [
            'lat'   => $row['lat'],
            'lon'   => $row['lon'],
            'ts'    => null,
            'stato' => 'inattivo'
        ];
    }

    // Faro inesistente
    return null;
}

/**
 * Recupera i dati anagrafici del faro
 */
function getFaroById($conn, int $id): ?array {
    $sql = "SELECT * FROM fari WHERE id = $1";
    $res = pg_query_params($conn, $sql, [$id]);
    if ($res && pg_num_rows($res) > 0) {
        return pg_fetch_assoc($res);
    }
    return null;
}

/**
 * Determina lo stato del faro:
 * - Attivo se ultimo dato ricevuto negli ultimi $timeout secondi
 * - Inattivo altrimenti
 */
function calcolaStatoFaro(?array $faroData, int $timeout): string {
    if (!$faroData || empty($faroData['ts'])) {
        return 'inattivo';
    }

    $ts = strtotime($faroData['ts']);
    if ($ts && (time() - $ts) <= $timeout) {
        return 'attivo';
    }

    return 'inattivo';
}
