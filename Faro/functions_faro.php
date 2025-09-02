<?php

function getFaroData($conn, int $id): ?array {
    
    $sql = "SELECT lat, lon, ts
            FROM fari_position
            WHERE id_faro = $1
            ORDER BY ts DESC
            LIMIT 1";
    $res = pg_query_params($conn, $sql, [$id]);

    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        return [
            'lat'   => $row['lat'],
            'lon'   => $row['lon'],
            'ts'    => $row['ts'],
            'stato' => null 
        ];
    }

   
    $sql = "SELECT lat, lon FROM fari WHERE id = $1";
    $res = pg_query_params($conn, $sql, [$id]);

    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        return [
            'lat'   => $row['lat'],
            'lon'   => $row['lon'],
            'ts'    => null,
            'stato' => 'inattivo'
        ];
    }

    
    return null;
}


function getFaroById($conn, int $id): ?array {
    $sql = "SELECT * FROM fari WHERE id = $1";
    $res = pg_query_params($conn, $sql, [$id]);
    if ($res && pg_num_rows($res) > 0) {
        return pg_fetch_assoc($res);
    }
    return null;
}


function calcolaStatoFaro(?array $faroData, int $timeout): string {
    if (!$faroData || empty($faroData['ts'])) {
        return 'inattivo';
    }

    $ts = strtotime($faroData['ts']);
    if ($ts && (time() - $ts) <= $timeout) {
        return 'attivo';
    }

    return 'inattivo';
}
