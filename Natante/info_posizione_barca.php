<?php
require __DIR__ . '/../connessione.php';
require __DIR__ . '/functions_boat.php';

header('Content-Type: application/json');

$targa = trim($_GET['targa'] ?? '');
if ($targa === '') {
    echo json_encode(['error' => 'Targa non specificata']);
    exit;
}

// Recupera posizione live
$live = getLivePosition($conn, $targa);
if (!$live) {
    echo json_encode(['error' => 'Nessuna posizione live o faro trovata per questa targa']);
    exit;
}

// Recupera storico (ultimi 10 punti)
$storico = getBoatHistory($conn, $targa);


// Recupera posizione faro
$faro = getFaroPosition($conn, $targa);

$soglia = 50; // metri
$stato = 'Sconosciuto';

// Determina stato live rispetto al faro
if ($faro) {
    if ($live['ts'] === null) {
        $stato = 'Nel porto';
    } else {
        $distanza = distanzaHaversine($live['lat'], $live['lon'], $faro['lat'], $faro['lon']);
        $stato = ($distanza <= $soglia) ? 'Nel porto' : 'Fuori dal porto';
    }
}

echo json_encode([
    'live' => $live,
    'storico' => $storico,
    'stato' => $stato,
    'faro' => $faro
]);
exit;
