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

$live = false;
if ($res_live) {
    $debug['live_rows'] = pg_num_rows($res_live);
    if ($debug['live_rows'] > 0) {
        $live = pg_fetch_assoc($res_live);
    }
}

// FALLBACK FARO
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
            $live = [
                'ts' => null,
                'lat' => floatval($faro['lat']),
                'lon' => floatval($faro['lon']),
                'id_rotta' => 0
            ];
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
        if (is_numeric($row['lat']) && is_numeric($row['lon'])) {
            $storico[] = [
                'ts' => $row['ts'],
                'lat' => floatval($row['lat']),
                'lon' => floatval($row['lon'])
            ];
        }
    }
}

echo json_encode([
    'live' => $live,
    'storico' => $storico,
    'debug' => $debug
]);
exit;
