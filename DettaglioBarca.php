<?php
include 'connessione.php';

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

        echo "<h1>Dettagli Barca: " . htmlspecialchars($barca['nome']) . "</h1>";
        echo "<p><strong>Targa:</strong> " . htmlspecialchars($barca['targa']) . "</p>";
        echo "<p><strong>Lunghezza:</strong> " . htmlspecialchars($barca['lunghezza']) . " metri</p>";
        echo "<p><strong>Proprietario:</strong> " . htmlspecialchars($barca['nome_utente'] ?? 'N/A') . "</p>";
        echo "<p><strong>Faro Associato:</strong> " . htmlspecialchars($barca['nome_faro'] ?? 'Nessuno') . "</p>";
    } else {
        echo "<p>Barca non trovata.</p>";
    }
} else {
    echo "<p>Nessuna targa specificata.</p>";
}
?>
