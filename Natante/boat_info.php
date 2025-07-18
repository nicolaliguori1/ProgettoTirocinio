<?php

require __DIR__ . '/../connessione.php';


if (!$conn) {
    echo json_encode(['trovata' => false]);
    exit;
}

$nome = $_GET['nome'] ?? '';
$nome = pg_escape_string($conn, $nome);

$query = "SELECT * FROM boats WHERE nome = '$nome' OR targa = '$nome'";
$result = pg_query($conn, $query);

if ($result && pg_num_rows($result) > 0) {
    echo json_encode(['trovata' => true]);
} else {
    echo json_encode(['trovata' => false]);
}
?>


