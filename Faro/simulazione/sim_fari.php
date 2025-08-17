<?php
require __DIR__ . '/../../connessione.php'; 

function inviaPosizioni($conn) {
    $result = pg_query($conn, "SELECT id, lat, lon FROM fari");
    if (!$result) {
        echo "Errore query SELECT: " . pg_last_error($conn) . "\n";
        return;
    }

    $fari = pg_fetch_all($result);
    if (!$fari) {
        echo "Nessun faro trovato.\n";
        return;
    }

    foreach ($fari as $faro) {
        $res = pg_query_params(
            $conn,
            "INSERT INTO fari_position (id_faro, lat, lon, ts) VALUES ($1, $2, $3, NOW())",
            [$faro['id'], $faro['lat'], $faro['lon']]
        );

        if ($res) {
            echo "Faro {$faro['id']} aggiornato con lat={$faro['lat']} lon={$faro['lon']}\n";
        } else {
            echo "Errore inserimento faro {$faro['id']}: " . pg_last_error($conn) . "\n";
        }
    }
}

while (true) {
    inviaPosizioni($conn);
    sleep(60); // attesa 60 secondi
}

pg_close($conn);
