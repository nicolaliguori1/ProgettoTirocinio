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
    <title>Elenco Barche</title>
</head>
<body>
    <h1>Elenco Barche</h1>
    <button onclick="window.location.href='AddBarca.php'">Aggiungi Barca</button>
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
