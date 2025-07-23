<?php
$status_file = __DIR__ . '/sim_status.txt';
$current_status = trim(@file_get_contents($status_file)) ?: 'off';
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<link rel="stylesheet" href="control.css?v=2">
<title>Controllo Simulazione GPS</title>
<script>
function setStatus(status) {
    fetch('set_sim_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'status=' + encodeURIComponent(status)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            document.getElementById('status-text').textContent = data.status;
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(err => alert('Errore nella richiesta: ' + err));
}
</script>
</head>
<body>
marcogay
  <div class="sim-wrapper">
    <div class="sim-container">
      <h1>Controllo Simulazione GPS</h1>

      <p>Stato attuale: <strong id="status-text"><?= htmlspecialchars($current_status) ?></strong></p>

      <button onclick="setStatus('on')">Avvia Simulazione</button>
      <button onclick="setStatus('off')">Ferma Simulazione</button>

      <small>Lo script <code>sim_gps.php</code> deve essere avviato da terminale e ascolta questo stato.</small>
    </div>
  </div>

</body>
</html>
