<?php
session_start();
include __DIR__ . '/../../connessione.php';

if (!isset($_SESSION["id"])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION["id"];
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $lunghezza = floatval($_POST["lunghezza"]);
    $targa = $_POST["targa"];
    $id_faro = intval($_POST["id_faro"]);

    // Controllo targa duplicata
    $check_targa = pg_query_params($conn, "SELECT 1 FROM boats WHERE targa = $1", array($targa));
    if (pg_num_rows($check_targa) > 0) {
        $errorMessage = "Esiste già una barca con questa targa.";
    } else {
        // Controllo faro esistente
        $check_faro = pg_query_params($conn, "SELECT 1 FROM fari WHERE id = $1", array($id_faro));
        if (pg_num_rows($check_faro) === 0) {
            $errorMessage = "Il faro associato non esiste.";
        } else {
            // Inserimento
            $sql = "INSERT INTO boats (nome, lunghezza, targa, id_faro, id_user) VALUES ($1, $2, $3, $4, $5)";
            $prep_name = "insert_boat";

            pg_prepare($conn, $prep_name, $sql);
            $result = pg_execute($conn, $prep_name, array($nome, $lunghezza, $targa, $id_faro, $user_id));

            if ($result) {
                header("Location: ../Elenco/elencoBarche.php");
                exit();
            } else {
                $errorMessage = "Errore durante l'inserimento: " . pg_last_error($conn);
            }
        }
    }
}
?>

<?php
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
    <title>Aggiungi Barca</title>
    <link rel="stylesheet" href="../Add/add.css">
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

<form method="post">
    <h2>Aggiungi Barca</h2>

    <label for="nome">Nome</label>
    <input type="text" name="nome" required>

    <label for="lunghezza">Lunghezza</label>
    <input type="number" name="lunghezza" required>

    <label for="targa">Targa</label>
    <input type="text" name="targa" required>

    <label for="id_faro">ID Faro</label>
    <select name="id_faro" id="id_faro" required>
        <option value=""></option>
        <?php foreach ($fari as $faro): ?>
            <option value="<?= htmlspecialchars($faro['id']) ?>">
                <?= htmlspecialchars($faro['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div style="margin-top: 30px;">
    <input type="submit" value="Aggiungi">
        </div>
</form>
</body>
</html>
