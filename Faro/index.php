<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Ricerca Faro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="manifest" href="manifest.json">
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="../BoatWatch/alert.css"> 
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

    async function cercaFaro() {
      const input = document.getElementById('codiceFaro').value.trim();

      if (input === '') {
        showPopup("Inserisci un codice ID valido.");
        return;
      }

      if (!/^\d+$/.test(input)) {
        showPopup("Inserisci un ID numerico valido.");
        return;
      }

      try {
        const res = await fetch('api_faro_info.php?id=' + encodeURIComponent(input), { cache: 'no-store' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        if (data.trovato) {
          window.location.href = 'faro.php?id=' + encodeURIComponent(input);
        } else {
          showPopup("Faro non trovato.");
        }
      } catch (error) {
        console.error("Errore nella ricerca:", error);
        showPopup("Errore nella connessione al server.");
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const input = document.getElementById('codiceFaro');
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') cercaFaro();
      });

      document.getElementById('popup-close').addEventListener('click', hidePopup);
      document.getElementById('popup-overlay').addEventListener('click', (e) => {
        if (e.target.id === 'popup-overlay') hidePopup();
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hidePopup();
      });
    });
  </script>
</head>

<body>
  <div class="container">
    <h1>Benvenuto nel Lighthouse Tracker</h1>
    <p>Inserisci l'ID numerico del faro per visualizzare i dettagli.</p>

    <input type="text" id="codiceFaro" placeholder="Es: 1, 2, 3...">
    <button onclick="cercaFaro()">Cerca</button>
  </div>

  <div id="popup-overlay" class="popup-overlay" style="display:none;">
    <div class="popup" role="alertdialog" aria-modal="true" aria-labelledby="popup-text">
      <p id="popup-text">Messaggio</p>
      <button id="popup-close" type="button">OK</button>
    </div>
  </div>
</body>
</html>
