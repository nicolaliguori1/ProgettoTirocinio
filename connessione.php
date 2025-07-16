<?php
$host = 'localhost';
$port = '5432';
$db = 'gruppo19';
$username = 'www';
$db_password = 'tw2024';
$connection_string = "host=$host dbname=$db user=$username password=$db_password ";

$db = pg_connect($connection_string)
    or die('Impossibile connetersi al database: ' .
        pg_last_error());
//echo "Connessione al database riuscita<br/>";
