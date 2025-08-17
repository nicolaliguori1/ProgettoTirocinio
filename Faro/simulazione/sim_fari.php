<?php
require __DIR__ . '/../../connessione.php';

function inviaPosizioni($conn) {
    $fari = pg_fetch_all(pg_query($conn, "SELECT id, lat, lon FROM fari"));
    if (!$fari) return;

    foreach ($fari as $faro) {
        $res = pg_query_params(
            $conn,
            "INSERT INTO fari_position (id_faro, lat, lon, ts) VALUES ($1, $2, $3, NOW())",
            [$faro['id'], $faro['lat'], $faro['lon']]
        );

        if (!$res) {
            echo "Errore inserimento faro {$faro['id']}: " . pg_last_error($conn) . "\n";
        }
    }
}

while (true) {
    inviaPosizioni($conn);
    sleep(60);
}
?>
