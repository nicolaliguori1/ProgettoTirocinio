<?php
// Avvio della sessione
session_start();
require_once 'connessione.php';

// Verifica se è stato passato un parametro "barca"
if (!isset($_GET['barca']) || !is_numeric($_GET['barca'])) {
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
<body>
<div class="sezione">
    <h1></h1>
    <div class="box-wrapper">
      <div class="box">
        <h2><strong>Targa</strong></h2>
        <h2></h2>
      </div>
      <div class="box">
        <h2><strong>Proprietario</strong></h2>
        <h2></h2>
      </div>
    </div>
  </div>

  <div class="sezione">
    <h1>Posizione Barca</h1>
    <div class="box-wrapper">
      <div class="box">
        <h2>Latitudine</h2>
        <h2></h2>
      </div>
      <div class="box">
        <h2>Longitudine</h2>
        <h2></h2>
      </div>
    </div>
  </div>

  <div class="sezione">
    <h1>INFORMAZIONI MOLO</h1>
    <div class="box-wrapper">
      <div class="box">
        <h2>Nome Faro</h2>
        <h2></h2>
      </div>
      <div class="box">
        <h2>Distanza Dal Faro</h2>
        <h2></h2>
      </div>
      <div class="box">
        <h2></h2>
      </div>
    </div>
  </div>

  <div class="sezione">
    <h1>Storico Recente</h1>
    <div class="storico">
      <ul id="storico-list">
        <!-- JS inserirà qui gli elementi -->
      </ul>
    </div>    
  </div>
</body>
</html>