<?php
// Avvio della sessione
session_start();
require_once 'connessione.php';

// Verifica se è stato passato un parametro "barca"
if (!isset($_GET['barca'])) || !is_numeric($_GET['barca']) {
    die("Errore: nessuna barca selezionata.");
}

// Recupera l'ID della barca dall'URL e lo rende intero
$barca_id = (int) $_GET['barca'];
// Prepara e esegue la query per ottenere la barca
if (pg_num_rows($result) === 0) {
    die("Errore: barca non trovata.");
}
// Dati della barca
$barca = pg_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?php echo htmlspecialchars($barca['nome']); ?></title>
</head>

</html>