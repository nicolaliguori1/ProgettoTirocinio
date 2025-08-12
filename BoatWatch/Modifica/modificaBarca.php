<?php
session_start();
include __DIR__ . '/../../connessione.php';

if (!isset($_SESSION["id"])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION["id"];

if (!isset($_GET["targa"])) {
    die("Targa barca mancante.");
}

$targa = $_GET["targa"];
$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $lunghezza = intval($_POST["lunghezza"]);
    $nuova_targa = $_POST["targa"];
    $id_faro = intval($_POST["id_faro"]);
    $targa_originale = $_POST["targa_originale"];

    $check_sql = "SELECT 1 FROM boats WHERE targa = $1 AND targa <> $2";
    $check_res = pg_query_params($conn, $check_sql, [$nuova_targa, $targa_originale]);

    if (pg_num_rows($check_res) > 0) {
        $errore = "❌ La targa inserita è già assegnata a un'altra barca.";
    } else {
        // Aggiornamento dati
        $sql = "UPDATE boats 
                SET nome = $1, lunghezza = $2, targa = $3, id_faro = $4 
                WHERE targa = $5 AND id_user = $6";
        pg_prepare($conn, "update_boat", $sql);
        $result = pg_execute($conn, "update_boat", [$nome, $lunghezza, $nuova_targa, $id_faro, $targa_originale, $user_id]);

        if ($result) {
            header("Location: ../Elenco/elencoBarche.php");
            exit();
        } else {
            $errore = "❌ Errore aggiornamento: " . pg_last_error($conn);
        }
    }
}

$sql = "SELECT * FROM boats WHERE targa = $1 AND id_user = $2";
pg_prepare($conn, "get_boat", $sql);
$res = pg_execute($conn, "get_boat", [$targa, $user_id]);
$boat = pg_fetch_assoc($res);

if (!$boat) {
    die("Barca non trovata o accesso negato.");
}

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
    <link rel="stylesheet" href="modifica.css?v=4">
    <link rel="stylesheet" href="../alert.css"> 
    <script>
        function showPopup(message) {
            const overlay = document.getElementById('popup-overlay');
            const text = document.getElementById('popup-text');
            text.textContent = message;
            overlay.style.display = 'flex';
            document.getElementById('popup-close').focus();
        }
        function hidePopup() {
            document.getElementById('popup-overlay').style.display = 'none';
        }
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('popup-close').addEventListener('click', hidePopup);
            document.getElementById('popup-overlay').addEventListener('click', (e) => {
                if (e.target.id === 'popup-overlay') hidePopup();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') hidePopup();
            });

            <?php if (!empty($errore)): ?>
                showPopup(<?= json_encode($errore) ?>);
            <?php endif; ?>
        });
    </script>
</head>
<body>
<div class="container">
    <form method="POST" action="">
        <?php include "../header.php" ?>
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
                <option value="<?= htmlspecialchars($faro['id']) ?>" <?= $boat['id_faro'] == $faro['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($faro['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="hidden" name="targa_originale" value="<?= htmlspecialchars($boat["targa"]) ?>">
        
        <div class="conferma">
            <input type="submit" value="Salva modifiche">
            <a href="../Elenco/elencoBarche.php" class="btn-back">Torna all'elenco</a>
        </div>


    </form>
</div>

<div id="popup-overlay" class="popup-overlay" style="display:none;">
    <div class="popup" role="alertdialog" aria-modal="true">
        <p id="popup-text"></p>
        <button id="popup-close" type="button">OK</button>
    </div>
</div>

</body>
</html>
