<?php
require __DIR__ . '/../connessione.php';

// Recupera il nome della barca
function getBoatName($conn, $targa) {
    $query = "SELECT nome FROM boats WHERE targa = $1";
    $res = pg_query_params($conn, $query, [$targa]);
    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        return $row['nome'] ?? null;
    }
    return null;
}

// Recupera la posizione del faro associato a una barca
function getFaroPosition($conn, $targa) {
    $query = "SELECT f.lat, f.lon
              FROM boats b
              JOIN fari f ON b.id_faro = f.id
              WHERE b.targa = $1";
    $res = pg_query_params($conn, $query, [$targa]);
    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        if (is_numeric($row['lat']) && is_numeric($row['lon'])) {
            return [
                'lat' => floatval($row['lat']),
                'lon' => floatval($row['lon'])
            ];
        }
    }
    return null;
}

// Recupera la posizione live della barca (se non c’è, ritorna il faro)
function getLivePosition($conn, $targa) {
    $res = pg_query_params(
        $conn,
        "SELECT ts, lat, lon, id_rotta 
         FROM boats_current_position 
         WHERE targa_barca = $1",
        [$targa]
    );
    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        if (isset($row['lat'], $row['lon']) && is_numeric($row['lat']) && is_numeric($row['lon'])) {
            return [
                'ts' => $row['ts'],
                'lat' => floatval($row['lat']),
                'lon' => floatval($row['lon']),
                'id_rotta' => intval($row['id_rotta'])
            ];
        }
    }

    // fallback → usa il faro
    $faro = getFaroPosition($conn, $targa);
    if ($faro) {
        return [
            'ts' => null,
            'lat' => $faro['lat'],
            'lon' => $faro['lon'],
            'id_rotta' => 0
        ];
    }

    return ['ts' => null, 'lat' => null, 'lon' => null, 'id_rotta' => 0];
}

// Recupera lo storico (ultimi 10 record)
function getBoatHistory($conn, $targa) {
    $query = "SELECT ts, lat, lon 
              FROM boats_position 
              WHERE targa_barca = $1 
              ORDER BY ts DESC 
              LIMIT 10";
    $res = pg_query_params($conn, $query, [$targa]);
    $history = [];
    if ($res && pg_num_rows($res) > 0) {
        while ($row = pg_fetch_assoc($res)) {
            $history[] = [
                'ts' => $row['ts'],
                'lat' => floatval($row['lat']),
                'lon' => floatval($row['lon'])
            ];
        }
    }
    return $history;
}

// Determina lo stato della barca (porto/mare)
function getBoatStatus($conn, $targa) {
    $live = getLivePosition($conn, $targa);
    if (!$live || $live['id_rotta'] === 0) {
        return "Nel porto";
    }
    return "In mare";
}

// Calcolo distanza (Haversine)
function distanzaHaversine($lat1, $lon1, $lat2, $lon2) {
    $R = 6371000; // raggio della Terra in metri
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $R * $c;
}
?>
