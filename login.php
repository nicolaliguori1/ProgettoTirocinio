<?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'connessione.php';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($email) && !empty($password)) {
            $db = pg_connect($connection_string) or die('Impossibile connettersi al database.');

            $query = "SELECT * FROM users WHERE email = $1";
            $prep = pg_prepare($db, "login_query", $query);
            $result = pg_execute($db, "login_query", [$email]);

            $data = pg_fetch_array($result, 0, PGSQL_ASSOC);

            if ($data && password_verify($password, $data['pw'])) {
                $_SESSION['nome'] = $data['nome_utente']; // Cambiato da nome → nome_utente
                $_SESSION['email'] = $data['email'];
                header("Location: dashboard.php");

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
