<?php
include 'connessione.php';

// Query per tutte le barche
$query = "SELECT * FROM boats";
$result = pg_query($conn, $query);

if (!$result) {
    die("Errore nella query.");
}
?>

<h1>Elenco Barche</h1>
<ul>
    <?php while ($barca = pg_fetch_assoc($result)): ?>
        <li>
            <strong><?php echo htmlspecialchars($barca['nome']); ?></strong> - 
            Targa: <?php echo htmlspecialchars($barca['targa']); ?> - 
            Lunghezza: <?php echo htmlspecialchars($barca['lunghezza']); ?>m
            <a href="DettaglioBarca.php?targa=<?php echo urlencode($barca['targa']); ?>">Dettagli</a>
        </li>
    <?php endwhile; ?>
</ul>
