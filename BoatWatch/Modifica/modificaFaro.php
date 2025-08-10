<?php
session_start();
include __DIR__ . '/../../connessione.php';

if (!isset($_GET["id"])) {
    die("ID faro mancante.");
}

$id_faro = intval($_GET["id"]);

// Se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $lat = floatval($_POST["latitudine"]);
    $lon = floatval($_POST["longitudine"]);

    $sql = "UPDATE fari SET nome = $1, lat = $2, lon = $3 WHERE id = $4";
    pg_prepare($conn, "update_faro", $sql);
    $result = pg_execute($conn, "update_faro", [$nome, $lat, $lon, $id_faro]);

    if ($result) {
        header("Location: ../Elenco/elencoFari.php");
        exit();
    } else {
        die("Errore aggiornamento faro: " . pg_last_error($conn));
    }
}

// Recupera dati attuali del faro
$sql = "SELECT * FROM fari WHERE id = $1";
pg_prepare($conn, "get_faro", $sql);
$res = pg_execute($conn, "get_faro", [$id_faro]);
$faro = pg_fetch_assoc($res);

if (!$faro) {
    die("Faro non trovato.");
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Faro</title>
    <link rel="stylesheet" href="modifica.css?v=2">
 
</head>
<body>
<div class="container">
    <form method="POST" action="">
    <?php
        include "../header.php"
        ?>
    <h2>Modifica Faro</h2>
        <label>Nome</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($faro["nome"]) ?>" required>

        <label>Latitudine</label>
        <input type="number" step="any" name="latitudine" value="<?= htmlspecialchars($faro["lat"]) ?>" required>

        <label>Longitudine</label>
        <input type="number" step="any" name="longitudine" value="<?= htmlspecialchars($faro["lon"]) ?>" required>

        <input type="submit" value="Salva modifiche">
    </form>
</div>
</body>
</html>
