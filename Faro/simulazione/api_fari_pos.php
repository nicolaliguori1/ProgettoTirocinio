<?php
require __DIR__ . '/../../connessione.php';
header('Content-Type: application/json');
$id = $_GET['id'] ?? '';

if (!ctype_digit($id)) {
    http_response_code(400);
    echo json_encode(["error" => "ID faro non valido"]);
    exit;
}

$id = (int)$id;

// Prende ultima posizione
$query_last_pos = "
    SELECT lat, lon, ts
    FROM fari_position
    WHERE id_faro = $id
    ORDER BY ts DESC
    LIMIT 1
";
$res_last_pos = pg_query($conn, $query_last_pos);
$last_pos = pg_fetch_assoc($res_last_pos);

if (!$last_pos) {
    // Nessun log → restituisco coordinate iniziali
    $query_faro = "SELECT lat, lon FROM fari WHERE id = $id";
    $res_faro = pg_query($conn, $query_faro);
    $faro = pg_fetch_assoc($res_faro);

    $data = [
        "lat" => $faro['lat'] ?? null,
        "lon" => $faro['lon'] ?? null,
        "stato" => "inattivo"
    ];
} else {
    $now = time();
    $last_ts = strtotime($last_pos['ts']);
    $stato = ($now - $last_ts <= 60) ? "attivo" : "inattivo";

    $data = [
        "lat" => $last_pos['lat'],
        "lon" => $last_pos['lon'],
        "stato" => $stato,
        "ts" => $last_pos['ts']
    ];
}

echo json_encode($data);
