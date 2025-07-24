<?php
require __DIR__ . '/../connessione.php';

header('Content-Type: application/json');

$targa = $_GET['targa'] ?? '';
$targa = trim($targa);

if ($targa === '') {
    echo json_encode([]);
    exit;
}

$res = pg_query_params($conn,
    "SELECT ts, stato FROM barca_presenza WHERE targa = $1 ORDER BY ts DESC LIMIT 10",
    [$targa]
);

$storico = [];

if ($res && pg_num_rows($res) > 0) {
    while ($row = pg_fetch_assoc($res)) {
        $storico[] = [
            'ts' => $row['ts'],
            'stato' => $row['stato']
        ];
    }
}

echo json_encode($storico);
?>
