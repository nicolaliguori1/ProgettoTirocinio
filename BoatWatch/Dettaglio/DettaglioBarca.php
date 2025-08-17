<?php
// dettaglioBarca.php
include __DIR__ . '/../../connessione.php';

/** distanza Haversine in metri */
function distanzaHaversine(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $r = 6371000; // metri
    $lat1 = deg2rad($lat1); $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2); $lon2 = deg2rad($lon2);
    $dlat = $lat2 - $lat1; $dlon = $lon2 - $lon1;
    $a = sin($dlat/2)**2 + cos($lat1)*cos($lat2)*sin($dlon/2)**2;
    return 2*$r*atan2(sqrt($a), sqrt(1-$a));
}

/** Calcola gli eventi Entrata/Uscita dal porto analizzando boats_position */
function getEntrateUscitePorto($conn, string $targa, float $soglia = 50): array {
    // faro associato
    $qFaro = "SELECT f.lat, f.lon
              FROM boats b
              JOIN fari f ON b.id_faro = f.id
              WHERE b.targa = $1";
    $rFaro = pg_query_params($conn, $qFaro, [$targa]);
    if (!$rFaro || pg_num_rows($rFaro) === 0) return [];
    $faro = pg_fetch_assoc($rFaro);
    $latFaro = (float)$faro['lat'];
    $lonFaro = (float)$faro['lon'];

    // posizioni storiche (in ordine cronologico)
    $qPos = "SELECT ts, lat, lon
             FROM boats_position
             WHERE targa_barca = $1
             ORDER BY ts ASC";
    $rPos = pg_query_params($conn, $qPos, [$targa]);
    if (!$rPos || pg_num_rows($rPos) === 0) return [];

    $eventi = [];
    $ultimoStato = null; // 'Dentro' | 'Fuori'
    while ($row = pg_fetch_assoc($rPos)) {
        $lat = (float)$row['lat'];
        $lon = (float)$row['lon'];
        $dist = distanzaHaversine($lat, $lon, $latFaro, $lonFaro);
        $stato = ($dist <= $soglia) ? 'Dentro' : 'Fuori';

        if ($stato !== $ultimoStato) {
            $eventi[] = [
                'ts'   => $row['ts'],
                'tipo' => ($stato === 'Dentro' ? 'Entrata nel porto' : 'Uscita dal porto'),
            ];
            $ultimoStato = $stato;
        }
    }
    return $eventi;
}

/* ====== endpoint AJAX JSON ====== */
if (isset($_GET['action']) && $_GET['action'] === 'eventi') {
    header('Content-Type: application/json; charset=utf-8');
    $targa = $_GET['targa'] ?? '';
    if ($targa === '') {
        echo json_encode(['eventi' => [], 'error' => 'Targa mancante']);
        exit;
    }
    $eventi = getEntrateUscitePorto($conn, $targa, 50); // soglia 50m (modificabile)
    echo json_encode(['eventi' => $eventi]);
    exit;
}

/* ====== rendering pagina ====== */
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

async function aggiornaStorico() {
  const tbody = document.querySelector('#storico-table tbody');
  try {
    if (!targa) {
      tbody.innerHTML = '<tr><td colspan="3">Targa mancante.</td></tr>';
      return;
    }
    const res = await fetch(`<?= basename(__FILE__) ?>?action=eventi&targa=${encodeURIComponent(targa)}&_=${Date.now()}`, { cache: 'no-store' });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();

    tbody.innerHTML = '';
    const eventi = Array.isArray(data.eventi) ? data.eventi.slice().reverse() : [];

    if (eventi.length === 0) {
      tbody.innerHTML = '<tr><td colspan="3">Nessun evento di entrata/uscita.</td></tr>';
      return;
    }

    // Mostra (ad es.) gli ultimi 10 cambi di stato
    eventi.slice(0, 10).forEach(ev => {
      const ts = new Date(ev.ts);
      const giorno = isNaN(ts) ? '-' : ts.toLocaleDateString('it-IT');
      const orario = isNaN(ts) ? '-' : ts.toLocaleTimeString('it-IT');
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${giorno}</td><td>${orario}</td><td>${ev.tipo}</td>`;
      tbody.appendChild(tr);
    });
  } catch (e) {
    console.error('Errore aggiornamento storico:', e);
    tbody.innerHTML = '<tr><td colspan="3">Errore di caricamento.</td></tr>';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  aggiornaStorico();
  setInterval(aggiornaStorico, 3000); // aggiornamento ogni 3s
});
</script>
</body>
</html>
