<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Ricerca Barca</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="manifest" href="manifest.json">
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="../BoatWatch/alert.css"> 
  <script>
    function showPopup(message) {
      const overlay = document.getElementById('popup-overlay');
      const text = document.getElementById('popup-text');
      const closeBtn = document.getElementById('popup-close');

      text.textContent = message;
      overlay.style.display = 'flex';
      closeBtn.focus();
    }

    function hidePopup() {
      document.getElementById('popup-overlay').style.display = 'none';
    }

    async function cercaBarca() {
      const inputEl = document.getElementById('codiceBarca');
      const value = inputEl.value.trim();

      if (value === '') {
        showPopup('Inserisci una targa valida.');
        return;
      }

      try {
        const res = await fetch('info_barca.php?targa=' + encodeURIComponent(value), { cache: 'no-store' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        if (data && data.trovata) {
          window.location.href = 'barca.php?targa=' + encodeURIComponent(value);
        } else {
          showPopup('Barca non trovata.');
        }
      } catch (err) {
        console.error('Errore nella ricerca:', err);
        showPopup('Errore nella connessione al server.');
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const input = document.getElementById('codiceBarca');
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') cercaBarca();
      });

      document.getElementById('popup-close').addEventListener('click', hidePopup);
      document.getElementById('popup-overlay').addEventListener('click', (e) => {
        if (e.target.id === 'popup-overlay') hidePopup(); // chiudi cliccando fuori
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hidePopup();
      });
    });
  </script>
</head>

<body>
  <div class="container">
    <h1>Benvenuto nel Boat Tracker</h1>
    <p>Inserisci la targa della barca per visualizzare i dettagli.</p>

    <input type="text" id="codiceBarca" placeholder="Es: ITA-001" autocomplete="off">
    <button onclick="cercaBarca()">Cerca</button>
  </div>

  <div id="popup-overlay" class="popup-overlay" style="display:none;">
    <div class="popup" role="alertdialog" aria-modal="true" aria-labelledby="popup-text">
      <p id="popup-text">Messaggio</p>
      <button id="popup-close" type="button">OK</button>
    </div>
  </div>
  
</body>
</html>
