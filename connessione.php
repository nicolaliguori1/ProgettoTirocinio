<?php
$host = 'localhost';
$port = '5432';
$db = 'tirocinio';
$username = 'tiroboat';
$db_password = 'tiroboat';

$connection_string = "host=$host port=$port dbname=$db user=$username password=$db_password";
$conn = pg_connect($connection_string);

if (!$conn) {
    die("Errore di connessione al database.");
}
?>
