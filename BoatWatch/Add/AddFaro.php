<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../../connessione.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $lat = floatval($_POST["latitudine"]);
    $lon = floatval($_POST["longitudine"]);
    $tolleranza = 0.01; // 0.01 ~ 1.1 km (approx)

    // Query con confronto tolleranza
    $check_query = "
        SELECT 1 FROM fari 
        WHERE ABS(lat - $1) < $3 
          AND ABS(lon - $2) < $3
        LIMIT 1
    ";
    $check_pos = "check_faro";

    // Prepara ed esegui query
    pg_prepare($conn, $check_pos, $check_query);
    $check_result = pg_execute($conn, $check_pos, array($lat, $lon, $tolleranza));
    
    $check_faro = pg_query_params($conn, "SELECT 1 FROM fari WHERE  nome= $1", array($nome));
    
    if (pg_num_rows($check_faro) > 0) {
        $errorMessage = "Esiste già un faro con questo nome.";
    }else if(pg_num_rows($check_result) > 0) {
        $errorMessage = "Faro con coordinate simili già esistente.";
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
    <link rel="stylesheet" href="add.css?v=2">
    <link rel="stylesheet" href="../alert.css">
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

    <form method="POST" action="">
    <h2>Aggiungi un Nuovo Faro</h2>
        <label>Nome</label>
        <input type="text" name="nome" required>

        <label>Latitudine</label>
        <input type="number" step="any" name="latitudine" value="<?= htmlspecialchars($faro["lat"]) ?>" required>
        <div style="margin-top: 20px;">
        <label>Longitudine</label>
</div>
        <input type="number" step="any" name="longitudine" value="<?= htmlspecialchars($faro["lon"]) ?>" required>
        <div style="margin-top: 30px;">
        <input type="submit" value="Aggiungi Faro">
</div>
    </form>
</body>
</html>