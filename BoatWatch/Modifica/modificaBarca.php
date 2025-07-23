<?php
session_start();
include __DIR__ . '/../../connessione.php';

// Verifica se l'utente è loggato
if (!isset($_SESSION["id"])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION["id"];

// Recupera la targa della barca da modificare
if (!isset($_GET["targa"])) {
    die("Targa barca mancante.");
}

$targa = $_GET["targa"];

// Se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $lunghezza = intval($_POST["lunghezza"]);
    $nuova_targa = $_POST["targa"];
    $id_faro = intval($_POST["id_faro"]);
    $targa_originale = $_POST["targa_originale"];

    $sql = "UPDATE boats 
            SET nome = $1, lunghezza = $2, targa = $3, id_faro = $4 
            WHERE targa = $5 AND id_user = $6";
    $prep_name = "update_boat";

    pg_prepare($conn, $prep_name, $sql);
    $result = pg_execute($conn, $prep_name, [$nome, $lunghezza, $nuova_targa, $id_faro, $targa_originale, $user_id]);

    if ($result) {
        header("Location: ../Elenco/elencoBarche.php");
        exit();
    } else {
        die("❌ Errore aggiornamento: " . pg_last_error($conn));
    }
}

// Recupera i dati attuali della barca
$sql = "SELECT * FROM boats WHERE targa = $1 AND id_user = $2";
pg_prepare($conn, "get_boat", $sql);
$res = pg_execute($conn, "get_boat", [$targa, $user_id]);
$boat = pg_fetch_assoc($res);

if (!$boat) {
    die("Barca non trovata o accesso negato.");
}

// Fari menu a tendina
$fari = [];
$query = "SELECT id, nome FROM fari ORDER BY nome";
$result = pg_query($conn, $query);

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $fari[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Barca</title>
    <link rel="stylesheet" href="../Add/add.css?v=2">
</head>
<body>
    <form method="POST" action="">
    <h2>Modifica Barca</h2>
        <label>Nome</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($boat["nome"]) ?>" required>

        <label>Lunghezza (metri)</label>
        <input type="number" step="0.01" name="lunghezza" value="<?= htmlspecialchars($boat["lunghezza"]) ?>" required>

        <label>Targa</label>
        <input type="text" name="targa" value="<?= htmlspecialchars($boat["targa"]) ?>" required>

        <label>ID Faro</label>
        <select name="id_faro" id="id_faro" required>
            <option value=""></option>
            <?php foreach ($fari as $faro): ?>
                <option value="<?= htmlspecialchars($faro['id']) ?>">
                    <?= htmlspecialchars($faro['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="hidden" name="targa_originale" value="<?= htmlspecialchars($boat["targa"]) ?>">

        <input type="submit" value="Salva modifiche">
    </form>
</body>
</html>
