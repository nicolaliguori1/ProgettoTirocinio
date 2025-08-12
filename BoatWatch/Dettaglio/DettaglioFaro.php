<?php
    include __DIR__ . '/../../connessione.php';

$faro = null;
$barche = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $queryFaro = "
        SELECT f.nome, f.lat, f.lon, f.id
        FROM fari f
        WHERE f.id = $1
    ";
    $resultFaro = pg_query_params($conn, $queryFaro, array($id));

    if ($resultFaro && pg_num_rows($resultFaro) > 0) {
        $faro = pg_fetch_assoc($resultFaro);

        $queryBarche = "
            SELECT nome, targa, lunghezza
            FROM boats
            WHERE id_faro = $1
        ";
        $resultBarche = pg_query_params($conn, $queryBarche, array($id));

        if ($resultBarche && pg_num_rows($resultBarche) > 0) {
            while ($row = pg_fetch_assoc($resultBarche)) {
                $barche[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dettaglio.css">
    <title>Dettaglio Faro</title>
</head>
<body>
    <div class="container">
        <?php include "../header.php" ?>

        <?php if ($faro): ?>
            <h1>Dettagli Faro: <?= htmlspecialchars($faro['nome']) ?></h1>
            <p><strong>Latitudine:</strong> <?= htmlspecialchars($faro['lat']) ?></p>
            <p><strong>Longitudine:</strong> <?= htmlspecialchars($faro['lon']) ?></p>
            <p><strong>Id:</strong> <?= htmlspecialchars($faro['id']) ?></p>

            <h2>Barche associate al faro</h2>
            <?php if (count($barche) > 0): ?>
                <ul>
                    <?php foreach ($barche as $barca): ?>
                        <li>
                            <strong><?= htmlspecialchars($barca['nome']) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nessuna barca associata a questo faro.</p>
            <?php endif; ?>

        <?php else: ?>
            <p>Faro non trovato o ID non specificato.</p>
        <?php endif; ?>

        <div style="margin-top: 20px; text-align: center;">
            <a href="../Elenco/elencoFari.php" 
               style="display: inline-block; padding: 10px 20px; background-color: #00d4ff; 
                      color: #000; text-decoration: none; border-radius: 10px; 
                      font-weight: bold; transition: background 0.3s ease;">
                ⬅ Torna all'elenco fari
            </a>
        </div>
    </div>
</body>
</html>
