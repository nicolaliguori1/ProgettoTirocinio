<?php
// Avvio della sessione
session_start();
require_once 'connessione.php';

// Verifica se è stato passato un parametro "faro"
if (!isset($_GET['faro'])) || !is_numeric($_GET['faro']) {
    die("Errore: nessun faro selezionato.");
}

// Recupera l'ID della barca dall'URL e lo rende intero
$faro_id = (int) $_GET['faro'];
// Prepara e esegue la query per ottenere la barca
if (pg_num_rows($result) === 0) {
    die("Errore: faro non trovato.");
}
// Dati della barca
$faro = pg_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?php echo htmlspecialchars($faro['nome']); ?></title>
</head>
</html>