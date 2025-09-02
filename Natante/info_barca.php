<?php
require __DIR__ . '/../connessione.php';
require __DIR__ . '/functions_boat.php';

header('Content-Type: application/json');

$targa = trim($_GET['targa'] ?? '');
if ($targa === '') {
    echo json_encode(['error' => 'Targa non specificata']);
    exit;
}

$nome = getBoatName($conn, $targa);
if (!$nome) {
    echo json_encode(['error' => 'Barca non trovata']);
    exit;
}

// Recupera dati
$live = getLivePosition($conn, $targa);
$storico = getBoatHistory($conn, $targa);
$faro = getFaroPosition($conn, $targa);

// Determina stato
$soglia = 50; 
$stato = 'Sconosciuto';
if ($faro) {
    if (!$live || $live['ts'] === null) {
        $stato = 'Nel porto';
    } else {
        $distanza = distanzaHaversine($live['lat'], $live['lon'], $faro['lat'], $faro['lon']);
        $stato = ($distanza <= $soglia) ? 'Nel porto' : 'Fuori dal porto';
    }
}

echo json_encode([
    'trovata' => true,
    'nome' => $nome,
    'live' => $live,
    'storico' => $storico,
    'stato' => $stato,
    'faro' => $faro
]);
exit;
