<?php
require_once __DIR__ . '/../connessione.php';

function getFaroById($conn, int $id): ?array {
    $res = pg_query_params($conn, "SELECT * FROM fari WHERE id = $1", [$id]);
    return ($res && pg_num_rows($res) > 0) ? pg_fetch_assoc($res) : null;
}

function getFaroLastPosition($conn, int $id): ?array {
    $res = pg_query_params($conn, "
        SELECT lat, lon, ts
        FROM fari_position
        WHERE id_faro = $1
        ORDER BY ts DESC
        LIMIT 1
    ", [$id]);
    return ($res && pg_num_rows($res) > 0) ? pg_fetch_assoc($res) : null;
}

function getFaroData($conn, int $id): ?array {
    $last_pos = getFaroLastPosition($conn, $id);

    if (!$last_pos) {
        $faro = getFaroById($conn, $id);
        if (!$faro) return null;

        return [
            "lat" => $faro['lat'] ?? null,
            "lon" => $faro['lon'] ?? null,
            "stato" => "inattivo"
        ];
    }

    $now = time();
    $last_ts = strtotime($last_pos['ts']);
    $stato = ($now - $last_ts <= 60) ? "attivo" : "inattivo";

    return [
        "lat" => $last_pos['lat'],
        "lon" => $last_pos['lon'],
        "stato" => $stato,
        "ts" => $last_pos['ts']
    ];
}
?>