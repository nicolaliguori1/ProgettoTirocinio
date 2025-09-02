<?php
require __DIR__ . '/../connessione.php';
require __DIR__ . '/functions_faro.php';

header('Content-Type: application/json');


$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    echo json_encode(['trovato' => false]);
    exit;
}

$faroData = getFaroData($conn, (int)$id);
if (!$faroData) {
    echo json_encode(['trovato' => false]);
    exit;
}


$stato = calcolaStatoFaro($faroData, 60);


echo json_encode([
    'trovato' => true,
    'lat' => $faroData['lat'],
    'lon' => $faroData['lon'],
    'stato' => $stato,
    'ts' => $faroData['ts'] ?? null
]);
