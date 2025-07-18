<?php
include 'connessione.php';

// Esegui la query per recuperare tutti i fari
$query = "SELECT * FROM fari";
$result = pg_query($conn, $query);

$fari = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $fari[] = $row;
    }
} else {
    die("Errore nella query.");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco Fari</title>
</head>
<body>
    <h1>Elenco Fari</h1>

    <?php if (count($fari) > 0): ?>
        <ul>
            <?php foreach ($fari as $faro): ?>
                <li>
                    <a href="DettaglioFaro.php?id=<?= urlencode($faro['id']) ?>">
                        <?= htmlspecialchars($faro['nome']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Nessun faro trovato.</p>
    <?php endif; ?>
</body>
</html>
