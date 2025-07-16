<?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'connessione.php';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($email) && !empty($password)) {
            $db = pg_connect($connection_string) or die('Impossibile connettersi al database.');
            $query = "SELECT * FROM registrazione WHERE email = $1";
            $prep = pg_prepare($db, "login_query", $query);
            $result = pg_execute($db, "login_query", [$email]);
            $data = pg_fetch_array($result, 0, PGSQL_ASSOC);

            if ($data && password_verify($password, $data['password'])) {
                $_SESSION['nome'] = $data['nome'];
                $_SESSION['email'] = $data['email'];
                $_SESSION['cap'] = $data['cap'];
                $_SESSION['data'] = $data['data'];
                $_SESSION['tipo'] = $data['tipo'];
                http_response_code(200); // Successo
            } else {
                http_response_code(401); // Credenziali errate
            }

        } else {
            http_response_code(400); // Campi mancanti
        }
        exit();
    }
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Login</title>
</head>

<body>

    <div class="page-wrapper">
        <div class="form-container2">
            <h3>Accedi</h3>
            <form id="login-form">
                <input id="email" name="email" type="email" placeholder="E-mail" required>
                <input id="password" name="password" type="password" placeholder="Password" required>
                <button type="submit" class="login-btn">Accedi</button>
            </form>
            <div id="message" class="message"></div>
            <div class="register-link">
                Non hai un account? <a href="registrazione.php">Registrati</a>
            </div>
        </div>
    </div>


    <script>
        document.getElementById('login-form').addEventListener('submit', function (event) {
            event.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');

            const xhttp = new XMLHttpRequest();
            xhttp.open('POST', 'login.php', true);
            xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhttp.onreadystatechange = function () {
                if (this.readyState === 4) {
                    if (this.status === 200) {
                        window.location.href = 'index.php';
                    } else if (this.status === 401) {
                        messageDiv.textContent = 'E-mail o password errati!';
                        messageDiv.className = 'message error';
                    } else if (this.status === 400) {
                        messageDiv.textContent = 'Tutti i campi sono obbligatori!';
                        messageDiv.className = 'message error';
                    } else {
                        messageDiv.textContent = 'Errore durante il login. Riprova più tardi.';
                        messageDiv.className = 'message error';
                    }
                }
            };

            xhttp.send(`email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`);
        });
    </script>

</body>

</html>
