<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

if ($_SESSION['priority'] < 1) {
  header('location: home.php');
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <?php include 'templates/head.php'; ?>
</head>

<body>
  <?php include 'templates/navbar.php'; ?>
  <?php include 'templates/topbar.php'; ?>
  <main>

  </main>
</body>

</html>