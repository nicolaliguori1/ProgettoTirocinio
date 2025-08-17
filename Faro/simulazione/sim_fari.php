<?php

require __DIR__ . '/../../connessione.php';

function getFari($conn) {
    $result = pg_query($conn, "SELECT id, lat, lon FROM fari");
    return pg_fetch_all($result);
}

function inviaPosizioni($conn) {
    $fari = getFari($conn);
    if (!$fari) {
        echo "Nessun faro trovato.\n";
        return;
    }

    foreach ($fari as $faro) {
        $id_faro = $faro['id'];
        $lat = $faro['lat'];
        $lon = $faro['lon'];

        $query = "INSERT INTO fari_position (id_faro, lat, lon, ts) VALUES ($1, $2, $3, NOW())";
        $res = pg_query_params($conn, $query, [$id_faro, $lat, $lon]);

        if ($res) {
            echo "[" . date("Y-m-d H:i:s") . "] Faro $id_faro -> $lat, $lon\n";
        } else {
            echo "Errore inserimento per faro $id_faro: " . pg_last_error($conn) . "\n";
        }
    }
}

while (true) {
    inviaPosizioni($conn);
    sleep(60);
}
?>
