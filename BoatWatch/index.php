<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once __DIR__ . '/../connessione.php';

    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    if ($email !== '' && $password !== '') {

        $conn = pg_connect($connection_string);
        if (!$conn) {
            http_response_code(500); 
            exit();
        }

        $query = "SELECT id, nome_utente, email, pw FROM users WHERE email = $1 LIMIT 1";
        if (!pg_prepare($conn, "login_query", $query)) {
            http_response_code(500);
            exit();
        }

        $result = pg_execute($conn, "login_query", [$email]);
        if ($result === false) {
            http_response_code(500);
            exit();
        }

        $data = pg_fetch_assoc($result);

        if ($data && password_verify($password, $data['pw'])) {
            $_SESSION['nome']  = $data['nome_utente'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['id']    = $data['id'];
            http_response_code(200);
        } else {
            http_response_code(401); 
        }
    } else {
        http_response_code(400); 
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
        <div class="form-container">
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
            xhttp.open('POST', 'index.php', true);
            xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhttp.onreadystatechange = function () {
                if (this.readyState === 4) {
                    if (this.status === 200) {
                        window.location.href = 'Dashboard/dashboard.php'; 
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
