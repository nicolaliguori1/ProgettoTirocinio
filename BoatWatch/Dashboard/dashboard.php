<?php
session_start(); 
include './connessione.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$utente = $_SESSION['nome'] ?? "Utente";
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css?v=2">
</head>
<body>
<div class="Dashboard">
    <div class="Logo">
        <img src="../logo.png" alt="Logo" class="logo-img">
    </div>

    <div class="welcome">
        <h2>Benvenuto su BoatWatch, <?= htmlspecialchars($utente) ?>!</h2>
    </div>

    <div class="Opzioni">
        <div class="myboat">
            <button class="custom-button" onclick="location.href='../Elenco/elencoBarche.php'">Barche</button>
        </div>
        <div class="myport">
            <button class="custom-button" onclick="location.href='../Elenco/elencoFari.php'">Fari</button>
        </div>
    </div>
    <?php
    include '../logoutbutton.php';
    ?>
</div>
</body>
</html>


    

    