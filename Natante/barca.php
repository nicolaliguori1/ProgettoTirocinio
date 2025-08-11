<?php
require __DIR__ . '/../connessione.php';

$targa = $_GET['targa'] ?? '';
$targa = trim($targa);

if ($targa === '') {
    die('Errore: targa non specificata');
}

// Prendo il nome barca
$query_nome = "SELECT nome FROM boats WHERE targa = $1";
$res_nome = pg_query_params($conn, $query_nome, [$targa]);

if (!$res_nome || pg_num_rows($res_nome) === 0) {
    die('Errore: barca non trovata');
}

$row_nome = pg_fetch_assoc($res_nome);
$nome_barca = $row_nome['nome'];

// Provo posizione live
$res_live = pg_query_params($conn, "SELECT ts, lat, lon, id_rotta FROM boats_current_position WHERE targa_barca = $1", [$targa]);

$live = false;
if ($res_live && pg_num_rows($res_live) > 0) {
    $row_live = pg_fetch_assoc($res_live);
    if (
        isset($row_live['lat'], $row_live['lon']) &&
        is_numeric($row_live['lat']) &&
        is_numeric($row_live['lon'])
    ) {
        $live = $row_live;
    }
}

if (!$live) {
    // fallback sul faro associato
    $query_faro = "SELECT f.lat, f.lon
                   FROM boats b
                   JOIN fari f ON b.id_faro = f.id
                   WHERE b.targa = $1";
    $res_faro = pg_query_params($conn, $query_faro, [$targa]);

    if ($res_faro && pg_num_rows($res_faro) > 0) {
        $row_faro = pg_fetch_assoc($res_faro);
        if (is_numeric($row_faro['lat']) && is_numeric($row_faro['lon'])) {
            $live = [
                'ts' => null,
                'lat' => floatval($row_faro['lat']),
                'lon' => floatval($row_faro['lon']),
                'id_rotta' => 0
            ];
        } else {
            $live = ['ts' => null, 'lat' => null, 'lon' => null, 'id_rotta' => 0];
        }
    } else {
        $live = ['ts' => null, 'lat' => null, 'lon' => null, 'id_rotta' => 0];
    }
}

$initialLat = is_numeric($live['lat']) ? floatval($live['lat']) : 0;
$initialLon = is_numeric($live['lon']) ? floatval($live['lon']) : 0;


?>

<!DOCTYPE html>
<html lang="it" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="css/style.css?v=3">
  <link rel="manifest" href="manifest.json">
  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" crossorigin=""></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" crossorigin=""/>
  <title>Tracker <?= htmlspecialchars($nome_barca) ?></title>
</head>

<body>
  <div class="pulsante-indietro">
  <a href="http://localhost/tirocinio/ProgettoTirocinio/Natante/">Indietro</a>
</div>

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
        <h2 id="lat"><?= (is_numeric($live['lat'])) ? htmlspecialchars($live['lat']) : 'N/D' ?></h2>
      </div>
      <div class="box">
        <h2><strong>Longitudine</strong></h2>
        <h2 id="lon"><?= (is_numeric($live['lon'])) ? htmlspecialchars($live['lon']) : 'N/D' ?></h2>
      </div>
      <div class="box">
        <h2 id="presfaro"></h2>
      </div>
    </div>

    <div id="map"></div>
    <!-- STORICO PRESENZA -->
    <div class="storico">
  
  <table id="storico-table">
  <caption>Storico Entrata/Uscita Porto</caption>
    <thead>
      <tr>
        <th>Giorno</th>
        <th>Orario</th>
        <th>Posizione</th>
      </tr>
    </thead>
    <tbody>
      <tr><td colspan="3">Caricamento storico...</td></tr>
    </tbody>
  </table>
</div>
  </div>

<script>
const targa = <?= json_encode($targa) ?>;
let initialLat = <?= json_encode($initialLat) ?>;
let initialLon = <?= json_encode($initialLon) ?>;

if (typeof initialLat !== 'number' || isNaN(initialLat)) initialLat = 0;
if (typeof initialLon !== 'number' || isNaN(initialLon)) initialLon = 0;

const mapElement = document.getElementById('map');

