<?php
session_start();
include 'connessione.php';

// Controllo login opzionale

if (!isset($_GET["id"])) {
    die("ID faro mancante.");
}

$id_faro = intval($_GET["id"]);

// Elimina il faro dal DB
$sql = "DELETE FROM fari WHERE id = $1";
pg_prepare($conn, "delete_faro", $sql);
$result = pg_execute($conn, "delete_faro", [$id_faro]);

if ($result) {
    header("Location: elencoFari.php");
    exit();
} else {
    die("Errore durante l'eliminazione: " . pg_last_error($conn));
}
?>
