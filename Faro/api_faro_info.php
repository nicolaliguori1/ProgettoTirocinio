<?php
require __DIR__ . '/../connessione.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['trovato' => false]);
    exit;
}

$id = $_GET['id'] ?? '';  

if (!ctype_digit($id)) {
    echo json_encode(['trovato' => false]);
    exit;
}

$id = (int)$id;

$query = "SELECT * FROM fari WHERE id = $id";
$result = pg_query($conn, $query);

if ($result && pg_num_rows($result) > 0) {
    echo json_encode(['trovato' => true]);
} else {
    echo json_encode(['trovato' => false]);
}
?>