if (mapElement) {
  const map = L.map('map').setView([initialLat, initialLon], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  let marker = null;
  let polyline = null;
  let pathPoints = [];
  let lineaVisibile = false;

  function aggiornaDati() {
    fetch('simulazione/api_posizione_barca.php?targa=' + encodeURIComponent(targa))
      .then(res => res.json())
      .then(data => {
        console.log('Dati live ricevuti:', data);

        if (data.live && data.live.lat != null && data.live.lon != null) {
          const currentLat = parseFloat(data.live.lat);
          const currentLon = parseFloat(data.live.lon);
          const id_rotta = parseInt(data.live.id_rotta);

          if (isNaN(currentLat) || isNaN(currentLon)) {
            console.warn('Coordinate non valide');
            return;
          }

          document.getElementById('lat').textContent = currentLat;
          document.getElementById('lon').textContent = currentLon;

          const liveLatLng = [currentLat, currentLon];

          if (marker) {
            marker.setLatLng(liveLatLng);
          } else {
            marker = L.marker(liveLatLng).addTo(map);
          }
          map.setView(liveLatLng, 13);

          // Reset linea se barca è nel porto
          if (data.stato && data.stato.trim().toLowerCase() === "nel porto") {
            if (polyline) {
              map.removeLayer(polyline);
              polyline = null;
            }
            lineaVisibile = false;
            pathPoints = [];
            return;
          }

          if (!lineaVisibile) {
            pathPoints = [liveLatLng];
            polyline = L.polyline(pathPoints, { color: 'blue' }).addTo(map);
            lineaVisibile = true;
          } else {
            pathPoints.push(liveLatLng);
            if (polyline) {
              polyline.setLatLngs(pathPoints);
            }
          }
        } else {
          console.warn('Dati live non disponibili o non validi');
        }
      })
      .catch(err => console.error('Errore aggiornamento dati:', err));
  }

  function aggiornaPresenzaPorto() {
    fetch('simulazione/api_posizione_barca.php?targa=' + encodeURIComponent(targa))
      .then(res => res.json())
      .then(data => {
        if (data.stato) {
          document.getElementById('presfaro').innerHTML =
            '<h3>Presenza nel porto</h3><h2>' + data.stato + '</h2>';
        } else {
          document.getElementById('presfaro').innerHTML =
            '<h3>Presenza nel porto</h3><h2>Errore</h2>';
          console.error(data.errore || 'Errore sconosciuto');
        }
      })
      .catch(err => {
        document.getElementById('presfaro').innerHTML =
          '<h3>Presenza nel porto</h3><h2>Errore</h2>';
        console.error('Errore chiamata api_posizione_barca:', err);
      });
  }

  function aggiornaStoricoPresenza() {
    fetch('simulazione/api_posizione_barca.php?targa=' + encodeURIComponent(targa))
      .then(res => res.json())
      .then(data => {
        const tbody = document.querySelector('#storico-table tbody');
        tbody.innerHTML = '';

        if (!data.storico || data.storico.length === 0) {
          tbody.innerHTML = '<tr><td colspan="3">Nessun dato disponibile.</td></tr>';
          return;
        }

        const ultime10 = data.storico.slice(0, 10);

        ultime10.forEach(entry => {
          const row = document.createElement('tr');

          const ts = new Date(entry.ts);
          const giorno = ts.toLocaleDateString('it-IT');
          const orario = ts.toLocaleTimeString('it-IT');

          const cellGiorno = document.createElement('td');
          cellGiorno.textContent = giorno;
          row.appendChild(cellGiorno);

          const cellOrario = document.createElement('td');
          cellOrario.textContent = orario;
          row.appendChild(cellOrario);

          const cellPosizione = document.createElement('td');
          cellPosizione.textContent = `Lat: ${entry.lat.toFixed(5)}, Lon: ${entry.lon.toFixed(5)}`;
          row.appendChild(cellPosizione);

          // Inserisci la riga in cima alla tabella
          if (tbody.firstChild) {
            tbody.insertBefore(row, tbody.firstChild);
          } else {
            tbody.appendChild(row);
          }
        });
      })
      .catch(err => {
        console.error('Errore caricamento storico:', err);
        const tbody = document.querySelector('#storico-table tbody');
        tbody.innerHTML = '<tr><td colspan="3">Errore nel caricamento dello storico.</td></tr>';
      });
  }

  // Avvio i timer e salvo gli ID per poterli stoppare
  aggiornaDati();
  aggiornaPresenzaPorto();
  aggiornaStoricoPresenza();

  const timerDati = setInterval(aggiornaDati, 2000);
  const timerPresenza = setInterval(aggiornaPresenzaPorto, 2000);
  const timerStorico = setInterval(aggiornaStoricoPresenza, 2000);

  // Pulisci i timer quando esci o ricarichi la pagina
  window.addEventListener('beforeunload', () => {
    clearInterval(timerDati);
    clearInterval(timerPresenza);
    clearInterval(timerStorico);
  });
}

</script>

</body>
</html>

