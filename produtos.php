<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

/* GET PRODUCTS IN DATABASE */
include 'config/connection.php';

$res = mysqli_query($conn, "SELECT * FROM produtos");
$products = mysqli_fetch_all($res, MYSQLI_ASSOC);

// print_r($products);

mysqli_free_result($res);
mysqli_close($conn);


?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <?php include 'templates/head.php'; ?>
  <link rel="stylesheet" href="styles/pages/produtos.css">
</head>

<body>
  <?php include 'templates/navbar.php'; ?>
  <?php include 'templates/topbar.php'; ?>
  <main>
    <section class="search">
      <form action="#" method="GET">
        <div class="input-group">
          <input type="text" name="product-name" id="product-name" class="form-control" placeholder="Pesquisar">
          <div class="input-group-append">
            <button type="submit" class="btn btn-outline-info">
              <svg id="search-input-button" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" class="svg-inline--fa fa-search fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path>
              </svg>
            </button>
          </div>
        </div>
        <div class="search-letters">
          <?php
          $alfabeto = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
          foreach ($alfabeto as $letra) {
            echo "<input type='submit' name='letter' value='$letra' class='btn btn-link'>";
          }
          ?>
        </div>
      </form>
    </section>
    <section class="products">
      <form action="">
        <?php
        $gridCount = 0;
        foreach ($products as $product) { ?>
          <?php if ($gridCount == 0) { ?>
            <div class="row">
            <?php } ?>
            <?php if ($gridCount < 4) { ?>
              <div class="product col-sm <?php if ($product['estoque'] > 0) { echo "border"; } ?> rounded">
                <p class="h6"><?php echo $product['descricao']; ?></p>
                <p>R$ <?php echo $product['valor']; ?></p>
                <button class="btn btn-outline-info value='<?php echo $product['idproduto']; ?>'" <?php if ($product['estoque'] == 0) { echo "disabled"; } ?>>Adicionar</button>
                <?php if ($product['estoque'] > 0) { ?>
                  <p class="in-stock">Disponível <span><?php echo $product['estoque'] ?></span></p>
                <?php } else { ?>
                  <p class="out-of-stock">Indisponível</p>
                <?php } ?>
              </div>
              <?php $gridCount += 1; ?>
            <?php } else { ?>
              <div class="product col-sm <?php if ($product['estoque'] > 0) { echo "border"; } ?> rounded">
                <p class="h6"><?php echo $product['descricao']; ?></p>
                <p>R$ <?php echo $product['valor']; ?></p>
                <button class="btn btn-outline-info" <?php if ($product['estoque'] == 0) { echo "disabled"; } ?>>Adicionar</button>
                <?php if ($product['estoque'] > 0) { ?>
                  <p class="in-stock">Disponível <span><?php echo $product['estoque'] ?></span></p>
                <?php } else { ?>
                  <p class="out-of-stock">Indisponível</p>
                <?php } ?>
              </div>
            </div>
            <?php $gridCount = 0; ?>
          <?php } ?>
        <?php } ?>
      </form>
    </section>
  </main>
</body>

</html>