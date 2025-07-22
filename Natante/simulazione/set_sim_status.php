<?php
$status_file = __DIR__ . '/sim_status.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'] === 'on' ? 'on' : 'off';
    file_put_contents($status_file, $status);
    echo json_encode(['success' => true, 'status' => $status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Parametro mancante']);
}
