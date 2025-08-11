<?php
session_start();

require_once __DIR__ . '/../connessione.php'; 

if (!isset($conn)) {
    if (!isset($connection_string)) {
        die('Connessione non configurata: manca $connection_string in connessione.php');
    }
    $conn = pg_connect($connection_string);
    if (!$conn) {
        die('Impossibile connettersi al database.');
    }
}

$nome_utente        = $_POST['nome_utente'] ?? '';
$email              = $_POST['email'] ?? '';
$password           = $_POST['password'] ?? '';
$conferma_password  = $_POST['conferma-password'] ?? '';

$errore_email = $errore_conferma_password = $errore_generico = '';
$email_valid = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_utente = trim($nome_utente);
    $email = trim($email);
    $password = trim($password);
    $conferma_password = trim($conferma_password);

    if ($nome_utente === '' || mb_strlen($nome_utente) > 20) {
        $errore_generico = "Nome utente non valido (max 20 caratteri).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errore_email = "Formato email non valido.";
        $email_valid = false;
    } elseif (mb_strlen($password) < 6 || mb_strlen($password) > 72) {
        $errore_conferma_password = "La password deve essere lunga tra 6 e 72 caratteri.";
    } elseif ($password !== $conferma_password) {
        $errore_conferma_password = "Le password non coincidono.";
    } else {
        $check_result = pg_query_params(
            $conn,
            "SELECT 1 FROM users WHERE email = $1 LIMIT 1",
            [$email]
        );

        if ($check_result === false) {
            $errore_generico = "Errore durante il controllo email: " . pg_last_error($conn);
        } elseif (pg_fetch_assoc($check_result)) {
            $errore_email = "L'email è già stata utilizzata.";
            $email_valid = false;
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert_result = pg_query_params(
                $conn,
                "INSERT INTO users (nome_utente, email, pw) VALUES ($1, $2, $3) RETURNING id, nome_utente, email",
                [$nome_utente, $email, $hash]
            );

            if ($insert_result === false) {
                $errore_generico = "Errore durante la registrazione: " . pg_last_error($conn);
            } else {
                $row = pg_fetch_assoc($insert_result);
                // Imposta la sessione
                $_SESSION['id']          = $row['id'] ?? null;
                $_SESSION['nome_utente'] = $row['nome_utente'] ?? $nome_utente;
                $_SESSION['email']       = $row['email'] ?? $email;

                // Redirect dopo successo
                header("Location: index.php");
                exit();
            }
        }
    }
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

    <p style="margin-top: 15px; font-size: 0.9em; color: #ccc;">
        Hai già un account?
        <a href="index.php" style="color: #00d4ff; text-decoration: none;">
            Clicca qui per accedere
        </a>
    </p>

</form>

        </div>
    </div>

    <script>
(function () {
  const form   = document.getElementById('form');
  const email  = document.getElementById('email');
  const pass   = document.getElementById('password');
  const cpass  = document.getElementById('conferma-password');

  const errEmail = document.getElementById('errore-email');
  const errPass  = document.getElementById('errore-conferma-password');

  function showError(el, msg) {
    if (!el) return;
    el.textContent = msg;
    el.classList.add('show');   // .errore.show { display:block }
  }

  function hideError(el) {
    if (!el) return;
    el.textContent = '';
    el.classList.remove('show');
  }

  function isValidEmail(v) {
    // semplice ma efficace per il form
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
  }

  // Validazione principale richiamata dall’onsubmit PHP: onsubmit="return controllaForm()"
  window.controllaForm = function controllaForm() {
    let ok = true;

    // Email
    const e = email.value.trim();
    if (!isValidEmail(e)) {
      showError(errEmail, 'Formato email non valido.');
      ok = false;
    } else if (errEmail && errEmail.textContent.trim() && errEmail.textContent.includes("già stata utilizzata")) {
      // se il server ha già segnalato email duplicata, lascio il messaggio e blocco l’invio
      errEmail.classList.add('show');
      ok = false;
    } else {
      hideError(errEmail);
    }

    // Password
    const p  = pass.value;
    const cp = cpass.value;

    if (p.length < 6) {
      showError(errPass, 'La password deve avere almeno 6 caratteri.');
      ok = false;
    } else if (p !== cp) {
      showError(errPass, 'Le password non coincidono.');
      ok = false;
    } else {
      hideError(errPass);
    }

    return ok; // true = invia, false = blocca
  };

  // Feedback “live” mentre l’utente digita
  email.addEventListener('input', () => {
    // rimuovo eventuale messaggio server/cliente quando l’email torna valida
    if (isValidEmail(email.value.trim())) hideError(errEmail);
    else showError(errEmail, 'Formato email non valido.');
  });

  pass.addEventListener('input', () => {
    // se allungo la password oltre 6 e coincide, nascondo
    if (pass.value.length >= 6 && pass.value === cpass.value) hideError(errPass);
  });

  cpass.addEventListener('input', () => {
    if (pass.value.length >= 6 && pass.value === cpass.value) hideError(errPass);
    else if (cpass.value.length > 0) showError(errPass, 'Le password non coincidono.');
  });
})();
</script>

</body>
</html>
