<?php
require __DIR__ . '/../connessione.php';

header('Content-Type: application/json');

$targa = $_GET['targa'] ?? '';
$targa = trim($targa);

if ($targa === '') {
    echo json_encode(['trovata' => false, 'errore' => 'Targa vuota']);
    exit;
}

$query = "SELECT nome FROM boats WHERE targa = $1 LIMIT 1";
$result = pg_query_params($conn, $query, [$targa]);

if (!$result) {
    echo json_encode(['trovata' => false, 'errore' => 'Errore query']);
    exit;
}

if (pg_num_rows($result) === 0) {
    echo json_encode(['trovata' => false]);
    exit;
}

$row = pg_fetch_assoc($result);
$nome = $row['nome'];

$res_live = pg_query_params($conn, "SELECT ts, lat, lon, id_rotta FROM boats_current_position WHERE targa_barca = $1", [$targa]);

$live = false;
if ($res_live && pg_num_rows($res_live) > 0) {
    $row_live = pg_fetch_assoc($res_live);
    if (
        isset($row_live['lat'], $row_live['lon']) &&
        is_numeric($row_live['lat']) &&
        is_numeric($row_live['lon'])
    ) {
        $live = $row_live;
    }
}

if (!$live) {
    $query_faro = "SELECT f.lat, f.lon
                   FROM boats b
                   JOIN fari f ON b.id_faro = f.id_faro
                   WHERE b.targa = $1";
    $res_faro = pg_query_params($conn, $query_faro, [$targa]);
    if ($res_faro && pg_num_rows($res_faro) > 0) {
        $row_faro = pg_fetch_assoc($res_faro);
        $live = [
            'ts' => null,
            'lat' => floatval($row_faro['lat']),
            'lon' => floatval($row_faro['lon']),
            'id_rotta' => 0
        ];
    } else {
        $live = [
            'ts' => null,
            'lat' => null,
            'lon' => null,
            'id_rotta' => 0
        ];
    }
}

echo json_encode([
    'trovata' => true,
    'nome' => $nome,
    'live' => $live
]);
exit;


