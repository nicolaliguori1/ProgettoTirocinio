<?php
require __DIR__ . '/../../connessione.php';

header('Content-Type: application/json');

$targa = $_GET['targa'] ?? '';
$targa = trim($targa);

if ($targa === '') {
    echo json_encode(['error' => 'Targa non specificata']);
    exit;
}

// Funzione per calcolare la distanza tra due coordinate GPS (in metri)
function distanzaHaversine($lat1, $lon1, $lat2, $lon2) {
    $raggioTerra = 6371000; // metri
    $lat1 = deg2rad($lat1);
    $lat2 = deg2rad($lat2);
    $diffLat = $lat2 - $lat1;
    $diffLon = deg2rad($lon2 - $lon1);

    $a = sin($diffLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($diffLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $raggioTerra * $c; // distanza in metri
}

// LIVE
$res_live = pg_query_params($conn, "
    SELECT ts, lat, lon, id_rotta
    FROM boats_current_position 
    WHERE targa_barca = $1
", [$targa]);

$live = null;
if ($res_live) {
    if (pg_num_rows($res_live) > 0) {
        $row = pg_fetch_assoc($res_live);

        // Controllo validità dati numerici
        $lat = isset($row['lat']) && is_numeric($row['lat']) ? floatval($row['lat']) : null;
        $lon = isset($row['lon']) && is_numeric($row['lon']) ? floatval($row['lon']) : null;
        $id_rotta = isset($row['id_rotta']) && is_numeric($row['id_rotta']) ? (int)$row['id_rotta'] : null;
        $ts = $row['ts'] ?? null;

        if ($lat !== null && $lon !== null) {
            $live = [
                'ts' => $ts,
                'lat' => $lat,
                'lon' => $lon,
                'id_rotta' => $id_rotta !== null ? $id_rotta : 0
            ];
        }
    }
}

// FALLBACK FARO se live non valida o assente
if (!$live) {
    $res_faro = pg_query_params($conn, "
        SELECT f.lat, f.lon
        FROM boats b
        JOIN fari f ON b.id_faro = f.id
        WHERE b.targa = $1
        LIMIT 1
    ", [$targa]);

    if ($res_faro && pg_num_rows($res_faro) > 0) {
        $faro = pg_fetch_assoc($res_faro);
        $lat = isset($faro['lat']) && is_numeric($faro['lat']) ? floatval($faro['lat']) : null;
        $lon = isset($faro['lon']) && is_numeric($faro['lon']) ? floatval($faro['lon']) : null;

        if ($lat !== null && $lon !== null) {
            $live = [
                'ts' => null,
                'lat' => $lat,
                'lon' => $lon,
                'id_rotta' => 0
            ];
        }
    }
}

// STORICO (ultime 10 posizioni, ordinate dal più vecchio al più recente)
$res_storico = pg_query_params($conn, "
    SELECT ts, lat, lon 
    FROM boats_position 
    WHERE targa_barca = $1
    ORDER BY ts DESC
    LIMIT 10
", [$targa]);

$storico = [];
if ($res_storico) {
    while ($row = pg_fetch_assoc($res_storico)) {
        if (isset($row['lat'], $row['lon']) && is_numeric($row['lat']) && is_numeric($row['lon'])) {
            $storico[] = [
                'ts' => $row['ts'],
                'lat' => floatval($row['lat']),
                'lon' => floatval($row['lon'])
            ];
        }
    }

    // Riordina per timestamp crescente (dal più vecchio al più recente)
    usort($storico, function ($a, $b) {
        return strtotime($a['ts']) <=> strtotime($b['ts']);
    });
}


// Se live ancora null, ritorna errore più chiaro
if (!$live) {
    echo json_encode([
        'error' => 'Nessuna posizione live o faro trovata per questa targa',
    ]);
    exit;
}

// Calcolo stato (nel porto/fuori) basato sulla distanza dalla posizione del faro
// Se live è fallback faro, è sicuramente 'Nel porto', altrimenti calcoliamo la distanza

// Prendo posizione faro
$res_faro_pos = pg_query_params($conn, "
    SELECT f.lat, f.lon
    FROM boats b
    JOIN fari f ON b.id_faro = f.id
    WHERE b.targa = $1
    LIMIT 1
", [$targa]);

$stato = 'Sconosciuto';
$soglia = 50; // metri

if ($res_faro_pos && pg_num_rows($res_faro_pos) > 0) {
    $pos_faro = pg_fetch_assoc($res_faro_pos);
    $lat_faro = floatval($pos_faro['lat']);
    $lon_faro = floatval($pos_faro['lon']);

    if ($live['ts'] === null) {
        // live è fallback faro: quindi consideriamo nel porto
        $stato = 'Nel porto';
    } else {
        $distanza = distanzaHaversine($live['lat'], $live['lon'], $lat_faro, $lon_faro);
        $stato = ($distanza <= $soglia) ? 'Nel porto' : 'Fuori dal porto';
    }
}

echo json_encode([
    'live' => $live,
    'storico' => $storico,
    'stato' => $stato,
]);
exit;
