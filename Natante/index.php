<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Ricerca Barca</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- PWA essentials -->
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#00BFFF">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <link rel="apple-touch-icon" href="/icons/icona_natante-180.png">

  <!-- CSS -->
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

    function closePopup() {
      document.getElementById('popup-overlay').style.display = 'none';
    }

    function onKeyDown(e) {
      if (e.key === 'Escape') closePopup();
    }

    function validateInputAndSubmit(e) {
      e.preventDefault();
      const input = document.getElementById('targaBarca').value.trim();

      if (input === '') {
        showPopup("Inserisci una targa valida.");
        return;
      }

      // Qui puoi aggiungere altre validazioni (alfanumerico ecc.)
      window.location.href = 'barca.php?targa=' + encodeURIComponent(input);
    }

    window.addEventListener('DOMContentLoaded', () => {
      document.addEventListener('keydown', onKeyDown);
      document.getElementById('searchForm').addEventListener('submit', validateInputAndSubmit);
    });
  </script>

  <style>
    body {
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
      margin: 0;
      background: #0b1220;
      color: #fff;
      min-height: 100vh;
      display: grid;
      place-items: center;
    }
    .card {
      background: #111a2b;
      border: 1px solid #22314f;
      border-radius: 16px;
      padding: 24px;
      width: min(520px, 92vw);
      box-shadow: 0 10px 30px rgba(0,0,0,.35);
    }
    h1 { margin: 0 0 12px; font-size: 1.4rem; font-weight: 600; }
    p  { margin: 0 0 24px; color: #b6c2e2; }
    .row { display: grid; grid-template-columns: 1fr auto; gap: 12px; }
    input[type="text"]{
      background: #0e1626; border: 1px solid #2b3b5f;
      color:#fff; padding: 12px 14px; border-radius: 10px; font-size: 1rem;
    }
    button{
      background:#00BFFF; color:#111; border:0; padding: 12px 16px;
      border-radius: 10px; font-weight:700; cursor:pointer;
    }
    button:hover{ filter: brightness(0.95); }
    /* popup */
    #popup-overlay{
      position: fixed; inset:0; background: rgba(0,0,0,.55);
      display:none; align-items:center; justify-content:center; z-index: 9999;
    }
    #popup {
      background:#111a2b; border:1px solid #22314f; border-radius:14px;
      padding: 18px; width:min(420px, 92vw);
    }
    #popup h2 { margin:0 0 10px; font-size:1.1rem; }
    #popup p  { margin:0 0 16px; color:#c4d0ef; }
    #popup button{
      background:#00BFFF; color:#111; border:0; padding: 10px 14px;
      border-radius: 10px; font-weight:700; cursor:pointer;
    }
  </style>
</head>
<body>
  <main class="card" role="main">
    <h1>Ricerca Barca</h1>
    <p>Inserisci la targa della barca per visualizzare i dettagli.</p>
    <form id="searchForm" class="row">
      <input id="targaBarca" type="text" placeholder="Es. ABC123" aria-label="Targa barca">
      <button type="submit">Cerca</button>
    </form>
  </main>

  <!-- popup -->
  <div id="popup-overlay" role="dialog" aria-modal="true" aria-labelledby="popup-title">
    <div id="popup">
      <h2 id="popup-title">Attenzione</h2>
      <p id="popup-text">Messaggio</p>
      <button id="popup-close" onclick="closePopup()" type="button">OK</button>
    </div>
  </div>

  <!-- Service Worker registration -->
  <script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js').catch(console.error);
    });
  }
  </script>
</body>
</html>
