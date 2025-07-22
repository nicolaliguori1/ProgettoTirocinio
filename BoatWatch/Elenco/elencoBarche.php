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
    <link rel="stylesheet" href="elenco.css?v=2">
    <title>Elenco Barche</title>
</head>
<body>
    <div class="container">
        <h1>Elenco Barche</h1>
        <button onclick="window.location.href='/TiroBarca/BoatWatch/Add/AddBarca.php'">➕ Aggiungi Barca</button>

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
                            <td><?= htmlspecialchars($barca['nome']) ?></td>
                            <td><?= htmlspecialchars($barca['targa']) ?></td>
                            <td>
                                <a href="/TiroBarca/BoatWatch/Dettaglio/DettaglioBarca.php?targa=<?= urlencode($barca['targa']) ?>">📍 Dettaglio</a>
                                <a href="/TiroBarca//TiroBoat/BoatWatch/Modifica/modificaBarca.php?targa=<?= urlencode($barca['targa']) ?>">✏ Modifica</a>
                                <a href="/TiroBarca/BoatWatch/Elimina/eliminaBarca.php?targa=<?= urlencode($barca['targa']) ?>" onclick="return confirm('Sei sicuro di voler eliminare questa barca?');">🗑 Elimina</a>
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
