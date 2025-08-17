<?php
require __DIR__ . '/../connessione.php';
require __DIR__ . '/functions_faro.php';

// Prendi l'ID e assicurati che sia un intero valido
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die('Errore: ID faro non specificato o non valido');
}

// Recupera i dati del faro
$faro = getFaroById($conn, $id);
if (!$faro) {
    die('Errore: Faro non trovato');
}

// Recupera ultima posizione o default
$last_pos = getFaroData($conn, $id);

$initialLat = is_numeric($last_pos['lat']) ? floatval($last_pos['lat']) : 0;
$initialLon = is_numeric($last_pos['lon']) ? floatval($last_pos['lon']) : 0;
$statoFaro = $last_pos['stato'] ?? 'inattivo';
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/faro.css?v=1">
<link rel="manifest" href="manifest.json">
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/>
<title> <?= htmlspecialchars($faro['nome']) ?></title>
</head>
<body>

<div class="pulsante-indietro"><a href="index.php">Indietro</a></div>
<div class="NomeFaro"><h1><?= htmlspecialchars($faro['nome']) ?></h1></div>

<div class="sezione">
  <h1>Posizione Attuale</h1>
  <div class="box-wrapper">
    <div class="box"><h2><strong>Latitudine</strong></h2><h2 id="lat"><?= htmlspecialchars($last_pos['lat'] ?? 'N/D') ?></h2></div>
    <div class="box"><h2><strong>Longitudine</strong></h2><h2 id="lon"><?= htmlspecialchars($last_pos['lon'] ?? 'N/D') ?></h2></div>
    <div class="box"><h2><strong>Stato</strong></h2><h2 id="stato"><?= htmlspecialchars($statoFaro) ?></h2></div>
  </div>

  <div id="map" style="height:500px;"></div>
</div>

<script>
const idFaro = <?= json_encode($id) ?>;
let initialLat = <?= json_encode($initialLat) ?>;
let initialLon = <?= json_encode($initialLon) ?>;

const map = L.map('map').setView([initialLat, initialLon], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

let marker = L.marker([initialLat, initialLon]).addTo(map);

function aggiornaDati() {
  fetch('faro_info.php?id=' + encodeURIComponent(idFaro))
    .then(res => res.json())
    .then(data => {
      if (!data || !data.lat || !data.lon) return;

      const currentLat = parseFloat(data.lat);
      const currentLon = parseFloat(data.lon);

      document.getElementById('lat').textContent = currentLat;
      document.getElementById('lon').textContent = currentLon;
      document.getElementById('stato').textContent = data.stato ?? 'inattivo';

      marker.setLatLng([currentLat, currentLon]);
      map.setView([currentLat, currentLon], 13);
    })
    .catch(err => console.error(err));
}

aggiornaDati();
setInterval(aggiornaDati, 2000);
</script>
</body>
</html>
