<?php
require __DIR__ . '/../../connessione.php';

header('Content-Type: application/json');

$targa = $_GET['targa'] ?? '';
$targa = pg_escape_string($conn, $targa);

if (!$targa) {
    echo json_encode(['error' => 'Targa mancante']);
    exit;
}

// Posizione live
$res_live = pg_query($conn, "
    SELECT ts, lat, lon
    FROM boats_position
    WHERE targa_barca = '$targa'
    ORDER BY ts DESC
    LIMIT 1
");
$live = pg_fetch_assoc($res_live);

// Storico ultimi 10
$res_storico = pg_query($conn, "
    SELECT ts, lat, lon
    FROM boats_position
    WHERE targa_barca = '$targa'
    ORDER BY ts DESC
    LIMIT 10
");

$storico = [];
while ($row = pg_fetch_assoc($res_storico)) {
    $storico[] = $row;
}

echo json_encode([
    'live' => $live,
    'storico' => $storico,
]);
marcogay