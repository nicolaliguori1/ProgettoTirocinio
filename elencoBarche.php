<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'connessione.php';

$query = "SELECT nome FROM boats ORDER BY nome ASC";
$result = pg_query($conn, $query);

if (!$result) {
    die("Errore nella query al database.");
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Elenco Barche</title>
</head>
<body>
  <h1>Seleziona una barca</h1>
  <ul>
    <?php while ($row = pg_fetch_assoc($result)): ?>
      <li>
        <a href="DettaglioBarca.php?boats=<?php echo urlencode($row['nome']); ?>">
          <?php echo htmlspecialchars($row['nome']); ?>
        </a>
      </li>
    <?php endwhile; ?>
  </ul>
</body>
</html>
