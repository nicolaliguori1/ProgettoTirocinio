<?php
require __DIR__ . '/../../connessione.php';

header('Content-Type: application/json');

$targa = $_GET['targa'] ?? '';
$targa = trim($targa);

if ($targa === '') {
    echo json_encode(['error' => 'Targa non specificata']);
    exit;
}

$debug = [
    'targa' => $targa,
    'live_rows' => 0,
    'faro_query_ok' => false,
    'faro_rows' => -1,
    'faro_query_error' => null
];

// LIVE
$res_live = pg_query_params($conn, "
    SELECT ts, lat, lon, id_rotta
    FROM boats_current_position 
    WHERE targa_barca = $1
", [$targa]);

$live = null;
if ($res_live) {
    $debug['live_rows'] = pg_num_rows($res_live);
    if ($debug['live_rows'] > 0) {
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

    if ($res_faro) {
        $debug['faro_query_ok'] = true;
        $debug['faro_rows'] = pg_num_rows($res_faro);

        if ($debug['faro_rows'] > 0) {
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
    } else {
        $debug['faro_query_error'] = pg_last_error($conn);
    }
}

// STORICO
$res_storico = pg_query_params($conn, "
    SELECT ts, lat, lon 
    FROM boats_position 
    WHERE targa_barca = $1
    ORDER BY ts ASC
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
}

// Se live ancora null, ritorna errore più chiaro
if (!$live) {
    echo json_encode([
        'error' => 'Nessuna posizione live o faro trovata per questa targa',
        'debug' => $debug
    ]);
    exit;
}

echo json_encode([
    'live' => $live,
    'storico' => $storico,
    'debug' => $debug
]);
exit;


