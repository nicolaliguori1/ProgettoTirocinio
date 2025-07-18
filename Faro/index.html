<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Ricerca Faro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="manifest" href="manifest.json">
  <link rel="stylesheet" href="css/style1.css">
  <script>
    async function cercaFaro() {
      const input = document.getElementById('codiceFaro').value.trim();

      if (input === '') {
        alert("Inserisci un codice o nome valido.");
        return;
      }

      try {
        const res = await fetch(`/api/cerca-faro.php?nome=` + encodeURIComponent(input));
        const data = await res.json();

        if (data.trovato) {
          window.location.href = 'faro.php?nome=' + encodeURIComponent(input);
        } else {
          alert("Faro non trovato.");
        }
      } catch (error) {
        console.error("Errore nella ricerca:", error);
        alert("Errore nella connessione al server.");
      }
    }
  </script>
</head>

<body>
  <div class="container">
    <h1>Benvenuto nel Lighthouse Tracker</h1>
    <p>Inserisci il nome o codice del faro per visualizzare i dettagli.</p>

    <input type="text" id="codiceFaro" placeholder="Es: Port of Southampton, FARO-123">
    <button onclick="cercaFaro()">Cerca</button>
  </div>
</body>
</html>
