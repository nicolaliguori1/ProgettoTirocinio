<?php
session_start();
include __DIR__ . '/../../connessione.php';

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
    <link rel="stylesheet" href="elenco.css?v=6">
    <title>Elenco Barche</title>
</head>
<body>
    <div class="container">
        <?php
        include "../header.php"
        ?>
        <h1>Elenco Barche</h1>
        <button class="add" onclick="window.location.href='../Add/AddBarca.php'">➕ Aggiungi Barca</button>

        <?php if (count($barche) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Targa</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($barche as $barca): ?>
                        <tr>
                        <td>
    <a href="../Dettaglio/DettaglioBarca.php?targa=<?= urlencode($barca['targa']) ?>">
        <strong><?= htmlspecialchars($barca['nome']) ?></strong>
    </a>
</td>
                            <td><?= htmlspecialchars($barca['targa']) ?></td>
                            <td>
                                <a href="../Modifica/modificaBarca.php?targa=<?= urlencode($barca['targa']) ?>">✏ Modifica</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-boats">Nessuna barca trovata.</p>
        <?php endif; ?>
    </div>
</body>
</html>
