<?php
require __DIR__ . '/../connessione.php';


$nome = $_GET['nome'] ?? '';
$nome = pg_escape_string($conn, $nome);

// Prendi targa e nome della barca
$query_barca = "SELECT * FROM boats WHERE nome = '$nome' OR targa = '$nome'";
$res_barca = pg_query($conn, $query_barca);
$barca = pg_fetch_assoc($res_barca);

if (!$barca) {
    echo "<h2>Errore: Barca non trovata.</h2>";
    exit;
}



$targa = $barca['targa'];
$nome_barca = $barca['nome'];

// Ultima posizione (live)
$res_live = pg_query($conn, "
    SELECT ts, lat, lon 
FROM boats_position 
WHERE targa_barca = '$targa' 
  AND ts = (SELECT MAX(ts) FROM boats_position WHERE targa_barca = '$targa')

");
$live = pg_fetch_assoc($res_live);


// Storico recente (ultimi 10)
$res_storico = pg_query($conn, "
   SELECT ts, lat, lon 
FROM boats_position 
WHERE targa_barca = '$targa' 
ORDER BY ts DESC 
LIMIT 10;
");
?>

<!DOCTYPE html>
<html lang="en-US" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="css/style.css">
  <link rel="manifest" href="manifest.json">
  <script type="module" src="js/app.js"></script>

  <title>Tracker <?= htmlspecialchars($nome_barca) ?></title>

</head>

<body>  
  <div class="NomeBarca">
  <h1>TRACKER <?= htmlspecialchars($nome_barca) ?></h1>
</div>

  <div class="sezione">
    <h1>Posizione Live</h1>
    <div class="box-wrapper">
  <div class="box">
    <h2><strong>Nome</strong></h2>
    <h2><?= htmlspecialchars($nome_barca) ?></h2>
  </div>
  <div class="box">
    <h2><strong>Latitudine</strong></h2>
    <h2><?= $live['lat'] ?? 'N/D' ?></h2>
  </div>
  <div class="box">
    <h2><strong>Longitudine</strong></h2>
    <h2><?= $live['lon'] ?? 'N/D' ?></h2>
  </div>
</div>

  </div>


  <div class="sezione">
    <h1>Storico Recente</h1>
    <div class="storico">
      <ul id="storico-list">
        <?php while ($row = pg_fetch_assoc($res_storico)): ?>
  <li><?= $row['ts'] ?> — <?= $row['lat'] ?>, <?= $row['lon'] ?></li>
<?php endwhile; ?>

      </ul>
    </div>    
  </div>
</body>
</html>
<!-- commento nuovo-->
 