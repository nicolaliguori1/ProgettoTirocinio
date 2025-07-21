<?php
require __DIR__ . '/../connessione.php';

$id = $_GET['id'] ?? '';

if (!ctype_digit($id)) {
    echo "<h2>Errore: ID faro non valido.</h2>";
    exit;
}

$id = (int)$id;

$query_faro = "SELECT * FROM fari WHERE id = $id";
$res_faro = pg_query($conn, $query_faro);
$faro = pg_fetch_assoc($res_faro);

if (!$faro) {
    echo "<h2>Errore: Faro non trovato.</h2>";
    exit;
}

$nome_faro = $faro['nome'];
$lat = $faro['lat'] ?? 'N/D';
$lon = $faro['lon'] ?? 'N/D';
$stato = 'N/D'; // Puoi togliere se non serve

?>
<!DOCTYPE html>
<html lang="it" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="css/style.css?v=2">
  <link rel="manifest" href="/manifest.json">
  <script type="module" src="js/faro.js"></script>

  <title><?= htmlspecialchars($nome_faro) ?></title>
</head>

<body>  
  <div class="NomeFaro">
    <h1><?= htmlspecialchars(strtoupper($nome_faro)) ?></h1>
  </div>

  <div class="sezione">
    <h1>Posizione</h1>
    <div class="box-wrapper">
      <div class="box">
        <h2><strong>Nome</strong></h2>
        <h2><?= htmlspecialchars($nome_faro) ?></h2>
      </div>
      <div class="box">
        <h2><strong>Stato Faro</strong></h2>
        <h2 id="stato-faro"><?= htmlspecialchars($stato) ?></h2>
      </div>    
      <div class="box">
        <h2><strong>Latitudine</strong></h2>
        <h2><?= htmlspecialchars($lat) ?></h2>
      </div>
      <div class="box">  
        <h2><strong>Longitudine</strong></h2>
        <h2><?= htmlspecialchars($lon) ?></h2>
      </div>  
    </div>
  </div>

</body>
</html>
