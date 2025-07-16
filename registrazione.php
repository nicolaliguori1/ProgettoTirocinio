<?php
    session_start();
    if (isset($_SESSION['email'])) {
        header("Location: areapersonale.php", true, 303);
        exit();
    }

    require_once 'connessione.php';

    // Inizializzazione variabili
    $nome = $_POST["nome"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $conferma_password = $_POST["conferma-password"] ?? "";
    $cap = $_POST['cap'] ?? "";
    $data = $_POST['data'] ?? "";
    $tipo = $_POST['tipo'] ?? "";
    $errore_email = $errore_conferma_password = "";
    $email_valid = true; // Variabile per segnalare se l'email è valida

    // Se il form è stato inviato
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Controllo se l'email è già registrata
        $check_query = "SELECT COUNT(*) FROM registrazione WHERE email = $1";
        pg_prepare($db, "check_email", $check_query);
        $check_result = pg_execute($db, "check_email", array($email));
        if (pg_fetch_result($check_result, 0, 0) > 0) {
            $errore_email = "L'email è già stata utilizzata.";
            $email_valid = false;
        } elseif ($password !== $conferma_password) {
            $errore_conferma_password = "Le password non coincidono.";
        } else {
            // Inserimento nel database
            $query = "INSERT INTO registrazione (nome, email, password, cap, data, tipo) VALUES ($1, $2, $3, $4, $5, $6)";
            pg_prepare($db, "register", $query);
            $result = pg_execute($db, "register", array($nome, $email, password_hash($password, PASSWORD_DEFAULT), $cap, $data, intval($tipo)));
            if ($result) {
                $_SESSION['nome'] = $nome;
                $_SESSION['email'] = $email;
                $_SESSION['cap'] = $cap;
                $_SESSION['data'] = $data;
                $_SESSION['tipo'] = $tipo;
                header("Location: index.php");
                exit();
            } else {
                $errore_email = "Errore durante la registrazione. Riprova.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Registrazione </title>
    <link rel="stylesheet" href="registrazione.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="FOOTER/footer.css">
    <style>
        .registrazione { text-align: center; }
        .errore { color: red; font-size: 0.9em; margin-top: 5px; display: none; }
    </style>
</head>

<body>

    <?php include "header.php"; ?>
    <div class="container">
        <div class="blocco-registrazione">
            <h2 style="color:rgb(4, 3, 46); text-align: center; font-size: 26px; font-weight: bold; margin-top: 40px; margin-bottom: 20px;">Registrazione</h2>
            <form id="form" method="post" class="registrazione" action="" onsubmit="return controllaForm()">
            <label for="tipo">
                <select name="tipo" id="tipo">
                    <option value="1" <?= $tipo == "1" ? "selected" : "" ?>>Individuo (Maschio)</option>
                    <option value="2" <?= $tipo == "2" ? "selected" : "" ?>>Individuo (Femmina)</option>
                    <option value="3" <?= $tipo == "3" ? "selected" : "" ?>>Individuo (Altro)</option>
                </select>
            </label>

            <p><label> <input maxlength="20" type="text" name="nome" placeholder="Nome" value="<?= htmlspecialchars($nome); ?>" required></label></p>

            <p><label> <input id="email" name="email" type="email" placeholder="Email" value="<?= htmlspecialchars($email); ?>" required oninput="nascondiErroreEmail()"></label></p>

            <p class="errore" id="errore-email" style="<?= !$email_valid ? 'display:block' : '' ?>"><?= $errore_email; ?></p>

            <p><label> <input name="cap" type="text" placeholder="CAP" value="<?= htmlspecialchars($cap); ?>" minlength="5" required></label></p>

            <p><label> <input id="password" type="password" maxlength="20" name="password" minlength="6" placeholder="Password" required></label></p>

            <p><label> <input id="conferma-password" type="password" maxlength="20" name="conferma-password" minlength="6" placeholder="Conferma Password" required oninput="nascondiErrore('errore-conferma-password')"></label></p>

            <p class="errore" id="errore-conferma-password" style="<?= $errore_conferma_password ? 'display:block' : '' ?>"><?= $errore_conferma_password; ?></p>

            <p style="color: black; font-weight: bold;">Data di nascita</p>

            <p><input required id="data" name="data" type="date" value="<?= htmlspecialchars($data); ?>"></p>

            <p style="color: black;">Dai il tuo consenso per il trattamento di dati: <input type="checkbox" required> 
            <a href="https://protezionedatipersonali.it/informativa" style="font-size: smaller;">Informazioni sulla privacy</a></p>

            <input type="submit" name="bottone" value="Registrati">
            </form>
        </div>
    </div>

    <?php include "FOOTER/footer.php"; ?>

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
            if(pass !== cpass) {
                erroreConferma.innerText = "Le password non coincidono.";
                erroreConferma.style.display = "block";
                errore = true;
            }else{
                erroreConferma.style.display = "none";
            }
        return !errore;
        }

        function nascondiErroreEmail() {
            let emailErrore = document.getElementById("errore-email");
            emailErrore.innerText = ""; // Rimuove il messaggio di errore
            emailErrore.style.display = "none";
        }

        function nascondiErrore(id) {
            document.getElementById(id).style.display = "none";
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("data").setAttribute("max", new Date().toISOString().split("T")[0]);
        });
    </script>

</body>

</html>