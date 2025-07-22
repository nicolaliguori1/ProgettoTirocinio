<?php
include 'connessione.php';

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
    <link rel="stylesheet" href="elenco.css?v=2">
    <title>Elenco Fari</title>
</head>
<body>
    <div class="container">
        <h1>Elenco Fari</h1>
        <button onclick="window.location.href='addFaro.php'">➕ Aggiungi Faro</button>

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
                            <td><?= htmlspecialchars($faro['nome']) ?></td>
                            <td><?= htmlspecialchars($faro['lat']) ?></td>
                            <td><?= htmlspecialchars($faro['lon']) ?></td>
                            <td>
                                <a href="modificaFaro.php?id=<?= $faro['id'] ?>">✏️ Modifica</a>
                                <a href="eliminaFaro.php?id=<?= $faro['id'] ?>" onclick="return confirm('Sei sicuro di voler eliminare questo faro?');">🗑️ Elimina</a>
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
