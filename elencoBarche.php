<?php
session_start();
include 'connessione.php';

// Verifica se l'utente è loggato
if (!isset($_SESSION["id"])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION["id"];

// Query per tutte le barche dell'utente loggato
$query = "SELECT * FROM boats WHERE id_user = $1";
$result = pg_query_params($conn, $query, array($user_id));

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
    <link rel="stylesheet" href="elenco.css?v=2">
    <title>Elenco Barche</title>
</head>
<body>
    <h1>Elenco Barche</h1>
    <button onclick="window.location.href='AddBarca.php'">➕ Aggiungi Barca</button>

    <?php if (count($barche) > 0): ?>
        <ul>
            <?php foreach ($barche as $barca): ?>
                <li>
                    <strong><?= htmlspecialchars($barca['nome']) ?></strong><br>
                    Targa: <?= htmlspecialchars($barca['targa']) ?><br>
                    <a href="DettaglioBarca.php?targa=<?= urlencode($barca['targa']) ?>">📍 Dettaglio</a>
                    <a href="modificaBarca.php?targa=<?= urlencode($barca['targa']) ?>">✏️ Modifica</a>
                    <a href="eliminaBarca.php?targa=<?= urlencode($barca['targa']) ?>" onclick="return confirm('Sei sicuro di voler eliminare questa barca?');">🗑️ Elimina</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Nessuna barca trovata.</p>
    <?php endif; ?>
</body>
</html>
