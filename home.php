<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

/* FETCH DEALS */
include 'config/connection.php';

$res = mysqli_query($conn, "SELECT * FROM produtos WHERE desconto != 0 ORDER BY desconto DESC LIMIT 15");

if (mysqli_num_rows($res) == 0) {
  $res = mysqli_query($conn, "SELECT * FROM produtos ORDER BY valor LIMIT 15");
  $deals = mysqli_fetch_all($res, MYSQLI_ASSOC);

  mysqli_free_result($res);
} else if (mysqli_num_rows($res) < 15) {
  $totalFetched = mysqli_num_rows($res);
  $totalToFetch = 15 - $totalFetched;
  $deals = mysqli_fetch_all($res, MYSQLI_ASSOC);

  $res = mysqli_query($conn, "SELECT * FROM produtos WHERE desconto = 0 ORDER BY valor LIMIT $totalToFetch");
  $missingDeals = mysqli_fetch_all($res, MYSQLI_ASSOC);

  foreach($missingDeals as $missingDeal) {
    array_push($deals, $missingDeal);
  }
} else {
  $deals = mysqli_fetch_all($res, MYSQLI_ASSOC);
  mysqli_free_result($res);
}

mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <?php include 'templates/head.php'; ?>
  <link rel="stylesheet" href="styles/pages/home.css">
</head>

<body>
  <?php include 'templates/navbar.php'; ?>
  <?php include 'templates/topbar.php'; ?>
  <main>
    <h1>Bem-vindo <?php echo $_SESSION['user']; ?>!</h1>
    <section class="deals">
      <h2>Confira as melhores ofertas</h2>
      <?php
      $girdCount = 0;

      foreach ($deals as $deal) {
        if ($girdCount == 0) {
          echo "<div class='row'>";
        } 
        
        if ($girdCount < 4) {
          echo "<div class='product col-sm border rounded'>";
          ?>
          <p class="h6"><?php echo $deal['descricao']; ?></p>
          <?php if ($deal['desconto'] == 0) { ?>
            <p>R$ <?php echo $deal['valor']; ?></p>
          <?php } else { ?>
            <p class="old-price"><span class="strike">R$ <?php echo $deal['valor']; ?></span></p>
            <?php
              // Calculate discounted price
              $newPrice = $deal['valor'] - ($deal['desconto'] / 100) * $deal['valor'];
            ?>
            <p><span class="deal">R$ <?php echo number_format((float)$newPrice, 2, '.', ''); ?></span></p>
          <?php } ?>
          <?php if ($deal['estoque'] == 0) { ?>
            <p class="out-of-stock">Indisponível</p>
          <?php } else { ?>
            <p class="in-stock">Disponível: <?php echo $deal['estoque']; ?></p>
          <?php } ?>
          <?php
          echo "</div>";
          $girdCount += 1;
        } else {
          echo "<div class='product col-sm border rounded'>";
          ?>
          <p class="h6"><?php echo $deal['descricao']; ?></p>
          <?php if ($deal['desconto'] == 0) { ?>
            <p>R$ <?php echo $deal['valor']; ?></p>
          <?php } else { ?>
            <p class="old-price"><span class="strike">R$ <?php echo $deal['valor']; ?></span></p>
            <?php
              // Calculate discounted price
                $newPrice = $deal['valor'] - ($deal['desconto'] / 100) * $deal['valor'];
            ?>
            <p><span class="deal">R$ <?php echo number_format((float)$newPrice, 2, '.', ''); ?></span></p>
          <?php } ?>
          <?php if ($deal['estoque'] == 0) { ?>
            <p class="out-of-stock">Indisponível</p>
          <?php } else { ?>
            <p class="in-stock">Disponível: <?php echo $deal['estoque']; ?></p>
          <?php } ?>
          <?php
          echo "</div>";
          echo "</div>";
          $girdCount = 0;
        }
      }
      ?>
    </section>
  </main>
</body>

</html>