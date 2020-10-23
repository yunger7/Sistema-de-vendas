<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'templates/head.php'; ?>
</head>
<body>
  <main>
    <?php
    session_start();
    $_SESSION['test'] = [];

    print_r($_SESSION['test']);
    echo "<br/>";

    // $_SESSION['test'][] = ['id' => 1, 'value' => 2.50, 'quant' => 1, 'disc' => 0];
    // $_SESSION['test'][] = ['id' => 2, 'value' => 2, 'quant' => 1, 'disc' => 0];
    // $_SESSION['test'][] = ['id' => 3, 'value' => 1, 'quant' => 1, 'disc' => 0];

    print_r($_SESSION['test']);
    echo "<br/>";

    $id = 5;

    if (empty($_SESSION['test'])) {
      // echo "is empty";
      
    }

    foreach($_SESSION['test'] as $testItem) {
      if ($testItem['id'] === $id) {
        $exist = 1;
      }
      $exist = 0;
    }

    if ($exist === 1) {
      echo "exist";
    } else {
      echo "doesn't exist";
    }
    
    ?>
  </main>
</body>
</html>