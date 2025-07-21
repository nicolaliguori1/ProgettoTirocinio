<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'connessione.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $lat = floatval($_POST["latitudine"]);
    $lon = floatval($_POST["longitudine"]);

    $query = "INSERT INTO fari (nome, lat, lon) VALUES ($1, $2, $3)";
    $prep_name = "insert_faro";

    // Prepara ed esegui
    pg_prepare($conn, $prep_name, $query);
    $result = pg_execute($conn, $prep_name, array($nome, $lat, $lon));

    if ($result) {
        echo "<p>✅ Faro aggiunto con successo!</p>";
    } else {
        echo "<p>❌ Errore durante l'inserimento: " . pg_last_error($conn) . "</p>";
    }
}
?>



<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="addFaro.css">
    <title>Aggiungi Nuovo Faro</title>
</head>
<body>
    <form method="POST" action="">
    <h2>Aggiungi un Nuovo Faro</h2>
        <label>Nome</label>
        <input type="text" name="nome" required>

        <label>Latitudine</label>
        <input type="text"  name="latitudine" required>

        <label>Longitudine</label>
        <input type="text" name="longitudine" required>

        <input type="submit" value="Aggiungi Faro">
    </form>
</body>
</html>
