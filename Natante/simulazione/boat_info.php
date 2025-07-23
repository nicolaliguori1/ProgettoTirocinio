<?php
require __DIR__ . '/../../connessione.php'; 

if (!$conn) {
    echo json_encode(['trovata' => false]);
    exit;
}

$targa = $_GET['targa'] ?? '';
$targa = pg_escape_string($conn, $targa);

$query = "SELECT * FROM boats WHERE targa = '$targa'";
$result = pg_query($conn, $query);

if ($result && pg_num_rows($result) > 0) {
    echo json_encode(['trovata' => true]);
} else {
    echo json_encode(['trovata' => false]);
}
?>



