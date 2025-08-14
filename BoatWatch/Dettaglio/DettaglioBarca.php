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

        <div style="margin-top: 20px; text-align: center;">
            <a href="../Elenco/elencoBarche.php" 
               style="display: inline-block; padding: 10px 20px; background-color: #00d4ff; 
                      color: #000; text-decoration: none; border-radius: 10px; 
                      transition: background 0.3s ease;">
                Torna all'elenco barche
            </a>
        </div>
    </div>

<script>
  const targa = <?= json_encode($targa) ?>;
  function renderEventi(eventi) {
    const tbody = document.querySelector('#storico-table tbody');
    tbody.innerHTML = '';

    if (!eventi || eventi.length === 0) {
      tbody.innerHTML = '<tr><td colspan="3">Nessun evento di entrata/uscita.</td></tr>';
      return;
    }
    const sorted = [...eventi].sort((a, b) => new Date(b.ts) - new Date(a.ts)).slice(0, 10);
    sorted.forEach(ev => {
      const ts = new Date(ev.ts);
      const giorno = isNaN(ts) ? '-' : ts.toLocaleDateString('it-IT');
      const orario = isNaN(ts) ? '-' : ts.toLocaleTimeString('it-IT');
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${giorno}</td>
        <td>${orario}</td>
        <td>${ev.tipo}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  function buildApiUrl() {
    const u = new URL('../../Natante/simulazione/api_posizione_barca.php', window.location.href);
    u.searchParams.set('targa', targa || '');
    u.searchParams.set('_', Date.now().toString());
    return u.toString();
  }

  async function aggiornaStoricoEventi() {
    try {
      if (!targa) {
        renderEventi([]);
        return;
      }
      const res = await fetch(buildApiUrl(), { cache: 'no-store' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      if (data?.error) {
        console.error('API error:', data.error);
        renderEventi([]);
        return;
      }

      renderEventi(Array.isArray(data.eventi) ? data.eventi : []);
    } catch (e) {
      console.error('Errore caricamento eventi:', e);
      renderEventi([]);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    aggiornaStoricoEventi();
    const timer = setInterval(aggiornaStoricoEventi, 2000);
    window.addEventListener('beforeunload', () => clearInterval(timer));
  });
</script>
</body>
</html>
