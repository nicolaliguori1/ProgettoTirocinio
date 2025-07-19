<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$utente= $_SESSION['nome'] ?? "Utente";
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="Dashboard">
    <div class="Logo">
        <img src="icona_natante.png" alt="Logo" class="logo-img">
    </div>

    <div class="welcome">
        <h2>Benvenuto, <?= htmlspecialchars($utente) ?>!</h2>
    </div>

    <div class="Opzioni">
        <div class="myboat">
            <button class="custom-button" onclick="location.href='elencoBarche.php'">My Boats</button>
        </div>
        <div class="myport">
            <button class="custom-button" onclick="location.href='elencoFari.php'">Ports</button>
        </div>
    </div>
</div>
</body>
</html>


    

    <?php
        // Verifica se l'utente è loggato
        $utente_email = isset($_SESSION['email']) ? $_SESSION['email'] : null;

        // Variabile per il messaggio del modale
        $modale_messaggio = "";

        // Quando viene inviato il modulo
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['destinazione'])) {
            // Se l'utente non è loggato, mostra il messaggio di errore
            if (!$utente_email) {
                $modale_messaggio = "Utente non loggato. Per aggiungere la destinazione al carrello effettua il login.";
            } else {
                $destinazione_id = (int)$_POST['destinazione']; // Id della destinazione inviato dal modulo
                // Dati relativi alle destinazioni e prezzi (puoi anche recuperarli dal database)
                $destinazioni = [
                    1 => 3690, 2 => 3190, 3 => 3490, 4 => 2190, 5 => 1890, 6 => 3790,
                    7 => 2190, 8 => 3090, 9 => 1590, 10 => 1490, 11 => 2990, 12 => 2590
                ];
                // Verifica che l'id della destinazione sia valido
                if (array_key_exists($destinazione_id, $destinazioni)) {
                    $prezzo = $destinazioni[$destinazione_id];

                    try {
                        // Connessione al database
                        require_once 'connessione.php';

                        // Controlla se la destinazione è già nel carrello per l'utente
                        $check_query = "SELECT COUNT(*) FROM carrello WHERE utente = $1 AND destinazione = $2";
                        $check_prep = pg_prepare($db, "check_destinazione", $check_query);
                        $check_result = pg_execute($db, "check_destinazione", array($utente_email, $destinazione_id));
                        $count = pg_fetch_result($check_result, 0, 0);

                        if ($count > 0) {
                            $modale_messaggio = "Hai già aggiunto questa destinazione al carrello.";
                        } else {
                            // Query per inserire nel carrello
                            $query = "INSERT INTO carrello (utente, prezzo, destinazione) VALUES ($1, $2, $3)";
                            $prep = pg_prepare($db, "inserisci_carrello", $query);
                            $result = pg_execute($db, "inserisci_carrello", array($utente_email, $prezzo, $destinazione_id));

                            if ($result) {
                                $modale_messaggio = "La destinazione è stata aggiunta al carrello con successo!";
                            } else {
                                $modale_messaggio = "Errore durante l'aggiunta al carrello. Riprova.";
                            }
                        }
                    } catch (Exception $e) {
                        $modale_messaggio = "Errore: " . $e->getMessage();
                    }
                } else {
                    $modale_messaggio = "Destinazione non valida.";
                }
            }
        }
    ?>

    <!-- Modale per i messaggi -->
    <?php if (!empty($modale_messaggio)): ?>
        <div class="modal-overlay" id="alert-modal">
            <div class="modal">
                <p><?php echo htmlspecialchars($modale_messaggio); ?></p>
                <button onclick="closeModal()">OK</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let messaggio = "<?php echo addslashes($modale_messaggio); ?>";
            let modalButton = document.getElementById("modal-button");

            // Se il messaggio riguarda il login, cambia comportamento del pulsante
            if (messaggio.includes("Utente non loggato")) {
                modalButton.innerHTML = "Vai al Login";
                modalButton.onclick = function () {
                    window.location.href = "login.php"; // Cambia con il percorso corretto del login
                };
            }
        });

        function closeModal() {
            document.getElementById("alert-modal").style.display = "none";
        }
    </script>
