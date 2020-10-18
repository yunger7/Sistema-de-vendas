<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'templates/head.php'; ?>
</head>

<body>
  <?php include 'templates/navbar.php'; ?>
  <?php include 'templates/topbar.php'; ?>
  <main>
  <h1>HOME</h1>
  </main>
</body>

</html>