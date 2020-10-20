<?php
$servername = "localhost";
$database = "sistemavendas";
$username = "root";
$databasePassword = "";

$conn = mysqli_connect($servername, $username, $databasePassword, $database);

if (!$conn) {
  die(mysqli_connect_error());
}
?>