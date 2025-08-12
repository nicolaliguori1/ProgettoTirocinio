<?php
session_start();
include __DIR__ . '/../../connessione.php';

if (!isset($_SESSION["id"])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION["id"];

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
    <link rel="stylesheet" href="elenco.css">
    <title>Elenco Fari</title>
</head>
<body>
    <div class="container">
        <?php
        include "../header.php"
        ?>
        <h1>Elenco Fari</h1>
        <button class="add" onclick="window.location.href='../Add/AddFaro.php'">➕ Aggiungi Faro</button>

        <?php if (count($fari) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Latitudine</th>
                        <th>Longitudine</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fari as $faro): ?>
                        <tr>
                            <td><a href="../Dettaglio/DettaglioFaro.php?id=<?= $faro['id'] ?>"><strong><?= htmlspecialchars($faro['nome']) ?></strong></a></td>
                            <td><?= htmlspecialchars($faro['lat']) ?></td>
                            <td><?= htmlspecialchars($faro['lon']) ?></td>
                            <td>
                                <a href="../Modifica/modificaFaro.php?id=<?= $faro['id'] ?>">Modifica</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nessun faro trovato.</p>
        <?php endif; ?>
    </div>
</body>
</html>
