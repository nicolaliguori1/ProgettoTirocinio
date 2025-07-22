<?php
require __DIR__ . '/../connessione.php';

$targa = $_GET['targa'] ?? '';
$targa = pg_escape_string($conn, $targa);

// Recupera i dati della barca tramite targa
$query_barca = "SELECT * FROM boats WHERE targa = '$targa'";
$res_barca = pg_query($conn, $query_barca);
$barca = pg_fetch_assoc($res_barca);

if (!$barca) {
    echo "<h2>Errore: Barca non trovata.</h2>";
    exit;
}

$nome_barca = $barca['nome'];

// Ultima posizione
$res_live = pg_query($conn, "
    SELECT ts, lat, lon 
    FROM boats_position 
    WHERE targa_barca = '$targa' 
      AND ts = (SELECT MAX(ts) FROM boats_position WHERE targa_barca = '$targa')
");
$live = pg_fetch_assoc($res_live);

// Storico ultimi 10
$res_storico = pg_query($conn, "
   SELECT ts, lat, lon 
   FROM boats_position 
   WHERE targa_barca = '$targa' 
   ORDER BY ts DESC 
   LIMIT 10
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
  <h2 id="lat"><?= $live['lat'] ?? 'N/D' ?></h2>
</div>
<div class="box">
  <h2><strong>Longitudine</strong></h2>
  <h2 id="lon"><?= $live['lon'] ?? 'N/D' ?></h2>
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
  <script>
const targa = <?= json_encode($targa) ?>;

function aggiornaDati() {
  fetch('simulazione/api_posizione_barca.php?targa=' + encodeURIComponent(targa))
    .then(res => res.json())
    .then(data => {
      if (data.live) {
        document.getElementById('lat').textContent = data.live.lat ?? 'N/D';
        document.getElementById('lon').textContent = data.live.lon ?? 'N/D';
      }

      const storicoList = document.getElementById('storico-list');
      storicoList.innerHTML = '';
      if (data.storico && data.storico.length) {
        data.storico.forEach(punto => {
          const li = document.createElement('li');
          li.textContent = `${punto.ts} — ${punto.lat}, ${punto.lon}`;
          storicoList.appendChild(li);
        });
      }
    })
    .catch(err => console.error('Errore aggiornamento dati:', err));
}

// Aggiorna subito e poi ogni 10 secondi
aggiornaDati();
setInterval(aggiornaDati, 10);
</script>

</body>
</html>
<!-- commento nuovo-->
 