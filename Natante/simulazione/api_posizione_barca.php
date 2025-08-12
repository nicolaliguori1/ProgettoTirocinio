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
    $raggioTerra = 6371000; 
    $lat1 = deg2rad($lat1);
    $lat2 = deg2rad($lat2);
    $diffLat = $lat2 - $lat1;
    $diffLon = deg2rad($lon2 - $lon1);

    $a = sin($diffLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($diffLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $raggioTerra * $c; 
}

$res_live = pg_query_params($conn, "
    SELECT ts, lat, lon, id_rotta
    FROM boats_current_position 
    WHERE targa_barca = $1
", [$targa]);

$live = null;
if ($res_live && pg_num_rows($res_live) > 0) {
    $row = pg_fetch_assoc($res_live);

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
    usort($storico, fn($a, $b) => strtotime($a['ts']) <=> strtotime($b['ts']));
}

if (!$live) {
    echo json_encode([
        'error' => 'Nessuna posizione live o faro trovata per questa targa',
    ]);
    exit;
}

$res_faro_pos = pg_query_params($conn, "
    SELECT f.lat, f.lon
    FROM boats b
    JOIN fari f ON b.id_faro = f.id
    WHERE b.targa = $1
    LIMIT 1
", [$targa]);

$stato = 'Sconosciuto';
$soglia = 50; 

$lat_faro = null;
$lon_faro = null;

if ($res_faro_pos && pg_num_rows($res_faro_pos) > 0) {
    $pos_faro = pg_fetch_assoc($res_faro_pos);
    $lat_faro = isset($pos_faro['lat']) ? floatval($pos_faro['lat']) : null;
    $lon_faro = isset($pos_faro['lon']) ? floatval($pos_faro['lon']) : null;

    if ($lat_faro !== null && $lon_faro !== null) {
        if ($live['ts'] === null) {
            $stato = 'Nel porto';
        } else {
            $distanza = distanzaHaversine($live['lat'], $live['lon'], $lat_faro, $lon_faro);
            $stato = ($distanza <= $soglia) ? 'Nel porto' : 'Fuori dal porto';
        }
    }
}

$eventi = [];
if ($lat_faro !== null && $lon_faro !== null) {
    $res_storico_raw = pg_query_params($conn, "
        SELECT ts, lat, lon
        FROM (
            SELECT ts, lat, lon
            FROM boats_position
            WHERE targa_barca = $1
            ORDER BY ts DESC
            LIMIT 500
        ) s
        ORDER BY ts ASC
    ", [$targa]);

    $prevStato = null;
    if ($res_storico_raw && pg_num_rows($res_storico_raw) > 0) {
        while ($r = pg_fetch_assoc($res_storico_raw)) {
            if (!is_numeric($r['lat']) || !is_numeric($r['lon'])) continue;

            $latP = (float)$r['lat'];
            $lonP = (float)$r['lon'];

            $dist = distanzaHaversine($latP, $lonP, $lat_faro, $lon_faro);
            $statoP = ($dist <= $soglia) ? 'Nel porto' : 'Fuori dal porto';

            if ($prevStato !== null && $statoP !== $prevStato) {
                $tipo = ($statoP === 'Nel porto') ? 'Entrata' : 'Uscita';
                $eventi[] = [
                    'ts'   => $r['ts'],
                    'tipo' => $tipo
                ];
            }
            $prevStato = $statoP;
        }

        usort($eventi, fn($a, $b) => strtotime($b['ts']) <=> strtotime($a['ts']));
        $eventi = array_slice($eventi, 0, 10);
        usort($eventi, fn($a, $b) => strtotime($a['ts']) <=> strtotime($b['ts']));
    }
}

echo json_encode([
    'live'    => $live,
    'storico' => $storico, 
    'stato'   => $stato,
    'eventi'  => $eventi,  
]);
exit;
