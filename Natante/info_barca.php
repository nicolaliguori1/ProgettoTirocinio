<?php
require __DIR__ . '/../connessione.php';
require __DIR__ . '/functions_boat.php';


header('Content-Type: application/json');

$targa = trim($_GET['targa'] ?? '');
if ($targa === '') {
    echo json_encode(['trovata' => false, 'errore' => 'Targa vuota']);
    exit;
}

// Controlla se la barca esiste
$res = pg_query_params($conn, "SELECT nome FROM boats WHERE targa = $1 LIMIT 1", [$targa]);
if (!$res || pg_num_rows($res) === 0) {
    echo json_encode(['trovata' => false]);
    exit;
}

$nome = pg_fetch_assoc($res)['nome'];

// Recupera posizione live
$live = getLivePosition($conn, $targa);

echo json_encode([
    'trovata' => true,
    'nome' => $nome,
    'live' => $live
]);
exit;
?>
