<?php
require __DIR__ . '/../connessione.php';
require __DIR__ . '/functions_boat.php';

$targa = trim($_GET['targa'] ?? '');
if ($targa === '') die('Errore: targa non specificata');

$nome_barca = getBoatName($conn, $targa);
if (!$nome_barca) die('Errore: barca non trovata');

$live = getLivePosition($conn, $targa);
$initialLat = is_numeric($live['lat']) ? floatval($live['lat']) : 0;
$initialLon = is_numeric($live['lon']) ? floatval($live['lon']) : 0;
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/barca.css?v=2">
<link rel="manifest" href="manifest.json">
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/>
<title>Tracker <?= htmlspecialchars($nome_barca) ?></title>
</head>
<body>

<div class="pulsante-indietro"><a href="index.php">Indietro</a></div>
<div class="NomeBarca"><h1>TRACKER <?= htmlspecialchars($nome_barca) ?></h1></div>

<div class="sezione">
  <h1>Posizione Live</h1>
  <div class="box-wrapper">
    <div class="box"><h2><strong>Latitudine</strong></h2><h2 id="lat"><?= htmlspecialchars($live['lat'] ?? 'N/D') ?></h2></div>
    <div class="box"><h2><strong>Longitudine</strong></h2><h2 id="lon"><?= htmlspecialchars($live['lon'] ?? 'N/D') ?></h2></div>
    <div class="box"><h2><strong>Presenza nel porto</strong></h2><h2 id="presfaro"></h2></div>
  </div>

  <div id="map" style="height:500px;"></div>

  <div class="storico">
    <table id="storico-table">
      <caption>Storico posizioni</caption>
      <thead>
        <tr><th>Giorno</th><th>Orario</th><th>Latitudine</th><th>Longitudine</th></tr>
      </thead>
      <tbody><tr><td colspan="4">Caricamento storico...</td></tr></tbody>
    </table>
  </div>
</div>

<script>
const targa = <?= json_encode($targa) ?>;
let initialLat = <?= json_encode($initialLat) ?>;
let initialLon = <?= json_encode($initialLon) ?>;

const map = L.map('map').setView([initialLat, initialLon], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

let marker = null, polyline = null, pathPoints = [], lineaVisibile = false;

function aggiornaDati() {
  fetch('info_barca.php?targa=' + encodeURIComponent(targa))
    .then(res => res.json())
    .then(data => {
      if (!data.live || data.live.lat == null || data.live.lon == null) return;
      const currentLat = parseFloat(data.live.lat);
      const currentLon = parseFloat(data.live.lon);
      document.getElementById('lat').textContent = currentLat;
      document.getElementById('lon').textContent = currentLon;

      const liveLatLng = [currentLat, currentLon];
      if (marker) marker.setLatLng(liveLatLng);
      else marker = L.marker(liveLatLng).addTo(map);
      map.setView(liveLatLng, 13);

      if (data.stato?.trim().toLowerCase() === "nel porto") {
        if (polyline) { map.removeLayer(polyline); polyline = null; }
        lineaVisibile = false; pathPoints = [];
        return;
      }

      if (!lineaVisibile) {
        pathPoints = [liveLatLng];
        polyline = L.polyline(pathPoints, { color: 'blue' }).addTo(map);
        lineaVisibile = true;
      } else {
        pathPoints.push(liveLatLng);
        if (polyline) polyline.setLatLngs(pathPoints);
      }
    });
}

function aggiornaPresenzaPorto() {
  fetch('info_barca.php?targa=' + encodeURIComponent(targa))
    .then(res => res.json())
    .then(data => {
      document.getElementById('presfaro').innerHTML = data.stato ? '<h2>' + data.stato + '</h2>' : '<h2>Errore</h2>';
    });
}

function aggiornaStoricoPosizioni() {
  fetch('info_barca.php?targa=' + encodeURIComponent(targa))
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector('#storico-table tbody');
      tbody.innerHTML = '';
      if (!data.storico || data.storico.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">Nessun dato disponibile.</td></tr>';
        return;
      }
      data.storico.forEach(entry => {
        const ts = new Date(entry.ts);
        const row = document.createElement('tr');
        row.innerHTML = `<td>${ts.toLocaleDateString('it-IT')}</td>
                         <td>${ts.toLocaleTimeString('it-IT')}</td>
                         <td>${entry.lat}</td>
                         <td>${entry.lon}</td>`;
        tbody.appendChild(row);
      });
    });
}

aggiornaDati(); aggiornaPresenzaPorto(); aggiornaStoricoPosizioni();
setInterval(aggiornaDati, 3000);
setInterval(aggiornaPresenzaPorto, 3000);
setInterval(aggiornaStoricoPosizioni, 3000);
</script>
</body>
</html>
