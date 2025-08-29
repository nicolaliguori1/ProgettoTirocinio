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
    /* Reset */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Body */
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
  color: #fff;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* Card principale */
.card {
  background-color: rgba(255, 255, 255, 0.05);
  padding: 40px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  text-align: center;
  max-width: 500px;
  width: 100%;
}

/* Titolo */
.card h1 {
  font-size: 1.8em;
  color: #00d4ff;
  margin-bottom: 15px;
}

.card p {
  margin-bottom: 30px;
  font-size: 1em;
  color: #ddd;
}

/* Form */
.row {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

.row input {
  padding: 12px 16px;
  font-size: 1em;
  border-radius: 10px;
  border: none;
  outline: none;
  width: 200px;
}

.row button {
  padding: 12px 24px;
  font-size: 1em;
  background: #00d4ff;
  color: #000;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.row button:hover {
  background: #00aacc;
}

/* Popup overlay */
#popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

#popup {
  background-color: rgba(255, 255, 255, 0.1);
  padding: 30px;
  border-radius: 15px;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
  text-align: center;
  max-width: 400px;
  width: 90%;
}

#popup h2 {
  font-size: 1.4em;
  margin-bottom: 15px;
  color: #ffcc00;
}

#popup p {
  margin-bottom: 20px;
  font-size: 1em;
}

#popup button {
  padding: 10px 20px;
  font-size: 1em;
  background: #00d4ff;
  color: #000;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}

#popup button:hover {
  background: #00aacc;
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
