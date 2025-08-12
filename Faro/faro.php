<?php
require __DIR__ . '/../connessione.php';
date_default_timezone_set('UTC');

$id = $_GET['id'] ?? '';


if (isset($_GET['api']) && $_GET['api'] == '1') {
    if (!ctype_digit($id)) {
        http_response_code(400);
        echo json_encode(["error" => "ID faro non valido"]);
        exit;
    }
    $id = (int)$id;

    $query_last_pos = "
        SELECT lat, lon, ts
        FROM fari_position
        WHERE id_faro = $id
        ORDER BY ts DESC
        LIMIT 1
    ";
    $res_last_pos = pg_query($conn, $query_last_pos);
    $last_pos = pg_fetch_assoc($res_last_pos);

    if (!$last_pos) {
        $query_faro = "SELECT lat, lon FROM fari WHERE id = $id";
        $res_faro = pg_query($conn, $query_faro);
        $faro = pg_fetch_assoc($res_faro);

        $data = [
            "lat" => $faro['lat'] ?? null,
            "lon" => $faro['lon'] ?? null,
            "stato" => "inattivo"
        ];
    } else {
        $now = time();
        $last_ts = strtotime($last_pos['ts']);
        $stato = ($now - $last_ts <= 60) ? "attivo" : "inattivo";

        $data = [
            "lat" => $last_pos['lat'],
            "lon" => $last_pos['lon'],
            "stato" => $stato,
            "ts" => $last_pos['ts']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}


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

// Recupera ultima posizione
$query_last_pos = "
    SELECT lat, lon, ts
    FROM fari_position
    WHERE id_faro = $id
    ORDER BY ts DESC
    LIMIT 1
";
$res_last_pos = pg_query($conn, $query_last_pos);
$last_pos = pg_fetch_assoc($res_last_pos);

$lat = $last_pos['lat'] ?? $faro['lat'] ?? 'N/D';
$lon = $last_pos['lon'] ?? $faro['lon'] ?? 'N/D';

$stato = 'inattivo';
if ($last_pos && $last_pos['ts']) {
    $last_ts = strtotime($last_pos['ts']);
    $now = time();
    if (($now - $last_ts) <= 60) {
        $stato = 'attivo';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="css/style.css?v=3">
  <link rel="manifest" href="manifest.json">

  <!-- Leaflet CSS/JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <title><?= htmlspecialchars($nome_faro) ?></title>
  
</head>
<body>  
  <div class="pulsante-indietro">
  <a href="index.php">Indietro</a>
</div>
  <div class="NomeFaro">
    <h1><?= htmlspecialchars(strtoupper($nome_faro)) ?></h1>
  </div>

  <div class="container">
    <h1>Dettagli Faro</h1>
    <div class="box-wrapper">
      <div class="box">
        <h2><strong>Latitudine</strong></h2>
        <h2 id="lat-faro"><?= htmlspecialchars($lat) ?></h2>
      </div>
      <div class="box">  
        <h2><strong>Longitudine</strong></h2>
        <h2 id="lon-faro"><?= htmlspecialchars($lon) ?></h2>
      </div>  
      <div class="box">
        <h2><strong>Stato Faro</strong></h2>
        <h2 id="stato-faro"><?= htmlspecialchars($stato) ?></h2>
      </div>  
    </div>
  </div>

  <div id="map"></div>

  <script>
    const FARO_ID = <?= $id ?>;

    const latElem = document.getElementById("lat-faro");
    const lonElem = document.getElementById("lon-faro");
    const statoElem = document.getElementById("stato-faro");

    let map = L.map('map').setView(
        [parseFloat(latElem.textContent) || 0, parseFloat(lonElem.textContent) || 0],
        15
    );
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker = L.marker(
        [parseFloat(latElem.textContent) || 0, parseFloat(lonElem.textContent) || 0]
    ).addTo(map);

    async function aggiornaDati() {
        try {
            const res = await fetch(`faro.php?id=${FARO_ID}&api=1`);
            const data = await res.json();

            if (data.error) {
                console.error(data.error);
                return;
            }

            latElem.textContent = data.lat ?? "N/D";
            lonElem.textContent = data.lon ?? "N/D";
            statoElem.textContent = data.stato ?? "N/D";

            if (data.lat && data.lon) {
                const latNum = parseFloat(data.lat);
                const lonNum = parseFloat(data.lon);
                marker.setLatLng([latNum, lonNum]);
                map.setView([latNum, lonNum]);
            }
        } catch (err) {
            console.error("Errore nel recupero dati faro:", err);
        }
    }

    aggiornaDati();
    setInterval(aggiornaDati, 3000);
  </script>
</body>
</html>
