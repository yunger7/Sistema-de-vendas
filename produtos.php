<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

/* SUBMIT ORDER */
if (isset($_POST['submit-order'])) {
  $orderValue = $_POST['order-value'];
  $userId = $_SESSION['user-id'];
  $clientId = $_POST['client-id'];

  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT idvendedor FROM vendedores WHERE fk_idpessoa = '$userId'");
  $res2 = mysqli_fetch_array($res);
  $sellerId = $res2['idvendedor'];

  mysqli_free_result($res);

  $sql = "INSERT INTO pedidos(valor, fk_idvendedor, fk_idcliente) VALUES ('$orderValue', '$sellerId', '$clientId')";

  if (mysqli_query($conn, $sql)) {
    $orderId = mysqli_insert_id($conn);

    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
      $productId = $_SESSION['cart'][$i]['id'];
      $quant = $_SESSION['cart'][$i]['quant'];
    
      // calculate product value
      if ($_SESSION['cart'][$i]['disc'] == 0) {
        if ($_SESSION['cart'][$i]['quant'] == 1) {
          $value = $_SESSION['cart'][$i]['value'];
        } else {
          $value = $_SESSION['cart'][$i]['quant'] * $_SESSION['cart'][$i]['value'];
        }
      } else {
        if ($_SESSION['cart'][$i]['quant'] == 1) {
          $price = $_SESSION['cart'][$i]['value'];
        } else {
          $price = $_SESSION['cart'][$i]['quant'] * $_SESSION['cart'][$i]['value'];
        }
        $discount = $_SESSION['cart'][$i]['disc'];
        $value = $price - ($discount / 100) * $price;
      }

      $sql2 = "INSERT INTO itens_pedidos(fk_idpedido, fk_idproduto, qtd, valor) VALUES ('$orderId', '$productId', '$quant', '$value')";

      if (mysqli_query($conn, $sql2)) {
        $success = 1;
      } else {
        $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php', 'text' => 'Houve um problema ao finalizar o pedido'];
        header('location: templates/finish-operation.php');
      }
    }

    if ($success === 1) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'produtos.php', 'text' => 'Pedido finalizado com sucesso!'];
      header('location: templates/finish-operation.php');
    }

  } else {
    $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php', 'text' => 'Houve um problema ao finalizar o pedido'];
    header('location: templates/finish-operation.php');
  }

  mysqli_close($conn);
}

