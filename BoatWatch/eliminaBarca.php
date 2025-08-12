<?php
session_start();
include './connessione.php';

if (!isset($_SESSION["id"])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION["id"];

$targa = $_GET["targa"] ?? "";

if (empty($targa)) {
    die("Targa barca non valida.");
}

$sql = "DELETE FROM boats WHERE targa = $1 AND id_user = $2";
pg_prepare($conn, "delete_boat", $sql);
$result = pg_execute($conn, "delete_boat", [$targa, $user_id]);

if ($result) {
    header("Location: elencoBarche.php");
    exit();
} else {
    die("Errore durante l'eliminazione: " . pg_last_error($conn));
}

