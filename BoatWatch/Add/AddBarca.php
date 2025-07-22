<?php
session_start();
include __DIR__ . '/../../connessione.php';

// Verifica se l'utente è loggato
if (!isset($_SESSION["id"])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $lunghezza = floatval($_POST["lunghezza"]);
    $targa = $_POST["targa"];
    $id_faro = intval($_POST["id_faro"]);

    // Query di inserimento
    $sql = "INSERT INTO boats (nome, lunghezza, targa, id_faro, id_user) VALUES ($1, $2, $3, $4, $5)";
    $prep_name = "insert_boat";

    // Prepara ed esegui
    pg_prepare($conn, $prep_name, $sql);
    $result = pg_execute($conn, $prep_name, array($nome, $lunghezza, $targa, $id_faro, $user_id));

    if ($result) {
        // ✅ Redirect alla pagina elencoBarche.php
        header("Location: /TiroBarca/BoatWatch/Elenco/elencoBarche.php");
        exit();
    } else {
        die("<p>❌ Errore durante l'inserimento: " . pg_last_error($conn) . "</p>");
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="add.css?v=2">
    <title>Aggiungi Nuova Barca</title>

</head>
<body>
   
    <form method="POST" action="">
    <h2>Aggiungi una Nuova Barca</h2>
        <label>Nome</label>
        <input type="text" name="nome" required>

        <label>Lunghezza (metri)</label>
        <input type="number" step="0.01" name="lunghezza" required>

        <label>Targa</label>
        <input type="text" name="targa" required>

        <label>Id faro</label>
        <input type="text" name="id_faro" required>

        <input type="submit" value="Aggiungi Barca">
    </form>
</body>
</html>
