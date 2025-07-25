<?php
require __DIR__ . '/../../connessione.php';

$status_file = __DIR__ . '/sim_status.txt';

$routes = [
    1 => [
        [40.6333, 14.6833], [40.6328, 14.6840], [40.6320, 14.6850], [40.6310, 14.6870],
        [40.6300, 14.6900], [40.6290, 14.6940], [40.6280, 14.7000], [40.6265, 14.7050],
        [40.6250, 14.7100], [40.6230, 14.7125], [40.6200, 14.7150], [40.6175, 14.7175],
        [40.6150, 14.7200], [40.6125, 14.7200], [40.6100, 14.7200], [40.6125, 14.7125],
        [40.6150, 14.7050], [40.6175, 14.7000], [40.6200, 14.6950], [40.6210, 14.6925],
        [40.6220, 14.6900], [40.6260, 14.6850], [40.6300, 14.6800]
    ],
    2 => [
        [40.2500, 14.9000], [40.2510, 14.9020], [40.2520, 14.9050], [40.2480, 14.9100],
        [40.2450, 14.9200], [40.2420, 14.9250], [40.2400, 14.9250], [40.2380, 14.9200],
        [40.2390, 14.9150], [40.2420, 14.9150], [40.2460, 14.9050], [40.2500, 14.9000]
    ],
    3 => [
        [40.6745, 14.7519], [40.6720, 14.7550], [40.6700, 14.7600], [40.6680, 14.7650],
        [40.6650, 14.7700], [40.6620, 14.7650], [40.6600, 14.7600], [40.6620, 14.7550],
        [40.6630, 14.7500], [40.6630, 14.7450], [40.6680, 14.7400], [40.6745, 14.7519]
    ]
];

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

    $query = "SELECT b.targa, b.id_faro FROM boats b WHERE b.id_faro IS NOT NULL";
    $result = pg_query($conn, $query);
    if (!$result) {
        echo "[" . date('H:i:s') . "] Errore nella query barche.\n";
        pg_close($conn);
        sleep(3);
        continue;
    }

    while ($row = pg_fetch_assoc($result)) {
        $targa = $row['targa'];
        $id_faro = (int)$row['id_faro'];

        if (!isset($routes[$id_faro])) {
            echo "[" . date('H:i:s') . "] [$targa] Faro $id_faro senza rotta definita.\n";
            continue;
        }

        $route = $routes[$id_faro];
        $noisyRoute = $route;
        for ($i = 1; $i < count($route); $i++) {
            $noiseLat = (mt_rand() / mt_getrandmax() - 0.5) * 0.0002;
            $noiseLon = (mt_rand() / mt_getrandmax() - 0.5) * 0.0002;
            $noisyRoute[$i][0] += $noiseLat;
            $noisyRoute[$i][1] += $noiseLon;
        }

        $posQuery = pg_query_params($conn,
            "SELECT id_rotta FROM boats_current_position WHERE targa_barca = $1",
            [$targa]
        );

        $id_rotta = 0;
        if ($posQuery && pg_num_rows($posQuery) > 0) {
            $rowPos = pg_fetch_assoc($posQuery);
            $id_rotta = (int)$rowPos['id_rotta'];
        }

        if (!isset($noisyRoute[$id_rotta])) {
            echo "[" . date('H:i:s') . "] [$targa] Punto rotta mancante per id_rotta $id_rotta\n";
            continue;
        }

        $point = $noisyRoute[$id_rotta];
        $newLat = $point[0];
        $newLon = $point[1];
        $now = date('Y-m-d H:i:s');

        $insertHist = pg_query_params($conn,
            "INSERT INTO boats_position (targa_barca, lat, lon, ts) VALUES ($1, $2, $3, $4)",
            [$targa, $newLat, $newLon, $now]
        );

        if (!$insertHist) {
            echo "[" . date('H:i:s') . "] [$targa] Errore inserimento storico: " . pg_last_error($conn) . "\n";
            continue;
        }

        $next_rotta = ($id_rotta + 1) % count($route);

        $check = pg_query_params($conn,
            "SELECT 1 FROM boats_current_position WHERE targa_barca = $1",
            [$targa]
        );

        if (pg_num_rows($check) > 0) {
            $update = pg_query_params($conn,
                "UPDATE boats_current_position 
                 SET lat = $2, lon = $3, ts = $4, id_rotta = $5 
                 WHERE targa_barca = $1",
                [$targa, $newLat, $newLon, $now, $next_rotta]
            );
            if ($update) {
                echo "[" . date('H:i:s') . "] [$targa] Aggiornata posizione: $newLat, $newLon (id_rotta $next_rotta)\n";
            } else {
                echo "[" . date('H:i:s') . "] [$targa] Errore UPDATE posizione corrente: " . pg_last_error($conn) . "\n";
            }
        } else {
            $insert = pg_query_params($conn,
                "INSERT INTO boats_current_position (targa_barca, lat, lon, ts, id_rotta) 
                 VALUES ($1, $2, $3, $4, $5)",
                [$targa, $newLat, $newLon, $now, $next_rotta]
            );
            if ($insert) {
                echo "[" . date('H:i:s') . "] [$targa] Inserita posizione iniziale (faro): $newLat, $newLon\n";
            } else {
                echo "[" . date('H:i:s') . "] [$targa] Errore INSERT posizione corrente: " . pg_last_error($conn) . "\n";
            }
        }
    }

    pg_close($conn);
    sleep(3);
}