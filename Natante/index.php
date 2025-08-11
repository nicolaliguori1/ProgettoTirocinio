<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Ricerca Barca</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="manifest" href="manifest.json">
  <link rel="stylesheet" href="css/style1.css">
  <script>
    async function cercaBarca() {
      const input = document.getElementById('codiceBarca').value.trim();

      if (input === '') {
        alert("Inserisci una targa valida.");
        return;
      }

      try {
        const res = await fetch(`api_boat_info.php?targa=` + encodeURIComponent(input));
        const data = await res.json();

        if (data.trovata) {
          window.location.href = 'barca.php?targa=' + encodeURIComponent(input);
        } else {
          alert("Barca non trovata.");
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
    <h1>Benvenuto nel Boat Tracker</h1>
    <p>Inserisci la targa della barca per visualizzare i dettagli.</p>

    <input type="text" id="codiceBarca" placeholder="Es: ITA-001">
    <button onclick="cercaBarca()">Cerca</button>
  </div>
</body>
</html>
