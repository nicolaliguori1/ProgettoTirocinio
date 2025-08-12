<?php
session_start();
include __DIR__ . '/../../connessione.php';

if (!isset($_GET["id"])) {
    die("ID faro mancante.");
}

$id_faro = intval($_GET["id"]);

$sql = "SELECT * FROM fari WHERE id = $1";
pg_prepare($conn, "get_faro", $sql);
$res = pg_execute($conn, "get_faro", [$id_faro]);
$faro = pg_fetch_assoc($res);

if (!$faro) {
    die("Faro non trovato.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $lat = floatval($_POST["latitudine"]);
    $lon = floatval($_POST["longitudine"]);

    $check_nome = pg_query_params($conn, "SELECT 1 FROM fari WHERE nome = $1 AND id <> $2", array($nome, $id_faro));

    $check_coord = pg_query_params($conn, "SELECT 1 FROM fari WHERE lat = $1 AND lon = $2 AND id <> $3", array($lat, $lon, $id_faro));

    if (pg_num_rows($check_nome) > 0) {
        $errorMessage = "Esiste già un faro con questo nome.";
    } elseif (pg_num_rows($check_coord) > 0) {
        $errorMessage = "Esiste già un faro con queste coordinate.";
    } else {
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
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Faro</title>
    <link rel="stylesheet" href="modifica.css?v=2">
    <link rel="stylesheet" href="../alert.css">

</head>
<body>

<?php if (!empty($errorMessage)): ?>
<div class="popup-overlay">
    <div class="popup">
        <p><?= htmlspecialchars($errorMessage) ?></p>
        <button onclick="closePopup()">OK</button>
    </div>
</div>
<script>
    function closePopup() {
        document.querySelector('.popup-overlay').style.display = 'none';
    }
</script>
<?php endif; ?>

<div class="container">
    <form method="POST" action="">
        <?php include "../header.php" ?>
        <h2>Modifica Faro</h2>

        <label>Nome</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($faro["nome"]) ?>" required>

        <label>Latitudine</label>
        <input type="number" step="any" name="latitudine" value="<?= htmlspecialchars($faro["lat"]) ?>" required>

        <label>Longitudine</label>
        <input type="number" step="any" name="longitudine" value="<?= htmlspecialchars($faro["lon"]) ?>" required>

        <div class="conferma">
            <input type="submit" value="Salva modifiche">
            <a href="../Elenco/elencoFari.php" class="btn-back">Torna all'elenco</a>
        </div>
    </form>
</div>
</body>
</html>