/* SEND PAGES */
if (isset($_GET['cart'])) { /* Cart page */ ?>
  <?php
  // Sort cart
  sort($_SESSION['cart']);

  /* CHANGE PRODUCT QUANTITY */
  if (isset($_GET['quant'])) {
    $quant = $_POST['quant'];
    $idEdit = $_POST['id'];

    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
      if ($_SESSION['cart'][$i]['id'] === $idEdit) {
        include 'config/connection.php';

        $res = mysqli_query($conn, "SELECT estoque FROM produtos WHERE idproduto = '$idEdit' ");
        $estoque = mysqli_fetch_row($res);

        if ($quant > $estoque[0]) {
          echo "
            <script language='javascript' type='text/javascript'>
            alert('Valor inserido acima do estoque');
            </script>
          ";
        } else {
          $_SESSION['cart'][$i]['quant'] = $quant;

          if ($_SESSION['cart'][$i]['quant'] == 1) {  
            $_SESSION['cart'][$i]['total'] = $_SESSION['cart'][$i]['value'];
          } else {
            $_SESSION['cart'][$i]['total'] *= $quant;
          }
        }

        mysqli_free_result($res);
        mysqli_close($conn);
      }
    }
  }

  /* ADD DISCOUNT TO PRODUCT */
  if (isset($_GET['disc'])) {
    $idEdit = $_POST['id'];
    $discount = $_POST['disc'];

    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
      if ($_SESSION['cart'][$i]['id'] === $idEdit) {
        if ($discount !== $_SESSION['cart'][$i]['disc']) {
          $_SESSION['cart'][$i]['disc'] = $discount;

          if ($_SESSION['cart'][$i]['disc'] === 0) {
            if ($_SESSION['cart'][$i]['quant'] === 1) {
              $_SESSION['cart'][$i]['total'] = $_SESSION['cart'][$i]['value'];
            } else {
              $_SESSION['cart'][$i]['total'] = $_SESSION['cart'][$i]['value'] * $_SESSION['cart'][$i]['quant'];
            }
          } else {
            $total = $_SESSION['cart'][$i]['total'];
            $discountPrice = $total - ($discount / 100) * $total;
            $_SESSION['cart'][$i]['total'] = $discountPrice;
          }
        }
      }
    }
  }
  
  /* DELETE ITEMS FROM CART */
  if (isset($_GET['remove'])) {
    $idRemove = $_GET['id'];

    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
      if ($_SESSION['cart'][$i]['id'] === $idRemove) {
        unset($_SESSION['cart'][$i]);
      }
    }

    sort($_SESSION['cart']);
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
            <?php foreach($products as $product){ ?>
              <?php foreach($_SESSION['cart'] as $cartItem){ ?>
                <?php if ($cartItem['id'] === $product['idproduto']){ ?>
                  <tr>
                    <td><a href="produtos.php?cart&remove&id=<?php echo $cartItem['id']; ?>" class="btn btn-danger">X</a></td>
                    <td><?php echo $product['descricao']; ?></td>
                    <td>
                      <form action="<?php echo $_SERVER['PHP_SELF']; ?>?cart&quant" method="POST">
                        <input type="hidden" name="id" value="<?php echo $cartItem['id']; ?>">
                        <input type="number" name="quant" min="1" class="quant form-control" placeholder="1"
                        <?php
                        if (!empty($_SESSION['cart'])) {
                          for ($i = 0; $i < count($_SESSION['cart']); $i++) {
                            if ($_SESSION['cart'][$i]['id'] === $cartItem['id']) {
                              if ($_SESSION['cart'][$i]['quant'] !== 1) {
                                $quant = $_SESSION['cart'][$i]['quant'];
                                echo "value='". $quant ."'";
                              }
                            }
                          }
                        }
                        ?>
                        >
                      </form>
                    </td>
                    <td>
                      <form action="<?php echo $_SERVER['PHP_SELF']; ?>?cart&disc" method="POST">
                        <input type="hidden" name="id" value="<?php echo $cartItem['id']; ?>">
                        <input type="number" name="disc" min="0" max="100" class="disc form-control" placeholder="0%"
                        <?php
                        if (!empty($_SESSION['cart'])) {
                          for ($i = 0; $i < count($_SESSION['cart']); $i++) {
                            if ($_SESSION['cart'][$i]['id'] === $cartItem['id']) {
                              if ($_SESSION['cart'][$i]['disc'] !== 0) {
                                $disc = $_SESSION['cart'][$i]['disc'];
                                echo "value='". $disc ."'";
                              }
                            }
                          }
                        }
                        ?>
                        >
                      </form>
                    </td>
                    <td>R$ 
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        for ($i = 0; $i < count($_SESSION['cart']); $i++) {
                          if ($_SESSION['cart'][$i]['id'] === $cartItem['id']) {
                            $total = $_SESSION['cart'][$i]['total'];
                            echo number_format((float)$total, 2, '.', '');
                          }
                        }
                      }
                      ?>
                    </td>
                  </tr>
                <?php } ?>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>
        <?php if ($_SESSION['priority'] > 0) { /* SELLER OR ADMIN */ ?>
          <section class="finish priority-2">
            <div class="total">
              <p class="h5"><strong>Total</strong></p>
              <p class="total-value">R$ 
                <?php
                if (!empty($_SESSION['cart'])) {
                  $sum = 0;
                  foreach ($_SESSION['cart'] as $cartItem) {
                    $sum += $cartItem['total'];
                  }
                  echo number_format((float)$sum, 2, '.', '');
                } else {
                  echo "0,00";
                }
                ?>
              </p>
            </div>
            <div class="buttons">
              <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
              <a href="<?php if (!empty($_SESSION['cart'])) { echo "produtos.php?final";} else { echo "#"; } ?>">
              <button type="button" class="btn btn-info" <?php if (empty($_SESSION['cart'])) { echo "disabled"; } ?>>Finalizar Pedido</button>
              </a>
            </div>
          </section>
        <?php } else { /* CLIENT */ ?>
          <section class="finish priority-0">
            <div class="total">
              <p class="h5"><strong>Total</strong></p>
              <p class="total-value">R$ 
                <?php
                if (!empty($_SESSION['cart'])) {
                  $sum = 0;
                  foreach ($_SESSION['cart'] as $cartItem) {
                    $sum += $cartItem['total'];
                  }
                  echo number_format((float)$sum, 2, '.', '');
                } else {
                  echo "0,00";
                }
                ?>
              </p>
            </div>
            <div class="buttons">
              <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
            </div>
          </section>
        <?php } ?>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['name']) || isset($_GET['letter'])) { /* Search page */ ?>
  <?php
  include 'config/connection.php';

  $cases = [$_GET['name'] ?? "", $_GET['letter'] ?? ""];

  switch ($cases) {
      // search by name
    case ($cases[0] !== "" && $cases[1] === ""):
      $name = $_GET['name'];
      $sql = "SELECT * FROM produtos WHERE descricao LIKE '%$name%'";
      break;
      // search by letter
    case ($cases[0] === "" && $cases[1] !== ""):
      $letter = $_GET['letter'];
      $sql = "SELECT * FROM produtos WHERE descricao LIKE '$letter%'";
      break;
      // empty search
    case ($cases[0] === "" && $cases[1] === ""):
      $sql = "SELECT * FROM produtos";
      break;
  }

  // Database search
  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) > 0) {
    $searchResults = 1;
    $products = mysqli_fetch_all($res, MYSQLI_ASSOC);
  } else {
    $searchResults = 0;
  }

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
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="input-group">
            <input type="text" name="name" id="product-name" class="form-control" placeholder="Pesquisar">
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
          Ver carrinho
        </a>
        <?php if ($searchResults === 0) { ?>
          <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
        <?php } else { ?>
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
                      <button type="submit" name="add-to-cart" class="btn btn-info" 
                        <?php
                        if (!empty($_SESSION['cart'])) {
                          $empty = 0;
                          foreach ($_SESSION['cart'] as $cartItem) {
                            if ($cartItem['id'] === $product['idproduto']) {
                              $empty = 1;
                            }
                          }

                          if ($empty === 1) {
                            echo "disabled";
                          }
                        }

                        ?>
                      >
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        $empty = 0;
                        foreach ($_SESSION['cart'] as $cartItem) {
                          if ($cartItem['id'] === $product['idproduto']) {
                            $empty = 1;
                          }
                        }

                        if ($empty === 1) {
                          echo "Adicionado";
                        } else {
                          echo "Adicionar";
                        }
                      } else {
                        echo "Adicionar";
                      }
                      ?>
                      </button>
                      <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                      <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                      <p class="in-stock">Disponível <?php echo $product['estoque'] ?>
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
                      <button type="submit" name="add-to-cart" class="btn btn-info" 
                        <?php
                        if (!empty($_SESSION['cart'])) {
                          $empty = 0;
                          foreach ($_SESSION['cart'] as $cartItem) {
                            if ($cartItem['id'] === $product['idproduto']) {
                              $empty = 1;
                            }
                          }

                          if ($empty === 1) {
                            echo "disabled";
                          }
                        }

                        ?>
                      >
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        $empty = 0;
                        foreach ($_SESSION['cart'] as $cartItem) {
                          if ($cartItem['id'] === $product['idproduto']) {
                            $empty = 1;
                          }
                        }

                        if ($empty === 1) {
                          echo "Adicionado";
                        } else {
                          echo "Adicionar";
                        }
                      } else {
                        echo "Adicionar";
                      }
                      ?>
                      </button>
                      <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                      <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                      <p class="in-stock">Disponível <?php echo $product['estoque'] ?>
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
        <?php } ?>
      </section>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['final']) || isset($_GET['final-name']) || isset($_GET['final-letter'])) { /* Finalize order page */ ?>
  <?php if (isset($_GET['final-name']) || isset($_GET['final-letter'])) {  /* Choose client (list) */ ?>
    <?php
    include 'config/connection.php';

    $cases = [$_GET['final-name'] ?? "", $_GET['final-letter'] ?? ""];
  
    switch ($cases) {
        // search by name
      case ($cases[0] !== "" && $cases[1] === ""):
        $name = $_GET['final-name'];
        $sql = "SELECT * FROM clientes JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE nome LIKE '%$name%'";
        break;
        // search by letter
      case ($cases[0] === "" && $cases[1] !== ""):
        $letter = $_GET['final-letter'];
        $sql = "SELECT * FROM clientes JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE nome LIKE '$letter%'";
        break;
        // empty search  
      case ($cases[0] === "" && $cases[1] === ""):
        $sql = "SELECT * FROM clientes JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa";
        break;
    }
  
    // Database search
    $res = mysqli_query($conn, $sql);
  
    if (mysqli_num_rows($res) > 0) {
      $searchResults = 1;
      $clients = mysqli_fetch_all($res, MYSQLI_ASSOC);
    } else {
      $searchResults = 0;
    }
  
    mysqli_free_result($res);
    mysqli_close($conn);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
      <?php include 'templates/head.php';?>
      <link rel="stylesheet" href="styles/pages/produtos.css">
    </head>
    <body id="final-list">
      <?php include 'templates/navbar.php'; ?>
      <?php include 'templates/topbar.php'; ?>
      <main>
        <?php if ($searchResults == 0) { ?>
          <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th scope="col">Nome</th>
                <th scope="col">CPF</th>
                <th scope="col">Renda</th>
                <th scope="col">Crédito</th>
                <th scope="col">Opções</th>
              </tr>
            </thead>
          </table>
          <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
          <a href="produtos.php?final" class="btn btn-secondary mt-3">Voltar</a>
        <?php } else { ?>
          <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th scope="col">Nome</th>
                <th scope="col">CPF</th>
                <th scope="col">Renda</th>
                <th scope="col">Crédito</th>
                <th scope="col">Opções</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($clients as $client) { ?>
                <tr>
                  <td><?php echo $client['nome']; ?></td>
                  <td><?php echo $client['cpf']; ?></td>
                  <td><?php echo $client['renda']; ?></td>
                  <td><?php echo $client['credito']; ?></td>
                  <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                      <?php
                      /* GET VALUES TO SUBMIT ORDER */
                      // order value
                      $orderValue = 0;
                      foreach ($_SESSION['cart'] as $cartItem) {
                        $orderValue += $cartItem['total'];
                      }

                      // client id
                      $clientId = $client['idcliente'];
                      ?>
                      <input type="hidden" name="order-value" value="<?php echo $orderValue; ?>">
                      <input type="hidden" name="client-id" value="<?php echo $clientId; ?>">
                      <button type="submit" name="submit-order" class="btn btn-outline-info">Selecionar</button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } ?>
      </main>
    </body>
    </html>
  <?php } else { /* Choose client (Search) */ ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
      <?php include 'templates/head.php';?>
      <link rel="stylesheet" href="styles/pages/produtos.css">
    </head>
    <body id="final-search">
      <main>
        <h1>Insira os dados do cliente</h1>
        <section class="search">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="input-group">
            <input type="text" name="final-name" id="product-name" class="form-control" placeholder="Deixe em branco para pesquisar todos os clientes">
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
              echo "<input type='submit' name='final-letter' value='$letra' class='btn btn-link'>";
            }
            ?>
          </div>
        </form>
      </section>
      </main>
    </body>
    </html>
  <?php } ?>
<?php } else { /* Index page */ ?>
  <?php
  /* ADD PRODUCTS TO CART */
  if (!isset($_GET['add-to-cart'])) {
    // First load
    $_SESSION['cart'] = [];
  }

  if (isset($_GET['add-to-cart'])) {
    $id = $_GET['id-produto'];
    $value = $_GET['value'];

    if (empty($_SESSION['cart'])) {
      $_SESSION['cart'][] = ['id' => $id, 'value' => $value, 'quant' => 1, 'disc' => 0, 'total' => $value];
    } else {
      // check if product is already in the cart
      $exist = 0;
      foreach ($_SESSION['cart'] as $cartItem) {
        if ($cartItem['id'] === $id) {
          $exist = 1;
        }
      }

      if ($exist === 0) {
        $_SESSION['cart'][] = ['id' => $id, 'value' => $value, 'quant' => 1, 'disc' => 0, 'total' => $value];
      }
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
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="input-group">
            <input type="text" name="name" id="product-name" class="form-control" placeholder="Pesquisar">
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
        <?php if ($_SESSION['priority'] > 0) { /* SELLER OR ADMIN */ ?>
          <a href="<?php echo $_SERVER['PHP_SELF']; ?>?cart" class="btn btn-info">
          <svg width="25px" height="25px" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="shopping-cart" class="svg-inline--fa fa-shopping-cart fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
            <path fill="currentColor" d="M528.12 301.319l47.273-208C578.806 78.301 567.391 64 551.99 64H159.208l-9.166-44.81C147.758 8.021 137.93 0 126.529 0H24C10.745 0 0 10.745 0 24v16c0 13.255 10.745 24 24 24h69.883l70.248 343.435C147.325 417.1 136 435.222 136 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-15.674-6.447-29.835-16.824-40h209.647C430.447 426.165 424 440.326 424 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-22.172-12.888-41.332-31.579-50.405l5.517-24.276c3.413-15.018-8.002-29.319-23.403-29.319H218.117l-6.545-32h293.145c11.206 0 20.92-7.754 23.403-18.681z"></path>
          </svg>
          Ver carrinho</a>
        <?php } else { /* CLIENT */ ?>
          <a href="<?php echo $_SERVER['PHP_SELF']; ?>?cart" class="btn btn-info">
            <svg width="25px" height="25px" viewBox="0 0 16 16" class="bi bi-calculator-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm2 .5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-2zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zM4.5 9a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zM4 12.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zM7.5 6a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zM7 9.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm.5 2.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zM10 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm.5 2.5a.5.5 0 0 0-.5.5v4a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 0-.5-.5h-1z"/>
            </svg>
            Calcular preços
          </a>
        <?php } ?>
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
                    <button type="submit" name="add-to-cart" class="btn btn-info" 
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        $empty = 0;
                        foreach ($_SESSION['cart'] as $cartItem) {
                          if ($cartItem['id'] === $product['idproduto']) {
                            $empty = 1;
                          }
                        }

                        if ($empty === 1) {
                          echo "disabled";
                        }
                      }

                      ?>
                    >
                    <?php
                    if (!empty($_SESSION['cart'])) {
                      $empty = 0;
                      foreach ($_SESSION['cart'] as $cartItem) {
                        if ($cartItem['id'] === $product['idproduto']) {
                          $empty = 1;
                        }
                      }

                      if ($empty === 1) {
                        echo "Adicionado";
                      } else {
                        echo "Adicionar";
                      }
                    } else {
                      echo "Adicionar";
                    }
                    ?>
                    </button>
                    <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                    <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                    <p class="in-stock">Disponível <?php echo $product['estoque'] ?>
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
                    <button type="submit" name="add-to-cart" class="btn btn-info" 
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        $empty = 0;
                        foreach ($_SESSION['cart'] as $cartItem) {
                          if ($cartItem['id'] === $product['idproduto']) {
                            $empty = 1;
                          }
                        }

                        if ($empty === 1) {
                          echo "disabled";
                        }
                      }

                      ?>
                    >
                    <?php
                    if (!empty($_SESSION['cart'])) {
                      $empty = 0;
                      foreach ($_SESSION['cart'] as $cartItem) {
                        if ($cartItem['id'] === $product['idproduto']) {
                          $empty = 1;
                        }
                      }

                      if ($empty === 1) {
                        echo "Adicionado";
                      } else {
                        echo "Adicionar";
                      }
                    } else {
                      echo "Adicionar";
                    }
                    ?>
                    </button>
                    <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                    <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                    <p class="in-stock">Disponível <?php echo $product['estoque'] ?>
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