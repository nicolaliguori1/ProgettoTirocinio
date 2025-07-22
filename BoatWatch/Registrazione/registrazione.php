<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    session_start();
    include __DIR__ . '/../../connessione.php';

    // Inizializzazione variabili
    $nome_utente = $_POST["nome_utente"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $conferma_password = $_POST["conferma-password"] ?? "";

    $errore_email = $errore_conferma_password = "";
    $email_valid = true;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Controllo se l'email è già registrata
        $check_query = "SELECT COUNT(*) FROM users WHERE email = $1";
        pg_prepare($conn, "check_email", $check_query);
        $check_result = pg_execute($conn, "check_email", array($email));
        
        if (pg_fetch_result($check_result, 0, 0) > 0) {
            $errore_email = "L'email è già stata utilizzata.";
            $email_valid = false;
        } elseif ($password !== $conferma_password) {
            $errore_conferma_password = "Le password non coincidono.";
        } else {
        // Inserimento nel database
        $query = "INSERT INTO users (nome_utente, email, pw) VALUES ($1, $2, $3)";
        $result = pg_query_params($conn, $query, array($nome_utente,$email,password_hash($password, PASSWORD_DEFAULT)));

        if ($result) {
            $_SESSION['nome_utente'] = $nome_utente;
            $_SESSION['email'] = $email;
            header("Location: ../Login/login.php");
            exit();
        } else {
            $errore_email = "Errore durante la registrazione: " . pg_last_error($conn);
        }}
    }
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <link rel="stylesheet" href="registrazione.css">
    <style>
        .registrazione { text-align: center; }
        .errore { color: red; font-size: 0.9em; margin-top: 5px; display: none; }
    </style>
</head>

<body>
    <div class="container">
        <div class="blocco-registrazione">
        <h2 style="color: #00d4ff; text-align: center; font-size: 26px; font-weight: bold; margin-top: 40px; margin-bottom: 20px;">Registrazione</h2>
        <form id="form" method="post" class="registrazione" action="" onsubmit="return controllaForm()">

                <p><label>
                    <input maxlength="20" type="text" name="nome_utente" placeholder="Nome utente" value="<?= htmlspecialchars($nome_utente); ?>" required>
                </label></p>

                <p><label>
                    <input id="email" name="email" type="email" placeholder="Email" value="<?= htmlspecialchars($email); ?>" required oninput="nascondiErroreEmail()">
                </label></p>

                <p class="errore" id="errore-email" style="<?= !$email_valid ? 'display:block' : '' ?>"><?= $errore_email; ?></p>

                <p><label>
                    <input id="password" type="password" maxlength="20" name="password" minlength="6" placeholder="Password" required>
                </label></p>

                <p><label>
                    <input id="conferma-password" type="password" maxlength="20" name="conferma-password" minlength="6" placeholder="Conferma Password" required oninput="nascondiErrore('errore-conferma-password')">
                </label></p>

                <p class="errore" id="errore-conferma-password" style="<?= $errore_conferma_password ? 'display:block' : '' ?>"><?= $errore_conferma_password; ?></p>

                <p style="color: #ccc;">Dai il tuo consenso per il trattamento di dati: 
                    <input type="checkbox" required> 
                    <a href="https://protezionedatipersonali.it/informativa" style="font-size: smaller;">Informazioni sulla privacy</a>
                </p>

                <input type="submit" name="bottone" value="Registrati">
            </form>
        </div>
    </div>

    <script>
        function controllaForm() {
            let errore = false;
            let emailErrore = document.getElementById("errore-email");
            let erroreConferma = document.getElementById("errore-conferma-password");
            let pass = document.getElementById("password").value;
            let cpass = document.getElementById("conferma-password").value;

            if (emailErrore.innerText.trim() !== "") {
                emailErrore.style.display = "block";
                errore = true;
            }

            if (pass !== cpass) {
                erroreConferma.innerText = "Le password non coincidono.";
                erroreConferma.style.display = "block";
                errore = true;
            } else {
                erroreConferma.style.display = "none";
            }

            return !errore;
        }

        function nascondiErroreEmail() {
            let emailErrore = document.getElementById("errore-email");
            emailErrore.innerText = "";
            emailErrore.style.display = "none";
        }

        function nascondiErrore(id) {
            document.getElementById(id).style.display = "none";
        }
    </script>
</body>
</html>
