<?php
$status_file = __DIR__ . '/sim_status.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'] === 'on' ? 'on' : 'off';

    // Scrive lo stato nel file
    if (file_put_contents($status_file, $status) !== false) {
        // Imposta i permessi del file dopo aver scritto
        chmod($status_file, 0666);
        echo json_encode(['success' => true, 'status' => $status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore nella scrittura del file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parametro mancante']);
}
marcogay

