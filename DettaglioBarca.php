<?php
session_start();
require_once 'connessione.php';

if (!isset($_GET['barca']) || !is_numeric($_GET['barca'])) {
    die("Errore: nessuna barca selezionata.");
}

$barca_id = (int) $_GET['barca'];

// Nota: verifica che il campo corretto sia b.id o b.barca_id
$query = "SELECT b.nome as nome_barca, b.targa, b.id_user, bp.lon, bp.lat, f.nome as nome_faro
          FROM boats b
          JOIN users u ON u.id = b.id_user
          JOIN boats_position bp ON bp.targa_barca = b.targa
          JOIN fari f ON f.id = b.id_faro
          WHERE b.id = $1";

$result = pg_query_params($conn, $query, array($barca_id));

if (!$result || pg_num_rows($result) === 0) {
    die("Errore: barca non trovata.");
}

$barca = pg_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="style.css" />
    <title><?php echo htmlspecialchars($barca['nome_barca']); ?></title>
</head>
<body>
<div class="sezione">
    <h1></h1>
    <div class="box-wrapper">
      <div class="box">
        <h2><strong>Targa</strong></h2>
        <h2><?php echo htmlspecialchars($barca['targa']); ?></h2>
      </div>
    </div>
  </div>

  <div class="sezione">
    <h1>Posizione Barca</h1>
    <div class="box-wrapper">
      <div class="box">
        <h2>Latitudine</h2>
        <h2><?php echo htmlspecialchars($barca['lat']); ?></h2>
      </div>
      <div class="box">
        <h2>Longitudine</h2>
        <h2><?php echo htmlspecialchars($barca['lon']); ?></h2>
      </div>
    </div>
  </div>

  <div class="sezione">
    <h1>INFORMAZIONI MOLO</h1>
    <div class="box-wrapper">
      <div class="box">
        <h2>Nome Faro</h2>
        <h2><?php echo htmlspecialchars($barca['nome_faro']); ?></h2>
      </div>
      <div class="box">
        <h2>Distanza Dal Faro</h2>
        <h2></h2>
      </div>
      <div class="box">
        <h2></h2>
      </div>
    </div>
  </div>

  <div class="sezione">
    <h1>Storico Recente</h1>
    <div class="storico">
      <ul id="storico-list">
        <!-- JS inserirà qui gli elementi -->
      </ul>
    </div>    
  </div>
</body>
</html>