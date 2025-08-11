<?php
    include __DIR__ . '/../../connessione.php';

    $barca = null;
    $targa = $_GET['targa'] ?? '';

    if ($targa !== '') {
        $query = "
            SELECT b.targa, b.nome, b.lunghezza, u.nome_utente, f.nome AS nome_faro
            FROM boats b
            LEFT JOIN users u ON b.id_user = u.id
            LEFT JOIN fari f ON b.id_faro = f.id
            WHERE b.targa = $1
        ";
        $result = pg_query_params($conn, $query, [$targa]);
        if ($result && pg_num_rows($result) > 0) {
            $barca = pg_fetch_assoc($result);
        }
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettagli Barca</title>
    <link rel="stylesheet" href="dettaglio.css">
</head>
<body>
    <div class="container">
        <?php include "../header.php" ?>
        <?php if ($barca): ?>
            <h1 class="titolo">Dettagli Barca: <?= htmlspecialchars($barca['nome']) ?></h1>
            <p><strong>Targa:</strong> <?= htmlspecialchars($barca['targa']) ?></p>
            <p><strong>Lunghezza:</strong> <?= number_format((float)$barca['lunghezza'], 1) ?> metri</p>
            <p><strong>Proprietario:</strong> <?= htmlspecialchars($barca['nome_utente'] ?? 'N/A') ?></p>
            <p><strong>Faro Associato:</strong> <?= htmlspecialchars($barca['nome_faro'] ?? 'Nessuno') ?></p>
        <?php elseif ($targa !== ''): ?>
            <p class="errore">Barca non trovata.</p>
        <?php else: ?>
            <p class="errore">Nessuna targa specificata.</p>
        <?php endif; ?>

        <div class="storico">
            <table id="storico-table">
                <caption>Storico Entrata/Uscita Porto</caption>
                <thead>
                    <tr>
                        <th>Giorno</th>
                        <th>Orario</th>
                        <th>Evento</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="3">Caricamento...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<script>
  // Targa dal PHP
  const targa = <?= json_encode($targa) ?>;

  // Stato precedente ed ultimo timestamp evento per evitare duplicati
  let lastStato = null;
  let lastEventTs = null;

  function formatTs(tsStr) {
    const ts = tsStr ? new Date(tsStr) : new Date();
    const ok = !isNaN(ts);
    return {
      giorno: ok ? ts.toLocaleDateString('it-IT') : '-',
      orario: ok ? ts.toLocaleTimeString('it-IT') : '-',
      rawISO: ok ? ts.toISOString() : null
    };
  }

  function aggiungiRigaEvento(giorno, orario, tipo) {
    const tbody = document.querySelector('#storico-table tbody');
    const row = document.createElement('tr');

    const tdG = document.createElement('td'); tdG.textContent = giorno;  row.appendChild(tdG);
    const tdO = document.createElement('td'); tdO.textContent = orario;  row.appendChild(tdO);
    const tdE = document.createElement('td'); tdE.textContent = tipo;    row.appendChild(tdE);

    // Più recenti in alto
    if (tbody.firstChild) tbody.insertBefore(row, tbody.firstChild);
    else tbody.appendChild(row);
  }

  function aggiornaEntrateUscite() {
    fetch('../../Natante/simulazione/api_posizione_barca.php?targa=' + encodeURIComponent(targa))
      .then(res => res.json())
      .then(data => {
        const tbody = document.querySelector('#storico-table tbody');

        if (!data || data.error) {
          console.error('API error:', data?.error || 'Errore sconosciuto');
          tbody.innerHTML = '<tr><td colspan="3">Errore nel caricamento.</td></tr>';
          return;
        }

        // Rimuovi placeholder alla prima risposta valida
        if (tbody.children.length === 1 && tbody.children[0].children[0]?.colSpan === 3) {
          tbody.innerHTML = '';
        }

        const stato = (data.stato || '').trim();               // "Nel porto" | "Fuori dal porto"
        const tsStr = data.live?.ts ?? null;                    // timestamp associato alla posizione live
        const { giorno, orario, rawISO } = formatTs(tsStr);

        // Se c'è una transizione di stato, registriamo l'evento
        if (lastStato !== null && stato && stato !== lastStato) {
          const tipo = (stato === 'Nel porto') ? 'Entrata' : 'Uscita';

          // Evita duplicati se arriva più volte lo stesso ts
          const eventKey = rawISO || tsStr || null;
          if (!lastEventTs || lastEventTs !== eventKey) {
            aggiungiRigaEvento(giorno, orario, tipo);
            lastEventTs = eventKey;
          }
        }

        // Aggiorna lo stato precedente
        if (stato) lastStato = stato;

        // Se ancora nessuna riga, mostra placeholder
        if (!tbody.firstChild) {
          tbody.innerHTML = '<tr><td colspan="3">Nessun evento di entrata/uscita.</td></tr>';
        }
      })
      .catch(err => {
        console.error('Errore:', err);
        const tbody = document.querySelector('#storico-table tbody');
        tbody.innerHTML = '<tr><td colspan="3">Errore nel caricamento.</td></tr>';
      });
  }

  // Avvio e refresh ogni 2s
  aggiornaEntrateUscite();
  const timer = setInterval(aggiornaEntrateUscite, 2000);
  window.addEventListener('beforeunload', () => clearInterval(timer));
</script>
</body>
</html>
