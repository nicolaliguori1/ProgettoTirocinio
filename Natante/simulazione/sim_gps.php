<?php
require __DIR__ . '/../../connessione.php';
$status_file = __DIR__ . '/sim_status.txt';

function randomFloat() {
    return mt_rand() / mt_getrandmax();
}

while (true) {
    $status = @file_get_contents($status_file);
    if (trim($status) !== 'on') {
        echo "[" . date('H:i:s') . "] Simulazione ferma, aspetto...\n";
        sleep(3);
        continue;
    }

    $conn = pg_connect($connection_string);
    if (!$conn) {
        echo "[" . date('H:i:s') . "] Errore di connessione al database.\n";
        sleep(3);
        continue;
    }

    $query = "
        SELECT b.targa, f.lat AS faro_lat, f.lon AS faro_lon
        FROM boats b
        LEFT JOIN fari f ON b.id_faro = f.id
    ";
    $result = pg_query($conn, $query);

    while ($row = pg_fetch_assoc($result)) {
        $targa = $row['targa'];
        $faroLat = $row['faro_lat'];
        $faroLon = $row['faro_lon'];

        $posQuery = pg_query_params($conn,
            "SELECT lat, lon FROM boats_position WHERE targa_barca = $1 ORDER BY ts DESC LIMIT 1",
            [$targa]
        );

        if (pg_num_rows($posQuery) > 0) {
            $lastPos = pg_fetch_assoc($posQuery);
            $lat = $lastPos['lat'];
            $lon = $lastPos['lon'];
        } else {
            if ($faroLat !== null && $faroLon !== null) {
                $lat = $faroLat;
                $lon = $faroLon;
            } else {
                $lat = 41.5 + randomFloat() * 1.5;
                $lon = 10.0 + randomFloat() * 2.0;
            }
        }

        $deltaLat = (randomFloat() - 0.5) * 0.01;
        $deltaLon = (randomFloat() - 0.5) * 0.01;
        $newLat = $lat + $deltaLat;
        $newLon = $lon + $deltaLon;

        $insert = pg_query_params($conn,
            "INSERT INTO boats_position (targa_barca, lat, lon) VALUES ($1, $2, $3)",
            [$targa, $newLat, $newLon]
        );

        if ($insert) {
            echo "[" . date('H:i:s') . "] [$targa] $newLat, $newLon\n";
        } else {
            echo "[" . date('H:i:s') . "] [$targa] Errore\n";
        }
    }

    pg_close($conn);
    sleep(3);
}
