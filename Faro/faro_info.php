<?php
require __DIR__ . '/../connessione.php';
require __DIR__ . '/functions_faro.php';

header('Content-Type: application/json');

// Controllo ID faro
$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    echo json_encode(['trovato' => false]);
    exit;
}

// Recupera dati completi del faro
$faroData = getFaroData($conn, (int)$id);

if (!$faroData) {
    echo json_encode(['trovato' => false]);
    exit;
}

echo json_encode([
    'trovato' => true,
    'lat' => $faroData['lat'],
    'lon' => $faroData['lon'],
    'stato' => $faroData['stato'],
    'ts' => $faroData['ts'] ?? null
]);
?>
