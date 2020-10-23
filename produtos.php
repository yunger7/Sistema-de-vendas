<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

/* SEND PAGES */
if (isset($_GET['cart'])) { /* Cart page */ ?>
  <?php
  /* SORT CART */
  sort($_SESSION['cart']);

  print_r($_SESSION['cart']);
  echo "<br/>";
  print_r($_SESSION['value']);

  /* CHANGE ITEM QUANTITY */
  if (isset($_GET['quant'])) {
    $quant = $_POST['quant'];
    $id = $_POST['id'];


    // Verify if item already has quantity
    // (still working on it)

    $_SESSION['quant'][] = ['id' => $id, 'quant' => $quant];

    print_r($_SESSION['quant']);
  }
  
  /* DELETE ITEMS FROM CART */
  if (isset($_GET['remove'])) {
    $idRemove = $_GET['id'];

    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
      if ($_SESSION['cart'][$i] === $idRemove) {
        unset($_SESSION['cart'][$i]);
      }
    }
  }

  /* GET DATA FROM DATABASE */
  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT idproduto, descricao, valor FROM produtos");
  $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

  mysqli_free_result($res);
  mysqli_close($conn);

  ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/produtos.css">
  </head>

  <body id="cart">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col"></th>
              <th scope="col">Nome</th>
              <th scope="col">Quantidade</th>
              <th scope="col">Desconto</th>
              <th scope="col">Valor</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product) { ?>
              <?php foreach ($_SESSION['cart'] as $item) { ?>
                <?php if ($item == $product['idproduto']) { ?>
                  <tr>
                    <td><a href="produtos.php?cart&remove&id=<?php echo $item; ?>" class="btn btn-danger">X</a></td>
                    <td><?php echo $product['descricao']; ?></td>
                    <td>
                      <form action="<?php echo $_SERVER['PHP_SELF']; ?>?cart&quant" method="POST">
                        <input type="number" name="quant" min="1" class="quant form-control" placeholder="1"
                        <?php
                        foreach ($_SESSION['quant'] as $itemQuant) {
                          if ($itemQuant['id'] == $item) {
                            echo "value='". $itemQuant['quant'] ."'";
                          }
                        }
                        ?>
                        >
                        <input type="hidden" name="id" value="<?php echo $item; ?>">
                      </form>
                    </td>
                    <td>
                      <form action="<?php echo $_SERVER['PHP_SELF']; ?>?cart&desc" method="POST">
                        <input type="number" name="desc" min="0" class="desc form-control" placeholder="R$ 0,00">
                        <input type="hidden" name="id" value="<?php echo $item; ?>">
                      </form>
                    </td>
                    <td>R$
                    </td>
                  </tr>
                <?php } ?>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>
        <section class="finish">
          <div class="total">
            <p class="h5"><strong>Total</strong></p>
            <p class="total-value">R$ 50,00</p>
          </div>
          <div class="buttons">
            <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-info">Finalizar pedido</button>
          </div>
        </section>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['name']) || isset($_GET['letter'])) { /* Search page */ ?>
<?php } else { /* Index page */ ?>
  <?php
  /* ADD PRODUCTS TO CART */
  if (!isset($_GET['add-to-cart'])) {
    // First load
    $_SESSION['cart'] = [];
    $_SESSION['quant'] = [];
    $_SESSION['desc'] = [];
    $_SESSION['value'] = [];
  }

  if (isset($_GET['add-to-cart'])) {
    $id = $_GET['id-produto'];
    $value = $_GET['value'];

    if (!in_array($id, $_SESSION['cart'])) {
      $_SESSION['cart'][] = $id;
      $_SESSION['value'][] = ['id' => $id, 'value' => $value];
    }
  }

  /* GET PRODUCTS IN DATABASE */
  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT * FROM produtos");
  $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

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
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?cart" class="btn btn-info">
          <svg id="cart-logo" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="shopping-cart" class="svg-inline--fa fa-shopping-cart fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="25px" height="25px">
            <path fill="currentColor" d="M528.12 301.319l47.273-208C578.806 78.301 567.391 64 551.99 64H159.208l-9.166-44.81C147.758 8.021 137.93 0 126.529 0H24C10.745 0 0 10.745 0 24v16c0 13.255 10.745 24 24 24h69.883l70.248 343.435C147.325 417.1 136 435.222 136 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-15.674-6.447-29.835-16.824-40h209.647C430.447 426.165 424 440.326 424 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-22.172-12.888-41.332-31.579-50.405l5.517-24.276c3.413-15.018-8.002-29.319-23.403-29.319H218.117l-6.545-32h293.145c11.206 0 20.92-7.754 23.403-18.681z"></path>
          </svg>
          Ver carrinho</a>
        <?php
        $gridCount = 0;
        foreach ($products as $product) { ?>
          <?php if ($gridCount == 0) { ?>
            <div class="row">
            <?php } ?>
            <?php if ($gridCount < 4) { ?>
              <div class="product col-sm <?php if ($product['estoque'] > 0) { echo "border"; } ?> rounded">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                  <p class="h6"><?php echo $product['descricao']; ?></p>
                  <p>R$ <?php echo $product['valor']; ?></p>
                  <?php if ($product['estoque'] > 0) { ?>
                    <button type="submit" name="add-to-cart" class="btn btn-info" <?php if (in_array($product['idproduto'], $_SESSION['cart'])) { echo "disabled"; } ?>>
                      <?php if (in_array($product['idproduto'], $_SESSION['cart'])) {
                        echo "Adicionado";
                      } else {
                        echo "Adicionar";
                      } ?>
                    </button>
                    <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                    <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                    <p class="in-stock">Disponível <?php echo $product['estoque'] ?></ span>
                    </p>
                  <?php } else { ?>
                    <p class="out-of-stock">Indisponível</p>
                  <?php } ?>
                </form>
              </div>
              <?php $gridCount += 1; ?>
            <?php } else { ?>
              <div class="product col-sm <?php if ($product['estoque'] > 0) { echo "border"; } ?> rounded">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                  <p class="h6"><?php echo $product['descricao']; ?></p>
                  <p>R$ <?php echo $product['valor']; ?></p>
                  <?php if ($product['estoque'] > 0) { ?>
                    <button type="submit" name="add-to-cart" class="btn btn-info" <?php if (in_array($product['idproduto'], $_SESSION['cart'])) { echo "disabled"; } ?>>
                      <?php if (in_array($product['idproduto'], $_SESSION['cart'])) {
                        echo "Adicionado";
                      } else {
                        echo "Adicionar";
                      } ?>
                    </button>
                    <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                    <p class="in-stock">Disponível <?php echo $product['estoque'] ?></ span>
                    </p>
                  <?php } else { ?>
                    <p class="out-of-stock">Indisponível</p>
                  <?php } ?>
                </form>
              </div>
            </div>
            <?php $gridCount = 0; ?>
          <?php } ?>
        <?php } ?>
      </section>
    </main>
  </body>

  </html>

<?php } ?>