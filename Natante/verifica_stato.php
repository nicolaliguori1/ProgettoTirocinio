<?php
require __DIR__ . '/../connessione.php';

header('Content-Type: application/json');

$targa = $_GET['targa'] ?? '';
$targa = trim($targa);

if ($targa === '') {
    echo json_encode(['errore' => 'Targa mancante']);
    exit;
}

function haversine($lat1, $lon1, $lat2, $lon2) {
    $raggioTerra = 6371000;
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;
    $a = sin($dlat/2)**2 + cos($lat1) * cos($lat2) * sin($dlon/2)**2;
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $raggioTerra * $c;
}

// Ottieni posizione live
$res_live = pg_query_params($conn, "SELECT lat, lon FROM boats_current_position WHERE targa_barca = $1", [$targa]);
if (!$res_live || pg_num_rows($res_live) === 0) {
    echo json_encode(['errore' => 'Posizione non trovata']);
    exit;
}
$row_live = pg_fetch_assoc($res_live);
$lat = floatval($row_live['lat']);
$lon = floatval($row_live['lon']);

// Ottieni faro associato
$res_faro = pg_query_params($conn, "SELECT f.lat, f.lon FROM boats b JOIN fari f ON b.id_faro = f.id WHERE b.targa = $1", [$targa]);
if (!$res_faro || pg_num_rows($res_faro) === 0) {
    echo json_encode(['errore' => 'Faro non trovato']);
    exit;
}
$row_faro = pg_fetch_assoc($res_faro);
$lat_faro = floatval($row_faro['lat']);
$lon_faro = floatval($row_faro['lon']);

$distanza = haversine($lat, $lon, $lat_faro, $lon_faro);
$stato_attuale = ($distanza <= 500) ? 'dentro' : 'fuori';

// Verifica ultimo stato salvato
$res_stato = pg_query_params($conn,
    "SELECT stato FROM barca_presenza WHERE targa = $1 ORDER BY ts DESC LIMIT 1",
    [$targa]
);
$ultimo_stato = pg_fetch_assoc($res_stato)['stato'] ?? null;

if ($ultimo_stato !== $stato_attuale) {
    pg_query_params($conn,
        "INSERT INTO barca_presenza (targa, stato, ts) VALUES ($1, $2, NOW())",
        [$targa, $stato_attuale]
    );
}

echo json_encode([
    'stato' => $stato_attuale === 'dentro' ? 'Dentro il porto' : 'Fuori dal porto',
    'distanza_m' => round($distanza)
]);
?>
