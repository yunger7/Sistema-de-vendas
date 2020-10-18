<?php
$servername = "localhost";
$database = "sistemavendas";
$username = "root";
$password = "";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
  die(mysqli_connect_error());
}
?>