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
        [40.2500, 14.9000], [40.2502, 14.8990], [40.2504, 14.8950], [40.2506, 14.8850],
        [40.2508, 14.8800], [40.2510, 14.8750], [40.2512, 14.8700], [40.2514, 14.8650],
        [40.2515, 14.8600], [40.2513, 14.8550], [40.2510, 14.8500], [40.2506, 14.8450],
        [40.2500, 14.8420], [40.2493, 14.8400], [40.2486, 14.8420], [40.2480, 14.8450],
        [40.2475, 14.8500], [40.2472, 14.8550], [40.2471, 14.8600], [40.2473, 14.8650],
        [40.2476, 14.8700], [40.2480, 14.8750], [40.2485, 14.8800], [40.2490, 14.8850],
        [40.2495, 14.8900], [40.2500, 14.8950], [40.2504, 14.8980], [40.2507, 14.8990],
        [40.2500, 14.9000]
    ]
];



while (true) {
    $status = @file_get_contents($status_file);
    if (trim($status) !== 'on') {
        echo "[" . date('H:i:s') . "] Simulazione ferma, aspetto...\n";
        sleep(3);
        continue;
    }

    
    $boatsResult = pg_query($conn, "SELECT targa, id_faro FROM boats WHERE id_faro IS NOT NULL");
    if (!$boatsResult) {
        echo "[" . date('H:i:s') . "] Errore query barche: " . pg_last_error($conn) . "\n";
        sleep(3);
        continue;
    }

    $boats = [];
    while ($row = pg_fetch_assoc($boatsResult)) {
        $boats[$row['targa']] = $row['id_faro'];
    }

    if (empty($boats)) {
        sleep(3);
        continue;
    }

    
    
    $positions = [];
    $posQuery = pg_query($conn,
        "SELECT targa_barca, lat, lon, id_rotta 
        FROM boats_current_position"
    );

    if ($posQuery) {
        while ($row = pg_fetch_assoc($posQuery)) {
            $positions[$row['targa_barca']] = $row;
        }
    }


    foreach ($boats as $targa => $id_faro) {
        if (!isset($routes[$id_faro])) {
            echo "[" . date('H:i:s') . "] [$targa] Faro $id_faro senza rotta definita.\n";
            continue;
        }

        $route = $routes[$id_faro];

        
        $id_rotta = $positions[$targa]['id_rotta'] ?? 0;

        if (!isset($route[$id_rotta])) {
            echo "[" . date('H:i:s') . "] [$targa] Punto rotta mancante per id_rotta $id_rotta\n";
            continue;
        }

        $point = $route[$id_rotta];
        $newLat = $point[0] + ((mt_rand() / mt_getrandmax() - 0.5) * 0.0002);
        $newLon = $point[1] + ((mt_rand() / mt_getrandmax() - 0.5) * 0.0002);

        
        pg_query_params($conn,
            "INSERT INTO boats_position (targa_barca, lat, lon, ts) VALUES ($1, $2, $3, NOW())",
            [$targa, $newLat, $newLon]
        );

        $next_rotta = ($id_rotta + 1) % count($route);

        
        $update = pg_query_params($conn,
            "UPDATE boats_current_position 
             SET lat=$2, lon=$3, ts=NOW(), id_rotta=$4 
             WHERE targa_barca=$1",
            [$targa, $newLat, $newLon, $next_rotta]
        );

        if (pg_affected_rows($update) === 0) {
            pg_query_params($conn,
                "INSERT INTO boats_current_position (targa_barca, lat, lon, ts, id_rotta) 
                 VALUES ($1, $2, $3, NOW(), $4)",
                [$targa, $newLat, $newLon, $next_rotta]
            );
            echo "[" . date('H:i:s') . "] [$targa] Inserita posizione iniziale: $newLat, $newLon\n";
        } else {
            echo "[" . date('H:i:s') . "] [$targa] Aggiornata posizione: $newLat, $newLon (id_rotta $next_rotta)\n";
        }
    }

    sleep(3);
}

pg_close($conn);
