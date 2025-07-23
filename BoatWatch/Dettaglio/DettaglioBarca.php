<?php
    include __DIR__ . '/../../connessione.php';

$barca = null;

if (isset($_GET['targa'])) {
    $targa = $_GET['targa'];

    $query = "
        SELECT b.targa, b.nome, b.lunghezza, u.nome_utente, f.nome AS nome_faro
        FROM boats b
        LEFT JOIN users u ON b.id_user = u.id
        LEFT JOIN fari f ON b.id_faro = f.id
        WHERE b.targa = $1
    ";

    $result = pg_query_params($conn, $query, array($targa));

    if ($result && pg_num_rows($result) > 0) {
        $barca = pg_fetch_assoc($result);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettagli Barca</title>
    <link rel="stylesheet" href="dettaglio.css?v=2">
</head>
<body>
    <div class="container">
        <?php if ($barca): ?>
            <h1 class="titolo">Dettagli Barca: <?= htmlspecialchars($barca['nome']) ?></h1>
            <p><strong>Targa:</strong> <?= htmlspecialchars($barca['targa']) ?></p>
            <p><strong>Lunghezza:</strong> <?= number_format((float)$barca['lunghezza'], 1) ?> metri</p>
            <p><strong>Proprietario:</strong> <?= htmlspecialchars($barca['nome_utente'] ?? 'N/A') ?></p>
            <p><strong>Faro Associato:</strong> <?= htmlspecialchars($barca['nome_faro'] ?? 'Nessuno') ?></p>
        <?php elseif (isset($targa)): ?>
            <p class="errore">Barca non trovata.</p>
        <?php else: ?>
            <p class="errore">Nessuna targa specificata.</p>
        <?php endif; ?>
    </div>
</body>
</html>
