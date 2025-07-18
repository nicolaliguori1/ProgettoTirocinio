<?php
include 'connessione.php';

// Query per tutte le barche
$query = "SELECT * FROM boats";
$result = pg_query($conn, $query);

$barche = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $barche[] = $row;
    }
} else {
    die("Errore nella query.");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco Barche</title>
</head>
<body>
    <h1>Elenco Barche</h1>
    <button onclick="window.location.href='AddBarca.php'">Aggiungi Faro</button>
    <?php if (count($barche) > 0): ?>
        <ul>
            <?php foreach ($barche as $barca): ?>
                <li>
                    <a href="DettaglioBarca.php?targa=<?= urlencode($barca['targa']) ?>">
                        <?= htmlspecialchars($barca['nome']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Nessuna barca trovata.</p>
    <?php endif; ?>
</body>
</html>
