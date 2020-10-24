<?php
session_start();

$type = $_SESSION['finish-operation']['type'];
$url = $_SESSION['finish-operation']['url'];
$text = $_SESSION['finish-operation']['text'];
$string = "Refresh: 1.5; URL=../" . $url;

unset($_SESSION['finish-operation']);

header($string);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <?php include 'head.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de vendas</title>

  <link rel="shortcut icon" href="../assets/shopping.svg" type="image/x-icon">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
  <link rel="stylesheet" href="../styles/main.css">
  <link rel="stylesheet" href="../styles/templates/finish-operation.css">
</head>
<body>
  <?php if ($type == "success") { ?>
    <main id="success">
      <div>
        <svg viewBox="0 0 16 16" class="bi bi-check" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.236.236 0 0 1 .02-.022z"/>
        </svg>
        <p class="text h3"><?php echo $text ?></p>
      </div>
    </main>
  <?php } else if ($type == "error") { ?>
    <main id="error">
      <div>
        <svg viewBox="0 0 16 16" class="bi bi-x" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
        </svg>
        <p class="text h3"><?php echo $text ?></p>
      </div>
    </main>
  <?php } ?>
  
</body>
</html>