<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../../connessione.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $lat = floatval($_POST["latitudine"]);
    $lon = floatval($_POST["longitudine"]);

    // Controllo se esiste già un faro con lo stesso nome
    $check_nome = pg_query_params($conn, "SELECT 1 FROM fari WHERE nome = $1", array($nome));

    // Controllo se esiste già un faro con stesse coordinate
    $check_coord = pg_query_params($conn, "SELECT 1 FROM fari WHERE lat = $1 AND lon = $2", array($lat, $lon));

    if (pg_num_rows($check_nome) > 0) {
        $errorMessage = "Esiste già un faro con questo nome.";
    } elseif (pg_num_rows($check_coord) > 0) {
        $errorMessage = "Esiste già un faro con queste coordinate.";
    } else {
        $query = "INSERT INTO fari (nome, lat, lon) VALUES ($1, $2, $3)";
        $prep_name = "insert_faro";

        pg_prepare($conn, $prep_name, $query);
        $result = pg_execute($conn, $prep_name, array($nome, $lat, $lon));

        if ($result) {
            header("Location: ../Elenco/elencoFari.php");
            exit();
        } else {
            echo "<p>❌ Errore durante l'inserimento: " . pg_last_error($conn) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../Add/add.css?v=3">
    <link rel="stylesheet" href="../alert.css?v=2">
    <title>Aggiungi Nuovo Faro</title>
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
        <h2>Aggiungi un Nuovo Faro</h2>

        <label>Nome</label>
        <input type="text" name="nome" required>

        <label>Latitudine</label>
        <input type="number" step="any" name="latitudine" required>

        <label>Longitudine</label>
        <input type="number" step="any" name="longitudine" required>

        <div style="margin-top: 30px;">
        <div class="conferma">
            <input type="submit" value="Aggiungi faro">
            <a href="../Elenco/elencoFari.php" class="btn-back">Torna all'elenco</a>
        </div>
</div>
    </form>
</div>
</body>
</html>
