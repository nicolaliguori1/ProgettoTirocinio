<?php

function getFaroData($conn, $id) {
    $stmt = $conn->prepare('SELECT id, nome, lat, lon, stato, ts FROM fari WHERE id = $1');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function getFaroById($conn, $id) {
    return getFaroData($conn, $id);
}
?>
